<?php

namespace Database\Seeders;

use App\Models\Option;
use Illuminate\Database\Seeder;

class OptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Option::truncate();

        Option::insert( [
            [
                'option_name' => 'growmodo_site',
                'option_value' => 'https://www.growmodo.com/',
                'public' => true,
            ],
            [
                'option_name' => 'help_link',
                'option_value' => 'https://growmodo.helpkit.so/growmodo/hSJAVvZpApN9LerBNa9fs9',
                'public' => true,
            ],
            [
                'option_name' => 'book_strategy_call_link',
                'option_value' => 'https://calendly.com/growmodo/growmodo-strategy-call',
                'public' => true,
            ],
            [
                'option_name' => 'asana_api_access_token',
                'option_value' => '1/5695158974463:f614b29900585a2f950f1876750a2422',
                'public' => false,
            ],
            [
                'option_name' => 'asana_api_default_team_id',
                'option_value' => '1200512683000379',
                'public' => false,
            ],
        ] );
    }
}
