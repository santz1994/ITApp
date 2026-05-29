<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Menu;
use App\Role;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run()
    {
        DB::table('menu_user')->truncate();
        DB::table('menu_role')->truncate();
        DB::table('menus')->truncate();

        $menus = [
            [
                'label' => 'Dashboard',
                'route' => 'home',
                'icon' => 'fa fa-dashboard',
                'order_index' => 1,
                'roles' => ['super-admin', 'admin', 'director', 'user', 'receptionist'],
            ],

            // Meeting Room Booking
            [
                'label' => 'Meeting Rooms',
                'icon' => 'fa fa-calendar',
                'order_index' => 2,
                'roles' => ['super-admin', 'admin', 'director', 'user', 'receptionist'],
                'children' => [
                    [
                        'label' => 'All Bookings',
                        'route' => 'meeting-room-bookings.index',
                        'icon' => 'fa fa-list',
                        'roles' => ['super-admin', 'admin', 'director', 'user', 'receptionist'],
                    ],
                    [
                        'label' => 'New Booking',
                        'route' => 'meeting-room-bookings.create',
                        'icon' => 'fa fa-plus',
                        'roles' => ['super-admin', 'admin', 'user'],
                    ],
                    [
                        'label' => 'Calendar View',
                        'route' => 'meeting-room-bookings-calendar',
                        'icon' => 'fa fa-calendar-o',
                        'roles' => ['super-admin', 'admin', 'director', 'receptionist'],
                    ],
                    [
                        'label' => 'Receptionist Dashboard',
                        'route' => 'meeting-room-receptionist-dashboard',
                        'icon' => 'fa fa-tachometer',
                        'roles' => ['super-admin', 'admin', 'receptionist'],
                    ],
                    [
                        'label' => 'Director Dashboard',
                        'route' => 'meeting-room-director-dashboard',
                        'icon' => 'fa fa-check-square-o',
                        'roles' => ['super-admin', 'director'],
                    ],
                ],
            ],

            // Vehicle Booking
            [
                'label' => 'Vehicles',
                'icon' => 'fa fa-car',
                'order_index' => 3,
                'roles' => ['super-admin', 'admin', 'director', 'user'],
                'children' => [
                    [
                        'label' => 'Vehicle List',
                        'route' => 'vehicles.index',
                        'icon' => 'fa fa-list',
                        'roles' => ['super-admin', 'admin', 'director', 'user'],
                    ],
                    [
                        'label' => 'New Booking',
                        'route' => 'vehicles.booking.create',
                        'icon' => 'fa fa-plus',
                        'roles' => ['super-admin', 'admin', 'user'],
                    ],
                    [
                        'label' => 'My Bookings',
                        'route' => 'vehicles.my-bookings',
                        'icon' => 'fa fa-history',
                        'roles' => ['super-admin', 'admin', 'director', 'user'],
                    ],
                    [
                        'label' => 'All Bookings',
                        'route' => 'vehicles.bookings',
                        'icon' => 'fa fa-list-alt',
                        'roles' => ['super-admin', 'admin', 'director'],
                    ],
                ],
            ],

            // Inventory Management
            [
                'label' => 'Inventory',
                'icon' => 'fa fa-cubes',
                'order_index' => 4,
                'roles' => ['super-admin', 'admin', 'director', 'user'],
                'children' => [
                    [
                        'label' => 'Item List',
                        'route' => 'inventory.index',
                        'icon' => 'fa fa-list',
                        'roles' => ['super-admin', 'admin', 'director', 'user'],
                    ],
                    [
                        'label' => 'New Request',
                        'route' => 'inventory.request.create',
                        'icon' => 'fa fa-plus',
                        'roles' => ['super-admin', 'admin', 'user'],
                    ],
                    [
                        'label' => 'My Requests',
                        'route' => 'inventory.requests',
                        'icon' => 'fa fa-file-text-o',
                        'roles' => ['super-admin', 'admin', 'director', 'user'],
                    ],
                    [
                        'label' => 'Low Stock Alert',
                        'route' => 'inventory.low-stock',
                        'icon' => 'fa fa-exclamation-triangle',
                        'roles' => ['super-admin', 'admin'],
                    ],
                ],
            ],

            // Approvals
            [
                'label' => 'Approvals',
                'route' => 'approvals.pending',
                'icon' => 'fa fa-check-circle',
                'order_index' => 5,
                'roles' => ['super-admin', 'admin', 'director'],
            ],

            // User Management
            [
                'label' => 'User Management',
                'icon' => 'fa fa-users',
                'order_index' => 6,
                'roles' => ['super-admin', 'admin'],
                'children' => [
                    [
                        'label' => 'All Users',
                        'route' => 'users.index',
                        'icon' => 'fa fa-users',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Add User',
                        'route' => 'users.create',
                        'icon' => 'fa fa-user-plus',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'User Roles',
                        'route' => 'users.roles',
                        'icon' => 'fa fa-id-badge',
                        'roles' => ['super-admin', 'admin'],
                    ],
                ],
            ],

            // Settings
            [
                'label' => 'Settings',
                'icon' => 'fa fa-cogs',
                'order_index' => 7,
                'roles' => ['super-admin'],
                'children' => [
                    [
                        'label' => 'System Settings',
                        'route' => 'system-settings.index',
                        'icon' => 'fa fa-gear',
                        'roles' => ['super-admin'],
                    ],
                    [
                        'label' => 'Roles & Permissions',
                        'route' => 'system.roles',
                        'icon' => 'fa fa-lock',
                        'roles' => ['super-admin'],
                    ],
                    [
                        'label' => 'Menu Management',
                        'route' => 'admin.menus.index',
                        'icon' => 'fa fa-bars',
                        'roles' => ['super-admin'],
                    ],
                    [
                        'label' => 'Approval Rules',
                        'route' => 'approvals.rules',
                        'icon' => 'fa fa-sitemap',
                        'roles' => ['super-admin'],
                    ],
                    [
                        'label' => 'Audit Logs',
                        'route' => 'audit-logs.index',
                        'icon' => 'fa fa-history',
                        'roles' => ['super-admin'],
                    ],
                ],
            ],
        ];

        $this->seedMenus($menus);
        $this->command->info('Menus seeded successfully!');
    }

    protected function seedMenus(array $menus, $parentId = null)
    {
        foreach ($menus as $index => $menuData) {
            $roles = $menuData['roles'] ?? [];
            $children = $menuData['children'] ?? [];

            unset($menuData['roles'], $menuData['children']);

            $menuData['parent_id'] = $parentId;
            $menuData['order_index'] = $menuData['order_index'] ?? $index;
            $menuData['is_active'] = $menuData['is_active'] ?? true;
            $menuData['is_external'] = $menuData['is_external'] ?? false;
            $menuData['target'] = $menuData['target'] ?? '_self';

            $menu = Menu::create($menuData);

            if (!empty($roles)) {
                $normalizedRoles = Role::expandNames(array_map(static function ($roleName): string {
                    return (string) $roleName;
                }, $roles));

                $roleIds = Role::query()
                    ->whereIn('name', $normalizedRoles)
                    ->whereIn('name', Role::canonicalNames())
                    ->pluck('id')
                    ->toArray();

                $syncData = [];
                foreach ($roleIds as $roleId) {
                    $syncData[$roleId] = ['can_view' => true];
                }

                $menu->roles()->sync($syncData);
            }

            if (!empty($children)) {
                $this->seedMenus($children, $menu->id);
            }
        }
    }
}
