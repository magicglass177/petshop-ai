<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $customers = Customer::query()
            ->with([
                'user:id,name,email,role,created_at',
            ])
            ->withCount('pets')

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {

                    $search = trim(
                        $request->search
                    );

                    $query->where(
                        function ($q) use ($search) {

                            /*
                            |--------------------------------------------------------------------------
                            | Cari dari tabel users
                            |--------------------------------------------------------------------------
                            */

                            $q->whereHas(
                                'user',
                                function ($userQuery) use ($search) {

                                    $userQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            )

                            /*
                            |--------------------------------------------------------------------------
                            | Cari nomor telepon
                            |--------------------------------------------------------------------------
                            */

                            ->orWhere(
                                'phone',
                                'like',
                                "%{$search}%"
                            )

                            /*
                            |--------------------------------------------------------------------------
                            | Cari alamat
                            |--------------------------------------------------------------------------
                            */

                            ->orWhere(
                                'address',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
                }
            )

            ->latest()
            ->get();


        return response()->json([
            'success' => true,

            'data' => $customers,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function show(Customer $customer)
    {
        $customer->load([
            'user:id,name,email,role,created_at',

            'pets' => function ($query) {
                $query->latest();
            },
        ]);


        return response()->json([
            'success' => true,

            'data' => $customer,
        ]);
    }
}