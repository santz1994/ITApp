<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MasterDataService;
use App\Services\UnifiedImportService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MasterDataController extends Controller
{
    protected $service;
    protected $importService;

    public function __construct(MasterDataService $service, UnifiedImportService $importService)
    {
        $this->middleware('auth');
        $this->middleware('role:administrator|developer');
        $this->service = $service;
        $this->importService = $importService;
    }

    // Landing page - exports overview
    public function index()
    {
        return view('admin.masterdata.index');
    }

    // Imports landing / upload form
    public function imports()
    {
        return view('admin.masterdata.import');
    }

    // Handle posted import file (Excel with multiple sheets)
    public function handleImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240'
        ]);

        $file = $request->file('file');
        $path = $file->store('imports', 'local');
        $fullPath = storage_path('app/' . $path);

        // Process import
        $result = $this->importService->importFromExcel($fullPath);

        if ($result['success']) {
            return redirect()->route('masterdata.imports')
                ->with('success', 'Import completed successfully!')
                ->with('results', $result['results']);
        } else {
            return redirect()->route('masterdata.imports')
                ->with('error', 'Import completed with errors')
                ->with('results', $result['results'])
                ->with('errors', $result['errors']);
        }
    }
    
    // Download template Excel file
    public function downloadTemplate()
    {
        $spreadsheet = $this->importService->generateTemplate();
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'master-data-template-' . date('Y-m-d') . '.xlsx';
        $tempFile = storage_path('app/temp/' . $filename);
        
        // Create temp directory if not exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
    
    // Export all master data
    public function exportAllData()
    {
        $spreadsheet = $this->importService->exportAllData();
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'master-data-export-' . date('Y-m-d-His') . '.xlsx';
        $tempFile = storage_path('app/temp/' . $filename);
        
        // Create temp directory if not exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    // Templates listing
    public function templates()
    {
        // Import template
        $templates = [
            'Master Data Template' => route('masterdata.template'),
        ];
        
        // Add asset template if exists
        if (\Illuminate\Support\Facades\Route::has('assets.download-template')) {
            $templates['Assets Template'] = route('assets.download-template');
        }

        return view('admin.masterdata.templates', compact('templates'));
    }

    // Show recent import result JSON files
    public function results()
    {
        $files = [];
        $all = \Illuminate\Support\Facades\Storage::files('imports');
        // Filter result files
        foreach ($all as $f) {
            if (strpos($f, 'results_') !== false && str_ends_with($f, '.json')) {
                $files[] = $f;
            }
        }
        // Sort by modified time desc
        usort($files, function ($a, $b) {
            return filemtime(storage_path('app/' . $b)) <=> filemtime(storage_path('app/' . $a));
        });

        return view('admin.masterdata.results', compact('files'));
    }

    // Download a specific result file (sanitized)
    public function downloadResult($file)
    {
        // Prevent path traversal - allow only filenames without directory separators
        if (strpos($file, '/') !== false || strpos($file, '..') !== false) {
            abort(400);
        }

        $path = 'imports/' . $file;
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404);
        }

        return response()->download(storage_path('app/' . $path));
    }
}
