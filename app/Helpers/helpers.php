<?php

use App\Helpers\RoleDataHelper;
use App\Helpers\Settings;

if (!function_exists('setting')) {
    /**
     * Global helper function to retrieve application settings.
     *
     * This function acts as a shortcut for fetching configuration values
     * stored by the application (via App\Helpers\Settings).
     *
     * Example:
     *   setting('site_name')           → returns the 'site_name' setting value
     *   setting('timezone', 'UTC')     → returns the setting or 'UTC' if not found
     *
     * @param string $key      The setting key/name to retrieve.
     * @param mixed  $default  The default value to return if the key doesn't exist.
     *
     * @return mixed           The setting value or the default if not found.
     */
    function setting($key, $default = null)
    {
        // Call the Settings helper to get the specified setting value.
        return Settings::get($key, $default);
    }
}
if (!function_exists('consthelper')) {
    /**
     * Get a constant or static property from any class
     *
     * @param string $key Format: "ClassName::CONST_NAME" or "ClassName::$propertyName"
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function consthelper(string $key, $default = null)
    {
        // Split Class and Key (e.g., "User::STATUS_ACTIVE" or "User::$statuses")
        $parts = explode('::', $key);

        // Default to App\Models if no namespace specified
        if (count($parts) === 1) {
            $className = 'App\\Models\\' . $parts[0];
            $keyName = $parts[0];
        } else {
            $className = (strpos($parts[0], '\\') === false)
                ? 'App\\Models\\' . $parts[0]
                : $parts[0];
            $keyName = $parts[1];
        }

        // Check if class exists
        if (!class_exists($className)) {
            return $default;
        }

        // Handle static properties (e.g., "$statuses")
        if (str_starts_with($keyName, '$')) {
            $propertyName = substr($keyName, 1); // Remove "$" prefix
            return $className::${$propertyName};
        }

        // Handle constants (e.g., "STATUS_ACTIVE")
        $reflection = new ReflectionClass($className);
        $constants = $reflection->getConstants();
        return $constants[$keyName] ?? $default;
    }
}
