<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysis;
use App\Models\Service;
use App\Services\AiGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiController extends Controller
{
    public function analyze(Request $request, AiGatewayService $aiGateway)
    {
        $validated = $this->validateQuestion($request);
        $services = $this->activeServices();
        try {
            $aiResponse = $aiGateway->analyzeServices($validated['question'], $services);
            $result = $this->prepareResult($aiResponse['result'], $services);
            return response()->json([
                'success' => true,
                'message' => 'Analisis AI berhasil.',
                'data' => $result,
                'ai' => $this->aiMetadata($aiResponse),
            ]);
        } catch (Throwable $error) {
            return $this->analysisError($error);
        }
    }

    public function analyzeWithPet(Request $request, AiGatewayService $aiGateway)
    {
        if (!$request->user()) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
        }
        $validated = $this->validateQuestion($request, true);
        $user = $request->user();
        $customer = $user->customer;
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil customer tidak ditemukan.'], 404);
        }
        $pet = null;
        if (!empty($validated['pet_id'])) {
            // Kepemilikan selalu diperiksa melalui relasi customer yang sedang login.
            $pet = $customer->pets()->whereKey($validated['pet_id'])->first();
            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data hewan tidak ditemukan atau bukan milik akun Anda.',
                ], 404);
            }
        }
        $services = $this->activeServices();
        $petData = $pet ? [
            'id' => $pet->id,
            'name' => $pet->name,
            'animal_type' => $pet->animal_type,
            'breed' => $pet->breed,
            'gender' => $pet->gender,
            'birth_date' => $pet->birth_date?->format('Y-m-d'),
            'weight' => $pet->weight !== null ? (float) $pet->weight : null,
            'notes' => $pet->notes,
        ] : null;

        try {
            $aiResponse = $aiGateway->analyzeServices($validated['question'], $services, $petData);
            $result = $this->prepareResult($aiResponse['result'], $services);
            $usage = $aiResponse['usage'] ?? [];

            // Cast tambahan berlaku pada instance ini, tanpa mengganti konfigurasi model lama.
            $analysis = new AiAnalysis();
            $analysis->mergeCasts(['health_details' => 'array']);
            // Hanya payload yang disusun server dan hasil yang divalidasi yang disimpan.
            $analysis->forceFill([
                'customer_id' => $customer->id,
                'pet_id' => $pet?->id,
                'question' => $validated['question'],
                'is_relevant' => $result['is_relevant'],
                'category' => $result['category'],
                'need_level' => $result['need_level'],
                'recommended_services' => $result['recommended_services'],
                'summary' => $result['summary'],
                'safety_note' => $result['safety_note'],
                'health_details' => [
                    'analysis_type' => $result['analysis_type'],
                    'possible_causes' => $result['possible_causes'],
                    'urgency' => $result['urgency'],
                    'follow_up_questions' => $result['follow_up_questions'],
                    'next_steps' => $result['next_steps'],
                ],
                'gateway' => 'AtmoRouter',
                'model' => $aiResponse['model'] ?? null,
                'prompt_tokens' => $this->tokenCount($usage, 'prompt_tokens'),
                'completion_tokens' => $this->tokenCount($usage, 'completion_tokens'),
                'total_tokens' => $this->tokenCount($usage, 'total_tokens'),
            ])->save();

            return response()->json([
                'success' => true,
                'message' => 'Analisis AI berhasil dan disimpan ke riwayat.',
                'analysis_id' => $analysis->id,
                'pet' => $petData,
                'data' => $result,
                'ai' => $this->aiMetadata($aiResponse),
            ]);
        } catch (Throwable $error) {
            return $this->analysisError($error, ['user_id' => $user->id, 'pet_id' => $pet?->id]);
        }
    }

    public function history(Request $request)
    {
        $customer = $request->user()?->customer;
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil customer tidak ditemukan.'], 404);
        }
        $histories = $customer->aiAnalyses()
            ->with(['pet:id,name,animal_type,breed'])->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Riwayat analisis AI berhasil diambil.',
            'data' => $histories->map(fn (AiAnalysis $analysis) => $this->serializeAnalysis($analysis))->values(),
        ]);
    }

    public function historyDetail(Request $request, AiAnalysis $analysis)
    {
        $customer = $request->user()?->customer;
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil customer tidak ditemukan.'], 404);
        }
        if ((int) $analysis->customer_id !== (int) $customer->id) {
            return response()->json(['success' => false, 'message' => 'Riwayat analisis tidak ditemukan.'], 404);
        }
        $analysis->load(['pet:id,name,animal_type,breed,gender,birth_date,weight']);
        return response()->json([
            'success' => true,
            'message' => 'Detail riwayat analisis AI berhasil diambil.',
            'data' => $this->serializeAnalysis($analysis),
        ]);
    }

    public function adminMonitoring()
    {
        $recentAnalyses = AiAnalysis::with([
            'customer:id,user_id', 'customer.user:id,name,email', 'pet:id,name,animal_type,breed',
        ])->latest()->limit(10)->get();
        return response()->json([
            'success' => true,
            'message' => 'Data monitoring AI berhasil diambil.',
            'data' => [
                'statistics' => [
                    'total_analyses' => AiAnalysis::count(),
                    'total_tokens' => (int) AiAnalysis::sum('total_tokens'),
                    'relevant_analyses' => AiAnalysis::where('is_relevant', true)->count(),
                    'with_pet' => AiAnalysis::whereNotNull('pet_id')->count(),
                    'without_pet' => AiAnalysis::whereNull('pet_id')->count(),
                ],
                'recent_analyses' => $recentAnalyses->map(fn (AiAnalysis $analysis) => $this->serializeAnalysis($analysis))->values(),
            ],
        ]);
    }

    private function validateQuestion(Request $request, bool $withPet = false): array
    {
        if (is_string($request->input('question'))) {
            $request->merge(['question' => trim($request->input('question'))]);
        }
        $rules = ['question' => ['required', 'string', 'min:10', 'max:2000']];
        if ($withPet) {
            $rules['pet_id'] = ['nullable', 'integer', 'min:1'];
        }
        return $request->validate($rules);
    }

    private function activeServices(): Collection
    {
        return Service::query()->where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'animal_type', 'description', 'price', 'duration']);
    }

    private function prepareResult(array $result, Collection $services): array
    {
        // Data tampilan layanan selalu berasal dari database, bukan nama/harga buatan AI.
        $serviceMap = $services->keyBy('id');
        $result['recommended_services'] = collect($result['recommended_services'])
            ->map(function (array $recommendation) use ($serviceMap) {
                $service = $serviceMap->get((int) $recommendation['service_id']);
                if (!$service) {
                    return null;
                }
                return [
                    'service_id' => (int) $service->id,
                    'name' => $service->name,
                    'animal_type' => $service->animal_type,
                    'description' => $service->description,
                    'price' => (float) $service->price,
                    'duration' => $service->duration,
                    'reason' => $recommendation['reason'],
                ];
            })->filter()->unique('service_id')->take(3)->values()->all();
        if (!$result['is_relevant'] || $result['analysis_type'] === 'health') {
            $result['recommended_services'] = [];
        }
        return $result;
    }

    private function serializeAnalysis(AiAnalysis $analysis): array
    {
        $analysis->mergeCasts(['health_details' => 'array']);
        $details = $analysis->health_details ?? [];
        if (!is_array($details)) {
            $details = [];
        }
        // Riwayat sebelum pengembangan tetap dapat dibaca tanpa detail kesehatan.
        return array_merge($analysis->toArray(), [
            'analysis_type' => $details['analysis_type'] ?? null,
            'possible_causes' => $details['possible_causes'] ?? [],
            'urgency' => $details['urgency'] ?? ['level' => 'not_applicable', 'reason' => '', 'action' => ''],
            'follow_up_questions' => $details['follow_up_questions'] ?? [],
            'next_steps' => $details['next_steps'] ?? [],
        ]);
    }

    private function aiMetadata(array $response): array
    {
        return [
            'gateway' => 'AtmoRouter',
            'model' => $response['model'] ?? null,
            'usage' => $response['usage'] ?? [],
        ];
    }

    private function tokenCount(array $usage, string $key): ?int
    {
        $value = $usage[$key] ?? null;
        return is_numeric($value) ? max(0, min(2147483647, (int) $value)) : null;
    }

    private function analysisError(Throwable $error, array $context = [])
    {
        Log::error('AI Pet Assistant Error', array_merge($context, ['message' => $error->getMessage()]));
        return response()->json([
            'success' => false,
            'message' => 'AI Pet Assistant sementara tidak dapat digunakan. Silakan coba kembali.',
        ], 503);
    }
}
