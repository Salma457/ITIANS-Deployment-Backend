<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\ResetPasswordMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\ItianProfile;
use App\Models\EmployerProfile;

class User extends Authenticatable
{

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login',
        'role',

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
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function itianProfile()
    {
        return $this->hasOne(ItianProfile::class, 'user_id');
    }
    public function jobs()
    {
        return $this->hasMany(Job::class, 'employer_id');
    }
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function employerProfile()
    {
        return $this->hasOne(EmployerProfile::class, 'user_id');
    }
    public function sendPasswordResetNotification($token)
    {
        $url = url(config('app.frontend_url') . "/reset-password?token={$token}&email=" . $this->email);

        \Illuminate\Support\Facades\Mail::to($this->email)->send(new ResetPasswordMail($token, $this->email));
    }
        public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

}
