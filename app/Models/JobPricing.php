<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPricing extends Model
{
    protected $table = 'job_pricing';
    protected $fillable = ['price'];
}
