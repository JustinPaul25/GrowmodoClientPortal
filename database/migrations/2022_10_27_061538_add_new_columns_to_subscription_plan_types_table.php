<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToSubscriptionPlanTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plan_types', function (Blueprint $table) {
            //
            $table->string('value')->nullable(); //: 1,
            $table->string('billed_label')->nullable(); //: 'Billed monthly',
            $table->string('breakdown_label')->nullable(); //: '/ month',
            $table->boolean('savings_percentage')->default(true); //: true,
            $table->boolean('savings_per_month')->default(false); //: false,
            $table->string('savings_label')->default('Savings'); //: 'Savings',
            $table->string('savings_currency')->default('USD'); //: 'USD',
            $table->string('savings_currency_legend')->nullable('$'); //: '$',
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plan_types', function (Blueprint $table) {
            //
            $table->dropColumn([
                'value',
                'billed_label',
                'breakdown_label',
                'savings_percentage',
                'savings_per_month',
                'savings_label',
                'savings_currency',
                'savings_currency_legend',
            ]);
        });
    }
}
