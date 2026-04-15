{{-- Technician Action Panel --}}
@can('update', $ticket)
<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-cogs"></i> Panel Teknisi</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    
    <div class="box-body">
        <!-- Timer Section -->
        <div class="row">
            <div class="col-md-12">
                <div id="timer-section" class="alert alert-info">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><i class="fa fa-clock-o"></i> Pelacakan Waktu Kerja</h5>
                            <div id="timer-display">
                                <span id="timer-status">Belum memulai timer</span>
                                <div id="timer-duration" style="display: none;">
                                    Durasi: <strong id="duration-text">0 menit</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button id="start-timer-btn" class="btn btn-success btn-sm">
                                <i class="fa fa-play"></i> Mulai Timer
                            </button>
                            <button id="stop-timer-btn" class="btn btn-danger btn-sm" style="display: none;">
                                <i class="fa fa-stop"></i> Hentikan Timer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Response Form -->
        <div class="row">
            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header">
                        <h4 class="box-title"><i class="fa fa-comment"></i> Tambah Respons</h4>
                    </div>
                    <div class="box-body">
                        <form id="add-response-form" method="POST" action="{{ route('tickets.add-response', $ticket->id) }}">
                            @csrf
                            <div class="form-group">
                                <label for="response">Respons/Update <span class="text-red">*</span></label>
                                <textarea class="form-control" name="response" rows="4" 
                                          placeholder="Jelaskan apa yang sudah dilakukan atau informasi terbaru..." required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="status_change">Ubah Status (Opsional)</label>
                                <select class="form-control" name="status_change">
                                    <option value="">-- Pertahankan Status Saat Ini --</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Pending">Pending User Response</option>
                                    <option value="Resolved">Resolved</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-paper-plane"></i> Kirim Respons
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header">
                        <h4 class="box-title"><i class="fa fa-flag"></i> Update Status</h4>
                    </div>
                    <div class="box-body">
                        <form id="update-status-form" method="POST" action="{{ route('tickets.update-status', $ticket->id) }}">
                            @csrf
                            <div class="form-group">
                                <label for="status">Status Baru <span class="text-red">*</span></label>
                                <select class="form-control" name="status" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Pending">Pending User Response</option>
                                    <option value="Resolved">Resolved</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Catatan Perubahan Status <span class="text-red">*</span></label>
                                <textarea class="form-control" name="notes" rows="3" 
                                          placeholder="Jelaskan alasan perubahan status..." required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-refresh"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complete Ticket Form (Only show if not resolved) -->
        @if($ticket->ticket_status->status != 'Resolved' && $ticket->ticket_status->status != 'Closed')
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header">
                        <h4 class="box-title"><i class="fa fa-check-circle"></i> Selesaikan Tiket</h4>
                    </div>
                    <div class="box-body">
                        <form id="complete-ticket-form" method="POST" action="{{ route('tickets.complete-with-resolution', $ticket->id) }}">
                            @csrf
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong>Penting:</strong> Pastikan masalah benar-benar sudah teratasi sebelum menutup tiket.
                            </div>
                            
                            <div class="form-group">
                                <label for="resolution">Langkah-langkah Penyelesaian <span class="text-red">*</span></label>
                                <textarea class="form-control" name="resolution" rows="6" 
                                          placeholder="Jelaskan secara detail:&#10;1. Diagnosa masalah&#10;2. Langkah-langkah yang dilakukan&#10;3. Solusi yang diterapkan&#10;4. Hasil akhir&#10;5. Tindakan pencegahan (jika ada)" required></textarea>
                                <small class="text-muted">Resolusi yang detail akan membantu untuk masalah serupa di masa depan.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="confirm-resolution" required> 
                                        Saya konfirmasi bahwa masalah telah diselesaikan dan solusi telah diverifikasi
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-check-circle"></i> Selesaikan Tiket
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Work Summary -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info collapsed-box">
                    <div class="box-header with-border">
                        <h4 class="box-title"><i class="fa fa-clock-o"></i> Ringkasan Waktu Kerja</h4>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" id="work-summary-content">
                        <p class="text-center text-muted">
                            <i class="fa fa-spinner fa-spin"></i> Memuat ringkasan waktu kerja...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

