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

  <div class="row">
    <div class="col-xs-12 col-sm-8 col-md-8">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Informasi Tiket</h3>
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
              <legend><i class="fa fa-info-circle"></i> Informasi Dasar</legend>

              <div class="form-group">
                <label>Pembuat / Pelapor</label>
                <p class="form-control-static"><i class="fa fa-user"></i> <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</p>
                <small class="text-muted">Anda membuat tiket ini atas nama diri sendiri</small>
              </div>

              <div class="form-group">
                <label for="subject">Subjek <span class="text-red">*</span></label>
                <input type="text" class="form-control @error('subject') is-invalid @enderror" name="subject" id="subject" value="{{old('subject')}}" required maxlength="255">
                <small class="text-muted">Ringkasan singkat masalah (maksimal 255 karakter)</small>
                @error('subject')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="description">Deskripsi <span class="text-red">*</span></label>
                <span id="char-counter">0 / 10 karakter (minimal 10)</span>
                <textarea class="form-control @error('description') is-invalid @enderror" rows="5" name="description" id="description" required minlength="10">{{old('description')}}</textarea>
                <small class="text-muted">Deskripsi detail masalah atau permintaan (minimal 10 karakter)</small>
                @error('description')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group">
                <label for="ticket_type_id">Jenis Tiket <span class="text-red">*</span></label>
                <select class="form-control ticket_type_id @error('ticket_type_id') is-invalid @enderror" name="ticket_type_id" id="ticket_type_id" required>
                  <option value="">-- Pilih Jenis Tiket --</option>
                  @foreach($ticketsTypes as $ticketType)
                    <option value="{{$ticketType->id}}" {{ old('ticket_type_id') == $ticketType->id ? 'selected' : '' }}>{{$ticketType->type}}</option>
                  @endforeach
                </select>
                <small class="text-muted">Kategori permintaan (contoh: Masalah Hardware, Dukungan Software, Masalah Jaringan)</small>
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
              <legend><i class="fa fa-map-marker"></i> Lokasi & Penugasan</legend>

              <div class="form-group">
                <label for="location_id">Lokasi</label>
                @php
                  $userLocation = Auth::user()->location;
                @endphp
                @if($userLocation)
                  <p class="form-control-static"><i class="fa fa-map-marker"></i> <strong>{{ $userLocation->location_name }} - {{ $userLocation->building }}, {{ $userLocation->office }}</strong></p>
                  <input type="hidden" name="location_id" value="{{ $userLocation->id }}">
                  <small class="text-muted">Lokasi otomatis diambil dari profil Anda</small>
                @else
                  <select class="form-control location_id @error('location_id') is-invalid @enderror" name="location_id" id="location_id" required>
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($locations as $location)
                      <option value="{{$location->id}}" {{ old('location_id') == $location->id ? 'selected' : '' }}>{{$location->location_name}} - {{$location->building}}, {{$location->office}}</option>
                    @endforeach
                  </select>
                  <small class="text-muted">Lokasi fisik tempat masalah terjadi</small>
                @endif
                @error('location_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </fieldset>

            {{-- SECTION 3: Asset Association --}}
            <fieldset>
              <legend><i class="fa fa-laptop"></i> Aset Terkait</legend>

              <div class="form-group">
                <label for="asset_ids">Aset Terkait (Opsional)</label>
                <select class="form-control asset_ids @error('asset_ids') is-invalid @enderror @error('asset_ids.*') is-invalid @enderror" name="asset_ids[]" id="asset_ids" multiple>
                  @foreach($assets as $asset)
                    <option value="{{$asset->id}}" {{ (old('asset_ids') && in_array($asset->id, old('asset_ids'))) || (isset($preselectedAssetId) && $preselectedAssetId == $asset->id) ? 'selected' : '' }}>
                      {{ $asset->model ? $asset->model->asset_model : 'Unknown Model' }} ({{ $asset->asset_tag }}) - ({{ $asset->assignedTo ? $asset->assignedTo->name : 'Not Assigned' }}) {{ $asset->division ? ($asset->division->name ?? $asset->division->division_name) : 'No Division' }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted">Pilih satu atau lebih aset terkait tiket ini (gunakan Ctrl/Cmd + Klik untuk memilih beberapa)</small>
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
                <i class="fa fa-save"></i> <b>Buat Tiket</b>
              </button>
              <a href="{{ route('tickets.index') }}" class="btn btn-default btn-lg">
                <i class="fa fa-times"></i> Batal
              </a>
            </div>
          </form>
        </div>
      </div>

      {{-- Display validation errors if any --}}
      @if(count($errors))
        <div class="alert alert-danger">
          <h4><i class="icon fa fa-ban"></i> Kesalahan Validasi!</h4>
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
          <h3 class="box-title"><i class="fa fa-magic"></i> Template Cepat</h3>
        </div>
        <div class="box-body">
          <p class="text-muted"><small>Gunakan template yang telah ditentukan untuk mempercepat pembuatan tiket</small></p>
          <div class="form-group">
            <label for="canned_subject">Template</label>
            <select class="form-control" name="canned_template" id="canned_subject">
              <option value="">-- Pilih Template --</option>
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
              <i class="fa fa-magic"></i> Gunakan Template
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
                  $('#char-counter').text(length + ' / 10 karakter (minimal 10)');
                }
                // Show success message
                toastr.success('Template berhasil diterapkan!');
              } else {
                toastr.warning('Pilih template terlebih dahulu');
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
    $(document).ready(function() {
      // Initialize Select2 for all dropdowns
      $(".location_id").select2({ placeholder: 'Pilih lokasi', allowClear: true });
      $(".ticket_status_id").select2({ placeholder: 'Pilih status (opsional)', allowClear: true });
      $(".ticket_type_id").select2({ placeholder: 'Pilih jenis tiket', allowClear: false });
      $(".ticket_priority_id").select2({ placeholder: 'Pilih prioritas', allowClear: false });
      $(".subject").select2({ placeholder: 'Pilih template', allowClear: true });
      $(".asset_ids").select2({ 
        placeholder: 'Cari dan pilih aset', 
        allowClear: true,
        width: '100%'
      });

      // Character counter for description
      function updateCharCounter() {
        var length = $('#description').val().length;
        var minLength = 10;
        var counter = $('#char-counter');
        
        counter.text(length + ' / ' + minLength + ' karakter (minimal ' + minLength + ')');
        
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
        showLoading('Membuat tiket...');
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


