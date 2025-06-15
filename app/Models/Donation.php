<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_id',
        'campaign_id',
        'user_id',
        'payment_gateway_id',
        'donor_name',
        'donor_email',
        'donor_phone',
        'is_anonymous',
        'amount',
        'currency',
        'fee_amount',
        'net_amount',
        'payment_reference',
        'payment_data',
        'status',
        'comment',
        'show_comment_publicly',
        'ip_address',
        'user_agent',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_data' => 'array',
        'show_comment_publicly' => 'boolean',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    public function scopeWithComments($query)
    {
        return $query->where('show_comment_publicly', true)->whereNotNull('comment');
    }

    // Accessors
    public function getDonorDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }

        if ($this->user) {
            return $this->user->full_name;
        }

        return $this->donor_name ?? 'Unknown Donor';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
