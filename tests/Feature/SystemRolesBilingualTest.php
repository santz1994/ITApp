<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemRolesBilingualTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_roles_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');
        $this->grantPermissionSafely($superAdmin, 'edit-settings');

        $response = $this->actingAs($superAdmin)->get('/system/roles');

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="systemRolesLanguageEnglish"', false);
        $response->assertSee('id="systemRolesLanguageIndonesian"', false);
        $response->assertSee('data-i18n="system.roles.section.system_roles"', false);
        $response->assertSee('data-i18n="system.roles.action.create_new_role"', false);
        $response->assertSee('data-i18n="system.roles.modal.create.title"', false);
        $response->assertSee("'system.roles.runtime.delete_confirm_prefix'", false);
        $response->assertSee('window.systemRolesLabel = getLabel;', false);
    }

    public function test_system_roles_page_includes_language_switch_behavior_hooks(): void
    {
        $superAdmin = User::factory()->create();
        $this->assignRoleSafely($superAdmin, 'super-admin');
        $this->grantPermissionSafely($superAdmin, 'edit-settings');

        $response = $this->actingAs($superAdmin)->get('/system/roles');

        $response->assertStatus(200);
        $response->assertSee("englishButton.addEventListener('click'", false);
        $response->assertSee("indonesianButton.addEventListener('click'", false);
        $response->assertSee('window.systemRolesDataTableLanguage = function()', false);
        $response->assertSee('window.systemRolesRefreshRuntimeText = function()', false);
        $response->assertSee('systemRolesUsersTable.settings()[0].oLanguage = window.systemRolesDataTableLanguage();', false);
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

    private function grantPermissionSafely(User $user, string $permissionName): void
    {
        try {
            if (!class_exists(Permission::class) || !method_exists($user, 'givePermissionTo')) {
                return;
            }

            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            $user->givePermissionTo($permissionName);
        } catch (\Throwable $exception) {
            // Keep tests resilient if permission tables are unavailable.
        }
    }
}
