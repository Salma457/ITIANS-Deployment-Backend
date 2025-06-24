<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItianProfile;
use App\Models\EmployerProfile;


class PostReaction extends Model
{
    protected $fillable = ['post_id', 'user_id', 'reaction_type'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function itianProfile()
{
    return $this->hasOne(ItianProfile::class, 'user_id', 'user_id');
}

public function employerProfile()
{
    return $this->hasOne(EmployerProfile::class, 'user_id', 'user_id');
}

}
