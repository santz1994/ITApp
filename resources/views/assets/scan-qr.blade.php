@extends('layouts.app')

@section('main-content')

{{-- Page Header Component --}}
@component('components.page-header')
    @slot('icon') fa-qrcode @endslot
    @slot('title') QR Code Scanner @endslot
    @slot('subtitle') Scan or search for assets using QR codes, asset tags, or serial numbers @endslot
@endcomponent

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
@endif

<div class="row">
    {{-- Main Content: 9 columns --}}
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-qrcode"></i> Scan Options
                </h3>
            </div>
            <div class="box-body">
                {{-- Instructions --}}
                <div class="alert alert-info">
                    <h4><i class="fa fa-info-circle"></i> How to Use</h4>
                    <ul style="margin-bottom: 0; margin-top: 10px;">
                        <li><strong>Camera Scan:</strong> Allow camera access and point at a QR code</li>
                        <li><strong>Manual Search:</strong> Enter an asset tag or serial number directly</li>
                        <li><strong>Tip:</strong> Works best with good lighting and steady hands</li>
                    </ul>
                </div>

                {{-- Camera Scan Section --}}
                <fieldset>
                    <legend><span class="form-section-icon"><i class="fa fa-camera"></i></span> Camera Scan</legend>
                    
                    <div class="text-center" style="margin-bottom: 20px;">
                        <button type="button" id="start-camera-btn" class="btn btn-lg btn-primary">
                            <i class="fa fa-camera"></i> Start Camera
                        </button>
                        <button type="button" id="stop-camera-btn" class="btn btn-lg btn-danger" style="display: none;">
                            <i class="fa fa-stop"></i> Stop Camera
                        </button>
                    </div>

                    <div id="camera-container" style="display: none;">
                        <div id="qr-reader" style="width: 100%; max-width: 600px; margin: 0 auto; border: 3px dashed #3c8dbc; border-radius: 8px; overflow: hidden;"></div>
                        <p class="text-center text-muted" style="margin-top: 10px;">
                            <i class="fa fa-info-circle"></i> Position the QR code within the highlighted area
                        </p>
                    </div>

                    <div id="qr-reader-results" class="alert alert-success" style="display: none; margin-top: 20px;">
                        <h4><i class="fa fa-check-circle"></i> Scanned Successfully!</h4>
                        <p id="scanned-text" style="font-size: 16px; margin: 10px 0;"></p>
                        <div id="asset-quick-info" style="margin: 15px 0;"></div>
                        <a href="#" id="view-asset-link" class="btn btn-success btn-lg">
                            <i class="fa fa-eye"></i> View Asset Details
                        </a>
                        <button type="button" id="scan-again-btn" class="btn btn-default btn-lg">
                            <i class="fa fa-refresh"></i> Scan Another
                        </button>
                    </div>
                </fieldset>

                <hr>

                {{-- Manual Search Section --}}
                <fieldset>
                    <legend><span class="form-section-icon"><i class="fa fa-keyboard-o"></i></span> Manual Search</legend>
                    
                    <form id="manual-search-form">
                        @csrf
                        <div class="form-group">
                            <label>
                                <i class="fa fa-search"></i> Asset Tag or Serial Number
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="text" name="search_term" id="search-term-input" class="form-control" placeholder="e.g., AST-001 or SN123456" autofocus>
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                </span>
                            </div>
                            <small class="help-text">
                                <i class="fa fa-info-circle"></i> Enter the asset tag (e.g., AST-001) or serial number to find the asset
                            </small>
                        </div>
                    </form>

                    <div id="search-result" style="margin-top: 20px;"></div>
                </fieldset>
            </div>
        </div>

        {{-- Recent Scans --}}
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-history"></i> Recent Scans
                </h3>
            </div>
            <div class="box-body">
                <div id="recent-scans-list">
                    <p class="text-muted text-center">
                        <i class="fa fa-info-circle"></i> No recent scans yet. Start scanning to see history.
                    </p>
                </div>
            </div>
        </div>
    </div>
    {{-- End Main Content --}}

    {{-- Sidebar: 3 columns --}}
    <div class="col-md-3">
        {{-- Scan Statistics --}}
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-chart-bar"></i> Scan Statistics
                </h3>
            </div>
            <div class="box-body">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-qrcode"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Today's Scans</span>
                        <span class="info-box-number" id="today-scans-count">0</span>
                    </div>
                </div>
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Successful</span>
                        <span class="info-box-number" id="successful-scans-count">0</span>
                    </div>
                </div>
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Not Found</span>
                        <span class="info-box-number" id="failed-scans-count">0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scanner Tips --}}
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-lightbulb-o"></i> Scanner Tips
                </h3>
            </div>
            <div class="box-body">
                <ul class="list-unstyled">
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-check text-success"></i> <strong>Good Lighting:</strong> Scan in well-lit areas
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-check text-success"></i> <strong>Hold Steady:</strong> Keep camera still for 1-2 seconds
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-check text-success"></i> <strong>Proper Distance:</strong> 6-12 inches from QR code
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-check text-success"></i> <strong>Clean Camera:</strong> Wipe lens if blurry
                    </li>
                    <li style="margin-bottom: 10px;">
                        <i class="fa fa-check text-success"></i> <strong>Alternative:</strong> Use manual search if scanning fails
                    </li>
                </ul>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-bolt"></i> Quick Actions
                </h3>
            </div>
            <div class="box-body">
                <a href="{{ route('assets.index') }}" class="btn btn-default btn-block">
                    <i class="fa fa-list"></i> View All Assets
                </a>
                <a href="{{ route('assets.create') }}" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Add New Asset
                </a>
            </div>
        </div>
    </div>
    {{-- End Sidebar --}}
