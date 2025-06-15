<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_name',
        'registration_number',
        'tax_number',
        'mission_statement',
        'founded_year',
        'website',
        'address',
        'contact_persons',
        'bank_details',
        'verification_documents',
        'verification_status',
        'verification_notes',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'founded_year' => 'integer',
        'contact_persons' => 'array',
        'bank_details' => 'encrypted:array',
        'verification_documents' => 'array',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }
}
