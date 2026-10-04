<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
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

        if ($request->filled('category_id')) {
            $query->where(
                'product_category_id',
                $request->category_id
            );
        }

        $products = $query
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function show(Product $product)
{
    if (!$product->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan.',
        ], 404);
    }

    $product->load('category');

    return response()->json([
        'success' => true,
        'data' => $product,
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_category_id' => [
                'required',
                'exists:product_categories,id'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string'
            ],
            'price' => [
                'required',
                'numeric',
                'min:0'
            ],
            'stock' => [
                'required',
                'integer',
                'min:0'
            ],
            'image' => [
                'nullable',
                'string',
                'max:255'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
        ]);

        $product = Product::create($validated);

        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.',
            'data' => $product,
        ], 201);
    }

    public function update(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'product_category_id' => [
                'required',
                'exists:product_categories,id'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string'
            ],
            'price' => [
                'required',
                'numeric',
                'min:0'
            ],
            'stock' => [
                'required',
                'integer',
                'min:0'
            ],
            'image' => [
                'nullable',
                'string',
                'max:255'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
        ]);

        $product->update($validated);

        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

        public function adminIndex(Request $request)
{
    $products = Product::query()
        ->with('category')

        ->when(
            $request->filled('search'),
            function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );
                });
            }
        )

        ->when(
            $request->filled('category_id'),
            function ($query) use ($request) {
                $query->where(
                    'product_category_id',
                    $request->category_id
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

        ->latest()
        ->get();

    return response()->json([
        'success' => true,
        'data' => $products,
    ]);
}
}