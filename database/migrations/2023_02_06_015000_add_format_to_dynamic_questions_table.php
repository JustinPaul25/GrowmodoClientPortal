<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormatToDynamicQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dynamic_questions', function (Blueprint $table) {
            $table->string('format')->nullable()->after('type');
            $table->string('alternative_format')->nullable()->after('alternative_type');
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
