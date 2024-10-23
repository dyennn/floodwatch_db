<?php

/**
 * Class UserVerification
 *
 * This model represents the user verification process in the application.
 * It is used to store and manage the verification codes sent to users for email verification.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property string $verification_code
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserVerification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVerification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVerification query()
 *
 * @mixin \Eloquent
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'verification_code',
        'email_verified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}