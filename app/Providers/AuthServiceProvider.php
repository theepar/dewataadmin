<?php
namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// << TAMBAHKAN INI

class AuthServiceProvider extends ServiceProvider
{
    // ... (properti $policies biarkan saja)

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        User::created(function (User $user) {
            $user->assignRole('pegawai');
        });
    }
}
