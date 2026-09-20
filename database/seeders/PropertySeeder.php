<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /** @return array<int, Property> */
    public function run(User $owner): array
    {
        $definitions = [
            ['address_line1' => '12 Maple Court', 'address_line2' => 'Flat 3', 'city' => 'Bristol', 'postcode' => 'BS1 4ST', 'property_type' => 'residential_flat', 'bedrooms' => 2],
            ['address_line1' => '45 Oak Avenue', 'address_line2' => 'Flat 1', 'city' => 'Bristol', 'postcode' => 'BS2 9LM', 'property_type' => 'residential_flat', 'bedrooms' => 2],
            ['address_line1' => '7 Willow House', 'address_line2' => 'Flat 5', 'city' => 'Bath', 'postcode' => 'BA1 2QF', 'property_type' => 'residential_flat', 'bedrooms' => 3],
            ['address_line1' => '18 Cedar Court', 'address_line2' => 'Flat 2', 'city' => 'Bath', 'postcode' => 'BA2 6RT', 'property_type' => 'residential_flat', 'bedrooms' => 2],
            ['address_line1' => '3 Birch Mansions', 'address_line2' => 'Flat 9', 'city' => 'Bristol', 'postcode' => 'BS8 1AA', 'property_type' => 'residential_flat', 'bedrooms' => 3],
            ['address_line1' => '22 Elm Grove', 'address_line2' => null, 'city' => 'Bristol', 'postcode' => 'BS7 8HN', 'property_type' => 'residential_house', 'bedrooms' => 3],
            ['address_line1' => '9 Ashford Road', 'address_line2' => null, 'city' => 'Bath', 'postcode' => 'BA1 5PW', 'property_type' => 'residential_house', 'bedrooms' => 3],
            ['address_line1' => 'Unit 4, Kingswood Business Park', 'address_line2' => null, 'city' => 'Bristol', 'postcode' => 'BS15 2NX', 'property_type' => 'commercial_unit', 'floor_area_sqm' => 85.00],
        ];

        $properties = [];

        foreach ($definitions as $def) {
            $properties[] = Property::create([
                ...$def,
                'owner_id' => $owner->id,
                'notes' => 'Access via keypad; code shared with contractors on assignment. Parking: 1 allocated space.',
            ]);
        }

        return $properties;
    }
}
