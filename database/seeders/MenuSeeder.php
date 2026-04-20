<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Menu;
use App\Role;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing menus
        DB::table('menu_user')->truncate();
        DB::table('menu_role')->truncate();
        DB::table('menus')->truncate();

        // Define menu structure
        $menus = [
            // Dashboard
            [
                'label' => 'Dashboard',
                'route' => 'home',
                'icon' => 'fa fa-dashboard',
                'order_index' => 1,
                'roles' => ['super-admin', 'admin', 'director', 'user', 'receptionist'],
            ],

            // Assets Management
            [
                'label' => 'Assets',
                'icon' => 'fa fa-desktop',
                'order_index' => 2,
                'roles' => ['super-admin', 'admin'],
                'children' => [
                    [
                        'label' => 'All Assets',
                        'route' => 'assets.index',
                        'icon' => 'fa fa-list',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Add New Asset',
                        'route' => 'assets.create',
                        'icon' => 'fa fa-plus',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Asset Models',
                        'route' => 'assetmodels.index',
                        'icon' => 'fa fa-cubes',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Asset Maintenance',
                        'route' => 'asset-maintenance.index',
                        'icon' => 'fa fa-wrench',
                        'roles' => ['super-admin', 'admin'],
                    ],
                ],
            ],

            // Tickets/Helpdesk
            [
                'label' => 'Tickets',
                'icon' => 'fa fa-ticket',
                'order_index' => 3,
                'roles' => ['super-admin', 'admin', 'user'],
                'children' => [
                    [
                        'label' => 'All Tickets',
                        'route' => 'tickets.index',
                        'icon' => 'fa fa-list',
                        'roles' => ['super-admin', 'admin', 'user'],
                    ],
                    [
                        'label' => 'Create Ticket',
                        'route' => 'tickets.create',
                        'icon' => 'fa fa-plus',
                        'roles' => ['super-admin', 'admin', 'user'],
                    ],
                    [
                        'label' => 'My Tickets',
                        'route' => 'user.tickets.index',
                        'icon' => 'fa fa-user',
                        'roles' => ['user'],
                    ],
                ],
            ],

            // Meeting Room Booking
            [
                'label' => 'Meeting Rooms',
                'icon' => 'fa fa-calendar',
                'order_index' => 4,
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

            // Inventory/Spares
            [
                'label' => 'Inventory',
                'route' => 'spares.index',
                'icon' => 'fa fa-archive',
                'order_index' => 5,
                'roles' => ['super-admin', 'admin'],
            ],

            // Reports
            [
                'label' => 'Reports',
                'icon' => 'fa fa-bar-chart',
                'order_index' => 6,
                'roles' => ['super-admin', 'admin', 'director'],
                'children' => [
                    [
                        'label' => 'Asset Reports',
                        'route' => 'assets.index',
                        'icon' => 'fa fa-desktop',
                        'roles' => ['super-admin', 'admin', 'director'],
                    ],
                    [
                        'label' => 'Ticket Reports',
                        'route' => 'tickets.index',
                        'icon' => 'fa fa-ticket',
                        'roles' => ['super-admin', 'admin', 'director'],
                    ],
                    [
                        'label' => 'SLA Dashboard',
                        'route' => 'sla.dashboard',
                        'icon' => 'fa fa-pie-chart',
                        'roles' => ['super-admin', 'director'],
                    ],
                ],
            ],

            // Management
            [
                'label' => 'Management',
                'icon' => 'fa fa-briefcase',
                'order_index' => 7,
                'roles' => ['super-admin', 'director'],
                'children' => [
                    [
                        'label' => 'Management Dashboard',
                        'route' => 'management.dashboard',
                        'icon' => 'fa fa-tachometer',
                        'roles' => ['super-admin', 'director'],
                    ],
                    [
                        'label' => 'KPI Dashboard',
                        'route' => 'kpi.dashboard',
                        'icon' => 'fa fa-line-chart',
                        'roles' => ['super-admin', 'director'],
                    ],
                ],
            ],

            // Master Data
            [
                'label' => 'Master Data',
                'icon' => 'fa fa-database',
                'order_index' => 8,
                'roles' => ['super-admin', 'admin'],
                'children' => [
                    [
                        'label' => 'Users',
                        'route' => 'users.index',
                        'icon' => 'fa fa-users',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Divisions',
                        'route' => 'divisions.index',
                        'icon' => 'fa fa-sitemap',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Locations',
                        'route' => 'locations.index',
                        'icon' => 'fa fa-map-marker',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Manufacturers',
                        'route' => 'manufacturers.index',
                        'icon' => 'fa fa-industry',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Suppliers',
                        'route' => 'suppliers.index',
                        'icon' => 'fa fa-truck',
                        'roles' => ['super-admin', 'admin'],
                    ],
                    [
                        'label' => 'Statuses',
                        'route' => 'statuses.index',
                        'icon' => 'fa fa-info-circle',
                        'roles' => ['super-admin', 'admin'],
                    ],
                ],
            ],

            // Settings
            [
                'label' => 'Settings',
                'icon' => 'fa fa-cogs',
                'order_index' => 9,
                'roles' => ['super-admin'],
                'children' => [
                    [
                        'label' => 'System Settings',
                        'route' => 'system.settings',
                        'icon' => 'fa fa-gear',
                        'roles' => ['super-admin'],
                    ],
                    [
                        'label' => 'Roles & Permissions',
                        'route' => 'roles.index',
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
                        'label' => 'SLA Policies',
                        'route' => 'sla.index',
                        'icon' => 'fa fa-clock-o',
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

        // Seed menus recursively
        $this->seedMenus($menus);

        $this->command->info('Menus seeded successfully!');
    }

    /**
     * Recursively seed menus and their children
     *
     * @param array $menus
     * @param int|null $parentId
     * @return void
     */
    protected function seedMenus(array $menus, $parentId = null)
    {
        foreach ($menus as $index => $menuData) {
            // Extract roles and children
            $roles = $menuData['roles'] ?? [];
            $children = $menuData['children'] ?? [];
            
            unset($menuData['roles'], $menuData['children']);

            // Set parent and order
            $menuData['parent_id'] = $parentId;
            $menuData['order_index'] = $menuData['order_index'] ?? $index;
            $menuData['is_active'] = $menuData['is_active'] ?? true;
            $menuData['is_external'] = $menuData['is_external'] ?? false;
            $menuData['target'] = $menuData['target'] ?? '_self';

            // Create menu
            $menu = Menu::create($menuData);

            // Attach roles
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

            // Recursively seed children
            if (!empty($children)) {
                $this->seedMenus($children, $menu->id);
            }
        }
    }
}
