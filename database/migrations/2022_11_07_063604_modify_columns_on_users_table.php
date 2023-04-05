<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('users', function (Blueprint $table) {
            //
            // $table->string('username')->nullable()->change();
            $table->string('username')->nullable()->change();
            $table->string('signup_token')->nullable();
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
        Schema::table('users', function (Blueprint $table) {
            //
            // $table->string('username')->nullable(false)->change();
            $table->dropColumns([
                'signup_token'
            ]);
        });
    }
}
