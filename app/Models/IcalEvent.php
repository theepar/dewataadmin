<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcalEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'ical_link_id',
        'uid',
        'summary',
        'description',
        'start_date',
        'end_date',
        'status',
        'guest_name',
        'reservation_id',
        'is_cancelled',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_cancelled' => 'boolean',
    ];

    public function icalLink(): BelongsTo
    {
        return $this->belongsTo(IcalLink::class);
    }
}
