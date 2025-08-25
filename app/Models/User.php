<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 *
 * Represents an application user with:
 * - Authentication capabilities (extends Authenticatable).
 * - Spatie's role & permission management functionality.
 * - API token handling via Laravel Sanctum.
 * - Soft delete functionality (keeps deleted users in DB with a deleted_at timestamp).
 * - Activity logging via custom LogsActivity trait.
 *
 * @package App\Models
 *
 * @property int                             $id                The primary key of the user.
 * @property string                          $name              The user's full name.
 * @property string                          $email             The user's email address (unique).
 * @property string|null                     $password          The user's hashed password.
 * @property \Carbon\Carbon|null             $email_verified_at The datetime when the email was verified.
 * @property string|null                     $remember_token    Token for "remember me" login.
 * @property \Carbon\Carbon|null             $created_at        Timestamp when user was created.
 * @property \Carbon\Carbon|null             $updated_at        Timestamp when user was last updated.
 * @property \Carbon\Carbon|null             $deleted_at        Timestamp when user was soft deleted.
 */
class User extends Authenticatable implements JWTSubject
{
    // Automatically logs model events such as create, update, and delete.
    use LogsActivity;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    // Sends notifications (mail, database, etc.) to the user.
    use Notifiable;

    // Adds role and permission handling from Spatie package.
    use HasRoles;

    // Allows soft deletes so users can be restored later if deleted.
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays and JSON serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be type cast.
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Casts to Carbon datetime object
            'password'          => 'hashed',   // Automatically hashes password
        ];
    }

    /**
     * Get the login history records for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function loginHistory()
    {
        // Defines one-to-many relationship: One user -> many login history records
        return $this->hasMany(LoginHistory::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}
