<?php

namespace App\Imports;

use App\Asset;
use App\AssetModel;
use App\Division;
use App\Supplier;
use App\Status;
use App\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

/**
 * Simple CSV-only importer used as a fallback when maatwebsite/excel is not available.
 */
class AssetsCsvImport
{
    protected $path;
    protected $created = 0;
    protected $errors = [];

    public function __construct($file)
    {
        $this->path = is_string($file) ? $file : $file->getRealPath();
    }

    public function import()
    {
        if (!file_exists($this->path)) {
            throw new \InvalidArgumentException('Import file not found');
        }

        $handle = fopen($this->path, 'r');
        $header = null;

        $rowIndex = 0;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (!$header) {
                $header = array_map(fn($h) => strtolower(trim($h)), $row);
                continue;
            }

            $rowData = array_combine($header, $row);

            // Row-level validation
            $rowNumber = $rowIndex + 2; // header is row 1, data starts at row 2

            $validator = ValidatorFacade::make($rowData, [
                'asset tag' => 'required|string|max:255',
                'serial number' => 'nullable|string|max:255',
                'model' => 'required|string',
                'division' => 'required|string',
                'supplier' => 'nullable|string',
                'purchase date' => 'nullable|date',
                'warranty months' => 'nullable|integer',
                'ip address' => 'nullable|ip',
                'mac address' => 'nullable|string',
                'status' => 'required|string'
            ], [], []);

            if ($validator->fails()) {
                $this->errors[] = ['row' => $rowNumber, 'errors' => $validator->errors()->all(), 'data' => $rowData];
                continue;
            }

            try {
                // Look up related models (do not auto-create AssetModel because of FK constraints)
                $model = AssetModel::where('asset_model', $rowData['model'])->first();
                $division = Division::firstOrCreate(['name' => $rowData['division']]);
                $supplier = !empty($rowData['supplier']) ? Supplier::firstOrCreate(['name' => $rowData['supplier']]) : null;
                $status = Status::firstOrCreate(['name' => $rowData['status']]);
                $assignedToName = $rowData['assigned to'] ?? $rowData['assigned_to'] ?? null;
                $assignedTo = $assignedToName ? User::where('name', $assignedToName)->first() : null;

                if (!$model) {
                    $this->errors[] = ['row' => $rowNumber, 'errors' => ['Model not found: ' . ($rowData['model'] ?? '')], 'data' => $rowData];
                    continue;
                }

                $tag = trim($rowData['asset tag'] ?? $rowData['asset_tag'] ?? '');

                // Prevent DB unique constraint errors by skipping rows with duplicate asset tags.
                if (!empty($tag) && \App\Asset::where('asset_tag', $tag)->exists()) {
                    $this->errors[] = ['row' => $rowNumber, 'error' => 'Duplicate asset tag: ' . $tag, 'data' => $rowData];
                    $rowIndex++;
                    continue;
                }

                Asset::create([
                    'asset_tag' => $tag,
                    'serial_number' => $rowData['serial number'] ?? $rowData['serial_number'] ?? null,
                    'model_id' => $model->id,
                    'division_id' => $division->id,
                    'supplier_id' => $supplier?->id,
                    'purchase_date' => !empty($rowData['purchase date']) ? Carbon::parse($rowData['purchase date']) : null,
                    'warranty_months' => $rowData['warranty months'] ?? null,
                    'ip_address' => $rowData['ip address'] ?? null,
                    'mac_address' => $rowData['mac address'] ?? null,
                    'status_id' => $status->id,
                    'assigned_to' => $assignedTo?->id,
                    'notes' => $rowData['notes'] ?? null,
                ]);

                $this->created++;
            } catch (\Throwable $e) {
                Log::error('AssetsCsvImport error', ['error' => $e->getMessage(), 'row' => $rowData]);
                $this->errors[] = ['row' => $rowNumber, 'error' => $e->getMessage(), 'data' => $rowData];
            }
            $rowIndex++;
        }

        fclose($handle);

        return ['created' => $this->created, 'errors' => $this->errors];
    }
}
