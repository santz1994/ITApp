@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'LCD Meeting Room Settings',
    'subtitle' => 'Atur jumlah ruang, urutan tampilan, dan status aktif di LCD dashboard',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Meeting Room Bookings', 'url' => route('meeting-room-bookings.index')],
        ['label' => 'LCD Settings']
    ],
    'actions' => '
        <div class="btn-group" role="group">
            <a href="'.route('meeting-room-bookings.lcd-dashboard').'" class="btn btn-primary" target="_blank">
                <i class="fa fa-tv"></i> Buka LCD Dashboard
            </a>
            <a href="'.route('meeting-room-bookings.lcd-dashboard2').'" class="btn btn-info" target="_blank">
                <i class="fa fa-clone"></i> Buka LCD Dashboard 2
            </a>
        </div>
    '
])

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-exclamation-triangle"></i>
            <strong>Gagal menyimpan pengaturan:</strong>
            <ul style="margin-top: 8px; margin-bottom: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3 id="totalRoomsCard">0</h3>
                    <p>Total Ruang (Termasuk Nonaktif)</p>
                </div>
                <div class="icon"><i class="fa fa-building"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 id="activeRoomsCard">{{ $activeRoomCount ?? 0 }}</h3>
                    <p>Ruang Aktif di LCD</p>
                </div>
                <div class="icon"><i class="fa fa-tv"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>Tips</h3>
                    <p>Posisi kecil tampil lebih awal (kiri-atas)</p>
                </div>
                <div class="icon"><i class="fa fa-lightbulb-o"></i></div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-sliders"></i> Pengaturan Ruang LCD</h3>
        </div>

        <form action="{{ route('meeting-room-bookings.lcd-settings.save') }}" method="POST" id="lcdSettingsForm">
            @csrf
            <div class="box-body">
                @php
                    $globalRoomsPerSlide = old('carousel_rooms_per_slide', $lcdGlobalSettings['rooms_per_slide'] ?? 2);
                    $globalIntervalSeconds = old('carousel_interval_seconds', $lcdGlobalSettings['slide_interval_seconds'] ?? 10);
                @endphp

                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <div class="alert alert-info" style="margin-bottom: 12px;">
                            <i class="fa fa-info-circle"></i>
                            Carousel LCD akan menampilkan beberapa ruang per slide dan berpindah otomatis sesuai interval.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="carousel_rooms_per_slide">Jumlah Ruang per Slide</label>
                            <input type="number"
                                   id="carousel_rooms_per_slide"
                                   name="carousel_rooms_per_slide"
                                   class="form-control"
                                   min="1"
                                   max="4"
                                   value="{{ $globalRoomsPerSlide }}"
                                   required>
                            <small class="text-muted">Rekomendasi LCD landscape: 2 ruang per slide.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="carousel_interval_seconds">Interval Pindah Slide (detik)</label>
                            <input type="number"
                                   id="carousel_interval_seconds"
                                   name="carousel_interval_seconds"
                                   class="form-control"
                                   min="5"
                                   max="120"
                                   value="{{ $globalIntervalSeconds }}"
                                   required>
                            <small class="text-muted">Default 10 detik, minimum 5 detik.</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="roomSettingsTable">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Nama Ruang</th>
                                <th style="width: 20%;">Posisi Tampil</th>
                                <th style="width: 20%;" class="text-center">Aktif di LCD</th>
                                <th style="width: 20%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $rows = old('rooms', $roomSettings ?? []);
                            @endphp

                            @foreach($rows as $index => $room)
                                <tr class="room-setting-row">
                                    <td>
                                        <input type="text"
                                               name="rooms[{{ $index }}][room_name]"
                                               class="form-control"
                                               value="{{ $room['room_name'] ?? '' }}"
                                               placeholder="Contoh: Ruang Meeting 1"
                                               required>
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="rooms[{{ $index }}][display_order]"
                                               class="form-control display-order-input"
                                               value="{{ $room['display_order'] ?? ($index + 1) }}"
                                               min="1"
                                               max="200"
                                               required>
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        <input type="hidden" name="rooms[{{ $index }}][is_active]" value="0">
                                        <input type="checkbox"
                                               name="rooms[{{ $index }}][is_active]"
                                               value="1"
                                               class="is-active-input"
                                               {{ !empty($room['is_active']) ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        <button type="button" class="btn btn-danger btn-sm remove-room-btn">
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-default" id="addRoomBtn">
                    <i class="fa fa-plus-circle"></i> Tambah Ruang
                </button>
            </div>

            <div class="box-footer clearfix">
                <div class="pull-left">
                    <a href="{{ route('meeting-room-bookings.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const tableBody = document.querySelector('#roomSettingsTable tbody');
    const addRoomBtn = document.getElementById('addRoomBtn');
    const activeRoomsCard = document.getElementById('activeRoomsCard');
    const totalRoomsCard = document.getElementById('totalRoomsCard');

    function updateCards() {
        const rows = tableBody.querySelectorAll('.room-setting-row');
        const activeCount = tableBody.querySelectorAll('.is-active-input:checked').length;

        totalRoomsCard.textContent = rows.length;
        activeRoomsCard.textContent = activeCount;
    }

    function reindexRows() {
        const rows = tableBody.querySelectorAll('.room-setting-row');

        rows.forEach(function (row, index) {
            const roomNameInput = row.querySelector('input[name*="[room_name]"]');
            const orderInput = row.querySelector('input[name*="[display_order]"]');
            const hiddenActiveInput = row.querySelector('input[type="hidden"][name*="[is_active]"]');
            const checkboxActiveInput = row.querySelector('input[type="checkbox"][name*="[is_active]"]');

            roomNameInput.name = 'rooms[' + index + '][room_name]';
            orderInput.name = 'rooms[' + index + '][display_order]';
            hiddenActiveInput.name = 'rooms[' + index + '][is_active]';
            checkboxActiveInput.name = 'rooms[' + index + '][is_active]';

            if (!orderInput.value || Number(orderInput.value) < 1) {
                orderInput.value = index + 1;
            }
        });

        updateCards();
    }

    function buildRow(index) {
        const tr = document.createElement('tr');
        tr.className = 'room-setting-row';
        tr.innerHTML =
            '<td>' +
                '<input type="text" name="rooms[' + index + '][room_name]" class="form-control" placeholder="Contoh: Ruang Meeting Baru" required>' +
            '</td>' +
            '<td>' +
                '<input type="number" name="rooms[' + index + '][display_order]" class="form-control display-order-input" min="1" max="200" value="' + (index + 1) + '" required>' +
            '</td>' +
            '<td class="text-center" style="vertical-align: middle;">' +
                '<input type="hidden" name="rooms[' + index + '][is_active]" value="0">' +
                '<input type="checkbox" name="rooms[' + index + '][is_active]" value="1" class="is-active-input" checked>' +
            '</td>' +
            '<td class="text-center" style="vertical-align: middle;">' +
                '<button type="button" class="btn btn-danger btn-sm remove-room-btn"><i class="fa fa-trash"></i> Hapus</button>' +
            '</td>';

        return tr;
    }

    addRoomBtn.addEventListener('click', function () {
        const index = tableBody.querySelectorAll('.room-setting-row').length;
        tableBody.appendChild(buildRow(index));
        reindexRows();
    });

    tableBody.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('.remove-room-btn');
        if (!removeBtn) {
            return;
        }

        const rows = tableBody.querySelectorAll('.room-setting-row');
        if (rows.length <= 1) {
            alert('Minimal harus ada 1 ruang meeting.');
            return;
        }

        removeBtn.closest('.room-setting-row').remove();
        reindexRows();
    });

    tableBody.addEventListener('change', function (event) {
        if (event.target.classList.contains('is-active-input') || event.target.classList.contains('display-order-input')) {
            updateCards();
        }
    });

    reindexRows();
})();
</script>
@endpush
