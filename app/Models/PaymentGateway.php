<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'provider_class',
        'configuration',
        'fee_percentage',
        'fee_fixed',
        'currency',
        'is_active',
        'supports_refunds',
        'logo',
        'sort_order',
    ];

    protected $casts = [
        'configuration' => 'array',
        'fee_percentage' => 'decimal:2',
        'fee_fixed' => 'decimal:2',
        'is_active' => 'boolean',
        'supports_refunds' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculateFee(float $amount): float
    {
        $percentageFee = ($amount * $this->fee_percentage) / 100;

        return $percentageFee + $this->fee_fixed;
    }
}
