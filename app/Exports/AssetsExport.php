<?php

namespace App\Exports;

use App\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Asset::with(['model', 'division', 'supplier', 'status', 'assignedTo'])
                   ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Asset Tag',
            'Serial Number',
            'Model',
            'Division',
            'Supplier',
            'Purchase Date',
            'Warranty (Months)',
            'IP Address',
            'MAC Address',
            'Status',
            'Assigned To',
            'Notes',
            'Created At',
        ];
    }

    /**
     * @param Asset $asset
     * @return array
     */
    public function map($asset): array
    {
        return [
            $asset->asset_tag,
            $asset->serial_number,
            $asset->model ? $asset->model->name : '',
            $asset->division ? $asset->division->name : '',
            $asset->supplier ? $asset->supplier->name : '',
            $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '',
            $asset->warranty_months,
            $asset->ip_address,
            $asset->mac_address,
            $asset->status ? $asset->status->name : '',
            $asset->assignedTo ? $asset->assignedTo->name : '',
            $asset->notes,
            $asset->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => ['font' => ['bold' => true]],
        ];
    }
}