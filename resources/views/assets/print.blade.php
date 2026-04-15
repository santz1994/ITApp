@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Label - {{ $asset->asset_tag }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 10cm 5.325cm;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .label-container {
            width: 10cm;
            background-color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
            font-size: 10pt;
            color: #000;
            text-align: left;
        }

        th.header {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            padding: 5px;
            background-color: white;
        }

        .col-label {
            width: 30%;
            font-weight: normal;
        }

        .col-value {
            width: 45%;
        }

        .col-qr {
            width: 25%;
            text-align: center;
            padding: 4px;
            vertical-align: middle;
        }

        .qr-container svg {
            width: 3cm !important;
            height: 3cm !important;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="3" class="header">PT QUTY KARUNIA</th>
        </tr>
        <tr>
            <td class="col-label">Product Name</td>
            <td class="col-value">{{ $asset->model->asset_model ?? '(Model)' }}</td>
            <td rowspan="4" class="col-qr">
                <div class="qr-container">
                    {!! QrCode::format('svg')->size(120)->margin(0)->generate($assetUrl ?? route('assets.show', $asset->id)) !!}
                </div>
                <div style="font-size: 8pt; margin-top: 2px;">[QR Code]</div>
            </td>
        </tr>
        <tr>
            <td class="col-label">Assets No.</td>
            <td class="col-value">{{ $asset->asset_tag ?? '(Assets Number)' }}</td>
        </tr>
        <tr>
            <td class="col-label">Purchase Date</td>
            <td class="col-value">
                @if($asset->purchase_date)
                    {{ is_string($asset->purchase_date) ? \Carbon\Carbon::parse($asset->purchase_date)->format('d/m/Y') : $asset->purchase_date->format('d/m/Y') }}
                @else
                    (Purchase Date)
                @endif
            </td>
        </tr>
        <tr>
            <td class="col-label">Management Dept.</td>
            <td class="col-value">{{ $asset->division->name ?? $asset->division->division_name ?? '(Division)' }}</td>
        </tr>
    </table>
</body>
</html>
