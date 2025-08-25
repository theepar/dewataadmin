<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class IcalEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'villa_unit_id',
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

    public function villaUnit()
    {
        return $this->belongsTo(\App\Models\VillaUnit::class, 'villa_unit_id');
    }

    protected static function booted()
    {
        static::created(function ($event) {
            $villaUnit = \App\Models\VillaUnit::find($event->villa_unit_id);
            if (!$villaUnit) return;

            $villaId = $villaUnit->villa_id;
            $accessUserIds = DB::table('villa_user')
                ->where('villa_id', $villaId)
                ->pluck('user_id')
                ->toArray();

            $admins = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            $users = \App\Models\User::whereIn('id', $accessUserIds)->get();
            $usersToNotify = $admins->merge($users)->unique('id');

            \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Notifications\NewBookingNotification($event));
        });
    }
}
