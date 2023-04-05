<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id')->nullable();
            $table->bigInteger('organization_id')->nullable();
            $table->string('title', 255);
            $table->longText('description')->nullable();
            $table->text('video_walkthrough')->nullable();
            $table->text('resources')->nullable();
            $table->text('target_audience')->nullable();

            $table->string('priority', 255)->nullable();
            $table->string('status', 255)->nullable();

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
        Schema::dropIfExists('tasks');
    }
}
