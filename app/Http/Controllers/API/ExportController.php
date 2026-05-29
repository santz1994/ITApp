<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function listExports(Request $request)
    {
        return response()->json(['data' => [], 'pagination' => []]);
    }

    public function getExportStatus($exportId)
    {
        return response()->json(['status' => 'error', 'message' => 'Export not found'], 404);
    }

    public function downloadExport($exportId)
    {
        return response()->json(['status' => 'error', 'message' => 'Export not found'], 404);
    }
}
