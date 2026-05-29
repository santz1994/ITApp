@php
  $sidebarLanguage = 'en';

  if (auth()->check()) {
    $rawPreferences = auth()->user()->portal_preferences ?? [];
    $preferences = is_array($rawPreferences) ? $rawPreferences : (json_decode((string) $rawPreferences, true) ?: []);
    $sidebarLanguage = (($preferences['language'] ?? 'en') === 'id') ? 'id' : 'en';
  }

  $sidebarLabels = [
    'navigation' => ['en' => 'Navigation', 'id' => 'Navigasi'],
    'workspace' => ['en' => 'Workspace', 'id' => 'Ruang Kerja'],
    'main_portal' => ['en' => 'Main Portal', 'id' => 'Portal Utama'],
    'profile' => ['en' => 'Profile', 'id' => 'Profil'],
  ];

  $routeName = optional(request()->route())->getName() ?? '';
  $requestedWorkspace = (string) request()->query('workspace', '');
  $validWorkspaces = [
    'meeting_room',
    'vehicle',
    'inventory',
    'approval',
    'user_management',
    'settings',
    'profile',
  ];

  $workspaceFromRoute = null;

  if (\Illuminate\Support\Str::startsWith($routeName, 'meeting-room-bookings.')) {
    $workspaceFromRoute = 'meeting_room';
  } elseif (\Illuminate\Support\Str::startsWith($routeName, 'vehicles.')) {
    $workspaceFromRoute = 'vehicle';
  } elseif (\Illuminate\Support\Str::startsWith($routeName, 'inventory.')) {
    $workspaceFromRoute = 'inventory';
  } elseif (\Illuminate\Support\Str::startsWith($routeName, 'approvals.')) {
    $workspaceFromRoute = 'approval';
  } elseif (
    \Illuminate\Support\Str::startsWith($routeName, 'users.')
    || \Illuminate\Support\Str::startsWith($routeName, 'admin.users.')
  ) {
    $workspaceFromRoute = 'user_management';
  } elseif (
    \Illuminate\Support\Str::startsWith($routeName, 'system-settings.')
    || \Illuminate\Support\Str::startsWith($routeName, 'system.')
    || \Illuminate\Support\Str::startsWith($routeName, 'audit-logs.')
    || \Illuminate\Support\Str::startsWith($routeName, 'admin.')
  ) {
    $workspaceFromRoute = 'settings';
  } elseif (\Illuminate\Support\Str::startsWith($routeName, 'profile.')) {
    $workspaceFromRoute = 'profile';
  }

  $sidebarWorkspace = in_array($requestedWorkspace, $validWorkspaces, true)
    ? $requestedWorkspace
    : (in_array((string) $workspaceFromRoute, $validWorkspaces, true) ? $workspaceFromRoute : null);

  if (in_array($routeName, ['home', 'portal.index'], true)) {
    $sidebarWorkspace = null;
  }

  $showWorkspace = static function (array $workspaceKeys) use ($sidebarWorkspace): bool {
    return $sidebarWorkspace === null || in_array($sidebarWorkspace, $workspaceKeys, true);
  };

  $workspaceLabels = [
    'meeting_room' => ['en' => 'Meeting Room', 'id' => 'Ruang Rapat'],
    'vehicle' => ['en' => 'Vehicle Management', 'id' => 'Manajemen Kendaraan'],
    'inventory' => ['en' => 'Inventory Management', 'id' => 'Manajemen Inventaris'],
    'approval' => ['en' => 'Approvals', 'id' => 'Persetujuan'],
    'user_management' => ['en' => 'User Management', 'id' => 'Manajemen Pengguna'],
    'settings' => ['en' => 'Settings', 'id' => 'Pengaturan'],
    'profile' => ['en' => 'Profile', 'id' => 'Profil'],
  ];
@endphp

