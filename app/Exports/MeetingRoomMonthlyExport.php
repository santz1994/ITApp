<?php

namespace App\Exports;

use App\MeetingRoomBooking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class MeetingRoomMonthlyExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths
{
    protected $month;
    protected $year;
    protected $rowNumber = 0;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $startDate = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($this->year, $this->month, 1)->endOfMonth();
        
        return MeetingRoomBooking::whereBetween('start_datetime', [$startDate, $endDate])
            ->whereIn('status', ['approved', 'finished'])
            ->where(function($query) {
                // Exclude bookings with purpose starting with "BLOCKED:"
                $query->where('purpose', 'not like', 'BLOCKED:%');
            })
            ->orderBy('start_datetime', 'asc')
            ->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'NO',
            'TANGGAL',
            'WAKTU',
            'MEETING ROOM',
            'DEPARTEMEN',
            'KETERANGAN',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($booking): array
    {
        $this->rowNumber++;
        
        $startDate = Carbon::parse($booking->start_datetime);
        $endDate = Carbon::parse($booking->end_datetime);
        
        // Format tanggal: DD/MM/YYYY
        $tanggal = $startDate->format('d/m/Y');
        
        // Format waktu: HH:MM - HH:MM
        $waktu = $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
        
        // Get user's department
        $departemen = $booking->user->division->name ?? '-';
        
        // Keterangan: Change "BLOCKED: " to "Blocked : "
        $keterangan = $booking->purpose;
        if (str_starts_with($keterangan, 'BLOCKED: ')) {
            $keterangan = 'Blocked : ' . substr($keterangan, 9);
        }
        
        return [
            $this->rowNumber,                    // NO
            $tanggal,                            // TANGGAL
            $waktu,                              // WAKTU
            $booking->room_name,                 // MEETING ROOM
            $departemen,                         // DEPARTEMEN
            $keterangan,                         // KETERANGAN
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->rowNumber + 1; // +1 for header row
        
        // Style header row
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style data rows
        if ($lastRow > 1) {
            $sheet->getStyle('A2:F' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ]);

            // Center align NO column
            $sheet->getStyle('A2:A' . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            // Center align TANGGAL and WAKTU columns
            $sheet->getStyle('B2:C' . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Auto-size rows for better readability
        for ($i = 2; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1); // Auto height
        }

        return [];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,   // NO
            'B' => 15,  // TANGGAL
            'C' => 18,  // WAKTU
            'D' => 20,  // MEETING ROOM
            'E' => 20,  // DEPARTEMEN
            'F' => 50,  // KETERANGAN
        ];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        $monthName = Carbon::create($this->year, $this->month, 1)->format('F Y');
        return 'Laporan ' . $monthName;
    }
}
