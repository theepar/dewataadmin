<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VillaUnitSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('villa_units')->insertOrIgnore([
            [
                'id' => 1,
                'villa_id' => 1,
                'unit_number' => '1',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1482214950166891676.ics?s=4fbd800ae308852babd10469d721dfbb&locale=en-GB',
                'last_synced_at' => '2025-08-20 14:48:46',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-20 06:48:46',
            ],
            [
                'id' => 2,
                'villa_id' => 2,
                'unit_number' => '1',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1220305582930381978.ics?s=2846232d5a72a96b88a9017b34432ca7&locale=en-GB',
                'last_synced_at' => '2025-08-16 19:26:12',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-16 11:26:12',
            ],
            [
                'id' => 3,
                'villa_id' => 2,
                'unit_number' => '2',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1220299809888896689.ics?s=058771291c042a8ad9e9706d32324b5e&locale=en-GB',
                'last_synced_at' => '2025-08-16 19:27:35',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-16 11:27:35',
            ],
            [
                'id' => 4,
                'villa_id' => 2,
                'unit_number' => '3',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1220267643729683376.ics?s=cf3479e7ccfee88fb90b7eff272bfe23&locale=en-GB',
                'last_synced_at' => '2025-08-16 19:27:37',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-16 11:27:37',
            ],
            [
                'id' => 5,
                'villa_id' => 2,
                'unit_number' => '4',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1220291393760663085.ics?s=a4f4263437d092784ca9bebf9330a70d&locale=en-GB',
                'last_synced_at' => '2025-08-16 19:27:40',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-16 11:27:40',
            ],
            [
                'id' => 6,
                'villa_id' => 2,
                'unit_number' => '5',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1220247176077794104.ics?s=ad54ee4877543e0343274256b4cc92a7&locale=en-GB',
                'last_synced_at' => '2025-08-16 19:27:42',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-16 11:27:42',
            ],
            [
                'id' => 7,
                'villa_id' => 3,
                'unit_number' => '1',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1418481554335110082.ics?s=e418501f3870197f7dbbd8220ef7234d&locale=en-GB',
                'last_synced_at' => '2025-08-16 19:27:45',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-16 11:27:45',
            ],
            [
                'id' => 8,
                'villa_id' => 3,
                'unit_number' => '2',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1336253486851252708.ics?s=31c620bdc2a78b207ff1dea77b6da8ea&locale=en-GB',
                'last_synced_at' => '2025-08-16 19:28:02',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-16 11:28:02',
            ],
            [
                'id' => 9,
                'villa_id' => 3,
                'unit_number' => '3',
                'ical_link' => 'https://www.airbnb.com/calendar/ical/1232921672443077290.ics?s=97f8d39d1e619b22b6d6b75e0c78fdc9&locale=en-GB',
                'last_synced_at' => '2025-08-16 19:27:55',
                'created_at' => '2025-08-16 11:01:49',
                'updated_at' => '2025-08-16 11:27:55',
            ],
        ]);
    }
}
