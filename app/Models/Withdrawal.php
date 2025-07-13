<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'phone_number',
        'bank_name',
        'account_number',
        'branch_code',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
