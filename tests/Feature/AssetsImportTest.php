<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;
use App\Status;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Asset;

class AssetsImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_creates_assets()
    {
        Storage::fake('local');

        // create a user with admin role by default in tests
        $user = User::factory()->create();
        // assign admin role so the user can access import route
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('admin');
        }

    // Ensure required related records exist
    $model = \App\AssetModel::factory()->create(['asset_model' => 'Dell XPS']);
    // create division and supplier that match CSV values
    \App\Division::factory()->create(['name' => 'IT']);
    \App\Supplier::factory()->create(['name' => 'Dell']);
    Status::factory()->create(['name' => 'Active']);

        $csv = "Asset Tag,Serial Number,Model,Division,Supplier,Purchase Date,Warranty Months,IP Address,MAC Address,Status,Assigned To,Notes\n";
        $csv .= "ASSET123,SN001,Dell XPS,IT,Dell,2024-01-01,12,192.168.1.10,001122334455,Active,,Imported from test\n";

        $file = UploadedFile::fake()->createWithContent('assets.csv', $csv);

        $response = $this->actingAs($user)->post('/assets/import', [
            'file' => $file
        ]);

    $response->assertRedirect();

    // Ensure the import summary is present in session (created + errors)
    $response->assertSessionHas('import_summary');

    $this->assertDatabaseHas('assets', ['asset_tag' => 'ASSET123', 'serial_number' => 'SN001']);
    }
}
