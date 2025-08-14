<?php

namespace Database\Seeders;

use App\Models\Villa;
use App\Models\VillaUnit;
use Illuminate\Database\Seeder;

class VillaSeeder extends Seeder
{
    public function run(): void
    {
        $villas = [
            [
                'name'             => 'Villa 101',
                'ownership_status' => ['Monthly', 'Freehold'],
                'price_idr'        => 55000000,
                'description'      => 'Escape to Villa 101, a stylish sanctuary offering the perfect blend of modern luxury and tropical serenity. Located in the vibrant heart of Canggu.',
                'location'         => 'https://maps.app.goo.gl/hwpf4arb6dvejJPd6',
                'bedroom'          => 1,
                'bed'              => 1,
                'bathroom'         => 1,
                'guest'            => 2, // Tambahkan guest di sini
                'amenities'        => [
                    // Bathroom
                    ['name' => 'Bathroom', 'available' => true],
                    ['name' => 'Bath', 'available' => true],
                    ['name' => 'Hair dryer', 'available' => true],
                    ['name' => 'Cleaning products', 'available' => true],
                    ['name' => 'Shampoo', 'available' => true],
                    ['name' => 'Conditioner', 'available' => true],
                    ['name' => 'Bidet', 'available' => true],
                    ['name' => 'Hot water', 'available' => true],
                    ['name' => 'Shower gel', 'available' => true],

                    // Bedroom and laundry
                    ['name' => 'Washing machine', 'available' => true],
                    ['name' => 'Dryer', 'available' => true],
                    ['name' => 'Hangers', 'available' => true],
                    ['name' => 'Bed linen', 'available' => true],
                    ['name' => 'Room-darkening blinds', 'available' => true],
                    ['name' => 'Clothes drying rack', 'available' => true],
                    ['name' => 'Clothes storage', 'available' => true],

                    // Entertainment
                    ['name' => 'TV', 'available' => true],

                    // Heating and cooling
                    ['name' => 'Air conditioning', 'available' => true],

                    // Home safety
                    ['name' => 'First aid kit', 'available' => true],

                    // Internet and office
                    ['name' => 'Wifi', 'available' => true],
                    ['name' => 'Dedicated workspace', 'available' => true],

                    // Kitchen and dining
                    ['name' => 'Kitchen', 'available' => true],
                    ['name' => 'Space where guests can cook their own meals', 'available' => true],
                    ['name' => 'Fridge', 'available' => true],
                    ['name' => 'Microwave', 'available' => true],
                    ['name' => 'Cooking basics', 'available' => true],
                    ['name' => 'Pots and pans, oil, salt and pepper', 'available' => true],
                    ['name' => 'Dishes and cutlery', 'available' => true],
                    ['name' => 'Bowls, chopsticks, plates, cups, etc.', 'available' => true],
                    ['name' => 'Freezer', 'available' => true],
                    ['name' => 'Cooker', 'available' => true],
                    ['name' => 'Oven', 'available' => true],
                    ['name' => 'Kettle', 'available' => true],
                    ['name' => 'Wine glasses', 'available' => true],
                    ['name' => 'Blender', 'available' => true],
                    ['name' => 'Dining table', 'available' => true],

                    // Location features
                    ['name' => 'Private entrance', 'available' => true],
                    ['name' => 'Separate street or building entrance', 'available' => true],

                    // Outdoor
                    ['name' => 'Patio or balcony', 'available' => true],
                    ['name' => 'Garden', 'available' => true],
                    ['name' => 'An open space on the property usually covered in grass', 'available' => true],
                    ['name' => 'Outdoor furniture', 'available' => true],
                    ['name' => 'Outdoor dining area', 'available' => true],
                    ['name' => 'Sun loungers', 'available' => true],

                    // Parking and facilities
                    ['name' => 'Free parking on premises', 'available' => true],
                    ['name' => 'Free on-street parking', 'available' => true],
                    ['name' => 'Pool', 'available' => true],
                    ['name' => 'Hot tub', 'available' => true],

                    // Services
                    ['name' => 'Long-term stays allowed', 'available' => true],
                    ['name' => 'Allow stays of 28 days or more', 'available' => true],
                    ['name' => 'Self check-in', 'available' => true],
                    ['name' => 'Keypad', 'available' => true],
                    ['name' => 'Check yourself in to the home with a door code', 'available' => true],
                    ['name' => 'Cleaning available during stay', 'available' => true],

                    // Not included
                    ['name' => 'Exterior security cameras on property', 'available' => false],
                    ['name' => 'Essentials', 'available' => false],
                    ['name' => 'Smoke alarm', 'available' => false],
                    ['name' => 'Carbon monoxide alarm', 'available' => false],
                    ['name' => 'Heating', 'available' => false],
                ],
                'unit_count'       => 1, // Tambahkan jumlah unit di sini
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => 'Doni\'s Room',
                'ownership_status' => ['Freehold'],
                'price_idr'        => 1353346,
                'description'      => 'Discover the epitome of tranquility and luxury in the heart of Canggu. Our cozy, comfortable, and elegantly designed bedrooms offer a perfect blend of modern style and Balinese charm. Nestled in a quiet area. Large windows invite natural light and offer picturesque views of lush greenery, creating an ambiance of calm and relaxation. bedrooms promise an unforgettable experience that will make you want to return again and again. Ideal for both relaxation and as an investment in your well-being.',
                'location'         => 'https://maps.app.goo.gl/c2iNVAQ7NuNNJq4X7',
                'bedroom'          => 1,
                'bed'              => 1,
                'bathroom'         => 1,
                'guest'            => 2, // Tambahkan guest di sini
                'amenities'        => [
                    // Bathroom
                    ['name' => 'Bathroom', 'available' => true],
                    ['name' => 'Cleaning products', 'available' => true],
                    ['name' => 'Bidet', 'available' => true],
                    ['name' => 'Hot water', 'available' => true],
                    ['name' => 'Shower gel', 'available' => true],

                    // Bedroom and laundry
                    ['name' => 'Hangers', 'available' => true],
                    ['name' => 'Bed linen', 'available' => true],
                    ['name' => 'Cotton linen', 'available' => true],
                    ['name' => 'Extra pillows and blankets', 'available' => true],
                    ['name' => 'Room-darkening blinds', 'available' => true],
                    ['name' => 'Safe', 'available' => true],
                    ['name' => 'Clothes storage: wardrobe', 'available' => true],

                    // Entertainment
                    ['name' => 'TV', 'available' => true],

                    // Heating and cooling
                    ['name' => 'Air conditioning', 'available' => true],

                    // Home safety
                    ['name' => 'Exterior security cameras on property', 'available' => true],
                    ['name' => 'side of property', 'available' => true],
                    ['name' => 'First aid kit', 'available' => true],

                    // Internet and office
                    ['name' => 'Wifi', 'available' => true],
                    ['name' => 'Dedicated workspace', 'available' => true],

                    // Kitchen and dining
                    ['name' => 'Kitchen', 'available' => true],
                    ['name' => 'Space where guests can cook their own meals', 'available' => true],
                    ['name' => 'Fridge', 'available' => true],
                    ['name' => 'Cooking basics', 'available' => true],
                    ['name' => 'Pots and pans, oil, salt and pepper', 'available' => true],
                    ['name' => 'Dishes and cutlery', 'available' => true],
                    ['name' => 'Bowls, chopsticks, plates, cups, etc.', 'available' => true],
                    ['name' => 'Mini fridge', 'available' => true],
                    ['name' => 'Other stainless steel gas cooker', 'available' => true],
                    ['name' => 'Kettle', 'available' => true],
                    ['name' => 'Toaster', 'available' => true],
                    ['name' => 'Blender', 'available' => true],
                    ['name' => 'Waste compactor', 'available' => true],
                    ['name' => 'Dining table', 'available' => true],
                    ['name' => 'Coffee', 'available' => true],

                    // Location features
                    ['name' => 'Private entrance', 'available' => true],
                    ['name' => 'Separate street or building entrance', 'available' => true],

                    // Outdoor
                    ['name' => 'Outdoor furniture', 'available' => true],
                    ['name' => 'Outdoor dining area', 'available' => true],

                    // Parking and facilities
                    ['name' => 'Free parking on premises', 'available' => true],

                    // Services
                    ['name' => 'Luggage drop-off allowed', 'available' => true],
                    ['name' => 'For guests\' convenience when they are arriving early or departing late', 'available' => true],
                    ['name' => 'Self check-in', 'available' => true],
                    ['name' => 'Lockbox', 'available' => true],
                    ['name' => 'Housekeeping available every day', 'available' => true],

                    // Not included
                    ['name' => 'Washing machine', 'available' => false],
                    ['name' => 'Dryer', 'available' => false],
                    ['name' => 'Essentials', 'available' => false],
                    ['name' => 'Smoke alarm', 'available' => false],
                    ['name' => 'Carbon monoxide alarm', 'available' => false],
                    ['name' => 'Heating', 'available' => false],
                ],
                'unit_count'       => 5,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => 'Dony\'s Cabin Canggu',
                'ownership_status' => ['Freehold'],
                'price_idr'        => 1680463,
                'description'      => 'Experience serene living in this ensuite 1-bedroom apartment in the heart of Canggu. Overlooking lush rice fields, it offers a peaceful escape with a touch of Bali’s natural beauty. Fully serviced for your comfort, the apartment features a private bathroom, modern furnishings, AC, and high-speed WiFi. Perfect for digital nomads or couples seeking both tranquility and convenience near cafes, beaches, and trendy spots.',
                'location'         => 'https://maps.app.goo.gl/CpyEMmv12wRTn4Zr6',
                'bedroom'          => 1,
                'bed'              => 1,
                'bathroom'         => 1,
                'guest'            => 2, // Tambahkan guest di sini
                'amenities'        => [
                    // Bathroom
                    ['name' => 'Bathroom', 'available' => true],
                    ['name' => 'Bath', 'available' => true],
                    ['name' => 'Cleaning products', 'available' => true],
                    ['name' => 'Bidet', 'available' => true],
                    ['name' => 'Hot water', 'available' => true],

                    // Bedroom and laundry
                    ['name' => 'Dryer', 'available' => true],
                    ['name' => 'Hangers', 'available' => true],
                    ['name' => 'Bed linen', 'available' => true],
                    ['name' => 'Room-darkening blinds', 'available' => true],
                    ['name' => 'Clothes drying rack', 'available' => true],
                    ['name' => 'Clothes storage: wardrobe', 'available' => true],

                    // Entertainment
                    ['name' => 'TV', 'available' => true],

                    // Heating and cooling
                    ['name' => 'Air conditioning', 'available' => true],
                    ['name' => 'Ceiling fan', 'available' => true],

                    // Home safety
                    ['name' => 'Exterior security cameras on property', 'available' => true],
                    ['name' => 'First aid kit', 'available' => true],

                    // Internet and office
                    ['name' => 'Wifi', 'available' => true],
                    ['name' => 'Dedicated workspace', 'available' => true],

                    // Kitchen and dining
                    ['name' => 'Kitchen', 'available' => true],
                    ['name' => 'Space where guests can cook their own meals', 'available' => true],
                    ['name' => 'Fridge', 'available' => true],
                    ['name' => 'Microwave', 'available' => true],
                    ['name' => 'Dishes and cutlery', 'available' => true],
                    ['name' => 'Bowls, chopsticks, plates, cups, etc.', 'available' => true],
                    ['name' => 'Freezer', 'available' => true],
                    ['name' => 'Gas cooker', 'available' => true],
                    ['name' => 'Kettle', 'available' => true],
                    ['name' => 'Blender', 'available' => true],
                    ['name' => 'Dining table', 'available' => true],

                    // Location features
                    ['name' => 'Private entrance', 'available' => true],
                    ['name' => 'Separate street or building entrance', 'available' => true],

                    // Outdoor
                    ['name' => 'Private patio or balcony', 'available' => true],
                    ['name' => 'Outdoor dining area', 'available' => true],

                    // Parking and facilities
                    ['name' => 'Free parking on premises', 'available' => true],
                    ['name' => 'Hot tub', 'available' => true],

                    // Services
                    ['name' => 'Housekeeping available 2 days a week', 'available' => true],
                    ['name' => 'Host greets you', 'available' => true],

                    // Not included
                    ['name' => 'Washing machine', 'available' => false],
                    ['name' => 'Essentials', 'available' => false],
                    ['name' => 'Smoke alarm', 'available' => false],
                    ['name' => 'Carbon monoxide alarm', 'available' => false],
                    ['name' => 'Heating', 'available' => false],
                ],
                'unit_count'       => 3,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        foreach ($villas as $villa) {
            $unitCount = $villa['unit_count'];
            unset($villa['unit_count']);
            $createdVilla = Villa::create($villa);

            // Buat unit sesuai jumlah yang diinginkan
            for ($i = 1; $i <= $unitCount; $i++) {
                VillaUnit::create([
                    'villa_id'    => $createdVilla->id,
                    'unit_number' => (string) $i,
                    'ical_link'   => null,
                ]);
            }
        }
    }
}
