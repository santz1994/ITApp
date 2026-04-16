<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Collection;

class UserRoleBadgeService
{
    private const ROLE_PRIORITY = [
        'developer',
        'super-admin',
        'admin',
        'director',
        'management',
        'human-resources',
        'receptionist',
        'user',
    ];

    private const ROLE_ALIASES = [
        'superadmin' => 'super-admin',
        'administrator' => 'admin',
        'human-resources' => 'human-resources',
        'human-resources-staff' => 'human-resources',
        'human-resources-team' => 'human-resources',
        'human-resources-department' => 'human-resources',
        'human-resources-dept' => 'human-resources',
        'humanresources' => 'human-resources',
        'human-resources-and-ga' => 'human-resources',
        'human-resources-and-general-affairs' => 'human-resources',
        'human-resources-and-admin' => 'human-resources',
        'human-resources-and-administration' => 'human-resources',
        'human-resources-and-people-ops' => 'human-resources',
        'human-resources-and-operations' => 'human-resources',
        'human_resources' => 'human-resources',
        'human resources' => 'human-resources',
        'hr' => 'human-resources',
    ];

    private const BADGE_DEFINITIONS = [
        'guest' => [
            'key' => 'guest',
            'level' => 0,
            'label_en' => 'Guest / Stranger',
            'label_id' => 'Tamu / Stranger',
            'icon' => 'fa-user-secret',
            'effect' => 'static',
            'css_variant' => 'role-badge-lv0',
            'color' => '#9CA3AF',
            'accent' => '#D5DCE4',
        ],
        'user' => [
            'key' => 'user',
            'level' => 1,
            'label_en' => 'User / The Operator',
            'label_id' => 'Pengguna / The Operator',
            'icon' => 'fa-cog',
            'effect' => 'static',
            'css_variant' => 'role-badge-lv1',
            'color' => '#64748B',
            'accent' => '#A9B3C1',
        ],
        'receptionist' => [
            'key' => 'receptionist',
            'level' => 2,
            'label_en' => 'Receptionist / The Navigator',
            'label_id' => 'Resepsionis / The Navigator',
            'icon' => 'fa-location-arrow',
            'effect' => 'soft-pulse',
            'css_variant' => 'role-badge-lv2',
            'color' => '#06B6D4',
            'accent' => '#7FE3F1',
        ],
        'human-resources' => [
            'key' => 'human-resources',
            'level' => 3,
            'label_en' => 'Human Resources / The Sync Ops',
            'label_id' => 'Sumber Daya Manusia / The Sync Ops',
            'icon' => 'fa-sitemap',
            'effect' => 'border-glow',
            'css_variant' => 'role-badge-lv3',
            'color' => '#10B981',
            'accent' => '#7FE0BE',
        ],
        'director' => [
            'key' => 'director',
            'level' => 8,
            'label_en' => 'Director / The Prime',
            'label_id' => 'Direktur / The Prime',
            'icon' => 'fa-star',
            'effect' => 'metallic-shine',
            'css_variant' => 'role-badge-lv8',
            'color' => '#F59E0B',
            'accent' => '#FAD37B',
        ],
        'management' => [
            'key' => 'management',
            'level' => 8,
            'label_en' => 'Management / The Prime',
            'label_id' => 'Manajemen / The Prime',
            'icon' => 'fa-star',
            'effect' => 'metallic-shine',
            'css_variant' => 'role-badge-lv8',
            'color' => '#F59E0B',
            'accent' => '#FAD37B',
        ],
        'admin' => [
            'key' => 'admin',
            'level' => 9,
            'label_en' => 'Administrator / The SysOp',
            'label_id' => 'Administrator / The SysOp',
            'icon' => 'fa-shield',
            'effect' => 'warning-glow',
            'css_variant' => 'role-badge-lv9',
            'color' => '#EF4444',
            'accent' => '#F9A0A0',
        ],
        'super-admin' => [
            'key' => 'super-admin',
            'level' => 9,
            'label_en' => 'Super Admin / The SysOp',
            'label_id' => 'Super Admin / The SysOp',
            'icon' => 'fa-shield',
            'effect' => 'warning-glow',
            'css_variant' => 'role-badge-lv9',
            'color' => '#EF4444',
            'accent' => '#F9A0A0',
        ],
        'developer' => [
            'key' => 'developer',
            'level' => 10,
            'label_en' => 'Developer / The Architect',
            'label_id' => 'Developer / The Architect',
            'icon' => 'fa-eye',
            'effect' => 'glitch-rgb',
            'css_variant' => 'role-badge-lv10',
            'color' => '#7C3AED',
            'accent' => '#22C55E',
        ],
    ];

    public function resolvePrimaryBadge(User $user): array
    {
        $roleKeys = $this->normalizeRoleNames(user_get_role_names($user));

        foreach (self::ROLE_PRIORITY as $roleKey) {
            if (in_array($roleKey, $roleKeys, true)) {
                return $this->buildBadge($roleKey);
            }
        }

        return $this->buildBadge('user');
    }

    public function resolveRoleSetBadges(User $user): array
    {
        $roleKeys = $this->normalizeRoleNames(user_get_role_names($user));

        $badges = [];
        foreach ($roleKeys as $roleKey) {
            if (!isset(self::BADGE_DEFINITIONS[$roleKey])) {
                continue;
            }

            $badges[] = $this->buildBadge($roleKey);
        }

        if (empty($badges)) {
            return [$this->resolvePrimaryBadge($user)];
        }

        usort($badges, static function (array $left, array $right): int {
            return (int) ($right['level'] ?? 0) <=> (int) ($left['level'] ?? 0);
        });

        return array_values($badges);
    }

    private function normalizeRoleNames(Collection $roleNames): array
    {
        $normalized = [];

        foreach ($roleNames as $roleName) {
            $key = $this->normalizeRole((string) $roleName);
            if ($key === '' || isset($normalized[$key])) {
                continue;
            }

            $normalized[$key] = true;
        }

        return array_keys($normalized);
    }

    private function normalizeRole(string $roleName): string
    {
        $normalized = strtolower(trim($roleName));
        $normalized = str_replace('_', '-', $normalized);
        $normalized = preg_replace('/\s+/', '-', $normalized) ?: '';

        if ($normalized === '') {
            return '';
        }

        return self::ROLE_ALIASES[$normalized] ?? $normalized;
    }

    private function buildBadge(string $roleKey): array
    {
        $definition = self::BADGE_DEFINITIONS[$roleKey] ?? self::BADGE_DEFINITIONS['user'];

        $definition['level_label'] = 'LV ' . (int) ($definition['level'] ?? 1);

        return $definition;
    }
}
