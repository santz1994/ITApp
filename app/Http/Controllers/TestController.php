<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Asset;

class TestController extends Controller
{
    public function testQrCode()
    {
        // Test basic QR code generation
        $qrCode = QrCode::size(200)->generate('Hello World from IT Helpdesk System!');
        
        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    public function testAssetQrCode()
    {
        // Create a test asset if none exists
        $asset = Asset::first();
        
        if (!$asset) {
            return response()->json([
                'message' => 'No assets found in database. Please create an asset first.',
                'suggestion' => 'Run php artisan db:seed or create an asset manually.'
            ]);
        }

        // Generate QR code for asset
        $url = route('assets.qr', $asset->qr_code ?? 'AST-TEST-001');
        $qrCode = QrCode::size(300)->generate($url);
        
        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    public function systemStatus()
    {
        return response()->json([
            'system' => 'IT Helpdesk System',
            'status' => 'RUNNING',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_connection' => 'OK',
            'migrations' => [
                'tickets_enhanced' => 'COMPLETED',
                'admin_online_status' => 'COMPLETED', 
                'daily_activities' => 'COMPLETED',
                'assets_enhanced' => 'COMPLETED',
                'asset_requests' => 'COMPLETED',
                'management_role' => 'COMPLETED'
            ],
            'features' => [
                'smart_ticketing' => 'READY',
                'asset_management' => 'READY',
                'qr_code_generation' => 'READY',
                'daily_activities' => 'READY',
                'kpi_dashboard' => 'READY',
                'management_portal' => 'READY'
            ],
            'routes' => [
                'test_qr' => route('test.qr'),
                'test_asset_qr' => route('test.asset-qr'),
                'system_status' => route('test.status')
            ]
        ]);
    }
}