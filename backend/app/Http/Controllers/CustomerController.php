<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Service;

class CustomerController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();

        $customer = $user
            ->customer()
            ->with('pets')
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Profil customer tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,

                'name' => $user->name,
                'email' => $user->email,

                'phone' => $customer->phone,
                'address' => $customer->address,

                'pets' => $customer->pets,
            ],
        ]);
    }


    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $user = $request->user();

        $customer = $user->customer;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Profil customer tidak ditemukan.',
            ], 404);
        }

        $user->update([
            'name' => $validated['name'],
        ]);

        $customer->update([
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
            ],
        ]);
    }

    public function dashboard(Request $request)
{
    $user = $request->user();

    $customer = $user->customer;

    if (!$customer) {
        return response()->json([
            'success' => false,
            'message' => 'Profil customer tidak ditemukan.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Dashboard customer berhasil dimuat.',
        'data' => [
            'customer' => [
                'id' => $customer->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $customer->phone,
            ],

            'statistics' => [
                'pets' => $customer
                    ->pets()
                    ->count(),

                'products' => Product::where(
                    'is_active',
                    true
                )->count(),

                'services' => Service::where(
                    'is_active',
                    true
                )->count(),
            ],
        ],
    ]);
}
}