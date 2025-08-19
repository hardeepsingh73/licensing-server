<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{

    protected $fillable = ['license_key_id', 'device_id', 'ip_address', 'user_agent'];

    public function licenseKey()
    {
        return $this->belongsTo(LicenseKey::class);
    }
}
