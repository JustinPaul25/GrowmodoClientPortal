<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('organization_id');
            $table->string('brand_name');
            $table->text('current_website')->nullable();
            $table->longText('project_summary')->nullable();
            $table->longText('business_description')->nullable();
            $table->longText('ideal_customer_profile')->nullable();
            $table->text('competitors')->nullable();
            $table->text('value_proposition')->nullable();
            $table->longText('customer_problems')->nullable();
            $table->longText('product_service_features')->nullable();
            $table->longText('product_service_benefits')->nullable();

            $table->longText('website_objective');
            $table->longText('web_design_inspiration');
            $table->longText('disliked_designs');
            $table->longText('graphic_files');
            $table->longText('sitemap');
            $table->text('wireframes');
            $table->text('content_management_system');
            $table->text('third_party_tools');


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
        Schema::dropIfExists('projects');
    }
}