{{-- Stop Timer Modal --}}
<div class="modal fade" id="stop-timer-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="stop-timer-form" method="POST" action="{{ route('tickets.stop-timer', $ticket->id) }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title">Hentikan Timer</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="work_summary">Ringkasan Pekerjaan <span class="text-red">*</span></label>
                        <textarea class="form-control" name="work_summary" rows="4" 
                                  placeholder="Jelaskan apa yang telah dikerjakan selama waktu ini..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Catatan Tambahan</label>
                        <textarea class="form-control" name="notes" rows="2" 
                                  placeholder="Catatan atau observasi tambahan..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="status_change">Update Status Tiket</label>
                        <select class="form-control" name="status_change">
                            <option value="">-- Pertahankan Status Saat Ini --</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Pending">Pending User Response</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-stop"></i> Hentikan Timer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let timerInterval;
    let startTime;
    
    // Check initial timer status
    checkTimerStatus();
    
    // Start timer
    $('#start-timer-btn').click(function() {
        $.post('{{ route("tickets.start-timer", $ticket->id) }}', {
            _token: '{{ csrf_token() }}',
            description: 'Bekerja pada tiket: {{ $ticket->subject }}'
        })
        .done(function(response) {
            if (response.success) {
                startTime = new Date();
                startTimerDisplay();
                showNotification('success', response.message);
            } else {
                showNotification('error', response.message);
            }
        })
        .fail(function() {
            showNotification('error', 'Gagal memulai timer');
        });
    });
    
    // Stop timer
    $('#stop-timer-btn').click(function() {
        $('#stop-timer-modal').modal('show');
    });
    
    // Handle stop timer form
    $('#stop-timer-form').submit(function(e) {
        e.preventDefault();
        
        $.post($(this).attr('action'), $(this).serialize())
        .done(function(response) {
            if (response.success) {
                stopTimerDisplay();
                $('#stop-timer-modal').modal('hide');
                showNotification('success', response.message + ' (Durasi: ' + response.duration_formatted + ')');
                loadWorkSummary(); // Refresh work summary
            } else {
                showNotification('error', response.message);
            }
        })
        .fail(function() {
            showNotification('error', 'Gagal menghentikan timer');
        });
    });
    
    function checkTimerStatus() {
        $.get('{{ route("tickets.timer-status", $ticket->id) }}')
        .done(function(response) {
            if (response.is_running) {
                startTime = new Date(response.start_time);
                startTimerDisplay();
            }
        });
    }
    
    function startTimerDisplay() {
        $('#start-timer-btn').hide();
        $('#stop-timer-btn').show();
        $('#timer-duration').show();
        $('#timer-status').text('Timer berjalan');
        
        timerInterval = setInterval(updateTimerDisplay, 1000);
    }
    
    function stopTimerDisplay() {
        $('#start-timer-btn').show();
        $('#stop-timer-btn').hide();
        $('#timer-duration').hide();
        $('#timer-status').text('Timer dihentikan');
        
        clearInterval(timerInterval);
    }
    
    function updateTimerDisplay() {
        if (startTime) {
            const now = new Date();
            const diff = Math.floor((now - startTime) / 1000 / 60); // minutes
            const hours = Math.floor(diff / 60);
            const minutes = diff % 60;
            
            let durationText;
            if (hours > 0) {
                durationText = hours + ' jam ' + minutes + ' menit';
            } else {
                durationText = minutes + ' menit';
            }
            
            $('#duration-text').text(durationText);
        }
    }
    
    function loadWorkSummary() {
        $.get('{{ route("tickets.work-summary", $ticket->id) }}')
        .done(function(response) {
            let html = '<div class="row">';
            html += '<div class="col-md-6">';
            html += '<h5>Total Waktu: ' + response.total_formatted + '</h5>';
            html += '<h6>Breakdown by Technician:</h6>';
            html += '<ul>';
            $.each(response.work_by_technician, function(userId, data) {
                html += '<li>' + data.name + ': ' + formatDuration(data.total_minutes) + ' (' + data.activities_count + ' aktivitas)</li>';
            });
            html += '</ul>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<h6>Aktivitas Terbaru:</h6>';
            html += '<ul>';
            $.each(response.activities.slice(0, 5), function(index, activity) {
                html += '<li><small>' + activity.date + ' - ' + activity.technician + ' (' + activity.duration_formatted + ')</small><br>' + activity.description + '</li>';
            });
            html += '</ul>';
            html += '</div>';
            html += '</div>';
            
            $('#work-summary-content').html(html);
        })
        .fail(function() {
            $('#work-summary-content').html('<p class="text-center text-danger">Gagal memuat ringkasan waktu kerja</p>');
        });
    }
    
    function formatDuration(minutes) {
        if (minutes < 60) {
            return minutes + ' menit';
        }
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        return hours + ' jam ' + (mins > 0 ? mins + ' menit' : '');
    }
    
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check' : 'fa-exclamation-triangle';
        
        const notification = $('<div class="alert ' + alertClass + ' alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
            '<i class="fa ' + icon + '"></i> ' + message +
            '</div>');
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Load work summary when expanded
    $('[data-widget="collapse"]').on('click', function() {
        if ($(this).closest('.box').hasClass('collapsed-box')) {
            setTimeout(loadWorkSummary, 300);
        }
    });
    
    // Form validation for completion
    $('#complete-ticket-form').submit(function(e) {
        const resolution = $('textarea[name="resolution"]').val().trim();
        if (resolution.length < 50) {
            e.preventDefault();
            showNotification('error', 'Resolusi harus berisi minimal 50 karakter untuk dokumentasi yang baik');
            return false;
        }
    });
});
</script>