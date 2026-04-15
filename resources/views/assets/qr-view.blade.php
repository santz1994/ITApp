@extends('layouts.app')

@section('styles')
<style>
    /* Mobile-first QR View Styling */
    .qr-asset-container {
        max-width: 600px;
        margin: 20px auto;
        padding: 15px;
    }
    
    .qr-asset-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .qr-asset-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .qr-asset-header h2 {
        margin: 0 0 5px 0;
        font-size: 24px;
        font-weight: 600;
    }
    
    .qr-asset-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 14px;
    }
    
    .qr-asset-body {
        padding: 20px;
    }
    
    .asset-info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .asset-info-row:last-child {
        border-bottom: none;
    }
    
    .asset-info-label {
        font-weight: 600;
        color: #555;
        width: 140px;
        flex-shrink: 0;
    }
    
    .asset-info-value {
        color: #333;
        flex-grow: 1;
    }
    
    .asset-status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-deployed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-ready {
        background: #cce5ff;
        color: #004085;
    }
    
    .status-repairs {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-written-off {
        background: #f8d7da;
        color: #721c24;
    }
    
    .qr-asset-actions {
        padding: 15px 20px;
        background: #f8f9fa;
        text-align: center;
    }
    
    .qr-asset-actions a {
        margin: 5px;
    }
    
    .qr-code-display {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        margin: 20px 0;
        border-radius: 8px;
    }
    
    .qr-code-display img {
        max-width: 200px;
        height: auto;
    }
    
    @media (max-width: 768px) {
        .qr-asset-container {
            margin: 10px;
            padding: 10px;
        }
        
        .asset-info-row {
            flex-direction: column;
        }
        
        .asset-info-label {
            width: 100%;
            margin-bottom: 5px;
        }
    }
</style>
@endsection

@section('main-content')
<div class="qr-asset-container">
    <div class="qr-asset-card">
        <div class="qr-asset-header">
            <h2><i class="fa fa-desktop"></i> {{ $asset->name }}</h2>
            <p>Asset Tag: {{ $asset->asset_tag }}</p>
        </div>
        
        <div class="qr-asset-body">
            <div class="asset-info-row">
                <div class="asset-info-label">Status:</div>
                <div class="asset-info-value">
                    <span class="asset-status-badge status-{{ strtolower($asset->status) }}">
                        {{ ucfirst($asset->status) }}
                    </span>
                </div>
            </div>
            
            <div class="asset-info-row">
                <div class="asset-info-label">Serial Number:</div>
                <div class="asset-info-value">{{ $asset->serial_number ?? '-' }}</div>
            </div>
            
            <div class="asset-info-row">
                <div class="asset-info-label">Model:</div>
                <div class="asset-info-value">{{ optional($asset->assetModel)->asset_model ?? '-' }}</div>
            </div>
            
            <div class="asset-info-row">
                <div class="asset-info-label">Manufacturer:</div>
                <div class="asset-info-value">{{ optional($asset->assetModel->manufacturer)->name ?? '-' }}</div>
            </div>
            
            <div class="asset-info-row">
                <div class="asset-info-label">Location:</div>
                <div class="asset-info-value">{{ optional($asset->location)->location_name ?? '-' }}</div>
            </div>
            
            <div class="asset-info-row">
                <div class="asset-info-label">Assigned To:</div>
                <div class="asset-info-value">
                    @if($asset->assignedUser)
                        {{ $asset->assignedUser->name }} ({{ $asset->assignedUser->email }})
                    @else
                        <em>Unassigned</em>
                    @endif
                </div>
            </div>
            
            <div class="asset-info-row">
                <div class="asset-info-label">Purchase Date:</div>
                <div class="asset-info-value">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</div>
            </div>
            
            <div class="asset-info-row">
                <div class="asset-info-label">Warranty:</div>
                <div class="asset-info-value">
                    @if($asset->warranty_end_date)
                        {{ $asset->warranty_end_date->format('d M Y') }}
                        @if($asset->warranty_end_date->isFuture())
                            <span class="text-success"><i class="fa fa-check-circle"></i> Active</span>
                        @else
                            <span class="text-danger"><i class="fa fa-times-circle"></i> Expired</span>
                        @endif
                    @else
                        -
                    @endif
                </div>
            </div>
            
            @if($asset->notes)
            <div class="asset-info-row">
                <div class="asset-info-label">Notes:</div>
                <div class="asset-info-value">{{ $asset->notes }}</div>
            </div>
            @endif
            
            @if($asset->qr_code && Storage::exists($asset->qr_code))
            <div class="qr-code-display">
                <h4>QR Code</h4>
                <img src="{{ asset('storage/' . $asset->qr_code) }}" alt="QR Code for {{ $asset->asset_tag }}">
            </div>
            @endif
        </div>
        
        <div class="qr-asset-actions">
            @auth
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
                    <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-primary">
                        <i class="fa fa-eye"></i> View Full Details
                    </a>
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit Asset
                    </a>
                @endif
            @endauth
            
            <a href="{{ route('assets.index') }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Assets
            </a>
        </div>
    </div>
</div>
@endsection
