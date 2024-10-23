<?php

/**
 * User Model
 *
 * This model represents a user in the application. It extends the Authenticatable class
 * provided by Laravel and implements MustVerifyEmail and CanResetPassword interfaces.
 * It uses several traits to provide additional functionality.
 *
 * Traits:
 * - HasApiTokens: Provides API token authentication.
 * - HasFactory: Enables the use of model factories.
 * - Notifiable: Allows the user to receive notifications.
 *
 * Properties:
 * - $fillable: An array of attributes that are mass assignable.
 * - $hidden: An array of attributes that should be hidden for arrays.
 *
 * Methods:
 * - casts(): Defines the attribute casting for the model.
 * - profile(): Defines a one-to-one relationship with the UserProfile model.
 * - verification(): Defines a one-to-one relationship with the UserVerification model.
 *
 * @package App\Models
 * @namespace App\Models
 * @extends Illuminate\Foundation\Auth\User
 * @implements Illuminate\Contracts\Auth\MustVerifyEmail
 * @implements Illuminate\Contracts\Auth\CanResetPassword
 */

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\CanResetPassword;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function verification()
    {
        return $this->hasOne(UserVerification::class);
    }
}