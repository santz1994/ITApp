<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\AssetModel;
use App\Asset;
use App\Status;
use App\User;
use App\Division;
use App\Supplier;

class AssetsImportEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCsv(array $rows)
    {
        $path = sys_get_temp_dir() . '/assets_import_' . uniqid() . '.csv';
        $handle = fopen($path, 'w');
        // header
        fputcsv($handle, ['Asset Tag','Serial Number','Model','Division','Supplier','Purchase Date','Warranty Months','IP Address','MAC Address','Status','Assigned To','Notes']);
        foreach ($rows as $r) {
            fputcsv($handle, $r);
        }
        fclose($handle);
        return $path;
    }

    public function test_missing_model_reports_error_and_no_asset_created()
    {
        // No AssetModel created on purpose
        $status = Status::factory()->create(['name' => 'Available']);
        $division = Division::factory()->create(['name' => 'IT']);

        $csv = $this->makeCsv([
            ['TAG001','SN001','NonExistentModel','IT','Acme','2025-01-01','12','','','Available','','']
        ]);

        $importer = new \App\Imports\AssetsCsvImport($csv);
        $result = $importer->import();

        $this->assertEquals(0, $result['created']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('Model not found', $result['errors'][0]['errors'][0]);
    }

    public function test_invalid_purchase_date_is_reported()
    {
        $model = AssetModel::factory()->create(['asset_model' => 'TestModel']);
        $status = Status::factory()->create(['name' => 'Available']);

        $csv = $this->makeCsv([
            ['TAG002','SN002','TestModel','IT','Acme','not-a-date','12','','','Available','','']
        ]);

        $importer = new \App\Imports\AssetsCsvImport($csv);
        $result = $importer->import();

        $this->assertEquals(0, $result['created']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('The purchase date is not a valid date', implode(' ', $result['errors'][0]['errors']));
    }

    public function test_duplicate_asset_tag_is_flagged()
    {
        $model = AssetModel::factory()->create(['asset_model' => 'TestModel']);
        $status = Status::factory()->create(['name' => 'Available']);

        // Create existing asset with same tag
        Asset::factory()->create(['asset_tag' => 'DUP001', 'model_id' => $model->id, 'status_id' => $status->id]);

        $csv = $this->makeCsv([
            ['DUP001','SN-DUP','TestModel','IT','Acme','2025-03-01','12','','','Available','','']
        ]);

        $importer = new \App\Imports\AssetsCsvImport($csv);
        $result = $importer->import();

        // Currently importer does not check duplicates; expect error - if not, this test will help drive change
        $this->assertEquals(0, $result['created']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_medium_csv_import_streams_without_exhausting_memory()
    {
        $model = AssetModel::factory()->create(['asset_model' => 'BulkModel']);
        $status = Status::factory()->create(['name' => 'Available']);

        $rows = [];
        for ($i = 0; $i < 200; $i++) {
            $rows[] = ["BULK$i","SN$i","BulkModel","IT","Acme","2025-01-01","12","","","Available","",""];
        }

        $csv = $this->makeCsv($rows);
        $importer = new \App\Imports\AssetsCsvImport($csv);
        $result = $importer->import();

        $this->assertEquals(200, $result['created']);
        $this->assertEmpty($result['errors']);
    }
}
