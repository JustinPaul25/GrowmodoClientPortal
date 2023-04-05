<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionTalent;
use App\Models\SubscriptionPlanType;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SubscriptionPlan::truncate();
        $types = SubscriptionPlanType::get();
        $talents = SubscriptionTalent::get();

        $product_id = [
            '1' => [
                '1' => 'price_1MRszWBGxhpCJs2lSVIgN1sA',
                '2' => 'price_1MRt2LBGxhpCJs2lMs191OAq',
                '3' => 'price_1MRt39BGxhpCJs2lgg8N7Lr7',
                '4' => 'price_1MRt3qBGxhpCJs2l4DLJsl9S',
            ],
            '2' => [
                '1' => 'price_1MRt71BGxhpCJs2l6yHnuVUN',
                '2' => 'price_1MRt71BGxhpCJs2lbTSBmLvA',
                '3' => 'price_1MRt71BGxhpCJs2lAY2SAdrJ',
                '4' => 'price_1MRt71BGxhpCJs2l2RnlatGS',
            ],
            '3' => [
                '1' => 'price_1MRt9ZBGxhpCJs2llwBeyLBA',
                '2' => 'price_1MRt9ZBGxhpCJs2lrdfpuNEk',
                '3' => 'price_1MRt9ZBGxhpCJs2lZi1pzmuu',
                '4' => 'price_1MRt9ZBGxhpCJs2lHDP9wMfN',
            ],
            '4' => [
                '1' => 'price_1MRtC8BGxhpCJs2lANmT6Z4v',
                '2' => 'price_1MRtC8BGxhpCJs2lntyiqp8v',
                '3' => 'price_1MRtC8BGxhpCJs2l7LUp70l7',
                '4' => 'price_1MRtC8BGxhpCJs2l12NqwWPd',
            ],
            '5' => [
                '1' => 'price_1MRtEVBGxhpCJs2lEDI6OYbK',
                '2' => 'price_1MRtEVBGxhpCJs2lGFNd8Em5',
                '3' => 'price_1MRtEVBGxhpCJs2la3fEob2q',
                '4' => 'price_1MRtEVBGxhpCJs2lkuPAAuUn',
            ]
        ];

        foreach ($talents as $talent) {
            if ($talent->label === "More") {
                SubscriptionPlan::create([
                    'label' => $talent->label,
                    'description' => 'For fast-moving agencies, marketing teams & scale-ups who need access to reliable on-demand design & dev talents to move even faster.',
                    'price_currency' => '$',
                    'price_per_month' => 1,
                ]);
            } else {
                foreach ($types as $type) {
                    SubscriptionPlan::create([
                        'label' => $talent->label,
                        'description' => 'For fast-moving agencies, marketing teams & scale-ups who need access to reliable on-demand design & dev talents to move even faster.',
                        'price_currency' => '$',
                        'price_per_month' => '100',
                        'plan_type_id' => $type->id,
                        'talent_id' => $talent->id,
                        'product_id' => $product_id[$talent->id][$type->id],
                    ]);
                }
            }
        }
    }
}
