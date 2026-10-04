<?php

use App\Models\AiAnalysis;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ikuti nama tabel model yang sudah digunakan proyek.
        Schema::table((new AiAnalysis())->getTable(), function (Blueprint $table) {
            $table->json('health_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table((new AiAnalysis())->getTable(), function (Blueprint $table) {
            $table->dropColumn('health_details');
        });
    }
};
