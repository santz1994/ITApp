<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\User;
use App\Division;
use App\Location;
use App\Role;
use App\Permission;
use App\AssetType;
use App\AssetModel;
use App\Manufacturer;
use App\Supplier;
use App\Status;
use App\WarrantyType;
use App\TicketsPriority;
use App\TicketsStatus;
use App\TicketsType;

class UnifiedImportService
{
    protected $results = [];
    protected $errors = [];
    
    /**
     * Import data from Excel file with multiple sheets
     */
    public function importFromExcel($filePath)
    {
        $this->results = [];
        $this->errors = [];
        
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            // Process each sheet based on sheet name
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                
                switch (strtolower($sheetName)) {
                    case 'divisions':
                        $this->importDivisions($sheet);
                        break;
                    case 'locations':
                        $this->importLocations($sheet);
                        break;
                    case 'users':
                        $this->importUsers($sheet);
                        break;
                    case 'roles':
                        $this->importRoles($sheet);
                        break;
                    case 'permissions':
                        $this->importPermissions($sheet);
                        break;
                    case 'asset_types':
                        $this->importAssetTypes($sheet);
                        break;
                    case 'manufacturers':
                        $this->importManufacturers($sheet);
                        break;
                    case 'asset_models':
                        $this->importAssetModels($sheet);
                        break;
                    case 'suppliers':
                        $this->importSuppliers($sheet);
                        break;
                    case 'statuses':
                        $this->importStatuses($sheet);
                        break;
                    case 'warranty_types':
                        $this->importWarrantyTypes($sheet);
                        break;
                    case 'ticket_priorities':
                        $this->importTicketPriorities($sheet);
                        break;
                    case 'ticket_statuses':
                        $this->importTicketStatuses($sheet);
                        break;
                    case 'ticket_types':
                        $this->importTicketTypes($sheet);
                        break;
                    default:
                        $this->errors[] = "Unknown sheet: {$sheetName}";
                }
            }
            
