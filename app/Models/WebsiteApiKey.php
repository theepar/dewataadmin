<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteApiKey extends Model
{
    protected $fillable = [
        'website_name',
        'api_key',
        'user_id',
    ];

    // Relasi ke User (optional)
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}