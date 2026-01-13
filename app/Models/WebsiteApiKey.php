<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'website_name',
        'api_key',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
