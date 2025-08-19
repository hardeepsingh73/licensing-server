<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    use SoftDeletes;

    const EVENT_CREATED  = 'created';
    const EVENT_UPDATED  = 'updated';
    const EVENT_DELETED  = 'deleted';
    const EVENT_RESTORED = 'restored';

    /**
     * The attributes that are mass assignable.
     *
     * These fields can be filled via create() / update().
     * 'properties' is generally used for additional data (JSON/Array).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event',         // e.g. 'created', 'updated', 'deleted'
        'description',   // Human-readable description of the activity
        'subject_id',    // ID of the model that was affected
        'subject_type',  // Fully-qualified class name of affected model
        'causer_id',     // ID of the user/system that caused the action
        'causer_type',   // Fully-qualified class name of causer (usually User)
        'properties',    // Additional meta data (stored as JSON)
        'ip_address',    // IP from which action was triggered
        'user_agent',    // Browser/device info
        'url',           // URL where the action occurred
        'method',        // HTTP method (GET, POST, etc.)
    ];

    /**
     * Attribute casting.
     *
     * This ensures 'properties' stored as JSON in DB
     * is automatically converted to array when accessed.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the related "subject" model of the activity.
     *
     * A subject is the actual model on which the activity was performed
     * (e.g., Post, Comment, Product).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get the related "causer" model of the activity.
     *
     * A causer is the model that initiated the activity
     * (typically a User, but could also be a System or API client).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Filter logs specifically for a given subject model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model    $subject
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubject($query, $subject)
    {
        return $query->where('subject_id', $subject->id)
            ->where('subject_type', get_class($subject));
    }

    /**
     * Scope: Filter logs specifically for a given causer model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model    $causer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCauser($query, $causer)
    {
        return $query->where('causer_id', $causer->id)
            ->where('causer_type', get_class($causer));
    }
}
