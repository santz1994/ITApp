<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AssetService;
use App\Asset;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    protected $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Display asset details by QR code (mobile-friendly)
     */
    public function showAssetByQR($qrCode)
    {
        $asset = $this->assetService->getAssetByQRCode($qrCode);
        
        if (!$asset) {
            abort(404, 'Asset not found');
        }

        // Return mobile-friendly view
        return view('assets.qr-view', compact('asset'));
    }

    /**
     * Generate QR code image for asset
     */
    public function generateQRCode(Asset $asset)
    {
        $this->authorize('view', $asset);

        return response(
            QrCode::format('png')->size(300)->generate($asset->qr_code_url)
        )->header('Content-Type', 'image/png');
    }

    /**
     * Download QR code for printing
     */
    public function downloadQRCode(Asset $asset)
    {
        $this->authorize('view', $asset);

        $qrCode = QrCode::format('png')
                       ->size(400)
                       ->margin(2)
                       ->generate($asset->qr_code_url);

        return response($qrCode)
               ->header('Content-Type', 'image/png')
               ->header('Content-Disposition', 'attachment; filename="asset-' . $asset->asset_tag . '-qr.png"');
    }

    /**
     * Bulk generate QR codes for multiple assets
     */
    public function bulkGenerateQRCodes(Request $request)
    {
        $this->authorize('manage-assets');

        $assetIds = $request->input('asset_ids', []);
        $assets = Asset::whereIn('id', $assetIds)->get();

        $zip = new \ZipArchive();
        $zipFileName = 'asset-qr-codes-' . now()->format('Y-m-d-H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($assets as $asset) {
                $qrCode = QrCode::format('png')
                               ->size(400)
                               ->margin(2)
                               ->generate($asset->qr_code_url);
                
                $zip->addFromString("asset-{$asset->asset_tag}-qr.png", $qrCode);
            }
            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Failed to generate QR codes');
    }
}