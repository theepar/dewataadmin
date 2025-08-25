<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = [
        'user_id',
        'fcm_token',
        'device_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
