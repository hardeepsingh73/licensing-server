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

    public function getStatusBadgeClassAttribute()
    {
        dd($this->status);
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
    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
