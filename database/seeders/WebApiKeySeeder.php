<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class WebApiKeySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();
        if ($admin) {
            DB::table('web_api_keys')->insert([
                'user_id' => $admin->id,
                'website_name' => 'nextjs',
                'key' => '01d3a112b1d13607ef488f25e722e3f8879aaef9fbc0d3c4',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
