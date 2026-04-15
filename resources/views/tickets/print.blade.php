<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket Details - {{ $ticket->ticket_code }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 5px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .ticket-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-value {
            display: table-cell;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #dc3545;
            border-bottom: 1px solid #dc3545;
            padding-bottom: 5px;
        }
        .description-box {
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
            margin-bottom: 20px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-open { background-color: #ffc107; color: #000; }
        .status-in-progress { background-color: #17a2b8; color: #fff; }
        .status-resolved { background-color: #28a745; color: #fff; }
        .status-closed { background-color: #6c757d; color: #fff; }
        .priority-urgent { color: #dc3545; font-weight: bold; }
        .priority-high { color: #fd7e14; font-weight: bold; }
        .priority-medium { color: #ffc107; font-weight: bold; }
        .priority-low { color: #28a745; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .entries {
            margin-top: 20px;
        }
        .entry {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .entry-header {
            font-weight: bold;
            margin-bottom: 5px;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">IT Support Ticket System</div>
        <div class="title">Ticket Detail Report</div>
        <div>Generated on: {{ now()->format('d F Y, H:i:s') }}</div>
    </div>

    <div class="section-title">Ticket Information</div>
    <div class="ticket-info">
        <div class="info-row">
            <div class="info-label">Ticket Code:</div>
            <div class="info-value"><strong>{{ $ticket->ticket_code }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Subject:</div>
            <div class="info-value">{{ $ticket->subject }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">
                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $ticket->ticket_status->name ?? 'open')) }}">
                    {{ $ticket->ticket_status->name ?? 'Open' }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Priority:</div>
            <div class="info-value">
                <span class="priority-{{ strtolower($ticket->ticket_priority->name ?? 'medium') }}">
                    {{ $ticket->ticket_priority->name ?? 'Medium' }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Type:</div>
            <div class="info-value">{{ $ticket->ticket_type->name ?? 'General' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Created By:</div>
            <div class="info-value">{{ $ticket->user->name ?? 'Unknown' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Assigned To:</div>
            <div class="info-value">{{ $ticket->assignedTo->name ?? 'Unassigned' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Location:</div>
            <div class="info-value">{{ $ticket->location->name ?? 'N/A' }}</div>
        </div>
        @if($ticket->asset)
        <div class="info-row">
            <div class="info-label">Related Asset:</div>
            <div class="info-value">{{ $ticket->asset->asset_tag }}</div>
        </div>
        @endif
    </div>

    <div class="section-title">Timeline</div>
    <div class="ticket-info">
        <div class="info-row">
            <div class="info-label">Created At:</div>
            <div class="info-value">{{ $ticket->created_at->format('d F Y, H:i:s') }}</div>
        </div>
        @if($ticket->assigned_at)
        <div class="info-row">
            <div class="info-label">Assigned At:</div>
            <div class="info-value">{{ $ticket->assigned_at->format('d F Y, H:i:s') }}</div>
        </div>
        @endif
        @if($ticket->sla_due)
        <div class="info-row">
            <div class="info-label">SLA Due:</div>
            <div class="info-value">{{ $ticket->sla_due->format('d F Y, H:i:s') }}</div>
        </div>
        @endif
        @if($ticket->first_response_at)
        <div class="info-row">
            <div class="info-label">First Response:</div>
            <div class="info-value">{{ $ticket->first_response_at->format('d F Y, H:i:s') }}</div>
        </div>
        @endif
        @if($ticket->resolved_at)
        <div class="info-row">
            <div class="info-label">Resolved At:</div>
            <div class="info-value">{{ $ticket->resolved_at->format('d F Y, H:i:s') }}</div>
        </div>
        @endif
    </div>

    <div class="section-title">Description</div>
    <div class="description-box">
        {{ $ticket->description }}
    </div>

    @if($ticket->ticket_entries && $ticket->ticket_entries->count() > 0)
    <div class="section-title">Ticket Entries / Updates</div>
    <div class="entries">
        @foreach($ticket->ticket_entries as $entry)
        <div class="entry">
            <div class="entry-header">
                {{ $entry->user->name ?? 'System' }} - {{ $entry->created_at->format('d F Y, H:i:s') }}
            </div>
            <div>{{ $entry->message }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <div>IT Support Ticket System - Ticket Report</div>
        <div>This document was generated automatically on {{ now()->format('d F Y \a\t H:i:s') }}</div>
    </div>
</body>
</html>