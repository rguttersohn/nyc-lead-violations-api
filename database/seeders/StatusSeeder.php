<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;
use Illuminate\Support\Facades\File;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Status $status): void
    {
        $status_file = json_decode(File::get('database/seeders/json/violation-status.json'));

        foreach($status_file as $data){
            
            $status->create([
                'currentstatusid' => $data->currentstatusid,
                'name' => $data->name,
                'definition' => $data->definition ?: null
            ]);
        }
    }
}
