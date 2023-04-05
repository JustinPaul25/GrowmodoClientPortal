<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIsMaintenanceToSubsplantype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plan_types', function (Blueprint $table) {
            $table->dropColumn([
                'is_maintenance'
            ]);
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
            $table->dropColumn([
                'is_maintenance'
            ]);
        });
    }
}
