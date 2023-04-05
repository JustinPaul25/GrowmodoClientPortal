#!/bin/sh
echo Running PHP Artisan Scripts;

php artisan key:generate;

php artisan passport:install --force;

# Database Seeders
php artisan db:seed AclSeeder;
php artisan db:seed RolesSeeder1;
php artisan db:seed BrandCategorySeeder;
php artisan db:seed CompanyTypesSeeder;
php artisan db:seed EmployeeCountSeeder;
php artisan db:seed SocialPlatformSeeder;
php artisan db:seed OptionsSeeder;
php artisan db:seed PlanTypeSeeder;
php artisan db:seed PlatformsSeeder;
php artisan db:seed ProjectDirSeeder;
php artisan db:seed TaskDirSeeder;
php artisan db:seed DynamicQuestionsSeeder;
php artisan db:seed SubTalentSeeder;

echo PHP Artisan Complete;
