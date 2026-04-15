<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="header">Navigation</li>            
            <!-- 🏠 Main Portal (All authenticated users) -->
            @auth
              <li><a href="{{ route('home') }}"><i class='fa fa-home'></i> <span>Main Portal</span></a></li>
            @endauth            
            <!-- 🏷️ Assets (Admin=2, SuperAdmin=3, Management=4 view-only) -->
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
            <!-- 📦 Asset Requests (All authenticated users) -->
            @auth
            <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-inbox'></i> <span>Asset Requests</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('asset-requests.index') }}"><i class="fa fa-list"></i> All Requests</a></li>
                    <li><a href="{{ route('asset-requests.create') }}"><i class="fa fa-plus-circle"></i> New Request</a></li>
                </ul>
            </li>
            @endauth            
            <!-- 📅 Meeting Room Booking (All authenticated users) -->
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
      <!-- 🎫 Tickets (visible to any authenticated user; admin subitems still guarded) -->
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
      <!-- 📅 Daily Activity (Admin=2/SuperAdmin=3 full, Management=4 view-only) -->
      @can('view-daily-activities')
      <li class="treeview">
        <a href="javascript:void(0)"><i class='fa fa-calendar'></i> <span>Daily Activity</span> <i class="fa fa-angle-left pull-right"></i></a>
        <ul class="treeview-menu">
          <li><a href="{{ url('/daily-activities')}}"><i class="fa fa-list"></i> Activity List</a></li>
          <li><a href="{{ url('/daily-activities/calendar')}}"><i class="fa fa-calendar-o"></i> Calendar View</a></li>
          @can('create-daily-activities')
          <li><a href="{{ url('/daily-activities/create')}}"><i class="fa fa-plus-circle"></i> Add Activity</a></li>
          @endcan
        </ul>
      </li>
      @endcan
      <!-- 📋 Reports (management, admin, super-admin) -->
      @can('view-reports')
      <li class="treeview">
        <a href="javascript:void(0)"><i class='fa fa-bar-chart'></i> <span>Reports</span> <i class="fa fa-angle-left pull-right"></i></a>
        <ul class="treeview-menu">
          <li><a href="{{ route('kpi.dashboard') }}"><i class="fa fa-dashboard"></i> KPI Dashboard</a></li>
          @hasrole('management|admin|super-admin')
          <li><a href="{{ url('/management/dashboard')}}"><i class="fa fa-line-chart"></i> Management Dashboard</a></li>
          <li><a href="{{ url('/management/admin-performance')}}"><i class="fa fa-users"></i> Admin Performance</a></li>
          @endhasrole
        </ul>
      </li>
      @endcan            
            <!-- 💻 Models & Master Data (SuperAdmin=3 only) -->
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
            <!-- 💰 Invoices and Budgets (SuperAdmin=3 only) -->
            @can('view-invoices')
              <li class="treeview">
                  <a href="javascript:void(0)"><i class='fa fa-usd'></i> <span>Invoices and Budgets</span> <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                      <li><a href="{{ url('/invoices')}}"><i class="fa fa-file-text-o"></i> Invoices</a></li>
                      <li><a href="{{ url('/budgets')}}"><i class="fa fa-money"></i> Budgets</a></li>
                  </ul>
              </li>
            @endcan            
            <!-- 📥📤 Import/Export (admin & super-admin) -->
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
            <!-- 👥 User Management (admin & super-admin) -->
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
            <!-- ⚙️ System Settings (super-admin only) -->
            @role('super-admin')
            <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-cogs'></i> <span>System Settings</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('system-settings.index') }}"><i class="fa fa-th"></i> Settings Overview</a></li>
                    <li><a href="{{ route('admin.menus.index') }}"><i class="fa fa-bars"></i> Menu Management</a></li>
                    <li><a href="{{ route('sla.index') }}"><i class="fa fa-clock-o"></i> SLA Policies</a></li>
                    <li><a href="{{ route('sla.dashboard') }}"><i class="fa fa-dashboard"></i> SLA Dashboard</a></li>
                    <li><a href="{{ route('sla.learning.dashboard') }}"><i class="fa fa-graduation-cap text-purple"></i> SLA Learning System</a></li>
                </ul>
            </li>
            @endrole

            <!-- 🔐 System Management (super-admin only) -->
            @role('super-admin')
            <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-shield'></i> <span>System Management</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('system.settings') }}"><i class="fa fa-server"></i> System Info</a></li>
                    <li><a href="{{ route('system.permissions') }}"><i class="fa fa-key"></i> Permissions</a></li>
                    <li><a href="{{ route('system.roles') }}"><i class="fa fa-users"></i> Roles</a></li>
                    <li><a href="{{ route('system.maintenance') }}"><i class="fa fa-wrench"></i> Maintenance</a></li>
                    <li><a href="{{ route('system.logs') }}"><i class="fa fa-file-text"></i> System Logs</a></li>
                </ul>
            </li>
            @endrole            
            <!-- 📝 Audit Logs (admin & super-admin) -->
            @role(['admin', 'super-admin'])
            <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-history'></i> <span>Audit Logs</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('audit-logs.index') }}"><i class="fa fa-list"></i> View Logs</a></li>
                    <li><a href="{{ route('audit-logs.export') }}"><i class="fa fa-download"></i> Export Logs</a></li>
                </ul>
            </li>
            @endrole            
            <!-- 🔧 Admin Tools (super-admin only) -->
            @role('super-admin')
            <li class="treeview">
                <a href="javascript:void(0)"><i class='fa fa-wrench'></i> <span>Admin Tools</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ url('/admin/dashboard')}}"><i class="fa fa-dashboard"></i> Admin Dashboard</a></li>
                    <li><a href="{{ url('/admin/database')}}"><i class="fa fa-database"></i> Database Management</a></li>
                    <li><a href="{{ url('/admin/cache')}}"><i class="fa fa-hdd-o"></i> Cache Management</a></li>
                    <li><a href="{{ url('/admin/backup')}}"><i class="fa fa-cloud-download"></i> Backup & Restore</a></li>
                    <li><a href="{{ url('/admin/notification-settings')}}"><i class="fa fa-bell"></i> Notification Settings</a></li>
                </ul>
            </li>
            @endrole
        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