            return [
                'success' => empty($this->errors),
                'results' => $this->results,
                'errors' => $this->errors
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'results' => $this->results,
                'errors' => array_merge($this->errors, ['File error: ' . $e->getMessage()])
            ];
        }
    }
    
    /**
     * Generate template Excel file with all sheets
     */
    public function generateTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet
        
        // Add all sheets with headers
        $this->addDivisionsSheet($spreadsheet);
        $this->addLocationsSheet($spreadsheet);
        $this->addUsersSheet($spreadsheet);
        $this->addRolesSheet($spreadsheet);
        $this->addPermissionsSheet($spreadsheet);
        $this->addAssetTypesSheet($spreadsheet);
        $this->addManufacturersSheet($spreadsheet);
        $this->addAssetModelsSheet($spreadsheet);
        $this->addSuppliersSheet($spreadsheet);
        $this->addStatusesSheet($spreadsheet);
        $this->addWarrantyTypesSheet($spreadsheet);
        $this->addTicketPrioritiesSheet($spreadsheet);
        $this->addTicketStatusesSheet($spreadsheet);
        $this->addTicketTypesSheet($spreadsheet);
        
        return $spreadsheet;
    }
    
    // ==================== IMPORT METHODS ====================
    
    protected function importDivisions($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        $headers = array_shift($rows); // Remove header row
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $description = trim($row['B'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                Division::updateOrCreate(
                    ['name' => $name],
                    ['description' => $description]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Divisions row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['divisions'] = compact('imported', 'skipped');
    }
    
    protected function importLocations($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $city = trim($row['B'] ?? '');
            $address = trim($row['C'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                Location::updateOrCreate(
                    ['name' => $name],
                    ['city' => $city, 'address' => $address]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Locations row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['locations'] = compact('imported', 'skipped');
    }
    
    protected function importUsers($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $email = trim($row['B'] ?? '');
            $password = trim($row['C'] ?? '');
            $division_name = trim($row['D'] ?? '');
            $location_name = trim($row['E'] ?? '');
            $phone = trim($row['F'] ?? '');
            $role_name = trim($row['G'] ?? 'user');
            
            if (empty($name) || empty($email)) {
                $skipped++;
                continue;
            }
            
            try {
                $division = $division_name ? Division::where('name', $division_name)->first() : null;
                $location = $location_name ? Location::where('name', $location_name)->first() : null;
                
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => $password ? Hash::make($password) : Hash::make('123456'),
                        'division_id' => $division ? $division->id : null,
                        'location_id' => $location ? $location->id : null,
                        'phone' => $phone,
                        'api_token' => Str::random(80),
                        'notify_email' => true,
                        'notify_ticket_created' => true,
                        'notify_ticket_assigned' => true,
                        'notify_ticket_updated' => true,
                        'notify_meeting_approved' => true,
                        'notify_meeting_rejected' => true,
                    ]
                );
                
                // Assign role if provided
                if ($role_name !== '') {
                    $normalizedRoleName = \App\Role::normalizeName($role_name);
                    $role = Role::query()
                        ->whereIn('name', \App\Role::equivalentNames($normalizedRoleName))
                        ->whereIn('name', \App\Role::assignableNames())
                        ->first();

                    if ($role) {
                        $user->syncRoles([$role->name]);
                    }
                }
                
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Users row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['users'] = compact('imported', 'skipped');
    }
    
    protected function importRoles($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $display_name = trim($row['B'] ?? '');
            $description = trim($row['C'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }

            $normalizedName = \App\Role::normalizeName($name);
            if (!in_array($normalizedName, \App\Role::canonicalNames(), true)) {
                $this->errors[] = "Roles row {$rowNum}: role '{$name}' is not in Project canonical role list.";
                $skipped++;
                continue;
            }
            
            try {
                $roleLevels = \App\Role::projectRoleLevels();
                $payload = [
                    'display_name' => $display_name ?: ucwords(str_replace('-', ' ', $normalizedName)),
                    'description' => $description,
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('roles', 'access_level')) {
                    $payload['access_level'] = $roleLevels[$normalizedName] ?? null;
                }

                Role::updateOrCreate(
                    [
                        'name' => $normalizedName,
                        'guard_name' => config('auth.defaults.guard', 'web'),
                    ],
                    $payload
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Roles row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['roles'] = compact('imported', 'skipped');
    }
    
    protected function importPermissions($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $display_name = trim($row['B'] ?? '');
            $description = trim($row['C'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                Permission::updateOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => $display_name ?: $name,
                        'description' => $description
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Permissions row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['permissions'] = compact('imported', 'skipped');
    }
    
    protected function importAssetTypes($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $description = trim($row['B'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                AssetType::updateOrCreate(
                    ['name' => $name],
                    ['description' => $description]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Asset Types row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['asset_types'] = compact('imported', 'skipped');
    }
    
    protected function importManufacturers($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $email = trim($row['B'] ?? '');
            $phone = trim($row['C'] ?? '');
            $address = trim($row['D'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                Manufacturer::updateOrCreate(
                    ['name' => $name],
                    [
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $address
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Manufacturers row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['manufacturers'] = compact('imported', 'skipped');
    }
    
    protected function importAssetModels($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $model_number = trim($row['B'] ?? '');
            $manufacturer_name = trim($row['C'] ?? '');
            $asset_type_name = trim($row['D'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                $manufacturer = $manufacturer_name ? Manufacturer::where('name', $manufacturer_name)->first() : null;
                $asset_type = $asset_type_name ? AssetType::where('name', $asset_type_name)->first() : null;
                
                AssetModel::updateOrCreate(
                    ['name' => $name, 'model_number' => $model_number],
                    [
                        'manufacturer_id' => $manufacturer ? $manufacturer->id : null,
                        'asset_type_id' => $asset_type ? $asset_type->id : null,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Asset Models row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['asset_models'] = compact('imported', 'skipped');
    }
    
    protected function importSuppliers($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $email = trim($row['B'] ?? '');
            $phone = trim($row['C'] ?? '');
            $address = trim($row['D'] ?? '');
            $contact_person = trim($row['E'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                Supplier::updateOrCreate(
                    ['name' => $name],
                    [
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $address,
                        'contact_person' => $contact_person
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Suppliers row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['suppliers'] = compact('imported', 'skipped');
    }
    
    protected function importStatuses($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $description = trim($row['B'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                Status::updateOrCreate(
                    ['name' => $name],
                    ['description' => $description]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Statuses row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['statuses'] = compact('imported', 'skipped');
    }
    
    protected function importWarrantyTypes($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $duration_months = trim($row['B'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                WarrantyType::updateOrCreate(
                    ['name' => $name],
                    ['duration_months' => $duration_months ? (int)$duration_months : null]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Warranty Types row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['warranty_types'] = compact('imported', 'skipped');
    }
    
    protected function importTicketPriorities($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $color = trim($row['B'] ?? '#000000');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                TicketsPriority::updateOrCreate(
                    ['name' => $name],
                    ['color' => $color]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Ticket Priorities row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['ticket_priorities'] = compact('imported', 'skipped');
    }
    
    protected function importTicketStatuses($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $color = trim($row['B'] ?? '#000000');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                TicketsStatus::updateOrCreate(
                    ['name' => $name],
                    ['color' => $color]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Ticket Statuses row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['ticket_statuses'] = compact('imported', 'skipped');
    }
    
    protected function importTicketTypes($sheet)
    {
        $rows = $sheet->toArray(null, true, true, true);
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $rowNum => $row) {
            $name = trim($row['A'] ?? '');
            $description = trim($row['B'] ?? '');
            
            if (empty($name)) {
                $skipped++;
                continue;
            }
            
            try {
                TicketsType::updateOrCreate(
                    ['name' => $name],
                    ['description' => $description]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Ticket Types row {$rowNum}: " . $e->getMessage();
                $skipped++;
            }
        }
        
        $this->results['ticket_types'] = compact('imported', 'skipped');
    }
    
    // ==================== TEMPLATE GENERATION METHODS ====================
    
    protected function addDivisionsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Divisions');
        $sheet->fromArray(['Name', 'Description'], null, 'A1');
        $sheet->fromArray(['IT Department', 'Information Technology'], null, 'A2');
        $sheet->fromArray(['HR Department', 'Human Resources'], null, 'A3');
    }
    
    protected function addLocationsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Locations');
        $sheet->fromArray(['Name', 'City', 'Address'], null, 'A1');
        $sheet->fromArray(['Head Office', 'Jakarta', 'Jl. Sudirman No. 123'], null, 'A2');
    }
    
    protected function addUsersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Users');
        $sheet->fromArray(['Name', 'Email', 'Password', 'Division', 'Location', 'Phone', 'Role'], null, 'A1');
        $sheet->fromArray(['John Doe', 'john@example.com', '123456', 'IT Department', 'Head Office', '081234567890', 'user'], null, 'A2');
    }
    
    protected function addRolesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Roles');
        $sheet->fromArray(['Name', 'Display Name', 'Description'], null, 'A1');

        $row = 2;
        foreach (\App\Role::canonicalNames() as $roleName) {
            $sheet->fromArray([
                $roleName,
                ucwords(str_replace('-', ' ', $roleName)),
                'Canonical role from Project.md',
            ], null, "A{$row}");
            $row++;
        }
    }
    
    protected function addPermissionsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Permissions');
        $sheet->fromArray(['Name', 'Display Name', 'Description'], null, 'A1');
        $sheet->fromArray(['create-asset', 'Create Asset', 'Can create assets'], null, 'A2');
    }
    
    protected function addAssetTypesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Asset_Types');
        $sheet->fromArray(['Name', 'Description'], null, 'A1');
        $sheet->fromArray(['Laptop', 'Portable computers'], null, 'A2');
        $sheet->fromArray(['Desktop', 'Desktop computers'], null, 'A3');
    }
    
    protected function addManufacturersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Manufacturers');
        $sheet->fromArray(['Name', 'Email', 'Phone', 'Address'], null, 'A1');
        $sheet->fromArray(['Dell', 'sales@dell.com', '021-12345678', 'Jakarta'], null, 'A2');
    }
    
    protected function addAssetModelsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Asset_Models');
        $sheet->fromArray(['Name', 'Model Number', 'Manufacturer', 'Asset Type'], null, 'A1');
        $sheet->fromArray(['Latitude 5420', 'LAT-5420', 'Dell', 'Laptop'], null, 'A2');
    }
    
    protected function addSuppliersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Suppliers');
        $sheet->fromArray(['Name', 'Email', 'Phone', 'Address', 'Contact Person'], null, 'A1');
        $sheet->fromArray(['PT ABC', 'sales@abc.com', '021-98765432', 'Jakarta', 'John Smith'], null, 'A2');
    }
    
    protected function addStatusesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Statuses');
        $sheet->fromArray(['Name', 'Description'], null, 'A1');
        $sheet->fromArray(['Available', 'Asset is available for use'], null, 'A2');
        $sheet->fromArray(['In Use', 'Asset is currently in use'], null, 'A3');
    }
    
    protected function addWarrantyTypesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Warranty_Types');
        $sheet->fromArray(['Name', 'Duration (Months)'], null, 'A1');
        $sheet->fromArray(['Standard Warranty', '12'], null, 'A2');
        $sheet->fromArray(['Extended Warranty', '36'], null, 'A3');
    }
    
    protected function addTicketPrioritiesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ticket_Priorities');
        $sheet->fromArray(['Name', 'Color'], null, 'A1');
        $sheet->fromArray(['Low', '#28a745'], null, 'A2');
        $sheet->fromArray(['Medium', '#ffc107'], null, 'A3');
        $sheet->fromArray(['High', '#dc3545'], null, 'A4');
    }
    
    protected function addTicketStatusesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ticket_Statuses');
        $sheet->fromArray(['Name', 'Color'], null, 'A1');
        $sheet->fromArray(['Open', '#17a2b8'], null, 'A2');
        $sheet->fromArray(['In Progress', '#ffc107'], null, 'A3');
        $sheet->fromArray(['Closed', '#28a745'], null, 'A4');
    }
    
    protected function addTicketTypesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ticket_Types');
        $sheet->fromArray(['Name', 'Description'], null, 'A1');
        $sheet->fromArray(['Hardware', 'Hardware related issues'], null, 'A2');
        $sheet->fromArray(['Software', 'Software related issues'], null, 'A3');
        $sheet->fromArray(['Network', 'Network connectivity issues'], null, 'A4');
    }
    
    // ==================== EXPORT METHODS ====================
    
    /**
     * Export all master data to Excel file
     */
    public function exportAllData()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        
        // Export all data types
        $this->exportDivisionsSheet($spreadsheet);
        $this->exportLocationsSheet($spreadsheet);
        $this->exportUsersSheet($spreadsheet);
        $this->exportRolesSheet($spreadsheet);
        $this->exportPermissionsSheet($spreadsheet);
        $this->exportAssetTypesSheet($spreadsheet);
        $this->exportManufacturersSheet($spreadsheet);
        $this->exportAssetModelsSheet($spreadsheet);
        $this->exportSuppliersSheet($spreadsheet);
        $this->exportStatusesSheet($spreadsheet);
        $this->exportWarrantyTypesSheet($spreadsheet);
        $this->exportTicketPrioritiesSheet($spreadsheet);
        $this->exportTicketStatusesSheet($spreadsheet);
        $this->exportTicketTypesSheet($spreadsheet);
        
        return $spreadsheet;
    }
    
    protected function exportDivisionsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Divisions');
        $sheet->fromArray(['Name', 'Description'], null, 'A1');
        
        $divisions = Division::all();
        $row = 2;
        foreach ($divisions as $division) {
            $sheet->fromArray([$division->name, $division->description], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportLocationsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Locations');
        $sheet->fromArray(['Name', 'City', 'Address'], null, 'A1');
        
        $locations = Location::all();
        $row = 2;
        foreach ($locations as $location) {
            $sheet->fromArray([$location->name, $location->city, $location->address], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportUsersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Users');
        $sheet->fromArray(['Name', 'Email', 'Password', 'Division', 'Location', 'Phone', 'Role'], null, 'A1');
        
        $users = User::with(['division', 'location'])->get();
        $row = 2;
        foreach ($users as $user) {
            $role = $user->getRoleNames()->first() ?? '';
            $sheet->fromArray([
                $user->name,
                $user->email,
                '', // Don't export passwords
                $user->division ? $user->division->name : '',
                $user->location ? $user->location->name : '',
                $user->phone,
                $role
            ], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportRolesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Roles');
        $sheet->fromArray(['Name', 'Display Name', 'Description'], null, 'A1');
        
        $roles = Role::query()->canonical()->orderBy('name')->get();
        $row = 2;
        foreach ($roles as $role) {
            $sheet->fromArray([$role->name, $role->display_name, $role->description], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportPermissionsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Permissions');
        $sheet->fromArray(['Name', 'Display Name', 'Description'], null, 'A1');
        
        $permissions = Permission::all();
        $row = 2;
        foreach ($permissions as $permission) {
            $sheet->fromArray([$permission->name, $permission->display_name, $permission->description], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportAssetTypesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Asset_Types');
        $sheet->fromArray(['Name', 'Description'], null, 'A1');
        
        $types = AssetType::all();
        $row = 2;
        foreach ($types as $type) {
            $sheet->fromArray([$type->name, $type->description], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportManufacturersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Manufacturers');
        $sheet->fromArray(['Name', 'Email', 'Phone', 'Address'], null, 'A1');
        
        $manufacturers = Manufacturer::all();
        $row = 2;
        foreach ($manufacturers as $manufacturer) {
            $sheet->fromArray([
                $manufacturer->name,
                $manufacturer->email,
                $manufacturer->phone,
                $manufacturer->address
            ], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportAssetModelsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Asset_Models');
        $sheet->fromArray(['Name', 'Model Number', 'Manufacturer', 'Asset Type'], null, 'A1');
        
        $models = AssetModel::with(['manufacturer', 'asset_type'])->get();
        $row = 2;
        foreach ($models as $model) {
            $sheet->fromArray([
                $model->name,
                $model->model_number,
                $model->manufacturer ? $model->manufacturer->name : '',
                $model->asset_type ? $model->asset_type->name : ''
            ], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportSuppliersSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Suppliers');
        $sheet->fromArray(['Name', 'Email', 'Phone', 'Address', 'Contact Person'], null, 'A1');
        
        $suppliers = Supplier::all();
        $row = 2;
        foreach ($suppliers as $supplier) {
            $sheet->fromArray([
                $supplier->name,
                $supplier->email,
                $supplier->phone,
                $supplier->address,
                $supplier->contact_person
            ], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportStatusesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Statuses');
        $sheet->fromArray(['Name', 'Description'], null, 'A1');
        
        $statuses = Status::all();
        $row = 2;
        foreach ($statuses as $status) {
            $sheet->fromArray([$status->name, $status->description], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportWarrantyTypesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Warranty_Types');
        $sheet->fromArray(['Name', 'Duration (Months)'], null, 'A1');
        
        $types = WarrantyType::all();
        $row = 2;
        foreach ($types as $type) {
            $sheet->fromArray([$type->name, $type->duration_months], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportTicketPrioritiesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ticket_Priorities');
        $sheet->fromArray(['Name', 'Color'], null, 'A1');
        
        $priorities = TicketsPriority::all();
        $row = 2;
        foreach ($priorities as $priority) {
            $sheet->fromArray([$priority->name, $priority->color], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportTicketStatusesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ticket_Statuses');
        $sheet->fromArray(['Name', 'Color'], null, 'A1');
        
        $statuses = TicketsStatus::all();
        $row = 2;
        foreach ($statuses as $status) {
            $sheet->fromArray([$status->name, $status->color], null, "A{$row}");
            $row++;
        }
    }
    
    protected function exportTicketTypesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ticket_Types');
        $sheet->fromArray(['Name', 'Description'], null, 'A1');
        
        $types = TicketsType::all();
        $row = 2;
        foreach ($types as $type) {
            $sheet->fromArray([$type->name, $type->description], null, "A{$row}");
            $row++;
        }
    }
}
