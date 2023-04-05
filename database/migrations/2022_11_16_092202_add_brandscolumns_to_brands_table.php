<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandscolumnsToBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->bigInteger('brand_type');
            $table->text('brand_icon')->nullable();
            $table->text('brand_images')->nullable();
            $table->text('graphic_elements')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            //
            $table->dropColumn([
                'brand_type', 'brand_icon', 'brand_images', 'graphic_elements'
            ]);
        });
    }
}
