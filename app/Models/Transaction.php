<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'donation_id',
        'payment_gateway_id',
        'type',
        'amount',
        'currency',
        'status',
        'gateway_transaction_id',
        'gateway_reference',
        'gateway_response',
        'description',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDonations($query)
    {
        return $query->where('type', 'donation');
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDonation(): bool
    {
        return $this->type === 'donation';
    }

    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }
}
