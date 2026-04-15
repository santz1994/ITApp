<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$results = [];

try {
    $r = DB::select('select count(*) as c from assets');
    $results['assets_count'] = $r[0]->c ?? null;
} catch (\Exception $e) {
    $results['assets_count_error'] = $e->getMessage();
}

try {
    $r = DB::select("select count(*) as c from assets where serial_number is null or serial_number = ''");
    $results['empty_serials'] = $r[0]->c ?? null;
} catch (\Exception $e) {
    $results['empty_serials_error'] = $e->getMessage();
}

try {
    $r = DB::select("select serial_number, count(*) as c from assets group by serial_number having c>1");
    $results['duplicate_serials'] = $r;
} catch (\Exception $e) {
    $results['duplicate_serials_error'] = $e->getMessage();
}

try {
    $r = DB::select("select count(*) as c from asset_requests where request_number is null or request_number = ''");
    $results['asset_requests_missing_request_number'] = $r[0]->c ?? null;
} catch (\Exception $e) {
    $results['asset_requests_missing_request_number_error'] = $e->getMessage();
}

try {
    $r = DB::select("select count(*) as c from assets where purchase_order_id is not null and purchase_order_id not in (select id from purchase_orders)");
    $results['assets_orphan_po'] = $r[0]->c ?? null;
} catch (\Exception $e) {
    $results['assets_orphan_po_error'] = $e->getMessage();
}

print_r($results);
