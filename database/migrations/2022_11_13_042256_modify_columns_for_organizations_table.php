<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsForOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('organizations', function (Blueprint $table) {
            $table->renameColumn('hm_employees', 'employee_count_id');
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
        Schema::table('organizations', function (Blueprint $table) {
            $table->renameColumn('employee_count_id', 'hm_employees');
        });

    }
}
