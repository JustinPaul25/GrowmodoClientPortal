<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsTaskTypeCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('task_type_categories', function(Blueprint $table) {
            // $table->renameColumn('title', 'label');
            $table->string('filter')->nullable();
            $table->string('icon')->nullable();
            // label: 'UX Design',
            // filter: 'ux-design',
            // icon: 'puzzle-piece', // please check untitle ui icons
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
        Schema::table('task_type_categories', function (Blueprint $table) {
            //
            // $table->renameColumn('label', 'title');
            $table->dropColumn([
                'filter', 'icon'
            ]);
        });
    }
}
