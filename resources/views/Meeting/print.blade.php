<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Permohonan Pemakaian Ruang Rapat</title>
    <style>
        /* Mengatur halaman untuk print A5 */
        @page {
            size: A5;
            margin: 10mm;
        }

        /* Reset dasar dan font sans-serif (seperti di gambar) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            background-color: #E0E0E0; /* Latar belakang abu-abu untuk pratinjau */
        }

        /* Kontainer halaman A5 untuk pratinjau di layar */
        .a5-page {
            width: 148mm;
            min-height: 210mm;
            padding: 10mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        /* --- Header Dokumen (Non-Tabel) --- */
        .header-container {
            width: 100%;
            margin-bottom: 10px;
            overflow: hidden; /* Clearfix */
        }

        .header-right {
            float: right;
            font-weight: bold;
            font-size: 10pt;
            padding-top: 5px;
        }

        .header-center {
            text-align: center;
        }

        .header-title-1 {
            font-size: 12pt;
            font-weight: bold;
        }

        .header-title-2 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        
        /* --- Penataan Tabel Utama --- */
        
        /* Trik untuk membuat tabel-tabel terpisah terlihat menyatu */
        .form-block {
            width: 100%;
            border-collapse: collapse;
            /* Trik agar border menyatu antar tabel */
            margin-top: -1px; 
        }
        
        /* Tabel pertama tidak perlu margin negatif */
        .form-block.first {
            margin-top: 0;
        }

        .form-block th,
        .form-block td {
            border: 1px solid black;
            padding: 4px 6px;
            height: 28px; /* Tinggi baris standar */
            vertical-align: top;
            font-size: 10pt;
        }

        .form-block th {
            font-weight: bold;
            text-align: center;
        }
        
        .shaded {
            background-color: #E0E0E0; /* Warna abu-abu seperti di gambar */
        }
        
        .centered {
            text-align: center;
        }
        
        .label {
            font-weight: bold;
        }
        
        .colon {
            width: 2%;
            text-align: center;
            font-weight: bold;
        }
        
        /* Tinggi khusus untuk kotak deskripsi dan tanda tangan */
        .desc-box {
            /* Tinggi sebelumnya 140px, ditambah 3 baris (3 * 28px = 84px) */
            height: 224px; 
        }
        
        .sig-box {
            height: 80px;
        }

        /* Aturan untuk print */
        @media print {
            body {
                background-color: white;
            }

            .a5-page {
                margin: 0;
                border: none;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Print Controls */
        .print-controls {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .btn {
            display: inline-block;
            padding: 10px 25px;
            margin: 0 5px;
            font-size: 11pt;
            font-weight: bold;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    {{-- Print Controls --}}
    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fa fa-print"></i> Print / Cetak
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fa fa-times"></i> Close / Tutup
        </button>
    </div>

    <div class="a5-page">

        <div class="header-container">
            <div class="header-right">{{ config('app.name', 'PT. QUTY KARUNIA') }}</div>
            <div class="header-center">
                <div class="header-title-1">FORM PERMOHONAN</div>
                <div class="header-title-2">PEMAKAIAN RUANG RAPAT (Meeting Room)</div>
            </div>
        </div>

        <table class="form-block first">
            <thead>
                <tr>
                    <th class="shaded centered">PEMOHON PEMAKAIAN RUANG RAPAT</th>
                </tr>
            </thead>
        </table>
        
        <table class="form-block">
            <tbody>
                <tr>
                    <td class="label" style="width: 16%">TANGGAL</td>
                    <td class="colon">:</td>
                    <td style="width: 15%">{{ $booking->start_datetime->format('d/m/Y') }}</td>
                    
                    <td class="label" style="width: 12%">BAGIAN</td>
                    <td class="colon">:</td>
                    <td style="width: 18%">{{ $booking->department }}</td>
                    
                    <td class="label" style="width: 20%">RUANG RAPAT</td>
                    <td class="colon">:</td>
                    <td style="width: 15%">{{ $booking->room_name }}</td>
                </tr>
            </tbody>
        </table>

        <table class="form-block">
            <tbody>
                <tr>
                    <td class="label" style="width: 31%">NAMA PEMOHON</td>
                    <td class="colon">:</td>
                    <td>{{ $booking->user->name }}</td>
                </tr>
                <tr>
                    <td class="label" style="width: 31%">JABATAN PEMOHON</td>
                    <td class="colon">:</td>
                    <td>{{ $booking->requester_position }}</td>
                </tr>
                <tr>
                    <td class="label" style="width: 31%">ESTIMASI PESERTA RAPAT</td>
                    <td class="colon">:</td>
                    <td>{{ $booking->attendees_count }} orang</td>
                </tr>
            </tbody>
        </table>
        
        <table class="form-block">
            <thead>
                <tr>
                    <th class="shaded centered label">Deskripsi / Keterangan Rapat</th>
                </tr>
            </thead>
        </table>

        <table class="form-block">
            <tbody>
                <tr>
                    <td class="desc-box">{{ $booking->meeting_description }}</td>
                </tr>
            </tbody>
        </table>

        <table class="form-block">
            <tbody>
                <tr>
                    <td class="label">KEPERLUAN RAPAT :</td>
                </tr>
                <tr>
                    <td>1. {{ $booking->purpose }}</td>
                </tr>
                <tr>
                    <td>2. @if($booking->meeting_needs){{ $booking->meeting_needs }}@else-@endif</td>
                </tr>
            </tbody>
        </table>
        
        <table class="form-block">
            <thead>
                <tr>
                    <th class="shaded centered">DIPERLUKAN PADA</th>
                </tr>
            </thead>
        </table>
        
            <tbody>
                <tr>
                    <td class="label" style="width: 31%">HARI/TANGGAL :</td>
                    <td>{{ $booking->start_datetime->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label" style="width: 31%">WAKTU MULAI :</td>
                    <td>{{ $booking->start_datetime->format('H:i') }} WIB</td>
                </tr>
                <tr>
                    <td class="label" style="width: 31%">WAKTU SELESAI :</td>
                    <td>{{ $booking->end_datetime->format('H:i') }} WIB (Durasi: {{ $booking->duration }})</td>
                </tr>
            </tbody>
        </table>
        
        <table class="form-block">
            <thead>
                <tr>
                    <th class="shaded centered">PEMOHON</th>
                    <th class="shaded centered">MENGETAHUI</th>
                    <th class="shaded centered">MENYETUJUI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="sig-box">
                        @if($booking->user)
                            {{ $booking->user->name }}<br>
                            <small>{{ $booking->created_at->format('d-m-Y') }}</small>
                        @endif
                    </td>
                    <td class="sig-box">
                        @if($booking->manager_id && $booking->manager)
                            {{ $booking->manager->name }}<br>
                            <small>
                                @if($booking->manager_approved_at)
                                    {{ $booking->manager_approved_at->format('d-m-Y') }}
                                @endif
                            </small>
                        @endif
                    </td>
                    <td class="sig-box">
                        @if($booking->status == 'approved' && $booking->approver)
                            {{ $booking->approver->name }}<br>
                            <small>{{ $booking->approved_at->format('d-m-Y') }}</small>
                        @elseif($booking->status == 'rejected' && $booking->approver)
                            <strong style="color: red;">REJECTED</strong><br>
                            {{ $booking->approver->name }}<br>
                            <small>{{ $booking->approved_at->format('d-m-Y') }}</small>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="shaded"></td>
                    <td class="shaded centered label">MANAGER</td>
                    <td class="shaded centered label">DIREKTUR</td>
                </tr>
            </tbody>
        </table>

    </div>
</body>
</html>
