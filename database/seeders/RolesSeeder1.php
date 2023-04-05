<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder1 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organization_admin = Role::create(['guard_name' => 'api', 'name' => 'organization_admin']);
        $organization_billing = Role::create(['guard_name' => 'api', 'name' => 'organization_billing']);
        $organization_employee = Role::create(['guard_name' => 'api', 'name' => 'organization_employee']);
    }
}
