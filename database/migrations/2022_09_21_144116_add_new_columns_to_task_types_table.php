<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToTaskTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_types', function (Blueprint $table) {
            //
            $table->integer('turn_around_days')->default(0);
            // $table->renameColumn('title', 'label');

            // turnAroundDays: 1, // Number we use starts to display it
            // platforms: ['Hotjar', 'Mouseflow', 'Smartlook'], // No prefered the first on the array is the default prefered
            // description: 'Set up my funnel and website analytics tool to track user behavior and conversions.',
            // tags: [1], // bale yung category dati is sa tags na array sya of ids ng mga category from task directory categories  (ito gamit sa filtering sa category)

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_types', function (Blueprint $table) {
            //
            $table->dropColumn('turn_around_days');
            // $table->renameColumn('label', 'title');
        });
    }
}
