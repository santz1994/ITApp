<?php

namespace App\Exports;

use App\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Ticket::with(['user', 'assignedTo', 'location', 'asset', 'ticket_status', 'ticket_priority', 'ticket_type'])
                     ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Ticket Code',
            'Subject',
            'Description',
            'User',
            'Assigned To',
            'Location',
            'Asset',
            'Status',
            'Priority',
            'Type',
            'SLA Due',
            'First Response At',
            'Resolved At',
            'Created At',
        ];
    }

    /**
     * @param Ticket $ticket
     * @return array
     */
    public function map($ticket): array
    {
        return [
            $ticket->ticket_code,
            $ticket->subject,
            $ticket->description,
            $ticket->user ? $ticket->user->name : '',
            $ticket->assignedTo ? $ticket->assignedTo->name : '',
            $ticket->location ? $ticket->location->name : '',
            $ticket->asset ? $ticket->asset->asset_tag : '',
            $ticket->ticket_status ? $ticket->ticket_status->name : '',
            $ticket->ticket_priority ? $ticket->ticket_priority->name : '',
            $ticket->ticket_type ? $ticket->ticket_type->name : '',
            $ticket->sla_due ? $ticket->sla_due->format('Y-m-d H:i:s') : '',
            $ticket->first_response_at ? $ticket->first_response_at->format('Y-m-d H:i:s') : '',
            $ticket->resolved_at ? $ticket->resolved_at->format('Y-m-d H:i:s') : '',
            $ticket->created_at->format('Y-m-d H:i:s'),
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