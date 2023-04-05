<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsForSubscriptionPlanTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('subscription_plan_types', function (Blueprint $table) {
            $table->float('savings')->nullable();
            $table->integer('interval')->default(1);
            $table->string('interval_type')->default('month');
            // $table->string('status')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('subscription_plan_types', function (Blueprint $table) {
            $table->dropColumn([
                'savings', 'interval', 'interval_type',
                // 'status'
            ]);
        });
    }
}
