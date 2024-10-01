<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DistrictType;

class DistricTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(DistrictType $district_type): void
    {
        
        $district_type->create([
            'type' => 'assembly',
        ]);

        $district_type->create([
            'type' => 'senate',
        ]);

        $district_type->create([
            'type' => 'council',
        ]);

    }
}
