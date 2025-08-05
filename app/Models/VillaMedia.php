<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillaMedia extends Model
{
    protected $table = 'villa_media';

    protected $fillable = [
        'villa_id',
        'file_path',
        'file_name',
        'type',
    ];

    public function villa()
    {
        return $this->belongsTo(Villa::class, 'villa_id');
    }
}
