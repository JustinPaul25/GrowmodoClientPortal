<?php

namespace Database\Seeders;

use App\Models\BrandCategory;
use Illuminate\Database\Seeder;

class BrandCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        BrandCategory::truncate();

        BrandCategory::insert([
            [
                'label' => 'Personal',
                'color' => '#6941C6',
                'bg_color' => '#F9F5FF'
            ],
            [
                'label' => 'Product',
                'color' => '#B42318',
                'bg_color' => '#FEF3F2'
            ],
            [
                'label' => 'Service',
                'color' => '#B54708',
                'bg_color' => '#FFFAEB'
            ],
            [
                'label' => 'Corporate',
                'color' => '#027A48',
                'bg_color' => '#ECFDF3'
            ],
            [
                'label' => 'Startup',
                'color' => '#026AA2',
                'bg_color' => '#F0F9FF'
            ],
            [
                'label' => 'NGO',
                'color' => '#363F72',
                'bg_color' => '#F8F9FC'
            ],
            [
                'label' => 'Government',
                'color' => '#3538CD',
                'bg_color' => '#EEF4FF'
            ]
        ]);
    }
}
