<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementBilingualTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_index_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="usersIndexLanguageEnglish"', false);
        $response->assertSee('id="usersIndexLanguageIndonesian"', false);
        $response->assertSee('data-i18n="users.index.table.name"', false);
        $response->assertSee('data-i18n="users.index.quick_create.title"', false);
        $response->assertSee('data-i18n="users.index.quick_create.action.submit"', false);
        $response->assertSee("'users.index.runtime.confirm_delete_selected'", false);
        $response->assertSee("'users.index.datatable.search'", false);
    }

    public function test_user_management_index_includes_language_switch_behavior_hooks(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee("englishButton.addEventListener('click'", false);
        $response->assertSee("indonesianButton.addEventListener('click'", false);
        $response->assertSee('window.usersIndexRefreshRuntimeText = function()', false);
        $response->assertSee('table.settings()[0].oLanguage = window.usersIndexDataTableLanguage();', false);
        $response->assertSee('window.usersIndexLabel = getLabel;', false);
        $response->assertSee('applyLanguage(getLanguage());', false);
    }

    public function test_user_management_create_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="userCreateLanguageEnglish"', false);
        $response->assertSee('id="userCreateLanguageIndonesian"', false);
        $response->assertSee('data-i18n="users.create.form.title"', false);
        $response->assertSee('data-i18n="users.create.section.basic"', false);
        $response->assertSee('data-i18n="users.create.action.submit"', false);
        $response->assertSee("'users.create.runtime.loading'", false);
        $response->assertSee('window.userCreateLabel = getLabel;', false);
    }

    public function test_user_management_edit_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');
        $targetUser = User::factory()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/users/' . $targetUser->id . '/edit');

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="userEditLanguageEnglish"', false);
        $response->assertSee('id="userEditLanguageIndonesian"', false);
        $response->assertSee('data-i18n="users.edit.form.title"', false);
        $response->assertSee('data-i18n="users.edit.section.basic"', false);
        $response->assertSee('data-i18n="users.edit.action.submit"', false);
        $response->assertSee("'users.edit.runtime.loading'", false);
        $response->assertSee('window.userEditLabel = getLabel;', false);
    }

    public function test_user_management_create_and_edit_pages_include_language_switch_behavior_hooks(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');

        $createResponse = $this->actingAs($superAdmin)->get('/admin/users/create');
        $createResponse->assertStatus(200);
        $createResponse->assertSee("englishButton.addEventListener('click'", false);
        $createResponse->assertSee("indonesianButton.addEventListener('click'", false);
        $createResponse->assertSee('window.userCreateLabel = getLabel;', false);
        $createResponse->assertSee('applyLanguage(getLanguage());', false);

        $targetUser = User::factory()->create();
        $editResponse = $this->actingAs($superAdmin)->get('/admin/users/' . $targetUser->id . '/edit');
        $editResponse->assertStatus(200);
        $editResponse->assertSee("englishButton.addEventListener('click'", false);
        $editResponse->assertSee("indonesianButton.addEventListener('click'", false);
        $editResponse->assertSee('window.userEditLabel = getLabel;', false);
        $editResponse->assertSee('applyLanguage(getLanguage());', false);
    }

    public function test_user_management_roles_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');

        $response = $this->actingAs($superAdmin)->get('/users/roles');

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="userRolesLanguageEnglish"', false);
        $response->assertSee('id="userRolesLanguageIndonesian"', false);
        $response->assertSee('data-i18n="users.roles.header.title"', false);
        $response->assertSee('data-i18n="users.roles.table.role_name"', false);
        $response->assertSee('data-i18n="users.roles.modal.title"', false);
        $response->assertSee("'users.roles.runtime.modal.loading'", false);
        $response->assertSee('window.userRolesLabel = getLabel;', false);
    }

    public function test_user_management_roles_page_includes_language_switch_behavior_hooks(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');

        $response = $this->actingAs($superAdmin)->get('/users/roles');

        $response->assertStatus(200);
        $response->assertSee("englishButton.addEventListener('click'", false);
        $response->assertSee("indonesianButton.addEventListener('click'", false);
        $response->assertSee('window.userRolesDataTableLanguage = function()', false);
        $response->assertSee('window.userRolesRefreshRuntimeText = function()', false);
        $response->assertSee('userRolesTable.settings()[0].oLanguage = window.userRolesDataTableLanguage();', false);
        $response->assertSee('applyLanguage(getLanguage());', false);
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
