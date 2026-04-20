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
    'assets_management',
    'purchase_request',
    'user_management',
    'settings',
    'kpi',
    'profile',
  ];

  $workspaceFromRoute = null;

  if (\Illuminate\Support\Str::startsWith($routeName, 'tickets.')) {
    $workspaceFromRoute = 'it_support';
  } elseif (\Illuminate\Support\Str::startsWith($routeName, 'meeting-room-bookings.')) {
    $workspaceFromRoute = 'meeting_room';
  } elseif (
    \Illuminate\Support\Str::startsWith($routeName, 'assets.')
    || \Illuminate\Support\Str::startsWith($routeName, 'asset-maintenance.')
    || \Illuminate\Support\Str::startsWith($routeName, 'spares.')
    || \Illuminate\Support\Str::startsWith($routeName, 'models.')
    || \Illuminate\Support\Str::startsWith($routeName, 'pcspecs.')
    || \Illuminate\Support\Str::startsWith($routeName, 'manufacturers.')
    || \Illuminate\Support\Str::startsWith($routeName, 'asset-types.')
    || \Illuminate\Support\Str::startsWith($routeName, 'suppliers.')
    || \Illuminate\Support\Str::startsWith($routeName, 'locations.')
    || \Illuminate\Support\Str::startsWith($routeName, 'divisions.')
    || \Illuminate\Support\Str::startsWith($routeName, 'invoices.')
    || \Illuminate\Support\Str::startsWith($routeName, 'budgets.')
    || \Illuminate\Support\Str::startsWith($routeName, 'masterdata.')
  ) {
    $workspaceFromRoute = 'assets_management';
  } elseif (
    \Illuminate\Support\Str::startsWith($routeName, 'purchase-requests.')
    || \Illuminate\Support\Str::startsWith($routeName, 'asset-requests.')
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

  if (in_array($routeName, ['home', 'portal.index'], true)) {
    $sidebarWorkspace = null;
  }

  $showWorkspace = static function (array $workspaceKeys) use ($sidebarWorkspace): bool {
    return $sidebarWorkspace === null || in_array($sidebarWorkspace, $workspaceKeys, true);
  };

  $workspaceLabels = [
    'it_support' => ['en' => 'IT Support Module', 'id' => 'Modul Dukungan TI'],
    'meeting_room' => ['en' => 'Meeting Room', 'id' => 'Ruang Rapat'],
    'assets_management' => ['en' => 'Assets Management', 'id' => 'Manajemen Aset'],
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
            @if($showWorkspace(['assets_management']))
            @can('view-assets')
              <li class="treeview">
                  <a href="javascript:void(0)"><i class='fa fa-tags'></i> <span>Assets</span> <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                      <li><a href="{{ url('/assets')}}"><i class="fa fa-list"></i> All Assets</a></li>
                      <li><a href="{{ route('assets.my-assets') }}"><i class="fa fa-user"></i> My Assets</a></li>
                      @can('create-assets')
                      <li><a href="{{ url('/asset-maintenance')}}"><i class="fa fa-wrench"></i> Asset Maintenance</a></li>
                      <li><a href="{{ url('/spares')}}"><i class="fa fa-cog"></i> Spares</a></li>
                      @endcan
                      <li><a href="{{ route('assets.scan-qr') }}"><i class="fa fa-qrcode"></i> Scan QR Code</a></li>
                      @can('export-assets')
                      <li><a href="{{ route('assets.export') }}"><i class="fa fa-download"></i> Export Assets</a></li>
                      @endcan
                      @can('import-assets')
                      <li><a href="{{ route('assets.import-form') }}"><i class="fa fa-upload"></i> Import Assets</a></li>
                      @endcan
                  </ul>
              </li>
            @endcan
            @endif

            <!-- 📦 Asset Requests (All authenticated users) -->
            @if($showWorkspace(['purchase_request']))
            @auth
            <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-inbox'></i> <span>Asset Requests</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('asset-requests.index') }}"><i class="fa fa-list"></i> All Requests</a></li>
                    <li><a href="{{ route('asset-requests.create') }}"><i class="fa fa-plus-circle"></i> New Request</a></li>
                </ul>
            </li>
              @endauth
              @endif

            <!-- 📅 Meeting Room Booking (All authenticated users) -->
              @if($showWorkspace(['meeting_room']))
            @auth
            <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-users'></i> <span><i class="fa fa-calendar-check-o"></i> Meeting Room Booking</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('meeting-room-bookings.index') }}"><i class="fa fa-list"></i> All Bookings</a></li>
                    <li><a href="{{ route('meeting-room-bookings.calendar') }}"><i class="fa fa-calendar"></i> Calendar View</a></li>
                    <li><a href="{{ route('meeting-room-bookings.create') }}"><i class="fa fa-plus-circle"></i> New Booking</a></li>
                    
                    {{-- Director Dashboard (Director, Management, Admin & Super-Admin) --}}
                    @role(['director', 'super-admin', 'admin', 'management'])
                    <li><a href="{{ route('meeting-room-bookings.director-dashboard') }}"><i class="fa fa-dashboard text-purple"></i> Director Dashboard</a></li>
                    @endrole
                    
                    {{-- Receptionist Dashboard (Receptionist/Admin/Super-Admin) --}}
                    @role(['receptionist', 'admin', 'super-admin'])
                    <li><a href="{{ route('meeting-room-bookings.receptionist-dashboard') }}"><i class="fa fa-desktop text-green"></i> Receptionist Dashboard</a></li>
                    <li><a href="{{ route('meeting-room-bookings.lcd-settings') }}"><i class="fa fa-sliders text-orange"></i> LCD Settings</a></li>
                    <li><a href="javascript:void(0)" onclick="openMonthlyReportModal()"><i class="fa fa-file-excel-o text-success"></i> Laporan Bulanan (Excel)</a></li>
                    @endrole
                    
                    {{-- LCD Dashboard (Public - All users) --}}
                    <li><a href="{{ route('meeting-room-bookings.lcd-dashboard') }}" target="_blank"><i class="fa fa-tv text-blue"></i> LCD Dashboard</a></li>
                    
                    @role(['director', 'admin', 'super-admin'])
                    <li><a href="{{ route('meeting-room-bookings.index', ['tab' => 'pending']) }}"><i class="fa fa-clock-o"></i> Pending Approval</a></li>
                    @endrole
                </ul>
            </li>
            @endauth
            @endif

      <!-- 🎫 Tickets (visible to any authenticated user; admin subitems still guarded) -->
      @if($showWorkspace(['it_support']))
      @auth
      <li class="treeview">
        <a href="javascript:void(0)"><i class='fa fa-ticket'></i> <span>Tickets</span> <i class="fa fa-angle-left pull-right"></i></a>
        <ul class="treeview-menu">
          <li><a href="{{ url('/tickets')}}"><i class="fa fa-list"></i> All Tickets</a></li>
          @can('assign-tickets')
          <li><a href="{{ url('/tickets/unassigned')}}"><i class="fa fa-inbox"></i> Unassigned Tickets</a></li>
          @endcan
          <li><a href="{{ url('/tickets/create')}}"><i class="fa fa-plus-circle"></i> Create Ticket</a></li>
          @can('export-tickets')
          <li><a href="{{ route('tickets.export') }}"><i class="fa fa-download"></i> Export Tickets</a></li>
          @endcan
        </ul>
      </li>
      @endauth
      @endif
      <!-- 📅 Daily Activity (Admin=2/SuperAdmin=3 full, Management=4 view-only) -->
      @if($showWorkspace(['settings', 'kpi']))
      @can('view-daily-activities')
      <li class="treeview">
        <a href="javascript:void(0)"><i class='fa fa-calendar'></i> <span>Daily Activity</span> <i class="fa fa-angle-left pull-right"></i></a>
        <ul class="treeview-menu">
          <li><a href="{{ url('/daily-activities') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-list"></i> Activity List</a></li>
          <li><a href="{{ url('/daily-activities/calendar') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-calendar-o"></i> Calendar View</a></li>
          @can('create-daily-activities')
          <li><a href="{{ url('/daily-activities/create') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-plus-circle"></i> Add Activity</a></li>
          @endcan
        </ul>
      </li>
      @endcan
      @endif

      <!-- 📋 Reports (management, admin, super-admin) -->
      @if($showWorkspace(['settings', 'kpi']))
      @can('view-reports')
      <li class="treeview">
        <a href="javascript:void(0)"><i class='fa fa-bar-chart'></i> <span>Reports</span> <i class="fa fa-angle-left pull-right"></i></a>
        <ul class="treeview-menu">
          <li><a href="{{ route('kpi.dashboard', ['workspace' => 'kpi']) }}"><i class="fa fa-dashboard"></i> KPI Dashboard</a></li>
          @hasrole('management|admin|super-admin')
          <li><a href="{{ url('/management/dashboard') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-line-chart"></i> Management Dashboard</a></li>
          <li><a href="{{ url('/management/admin-performance') }}{{ $sidebarWorkspace === 'kpi' ? '?workspace=kpi' : '' }}"><i class="fa fa-users"></i> Admin Performance</a></li>
          @endhasrole
        </ul>
      </li>
        @endcan
        @endif

            <!-- 💻 Models & Master Data (SuperAdmin=3 only) -->
          @if($showWorkspace(['assets_management']))
            @can('view-models')
              <li class="treeview">
                  <a href="javascript:void(0)"><i class='fa fa-database'></i> <span>Models & Master Data</span> <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                      <li><a href="{{ url('/models')}}"><i class="fa fa-laptop"></i> Models</a></li>
                      <li><a href="{{ url('/pcspecs')}}"><i class="fa fa-microchip"></i> PC Specifications</a></li>
                      <li><a href="{{ url('/manufacturers')}}"><i class="fa fa-industry"></i> Manufacturers</a></li>
                      <li><a href="{{ url('/asset-types')}}"><i class="fa fa-cubes"></i> Asset Types</a></li>
                      @can('view-suppliers')
                      <li><a href="{{ url('/suppliers')}}"><i class="fa fa-shopping-cart"></i> Suppliers</a></li>
                      @endcan
                      @can('view-locations')
                      <li><a href="{{ url('/locations')}}"><i class="fa fa-building"></i> Locations</a></li>
                      @endcan
                      @can('view-divisions')
                      <li><a href="{{ url('/divisions')}}"><i class="fa fa-group"></i> Divisions</a></li>
                      @endcan
                  </ul>
              </li>
            @endcan
            @endif

            <!-- 💰 Invoices and Budgets (SuperAdmin=3 only) -->
            @if($showWorkspace(['assets_management']))
            @can('view-invoices')
              <li class="treeview">
                  <a href="javascript:void(0)"><i class='fa fa-usd'></i> <span>Invoices and Budgets</span> <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                      <li><a href="{{ url('/invoices')}}"><i class="fa fa-file-text-o"></i> Invoices</a></li>
                      <li><a href="{{ url('/budgets')}}"><i class="fa fa-money"></i> Budgets</a></li>
                  </ul>
              </li>
            @endcan
            @endif

            <!-- 📥📤 Import/Export (admin & super-admin) -->
            @if($showWorkspace(['assets_management']))
            @can('export-data')
            <li class="treeview">
              <a href="javascript:void(0)"><i class='fa fa-exchange'></i> <span>Import/Export</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                @can('export-data')
                <li><a href="{{ route('masterdata.index') }}"><i class="fa fa-download"></i> Export Data</a></li>
                @endcan
                @can('import-data')
                <li><a href="{{ route('masterdata.imports') }}"><i class="fa fa-upload"></i> Import Data</a></li>
                @endcan
                <li><a href="{{ route('masterdata.templates') }}"><i class="fa fa-file-excel-o"></i> Download Templates</a></li>
              </ul>
            </li>
            @endcan
            @endif
            <!-- 👥 User Management (admin & super-admin) -->
            @if($showWorkspace(['user_management']))
            @can('view-users')
            <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-users'></i> <span>User Management</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ url('/users')}}"><i class="fa fa-list"></i> All Users</a></li>
                    @can('create-users')
                    <li><a href="{{ url('/users/create')}}"><i class="fa fa-user-plus"></i> Add User</a></li>
                    @endcan
                    @can('view-users')
                    <li><a href="{{ url('/users/roles')}}"><i class="fa fa-id-badge"></i> User Roles</a></li>
                    @endcan
                </ul>
            </li>
              @endcan
              @endif

            <!-- ⚙️ Settings & AI (admin and super-admin) -->
              @if($showWorkspace(['settings']))
              @role(['admin', 'super-admin'])
              <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-cogs'></i> <span>Settings &amp; AI</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                  @role('super-admin')
                  <li><a href="{{ route('system-settings.index') }}"><i class="fa fa-sliders"></i> System Settings</a></li>
                  <li><a href="{{ route('admin.menus.index') }}"><i class="fa fa-bars"></i> Menu Management</a></li>
                  <li><a href="{{ route('sla.index') }}"><i class="fa fa-clock-o"></i> SLA Policies</a></li>
                  <li><a href="{{ route('sla.dashboard') }}"><i class="fa fa-dashboard"></i> SLA Dashboard</a></li>
                  <li><a href="{{ route('sla.learning.dashboard') }}"><i class="fa fa-graduation-cap"></i> SLA Learning System</a></li>
                  <li class="divider" role="separator"></li>
                  <li><a href="{{ route('system.settings') }}"><i class="fa fa-server"></i> System Info</a></li>
                  <li><a href="{{ route('system.permissions') }}"><i class="fa fa-key"></i> Permissions</a></li>
                  <li><a href="{{ route('system.roles') }}"><i class="fa fa-users"></i> Roles</a></li>
                  <li><a href="{{ route('system.maintenance') }}"><i class="fa fa-wrench"></i> Maintenance</a></li>
                  <li><a href="{{ route('system.logs') }}"><i class="fa fa-file-text"></i> System Logs</a></li>
                  <li class="divider" role="separator"></li>
                  @endrole

                  <li><a href="{{ route('audit-logs.index') }}"><i class="fa fa-history"></i> Audit Logs</a></li>
                  <li><a href="{{ route('audit-logs.export') }}"><i class="fa fa-download"></i> Export Audit Logs</a></li>

                  @role('super-admin')
                  <li class="divider" role="separator"></li>
                  <li><a href="{{ url('/admin/dashboard') }}"><i class="fa fa-dashboard"></i> Admin Dashboard</a></li>
                  <li><a href="{{ url('/admin/database') }}"><i class="fa fa-database"></i> Database Management</a></li>
                  <li><a href="{{ url('/admin/cache') }}"><i class="fa fa-hdd-o"></i> Cache Management</a></li>
                  <li><a href="{{ url('/admin/backup') }}"><i class="fa fa-cloud-download"></i> Backup &amp; Restore</a></li>
                  <li><a href="{{ url('/admin/notification-settings') }}"><i class="fa fa-bell"></i> Notification Settings</a></li>
                  @endrole
                </ul>
              </li>
              @endrole
              @endif
        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
