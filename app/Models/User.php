<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Env;
use Tymon\JWTAuth\Contracts\JWTSubject; // <-- Ensure this is imported

class User extends Authenticatable implements JWTSubject // <-- Implement JWTSubject interface
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the JWT token.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get custom claims for the JWT token.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $frontendUrl = env('FRONTEND_URL');
        $url = $frontendUrl . '/reset-password/confirm?token=' . $token . '&email=' . urlencode($this->email);
        $this->notify(new ResetPasswordNotification($url));
    }
    
}
