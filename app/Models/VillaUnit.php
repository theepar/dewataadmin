<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VillaUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'villa_id',
        'unit_number',
        'ical_link',
    ];

    public function villa()
    {
        return $this->belongsTo(\App\Models\Villa::class, 'villa_id');
    }
}
