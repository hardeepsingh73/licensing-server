<?php

namespace App\Traits;

use App\Helpers\Settings;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait LogsActivity
{
    /**
     * Laravel automatically boots traits prefixed with "boot".
     * This method registers model event listeners for activity logging.
     */
    protected static function bootLogsActivity()
    {
        // Subscribe to events: created, updated, deleted, restored on the model
        foreach (static::getModelEvents() as $event) {
            // Register listener for each event
            static::$event(function (Model $model) use ($event) {
                $model->logActivity($event);
            });
        }
    }

    /**
     * Fetch the list of Eloquent model events to listen for.
     *
     * @return array<string>
     */
    protected static function getModelEvents()
    {
        return ['created', 'updated', 'deleted', 'restored'];
    }

    /**
     * Log activity to ActivityLog model for the given event.
     *
     * @param string $event The type of event (created, updated, deleted, etc.)
     * @return void
     */
    public function logActivity($event)
    {
        // Check app setting if activity logs are enabled
        if (!Settings::get('activity_logs', true)) {
            return;
        }

        // Create human-readable description of the event
        $description = $this->getActivityDescription($event);

        // Create a new activity log entry with detailed context
        ActivityLog::create([
            'event'         => $event,
            'description'   => $description,
            'subject_id'    => $this->id,
            'subject_type'  => get_class($this),
            'causer_id'     => auth()->id(),
            'causer_type'   => auth()->user() ? get_class(auth()->user()) : null,
            'properties'    => $this->getActivityProperties($event),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
            'url'        => app()->runningInConsole() ? null : request()->fullUrl(),
            'method'     => app()->runningInConsole() ? null : request()->method(),
        ]);
    }

    /**
     * Generate a descriptive string for the activity log.
     *
     * @param string $event
     * @return string
     */
    protected function getActivityDescription($event)
    {
        $modelName = class_basename($this);

        return match ($event) {
            'created' => "Created {$modelName}",
            'updated' => "Updated {$modelName}",
            'deleted' => "Deleted {$modelName}",
            'restored' => "Restored {$modelName}",
            default => "Performed {$event} on {$modelName}",
        };
    }

    /**
     * Retrieve properties to save as part of the activity log.
     * For 'updated', logs changed attributes and original values.
     * For 'created' and 'deleted', logs full attributes.
     *
     * @param string $event
     * @return array<mixed>
     */
    protected function getActivityProperties($event)
    {
        $properties = [];

        if ($event === ActivityLog::EVENT_UPDATED) {
            // Show old and new attribute values excluding hidden
            $properties = [
                'old'        => Arr::except($this->getOriginal(), $this->getHidden()),
                'attributes' => Arr::except($this->getChanges(), $this->getHidden()),
            ];
        } elseif ($event === ActivityLog::EVENT_CREATED || $event === ActivityLog::EVENT_DELETED) {
            // Show all current attributes excluding hidden ones
            $properties = [
                'attributes' => Arr::except($this->getAttributes(), $this->getHidden()),
            ];
        }

        return $properties;
    }
}
