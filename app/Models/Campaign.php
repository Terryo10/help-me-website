<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'story',
        'goal_amount',
        'raised_amount',
        'currency',
        'featured_image',
        'gallery',
        'status',
        'start_date',
        'end_date',
        'location',
        'beneficiary_info',
        'is_featured',
        'is_urgent',
        'allow_anonymous_donations',
        'minimum_donation',
        'view_count',
        'share_count',
        'admin_notes',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'goal_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
        'gallery' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'beneficiary_info' => 'array',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'allow_anonymous_donations' => 'boolean',
        'minimum_donation' => 'integer',
        'view_count' => 'integer',
        'share_count' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(CampaignUpdate::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(CampaignMedia::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    // Accessors & Mutators
    public function getProgressPercentageAttribute(): float
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }

        return min(($this->raised_amount / $this->goal_amount) * 100, 100);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max($this->goal_amount - $this->raised_amount, 0);
    }

    public function getDonationCountAttribute(): int
    {
        return $this->donations()->where('status', 'completed')->count();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->raised_amount >= $this->goal_amount;
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }
}
