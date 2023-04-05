<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Role::truncate();
        User::truncate();

        $superadmin = Role::create(['guard_name' => 'api', 'name' => 'superadmin']);
        $superadmin_user = User::create([
            'username' => 'super_growmodo',
            'firstname' => 'Super',
            'lastname' => 'Growmodo',
            'status' => 'active',
            'email' => 'super@growmodo.dev',
            'email_verified_at' => Carbon::now()->format('2022-01-01 00:00:00'),
            'password' => Hash::make('growmodosuper'),
        ]);

        $superadmin_user->assignRole($superadmin);

        $organization_admin = Role::create(['guard_name' => 'api', 'name' => 'organization_admin']);
        $organization_billing = Role::create(['guard_name' => 'api', 'name' => 'organization_billing']);
        $organization_employee = Role::create(['guard_name' => 'api', 'name' => 'organization_member']);
    }
}
