<?php

namespace Database\Seeders;

use App\Models\PtCat;
use App\Models\Platform;
use App\Models\ProjectType;
use Illuminate\Support\Str;
use App\Models\DynamicQuestion;
use Illuminate\Database\Seeder;

use App\Models\DynamicQuestionable;
use App\Models\PlatformProjectType;
use App\Models\ProjectTypeCategory;
use Illuminate\Support\Facades\File;

class ProjectDirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ////
        ProjectTypeCategory::truncate();
        ProjectType::truncate();
        PlatformProjectType::truncate();
        PtCat::truncate();

        DynamicQuestionable::where('dynamic_questionable_type', 'App\Models\ProjectType')->delete();

        $projectTypeCategories = [
            // "UX Design",
            // "Graphic Design",
            // "Development",
            // "Maintenance",
            // "SEO",
            // "Hosting",
            // "Automation",
            // "No-Code",
            // "Conversion",
            // "Advice",

            "websites" => [
                "label" => "Websites",
                "tag" => "websites",
                "icon" => "icon-browser"
            ],
            "funnels" => [
                "label" => "Funnels",
                "tag" => "funnels",
                "icon" => "icon-filter-funnel-02"
            ],
            "marketplace" => [
                "label" => "Marketplace",
                "tag" => "marketplace",
                "icon" => "icon-shopping-cart-03"
            ],
            "portal" => [
                "label" => "Portal",
                "tag" => "portal",
                "icon" => "icon-cube-01"
            ],
            "directory-site" => [
                "label" => "Directory Site",
                "tag" => "directory-site",
                "icon" => "icon-rows-03"
            ],
            "no-code-apps" => [
                "label" => "No-code Apps",
                "tag" => "no-code-apps",
                "icon" => "icon-phone-02"
            ]
        ];

        foreach ($projectTypeCategories as $ptc => $ptcat) {
            ProjectTypeCategory::insert([
                'title' => $ptcat['label'],
                'filter' => $ptcat['tag'], //strtolower($ptcat),
                'slug' => $ptcat['tag'], //Str::slug($ptcat),
                'icon' => $ptcat['icon'],
            ]);
        }



        // $projectTypes = array(
        //     // array("val0"=>"label","val1"=>"type","val2"=>"category","val3"=>"platforms","val4"=>"description","val5"=>"tags","val6"=>"icon","keywords"=>"keywords")
        //     array("label" => "Business Website", "type" => "Template Design", "category" => "Websites", "platforms" => "Elementor,Webflow,Wordpress", "description" => "Build a new website based on a template and customize it according to my brand guidelines.", "tags" => "websites", "icon" => "icon-briefcase-01", "keywords" => ""),
        //     array("label" => "Business Website", "type" => "Custom Design", "category" => "Websites", "platforms" => "Elementor,Webflow", "description" => "Create a custom design concept for my website and then develop it on my preferred platform.", "tags" => "websites", "icon" => "icon-briefcase-01", "keywords" => ""),
        //     array("label" => "Ecommerce Website", "type" => "Template Design", "category" => "Websites", "platforms" => "Elementor,Webflow,Wordpress", "description" => "Build a new website based on a template and customize it according to my brand guidelines.", "tags" => "websites", "icon" => "icon-shopping-cart-03", "keywords" => ""),
        //     array("label" => "Ecommerce Website", "type" => "Custom Design", "category" => "Websites", "platforms" => "Elementor,Webflow", "description" => "Create a custom design concept for my website and then develop it on my preferred platform.", "tags" => "websites", "icon" => "icon-shopping-cart-03", "keywords" => ""),
        //     array("label" => "Sales Funnel", "type" => "Template Design", "category" => "Funnels", "platforms" => "Elementor,Webflow,Wordpress", "description" => "Build a new sales funnel with multiple landing pages on templates and customize it according to my brand guidelines.", "tags" => "funnels", "icon" => "icon-filter-funnel-02", "keywords" => ""),
        //     array("label" => "Sales Funnel", "type" => "Custom Design", "category" => "Funnels", "platforms" => "Elementor,Webflow", "description" => "Create a custom design concept for my sales funeel pages and then develop it on my preferred platform.", "tags" => "funnels", "icon" => "icon-filter-funnel-02", "keywords" => ""),
        //     array("label" => "Marketplace Site", "type" => "Template Design", "category" => "Marketplace", "platforms" => "Elementor,Webflow,Wordpress", "description" => "Build a multi-vendor marketplace based on templates and customize it according to my brand guidelines.", "tags" => "marketplace", "icon" => "icon-home-smile", "keywords" => ""),
        //     array("label" => "Marketplace Site", "type" => "Custom Design", "category" => "Marketplace", "platforms" => "Elementor,Webflow", "description" => "Create a custom design concept for my new multi-vendor marketplace and then develop it on my preferred platform.", "tags" => "marketplace", "icon" => "icon-home-smile", "keywords" => ""),
        //     array("label" => "Membership Portal", "type" => "Template Design", "category" => "Portal", "platforms" => "Elementor,Webflow,Wordpress", "description" => "Build a membership portal based on templates and customize it according to my brand guidelines.", "tags" => "portal", "icon" => "icon-cube-01", "keywords" => ""),
        //     array("label" => "Membership Portal", "type" => "Custom Design", "category" => "Portal", "platforms" => "Elementor,Webflow", "description" => "Create a custom design concept for my new membership portal and then develop it on my preferred platform.", "tags" => "portal", "icon" => "icon-cube-01", "keywords" => ""),
        //     array("label" => "Client Portal", "type" => "Template Design", "category" => "Portal", "platforms" => "Elementor,Webflow,Wordpress", "description" => "Build a client portal based on templates and customize it according to my brand guidelines.", "tags" => "portal", "icon" => "icon-layout-alt-04", "keywords" => ""),
        //     array("label" => "Client Portal", "type" => "Custom Design", "category" => "Portal", "platforms" => "Elementor,Webflow", "description" => "Create a custom design concept for my new client portal and then develop it on my preferred platform.", "tags" => "portal", "icon" => "icon-layout-alt-04", "keywords" => ""),
        //     array("label" => "Directory Site", "type" => "Template Design", "category" => "Directory Site", "platforms" => "Elementor,Webflow,Wordpress", "description" => "Build a directory based on templates and customize it according to my brand guidelines.", "tags" => "directory-site", "icon" => "icon-rows-03", "keywords" => ""),
        //     array("label" => "Directory Site", "type" => "Custom Design", "category" => "Directory Site", "platforms" => "Elementor,Webflow", "description" => "Create a custom design concept for my new directory and then develop it on my preferred platform.", "tags" => "directory-site", "icon" => "icon-rows-03", "keywords" => ""),
        //     array("label" => "No-code Web App", "type" => "", "category" => "No-code Apps", "platforms" => "Elementor,Webflow,Wordpress", "description" => "Design and develop a web application using my preferred no-code app", "tags" => "no-code-apps", "icon" => "icon-monitor-03", "keywords" => ""),
        //     array("label" => "No-code Mobile App", "type" => "", "category" => "No-code Apps", "platforms" => "Elementor,Webflow", "description" => "Design and develop a mobile application using my preferred no-code app", "tags" => "no-code-apps", "icon" => "icon-phone-02", "keywords" => ""),
        // );

        $projectTypes = File::get("database/data/project_types.json");
        $projectTypes = json_decode($projectTypes);

        foreach ($projectTypes as $pt => $type) {
            $pls = explode(', ', $type->platforms);
            // $platforms = Platform::where('title', 'like', $pls)->get();
            $platforms = Platform::where(function ($query) use ($type, $pls) {
                foreach ($pls as $p => $pl) {
                    $query->orWhere('title', $pl);
                }
            })->pluck('id');

            $cts = explode(',', $type->category);
            // $categories = ProjectTypeCategory::whereIn('slug', $cts)->pluck('id');
            $categories = ProjectTypeCategory::where(function ($query) use ($type, $cts) {
                foreach ($cts as $c => $ct) {
                    $query->orWhere('title', $ct);
                }
            })->pluck('id');

            \Log::info(json_encode([$platforms, $categories], JSON_PRETTY_PRINT));

            $projectType = new ProjectType();
            $projectType->title = $type->title;
            $projectType->type = $type->type;
            $projectType->description = $type->description;
            $projectType->platforms = $type->platforms;
            $projectType->category = $type->category;

            if ($projectType->save()) {
                if (!empty($platforms))
                    $projectType->platforms()->attach($platforms);

                if (!empty($categories))
                    $projectType->categories()->attach($categories);

                $projectType->questions()->attach($type->questions);
            }
        }
    }
}
