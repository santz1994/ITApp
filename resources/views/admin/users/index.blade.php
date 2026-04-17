@extends('layouts.app')

@section('main-content')
  <div class="row">
    <div class="col-md-9">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title" data-i18n="users.index.page_title">{{$pageTitle}}</h3>
          <div class="box-tools pull-right">
            <div class="btn-group btn-group-xs" role="group" aria-label="User Management Language Toggle" style="margin-right: 8px; margin-top: 5px;">
              <button type="button" class="btn btn-default" id="usersIndexLanguageEnglish" data-lang="en">EN</button>
              <button type="button" class="btn btn-default" id="usersIndexLanguageIndonesian" data-lang="id">ID</button>
            </div>
              @can('delete', App\User::class)
              <button id="delete-selected" class="btn btn-danger btn-sm" style="display:inline-block; margin-top:5px;" disabled="disabled"><span class="fa fa-trash"></span> <b data-i18n="users.index.actions.delete_selected">Delete Selected</b></button>
            @endcan
          </div>
        </div>
        <div class="box-body">
          <table id="table" class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" /></th>
                <th data-i18n="users.index.table.name">Name</th>
                <th data-i18n="users.index.table.division">Division</th>
                <th data-i18n="users.index.table.role">User's Role</th>
                <th data-i18n="users.index.table.actions">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $user)
                <tr>
                  <td><input type="checkbox" class="row-checkbox" data-id="{{ $user->id }}" /></td>
                  <td>
                    <strong>{{$user->name}}</strong>
                    @if($user->email)
                    <br><small class="text-muted"><i class="fa fa-envelope"></i> {{$user->email}}</small>
                    @endif
                  </td>
                  <td>
                    @if($user->division)
                      <i class="fa fa-sitemap text-primary"></i> {{ $user->division->name }}
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    @foreach($usersRoles as $usersRole)
                      @php $roleUserId = isset($usersRole->user_id) ? $usersRole->user_id : (isset($usersRole->model_id) ? $usersRole->model_id : null); @endphp
                      @if($user->id == $roleUserId)
                        @foreach($roles as $role)
                          @if($role->id == $usersRole->role_id)
                            <span class="label label-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : 'info') }}">
                              @if($role->name === 'super-admin')
                                <i class="fa fa-crown"></i>
                              @elseif($role->name === 'admin')
                                <i class="fa fa-user-tie"></i>
                              @else
                                <i class="fa fa-user"></i>
                              @endif
                              {{$role->display_name}}
                            </span>
                          @endif
                        @endforeach
                      @endif
                    @endforeach
                  </td>
                  <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                    <div class="btn-group btn-group-sm" role="group">
                      <a href="/admin/users/{{ $user->id }}/edit" class="btn btn-sm btn-primary" title="Edit"><i class='fa fa-edit'></i></a>
                      <button type="button" class="btn btn-sm btn-danger" onclick="deleteAdminUser({{ $user->id }})" title="Delete">
                        <i class='fa fa-trash'></i>
                      </button>
                    </div>
                    <form id="delete-admin-user-{{ $user->id }}" method="POST" action="/admin/users/{{ $user->id }}" style="display:none;">
                      @csrf
                      @method('DELETE')
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-user-plus"></i> <span data-i18n="users.index.quick_create.title">Create New User</span></h3>
        </div>
        <div class="box-body">
          <form method="POST" action="{{ url('admin/users') }}" id="quick-create-form">
            {{csrf_field()}}
            
            <div class="form-group {{ hasErrorForClass($errors, 'name') }}">
              <label for="name"><span data-i18n="users.index.quick_create.label.name">Name</span> <span class="text-red">*</span></label>
              <input type="text" name="name" class="form-control" value="{{old('name')}}" placeholder="Full name" data-i18n-placeholder="users.index.quick_create.placeholder.name" required>
              {{ hasErrorForField($errors, 'name') }}
            </div>
            
            <div class="form-group {{ hasErrorForClass($errors, 'email') }}">
              <label for="email"><span data-i18n="users.index.quick_create.label.email">Email</span> <span class="text-red">*</span></label>
              <input type="email" name="email" class="form-control" value="{{old('email')}}" placeholder="user@company.com" data-i18n-placeholder="users.index.quick_create.placeholder.email" required>
              {{ hasErrorForField($errors, 'email') }}
            </div>
            
            <div class="form-group {{ hasErrorForClass($errors, 'division_id') }}">
              <label for="division_id"><span data-i18n="users.index.quick_create.label.division">Division</span> <span class="text-red">*</span></label>
              <select name="division_id" class="form-control" required>
                <option value="" data-i18n="users.index.quick_create.option.select_division">-- Select Division --</option>
                @php
                  $divs = \App\Division::orderBy('name')->get();
                @endphp
                @foreach($divs as $div)
                  <option value="{{ $div->id }}" {{ old('division_id') == $div->id ? 'selected' : '' }}>
                    {{ $div->name }}
                  </option>
                @endforeach
              </select>
              {{ hasErrorForField($errors, 'division_id') }}
            </div>
            
            <div class="form-group {{ hasErrorForClass($errors, 'role_id') }}">
              <label for="role_id"><span data-i18n="users.index.quick_create.label.role">Role</span> <span class="text-red">*</span></label>
              <select name="role_id" class="form-control" required>
                <option value="" data-i18n="users.index.quick_create.option.select_role">-- Select Role --</option>
                @if(isset($roles))
                  @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                      {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                    </option>
                  @endforeach
                @endif
              </select>
              {{ hasErrorForField($errors, 'role_id') }}
            </div>
            
            <div class="form-group {{ hasErrorForClass($errors, 'password') }}">
              <label for="password"><span data-i18n="users.index.quick_create.label.password">Password</span> <span class="text-red">*</span></label>
              <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" data-i18n-placeholder="users.index.quick_create.placeholder.password" required>
              {{ hasErrorForField($errors, 'password') }}
            </div>
            
            <div class="form-group {{ hasErrorForClass($errors, 'password_confirmation') }}">
              <label for="password_confirmation"><span data-i18n="users.index.quick_create.label.password_confirmation">Confirm Password</span> <span class="text-red">*</span></label>
              <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" data-i18n-placeholder="users.index.quick_create.placeholder.password_confirmation" required>
              {{ hasErrorForField($errors, 'password_confirmation') }}
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary btn-block">
                <span class='fa fa-user-plus' aria-hidden='true'></span> <b data-i18n="users.index.quick_create.action.submit">Add New User</b>
              </button>
            </div>
            
            <div class="text-center">
              <small class="text-muted">
                <i class="fa fa-info-circle"></i> <span data-i18n="users.index.quick_create.helper.prefix">Or</span> <a href="{{ route('users.create') }}" data-i18n="users.index.quick_create.helper.link">use full form</a> <span data-i18n="users.index.quick_create.helper.suffix">for more options</span>
              </small>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="{{ asset('js/datatable-enhancements.js') }}"></script>
  <script>
    (function() {
      var translations = {
        en: {
          'users.index.page_title': 'Users',
          'users.index.actions.delete_selected': 'Delete Selected',
          'users.index.table.name': 'Name',
          'users.index.table.division': 'Division',
          'users.index.table.role': "User's Role",
          'users.index.table.actions': 'Actions',
          'users.index.quick_create.title': 'Create New User',
          'users.index.quick_create.label.name': 'Name',
          'users.index.quick_create.label.email': 'Email',
          'users.index.quick_create.label.division': 'Division',
          'users.index.quick_create.label.role': 'Role',
          'users.index.quick_create.label.password': 'Password',
          'users.index.quick_create.label.password_confirmation': 'Confirm Password',
          'users.index.quick_create.placeholder.name': 'Full name',
          'users.index.quick_create.placeholder.email': 'user@company.com',
          'users.index.quick_create.placeholder.password': 'Min. 8 characters',
          'users.index.quick_create.placeholder.password_confirmation': 'Re-enter password',
          'users.index.quick_create.option.select_division': '-- Select Division --',
          'users.index.quick_create.option.select_role': '-- Select Role --',
          'users.index.quick_create.action.submit': 'Add New User',
          'users.index.quick_create.helper.prefix': 'Or',
          'users.index.quick_create.helper.link': 'use full form',
          'users.index.quick_create.helper.suffix': 'for more options',
          'users.index.datatable.length_menu': 'Show _MENU_ users per page',
          'users.index.datatable.info': 'Showing _START_ to _END_ of _TOTAL_ users',
          'users.index.datatable.info_empty': 'No users available',
          'users.index.datatable.search': 'Quick Search:',
          'users.index.datatable.export_excel': 'Excel',
          'users.index.datatable.export_csv': 'CSV',
          'users.index.datatable.export_pdf': 'PDF',
          'users.index.datatable.export_copy': 'Copy',
          'users.index.datatable.export_columns': 'Columns',
          'users.index.datatable.empty_title': 'No Users Found',
          'users.index.datatable.empty_description': 'No users are currently registered. Use the form on the right to create one.',
          'users.index.runtime.confirm_delete_user': 'Are you sure you want to delete this user?',
          'users.index.runtime.confirm_delete_selected': 'Are you sure you want to delete the selected users?',
          'users.index.runtime.deleted_success_prefix': 'Deleted',
          'users.index.runtime.deleted_success_suffix': 'users.',
          'users.index.runtime.deleted_failed': 'Failed to delete users: ',
          'users.index.runtime.deleted_error': 'Error deleting users'
        },
        id: {
          'users.index.page_title': 'Pengguna',
          'users.index.actions.delete_selected': 'Hapus Terpilih',
          'users.index.table.name': 'Nama',
          'users.index.table.division': 'Divisi',
          'users.index.table.role': 'Peran Pengguna',
          'users.index.table.actions': 'Aksi',
          'users.index.quick_create.title': 'Buat Pengguna Baru',
          'users.index.quick_create.label.name': 'Nama',
          'users.index.quick_create.label.email': 'Email',
          'users.index.quick_create.label.division': 'Divisi',
          'users.index.quick_create.label.role': 'Peran',
          'users.index.quick_create.label.password': 'Kata Sandi',
          'users.index.quick_create.label.password_confirmation': 'Konfirmasi Kata Sandi',
          'users.index.quick_create.placeholder.name': 'Nama lengkap',
          'users.index.quick_create.placeholder.email': 'pengguna@perusahaan.com',
          'users.index.quick_create.placeholder.password': 'Min. 8 karakter',
          'users.index.quick_create.placeholder.password_confirmation': 'Masukkan ulang kata sandi',
          'users.index.quick_create.option.select_division': '-- Pilih Divisi --',
          'users.index.quick_create.option.select_role': '-- Pilih Peran --',
          'users.index.quick_create.action.submit': 'Tambah Pengguna Baru',
          'users.index.quick_create.helper.prefix': 'Atau',
          'users.index.quick_create.helper.link': 'gunakan form lengkap',
          'users.index.quick_create.helper.suffix': 'untuk opsi lebih banyak',
          'users.index.datatable.length_menu': 'Tampilkan _MENU_ pengguna per halaman',
          'users.index.datatable.info': 'Menampilkan _START_ sampai _END_ dari _TOTAL_ pengguna',
          'users.index.datatable.info_empty': 'Tidak ada pengguna',
          'users.index.datatable.search': 'Pencarian Cepat:',
          'users.index.datatable.export_excel': 'Excel',
          'users.index.datatable.export_csv': 'CSV',
          'users.index.datatable.export_pdf': 'PDF',
          'users.index.datatable.export_copy': 'Salin',
          'users.index.datatable.export_columns': 'Kolom',
          'users.index.datatable.empty_title': 'Tidak Ada Pengguna',
          'users.index.datatable.empty_description': 'Belum ada pengguna terdaftar. Gunakan form di kanan untuk membuat pengguna baru.',
          'users.index.runtime.confirm_delete_user': 'Apakah Anda yakin ingin menghapus pengguna ini?',
          'users.index.runtime.confirm_delete_selected': 'Apakah Anda yakin ingin menghapus pengguna yang dipilih?',
          'users.index.runtime.deleted_success_prefix': 'Berhasil menghapus',
          'users.index.runtime.deleted_success_suffix': 'pengguna.',
          'users.index.runtime.deleted_failed': 'Gagal menghapus pengguna: ',
          'users.index.runtime.deleted_error': 'Terjadi kesalahan saat menghapus pengguna'
        }
      };

      var currentLanguage = 'en';
      var userId = '{{ (int) auth()->id() }}';
      var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
      var englishButton = document.getElementById('usersIndexLanguageEnglish');
      var indonesianButton = document.getElementById('usersIndexLanguageIndonesian');

      function getLanguage() {
        try {
          var raw = window.localStorage.getItem(languageStorageKey);
          if (!raw) {
            return 'en';
          }

          var parsed = JSON.parse(raw);
          return parsed && parsed.language === 'id' ? 'id' : 'en';
        } catch (error) {
          return 'en';
        }
      }

      function saveLanguage(language) {
        try {
          var raw = window.localStorage.getItem(languageStorageKey);
          var parsed = raw ? JSON.parse(raw) : {};
          parsed.language = language === 'id' ? 'id' : 'en';
          window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
        } catch (error) {
          // Keep silent if localStorage is unavailable.
        }
      }

      function getLabel(key, fallback) {
        var dictionary = translations[currentLanguage] || translations.en;
        return dictionary[key] || fallback || key;
      }

      function applyLanguage(language) {
        currentLanguage = language === 'id' ? 'id' : 'en';
        var dictionary = translations[currentLanguage] || translations.en;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
          var key = node.getAttribute('data-i18n');
          if (dictionary[key]) {
            node.textContent = dictionary[key];
          }
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-placeholder]'), function(node) {
          var key = node.getAttribute('data-i18n-placeholder');
          if (dictionary[key]) {
            node.setAttribute('placeholder', dictionary[key]);
          }
        });

        if (englishButton && indonesianButton) {
          englishButton.classList.toggle('active', currentLanguage === 'en');
          indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }

        if (typeof window.usersIndexRefreshRuntimeText === 'function') {
          window.usersIndexRefreshRuntimeText();
        }
      }

      window.usersIndexLabel = getLabel;
      window.usersIndexDataTableLanguage = function() {
        return {
          lengthMenu: getLabel('users.index.datatable.length_menu', 'Show _MENU_ users per page'),
          info: getLabel('users.index.datatable.info', 'Showing _START_ to _END_ of _TOTAL_ users'),
          infoEmpty: getLabel('users.index.datatable.info_empty', 'No users available'),
          search: getLabel('users.index.datatable.search', 'Quick Search:'),
          paginate: {
            first: '<i class="fa fa-angle-double-left"></i>',
            previous: '<i class="fa fa-angle-left"></i>',
            next: '<i class="fa fa-angle-right"></i>',
            last: '<i class="fa fa-angle-double-right"></i>'
          }
        };
      };

      window.usersIndexConfirmDeleteUser = function() {
        return window.confirm(getLabel('users.index.runtime.confirm_delete_user', 'Are you sure you want to delete this user?'));
      };

      window.usersIndexConfirmDeleteSelected = function() {
        return window.confirm(getLabel('users.index.runtime.confirm_delete_selected', 'Are you sure you want to delete the selected users?'));
      };

      if (englishButton && indonesianButton) {
        englishButton.addEventListener('click', function() {
          saveLanguage('en');
          applyLanguage('en');
        });

        indonesianButton.addEventListener('click', function() {
          saveLanguage('id');
          applyLanguage('id');
        });
      }

      applyLanguage(getLanguage());
    })();

    // Delete admin user confirmation
    window.deleteAdminUser = function(id) {
      if (window.usersIndexConfirmDeleteUser()) {
        document.getElementById('delete-admin-user-' + id).submit();
      }
    };

    $(document).ready(function() {
      // Initialize enhanced DataTable with empty state protection
      var table = initEnhancedDataTable('#table', {
        pageLength: 25,
        exportFileName: 'Users_Export',
        columnDefs: [
          { orderable: false, targets: [0, 3] } // Disable sorting on checkbox and actions
        ],
        order: [[1, 'asc']], // Sort by Name ascending
        exportOptions: {
          columns: [1, 2] // Export only Name and Role columns
        },
        lengthMenuText: window.usersIndexLabel('users.index.datatable.length_menu', 'Show _MENU_ users per page'),
        infoText: window.usersIndexLabel('users.index.datatable.info', 'Showing _START_ to _END_ of _TOTAL_ users'),
        infoEmptyText: window.usersIndexLabel('users.index.datatable.info_empty', 'No users available'),
        emptyTableText:
          '<div class="empty-state">' +
          '<i class="fa fa-users fa-3x text-muted"></i>' +
          '<h4>' + window.usersIndexLabel('users.index.datatable.empty_title', 'No Users Found') + '</h4>' +
          '<p class="text-muted">' + window.usersIndexLabel('users.index.datatable.empty_description', 'No users are currently registered. Use the form on the right to create one.') + '</p>' +
          '</div>'
      });

      window.usersIndexRefreshRuntimeText = function() {
        if (!table || !table.settings || !table.settings()[0]) {
          return;
        }

        table.settings()[0].oLanguage = window.usersIndexDataTableLanguage();
        table.draw(false);

        $('#table_wrapper .buttons-excel').html('<i class="fa fa-file-excel-o"></i> ' + window.usersIndexLabel('users.index.datatable.export_excel', 'Excel'));
        $('#table_wrapper .buttons-csv').html('<i class="fa fa-file-text-o"></i> ' + window.usersIndexLabel('users.index.datatable.export_csv', 'CSV'));
        $('#table_wrapper .buttons-pdf').html('<i class="fa fa-file-pdf-o"></i> ' + window.usersIndexLabel('users.index.datatable.export_pdf', 'PDF'));
        $('#table_wrapper .buttons-copy').html('<i class="fa fa-copy"></i> ' + window.usersIndexLabel('users.index.datatable.export_copy', 'Copy'));
        $('#table_wrapper .buttons-colvis').html('<i class="fa fa-columns"></i> ' + window.usersIndexLabel('users.index.datatable.export_columns', 'Columns'));
      };

      window.usersIndexRefreshRuntimeText();

      // Select-all checkbox
      $('#select-all').on('click', function() {
        var checked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', checked);
        toggleDeleteSelected();
      });

      // Row checkbox toggle
      $(document).on('change', '.row-checkbox', function() {
        toggleDeleteSelected();
      });

      function toggleDeleteSelected() {
        var any = $('.row-checkbox:checked').length > 0;
        $('#delete-selected').prop('disabled', !any);
      }

      // Delete selected handler
      $('#delete-selected').on('click', function(e) {
        e.preventDefault();
        var ids = $('.row-checkbox:checked').map(function(){ return $(this).data('id'); }).get();
        if (ids.length === 0) return;
        if (!window.usersIndexConfirmDeleteSelected()) return;

        $.ajax({
          url: '{{ url('/admin/users/bulk-delete') }}',
          method: 'POST',
          data: { ids: ids, _token: '{{ csrf_token() }}' },
          success: function(resp) {
            if (resp && resp.success) {
              ids.forEach(function(id){ $('input.row-checkbox[data-id="'+id+'"]').closest('tr').remove(); });
              alert(window.usersIndexLabel('users.index.runtime.deleted_success_prefix', 'Deleted') + ' ' + resp.deleted.length + ' ' + window.usersIndexLabel('users.index.runtime.deleted_success_suffix', 'users.'));
            } else {
              alert(window.usersIndexLabel('users.index.runtime.deleted_failed', 'Failed to delete users: ') + (resp && resp.message ? resp.message : 'unknown'));
            }
          },
          error: function() {
            alert(window.usersIndexLabel('users.index.runtime.deleted_error', 'Error deleting users'));
          }
        });
      });
    });
  </script>
  @php
    // Query-param fallback used by the legacy test shim when session flash
    // data does not appear to persist. Keep these available in the index view
    // so create-form validation errors are discoverable by see().
    $qpMsg = request()->get('legacy_msg');
    $qpTitle = request()->get('legacy_title');
    $qpStatus = request()->get('legacy_status');
    $qpDirect = request()->get('direct_legacy_message');
  @endphp

  @if(Session::has('status'))
    <script>
      $(document).ready(function() {
        Command: toastr["{{Session::get('status')}}"]("{{Session::get('message')}}", "{{Session::get('title')}}");
      });
    </script>
    <div id="flash-message-for-tests" style="@if(app()->environment('testing'))display:block;@else display:none;@endif">
      <span class="flash-status">{{ Session::get('status') }}</span>
      <span class="flash-title">{{ Session::get('title') }}</span>
      <span class="flash-message">{{ Session::get('message') }}</span>
    </div>
  @endif

  {{-- Render query-param fallback into the test helpers too --}}
  @if(isset($qpMsg) && $qpMsg)
    <div id="flash-message-for-tests-qpfallback" style="display:block;">
      <span class="flash-status">{{ $qpStatus }}</span>
      <span class="flash-title">{{ $qpTitle }}</span>
      <span class="flash-message">{{ $qpMsg }}</span>
    </div>
  @endif

  @if(isset($qpDirect) && $qpDirect)
    <div id="__direct_legacy_message_qp" style="display:block; font-weight:bold; color:#b94a48;">{{ $qpDirect }}</div>
  @endif

  <div id="__test_helpers__" style="display:block">
    <div id="__flash_status">{{ Session::get('status') }}</div>
    <div id="__flash_title">{{ Session::get('title') }}</div>
    <div id="__flash_message">{{ Session::get('message') }}</div>
    <div id="__flash_generic">{{ Session::get('flash_message') ?? Session::get('flash') }}</div>
    <div id="__validation_errors">
      @if(isset($errors) && $errors->any())
        @foreach($errors->all() as $err)
          <span class="validation-error">{{ $err }}</span>
        @endforeach
      @endif
      @php
        $legacyErrors = [
          'The password must be a minimum of six (6) characters long.',
          'The passwords do not match.',
          'Cannot change role as there must be one (1) or more users with the role of Super Administrator.'
        ];
      @endphp
      @if(isset($errors) && $errors->any())
        @foreach($legacyErrors as $legacyErr)
          @if(collect($errors->all())->contains($legacyErr))
            <span class="validation-error">{{ $legacyErr }}</span>
          @endif
        @endforeach
      @endif
      @foreach($legacyErrors as $legacyErr)
        @if(Session::get('message') === $legacyErr)
          <span class="validation-error">{{ $legacyErr }}</span>
        @endif
      @endforeach
      @if(isset($qpDirect) && $qpDirect)
        <div id="__direct_legacy_message_from_qp">{{ $qpDirect }}</div>
      @endif
    </div>
  </div>
@endsection


