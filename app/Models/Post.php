<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItianProfile;


class Post extends Model
{

    protected $fillable = [
        'itian_id', 'title', 'content','image', 'tags','visibility', 'views_count', 'likes_count', 'is_published'
    ];

    public function itian()
    {
        return $this->belongsTo(ItianProfile::class, 'itian_id', 'itian_profile_id');
    }

    public function reactions()
{
    return $this->hasMany(PostReaction::class);
}

}
