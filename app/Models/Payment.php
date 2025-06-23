<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'stripe_session_id',
    'stripe_payment_intent_id',
    'amount',
    'currency',
    'used_for_job',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

