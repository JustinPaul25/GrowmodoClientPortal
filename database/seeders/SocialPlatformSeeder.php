<?php

namespace Database\Seeders;

use App\Models\SocialMediaPlatform;
use Illuminate\Database\Seeder;

class SocialPlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        SocialMediaPlatform::truncate();

        SocialMediaPlatform::insert([
            [
                'platform' => "linkedin",
                'base_url' => "www.linkedin.com/company",
                'icon' => "linkedin",
            ],
            [
                'platform' => "facebook",
                'base_url' => "www.facebook.com",
                'icon' => "facebook",
            ],
            [
                'platform' => "twitter",
                'base_url' => "www.twitter.com",
                'icon' => "twitter",
            ],
            [
                'platform' => "instagram",
                'base_url' => "www.instagram.com",
                'icon' => "instagram",
            ],
        ]);
    }
}
