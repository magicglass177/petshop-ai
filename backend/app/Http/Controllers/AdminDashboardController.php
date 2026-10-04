<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Dashboard admin berhasil dimuat.',
            'data' => [

                'statistics' => [
                    'products' => Product::count(),

                    'active_products' => Product::where(
                        'is_active',
                        true
                    )->count(),

                    'categories' => ProductCategory::count(),

                    'services' => Service::count(),

                    'active_services' => Service::where(
                        'is_active',
                        true
                    )->count(),

                    'customers' => Customer::count(),
                ],

            ],
        ]);
    }
}