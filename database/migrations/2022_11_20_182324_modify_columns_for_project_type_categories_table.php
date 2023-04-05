<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsForProjectTypeCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('project_type_categories', function (Blueprint $table) {
            $table->string('filter')->nullable()->change();
            $table->string('icon')->nullable()->change();
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
        Schema::table('project_type_categories', function (Blueprint $table) {
            // $table->timestamp('deleted_at')->nullable();
        });
    }
}
