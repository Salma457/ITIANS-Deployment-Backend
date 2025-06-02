<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
<<<<<<< HEAD
=======
use Laravel\Sanctum\HasApiTokens;
>>>>>>> c588cab7d0999f7d8a8cea67d862cb04a0803038

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
<<<<<<< HEAD
    use HasFactory, Notifiable;
=======
    use HasFactory, Notifiable, HasApiTokens;
>>>>>>> c588cab7d0999f7d8a8cea67d862cb04a0803038

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
<<<<<<< HEAD
=======
        'last_login',
        'role',
>>>>>>> c588cab7d0999f7d8a8cea67d862cb04a0803038
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
<<<<<<< HEAD
            'email_verified_at' => 'datetime',
=======
            'last_login' => 'datetime',
>>>>>>> c588cab7d0999f7d8a8cea67d862cb04a0803038
            'password' => 'hashed',
        ];
    }
}
