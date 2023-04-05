<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMultipleColumnsInTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('project_id');
            $table->dropColumn('organization_id');
            $table->dropColumn('description');
            $table->dropColumn('video_walkthrough');
            $table->dropColumn('resources');
            $table->dropColumn('target_audience');
            $table->dropColumn('priority');
            $table->dropColumn('status');
            $table->dropColumn('asana_gid');
            $table->dropColumn('warnings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            //
        });
    }
}
