<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin portal roles
    |--------------------------------------------------------------------------
    */
    'portal_roles' => ['super_admin', 'admin', 'technical'],

    'assignable_user_roles' => [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'technical' => 'Technical',
        'manager' => 'Manager',
        'waiter' => 'Waiter',
    ],

    'editable_roles' => ['admin', 'technical'],

    'panel_home_route' => 'admin.dashboard',

    'technical_home_route' => 'admin.settings.index',

    /*
    |--------------------------------------------------------------------------
    | Permission registry (panel = operations admin, technical = system tools)
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'admin.panel.dashboard' => ['label' => 'Dashboard', 'section' => 'panel', 'group' => 'Main'],
        'admin.panel.analytics' => ['label' => 'TipTap Analytics', 'section' => 'panel', 'group' => 'Main'],
        'admin.panel.search' => ['label' => 'Global Search', 'section' => 'panel', 'group' => 'Main'],

        'admin.panel.restaurants' => ['label' => 'Restaurants', 'section' => 'panel', 'group' => 'People'],
        'admin.panel.restaurant_requests' => ['label' => 'Restaurant Requests', 'section' => 'panel', 'group' => 'People'],
        'admin.panel.plans' => ['label' => 'Plans & Pricing', 'section' => 'panel', 'group' => 'People'],
        'admin.panel.waiters' => ['label' => 'Waiters', 'section' => 'panel', 'group' => 'People'],

        'admin.panel.live_orders' => ['label' => 'Live Orders', 'section' => 'panel', 'group' => 'Operations'],
        'admin.panel.orders' => ['label' => 'Orders History', 'section' => 'panel', 'group' => 'Operations'],
        'admin.panel.customer_requests' => ['label' => 'Customer Requests', 'section' => 'panel', 'group' => 'Operations'],

        'admin.panel.payments' => ['label' => 'Payments', 'section' => 'panel', 'group' => 'Finance'],
        'admin.panel.withdrawals' => ['label' => 'Withdrawals', 'section' => 'panel', 'group' => 'Finance'],
        'admin.panel.tips' => ['label' => 'Tips', 'section' => 'panel', 'group' => 'Finance'],
        'admin.panel.payroll' => ['label' => 'Payroll', 'section' => 'panel', 'group' => 'Finance'],
        'admin.panel.reports' => ['label' => 'Reports', 'section' => 'panel', 'group' => 'Finance'],

        'admin.panel.landing_page' => ['label' => 'Landing Page', 'section' => 'panel', 'group' => 'Content'],
        'admin.panel.feedback' => ['label' => 'Feedback', 'section' => 'panel', 'group' => 'Content'],
        'admin.panel.menus' => ['label' => 'Menus', 'section' => 'panel', 'group' => 'Content'],
        'admin.panel.notifications' => ['label' => 'Notifications', 'section' => 'panel', 'group' => 'Content'],
        'admin.panel.impersonate' => ['label' => 'Impersonate Managers', 'section' => 'panel', 'group' => 'Operations'],

        'admin.technical.docker' => ['label' => 'Docker', 'section' => 'technical', 'group' => 'System'],
        'admin.technical.bots' => ['label' => 'Bots & WhatsApp', 'section' => 'technical', 'group' => 'System'],
        'admin.technical.activity_log' => ['label' => 'Activity Log', 'section' => 'technical', 'group' => 'System'],
        'admin.technical.settings' => ['label' => 'Settings', 'section' => 'technical', 'group' => 'System'],
        'admin.technical.payment_integration' => ['label' => 'Payment Integration', 'section' => 'technical', 'group' => 'System'],
        'admin.technical.fix_storage' => ['label' => 'Fix Storage', 'section' => 'technical', 'group' => 'System'],

        'admin.manage_users' => ['label' => 'Manage Users', 'section' => 'management', 'group' => 'Access Control'],
        'admin.manage_roles' => ['label' => 'Manage Roles', 'section' => 'management', 'group' => 'Access Control'],
    ],

    'default_role_permissions' => [
        'super_admin' => '*',
        'admin' => [
            'admin.panel.dashboard',
            'admin.panel.analytics',
            'admin.panel.search',
            'admin.panel.restaurants',
            'admin.panel.restaurant_requests',
            'admin.panel.plans',
            'admin.panel.waiters',
            'admin.panel.live_orders',
            'admin.panel.orders',
            'admin.panel.customer_requests',
            'admin.panel.payments',
            'admin.panel.withdrawals',
            'admin.panel.tips',
            'admin.panel.payroll',
            'admin.panel.reports',
            'admin.panel.landing_page',
            'admin.panel.feedback',
            'admin.panel.menus',
            'admin.panel.notifications',
            'admin.panel.impersonate',
        ],
        'technical' => [
            'admin.technical.docker',
            'admin.technical.bots',
            'admin.technical.activity_log',
            'admin.technical.settings',
            'admin.technical.payment_integration',
            'admin.technical.fix_storage',
        ],
    ],

];
