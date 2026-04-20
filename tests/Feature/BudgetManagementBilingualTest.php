<?php

namespace Tests\Feature;

use App\Budget;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\TestDataHelper;

class BudgetManagementBilingualTest extends TestCase
{
    use DatabaseTransactions;
    use TestDataHelper;

    protected $budgetAdmin;

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

        $this->budgetAdmin = $this->testSuperAdmin;

        if (!$this->budgetAdmin) {
            $this->budgetAdmin = User::factory()->create();
        }

        $this->assignRoleSafely($this->budgetAdmin, 'developer');

        if (!$this->testDivision) {
            $this->testDivision = \App\Division::firstOrCreate([
                'name' => 'Budget Test Division',
            ], [
                'description' => 'Division for budget bilingual tests',
            ]);
        }
    }

    public function test_budget_index_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $this->createBudgetRecord();

        $response = $this->actingAs($this->budgetAdmin)
            ->get(route('budgets.index'));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="budgetIndexLanguageEnglish"', false);
        $response->assertSee('id="budgetIndexLanguageIndonesian"', false);
        $response->assertSee('data-i18n="budgets.index.table.division"', false);
        $response->assertSee('data-i18n="budgets.index.form.title"', false);
        $response->assertSee('data-i18n="budgets.index.action.submit"', false);
        $response->assertSee("'budgets.index.runtime.required_fields'", false);
        $response->assertSee("'budgets.index.datatable.search'", false);
    }

    public function test_budget_index_page_includes_language_switch_behavior_hooks(): void
    {
        $this->createBudgetRecord();

        $response = $this->actingAs($this->budgetAdmin)
            ->get(route('budgets.index'));

        $response->assertStatus(200);
        $response->assertSee("englishButton.addEventListener('click'", false);
        $response->assertSee("indonesianButton.addEventListener('click'", false);
        $response->assertSee('window.budgetIndexRefreshRuntimeText = function()', false);
        $response->assertSee('budgetsTable.settings()[0].oLanguage = window.budgetIndexDataTableLanguage();', false);
        $response->assertSee('window.budgetIndexLabel = getLabel;', false);
        $response->assertSee('applyLanguage(getLanguage());', false);
    }

    public function test_budget_edit_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $budget = $this->createBudgetRecord();

        $response = $this->actingAs($this->budgetAdmin)
            ->get(route('budgets.edit', $budget->id));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="budgetEditLanguageEnglish"', false);
        $response->assertSee('id="budgetEditLanguageIndonesian"', false);
        $response->assertSee('data-i18n="budgets.edit.form.title"', false);
        $response->assertSee('data-i18n="budgets.edit.section.info"', false);
        $response->assertSee('data-i18n="budgets.edit.action.submit"', false);
        $response->assertSee("'budgets.edit.runtime.loading'", false);
        $response->assertSee('window.budgetEditLabel = getLabel;', false);
    }

    public function test_budget_show_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $budget = $this->createBudgetRecord();

        $response = $this->actingAs($this->budgetAdmin)
            ->get(route('budgets.show', $budget->id));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="budgetShowLanguageEnglish"', false);
        $response->assertSee('id="budgetShowLanguageIndonesian"', false);
        $response->assertSee('data-i18n="budgets.show.section.info"', false);
        $response->assertSee('data-i18n="budgets.show.section.utilization"', false);
        $response->assertSee('data-i18n="budgets.show.action.edit"', false);
        $response->assertSee('window.budgetShowLabel = getLabel;', false);
    }

    public function test_budget_edit_and_show_pages_include_language_switch_behavior_hooks(): void
    {
        $budget = $this->createBudgetRecord();

        $editResponse = $this->actingAs($this->budgetAdmin)
            ->get(route('budgets.edit', $budget->id));

        $editResponse->assertStatus(200);
        $editResponse->assertSee("englishButton.addEventListener('click'", false);
        $editResponse->assertSee("indonesianButton.addEventListener('click'", false);
        $editResponse->assertSee('window.budgetEditLabel = getLabel;', false);
        $editResponse->assertSee('applyLanguage(getLanguage());', false);

        $showResponse = $this->actingAs($this->budgetAdmin)
            ->get(route('budgets.show', $budget->id));

        $showResponse->assertStatus(200);
        $showResponse->assertSee("englishButton.addEventListener('click'", false);
        $showResponse->assertSee("indonesianButton.addEventListener('click'", false);
        $showResponse->assertSee('window.budgetShowLabel = getLabel;', false);
        $showResponse->assertSee('applyLanguage(getLanguage());', false);
    }

    private function createBudgetRecord(array $overrides = []): Budget
    {
        $defaults = [
            'division_id' => $this->testDivision->id,
            'year' => (int) date('Y'),
            'total' => 12000000,
        ];

        return Budget::create(array_merge($defaults, $overrides));
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
