<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillaMedia extends Model
{
    protected $fillable = [
        'villa_id',
        'file_path',
        'file_name',
        'type',
        'is_cover',
    ];

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
