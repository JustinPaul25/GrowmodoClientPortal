<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryInProjectTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_types', function (Blueprint $table) {
            $table->dropColumn('tags');
            $table->dropColumn('keywords');
            $table->dropColumn('project_type');
            $table->dropColumn('status');
            $table->dropColumn('filter');
            $table->string('category')->nullable()->after('description');
            $table->string('platforms')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_types', function (Blueprint $table) {
            //
        });
    }
}
