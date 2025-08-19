<?php

namespace App\Helpers;

use App\Models\Setting;
use phpDocumentor\Reflection\Types\Self_;

class Settings
{
    /**
     * Retrieve a setting value from the database with type casting.
     *
     * Looks up a record in the `settings` table by its key and returns
     * its value casted to the specified type stored in the DB.
     *
     * Supported types:
     *  - boolean / bool   → Returns true/false
     *  - integer / int    → Returns integer value
     *  - float / double   → Returns float value
     *  - array            → Returns array (comma-separated values)
     *  - json             → Returns associative array (decoded from JSON)
     *  - string (default) → Returns raw string value
     *
     * Example usage:
     *   Settings::get('site_name', 'My Website');
     *   Settings::get('maintenance_mode', false);
     *
     * @param  string  $key      Setting key name
     * @param  mixed   $default  Value to return if key is not found
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        // Find the setting by its key
        $setting = Setting::where('key', $key)->first();

        // Return default value if setting does not exist
        if (!$setting) {
            return $default;
        }

        // Type cast the value based on $setting->type
        switch ($setting->type) {
            case Setting::TYPE_BOOLEAN:
            case Setting::TYPE_BOOL:
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);

            case Setting::TYPE_INT:
            case Setting::TYPE_INTEGER:
                return (int) $setting->value;

            case Setting::TYPE_FLOAT:
            case Setting::TYPE_DOUBLE:
                return (float) $setting->value;

            case Setting::TYPE_ARRAY:
                // Stored as comma-separated string, convert to array
                return explode(',', $setting->value);

            case Setting::TYPE_JSON:
                // Decode JSON into an associative array
                return json_decode($setting->value, true);

            case Setting::TYPE_STRING:
            default:
                // Default is raw string
                return $setting->value;
        }

        // fallback in case type casting is somehow missed
        return $setting ? $setting->value : $default;
    }

    /**
     * Create or update a setting value in the database.
     *
     * If the key exists, update its value/type/group.
     * If not found, create a new record.
     *
     * @param  string  $key    The setting key name
     * @param  mixed   $value  The value to store
     * @param  string  $type   The setting type (e.g., 'string', 'int', 'boolean', etc.)
     * @param  string  $group  The group/category name (default: 'general')
     * @return \App\Models\Setting
     */
    public static function set($key, $value, $type = Setting::TYPE_STRING, $group = Setting::DEFAULT_GROUP)
    {
        // Find existing setting or prepare a new one
        $setting = Setting::firstOrNew(['key' => $key]);

        // Assign the provided values
        $setting->value = $value;
        $setting->type = $type;
        $setting->group = $group;

        // Save to the database
        $setting->save();

        return $setting;
    }
}
