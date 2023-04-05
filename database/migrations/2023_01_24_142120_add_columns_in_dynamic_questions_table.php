<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInDynamicQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dynamic_questions', function (Blueprint $table) {
            $table->string('placeholder')->nullable();
            $table->string('alternative_if')->nullable();
            $table->string('alternative_question')->nullable();
            $table->string('alternative_placeholder')->nullable();
            $table->string('alternative_type')->nullable();
            $table->json('alternative_options')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dynamic_questions', function (Blueprint $table) {
            //
        });
    }
}
