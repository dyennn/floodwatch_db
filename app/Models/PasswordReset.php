<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PasswordReset
 *
 * This model represents a password reset request in the application.
 * It includes fields for storing the email, token, and expiration times
 * for both the reset code and the reset token.
 *
 * @package App\Models
 */
class PasswordReset extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'token',
        'reset_password_code',
        'reset_password_code_expires_at',
        'reset_password_token',
        'reset_password_token_expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'reset_password_code_expires_at' => 'datetime',
        'reset_password_token_expires_at' => 'datetime',
    ];
}