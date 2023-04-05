<?php

namespace Database\Seeders;

use App\Models\SubscriptionTalent;
use Illuminate\Database\Seeder;

class SubTalentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        SubscriptionTalent::truncate();

        $subTalents = [
            [
                "label" => '1 Talent',
                "value" => 1,
            ],
            [
                "label" => '2 Talents',
                "value" => 2,
            ],
            [
                "label" => '3 Talents',
                "value" => 3,
            ],
            [
                "label" => '4 Talents',
                "value" => 4,
            ],
            [
                "label" => '5 Talents',
                "value" => 5,
            ],
            [
                "label" => 'More',
                "value" => -1,
            ]
        ];

        foreach ($subTalents as $st => $talent) {
            SubscriptionTalent::create( $talent );
        }
    }
}
