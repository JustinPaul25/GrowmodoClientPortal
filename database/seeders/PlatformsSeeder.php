<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PlatformsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Platform::truncate();

        $json = File::get("database/data/platforms.json");

        $data = json_decode($json, true);

        foreach ($data as $d => $pl) {
            unset($pl['id']);
            Platform::create($pl);
        }
    }
}
