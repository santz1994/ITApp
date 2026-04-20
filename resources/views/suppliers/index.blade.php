@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

@include('components.page-header', [
    'title' => 'Suppliers',
    'subtitle' => 'Manage vendors and suppliers',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Suppliers']
    ]
])

@include('layouts.partials.module-toolbar', [
  'englishButtonId' => 'supplierIndexLanguageEnglish',
  'indonesianButtonId' => 'supplierIndexLanguageIndonesian',
  'ariaLabel' => 'Supplier Index Language Toggle',
])

<div class="container-fluid">
  {{-- Flash Messages --}}
  @if(session('success') || Session::get('status') == 'success')
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-check"></i> {{ session('success') ?? Session::get('message') ?? 'Operation completed successfully!' }}
    </div>
  @endif

  @if(session('error') || Session::get('status') == 'error')
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-ban"></i> {{ session('error') ?? Session::get('message') ?? 'An error occurred!' }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-warning"></i> <span data-i18n="suppliers.index.alert.validation">Please correct the following errors:</span></h4>
      <ul style="margin-bottom: 0;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row">
    <div class="col-md-9">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">
            <i class="fa fa-truck"></i> <span data-i18n="suppliers.index.table.title">All Suppliers</span>
            <span class="badge bg-blue count-badge">{{ count($suppliers) }}</span>
          </h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
              <i class="fa fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="box-body">
          <table id="table" class="table table-striped table-bordered table-hover table-enhanced">
            <thead>
              <tr>
                <th style="width: 60px;"><i class="fa fa-hashtag"></i> <span data-i18n="suppliers.index.table.id">ID</span></th>
                <th><i class="fa fa-building"></i> <span data-i18n="suppliers.index.table.name">Supplier Name</span></th>
                <th style="width: 150px;"><i class="fa fa-calendar"></i> <span data-i18n="suppliers.index.table.created">Created Date</span></th>
                <th style="width: 100px;"><i class="fa fa-cogs"></i> <span data-i18n="suppliers.index.table.actions">Actions</span></th>
              </tr>
            </thead>
            <tbody>
              @forelse($suppliers as $supplier)
                <tr>
                  <td class="text-center">{{ $supplier->id }}</td>
                  <td><strong>{{ $supplier->name }}</strong></td>
                  <td>{{ $supplier->created_at ? $supplier->created_at->format('M d, Y') : '-' }}</td>
                  <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-primary" title="Edit" data-i18n-title="suppliers.index.action.edit_title">
                          <i class="fa fa-pencil"></i>
                        </a>
                        @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSupplier({{ $supplier->id }})" title="Delete" data-i18n-title="suppliers.index.action.delete_title">
                              <i class="fa fa-trash"></i>
                            </button>
                        @endif
                    </div>
                    @if(Auth::user() && Auth::user()->hasRole('super-admin'))
                        <form id="delete-supplier-{{ $supplier->id }}" method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" style="display:none;">
                          @csrf
                          @method('DELETE')
                        </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center empty-state">
                    <i class="fa fa-info-circle"></i> <span data-i18n="suppliers.index.empty.title">No suppliers found. Create one using the form on the right.</span>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-plus-circle"></i> <span data-i18n="suppliers.index.form.title">Create New Supplier</span></h3>
        </div>
        <div class="box-body">
          <form method="POST" action="{{ url('suppliers') }}" id="create-supplier-form">
            @csrf

            <fieldset>
              <legend>
                <span class="form-section-icon"><i class="fa fa-info-circle"></i></span>
                <span data-i18n="suppliers.index.form.section.details">Supplier Details</span>
              </legend>

              {{-- Supplier Name Field --}}
              <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">
                  <span data-i18n="suppliers.index.form.label.name">Supplier Name</span> <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control" 
                       value="{{ old('name') }}"
                       placeholder="e.g., Dell Technologies, HP Inc."
                       data-i18n-placeholder="suppliers.index.form.placeholder.name"
                       required>
                <small class="help-text">
                  <i class="fa fa-info-circle"></i> <span data-i18n="suppliers.index.form.help.name">Enter the full legal or trading name of the supplier</span>
                </small>
                @error('name')
                  <span class="help-block">{{ $message }}</span>
                @enderror
              </div>
            </fieldset>

            {{-- Submit Button --}}
            <div class="form-group" style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #e3e3e3;">
              <button type="submit" class="btn btn-primary btn-block btn-submit" id="createSupplierSubmitButton">
                <i class="fa fa-plus-circle"></i> <span data-i18n="suppliers.index.action.submit">Add New Supplier</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Help & Tips Sidebar --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> <span data-i18n="suppliers.index.guidelines.title">Supplier Guidelines</span></h3>
        </div>
        <div class="box-body info-box-custom">
          <h4><i class="fa fa-info-circle"></i> <span data-i18n="suppliers.index.guidelines.best_practices">Best Practices</span></h4>
          <ul>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.index.guidelines.item_one">Use official company names</span></li>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.index.guidelines.item_two">Avoid abbreviations unless standard</span></li>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.index.guidelines.item_three">Check for duplicates before adding</span></li>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.index.guidelines.item_four">Keep naming consistent</span></li>
          </ul>

          <h4 style="margin-top: 15px;"><i class="fa fa-list"></i> <span data-i18n="suppliers.index.guidelines.common_suppliers">Common Suppliers</span></h4>
          <ul>
            <li><i class="fa fa-building text-info"></i> Dell Technologies</li>
            <li><i class="fa fa-building text-info"></i> HP Inc.</li>
            <li><i class="fa fa-building text-info"></i> Lenovo</li>
            <li><i class="fa fa-building text-info"></i> Microsoft</li>
            <li><i class="fa fa-building text-info"></i> Cisco Systems</li>
          </ul>
        </div>
      </div>

      {{-- Supplier Stats --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bar-chart"></i> <span data-i18n="suppliers.index.stats.title">Quick Stats</span></h3>
        </div>
        <div class="box-body">
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-truck"></i></span>
            <div class="info-box-content">
              <span class="info-box-text" data-i18n="suppliers.index.stats.total">Total Suppliers</span>
              <span class="info-box-number">{{ count($suppliers) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    (function() {
      var translations = {
        en: {
          'suppliers.index.alert.validation': 'Please correct the following errors:',
          'suppliers.index.table.title': 'All Suppliers',
          'suppliers.index.table.id': 'ID',
          'suppliers.index.table.name': 'Supplier Name',
          'suppliers.index.table.created': 'Created Date',
          'suppliers.index.table.actions': 'Actions',
          'suppliers.index.action.edit_title': 'Edit',
          'suppliers.index.action.delete_title': 'Delete',
          'suppliers.index.empty.title': 'No suppliers found. Create one using the form on the right.',
          'suppliers.index.form.title': 'Create New Supplier',
          'suppliers.index.form.section.details': 'Supplier Details',
          'suppliers.index.form.label.name': 'Supplier Name',
          'suppliers.index.form.placeholder.name': 'e.g., Dell Technologies, HP Inc.',
          'suppliers.index.form.help.name': 'Enter the full legal or trading name of the supplier',
          'suppliers.index.action.submit': 'Add New Supplier',
          'suppliers.index.action.submitting': 'Adding Supplier...',
          'suppliers.index.guidelines.title': 'Supplier Guidelines',
          'suppliers.index.guidelines.best_practices': 'Best Practices',
          'suppliers.index.guidelines.item_one': 'Use official company names',
          'suppliers.index.guidelines.item_two': 'Avoid abbreviations unless standard',
          'suppliers.index.guidelines.item_three': 'Check for duplicates before adding',
          'suppliers.index.guidelines.item_four': 'Keep naming consistent',
          'suppliers.index.guidelines.common_suppliers': 'Common Suppliers',
          'suppliers.index.stats.title': 'Quick Stats',
          'suppliers.index.stats.total': 'Total Suppliers',
          'suppliers.index.runtime.delete_confirm': 'Are you sure you want to delete this supplier? This action cannot be undone.',
          'suppliers.index.runtime.name_required': 'Supplier name is required!',
          'suppliers.index.runtime.name_short': 'Supplier name must be at least 2 characters long!',
          'suppliers.index.datatable.length': 'Show _MENU_ suppliers per page',
          'suppliers.index.datatable.info': 'Showing _START_ to _END_ of _TOTAL_ suppliers',
          'suppliers.index.datatable.info_empty': 'No suppliers available',
          'suppliers.index.datatable.empty': 'No suppliers found. Use the form on the right to add one.',
          'suppliers.index.datatable.search': 'Search suppliers:',
          'suppliers.index.datatable.first': 'First',
          'suppliers.index.datatable.last': 'Last',
          'suppliers.index.datatable.next': 'Next',
          'suppliers.index.datatable.previous': 'Previous',
          'suppliers.index.datatable.copy': 'Copy',
          'suppliers.index.datatable.csv': 'CSV',
          'suppliers.index.datatable.excel': 'Excel',
          'suppliers.index.datatable.pdf': 'PDF',
          'suppliers.index.datatable.print': 'Print'
        },
        id: {
          'suppliers.index.alert.validation': 'Mohon perbaiki kesalahan berikut:',
          'suppliers.index.table.title': 'Semua Supplier',
          'suppliers.index.table.id': 'ID',
          'suppliers.index.table.name': 'Nama Supplier',
          'suppliers.index.table.created': 'Tanggal Dibuat',
          'suppliers.index.table.actions': 'Aksi',
          'suppliers.index.action.edit_title': 'Ubah',
          'suppliers.index.action.delete_title': 'Hapus',
          'suppliers.index.empty.title': 'Belum ada supplier. Buat supplier baru melalui formulir di sebelah kanan.',
          'suppliers.index.form.title': 'Buat Supplier Baru',
          'suppliers.index.form.section.details': 'Detail Supplier',
          'suppliers.index.form.label.name': 'Nama Supplier',
          'suppliers.index.form.placeholder.name': 'contoh, Dell Technologies, HP Inc.',
          'suppliers.index.form.help.name': 'Masukkan nama legal atau nama dagang supplier secara lengkap',
          'suppliers.index.action.submit': 'Tambah Supplier Baru',
          'suppliers.index.action.submitting': 'Menambahkan Supplier...',
          'suppliers.index.guidelines.title': 'Panduan Supplier',
          'suppliers.index.guidelines.best_practices': 'Praktik Terbaik',
          'suppliers.index.guidelines.item_one': 'Gunakan nama perusahaan resmi',
          'suppliers.index.guidelines.item_two': 'Hindari singkatan kecuali sudah standar',
          'suppliers.index.guidelines.item_three': 'Periksa duplikasi sebelum menambah data',
          'suppliers.index.guidelines.item_four': 'Jaga konsistensi penamaan',
          'suppliers.index.guidelines.common_suppliers': 'Contoh Supplier Umum',
          'suppliers.index.stats.title': 'Statistik Singkat',
          'suppliers.index.stats.total': 'Total Supplier',
          'suppliers.index.runtime.delete_confirm': 'Apakah Anda yakin ingin menghapus supplier ini? Tindakan ini tidak dapat dibatalkan.',
          'suppliers.index.runtime.name_required': 'Nama supplier wajib diisi!',
          'suppliers.index.runtime.name_short': 'Nama supplier minimal 2 karakter!',
          'suppliers.index.datatable.length': 'Tampilkan _MENU_ supplier per halaman',
          'suppliers.index.datatable.info': 'Menampilkan _START_ sampai _END_ dari _TOTAL_ supplier',
          'suppliers.index.datatable.info_empty': 'Tidak ada supplier tersedia',
          'suppliers.index.datatable.empty': 'Belum ada supplier. Gunakan formulir di sebelah kanan untuk menambah data.',
          'suppliers.index.datatable.search': 'Cari supplier:',
          'suppliers.index.datatable.first': 'Pertama',
          'suppliers.index.datatable.last': 'Terakhir',
          'suppliers.index.datatable.next': 'Berikutnya',
          'suppliers.index.datatable.previous': 'Sebelumnya',
          'suppliers.index.datatable.copy': 'Salin',
          'suppliers.index.datatable.csv': 'CSV',
          'suppliers.index.datatable.excel': 'Excel',
          'suppliers.index.datatable.pdf': 'PDF',
          'suppliers.index.datatable.print': 'Cetak'
        }
      };

      var currentLanguage = 'en';
      var userId = '{{ (int) auth()->id() }}';
      var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
      var englishButton = document.getElementById('supplierIndexLanguageEnglish');
      var indonesianButton = document.getElementById('supplierIndexLanguageIndonesian');
      var suppliersTable = null;

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

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-title]'), function(node) {
          var key = node.getAttribute('data-i18n-title');
          if (dictionary[key]) {
            node.setAttribute('title', dictionary[key]);
          }
        });

        if (englishButton && indonesianButton) {
          englishButton.classList.toggle('active', currentLanguage === 'en');
          indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }

        if (typeof window.supplierIndexRefreshRuntimeText === 'function') {
          window.supplierIndexRefreshRuntimeText();
        }
      }

      window.supplierIndexLabel = getLabel;

      window.supplierIndexDataTableLanguage = function() {
        return {
          lengthMenu: getLabel('suppliers.index.datatable.length', 'Show _MENU_ suppliers per page'),
          info: getLabel('suppliers.index.datatable.info', 'Showing _START_ to _END_ of _TOTAL_ suppliers'),
          infoEmpty: getLabel('suppliers.index.datatable.info_empty', 'No suppliers available'),
          emptyTable: getLabel('suppliers.index.datatable.empty', 'No suppliers found. Use the form on the right to add one.'),
          search: getLabel('suppliers.index.datatable.search', 'Search suppliers:'),
          paginate: {
            first: getLabel('suppliers.index.datatable.first', 'First'),
            last: getLabel('suppliers.index.datatable.last', 'Last'),
            next: getLabel('suppliers.index.datatable.next', 'Next'),
            previous: getLabel('suppliers.index.datatable.previous', 'Previous')
          }
        };
      };

      window.supplierIndexRefreshRuntimeText = function() {
        if (suppliersTable) {
          suppliersTable.settings()[0].oLanguage = window.supplierIndexDataTableLanguage();
          suppliersTable.draw(false);
        }
      };

      window.deleteSupplier = function(id) {
        if (confirm(window.supplierIndexLabel('suppliers.index.runtime.delete_confirm', 'Are you sure you want to delete this supplier? This action cannot be undone.'))) {
          document.getElementById('delete-supplier-' + id).submit();
        }
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

      $(document).ready(function() {
        suppliersTable = $('#table').DataTable({
          pageLength: 25,
          order: [[1, 'asc']],
          columnDefs: [
            { orderable: false, targets: -1 }
          ],
          language: window.supplierIndexDataTableLanguage(),
          dom: 'Bfrtip',
          buttons: [
            {
              extend: 'copy',
              text: '<i class="fa fa-copy"></i> ' + window.supplierIndexLabel('suppliers.index.datatable.copy', 'Copy'),
              exportOptions: { columns: ':not(:last-child)' }
            },
            {
              extend: 'csv',
              text: '<i class="fa fa-file-text-o"></i> ' + window.supplierIndexLabel('suppliers.index.datatable.csv', 'CSV'),
              exportOptions: { columns: ':not(:last-child)' }
            },
            {
              extend: 'excel',
              text: '<i class="fa fa-file-excel-o"></i> ' + window.supplierIndexLabel('suppliers.index.datatable.excel', 'Excel'),
              exportOptions: { columns: ':not(:last-child)' }
            },
            {
              extend: 'pdf',
              text: '<i class="fa fa-file-pdf-o"></i> ' + window.supplierIndexLabel('suppliers.index.datatable.pdf', 'PDF'),
              exportOptions: { columns: ':not(:last-child)' }
            },
            {
              extend: 'print',
              text: '<i class="fa fa-print"></i> ' + window.supplierIndexLabel('suppliers.index.datatable.print', 'Print'),
              exportOptions: { columns: ':not(:last-child)' }
            }
          ]
        });

        suppliersTable.on('draw', function() {
          var info = suppliersTable.page.info();
          $('.count-badge').text(info.recordsDisplay);
        });

        $('#create-supplier-form').on('submit', function(e) {
          var supplierName = $('#name').val().trim();

          if (supplierName === '') {
            e.preventDefault();
            alert(window.supplierIndexLabel('suppliers.index.runtime.name_required', 'Supplier name is required!'));
            return false;
          }

          if (supplierName.length < 2) {
            e.preventDefault();
            alert(window.supplierIndexLabel('suppliers.index.runtime.name_short', 'Supplier name must be at least 2 characters long!'));
            return false;
          }

          var submitButton = document.getElementById('createSupplierSubmitButton');
          if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + window.supplierIndexLabel('suppliers.index.action.submitting', 'Adding Supplier...');
          }
        });

        setTimeout(function() {
          $('.alert-dismissible').fadeOut('slow');
        }, 5000);

        applyLanguage(getLanguage());
      });
    })();
</script>
@endpush


