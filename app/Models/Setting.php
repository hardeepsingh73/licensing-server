<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    // type Constants
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_ARRAY = 'array';
    const TYPE_JSON = 'json';
    const TYPE_IMAGE = 'image';

    /**
     * type options with their labels
     */
    public static $types = [
        self::TYPE_STRING => 'string',
        self::TYPE_BOOLEAN => 'boolean',
        self::TYPE_INTEGER => 'integer',
        self::TYPE_FLOAT => 'float',
        self::TYPE_ARRAY => 'array',
        self::TYPE_JSON => 'json',
        self::TYPE_IMAGE => 'image',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * These columns can be updated in bulk using methods like create(), update(), or fill().
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',         // The unique setting key (e.g., "site_name", "maintenance_mode")
        'value',       // The stored value (raw string; casting happens externally if needed)
        'type',        // Data type of the value (e.g., 'string', 'boolean', 'json', 'image')
        'group',       // Logical grouping/category of the setting (e.g., "general", "email")
        'description', // Human-readable description of the setting
    ];

    /**
     * Retrieve the value of a specific setting key.
     *
     * If the setting does not exist, the provided $default value will be returned.
     * This is a quick-access helper without casting logic — use App\Helpers\Settings for typed values.
     *
     * @param  string  $key      The unique key identifying the setting.
     * @param  mixed   $default  The default value if the setting is not found.
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Create or update a setting’s value by key.
     *
     * If a setting with the given key exists, it will be updated.
     * If it doesn't exist, a new record will be created.
     * Type, group, and description are not automatically updated here — only 'value'.
     *
     * @param  string  $key    The unique key.
     * @param  mixed   $value  The value to store (should be string/serialised if complex).
     * @return \App\Models\Setting
     */
    public static function setValue(string $key, $value): Setting
    {
        return self::updateOrCreate(
            ['key' => $key], // Search criteria
            ['value' => $value] // Values to insert/update
        );
    }
}
