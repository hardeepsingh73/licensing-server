<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicenseActivation extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = ['license_key_id', 'device_id', 'ip_address', 'user_agent'];

    public function licenseKey()
    {
        return $this->belongsTo(LicenseKey::class);
    }
}
