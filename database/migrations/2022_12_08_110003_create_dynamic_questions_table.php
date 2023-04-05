<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('questionable_id')->nullable();
            $table->string('questionable_type');
            $table->text('title');
            $table->text('question');
            $table->string('type');
            $table->longText('options');
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
        Schema::dropIfExists('dynamic_questions');
    }
}
