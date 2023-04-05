<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTurnarounddaysOnProjectTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('project_types', function (Blueprint $table) {
            $table->integer('turn_around_days_from')->default(0);
            $table->integer('turn_around_days_to')->default(0);
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

        Schema::table('project_types', function (Blueprint $table) {
            $table->dropColumn([
                'turn_around_days_from', 'turn_around_days_to'
            ]);
        });
    }
}
