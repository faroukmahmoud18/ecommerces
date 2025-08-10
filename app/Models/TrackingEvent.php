
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'event_code',
        'event_description',
        'event_location',
        'event_date',
        'event_time',
        'raw_data',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Get the shipment that owns the tracking event.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Scope a query to order by event date (newest first).
     */
    public function scopeOrderedByDate($query)
    {
        return $query->orderBy('event_date', 'desc')->orderBy('event_time', 'desc');
    }

    /**
     * Get the full event date and time.
     */
    public function getFullEventDateTimeAttribute()
    {
        if ($this->event_date && $this->event_time) {
            return $this->event_date->format('Y-m-d') . ' ' . $this->event_time->format('H:i:s');
        } elseif ($this->event_date) {
            return $this->event_date->format('Y-m-d');
        } elseif ($this->event_time) {
            return $this->event_time->format('Y-m-d H:i:s');
        }

        return null;
    }

    /**
     * Get the location and description formatted for display.
     */
    public function getLocationAndDescriptionAttribute()
    {
        $parts = [];

        if ($this->event_location) {
            $parts[] = $this->event_location;
        }

        if ($this->event_description) {
            $parts[] = $this->event_description;
        }

        return implode(', ', $parts);
    }
}
