<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleDriveFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_drive_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('google_drive_folder_id')->nullable();
            $table->unsignedBigInteger('organization_id');
            $table->string('file_id');
            $table->string('name');
            $table->integer('file_size');
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
        Schema::dropIfExists('google_drive_files');
    }
}
