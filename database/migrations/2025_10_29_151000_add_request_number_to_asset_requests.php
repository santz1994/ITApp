<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasColumn('asset_requests', 'request_number')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                $table->string('request_number')->nullable()->unique()->after('id');
            });

            // Backfill existing rows with generated numbers AR-YYYY-0001 ...
            $rows = DB::table('asset_requests')->orderBy('created_at')->get();
            $yearCounters = [];
            foreach ($rows as $row) {
                $year = date('Y', strtotime($row->created_at ?? now()));
                if (! isset($yearCounters[$year])) {
                    $yearCounters[$year] = 0;
                }
                $yearCounters[$year]++;
                $num = str_pad($yearCounters[$year], 4, '0', STR_PAD_LEFT);
                $reqNum = "AR-{$year}-{$num}";
                DB::table('asset_requests')->where('id', $row->id)->update(['request_number' => $reqNum]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('asset_requests', 'request_number')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                $table->dropUnique(['request_number']);
                $table->dropColumn('request_number');
            });
        }
    }
};
