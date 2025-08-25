<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class LicenseKey extends Authenticatable implements JWTSubject
{
    use SoftDeletes, LogsActivity;

    // Status Constants
    const STATUS_ACTIVE = 1;
    const STATUS_REISSUE = 2;
    const STATUS_EXPIRED = 3;

    /**
     * Status options with their labels
     */
    public static $statuses = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_REISSUE => 'Reissued',
        self::STATUS_EXPIRED => 'Expired',
    ];

    protected $fillable = ['user_id', 'key', 'status', 'activation_limit', 'activations', 'expires_at'];

    protected $casts = [
        'expires_at' => 'date',
        'status' => 'integer',
    ];

    public function devices()
    {
        return $this->hasMany(LicenseActivation::class);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return self::$statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            self::STATUS_ACTIVE  => 'bg-success',
            self::STATUS_REISSUE => 'bg-danger',
            self::STATUS_EXPIRED => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Check if LicenseKey is active
     */
    public function getIsActiveAttribute()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the identifier that will be stored in the JWT subject claim.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        $deviceCount = $this->devices ? $this->devices()->count() : 0;
        return [
            'license_key' => $this->key,
            'activation_limit' => $this->activation_limit,
            'activated_devices' => $deviceCount + 1,
            'exp' => now()->addDays(7)->timestamp
        ];
    }
}
