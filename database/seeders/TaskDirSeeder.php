<?php

namespace Database\Seeders;

use App\Models\TtCat;
use App\Models\Platform;
use App\Models\TaskType;
use Illuminate\Support\Str;
use App\Models\DynamicQuestion;
use Illuminate\Database\Seeder;

use App\Models\PlatformTaskType;
use App\Models\TaskTypeCategory;
use App\Models\DynamicQuestionable;
use Illuminate\Support\Facades\File;

class TaskDirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        TaskTypeCategory::truncate();

        DynamicQuestionable::where('dynamic_questionable_type', 'App\Models\TaskType')->delete();

        $taskTypeCategories = [
            // "UX Design",
            // "Graphic Design",
            // "Development",
            // "Maintenance",
            // "SEO",
            // "Hosting",
            // "Automation",
            // "No-Code",
            // "Conversion",
            // "Advice",[
            "ux-design" => [
                "label" => "UX Design",
                "tag" => "ux-design",
                "icon" => "icon-puzzle-piece"
            ],
            "ui-design" => [
                "label" => "UI Design",
                "tag" => "ui-design",
                "icon" => "icon-browser"
            ],
            "graphic-design" => [
                "label" => "Graphic Design",
                "tag" => "graphic-design",
                "icon" => "icon-pen-tool-02"
            ],
            "development" => [
                "label" => "Development",
                "tag" => "development",
                "icon" => "icon-code-browser"
            ],
            "maintenance" => [
                "label" => "Maintenance",
                "tag" => "maintenance",
                "icon" => "icon-tool-02"
            ],
            "seo" => [
                "label" => "SEO",
                "tag" => "seo",
                "icon" => "icon-layout-alt-02"
            ],
            "hosting" => [
                "label" => "Hosting",
                "tag" => "hosting",
                "icon" => "icon-server-01"
            ],
            "automation" => [
                "label" => "Automation",
                "tag" => "automation",
                "icon" => "icon-refresh-ccw-03"
            ],
            "no-code" => [
                "label" => "No-Code",
                "tag" => "no-code",
                "icon" => "icon-code-square-01"
            ],
            "conversion-optimization" => [
                "label" => "Conversion Optimization",
                "tag" => "conversion-optimization",
                "icon" => "icon-line-chart-up-03"
            ],
            "advice" => [
                "label" => "Advice",
                "tag" => "advice",
                "icon" => "icon-annotation-check"
            ]
        ];

        foreach ($taskTypeCategories as $ttc => $ttcat) {
            TaskTypeCategory::insert([
                'title' => $ttcat['label'],
                'slug' => $ttcat['tag'], //Str::slug($ttcat),
                'icon' => $ttcat['icon'],
            ]);
        }



        TaskType::truncate();
        TtCat::truncate();
        PlatformTaskType::truncate();

        // $projectTypes = array(
        //     // array("category"=>"category","label"=>"label","turn_around_days"=>"turn_around_days","platforms"=>"platforms","description"=>"description","tags"=>"tags","icon"=>"icon","keywords"=>"keywords"),
        //     array("category" => "UX Design", "label" => "Website Sitemap", "turn_around_days" => "1 Day", "platforms" => "Octopus.do", "description" => "Give me a 10,000-foot view of my website to make navigating the site easy and intuitive.", "tags" => "ux-design", "icon" => "icon-dataflow-04", "keywords" => ""),
        //     array("category" => "UX Design", "label" => "Information Architecture", "turn_around_days" => "1 Day", "platforms" => "Octopus.do", "description" => "Map out the content section for each page of my website or funnel to create a clear and intuitive user flow.", "tags" => "ux-design", "icon" => "icon-dataflow-01", "keywords" => ""),
        //     array("category" => "UX Design", "label" => "Website Wireframe", "turn_around_days" => "1-2 Days", "platforms" => "Figma,Adobe XD", "description" => "Create a conversion-optimized wireframe for my landing page or website.", "tags" => "ux-design", "icon" => "icon-image-indent-left", "keywords" => ""),
        //     array("category" => "UI Design", "label" => "Homepage Design Concept", "turn_around_days" => "1-2 Days", "platforms" => "Figma,Adobe XD", "description" => "Create a pixel-perfect design concept for my new homepage that impresses my customers.", "tags" => "ui-design", "icon" => "icon-home-05", "keywords" => ""),
        //     array("category" => "UI Design", "label" => "Landing Page Design Concept", "turn_around_days" => "1-2 Days", "platforms" => "Figma,Adobe XD", "description" => "Design a high-converting landing page concept for my product or service.", "tags" => "ui-design", "icon" => "icon-flag-01", "keywords" => ""),
        //     array("category" => "UI Design", "label" => "Internal Web Page Design Concept", "turn_around_days" => "1-2 Days", "platforms" => "Figma,Adobe XD", "description" => "Design my internal web pages based on your pre-defined design guidelines or homepage concept.", "tags" => "ui-design", "icon" => "icon-clapperboard", "keywords" => ""),
        //     array("category" => "UI Design", "label" => "Mobile App Design Concept", "turn_around_days" => "1-4 Days", "platforms" => "Figma,Adobe XD", "description" => "Create a user-centric design concept for my mobile app based on a wireframe.", "tags" => "ui-design", "icon" => "icon-phone-02", "keywords" => ""),
        //     array("category" => "UI Design", "label" => "Web App Design Concept", "turn_around_days" => "1-4 Days", "platforms" => "Figma,Adobe XD", "description" => "Design a new user interface for my web app based on my current version or new wireframes.", "tags" => "ui-design", "icon" => "icon-monitor-03", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Logo Design", "turn_around_days" => "1-2 Days", "platforms" => "Canva,Figma,Adobe XD,Photoshop,Illustrator,InDesign", "description" => "Design a new logo for my brand or product.", "tags" => "graphic-design", "icon" => "icon-pen-tool-02", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Brand Guidelines", "turn_around_days" => "1-2 Days", "platforms" => "Canva,Figma,Power Point,Google Slides", "description" => "Create new brand guidelines for my product or company.", "tags" => "graphic-design", "icon" => "icon-brand-guidelines", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Flyer & Brochure Design", "turn_around_days" => "1-2 Days", "platforms" => "Canva,Figma,Adobe XD,Photoshop,Illustrator,InDesign", "description" => "Create a creative flyer or brochure design for my product, service, or event.", "tags" => "graphic-design", "icon" => "icon-flyer-brochure", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Book Cover Design", "turn_around_days" => "1-2 Days", "platforms" => "Canva,Figma,Adobe XD,Photoshop,Illustrator,InDesign", "description" => "Design a cover for my paperback book or ebook that gets attention.", "tags" => "graphic-design", "icon" => "icon-book-cover", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Social Media Image Posts", "turn_around_days" => "1-2 Days", "platforms" => "Canva,Figma,Adobe XD,Photoshop,Illustrator,InDesign", "description" => "Design eye-catching social media posts for every platform and format.", "tags" => "graphic-design", "icon" => "icon-message-heart-square", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Social Image Ads", "turn_around_days" => "1-2 Days", "platforms" => "Canva,Figma,Adobe XD,Photoshop,Illustrator,InDesign", "description" => "Create a scroll-stopping ad image for multiple social media platforms.", "tags" => "graphic-design", "icon" => "icon-image-04", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Banner Ads", "turn_around_days" => "1-2 Days", "platforms" => "Canva,Figma,Adobe XD,Photoshop,Illustrator,InDesign", "description" => "Design attention-grabbing banner ads to promote my products or services.", "tags" => "graphic-design", "icon" => "icon-image-04", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Landing Page Graphics", "turn_around_days" => "1-2 Days", "platforms" => "Canva,Figma,Adobe XD,Photoshop,Illustrator,InDesign", "description" => "Create professional-looking graphics for my landing page or website that is customized for my brand.", "tags" => "graphic-design", "icon" => "icon-image-04", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Motion Graphic", "turn_around_days" => "1-2 Days", "platforms" => "After Effects", "description" => "Create an animated graphic for my landing page or website to increase engagement.", "tags" => "graphic-design", "icon" => "icon-motion-graphic", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Packaging Design", "turn_around_days" => "2 Days", "platforms" => "Canva,Figma,Adobe XD,Photoshop,Illustrator,InDesign", "description" => "Design a professional package design for my physical product.", "tags" => "graphic-design", "icon" => "icon-packaging-design", "keywords" => ""),
        //     array("category" => "Graphic Design", "label" => "Email Newsletter Template", "turn_around_days" => "1-2 Days", "platforms" => "Figma,Adobe XD", "description" => "Design a beautiful email template for newsletters or transactional emails.", "tags" => "graphic-design", "icon" => "icon-mail-02", "keywords" => ""),
        //     array("category" => "Development", "label" => "Homepage Development", "turn_around_days" => "1-4 Days", "platforms" => "Elementor,Webflow,Shopify,Hubspot", "description" => "Set up my new site and develop the homepage based on a predefined design concept or template.", "tags" => "development", "icon" => "icon-terminal-browser", "keywords" => ""),
        //     array("category" => "Development", "label" => "Internal Web Page Development", "turn_around_days" => "1-2 Days", "platforms" => "Elementor,Webflow,Shopify,Hubspot", "description" => "Develop an additional page on my website based on a predefined design concept or template.", "tags" => "development", "icon" => "icon-terminal-browser", "keywords" => ""),
        //     array("category" => "Development", "label" => "Landing Page Development", "turn_around_days" => "1-2 Days", "platforms" => "Elementor,Webflow,Shopify,Hubspot,Unbounce,Clickfunnels,Swipe Pages,Instapage", "description" => "Build a landing page based on a design concept or template that is optimized for performance.", "tags" => "development", "icon" => "icon-terminal-browser", "keywords" => ""),
        //     array("category" => "Development", "label" => "Email Template Development", "turn_around_days" => "1-2 Days", "platforms" => "HTML", "description" => "Develop an email template based on a design concept that can be used in my email marketing software.", "tags" => "development", "icon" => "icon-mail-02", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Fixing Website Bugs", "turn_around_days" => "1-2 Days", "platforms" => "Wordpress,Webflow,Shopify,Hubspot", "description" => "Improve the user experience of my website by fixing errors or bugs on my website.", "tags" => "maintenance", "icon" => "icon-tool-02", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Plugin Updates", "turn_around_days" => "1 Day", "platforms" => "Wordpress", "description" => "Update all my Wordpress Plugins in a safe way so that my site doesn''t break.", "tags" => "maintenance", "icon" => "icon-upload-04", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Security Features Setup", "turn_around_days" => "1-2 Days", "platforms" => "Wordpress", "description" => "Protect my website from getting hacked by implementing necessary security features.", "tags" => "maintenance", "icon" => "icon-passcode-lock", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Integrating Website with 3rd Party Tool", "turn_around_days" => "1-2 Days", "platforms" => "Wordpress,Webflow,Shopify,Hubspot", "description" => "Integrate an external tool with my website and make it look on- brand.", "tags" => "maintenance", "icon" => "icon-dataflow-03", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Website Content Update", "turn_around_days" => "1-2 Days", "platforms" => "Wordpress", "description" => "Update the content on my website to improve the user experience and/or search rankings.", "tags" => "maintenance", "icon" => "icon-image-indent-left", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Adding New Blog Post", "turn_around_days" => "1-2 Days", "platforms" => "Elementor,Webflow,Shopify,Hubspot", "description" => "Add a new blog post to my company blog with suitable graphics and a catchy featured image.", "tags" => "maintenance", "icon" => "icon-image-indent-left", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Site Speed Optimization", "turn_around_days" => "1 Day", "platforms" => "Wordpress", "description" => "Improve the loading time of my website to improve conversions and search rankings.", "tags" => "maintenance", "icon" => "icon-speedometer-02", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Site Backup", "turn_around_days" => "1 Day", "platforms" => "Wordpress", "description" => "Secure my website with a backup of the current site or install a plugin that does it automatically in the future.", "tags" => "maintenance", "icon" => "icon-download-02", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Broken Links and 404 Errors Check", "turn_around_days" => "1 Day", "platforms" => "Wordpress", "description" => "Check my website for any broken links and errors that we need to fix to improve the user experience and search rankings.", "tags" => "maintenance", "icon" => "icon-link-broken-01", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Google Analytics Setup", "turn_around_days" => "1 Day", "platforms" => "Google Analytics", "description" => "Install the Google Analytics pixel on my website so we can track our site visitors and conversions.", "tags" => "maintenance", "icon" => "icon-settings-01", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Google Search Console Setup", "turn_around_days" => "1 Day", "platforms" => "Google Search Console", "description" => "Install the Google Search Console pixel on my website, so I can index my pages and increase search rankings.", "tags" => "maintenance", "icon" => "icon-settings-01", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Google Tag Manager Setup", "turn_around_days" => "1 Day", "platforms" => "Google Tag Manager", "description" => "Install the GTM pixel on my website to speed up the integration of third-party tools in the future.", "tags" => "maintenance", "icon" => "icon-settings-01", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Funnel Analytics Setup", "turn_around_days" => "1 Day", "platforms" => "Hotjar,Mouseflow,Smartlook", "description" => "Set up my funnel and website analytics tool to track user behavior and conversions.", "tags" => "maintenance", "icon" => "icon-filter-funnel-02", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Facebook Pixel Setup", "turn_around_days" => "1 Day", "platforms" => "Meta,Google Tag Manager", "description" => "Install the Facebook pixel on my website and landing pages to track ad performance and conversions.", "tags" => "maintenance", "icon" => "icon-social-facebook", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Payment Integration", "turn_around_days" => "1 Day", "platforms" => "Stripe,Paypal,RechargePayments", "description" => "Integrate my checkout page with my preferred payment provider.", "tags" => "maintenance", "icon" => "icon-credit-card-02", "keywords" => ""),
        //     array("category" => "Maintenance", "label" => "Cookie Consent Setup", "turn_around_days" => "1 Day", "platforms" => "Usercentrics,Iubenda", "description" => "Set up the cookie consent tool to stay compliant with my website worldwide.", "tags" => "maintenance", "icon" => "icon-settings-01", "keywords" => ""),
        //     array("category" => "SEO", "label" => "Title tag & Meta Description Update", "turn_around_days" => "1-2 Days", "platforms" => "Wordpress,Webflow,Shopify,Others", "description" => "Add or update my website''s title tag and meta description of one or multiple pages.", "tags" => "seo", "icon" => "icon-code-snippet-02", "keywords" => ""),
        //     array("category" => "SEO", "label" => "Image Alt tag Update", "turn_around_days" => "1-2 Days", "platforms" => "Wordpress,Webflow,Shopify,Others", "description" => "Add or update image alt tags on my website so that search engines rank my site higher.", "tags" => "seo", "icon" => "icon-image-03", "keywords" => ""),
        //     array("category" => "Hosting", "label" => "Fixing Hosting Issues", "turn_around_days" => "1 Day", "platforms" => "Wordpress", "description" => "Fix my hosting issues and ensure that my website is online and secure.", "tags" => "hosting", "icon" => "icon-server-01", "keywords" => ""),
        //     array("category" => "Hosting", "label" => "Site Migration", "turn_around_days" => "1 Day", "platforms" => "Wordpress", "description" => "Migrate my new website to my server or hosting account.", "tags" => "hosting", "icon" => "icon-arrows-right", "keywords" => ""),
        //     array("category" => "Hosting", "label" => "DNS Setup", "turn_around_days" => "1 Day", "platforms" => "Wordpress", "description" => "Set up my DNS and update my MX server settings when moving my site to another hosting provider.", "tags" => "hosting", "icon" => "icon-server-05", "keywords" => ""),
        //     array("category" => "Automation", "label" => "Workflow Setup Between Apps", "turn_around_days" => "1 Day", "platforms" => "Zapier,Make,Parabola,Phantombuster", "description" => "Set up a workflow between my apps to boost our productivity by eliminating recurring tasks.", "tags" => "automation", "icon" => "icon-workflow-setup", "keywords" => ""),
        //     array("category" => "Automation", "label" => "Email Marketing Campaign Setup", "turn_around_days" => "1-2 Days", "platforms" => "Active Campaign,Klaviyo,Mailchimp,Keap,Hubspot", "description" => "Set up a new broadcast campaign or drip sequence within my email marketing software.", "tags" => "automation", "icon" => "icon-mail-02", "keywords" => ""),
        //     array("category" => "Automation", "label" => "Email Outreach Sequence Setup", "turn_around_days" => "1-2 Days", "platforms" => "Mailshake,Lemlist,Reply.io", "description" => "Set up a new email outreach sequence within my sales automation software.", "tags" => "automation", "icon" => "icon-mail-02", "keywords" => ""),
        //     array("category" => "Automation", "label" => "Chatbot Setup", "turn_around_days" => "1-2 Days", "platforms" => "Manychat,Landbot,ChatFuel", "description" => "Create chatbot automation to engage my website visitors and turn them into customers.", "tags" => "automation", "icon" => "icon-message-chat-square", "keywords" => ""),
        //     array("category" => "Automation", "label" => "Data Enrichment Workflow Setup", "turn_around_days" => "1-2 Days", "platforms" => "Clay.run,Clearbit", "description" => "Create a workflow to build lead lists with information from multiple sources.", "tags" => "automation", "icon" => "icon-data-enrichment", "keywords" => ""),
        //     array("category" => "Automation", "label" => "Automated Image Creation Setup", "turn_around_days" => "1-2 Days", "platforms" => "Bannerbear,Abyssale", "description" => "Create a workflow to build lead lists with information from multiple sources.", "tags" => "automation", "icon" => "icon-image-03", "keywords" => ""),
        //     array("category" => "No-Code", "label" => "Membership Site Setup", "turn_around_days" => "1-2 Days", "platforms" => "Memberful,Memberspace,Memberstack", "description" => "Set up a membership feature on my website for gated content that is only visible for logged-in users.", "tags" => "no-code", "icon" => "icon-users-01", "keywords" => ""),
        //     array("category" => "No-Code", "label" => "Online Course Setup", "turn_around_days" => "1-2 Days", "platforms" => "Teachable,Kajabi,Podia", "description" => "Set up my online course and add the provided learning material.", "tags" => "no-code", "icon" => "icon-online-course", "keywords" => ""),
        //     array("category" => "No-Code", "label" => "Community Tool Setup", "turn_around_days" => "1-2 Days", "platforms" => "Circle.so,Skool,Buddy Boss,Mighty Networks", "description" => "Set up my online community including the design of the login area, checkout, and content sections.", "tags" => "no-code", "icon" => "icon-users-01", "keywords" => ""),
        //     array("category" => "No-Code", "label" => "Webinar Setup", "turn_around_days" => "1 Day", "platforms" => "Kajabi,Podia,Livestorm", "description" => "Set up the landing page and email marketing campaign for my next webinar.", "tags" => "no-code", "icon" => "icon-video-recorder", "keywords" => ""),
        //     array("category" => "No-Code", "label" => "Glide App Setup", "turn_around_days" => "1-2 Days", "platforms" => "Glide", "description" => "Build a Glide up based on our Google Sheets data set for my team or clients.", "tags" => "no-code", "icon" => "icon-code-square-01", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Opt-In Popup Design & Setup", "turn_around_days" => "1-2 Days", "platforms" => "Optin Monster,Sumo,Sleeknote,Convertflow", "description" => "Design and set up a popup feature on my website and connect it with my email marketing software.", "tags" => "conversion-optimization", "icon" => "icon-settings-01", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Gamification Popup Setup", "turn_around_days" => "1 Day", "platforms" => "Justuno,Privy", "description" => "Add a gamification popup to my website to increase engagement and generate leads.", "tags" => "conversion-optimization", "icon" => "icon-gaming-pad-01", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Website/Funnel Quiz Setup", "turn_around_days" => "1 Day", "platforms" => "Involve.me,Bucket.io,Perspective,Typeform,Convertflow,Outgrow", "description" => "Set up a quiz funnel for my website or advertising campaigns to create more qualified buyers.", "tags" => "conversion-optimization", "icon" => "icon-filter-funnel-02", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Website Personalization Setup", "turn_around_days" => "1 Day", "platforms" => "Active Campaign,If-so,Hyperise", "description" => "Set up a personalization feature on my website to show dynamic content to multiple audiences based on their IP address.", "tags" => "conversion-optimization", "icon" => "icon-monitor-03", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Optimize Add to Cart Page", "turn_around_days" => "1 Day", "platforms" => "Cartpops,Vanga.ai", "description" => "Add a coupon code and upsell section to my Add-to Cart page to increase conversions.", "tags" => "conversion-optimization", "icon" => "icon-shopping-cart-03", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "A/B Test Setup", "turn_around_days" => "1 Day", "platforms" => "Google Optimize,AB Testing.ai,Hypertune.ai", "description" => "Set up an A/B test for my landing page to test multiple copy and design variations and increase my conversion rate.", "tags" => "conversion-optimization", "icon" => "icon-settings-01", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Social Proof Tool Setup", "turn_around_days" => "1 Day", "platforms" => "Useproof,Fomo.com,Yotpo", "description" => "Add a FOMO feature to my website to show site visitors that other people are buying from us.", "tags" => "conversion-optimization", "icon" => "icon-message-smile-square", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Website Calculator Setup", "turn_around_days" => "1 Day", "platforms" => "Involve.me,Outgrow,Calconic", "description" => "Build and integrate a calculator on my website to help our customers make informed buying decisions.", "tags" => "conversion-optimization", "icon" => "icon-calculator", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Referral & Reward Program Setup", "turn_around_days" => "1 Day", "platforms" => "Viral Loops,TryBeans,Smile.io", "description" => "Set up a referral program for my product, service, or newsletter to attract more customers.", "tags" => "conversion-optimization", "icon" => "icon-users-plus", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Automated Image/Video Personalization Setup", "turn_around_days" => "1 Day", "platforms" => "Hyperise", "description" => "Personalize my email outreach campaign with dynamic images and videos.", "tags" => "conversion-optimization", "icon" => "icon-settings-01", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "Testimonial Campaign Setup", "turn_around_days" => "1 Day", "platforms" => "Testimonial.to,Shoutout.so,Videopeel", "description" => "Ask my customers for text or video testimonials and highlight them on my website.", "tags" => "conversion-optimization", "icon" => "icon-message-smile-square", "keywords" => ""),
        //     array("category" => "Conversion Optimization", "label" => "B2B Website Visitor Identification Setup", "turn_around_days" => "1 Day", "platforms" => "Albacross,Leadfeeder", "description" => "Set up a website visitor identification tool so I can reach out to companies who browse my website.", "tags" => "conversion-optimization", "icon" => "icon-settings-01", "keywords" => ""),
        //     array("category" => "Advice", "label" => "Landing Page Conversion Audit", "turn_around_days" => "3 Days", "platforms" => "Zoom,Loom", "description" => "Conduct an audit of my landing page and give me clear action steps on how to improve my conversion rate.", "tags" => "advice", "icon" => "icon-browser", "keywords" => ""),
        //     array("category" => "Advice", "label" => "Funnel Audit & Strategy", "turn_around_days" => "3 Days", "platforms" => "Zoom,Loom", "description" => "Conduct an audit of my funnel or provide me with tips on how to structure my next funnel.", "tags" => "advice", "icon" => "icon-filter-funnel-02", "keywords" => ""),
        //     array("category" => "Advice", "label" => "Security Audit", "turn_around_days" => "1 Day", "platforms" => "Google Docs", "description" => "Provide me with a report on potential security issues of my website and how to fix them.", "tags" => "advice", "icon" => "icon-shield-tick", "keywords" => ""),
        //     array("category" => "Advice", "label" => "Software/Tool Suggestions", "turn_around_days" => "1 Day", "platforms" => "Google Docs", "description" => "Give me suggestions on software or a tool to do X.", "tags" => "advice", "icon" => "icon-message-check-square", "keywords" => ""),
        //     array("category" => "Advice", "label" => "Workflow/Automation Suggestions", "turn_around_days" => "2 Days", "platforms" => "Google Docs", "description" => "Give me suggestions on automating repetitive tasks with one or multiple apps.", "tags" => "advice", "icon" => "icon-workflow-setup", "keywords" => ""),
        // );

        $taskTypes = File::get("database/data/directories.json");
        $taskTypes = json_decode($taskTypes);

        foreach ($taskTypes as $pt => $type) {
            $pls = explode(',', $type->platforms);
            // $platforms = Platform::where('title', 'like', $pls)->get();
            $platforms = Platform::where(function ($query) use ($type, $pls) {
                foreach ($pls as $p => $pl) {
                    $query->orWhere('title', $pl);
                }
            })->pluck('id');

            $cts = explode(',', $type->category);
            // $categories = TaskTypeCategory::whereIn('slug', $cts)->pluck('id');
            $categories = TaskTypeCategory::where(function ($query) use ($type, $cts) {
                foreach ($cts as $c => $ct) {
                    $query->orWhere('title',  $ct);
                }
            })->pluck('id');

            // \Log::info(json_encode([$platforms, $categories], JSON_PRETTY_PRINT));

            $taskType = new TaskType();
            $taskType->title = $type->label;
            $taskType->description = $type->description;
            $taskType->icon = $type->icon;
            $taskType->keywords = $type->keywords;
            $taskType->turn_around_days = $type->turn_around_days;

            if ($taskType->save()) {
                if (!empty($platforms))
                    $taskType->platforms()->attach($platforms);

                if (!empty($categories))
                    $taskType->categories()->attach($categories);

                $taskType->dynamicQuestions()->attach($type->questions);
            }
        }
    }
}
