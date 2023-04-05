<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMultipleColumnInProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('organization_id');
            $table->dropColumn('brand_name');
            $table->dropColumn('current_website');
            $table->dropColumn('project_summary');
            $table->dropColumn('business_description');
            $table->dropColumn('ideal_customer_profile');
            $table->dropColumn('competitors');
            $table->dropColumn('value_proposition');
            $table->dropColumn('customer_problems');
            $table->dropColumn('product_service_features');
            $table->dropColumn('product_service_benefits');
            $table->dropColumn('website_objective');
            $table->dropColumn('web_design_inspiration');
            $table->dropColumn('disliked_designs');
            $table->dropColumn('graphic_files');
            $table->dropColumn('sitemap');
            $table->dropColumn('wireframes');
            $table->dropColumn('content_management_system');
            $table->dropColumn('third_party_tools');
            $table->dropColumn('asana_gid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            //
        });
    }
}
