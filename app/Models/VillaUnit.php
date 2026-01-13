<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillaUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'villa_id',
        'unit_number',
        'ical_link',
    ];

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function villaUnit()
    {
        return $this->belongsTo(\App\Models\VillaUnit::class, 'villa_unit_id');
    }

    public function icalEvents()
    {
        return $this->hasMany(\App\Models\IcalEvent::class, 'villa_unit_id');
    }
}
