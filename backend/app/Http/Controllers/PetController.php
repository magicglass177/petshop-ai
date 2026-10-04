<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;

class PetController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Daftar Hewan Milik Customer
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        $pets = $customer
            ->pets()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pets,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Tambah Hewan
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

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'gender' => [
                'nullable',
                'in:Jantan,Betina',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $customer = $request->user()->customer;

        $pet = $customer
            ->pets()
            ->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data hewan berhasil ditambahkan.',
            'data' => $pet,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Detail Hewan
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Pet $pet
    ) {
        $customer = $request->user()->customer;

        if ($pet->customer_id !== $customer->id) {
    return response()->json([
        'success' => false,
        'message' => 'Data hewan tidak ditemukan.',
    ], 404);
}

        return response()->json([
            'success' => true,
            'data' => $pet,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Hewan
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Pet $pet
    ) {
        $customer = $request->user()->customer;

        if ($pet->customer_id !== $customer->id) {
    return response()->json([
        'success' => false,
        'message' => 'Data hewan tidak ditemukan.',
    ], 404);
}

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

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'gender' => [
                'nullable',
                'in:Jantan,Betina',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $pet->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data hewan berhasil diperbarui.',
            'data' => $pet,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Hapus Hewan
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Pet $pet
    ) {
        $customer = $request->user()->customer;

        if ($pet->customer_id !== $customer->id) {
    return response()->json([
        'success' => false,
        'message' => 'Data hewan tidak ditemukan.',
    ], 404);
}

        $pet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data hewan berhasil dihapus.',
        ]);
    }
}