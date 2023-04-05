<?php

namespace Database\Seeders;

use App\Models\EmployeeCount;
use Illuminate\Database\Seeder;

class EmployeeCountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        EmployeeCount::truncate();

        $empCount = [
            [
                "label" => 'Me, myself & I',
            ],
            [
                "label" => '2-10 Employees',
            ],
            [
                "label" => '11-20 Employees',
            ],
            [
                "label" => '21-50 Employees',
            ],
            [
                "label" => '51-200 Employees',
            ],
            [
                "label" => '201-1000 Employees',
            ],
            [
                "label" => '1000+ Employees',
            ],
        ];

        foreach ($empCount as $st => $count) {
            EmployeeCount::create( $count );
        }
    }
}
