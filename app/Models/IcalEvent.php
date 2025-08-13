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
        'property_name',
        'jumlah_orang',
        'durasi',
        'is_cancelled',
    ];

    protected $casts = [
        'start_date'   => 'datetime',
        'end_date'     => 'datetime',
        'is_cancelled' => 'boolean',
    ];

    public function villa()
    {
        return $this->belongsTo(\App\Models\Villa::class, 'villa_id');
    }

    public function villaUnit()
    {
        return $this->belongsTo(\App\Models\VillaUnit::class, 'villa_unit_id');
    }

    public function icalLink()
    {
        return $this->belongsTo(\App\Models\IcalLink::class, 'ical_link_id');
    }
}
