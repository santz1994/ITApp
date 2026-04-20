@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

@include('components.page-header', [
    'title' => 'Edit Supplier',
    'subtitle' => 'Update supplier information',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Suppliers', 'url' => route('suppliers.index')],
        ['label' => 'Edit']
    ]
])

@include('layouts.partials.module-toolbar', [
  'englishButtonId' => 'supplierEditLanguageEnglish',
  'indonesianButtonId' => 'supplierEditLanguageIndonesian',
  'ariaLabel' => 'Supplier Edit Language Toggle',
])

<div class="container-fluid">
  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-check"></i> {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <i class="icon fa fa-ban"></i> {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-warning"></i> <span data-i18n="suppliers.edit.alert.validation">Please correct the following errors:</span></h4>
      <ul style="margin-bottom: 0;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Supplier Metadata --}}
  @if($supplier->created_at)
    <div class="alert alert-info metadata-alert">
      <strong><i class="fa fa-info-circle"></i> <span data-i18n="suppliers.edit.meta.info">Supplier Info:</span></strong>
      <span data-i18n="suppliers.edit.meta.created">Created on</span> {{ $supplier->created_at->format('M d, Y \a\t h:i A') }}
      @if($supplier->updated_at && $supplier->updated_at != $supplier->created_at)
        | <span data-i18n="suppliers.edit.meta.updated">Last updated on</span> {{ $supplier->updated_at->format('M d, Y \a\t h:i A') }}
      @endif
    </div>
  @endif

  <div class="row">
    <div class="col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-edit"></i> <span data-i18n="suppliers.edit.form.title">Edit Supplier Details</span></h3>
        </div>
        <div class="box-body">
          <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}" id="edit-supplier-form">
            @method('PATCH')
            @csrf

            <fieldset>
              <legend>
                <span class="form-section-icon"><i class="fa fa-truck"></i></span>
                <span data-i18n="suppliers.edit.form.section.info">Supplier Information</span>
              </legend>

              {{-- Supplier Name Field --}}
              <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">
                  <span data-i18n="suppliers.edit.form.label.name">Supplier Name</span> <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-building"></i></span>
                  <input type="text" 
                         id="name" 
                         name="name" 
                         class="form-control" 
                         value="{{ old('name', $supplier->name) }}"
                         placeholder="e.g., Dell Technologies, HP Inc."
                         data-i18n-placeholder="suppliers.edit.form.placeholder.name"
                         required>
                </div>
                <small class="help-text">
                  <i class="fa fa-info-circle"></i> <span data-i18n="suppliers.edit.form.help.name">Enter the full legal or trading name of the supplier</span>
                </small>
                @error('name')
                  <span class="help-block">{{ $message }}</span>
                @enderror
              </div>
            </fieldset>

            {{-- Action Buttons --}}
            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
              <button type="submit" class="btn btn-primary btn-lg btn-submit" id="editSupplierSubmitButton">
                <i class="fa fa-save"></i> <span data-i18n="suppliers.edit.action.submit">Update Supplier</span>
              </button>
              <a href="{{ route('suppliers.index') }}" class="btn btn-default btn-lg">
                <i class="fa fa-arrow-left"></i> <span data-i18n="suppliers.edit.action.cancel">Cancel</span>
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-md-4">
      {{-- Edit Tips --}}
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> <span data-i18n="suppliers.edit.tips.title">Edit Tips</span></h3>
        </div>
        <div class="box-body info-box-custom">
          <ul>
            <li><i class="fa fa-warning text-warning"></i> <strong data-i18n="suppliers.edit.tips.impact">Impact:</strong> <span data-i18n="suppliers.edit.tips.impact_desc">Changing this supplier may affect purchase orders and assets</span></li>
            <li><i class="fa fa-box text-info"></i> <span data-i18n="suppliers.edit.tips.linked_desc">Existing purchase orders and assets will remain linked</span></li>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.edit.tips.audit_desc">All changes are logged for audit purposes</span></li>
            <li><i class="fa fa-history text-muted"></i> <span data-i18n="suppliers.edit.tips.history_desc">You can view the change history after saving</span></li>
          </ul>
        </div>
      </div>

      {{-- Quick Actions --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bolt"></i> <span data-i18n="suppliers.edit.quick_actions.title">Quick Actions</span></h3>
        </div>
        <div class="box-body">
          <a href="{{ route('suppliers.index') }}" class="btn btn-default btn-block">
            <i class="fa fa-list"></i> <span data-i18n="suppliers.edit.quick_actions.back">Back to All Suppliers</span>
          </a>
          @if(isset($supplier->id))
            <a href="{{ route('assets.index', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary btn-block">
              <i class="fa fa-desktop"></i> <span data-i18n="suppliers.edit.quick_actions.assets">View Assets from This Supplier</span>
            </a>
          @endif
        </div>
      </div>

      {{-- Supplier Guidelines --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> <span data-i18n="suppliers.edit.best_practices.title">Best Practices</span></h3>
        </div>
        <div class="box-body info-box-custom">
          <ul>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.edit.best_practices.item_one">Use official company names</span></li>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.edit.best_practices.item_two">Avoid abbreviations</span></li>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.edit.best_practices.item_three">Keep naming consistent</span></li>
            <li><i class="fa fa-check text-success"></i> <span data-i18n="suppliers.edit.best_practices.item_four">Check for duplicates</span></li>
          </ul>
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
        'suppliers.edit.alert.validation': 'Please correct the following errors:',
        'suppliers.edit.meta.info': 'Supplier Info:',
        'suppliers.edit.meta.created': 'Created on',
        'suppliers.edit.meta.updated': 'Last updated on',
        'suppliers.edit.form.title': 'Edit Supplier Details',
        'suppliers.edit.form.section.info': 'Supplier Information',
        'suppliers.edit.form.label.name': 'Supplier Name',
        'suppliers.edit.form.placeholder.name': 'e.g., Dell Technologies, HP Inc.',
        'suppliers.edit.form.help.name': 'Enter the full legal or trading name of the supplier',
        'suppliers.edit.action.submit': 'Update Supplier',
        'suppliers.edit.action.submitting': 'Updating Supplier...',
        'suppliers.edit.action.cancel': 'Cancel',
        'suppliers.edit.tips.title': 'Edit Tips',
        'suppliers.edit.tips.impact': 'Impact:',
        'suppliers.edit.tips.impact_desc': 'Changing this supplier may affect purchase orders and assets',
        'suppliers.edit.tips.linked_desc': 'Existing purchase orders and assets will remain linked',
        'suppliers.edit.tips.audit_desc': 'All changes are logged for audit purposes',
        'suppliers.edit.tips.history_desc': 'You can view the change history after saving',
        'suppliers.edit.quick_actions.title': 'Quick Actions',
        'suppliers.edit.quick_actions.back': 'Back to All Suppliers',
        'suppliers.edit.quick_actions.assets': 'View Assets from This Supplier',
        'suppliers.edit.best_practices.title': 'Best Practices',
        'suppliers.edit.best_practices.item_one': 'Use official company names',
        'suppliers.edit.best_practices.item_two': 'Avoid abbreviations',
        'suppliers.edit.best_practices.item_three': 'Keep naming consistent',
        'suppliers.edit.best_practices.item_four': 'Check for duplicates',
        'suppliers.edit.runtime.name_required': 'Supplier name is required!',
        'suppliers.edit.runtime.name_short': 'Supplier name must be at least 2 characters long!'
      },
      id: {
        'suppliers.edit.alert.validation': 'Mohon perbaiki kesalahan berikut:',
        'suppliers.edit.meta.info': 'Info Supplier:',
        'suppliers.edit.meta.created': 'Dibuat pada',
        'suppliers.edit.meta.updated': 'Terakhir diperbarui pada',
        'suppliers.edit.form.title': 'Ubah Detail Supplier',
        'suppliers.edit.form.section.info': 'Informasi Supplier',
        'suppliers.edit.form.label.name': 'Nama Supplier',
        'suppliers.edit.form.placeholder.name': 'contoh, Dell Technologies, HP Inc.',
        'suppliers.edit.form.help.name': 'Masukkan nama legal atau nama dagang supplier secara lengkap',
        'suppliers.edit.action.submit': 'Perbarui Supplier',
        'suppliers.edit.action.submitting': 'Memperbarui Supplier...',
        'suppliers.edit.action.cancel': 'Batal',
        'suppliers.edit.tips.title': 'Tips Perubahan',
        'suppliers.edit.tips.impact': 'Dampak:',
        'suppliers.edit.tips.impact_desc': 'Perubahan supplier ini dapat memengaruhi purchase order dan aset',
        'suppliers.edit.tips.linked_desc': 'Purchase order dan aset yang sudah ada akan tetap terhubung',
        'suppliers.edit.tips.audit_desc': 'Semua perubahan dicatat untuk kebutuhan audit',
        'suppliers.edit.tips.history_desc': 'Anda dapat melihat riwayat perubahan setelah menyimpan',
        'suppliers.edit.quick_actions.title': 'Aksi Cepat',
        'suppliers.edit.quick_actions.back': 'Kembali ke Semua Supplier',
        'suppliers.edit.quick_actions.assets': 'Lihat Aset dari Supplier Ini',
        'suppliers.edit.best_practices.title': 'Praktik Terbaik',
        'suppliers.edit.best_practices.item_one': 'Gunakan nama perusahaan resmi',
        'suppliers.edit.best_practices.item_two': 'Hindari singkatan',
        'suppliers.edit.best_practices.item_three': 'Jaga konsistensi penamaan',
        'suppliers.edit.best_practices.item_four': 'Periksa duplikasi data',
        'suppliers.edit.runtime.name_required': 'Nama supplier wajib diisi!',
        'suppliers.edit.runtime.name_short': 'Nama supplier minimal 2 karakter!'
      }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('supplierEditLanguageEnglish');
    var indonesianButton = document.getElementById('supplierEditLanguageIndonesian');

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
    }

    window.supplierEditLabel = getLabel;

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
      $('#edit-supplier-form').on('submit', function(e) {
        var supplierName = $('#name').val().trim();

        if (supplierName === '') {
          e.preventDefault();
          alert(window.supplierEditLabel('suppliers.edit.runtime.name_required', 'Supplier name is required!'));
          return false;
        }

        if (supplierName.length < 2) {
          e.preventDefault();
          alert(window.supplierEditLabel('suppliers.edit.runtime.name_short', 'Supplier name must be at least 2 characters long!'));
          return false;
        }

        var submitButton = document.getElementById('editSupplierSubmitButton');
        if (submitButton) {
          submitButton.disabled = true;
          submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + window.supplierEditLabel('suppliers.edit.action.submitting', 'Updating Supplier...');
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


