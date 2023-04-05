<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('subscription_plan_id');
            $table->unsignedBigInteger('subscription_plan_type_id');
            $table->timestamp('subscription_start')->default(now());
            $table->timestamp('subscription_renewed')->default(now());
            $table->timestamp('subscription_end')->default(now());

            // subscriptionId: 1, // id of the plan
            // subscriptionType: 1, // id of subscription type
            // subscription_start: '2022-08-11T08:00:20.000000Z',
            // subscription_renewed: '2022-09-10T08:00:20.000000Z',
            // subscription_end: '2022-10-10T08:00:20.000000Z',

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
