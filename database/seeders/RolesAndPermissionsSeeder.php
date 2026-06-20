<?php

namespace Database\Seeders;

use App\Support\AdminPortalAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminPortalAccess::bootstrapPortalAccess();

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions(['manage_menu', 'manage_waiters', 'view_orders', 'update_orders', 'view_payments', 'confirm_payments', 'view_feedback', 'view_reports_restaurant']);

        $waiter = Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web']);
        $waiter->syncPermissions(['view_orders', 'update_orders_status', 'view_tips']);

        $botService = Role::firstOrCreate(['name' => 'bot_service', 'guard_name' => 'web']);
        $botService->syncPermissions(['api_restaurant_search', 'api_get_menu', 'api_create_order', 'api_create_payment', 'api_check_payment', 'api_submit_feedback', 'api_submit_tip']);
    }
}
