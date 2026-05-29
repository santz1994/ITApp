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
    'it_support',
    'meeting_room',
    'purchase_request',
    'user_management',
    'settings',
    'kpi',
    'profile',
  ];

  $workspaceFromRoute = null;

  if (\Illuminate\Support\Str::startsWith($routeName, 'meeting-room-bookings.')) {
    $workspaceFromRoute = 'meeting_room';
  } elseif (
    \Illuminate\Support\Str::startsWith($routeName, 'purchase-requests.')
  ) {
    $workspaceFromRoute = 'purchase_request';
  } elseif (
    \Illuminate\Support\Str::startsWith($routeName, 'users.')
    || \Illuminate\Support\Str::startsWith($routeName, 'admin.users.')
  ) {
    $workspaceFromRoute = 'user_management';
  } elseif (
    \Illuminate\Support\Str::startsWith($routeName, 'kpi.')
    || \Illuminate\Support\Str::startsWith($routeName, 'management.')
  ) {
    $workspaceFromRoute = 'kpi';
  } elseif (
    \Illuminate\Support\Str::startsWith($routeName, 'system-settings.')
    || \Illuminate\Support\Str::startsWith($routeName, 'system.')
    || \Illuminate\Support\Str::startsWith($routeName, 'audit-logs.')
    || \Illuminate\Support\Str::startsWith($routeName, 'admin.')
    || \Illuminate\Support\Str::startsWith($routeName, 'sla.')
    || \Illuminate\Support\Str::startsWith($routeName, 'daily-activities.')
  ) {
    $workspaceFromRoute = 'settings';
  } elseif (\Illuminate\Support\Str::startsWith($routeName, 'profile.')) {
    $workspaceFromRoute = 'profile';
  }

  $sidebarWorkspace = in_array($requestedWorkspace, $validWorkspaces, true)
    ? $requestedWorkspace
    : (in_array((string) $workspaceFromRoute, $validWorkspaces, true) ? $workspaceFromRoute : null);

  // Prevent stale URL params from forcing Settings menu inside KPI workspace pages.
  if ($workspaceFromRoute === 'kpi') {
    $sidebarWorkspace = 'kpi';
  }

  if (in_array($routeName, ['home', 'portal.index'], true)) {
    $sidebarWorkspace = null;
  }

  $showWorkspace = static function (array $workspaceKeys) use ($sidebarWorkspace): bool {
    return $sidebarWorkspace === null || in_array($sidebarWorkspace, $workspaceKeys, true);
  };

  $workspaceLabels = [
    'it_support' => ['en' => 'IT Support Module', 'id' => 'Modul Dukungan TI'],
    'meeting_room' => ['en' => 'Meeting Room', 'id' => 'Ruang Rapat'],
    'purchase_request' => ['en' => 'Purchase Request', 'id' => 'Permintaan Pengadaan'],
    'user_management' => ['en' => 'User Management', 'id' => 'Manajemen Pengguna'],
    'settings' => ['en' => 'Settings', 'id' => 'Pengaturan'],
    'kpi' => ['en' => 'KPI Workspace', 'id' => 'Ruang Kerja KPI'],
    'profile' => ['en' => 'Profile', 'id' => 'Profil'],
  ];
@endphp

