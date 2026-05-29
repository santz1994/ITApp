<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DatatableController extends Controller
{
    public function assets(Request $request)
    {
        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ]);
    }

    public function tickets(Request $request)
    {
        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ]);
    }
}
