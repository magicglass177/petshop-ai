<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PUBLIC - Daftar Layanan
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Service::query()
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('animal_type')) {
            $query->where(
                'animal_type',
                $request->animal_type
            );
        }

        $services = $query
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | PUBLIC - Detail Layanan
    |--------------------------------------------------------------------------
    */
    public function show(Service $service)
    {
        if (!$service->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN - Tambah Layanan
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'animal_type' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'duration' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $service = Service::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan.',
            'data' => $service,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN - Update Layanan
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Service $service
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'animal_type' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'duration' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $service->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil diperbarui.',
            'data' => $service,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN - Hapus Layanan
    |--------------------------------------------------------------------------
    */
    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dihapus.',
        ]);
    }

    public function adminIndex(Request $request)
{
    $services = Service::query()

        ->when(
            $request->filled('search'),
            function ($query) use ($request) {

                $search = $request->search;

                $query->where(
                    function ($q) use ($search) {

                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'animal_type',
                            'like',
                            "%{$search}%"
                        );

                    }
                );
            }
        )

        ->when(
            $request->filled('animal_type'),
            function ($query) use ($request) {

                $query->where(
                    'animal_type',
                    $request->animal_type
                );
            }
        )

        ->when(
            $request->filled('status'),
            function ($query) use ($request) {

                if ($request->status === 'active') {
                    $query->where(
                        'is_active',
                        true
                    );
                }

                if ($request->status === 'inactive') {
                    $query->where(
                        'is_active',
                        false
                    );
                }
            }
        )

        ->orderBy('name')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $services,
    ]);
}
}