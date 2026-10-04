<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
{
    $categories = ProductCategory::withCount([
        'products' => function ($query) {
            $query->where('is_active', true);
        }
    ])
        ->orderBy('name')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $categories,
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:product_categories,name'
            ],
            'description' => [
                'nullable',
                'string'
            ],
        ]);

        $category = ProductCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori produk berhasil ditambahkan.',
            'data' => $category,
        ], 201);
    }

    public function show(ProductCategory $category)
{
    $category->load([
        'products' => function ($query) {
            $query->where('is_active', true)
                ->latest();
        }
    ]);

    return response()->json([
        'success' => true,
        'data' => $category,
    ]);
}

    public function update(
        Request $request,
        ProductCategory $category
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:product_categories,name,' . $category->id
            ],
            'description' => [
                'nullable',
                'string'
            ],
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori produk berhasil diperbarui.',
            'data' => $category,
        ]);
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Kategori tidak dapat dihapus karena masih digunakan oleh produk.'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori produk berhasil dihapus.',
        ]);
    }
}