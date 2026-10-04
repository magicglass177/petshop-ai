<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Pemilik riwayat
            |--------------------------------------------------------------------------
            */

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Pet opsional
            |--------------------------------------------------------------------------
            |
            | null = customer menggunakan AI tanpa memilih pet.
            |
            */

            $table->foreignId('pet_id')
                ->nullable()
                ->constrained('pets')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Input customer
            |--------------------------------------------------------------------------
            */

            $table->text('question');


            /*
            |--------------------------------------------------------------------------
            | Hasil AI
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_relevant')
                ->default(true);

            $table->string('category', 150)
                ->nullable();

            $table->string('need_level', 50)
                ->nullable();

            $table->json('recommended_services')
                ->nullable();

            $table->text('summary')
                ->nullable();

            $table->text('safety_note')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Metadata AI
            |--------------------------------------------------------------------------
            */

            $table->string('gateway', 100)
                ->nullable();

            $table->string('model', 150)
                ->nullable();

            $table->unsignedInteger('prompt_tokens')
                ->nullable();

            $table->unsignedInteger('completion_tokens')
                ->nullable();

            $table->unsignedInteger('total_tokens')
                ->nullable();

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index([
                'customer_id',
                'created_at',
            ]);

            $table->index([
                'pet_id',
                'created_at',
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};