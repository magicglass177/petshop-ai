<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    protected $fillable = [
        'customer_id',
        'pet_id',
        'question',
        'is_relevant',
        'category',
        'need_level',
        'recommended_services',
        'summary',
        'safety_note',
        'gateway',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
    ];


    protected function casts(): array
    {
        return [
            'is_relevant' => 'boolean',
            'recommended_services' => 'array',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Customer pemilik analisis
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Pet yang digunakan pada analisis
    |--------------------------------------------------------------------------
    */

    public function pet(): BelongsTo
    {
        return $this->belongsTo(
            Pet::class
        );
    }
}