<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="header" data-i18n="sidebar.navigation">{{ $sidebarLabels['navigation'][$sidebarLanguage] }}</li>
            @if($sidebarWorkspace !== null)
              <li class="header">
                <span data-i18n="sidebar.workspace">{{ $sidebarLabels['workspace'][$sidebarLanguage] }}</span>:
                <span data-i18n="sidebar.workspace.{{ $sidebarWorkspace }}">{{ $workspaceLabels[$sidebarWorkspace][$sidebarLanguage] ?? ($workspaceLabels[$sidebarWorkspace]['en'] ?? 'Module') }}</span>
              </li>
            @endif
            <!-- 🏠 Main Portal (All authenticated users) -->
            @auth
              <li><a href="{{ route('home') }}"><i class='fa fa-home'></i> <span data-i18n="sidebar.main_portal">{{ $sidebarLabels['main_portal'][$sidebarLanguage] }}</span></a></li>
            @endauth            

            @if($showWorkspace(['profile']))
            @if(Route::has('profile.edit'))
            @auth
            <li><a href="{{ route('profile.edit') }}"><i class='fa fa-user-circle'></i> <span data-i18n="sidebar.profile">{{ $sidebarLabels['profile'][$sidebarLanguage] }}</span></a></li>
            @endauth
            @endif
            @endif

            <!-- 🏷️ Assets (Admin=2, SuperAdmin=3, Management=4 view-only) -->
            <!-- Assets & Spares removed (legacy) -->

            <!-- Asset request menu removed -->

            <!-- 📅 Meeting Room Booking (All authenticated users) -->
            @if($showWorkspace(['meeting_room']))
            @auth
              <li class="header sidebar-section-header">Meeting Room Booking</li>
              <li><a href="{{ route('meeting-room-bookings.index') }}"><i class="fa fa-calendar-check-o"></i> All Bookings</a></li>
              <li><a href="{{ route('meeting-room-bookings.calendar') }}"><i class="fa fa-calendar"></i> Calendar View</a></li>
              <li><a href="{{ route('meeting-room-bookings.create') }}"><i class="fa fa-plus-circle"></i> New Booking</a></li>

              {{-- Director Dashboard (Director, Management, Admin & Super-Admin) --}}
              @role(['director', 'developer', 'administrator'])
              <li><a href="{{ route('meeting-room-bookings.director-dashboard') }}"><i class="fa fa-dashboard text-purple"></i> Director Dashboard</a></li>
              @endrole

              {{-- Receptionist Dashboard (Receptionist/Admin/Super-Admin) --}}
              @role(['receptionist', 'administrator', 'developer'])
              <li><a href="{{ route('meeting-room-bookings.receptionist-dashboard') }}"><i class="fa fa-desktop text-green"></i> Receptionist Dashboard</a></li>
              <li><a href="{{ route('meeting-room-bookings.lcd-settings') }}"><i class="fa fa-sliders text-orange"></i> LCD Settings</a></li>
              <li><a href="#" data-action="open-monthly-report-modal"><i class="fa fa-file-excel-o text-success"></i> Laporan Bulanan (Excel)</a></li>
              @endrole

              {{-- LCD Dashboard (Public - All users) --}}
              <li><a href="{{ route('meeting-room-bookings.lcd-dashboard') }}" target="_blank"><i class="fa fa-tv text-blue"></i> LCD Dashboard</a></li>

              @role(['director', 'administrator', 'developer'])
              <li><a href="{{ route('meeting-room-bookings.index', ['tab' => 'pending']) }}"><i class="fa fa-clock-o"></i> Pending Approval</a></li>
              @endrole
            @endauth
            @endif

            <!-- Tickets module removed (legacy) -->

            <!-- 📅 Daily Activity (Admin=2/SuperAdmin=3 full, Management=4 view-only) -->
            @if($showWorkspace(['kpi']))
            @can('view-daily-activities')
              <li class="header sidebar-section-header">Daily Activity</li>
              <li><a href="{{ url('/daily-activities') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-calendar"></i> Activity List</a></li>
              <li><a href="{{ url('/daily-activities/calendar') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-calendar-o"></i> Calendar View</a></li>
              @can('create-daily-activities')
              <li><a href="{{ url('/daily-activities/create') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-plus-circle"></i> Add Activity</a></li>
              @endcan
            @endcan
            @endif

            <!-- 📋 Reports (management, admin, super-admin) -->
            @if($showWorkspace(['kpi']))
            @can('view-reports')
              <li class="header sidebar-section-header">Reports</li>
              <li><a href="{{ route('kpi.dashboard', ['workspace' => 'kpi']) }}"><i class="fa fa-bar-chart"></i> KPI Dashboard</a></li>
              @hasrole('director|administrator|developer')
              <li><a href="{{ url('/management/dashboard') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-line-chart"></i> Management Dashboard</a></li>
              <li><a href="{{ url('/management/admin-performance') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-users"></i> Admin Performance</a></li>
              @endhasrole
            @endcan
            @endif

            <!-- Legacy asset/model/invoice/budget/import-export menus removed -->

            <!-- 👥 User Management (admin & super-admin) -->
            @if($showWorkspace(['user_management']))
            @can('view-users')
              <li class="header sidebar-section-header">User Management</li>
              <li><a href="{{ url('/users')}}"><i class="fa fa-users"></i> All Users</a></li>
              @can('create-users')
              <li><a href="{{ url('/users/create')}}"><i class="fa fa-user-plus"></i> Add User</a></li>
              @endcan
              @can('view-users')
              <li><a href="{{ url('/users/roles')}}"><i class="fa fa-id-badge"></i> User Roles</a></li>
              @endcan
            @endcan
            @endif

            <!-- ⚙️ Settings & AI (admin and super-admin) -->
            @if($showWorkspace(['settings']))
            @role(['administrator', 'developer'])
              <li class="header sidebar-section-header">Settings &amp; AI</li>

              <li class="header sidebar-section-header sidebar-section-subheader">Application &amp; AI</li>
              <li><a href="{{ route('system-settings.index', ['workspace' => 'settings']) }}"><i class="fa fa-cog"></i> System Settings</a></li>
              <li><a href="{{ route('sla.index', ['workspace' => 'settings']) }}"><i class="fa fa-clock-o"></i> SLA Policies</a></li>
              <li><a href="{{ route('sla.dashboard', ['workspace' => 'settings']) }}"><i class="fa fa-line-chart"></i> SLA Dashboard</a></li>
              <li><a href="{{ route('sla.learning.dashboard', ['workspace' => 'settings']) }}"><i class="fa fa-graduation-cap"></i> AI Management</a></li>

              @can('view-users')
              <li class="header sidebar-section-header sidebar-section-subheader">User Control</li>
              <li><a href="{{ route('users.index', ['workspace' => 'settings']) }}"><i class="fa fa-users"></i> User Accounts</a></li>
              @can('create-users')
              <li><a href="{{ route('users.create', ['workspace' => 'settings']) }}"><i class="fa fa-user-plus"></i> Add User</a></li>
              @endcan
              <li><a href="{{ route('users.roles', ['workspace' => 'settings']) }}"><i class="fa fa-id-badge"></i> User Roles &amp; Permissions</a></li>
              @endcan

              <li class="header sidebar-section-header sidebar-section-subheader">Governance</li>
              <li><a href="{{ route('system.settings', ['workspace' => 'settings']) }}"><i class="fa fa-server"></i> System Info</a></li>
              <li><a href="{{ route('system.permissions', ['workspace' => 'settings']) }}"><i class="fa fa-key"></i> Permissions</a></li>
              <li><a href="{{ route('system.roles', ['workspace' => 'settings']) }}"><i class="fa fa-users"></i> Roles</a></li>
              <li><a href="{{ route('system.maintenance', ['workspace' => 'settings']) }}"><i class="fa fa-wrench"></i> Maintenance</a></li>
              <li><a href="{{ route('system.logs', ['workspace' => 'settings']) }}"><i class="fa fa-file-text"></i> System Logs</a></li>
              <li><a href="{{ route('audit-logs.index', ['workspace' => 'settings']) }}"><i class="fa fa-history"></i> Audit Logs</a></li>
              <li><a href="{{ route('audit-logs.export', ['workspace' => 'settings']) }}"><i class="fa fa-download"></i> Export Audit Logs</a></li>

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
        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
