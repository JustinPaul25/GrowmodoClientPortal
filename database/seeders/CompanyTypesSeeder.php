<?php

namespace Database\Seeders;

use App\Models\CompanyType;
use Illuminate\Database\Seeder;

class CompanyTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        CompanyType::truncate();

        CompanyType::insert([
            [ 'label' => 'Agency' ],
            [ 'label' => 'SaaS' ],
            [ 'label' => 'Service-Based Business' ],
            [ 'label' => 'E-commerce' ],
            [ 'label' => 'Venture Capital' ],
            [ 'label' => 'Others' ],
        ]);
    }
}