</div>
@endsection

@section('scripts')
{{-- Include Html5-QRCode library from CDN --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
$(document).ready(function() {
    let html5QrcodeScanner = null;
    let scanHistory = JSON.parse(localStorage.getItem('scanHistory') || '[]');
    let todayScans = 0;
    let successfulScans = 0;
    let failedScans = 0;

    // Load statistics from localStorage
    function loadStatistics() {
        const today = new Date().toDateString();
        const todayData = JSON.parse(localStorage.getItem('scanStats_' + today) || '{"today": 0, "successful": 0, "failed": 0}');
        
        todayScans = todayData.today || 0;
        successfulScans = todayData.successful || 0;
        failedScans = todayData.failed || 0;
        
        updateStatistics();
    }

    // Save statistics to localStorage
    function saveStatistics() {
        const today = new Date().toDateString();
        localStorage.setItem('scanStats_' + today, JSON.stringify({
            today: todayScans,
            successful: successfulScans,
            failed: failedScans
        }));
    }

    // Update statistics display
    function updateStatistics() {
        $('#today-scans-count').text(todayScans);
        $('#successful-scans-count').text(successfulScans);
        $('#failed-scans-count').text(failedScans);
    }

    // Add to scan history
    function addToScanHistory(searchTerm, success, assetInfo = null) {
        const timestamp = new Date().toLocaleString();
        const entry = {
            searchTerm: searchTerm,
            success: success,
            timestamp: timestamp,
            asset: assetInfo
        };
        
        scanHistory.unshift(entry);
        if (scanHistory.length > 10) {
            scanHistory = scanHistory.slice(0, 10);
        }
        
        localStorage.setItem('scanHistory', JSON.stringify(scanHistory));
        updateScanHistoryDisplay();
        
        // Update statistics
        todayScans++;
        if (success) {
            successfulScans++;
        } else {
            failedScans++;
        }
        saveStatistics();
        updateStatistics();
    }

    // Update scan history display
    function updateScanHistoryDisplay() {
        if (scanHistory.length === 0) {
            $('#recent-scans-list').html('<p class="text-muted text-center"><i class="fa fa-info-circle"></i> No recent scans yet.</p>');
            return;
        }
        
        let html = '<ul class="list-group">';
        scanHistory.forEach(function(entry) {
            const iconClass = entry.success ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
            const statusText = entry.success ? 'Found' : 'Not Found';
            
            html += '<li class="list-group-item">';
            html += '<i class="fa ' + iconClass + '"></i> ';
            html += '<strong>' + entry.searchTerm + '</strong> ';
            html += '<span class="label label-' + (entry.success ? 'success' : 'danger') + '">' + statusText + '</span>';
            html += '<br><small class="text-muted"><i class="fa fa-clock-o"></i> ' + entry.timestamp + '</small>';
            
            if (entry.success && entry.asset) {
                html += '<br><a href="/assets/' + entry.asset.id + '" class="btn btn-sm btn-primary" style="margin-top: 5px;">';
                html += '<i class="fa fa-eye"></i> View</a>';
            }
            
            html += '</li>';
        });
        html += '</ul>';
        
        $('#recent-scans-list').html(html);
    }

    // Start camera button
    $('#start-camera-btn').on('click', function() {
        $(this).hide();
        $('#stop-camera-btn').show();
        $('#camera-container').show();
        $('#qr-reader-results').hide();
        
        initializeScanner();
    });

    // Stop camera button
    $('#stop-camera-btn').on('click', function() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(err => console.log(err));
        }
        
        $(this).hide();
        $('#start-camera-btn').show();
        $('#camera-container').hide();
    });

    // Scan again button
    $('#scan-again-btn').on('click', function() {
        $('#qr-reader-results').hide();
        $('#start-camera-btn').trigger('click');
    });

    // Initialize QR Code Scanner
    function initializeScanner() {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", 
            { 
                fps: 10, 
                qrbox: {width: 300, height: 300},
                rememberLastUsedCamera: true,
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
            },
            /* verbose= */ false
        );
        
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }

    // On successful scan
    function onScanSuccess(decodedText, decodedResult) {
        console.log('QR Code scanned:', decodedText);
        
        // Stop scanning
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(err => console.log(err));
        }
        
        $('#camera-container').hide();
        $('#stop-camera-btn').hide();
        $('#start-camera-btn').show();
        
        // Show scanning result
        $('#scanned-text').html('<i class="fa fa-barcode"></i> <strong>' + decodedText + '</strong>');
        $('#qr-reader-results').show();
        $('#asset-quick-info').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Looking up asset...</div>');
        
        // Search for asset
        searchAsset(decodedText);
    }

    // On scan failure (ignore most errors to avoid console spam)
    function onScanFailure(error) {
        // Silently ignore scan errors (camera is actively scanning)
    }

    // Search for asset by tag or serial
    function searchAsset(searchTerm) {
        $.ajax({
            url: '/assets/search-by-qr',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                search_term: searchTerm
            },
            success: function(response) {
                if (response.success && response.asset) {
                    let asset = response.asset;
                    
                    // Update quick info
                    $('#asset-quick-info').html(`
                        <div class="well well-sm">
                            <p style="margin: 5px 0;"><strong>Asset Tag:</strong> ${asset.asset_tag}</p>
                            <p style="margin: 5px 0;"><strong>Model:</strong> ${asset.model_name || 'N/A'}</p>
                            <p style="margin: 5px 0;"><strong>Serial:</strong> ${asset.serial_number || 'N/A'}</p>
                            <p style="margin: 5px 0;"><strong>Location:</strong> ${asset.location_name || 'N/A'}</p>
                            <p style="margin: 5px 0;"><strong>Status:</strong> <span class="label label-success">${asset.status_name || 'N/A'}</span></p>
                        </div>
                    `);
                    
                    $('#view-asset-link').attr('href', '/assets/' + asset.id);
                    
                    // Add to scan history
                    addToScanHistory(searchTerm, true, asset);
                } else {
                    $('#asset-quick-info').html(`
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> Asset not found in database.
                        </div>
                    `);
                    
                    $('#view-asset-link').hide();
                    
                    // Add to scan history
                    addToScanHistory(searchTerm, false);
                }
            },
            error: function(xhr) {
                $('#asset-quick-info').html(`
                    <div class="alert alert-danger">
                        <i class="fa fa-times-circle"></i> Error searching for asset. Please try again.
                    </div>
                `);
                
                $('#view-asset-link').hide();
                
                // Add to scan history
                addToScanHistory(searchTerm, false);
            }
        });
    }

    // Manual search form
    $('#manual-search-form').on('submit', function(e) {
        e.preventDefault();
        
        let searchTerm = $('#search-term-input').val().trim();
        if (!searchTerm) {
            $('#search-result').html(`
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> Please enter an asset tag or serial number.
                </div>
            `);
            return;
        }

        // Show loading
        $('#search-result').html(`
            <div class="alert alert-info">
                <i class="fa fa-spinner fa-spin"></i> Searching for asset...
            </div>
        `);

        $.ajax({
            url: '/assets/search-by-qr',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                search_term: searchTerm
            },
            success: function(response) {
                if (response.success && response.asset) {
                    let asset = response.asset;
                    $('#search-result').html(`
                        <div class="alert alert-success">
                            <h4><i class="fa fa-check-circle"></i> Asset Found!</h4>
                            <div class="well well-sm" style="margin-top: 15px;">
                                <p style="margin: 5px 0;"><strong>Asset Tag:</strong> ${asset.asset_tag}</p>
                                <p style="margin: 5px 0;"><strong>Model:</strong> ${asset.model_name || 'N/A'}</p>
                                <p style="margin: 5px 0;"><strong>Serial:</strong> ${asset.serial_number || 'N/A'}</p>
                                <p style="margin: 5px 0;"><strong>Location:</strong> ${asset.location_name || 'N/A'}</p>
                                <p style="margin: 5px 0;"><strong>Division:</strong> ${asset.division_name || 'N/A'}</p>
                                <p style="margin: 5px 0;"><strong>Assigned To:</strong> ${asset.assigned_to_name || 'Unassigned'}</p>
                                <p style="margin: 5px 0;"><strong>Status:</strong> <span class="label label-success">${asset.status_name || 'N/A'}</span></p>
                            </div>
                            <a href="/assets/${asset.id}" class="btn btn-success btn-lg">
                                <i class="fa fa-eye"></i> View Full Details
                            </a>
                        </div>
                    `);
                    
                    // Clear search input
                    $('#search-term-input').val('');
                    
                    // Add to scan history
                    addToScanHistory(searchTerm, true, asset);
                } else {
                    $('#search-result').html(`
                        <div class="alert alert-danger">
                            <h4><i class="fa fa-times-circle"></i> Asset Not Found</h4>
                            <p>No asset found with tag or serial: <strong>${searchTerm}</strong></p>
                            <p>Please check the spelling and try again, or contact IT support.</p>
                        </div>
                    `);
                    
                    // Add to scan history
                    addToScanHistory(searchTerm, false);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error searching for asset. Please try again.';
                if (xhr.status === 404) {
                    errorMsg = 'Search endpoint not found. Please contact administrator.';
                }
                
                $('#search-result').html(`
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i> ${errorMsg}
                    </div>
                `);
                
                // Add to scan history
                addToScanHistory(searchTerm, false);
            }
        });
    });

    // Initialize on page load
    loadStatistics();
    updateScanHistoryDisplay();
});
</script>
@endsection