<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu">
            <li class="header" data-i18n="sidebar.navigation">{{ $sidebarLabels['navigation'][$sidebarLanguage] }}</li>
            @if($sidebarWorkspace !== null)
              <li class="header">
                <span data-i18n="sidebar.workspace">{{ $sidebarLabels['workspace'][$sidebarLanguage] }}</span>:
                <span>{{ $workspaceLabels[$sidebarWorkspace][$sidebarLanguage] ?? ($workspaceLabels[$sidebarWorkspace]['en'] ?? 'Module') }}</span>
              </li>
            @endif

            {{-- Main Portal --}}
            @auth
              <li><a href="{{ route('home') }}"><i class='fa fa-home'></i> <span data-i18n="sidebar.main_portal">{{ $sidebarLabels['main_portal'][$sidebarLanguage] }}</span></a></li>
            @endauth

            {{-- Profile --}}
            @if($showWorkspace(['profile']))
            @if(Route::has('profile.edit'))
            @auth
            <li><a href="{{ route('profile.edit') }}"><i class='fa fa-user-circle'></i> <span data-i18n="sidebar.profile">{{ $sidebarLabels['profile'][$sidebarLanguage] }}</span></a></li>
            @endauth
            @endif
            @endif

            {{-- Meeting Room Booking --}}
            @if($showWorkspace(['meeting_room']))
            @auth
              <li class="header sidebar-section-header">Meeting Room Booking</li>
              <li><a href="{{ route('meeting-room-bookings.index') }}"><i class="fa fa-calendar-check-o"></i> All Bookings</a></li>
              <li><a href="{{ route('meeting-room-bookings.calendar') }}"><i class="fa fa-calendar"></i> Calendar View</a></li>
              <li><a href="{{ route('meeting-room-bookings.create') }}"><i class="fa fa-plus-circle"></i> New Booking</a></li>

              @role(['director', 'developer', 'administrator'])
              <li><a href="{{ route('meeting-room-bookings.director-dashboard') }}"><i class="fa fa-dashboard text-purple"></i> Director Dashboard</a></li>
              @endrole

              @role(['receptionist', 'administrator', 'developer'])
              <li><a href="{{ route('meeting-room-bookings.receptionist-dashboard') }}"><i class="fa fa-desktop text-green"></i> Receptionist Dashboard</a></li>
              <li><a href="{{ route('meeting-room-bookings.lcd-settings') }}"><i class="fa fa-sliders text-orange"></i> LCD Settings</a></li>
              <li><a href="#" data-action="open-monthly-report-modal"><i class="fa fa-file-excel-o text-success"></i> Monthly Report (Excel)</a></li>
              @endrole

              <li><a href="{{ route('meeting-room-bookings.lcd-dashboard') }}" target="_blank"><i class="fa fa-tv text-blue"></i> LCD Dashboard</a></li>

              @role(['director', 'administrator', 'developer'])
              <li><a href="{{ route('meeting-room-bookings.index', ['tab' => 'pending']) }}"><i class="fa fa-clock-o"></i> Pending Approval</a></li>
              @endrole
            @endauth
            @endif

            {{-- Vehicle Booking --}}
            @if($showWorkspace(['vehicle']))
            @auth
              <li class="header sidebar-section-header">Vehicle Management</li>
              <li><a href="{{ route('vehicles.index') }}"><i class="fa fa-car"></i> Vehicle List</a></li>
              <li><a href="{{ route('vehicles.booking.create') }}"><i class="fa fa-plus-circle"></i> New Booking</a></li>
              <li><a href="{{ route('vehicles.my-bookings') }}"><i class="fa fa-history"></i> My Bookings</a></li>

              @role(['director', 'administrator', 'developer'])
              <li><a href="{{ route('vehicles.bookings') }}"><i class="fa fa-list-alt"></i> All Bookings</a></li>
              @endrole
            @endauth
            @endif

            {{-- Inventory Management --}}
            @if($showWorkspace(['inventory']))
            @auth
              <li class="header sidebar-section-header">Inventory Management</li>
              <li><a href="{{ route('inventory.index') }}"><i class="fa fa-cubes"></i> Item List</a></li>
              <li><a href="{{ route('inventory.request.create') }}"><i class="fa fa-plus-circle"></i> New Request</a></li>
              <li><a href="{{ route('inventory.requests') }}"><i class="fa fa-file-text-o"></i> My Requests</a></li>

              @role(['administrator', 'developer'])
              <li><a href="{{ route('inventory.low-stock') }}"><i class="fa fa-exclamation-triangle text-yellow"></i> Low Stock Alert</a></li>
              @endrole
            @endauth
            @endif

            {{-- Approvals --}}
            @if($showWorkspace(['approval']))
            @auth
              <li class="header sidebar-section-header">Approvals</li>
              <li><a href="{{ route('approvals.pending') }}"><i class="fa fa-check-circle"></i> Pending Approvals</a></li>

              @role(['administrator', 'developer'])
              <li><a href="{{ route('approvals.rules') }}"><i class="fa fa-sitemap"></i> Approval Rules</a></li>
              @endrole
            @endauth
            @endif

            {{-- User Management --}}
            @if($showWorkspace(['user_management']))
            @can('view-users')
              <li class="header sidebar-section-header">User Management</li>
              <li><a href="{{ url('/users')}}"><i class="fa fa-users"></i> All Users</a></li>
              @can('create-users')
              <li><a href="{{ url('/users/create')}}"><i class="fa fa-user-plus"></i> Add User</a></li>
              @endcan
              <li><a href="{{ url('/users/roles')}}"><i class="fa fa-id-badge"></i> User Roles</a></li>
            @endcan
            @endif

            {{-- Settings --}}
            @if($showWorkspace(['settings']))
            @role(['administrator', 'developer'])
              <li class="header sidebar-section-header">Settings</li>
              <li><a href="{{ route('system-settings.index', ['workspace' => 'settings']) }}"><i class="fa fa-cog"></i> System Settings</a></li>

              @can('view-users')
              <li><a href="{{ route('users.index', ['workspace' => 'settings']) }}"><i class="fa fa-users"></i> User Accounts</a></li>
              <li><a href="{{ route('users.roles', ['workspace' => 'settings']) }}"><i class="fa fa-id-badge"></i> User Roles &amp; Permissions</a></li>
              @endcan

              <li><a href="{{ route('system.settings', ['workspace' => 'settings']) }}"><i class="fa fa-server"></i> System Info</a></li>
              <li><a href="{{ route('system.permissions', ['workspace' => 'settings']) }}"><i class="fa fa-key"></i> Permissions</a></li>
              <li><a href="{{ route('system.roles', ['workspace' => 'settings']) }}"><i class="fa fa-users"></i> Roles</a></li>
              <li><a href="{{ route('system.maintenance', ['workspace' => 'settings']) }}"><i class="fa fa-wrench"></i> Maintenance</a></li>
              <li><a href="{{ route('system.logs', ['workspace' => 'settings']) }}"><i class="fa fa-file-text"></i> System Logs</a></li>
              <li><a href="{{ route('audit-logs.index', ['workspace' => 'settings']) }}"><i class="fa fa-history"></i> Audit Logs</a></li>

              <li class="header sidebar-section-header sidebar-section-subheader">Admin Tools</li>
              <li><a href="{{ route('admin.dashboard', ['workspace' => 'settings']) }}"><i class="fa fa-dashboard"></i> Admin Dashboard</a></li>
              <li><a href="{{ route('admin.database.index', ['workspace' => 'settings']) }}"><i class="fa fa-database"></i> Database Management</a></li>
              <li><a href="{{ route('admin.cache', ['workspace' => 'settings']) }}"><i class="fa fa-hdd-o"></i> Cache Management</a></li>
              <li><a href="{{ route('admin.backup', ['workspace' => 'settings']) }}"><i class="fa fa-cloud-download"></i> Backup &amp; Restore</a></li>

              @role('developer')
              <li><a href="{{ route('admin.menus.index', ['workspace' => 'settings']) }}"><i class="fa fa-bars"></i> Menu Management</a></li>
              <li><a href="{{ route('notification-settings.index', ['workspace' => 'settings']) }}"><i class="fa fa-bell"></i> Notification Settings</a></li>
              @endrole
            @endrole
            @endif
        </ul>
    </section>
</aside>
