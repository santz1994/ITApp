@extends('layouts.app')

@section('main-content')

@include('components.page-header', [
    'title' => 'SLA Learning System',
    'subtitle' => 'Machine Learning Dashboard for Intelligent SLA Prediction',
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'SLA Learning Dashboard']
    ]
])

<div class="row">
    {{-- Overview Stats --}}
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Tentang Sistem</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-info">
                    <h4><i class="fa fa-graduation-cap"></i> SLA Learning System</h4>
                    <p>
                        Sistem ini menggunakan <strong>Machine Learning</strong> untuk memprediksi waktu SLA yang optimal berdasarkan:
                    </p>
                    <ul>
                        <li><strong>Historical Data:</strong> Belajar dari 90 hari terakhir resolusi tiket</li>
                        <li><strong>Priority & Type:</strong> Mempertimbangkan jenis dan prioritas tiket</li>
                        <li><strong>Time Context:</strong> Menyesuaikan berdasarkan waktu (jam kerja, hari, weekend)</li>
                        <li><strong>Confidence Level:</strong> Minimal 10 sampel data untuk prediksi, confidence maksimal di 50 sampel</li>
                    </ul>
                    <p class="text-muted" style="margin-bottom: 0;">
                        <strong>Note:</strong> Sistem akan otomatis update setiap kali tiket diselesaikan. 
                        Semakin banyak data, semakin akurat prediksinya.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Overall Metrics --}}
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-line-chart"></i> Overall Resolution Metrics (90 Hari Terakhir)</h3>
            </div>
            <div class="box-body">
                @if(count($metrics) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-light-blue">
                                    <th>Priority</th>
                                    <th>Total Resolved Tickets</th>
                                    <th>Average Resolution Time (Hours)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics as $metric)
                                    <tr>
                                        <td>
                                            <span class="label 
                                                @if($metric['priority'] == 'High') label-danger
                                                @elseif($metric['priority'] == 'Medium') label-warning
                                                @else label-info
                                                @endif">
                                                {{ $metric['priority'] }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $metric['count'] }}</strong> tickets</td>
                                        <td><strong>{{ $metric['avg_hours'] }}</strong> hours</td>
                                        <td>
                                            @if($metric['count'] >= 50)
                                                <span class="label label-success"><i class="fa fa-check"></i> Excellent Data</span>
                                            @elseif($metric['count'] >= 10)
                                                <span class="label label-warning"><i class="fa fa-info"></i> Good Data</span>
                                            @else
                                                <span class="label label-default"><i class="fa fa-clock-o"></i> Collecting Data</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> Belum ada data resolusi tiket dalam 90 hari terakhir. Sistem masih menggunakan SLA default.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Learned SLA Predictions --}}
    <div class="col-md-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-brain"></i> Learned SLA Predictions (Per Type & Priority)</h3>
            </div>
            <div class="box-body">
                @if(count($statistics) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-orange">
                                    <th>Priority</th>
                                    <th>Ticket Type</th>
                                    <th>Predicted SLA (Hours)</th>
                                    <th>Sample Size</th>
                                    <th>Confidence Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics as $stat)
                                    <tr>
                                        <td>
                                            <span class="label 
                                                @if($stat['priority'] == 'High') label-danger
                                                @elseif($stat['priority'] == 'Medium') label-warning
                                                @else label-info
                                                @endif">
                                                {{ $stat['priority'] }}
                                            </span>
                                        </td>
                                        <td>{{ $stat['type'] }}</td>
                                        <td><strong>{{ $stat['avg_hours'] }}</strong> hours</td>
                                        <td>{{ $stat['sample_size'] }} tickets</td>
                                        <td>
                                            <div class="progress" style="margin-bottom: 0;">
                                                <div class="progress-bar 
                                                    @if(floatval($stat['confidence']) >= 70) progress-bar-success
                                                    @elseif(floatval($stat['confidence']) >= 50) progress-bar-warning
                                                    @else progress-bar-info
                                                    @endif"
                                                    style="width: {{ $stat['confidence'] }}">
                                                    {{ $stat['confidence'] }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-success">
                        <i class="fa fa-lightbulb-o"></i> <strong>Sistem Aktif!</strong> 
                        Prediksi SLA ini akan otomatis digunakan untuk tiket baru yang sesuai kriteria.
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Sistem masih belajar. Belum ada prediksi khusus berdasarkan kombinasi type & priority. 
                        Minimal 10 tiket resolved per kombinasi untuk mulai memprediksi.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Training Data --}}
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> Recent Training Data (10 Tiket Terakhir)</h3>
            </div>
            <div class="box-body">
                @if(count($recentTickets) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Ticket Code</th>
                                    <th>Priority</th>
                                    <th>Type</th>
                                    <th>Resolution Time</th>
                                    <th>Resolved At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTickets as $ticket)
                                    <tr>
                                        <td><strong>{{ $ticket['ticket_code'] }}</strong></td>
                                        <td>
                                            <span class="label 
                                                @if($ticket['priority'] == 'High') label-danger
                                                @elseif($ticket['priority'] == 'Medium') label-warning
                                                @else label-info
                                                @endif">
                                                {{ $ticket['priority'] }}
                                            </span>
                                        </td>
                                        <td>{{ $ticket['type'] }}</td>
                                        <td><strong>{{ $ticket['resolution_hours'] }}</strong> hours</td>
                                        <td>{{ $ticket['resolved_at'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> Belum ada tiket yang diselesaikan untuk training data.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
