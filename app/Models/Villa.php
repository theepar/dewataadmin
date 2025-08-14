<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Villa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ownership_status',
        'price_idr',
        'description',
        'location',
        'bedroom',
        'bed',
        'bathroom',
        'guest',         // Tambahkan field guest
        'amenities',
        // tambahkan field lain jika ada
    ];

    protected $casts = [
        'amenities'        => 'array',
        'ownership_status' => 'array',
    ];

    public function media()
    {
        return $this->hasMany(\App\Models\VillaMedia::class, 'villa_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'villa_user');
    }

    public function units()
    {
        return $this->hasMany(\App\Models\VillaUnit::class, 'villa_id');
    }
}
