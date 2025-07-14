<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsLetter extends Model
{
    protected $fillable = [
        'email',
        'is_subscribed',
        'subscribed_at',
        'unsubscribed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_subscribed' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];
}
