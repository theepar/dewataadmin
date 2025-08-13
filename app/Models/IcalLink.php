<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcalLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'villa_id',
        'ical_url',
        'last_synced_at',
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
}
