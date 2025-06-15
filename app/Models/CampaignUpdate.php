<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'title',
        'content',
        'images',
        'notify_donors',
        'published_at',
    ];

    protected $casts = [
        'images' => 'array',
        'notify_donors' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
} 