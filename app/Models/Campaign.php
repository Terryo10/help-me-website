<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

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

    // Relationships
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

    public function raised_amount_count()
    {
        return $this->donations()->where('status', 'completed')->sum('amount');
    }

    public function goal_percentage()
    {
        return $this->raised_amount_count() / $this->goal_amount * 100;
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

    public function scopePublished($query)
    {
        return $query->whereIn('status', ['active', 'completed']);
    }

    public function scopeByCategory($query, $categorySlug)
    {
        return $query->whereHas('categories', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', '%' . $location . '%');
    }

    public function scopeEndingSoon($query, $days = 7)
    {
        return $query->where('end_date', '<=', now()->addDays($days))
                    ->where('end_date', '>=', now())
                    ->where('status', 'active');
    }

    public function scopeRecentlyCreated($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSearchByKeyword($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('description', 'like', '%' . $keyword . '%')
              ->orWhere('story', 'like', '%' . $keyword . '%');
        });
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

    public function getUniqueDonorsCountAttribute(): int
    {
        return $this->donations()
                    ->where('status', 'completed')
                    ->distinct('user_id')
                    ->count('user_id');
    }

    public function getAverageDonationAttribute(): float
    {
        $completedDonations = $this->donations()->where('status', 'completed');
        $count = $completedDonations->count();

        if ($count === 0) {
            return 0;
        }

        return $completedDonations->sum('amount') / $count;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        $remaining = now()->diffInDays($this->end_date, false);
        return $remaining >= 0 ? $remaining : 0;
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        return Storage::disk('public')->url($this->featured_image);
    }

    public function getGalleryUrlsAttribute(): array
    {
        if (!$this->gallery || !is_array($this->gallery)) {
            return [];
        }

        return array_map(function ($path) {
            return Storage::disk('public')->url($path);
        }, $this->gallery);
    }

    public function getShareUrlAttribute(): string
    {
        return route('campaigns.show', $this->slug);
    }

    public function getSocialShareDataAttribute(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->featured_image_url,
            'url' => $this->share_url,
        ];
    }

    public function getBeneficiaryNameAttribute(): ?string
    {
        return $this->beneficiary_info['name'] ?? null;
    }

    public function getBeneficiaryRelationshipAttribute(): ?string
    {
        return $this->beneficiary_info['relationship'] ?? null;
    }

    public function getBeneficiaryAgeAttribute(): ?int
    {
        return $this->beneficiary_info['age'] ?? null;
    }

    public function getBeneficiaryContactAttribute(): ?string
    {
        return $this->beneficiary_info['contact'] ?? null;
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->raised_amount >= $this->goal_amount || $this->status === 'completed';
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canAcceptDonations(): bool
    {
        return $this->isActive() && !$this->isExpired() && !$this->isCompleted();
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id && in_array($this->status, ['draft', 'rejected']);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function incrementShareCount(): void
    {
        $this->increment('share_count');
    }

    public function addDonation(float $amount): void
    {
        $this->increment('raised_amount', $amount);

        // Auto-complete campaign if goal is reached
        if ($this->raised_amount >= $this->goal_amount && $this->status === 'active') {
            $this->update(['status' => 'completed']);
        }
    }

    public function removeDonation(float $amount): void
    {
        $this->decrement('raised_amount', $amount);

        // Reactivate campaign if it was completed and now below goal
        if ($this->raised_amount < $this->goal_amount && $this->status === 'completed') {
            $this->update(['status' => 'active']);
        }
    }

    public function approve(User $approver): void
    {
        $this->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $approver->id,
        ]);
    }

    public function reject(string $reason = null): void
    {
        $updateData = ['status' => 'rejected'];

        if ($reason) {
            $updateData['admin_notes'] = $reason;
        }

        $this->update($updateData);
    }

    public function suspend(string $reason = null): void
    {
        $updateData = ['status' => 'suspended'];

        if ($reason) {
            $updateData['admin_notes'] = $reason;
        }

        $this->update($updateData);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'active' => 'success',
            'paused' => 'info',
            'completed' => 'primary',
            'suspended' => 'danger',
            'rejected' => 'dark',
            default => 'secondary',
        };
    }

    public function getStatusDisplayNameAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'paused' => 'Paused',
            'completed' => 'Completed',
            'suspended' => 'Suspended',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    // Static Methods
    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'paused' => 'Paused',
            'completed' => 'Completed',
            'suspended' => 'Suspended',
            'rejected' => 'Rejected',
        ];
    }

    public static function getCurrencyOptions(): array
    {
        return [
            'USD' => 'US Dollar ($)',
            'ZWL' => 'Zimbabwean Dollar (Z$)',
        ];
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($campaign) {
            if (!$campaign->start_date) {
                $campaign->start_date = now();
            }
        });

        static::deleting(function ($campaign) {
            // Delete associated media files
            if ($campaign->featured_image) {
                Storage::disk('public')->delete($campaign->featured_image);
            }

            if ($campaign->gallery && is_array($campaign->gallery)) {
                foreach ($campaign->gallery as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        });
    }
}
