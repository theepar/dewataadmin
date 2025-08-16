<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;

class WebApiKeySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();
        if ($admin) {
            DB::table('web_api_keys')->insert([
                'user_id'      => $admin->id,
                'website_name' => 'nextjs',
                'key'          => Str::uuid(), // atau Str::random(32)
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
