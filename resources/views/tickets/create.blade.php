@extends('layouts.app')

@section('main-content')

{{-- All styles moved to public/css/ui-enhancements.css for better performance and maintainability --}}

@include('components.page-header', [
    'title' => 'Create New Ticket',
    'subtitle' => 'Submit a new support ticket',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Tickets', 'url' => route('tickets.index')],
        ['label' => 'Create']
    ]
])

  <div class="pull-right" style="margin-top: -52px; margin-bottom: 16px; margin-right: 15px;">
    <div class="btn-group btn-group-xs" role="group" aria-label="Ticket Create Language Toggle">
      <button type="button" class="btn btn-default" id="ticketCreateLanguageEnglish" data-lang="en">EN</button>
      <button type="button" class="btn btn-default" id="ticketCreateLanguageIndonesian" data-lang="id">ID</button>
    </div>
  </div>
  <div class="clearfix"></div>

  <div class="row">
    <div class="col-xs-12 col-sm-8 col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title" data-i18n="tickets.create.form.title">Ticket Information</h3>
        </div>
        <div class="box-body">
          {{-- Flash Messages --}}
          @if(session('success'))
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"></i> {{ session('success') }}
            </div>
          @endif

          @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-ban"></i> {{ session('error') }}
            </div>
          @endif

          <form method="POST" action="{{ url('tickets') }}" id="ticket-create-form">
            {{csrf_field()}}
            
            {{-- Hidden creator field --}}
            <input type="hidden" name="user_id" value="{{ old('user_id', Auth::id()) }}">

            {{-- SECTION 1: Basic Information --}}
            <fieldset>
              <legend><i class="fa fa-info-circle"></i> <span data-i18n="tickets.create.section.basic">Basic Information</span></legend>

              <div class="form-group">
                <label data-i18n="tickets.create.field.reporter">Creator / Reporter</label>
                <p class="form-control-static"><i class="fa fa-user"></i> <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</p>
                <small class="text-muted" data-i18n="tickets.create.help.reporter">This ticket is created under your account</small>
              </div>

              <div class="form-group">
                <label for="subject"><span data-i18n="tickets.create.field.subject">Subject</span> <span class="text-red">*</span></label>
                <input type="text" class="form-control @error('subject') is-invalid @enderror" name="subject" id="subject" value="{{old('subject')}}" required maxlength="255" data-i18n-placeholder="tickets.create.placeholder.subject">
                <small class="text-muted" data-i18n="tickets.create.help.subject">Short summary of the issue (maximum 255 characters)</small>
                @error('subject')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="description"><span data-i18n="tickets.create.field.description">Description</span> <span class="text-red">*</span></label>
                <span id="char-counter">0 / 10 karakter (minimal 10)</span>
                <textarea class="form-control @error('description') is-invalid @enderror" rows="5" name="description" id="description" required minlength="10" data-i18n-placeholder="tickets.create.placeholder.description">{{old('description')}}</textarea>
                <small class="text-muted" data-i18n="tickets.create.help.description">Detailed issue or request description (minimum 10 characters)</small>
                @error('description')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="ticket_type_id"><span data-i18n="tickets.create.field.ticket_type">Ticket Type</span> <span class="text-red">*</span></label>
                <select class="form-control ticket_type_id @error('ticket_type_id') is-invalid @enderror" name="ticket_type_id" id="ticket_type_id" required>
                  <option value="" data-i18n="tickets.create.option.select_type">-- Select Ticket Type --</option>
                  @foreach($ticketsTypes as $ticketType)
                    <option value="{{$ticketType->id}}" {{ old('ticket_type_id') == $ticketType->id ? 'selected' : '' }}>{{$ticketType->type}}</option>
                  @endforeach
                </select>
                <small class="text-muted" data-i18n="tickets.create.help.ticket_type">Request category (example: hardware issue, software support, network issue)</small>
                @error('ticket_type_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label>Prioritas <span class="badge bg-blue">Otomatis</span></label>
                <input type="hidden" name="ticket_priority_id" value="">
                <p class="form-control-static">
                  <i class="fa fa-magic"></i> Sistem akan otomatis mendeteksi prioritas berdasarkan kata kunci dalam subjek dan deskripsi
                </p>
                <div class="alert alert-info" style="margin-top: 10px;">
                  <strong>Contoh kata kunci:</strong>
                  <ul class="list-unstyled" style="margin-bottom: 0;">
                    <li><span class="label label-danger">HIGH</span> urgent, mendesak, darurat, mati, crash, down</li>
                    <li><span class="label label-warning">MEDIUM</span> lambat, masalah, error, gangguan, maintenance</li>
                    <li><span class="label label-info">LOW</span> request, permintaan, pertanyaan, saran</li>
                  </ul>
                </div>
              </div>

              <div class="form-group" style="display: none;">
                <label for="ticket_status_id">Status Awal</label>
                <input type="hidden" name="ticket_status_id" value="">
                <p class="form-control-static"><span class="label label-success">Open</span></p>
                <small class="text-muted">Semua tiket baru otomatis berstatus "Open"</small>
              </div>
            </fieldset>

            {{-- SECTION 2: Location & Assignment --}}
            <fieldset>
              <legend><i class="fa fa-map-marker"></i> <span data-i18n="tickets.create.section.location">Location & Assignment</span></legend>

              <div class="form-group">
                <label for="location_id" data-i18n="tickets.create.field.location">Location</label>
                @php
                  $userLocation = Auth::user()->location;
                @endphp
                @if($userLocation)
                  <p class="form-control-static"><i class="fa fa-map-marker"></i> <strong>{{ $userLocation->location_name }} - {{ $userLocation->building }}, {{ $userLocation->office }}</strong></p>
                  <input type="hidden" name="location_id" value="{{ $userLocation->id }}">
                  <small class="text-muted" data-i18n="tickets.create.help.location_auto">Location is auto-filled from your profile</small>
                @else
                  <select class="form-control location_id @error('location_id') is-invalid @enderror" name="location_id" id="location_id" required>
                    <option value="" data-i18n="tickets.create.option.select_location">-- Select Location --</option>
                    @foreach($locations as $location)
                      <option value="{{$location->id}}" {{ old('location_id') == $location->id ? 'selected' : '' }}>{{$location->location_name}} - {{$location->building}}, {{$location->office}}</option>
                    @endforeach
                  </select>
                  <small class="text-muted" data-i18n="tickets.create.help.location_manual">Physical location where the issue happened</small>
                @endif
                @error('location_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </fieldset>

            {{-- SECTION 3: Asset Association --}}
            <fieldset>
              <legend><i class="fa fa-laptop"></i> <span data-i18n="tickets.create.section.asset">Related Assets</span></legend>

              <div class="form-group">
                <label for="asset_ids" data-i18n="tickets.create.field.assets">Related Assets (Optional)</label>
                <select class="form-control asset_ids @error('asset_ids') is-invalid @enderror @error('asset_ids.*') is-invalid @enderror" name="asset_ids[]" id="asset_ids" multiple>
                  @foreach($assets as $asset)
                    <option value="{{$asset->id}}" {{ (old('asset_ids') && in_array($asset->id, old('asset_ids'))) || (isset($preselectedAssetId) && $preselectedAssetId == $asset->id) ? 'selected' : '' }}>
                      {{ $asset->model ? $asset->model->asset_model : 'Unknown Model' }} ({{ $asset->asset_tag }}) - ({{ $asset->assignedTo ? $asset->assignedTo->name : 'Not Assigned' }}) {{ $asset->division ? ($asset->division->name ?? $asset->division->division_name) : 'No Division' }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted" data-i18n="tickets.create.help.assets">Choose one or more assets related to this ticket (use Ctrl/Cmd + click for multi-select)</small>
                @error('asset_ids')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('asset_ids.*')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </fieldset>

            {{-- Submit Buttons --}}
            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e3e3e3;">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa fa-save"></i> <b data-i18n="tickets.create.action.submit">Create Ticket</b>
              </button>
              <a href="{{ route('tickets.index') }}" class="btn btn-default btn-lg">
                <i class="fa fa-times"></i> <span data-i18n="tickets.create.action.cancel">Cancel</span>
              </a>
            </div>
          </form>
        </div>
      </div>

      {{-- Display validation errors if any --}}
      @if(count($errors))
        <div class="alert alert-danger">
          <h4><i class="icon fa fa-ban"></i> <span data-i18n="tickets.create.validation.title">Validation Errors!</span></h4>
          <ul>
            @foreach($errors->all() as $error)
              <li>{{$error}}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
    
    {{-- SIDEBAR: Canned Fields & Help --}}
    <div class="col-xs-12 col-sm-4 col-md-4">
      {{-- Canned Fields Quick Select --}}
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-magic"></i> <span data-i18n="tickets.create.template.title">Quick Template</span></h3>
        </div>
        <div class="box-body">
          <p class="text-muted"><small data-i18n="tickets.create.template.help">Use predefined templates to speed up ticket creation</small></p>
          <div class="form-group">
            <label for="canned_subject" data-i18n="tickets.create.template.field">Template</label>
            <select class="form-control" name="canned_template" id="canned_subject">
              <option value="" data-i18n="tickets.create.template.option">-- Select Template --</option>
              @foreach($ticketsCannedFields as $ticketsCannedField)
                <option value="{{$ticketsCannedField->id}}" 
                        data-subject="{{$ticketsCannedField->subject}}" 
                        data-description="{{$ticketsCannedField->description}}">
                  {{$ticketsCannedField->subject}}
                </option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <button type="button" class="btn btn-info btn-block" id="apply-template-btn">
              <i class="fa fa-magic"></i> <span data-i18n="tickets.create.template.apply">Apply Template</span>
            </button>
          </div>
          
          <script>
          $(document).ready(function() {
            $('#apply-template-btn').click(function() {
              var selected = $('#canned_subject option:selected');
              if (selected.val()) {
                $('#subject').val(selected.data('subject'));
                $('#description').val(selected.data('description'));
                // Trigger character counter update if exists
                if ($('#char-counter').length) {
                  var length = $('#description').val().length;
                  var label = typeof window.ticketCreateLabelFormat === 'function'
                    ? window.ticketCreateLabelFormat('tickets.create.runtime.char_counter', '{count} / {min} characters (minimum {min})', { count: length, min: 10 })
                    : (length + ' / 10 characters (minimum 10)');
                  $('#char-counter').text(label);
                }
                // Show success message
                toastr.success(typeof window.ticketCreateLabel === 'function'
                  ? window.ticketCreateLabel('tickets.create.runtime.template_applied', 'Template applied successfully!')
                  : 'Template applied successfully!');
              } else {
                toastr.warning(typeof window.ticketCreateLabel === 'function'
                  ? window.ticketCreateLabel('tickets.create.runtime.select_template', 'Please select a template first')
                  : 'Please select a template first');
              }
            });
          });
          </script>
        </div>
      </div>

      {{-- Help & Tips --}}
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-question-circle"></i> Bantuan & Tips</h3>
        </div>
        <div class="box-body">
          <p><strong>Panduan Prioritas:</strong></p>
          <ul class="list-unstyled">
            <li><span class="badge bg-red">Tinggi</span> Sistem mati, masalah kritis</li>
            <li><span class="badge bg-yellow">Sedang</span> Mempengaruhi pekerjaan tapi tidak kritis</li>
            <li><span class="badge bg-green">Rendah</span> Masalah kecil atau permintaan</li>
          </ul>
          
          <hr>
          
          <p><strong>Jenis Tiket Umum:</strong></p>
          <ul style="font-size: 12px;">
            <li><i class="fa fa-wrench"></i> Masalah Hardware</li>
            <li><i class="fa fa-code"></i> Dukungan Software</li>
            <li><i class="fa fa-wifi"></i> Masalah Jaringan</li>
            <li><i class="fa fa-user-plus"></i> Permintaan Akses</li>
          </ul>

          <hr>

          <p><strong>Tips untuk Dukungan Lebih Baik:</strong></p>
          <ul style="font-size: 12px;">
            <li>Jelaskan secara spesifik dalam deskripsi</li>
            <li>Sertakan pesan error jika ada</li>
            <li>Sebutkan kapan masalah mulai terjadi</li>
            <li>Pilih aset yang sesuai</li>
          </ul>
        </div>
      </div>

      {{-- SLA Information --}}
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-clock-o"></i> Informasi SLA</h3>
        </div>
        <div class="box-body">
          <p class="text-muted"><small>Waktu respons yang diharapkan berdasarkan prioritas:</small></p>
          <table class="table table-condensed" style="font-size: 12px;">
            <tr>
              <td><span class="badge bg-red">Tinggi</span></td>
              <td>4 jam</td>
            </tr>
            <tr>
              <td><span class="badge bg-yellow">Sedang</span></td>
              <td>24 jam</td>
            </tr>
            <tr>
              <td><span class="badge bg-green">Rendah</span></td>
              <td>48 jam</td>
            </tr>
          </table>
          <p class="text-muted"><small><em>* Waktu SLA dimulai saat tiket dibuat</em></small></p>
        </div>
      </div>
    </div>
  </div>

@include('components.loading-overlay')

@endsection

@section('footer')
  <script type="text/javascript">
    (function() {
      var translations = {
        en: {
          'tickets.create.form.title': 'Ticket Information',
          'tickets.create.section.basic': 'Basic Information',
          'tickets.create.section.location': 'Location & Assignment',
          'tickets.create.section.asset': 'Related Assets',
          'tickets.create.field.reporter': 'Creator / Reporter',
          'tickets.create.help.reporter': 'This ticket is created under your account',
          'tickets.create.field.subject': 'Subject',
          'tickets.create.placeholder.subject': 'Brief issue summary',
          'tickets.create.help.subject': 'Short summary of the issue (maximum 255 characters)',
          'tickets.create.field.description': 'Description',
          'tickets.create.placeholder.description': 'Describe the issue details and impact',
          'tickets.create.help.description': 'Detailed issue or request description (minimum 10 characters)',
          'tickets.create.field.ticket_type': 'Ticket Type',
          'tickets.create.option.select_type': '-- Select Ticket Type --',
          'tickets.create.help.ticket_type': 'Request category (example: hardware issue, software support, network issue)',
          'tickets.create.field.location': 'Location',
          'tickets.create.help.location_auto': 'Location is auto-filled from your profile',
          'tickets.create.option.select_location': '-- Select Location --',
          'tickets.create.help.location_manual': 'Physical location where the issue happened',
          'tickets.create.field.assets': 'Related Assets (Optional)',
          'tickets.create.help.assets': 'Choose one or more assets related to this ticket (use Ctrl/Cmd + click for multi-select)',
          'tickets.create.action.submit': 'Create Ticket',
          'tickets.create.action.cancel': 'Cancel',
          'tickets.create.validation.title': 'Validation Errors!',
          'tickets.create.template.title': 'Quick Template',
          'tickets.create.template.help': 'Use predefined templates to speed up ticket creation',
          'tickets.create.template.field': 'Template',
          'tickets.create.template.option': '-- Select Template --',
          'tickets.create.template.apply': 'Apply Template',
          'tickets.create.runtime.template_applied': 'Template applied successfully!',
          'tickets.create.runtime.select_template': 'Please select a template first',
          'tickets.create.runtime.char_counter': '{count} / {min} characters (minimum {min})',
          'tickets.create.runtime.loading_create': 'Creating ticket...',
          'tickets.create.select2.location': 'Select location',
          'tickets.create.select2.status_optional': 'Select status (optional)',
          'tickets.create.select2.ticket_type': 'Select ticket type',
          'tickets.create.select2.priority': 'Select priority',
          'tickets.create.select2.template': 'Select template',
          'tickets.create.select2.assets': 'Search and select assets'
        },
        id: {
          'tickets.create.form.title': 'Informasi Tiket',
          'tickets.create.section.basic': 'Informasi Dasar',
          'tickets.create.section.location': 'Lokasi & Penugasan',
          'tickets.create.section.asset': 'Aset Terkait',
          'tickets.create.field.reporter': 'Pembuat / Pelapor',
          'tickets.create.help.reporter': 'Tiket ini dibuat atas nama akun Anda',
          'tickets.create.field.subject': 'Subjek',
          'tickets.create.placeholder.subject': 'Ringkasan singkat masalah',
          'tickets.create.help.subject': 'Ringkasan singkat masalah (maksimal 255 karakter)',
          'tickets.create.field.description': 'Deskripsi',
          'tickets.create.placeholder.description': 'Jelaskan detail masalah dan dampaknya',
          'tickets.create.help.description': 'Deskripsi detail masalah atau permintaan (minimal 10 karakter)',
          'tickets.create.field.ticket_type': 'Jenis Tiket',
          'tickets.create.option.select_type': '-- Pilih Jenis Tiket --',
          'tickets.create.help.ticket_type': 'Kategori permintaan (contoh: Masalah Hardware, Dukungan Software, Masalah Jaringan)',
          'tickets.create.field.location': 'Lokasi',
          'tickets.create.help.location_auto': 'Lokasi otomatis diambil dari profil Anda',
          'tickets.create.option.select_location': '-- Pilih Lokasi --',
          'tickets.create.help.location_manual': 'Lokasi fisik tempat masalah terjadi',
          'tickets.create.field.assets': 'Aset Terkait (Opsional)',
          'tickets.create.help.assets': 'Pilih satu atau lebih aset terkait tiket ini (gunakan Ctrl/Cmd + klik untuk memilih beberapa)',
          'tickets.create.action.submit': 'Buat Tiket',
          'tickets.create.action.cancel': 'Batal',
          'tickets.create.validation.title': 'Kesalahan Validasi!',
          'tickets.create.template.title': 'Template Cepat',
          'tickets.create.template.help': 'Gunakan template yang telah ditentukan untuk mempercepat pembuatan tiket',
          'tickets.create.template.field': 'Template',
          'tickets.create.template.option': '-- Pilih Template --',
          'tickets.create.template.apply': 'Gunakan Template',
          'tickets.create.runtime.template_applied': 'Template berhasil diterapkan!',
          'tickets.create.runtime.select_template': 'Pilih template terlebih dahulu',
          'tickets.create.runtime.char_counter': '{count} / {min} karakter (minimal {min})',
          'tickets.create.runtime.loading_create': 'Membuat tiket...',
          'tickets.create.select2.location': 'Pilih lokasi',
          'tickets.create.select2.status_optional': 'Pilih status (opsional)',
          'tickets.create.select2.ticket_type': 'Pilih jenis tiket',
          'tickets.create.select2.priority': 'Pilih prioritas',
          'tickets.create.select2.template': 'Pilih template',
          'tickets.create.select2.assets': 'Cari dan pilih aset'
        }
      };

      var currentLanguage = 'en';
      var userId = '{{ (int) auth()->id() }}';
      var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
      var englishButton = document.getElementById('ticketCreateLanguageEnglish');
      var indonesianButton = document.getElementById('ticketCreateLanguageIndonesian');

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

      function formatLabel(key, fallback, vars) {
        var label = getLabel(key, fallback);
        Object.keys(vars || {}).forEach(function(varKey) {
          label = label.replace(new RegExp('\\{' + varKey + '\\}', 'g'), String(vars[varKey]));
        });
        return label;
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

      window.ticketCreateLabel = getLabel;
      window.ticketCreateLabelFormat = formatLabel;

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

    $(document).ready(function() {
      // Initialize Select2 for all dropdowns
      $(".location_id").select2({ placeholder: window.ticketCreateLabel('tickets.create.select2.location', 'Select location'), allowClear: true });
      $(".ticket_status_id").select2({ placeholder: window.ticketCreateLabel('tickets.create.select2.status_optional', 'Select status (optional)'), allowClear: true });
      $(".ticket_type_id").select2({ placeholder: window.ticketCreateLabel('tickets.create.select2.ticket_type', 'Select ticket type'), allowClear: false });
      $(".ticket_priority_id").select2({ placeholder: window.ticketCreateLabel('tickets.create.select2.priority', 'Select priority'), allowClear: false });
      $(".subject").select2({ placeholder: window.ticketCreateLabel('tickets.create.select2.template', 'Select template'), allowClear: true });
      $(".asset_ids").select2({ 
        placeholder: window.ticketCreateLabel('tickets.create.select2.assets', 'Search and select assets'), 
        allowClear: true,
        width: '100%'
      });

      // Character counter for description
      function updateCharCounter() {
        var length = $('#description').val().length;
        var minLength = 10;
        var counter = $('#char-counter');

        counter.text(window.ticketCreateLabelFormat('tickets.create.runtime.char_counter', '{count} / {min} characters (minimum {min})', {
          count: length,
          min: minLength
        }));
        
        if (length >= minLength) {
          counter.removeClass('invalid').addClass('valid');
        } else {
          counter.removeClass('valid').addClass('invalid');
        }
      }

      // Update counter on load and on input
      updateCharCounter();
      $('#description').on('input', updateCharCounter);

      // SLA Due Date Calculator
      function calculateSLADueDate() {
        var selectedOption = $('#ticket_priority_id').find(':selected');
        var slaHours = selectedOption.data('sla-hours');
        
        if (slaHours) {
          // Calculate due date from current time
          var now = new Date();
          var dueDate = new Date(now.getTime() + (slaHours * 60 * 60 * 1000));
          
          // Format: "Mon, Nov 5 at 2:30 PM" or "Tue, Nov 6 at 10:00 AM"
          var options = { 
            weekday: 'short', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit', 
            minute: '2-digit'
          };
          var formattedDate = dueDate.toLocaleString('en-US', options);
          
          // Show preview
          $('#sla-due-date').text(formattedDate);
          $('#sla-preview').slideDown();
        } else {
          // Hide preview if no priority selected
          $('#sla-preview').slideUp();
        }
      }

      // Calculate SLA on priority change
      $('#ticket_priority_id').on('change', calculateSLADueDate);
      
      // Calculate on page load if priority was already selected (from old input)
      if ($('#ticket_priority_id').val()) {
        calculateSLADueDate();
      }

      // Add loading overlay on form submit
      $('#ticket-create-form').on('submit', function() {
        showLoading(window.ticketCreateLabel('tickets.create.runtime.loading_create', 'Creating ticket...'));
      });

      // Prevent enter key from submitting form
      $(":input").keypress(function(event){
        if (event.which == '10' || event.which == '13') {
          event.preventDefault();
        }
      });
    });
  </script>
@endsection


