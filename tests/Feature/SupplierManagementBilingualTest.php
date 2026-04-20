<?php

namespace Tests\Feature;

use App\Supplier;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\TestDataHelper;

class SupplierManagementBilingualTest extends TestCase
{
    use DatabaseTransactions;
    use TestDataHelper;

    protected $supplierAdmin;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        try {
            if (class_exists(\Database\Seeders\LocationsTableSeeder::class)) {
                (new \Database\Seeders\LocationsTableSeeder())->run();
            }
        } catch (\Throwable $e) {
        }

        try {
            if (class_exists(\Database\Seeders\RolesTableSeeder::class)) {
                (new \Database\Seeders\RolesTableSeeder())->run();
            }
        } catch (\Throwable $e) {
        }

        try {
            if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) {
                (new \Database\Seeders\TestUsersTableSeeder())->run();
            }
        } catch (\Throwable $e) {
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();

        $this->supplierAdmin = $this->testSuperAdmin;

        if (!$this->supplierAdmin) {
            $this->supplierAdmin = User::factory()->create();
        }

        $this->assignRoleSafely($this->supplierAdmin, 'developer');
    }

    public function test_supplier_index_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $this->createSupplierRecord();

        $response = $this->actingAs($this->supplierAdmin)
            ->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="supplierIndexLanguageEnglish"', false);
        $response->assertSee('id="supplierIndexLanguageIndonesian"', false);
        $response->assertSee('data-i18n="suppliers.index.table.name"', false);
        $response->assertSee('data-i18n="suppliers.index.form.title"', false);
        $response->assertSee('data-i18n="suppliers.index.action.submit"', false);
        $response->assertSee("'suppliers.index.runtime.name_required'", false);
        $response->assertSee("'suppliers.index.datatable.search'", false);
    }

    public function test_supplier_index_page_includes_language_switch_behavior_hooks(): void
    {
        $this->createSupplierRecord();

        $response = $this->actingAs($this->supplierAdmin)
            ->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee("englishButton.addEventListener('click'", false);
        $response->assertSee("indonesianButton.addEventListener('click'", false);
        $response->assertSee('window.supplierIndexRefreshRuntimeText = function()', false);
        $response->assertSee('suppliersTable.settings()[0].oLanguage = window.supplierIndexDataTableLanguage();', false);
        $response->assertSee('window.supplierIndexLabel = getLabel;', false);
        $response->assertSee('applyLanguage(getLanguage());', false);
    }

    public function test_supplier_edit_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $supplier = $this->createSupplierRecord();

        $response = $this->actingAs($this->supplierAdmin)
            ->get(route('suppliers.edit', $supplier->id));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="supplierEditLanguageEnglish"', false);
        $response->assertSee('id="supplierEditLanguageIndonesian"', false);
        $response->assertSee('data-i18n="suppliers.edit.form.title"', false);
        $response->assertSee('data-i18n="suppliers.edit.form.section.info"', false);
        $response->assertSee('data-i18n="suppliers.edit.action.submit"', false);
        $response->assertSee("'suppliers.edit.action.submitting'", false);
        $response->assertSee('window.supplierEditLabel = getLabel;', false);
    }

    public function test_supplier_show_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $supplier = $this->createSupplierRecord();

        $response = $this->actingAs($this->supplierAdmin)
            ->get(route('suppliers.show', $supplier->id));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="supplierShowLanguageEnglish"', false);
        $response->assertSee('id="supplierShowLanguageIndonesian"', false);
        $response->assertSee('data-i18n="suppliers.show.section.info"', false);
        $response->assertSee('data-i18n="suppliers.show.section.assets"', false);
        $response->assertSee('data-i18n="suppliers.show.section.invoices"', false);
        $response->assertSee('window.supplierShowLabel = getLabel;', false);
    }

    public function test_supplier_edit_and_show_pages_include_language_switch_behavior_hooks(): void
    {
        $supplier = $this->createSupplierRecord();

        $editResponse = $this->actingAs($this->supplierAdmin)
            ->get(route('suppliers.edit', $supplier->id));

        $editResponse->assertStatus(200);
        $editResponse->assertSee("englishButton.addEventListener('click'", false);
        $editResponse->assertSee("indonesianButton.addEventListener('click'", false);
        $editResponse->assertSee('window.supplierEditLabel = getLabel;', false);
        $editResponse->assertSee('applyLanguage(getLanguage());', false);

        $showResponse = $this->actingAs($this->supplierAdmin)
            ->get(route('suppliers.show', $supplier->id));

        $showResponse->assertStatus(200);
        $showResponse->assertSee("englishButton.addEventListener('click'", false);
        $showResponse->assertSee("indonesianButton.addEventListener('click'", false);
        $showResponse->assertSee('window.supplierShowLabel = getLabel;', false);
        $showResponse->assertSee('applyLanguage(getLanguage());', false);
    }

    private function createSupplierRecord(array $overrides = []): Supplier
    {
        $defaults = [
            'name' => 'Supplier Bilingual ' . uniqid(),
        ];

        return Supplier::create(array_merge($defaults, $overrides));
    }

    private function assignRoleSafely(User $user, string $roleName): void
    {
        try {
            if (!class_exists(Role::class) || !method_exists($user, 'assignRole')) {
                return;
            }

            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->assignRole($roleName);
        } catch (\Throwable $exception) {
            // Keep tests resilient if role tables are unavailable.
        }
    }
}
