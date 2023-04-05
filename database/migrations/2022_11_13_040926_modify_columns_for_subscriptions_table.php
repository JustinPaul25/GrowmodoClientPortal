<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsForSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_plan_id')->mullable()->change();
            $table->unsignedBigInteger('subscription_talent_id');
            $table->string('status')->default('pending');
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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_talent_id',
                'status'
            ]);
        });
    }
}
