<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicenseKey extends Model
{
    use SoftDeletes, LogsActivity;

    // Status Constants
    const STATUS_ACTIVE = 1;
    const STATUS_REVOKED = 2;
    const STATUS_EXPIRED = 3;

    /**
     * Status options with their labels
     */
    public static $statuses = [
        self::STATUS_ACTIVE => 'Active',
        // self::STATUS_REVOKED => 'Revoked',
        self::STATUS_EXPIRED => 'Expired',
    ];
    protected $fillable = ['key', 'status', 'activation_limit', 'activations', 'expires_at'];

    protected $casts = [
        'expires_at' => 'date'
    ];

    public function activations()
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

    /**
     * Check if LicenseKey is active
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }
}
