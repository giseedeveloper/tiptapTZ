<?php

namespace Database\Seeders;

use App\Support\AdminPortalAccess;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminPortalAccess::bootstrapRestaurantStaffRoles();
    }
}
