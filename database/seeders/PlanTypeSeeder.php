<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlanType;
use Illuminate\Database\Seeder;

class PlanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        SubscriptionPlanType::truncate();

        $subPlanTypes = [
            [
                "label" => 'Monthly',
                "value" => 1,
                "billed_label" => 'Billed monthly',
                "breakdown_label" => '/ month',
                "savings_percentage" => true,
                "savings_per_month" => false,
                "savings_label" => 'Savings',
                "savings_currency" => 'USD',
                'interval' => 1,
                'interval_type' => 'month',
            ],
            [
                "label" => '3 Months',
                "value" => 3,
                "billed_label" => 'Billed every 3 months',
                "breakdown_label" => '/ 3 months',
                "savings" => 0.047,
                "savings_percentage" => true,
                "savings_per_month" => false,
                "savings_label" => 'Savings',
                "savings_currency" => 'USD',
                'interval' => 3,
                'interval_type' => 'month',
            ],
            [
                "label" => '6 Months',
                "value" => 6,
                "billed_label" => 'Billed every 6 months',
                "breakdown_label" => '/ 6 months',
                "savings" => 0.093,
                "savings_percentage" => true,
                "savings_per_month" => false,
                "savings_label" => 'Savings',
                "savings_currency" => 'USD',
                'interval' => 6,
                'interval_type' => 'month',
            ],
            [
                "label" => '12 Months',
                "value" => 12,
                "billed_label" => 'Billed annually',
                "breakdown_label" => '/ year',
                "savings" => 0.116,
                "savings_percentage" => true,
                "savings_per_month" => false,
                "savings_label" => 'Savings',
                "savings_currency" => 'USD',
                'interval' => 12,
                'interval_type' => 'month',
            ],
        ];

        foreach ($subPlanTypes as $spt => $type) {
            SubscriptionPlanType::create( $type );
        }
    }
}
