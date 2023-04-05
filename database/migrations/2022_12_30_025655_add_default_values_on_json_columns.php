<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultValuesOnJsonColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->json('brand_colors')->default('{}')->change();
            $table->json('social_accounts')->default('{}')->change();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->json('social_accounts')->default('{}')->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->json('dynamic_questions')->default('{}')->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->json('dynamic_questions')->default('{}')->change();
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
    }
}
