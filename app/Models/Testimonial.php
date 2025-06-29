<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email', 
        'role',
        'message',
        'rating',
        'status'
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    // Scope to get only approved testimonials
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope to get only pending testimonials
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}