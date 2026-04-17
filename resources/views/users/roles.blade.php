@extends('layouts.app')

@section('main-content')
@php
    $normalizeRole = function (?string $roleName): string {
        return strtolower(str_replace([' ', '_'], '-', (string) $roleName));
    };

    $getCanonicalRoleKey = function (?string $roleName) use ($normalizeRole): string {
        $key = $normalizeRole($roleName);

        $aliases = [
            'guest' => 'guest',
            'user' => 'user',
            'receptionist' => 'receptionist',
            'human-resources' => 'human-resources',
            'hr' => 'human-resources',
            'director' => 'director',
            'management' => 'director',
            'administrator' => 'administrator',
            'admin' => 'administrator',
            'super-admin' => 'developer',
            'developer' => 'developer',
        ];

        return $aliases[$key] ?? 'user';
    };

    $getRoleLevel = function (?string $roleName) use ($getCanonicalRoleKey): int {
        $key = $getCanonicalRoleKey($roleName);
        $levelMap = [
            'guest' => 0,
            'user' => 1,
            'receptionist' => 2,
            'human-resources' => 3,
            'director' => 8,
            'administrator' => 9,
            'developer' => 10,
        ];

        return $levelMap[$key] ?? 1;
    };

    $getRoleBadgeClass = function (?string $roleName) use ($getCanonicalRoleKey): string {
        $key = $getCanonicalRoleKey($roleName);

        $classMap = [
            'guest' => 'role-badge-lv0',
            'user' => 'role-badge-lv1',
            'receptionist' => 'role-badge-lv2',
            'human-resources' => 'role-badge-lv3',
            'director' => 'role-badge-lv8',
            'administrator' => 'role-badge-lv9',
            'developer' => 'role-badge-lv10',
        ];

        return $classMap[$key] ?? 'role-badge-lv1';
    };

    $getRoleShortDisplay = function (?string $roleName) use ($getCanonicalRoleKey): string {
        $key = $getCanonicalRoleKey($roleName);
        $names = [
            'guest' => 'Guest',
            'user' => 'User',
            'receptionist' => 'Receptionist',
            'human-resources' => 'Human Resources',
            'director' => 'Director',
            'administrator' => 'Administrator',
            'developer' => 'Developer',
        ];

        return $names[$key] ?? 'User';
    };

    $getRoleDisplay = function (?string $roleName) use ($getCanonicalRoleKey): string {
        $key = $getCanonicalRoleKey($roleName);
        $names = [
            'guest' => 'Guest',
            'user' => 'User',
            'receptionist' => 'Receptionist',
            'human-resources' => 'Human Resources',
            'director' => 'Director (Management)',
            'administrator' => 'Administrator (IT Support Staff)',
            'developer' => 'Developer (IT Programmer Staff)',
        ];

        return $names[$key] ?? 'User';
    };
@endphp

<section class="content-header">
    <h1>
        <span data-i18n="users.roles.header.title">User Roles Management</span>
        <small data-i18n="users.roles.header.subtitle">Manage user roles and permissions</small>
    </h1>
    <div class="pull-right" style="margin-top: -34px;">
        <div class="btn-group btn-group-xs" role="group" aria-label="User Roles Language Toggle">
            <button type="button" class="btn btn-default" id="userRolesLanguageEnglish" data-lang="en">EN</button>
            <button type="button" class="btn btn-default" id="userRolesLanguageIndonesian" data-lang="id">ID</button>
        </div>
    </div>
    <div class="clearfix"></div>
    <ol class="breadcrumb">
        <li><a href="{{ url('/home') }}"><i class="fa fa-dashboard"></i> <span data-i18n="users.roles.breadcrumb.home">Home</span></a></li>
        <li><a href="{{ route('users.index') }}" data-i18n="users.roles.breadcrumb.users">Users</a></li>
        <li class="active" data-i18n="users.roles.breadcrumb.roles">Roles</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-shield"></i> <span data-i18n="users.roles.section.overview">System Roles Overview</span>
                    </h3>
                    <div class="box-tools pull-right">
                        @can('manageRoles', App\User::class)
                            <a href="{{ route('system.roles') }}" class="btn btn-primary btn-sm" data-i18n-title="users.roles.action.manage_roles_title" title="Manage roles and permissions">
                                <i class="fa fa-cog"></i> <span data-i18n="users.roles.action.manage_roles">Manage Roles & Permissions</span>
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="box-body">
                    @if(isset($roles) && count($roles) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="rolesTable">
                                <thead>
                                    <tr>
                                        <th data-i18n="users.roles.table.role_name">Role Name</th>
                                        <th data-i18n="users.roles.table.display_name">Display Name</th>
                                        <th data-i18n="users.roles.table.users">Users</th>
                                        <th data-i18n="users.roles.table.permissions">Permissions</th>
                                        <th data-i18n="users.roles.table.created">Created</th>
                                        <th style="width: 220px;" data-i18n="users.roles.table.actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                    @php
                                        $roleCanonical = $getCanonicalRoleKey($role->name);
                                        $roleLevel = $getRoleLevel($role->name);
                                        $badgeClass = $getRoleBadgeClass($role->name);
                                        $roleShort = $getRoleShortDisplay($role->name);
                                        $roleDisplay = $getRoleDisplay($role->name);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="role-badge {{ $badgeClass }}"
                                                  data-role-badge-key="{{ $roleCanonical }}"
                                                  data-role-badge-level="{{ $roleLevel }}">
                                                <span class="role-badge-name">{{ $roleShort }}</span>
                                                <span class="role-badge-level">LV {{ $roleLevel }}</span>
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $roleDisplay }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-blue" title="{{ $role->users->count() }} users assigned">
                                                {{ $role->users->count() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($role->permissions && $role->permissions->count() > 0)
                                                <span class="badge bg-green" title="{{ $role->permissions->count() }} permissions">
                                                    {{ $role->permissions->count() }} <span data-i18n="users.roles.table.permissions_suffix">permissions</span>
                                                </span>
                                            @else
                                                <span class="text-muted"><em data-i18n="users.roles.table.no_permissions">No permissions</em></span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $role->created_at ? $role->created_at->format('M d, Y') : 'N/A' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button"
                                                        class="btn btn-info"
                                                        data-toggle="modal"
                                                        data-target="#roleDetailsModal"
                                                        data-role-id="{{ $role->id }}"
                                                        data-role-name="{{ $roleShort }}"
                                                        data-role-display="{{ $roleDisplay }}"
                                                        data-role-level="{{ $roleLevel }}"
                                                        data-role-users="{{ $role->users->count() }}"
                                                        data-role-permissions="{{ $role->permissions->count() }}"
                                                        data-role-users-json="{{ json_encode($role->users->map(function($u) { return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]; })->values()) }}"
                                                        data-role-permissions-json="{{ json_encode($role->permissions->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; })->values()) }}"
                                                        onclick="showRoleDetails(this)"
                                                        data-i18n-title="users.roles.action.details_title"
                                                        title="View role details">
                                                    <i class="fa fa-eye"></i> <span data-i18n="users.roles.action.details">Details</span>
                                                </button>
                                                <a href="{{ route('users.index') }}?role={{ $role->name }}"
                                                   class="btn btn-primary"
                                                   data-i18n-title="users.roles.action.users_title"
                                                   title="View users with this role">
                                                    <i class="fa fa-users"></i> <span data-i18n="users.roles.action.users">Users</span>
                                                </a>

                                                @can('manageRoles', App\User::class)
                                                    <a href="{{ route('system.roles') }}"
                                                       class="btn btn-warning"
                                                       data-i18n-title="users.roles.action.manage_title"
                                                       title="Edit this role">
                                                        <i class="fa fa-edit"></i> <span data-i18n="users.roles.action.manage">Manage</span>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="callout callout-info">
                            <h4><i class="fa fa-info-circle"></i> <span data-i18n="users.roles.empty.title">No Roles Found</span></h4>
                            <p data-i18n="users.roles.empty.description">No user roles are currently defined in the system.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if(isset($roles) && count($roles) > 0)
            @foreach($roles as $role)
            @php
                $roleShort = $getRoleShortDisplay($role->name);
                $roleLevel = $getRoleLevel($role->name);
                $badgeClass = $getRoleBadgeClass($role->name);
                $roleDisplay = $getRoleDisplay($role->name);
            @endphp
            <div class="col-md-6 col-lg-3">
                <div class="box box-widget">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <span class="role-badge {{ $badgeClass }}" data-role-badge-level="{{ $roleLevel }}">
                                <span class="role-badge-name">{{ $roleShort }}</span>
                                <span class="role-badge-level">LV {{ $roleLevel }}</span>
                            </span>
                        </h3>
                    </div>
                    <div class="box-body">
                        <dl class="row">
                            <dt class="col-sm-6" data-i18n="users.roles.card.assigned_users">Assigned Users:</dt>
                            <dd class="col-sm-6"><strong>{{ $role->users->count() }}</strong></dd>

                            <dt class="col-sm-6" data-i18n="users.roles.card.permissions">Permissions:</dt>
                            <dd class="col-sm-6"><strong>{{ $role->permissions ? $role->permissions->count() : 0 }}</strong></dd>

                            <dt class="col-sm-6" data-i18n="users.roles.card.created">Created:</dt>
                            <dd class="col-sm-6"><small>{{ $role->created_at ? $role->created_at->format('M d, Y') : 'N/A' }}</small></dd>
                        </dl>
                    </div>
                    <div class="box-footer">
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <button type="button"
                                    class="btn btn-info"
                                    data-toggle="modal"
                                    data-target="#roleDetailsModal"
                                    data-role-id="{{ $role->id }}"
                                    data-role-name="{{ $roleShort }}"
                                    data-role-display="{{ $roleDisplay }}"
                                    data-role-level="{{ $roleLevel }}"
                                    data-role-users="{{ $role->users->count() }}"
                                    data-role-permissions="{{ $role->permissions->count() }}"
                                    data-role-users-json="{{ json_encode($role->users->map(function($u) { return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]; })->values()) }}"
                                    data-role-permissions-json="{{ json_encode($role->permissions->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; })->values()) }}"
                                    onclick="showRoleDetails(this)"
                                    style="flex: 1;">
                                <i class="fa fa-eye"></i> <span data-i18n="users.roles.action.details">Details</span>
                            </button>
                            @can('manageRoles', App\User::class)
                                <a href="{{ route('system.roles') }}" class="btn btn-warning" style="flex: 1;">
                                    <i class="fa fa-cog"></i> <span data-i18n="users.roles.action.manage">Manage</span>
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</section>

<div class="modal fade" id="roleDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-shield"></i> <span data-i18n="users.roles.modal.title">Role Details</span>
                </h4>
            </div>
            <div class="modal-body">
                <dl class="row" id="roleDetailsContent">
                    <dt class="col-sm-3" data-i18n="users.roles.modal.role_name">Role Name:</dt>
                    <dd class="col-sm-9"><strong id="detailRoleName">-</strong></dd>

                    <dt class="col-sm-3" data-i18n="users.roles.modal.display_name">Display Name:</dt>
                    <dd class="col-sm-9"><strong id="detailRoleDisplay">-</strong></dd>

                    <dt class="col-sm-3" data-i18n="users.roles.modal.level">Access Level:</dt>
                    <dd class="col-sm-9"><strong id="detailRoleLevel">LV 1</strong></dd>

                    <dt class="col-sm-3" data-i18n="users.roles.modal.users">Users Assigned:</dt>
                    <dd class="col-sm-9"><span class="badge badge-primary" id="detailRoleUsers">0</span></dd>

                    <dt class="col-sm-3" data-i18n="users.roles.modal.permissions">Permissions:</dt>
                    <dd class="col-sm-9"><span class="badge badge-success" id="detailRolePermissions">0</span></dd>
                </dl>

                <hr>

                <h5 data-i18n="users.roles.modal.users_list">Users with this role:</h5>
                <div id="roleUsersList" class="well well-sm">
                    <div class="text-center text-muted">
                        <i class="fa fa-spinner fa-spin"></i> <span data-i18n="users.roles.runtime.modal.loading">Loading...</span>
                    </div>
                </div>

                <h5 data-i18n="users.roles.modal.permissions_list">Permissions for this role:</h5>
                <div id="rolePermissionsList" class="well well-sm">
                    <div class="text-center text-muted">
                        <i class="fa fa-spinner fa-spin"></i> <span data-i18n="users.roles.runtime.modal.loading">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @can('manageRoles', App\User::class)
                    <a href="{{ route('system.roles') }}" class="btn btn-warning">
                        <i class="fa fa-cog"></i> <span data-i18n="users.roles.modal.edit_role">Edit Role</span>
                    </a>
                @endcan
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <span data-i18n="users.roles.modal.close">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var userRolesTable = null;

(function() {
    var translations = {
        en: {
            'users.roles.header.title': 'User Roles Management',
            'users.roles.header.subtitle': 'Manage user roles and permissions',
            'users.roles.breadcrumb.home': 'Home',
            'users.roles.breadcrumb.users': 'Users',
            'users.roles.breadcrumb.roles': 'Roles',
            'users.roles.section.overview': 'System Roles Overview',
            'users.roles.action.manage_roles': 'Manage Roles & Permissions',
            'users.roles.action.manage_roles_title': 'Manage roles and permissions',
            'users.roles.table.role_name': 'Role Name',
            'users.roles.table.display_name': 'Display Name',
            'users.roles.table.users': 'Users',
            'users.roles.table.permissions': 'Permissions',
            'users.roles.table.created': 'Created',
            'users.roles.table.actions': 'Actions',
            'users.roles.table.permissions_suffix': 'permissions',
            'users.roles.table.no_permissions': 'No permissions',
            'users.roles.action.details': 'Details',
            'users.roles.action.users': 'Users',
            'users.roles.action.manage': 'Manage',
            'users.roles.action.details_title': 'View role details',
            'users.roles.action.users_title': 'View users with this role',
            'users.roles.action.manage_title': 'Edit this role',
            'users.roles.empty.title': 'No Roles Found',
            'users.roles.empty.description': 'No user roles are currently defined in the system.',
            'users.roles.card.assigned_users': 'Assigned Users:',
            'users.roles.card.permissions': 'Permissions:',
            'users.roles.card.created': 'Created:',
            'users.roles.modal.title': 'Role Details',
            'users.roles.modal.role_name': 'Role Name:',
            'users.roles.modal.display_name': 'Display Name:',
            'users.roles.modal.level': 'Access Level:',
            'users.roles.modal.users': 'Users Assigned:',
            'users.roles.modal.permissions': 'Permissions:',
            'users.roles.modal.users_list': 'Users with this role:',
            'users.roles.modal.permissions_list': 'Permissions for this role:',
            'users.roles.modal.edit_role': 'Edit Role',
            'users.roles.modal.close': 'Close',
            'users.roles.runtime.modal.loading': 'Loading...',
            'users.roles.runtime.modal.no_users': 'No users assigned to this role',
            'users.roles.runtime.modal.no_permissions': 'No permissions assigned to this role',
            'users.roles.runtime.modal.users_error': 'Error loading users. Please try again.',
            'users.roles.runtime.modal.permissions_error': 'Error loading permissions. Please try again.',
            'users.roles.datatable.search': 'Quick Search:',
            'users.roles.datatable.zero_records': 'No matching roles found',
            'users.roles.datatable.empty_table': 'No role data available'
        },
        id: {
            'users.roles.header.title': 'Manajemen Peran Pengguna',
            'users.roles.header.subtitle': 'Kelola peran dan izin pengguna',
            'users.roles.breadcrumb.home': 'Beranda',
            'users.roles.breadcrumb.users': 'Pengguna',
            'users.roles.breadcrumb.roles': 'Peran',
            'users.roles.section.overview': 'Ringkasan Peran Sistem',
            'users.roles.action.manage_roles': 'Kelola Peran & Izin',
            'users.roles.action.manage_roles_title': 'Kelola peran dan izin',
            'users.roles.table.role_name': 'Nama Peran',
            'users.roles.table.display_name': 'Nama Tampilan',
            'users.roles.table.users': 'Pengguna',
            'users.roles.table.permissions': 'Izin',
            'users.roles.table.created': 'Dibuat',
            'users.roles.table.actions': 'Aksi',
            'users.roles.table.permissions_suffix': 'izin',
            'users.roles.table.no_permissions': 'Tidak ada izin',
            'users.roles.action.details': 'Detail',
            'users.roles.action.users': 'Pengguna',
            'users.roles.action.manage': 'Kelola',
            'users.roles.action.details_title': 'Lihat detail peran',
            'users.roles.action.users_title': 'Lihat pengguna dengan peran ini',
            'users.roles.action.manage_title': 'Ubah peran ini',
            'users.roles.empty.title': 'Peran Tidak Ditemukan',
            'users.roles.empty.description': 'Belum ada peran pengguna yang didefinisikan dalam sistem.',
            'users.roles.card.assigned_users': 'Pengguna Ditugaskan:',
            'users.roles.card.permissions': 'Izin:',
            'users.roles.card.created': 'Dibuat:',
            'users.roles.modal.title': 'Detail Peran',
            'users.roles.modal.role_name': 'Nama Peran:',
            'users.roles.modal.display_name': 'Nama Tampilan:',
            'users.roles.modal.level': 'Level Akses:',
            'users.roles.modal.users': 'Pengguna Ditugaskan:',
            'users.roles.modal.permissions': 'Izin:',
            'users.roles.modal.users_list': 'Pengguna dengan peran ini:',
            'users.roles.modal.permissions_list': 'Izin untuk peran ini:',
            'users.roles.modal.edit_role': 'Ubah Peran',
            'users.roles.modal.close': 'Tutup',
            'users.roles.runtime.modal.loading': 'Memuat...',
            'users.roles.runtime.modal.no_users': 'Tidak ada pengguna untuk peran ini',
            'users.roles.runtime.modal.no_permissions': 'Tidak ada izin untuk peran ini',
            'users.roles.runtime.modal.users_error': 'Gagal memuat pengguna. Silakan coba lagi.',
            'users.roles.runtime.modal.permissions_error': 'Gagal memuat izin. Silakan coba lagi.',
            'users.roles.datatable.search': 'Pencarian Cepat:',
            'users.roles.datatable.zero_records': 'Tidak ada peran yang cocok',
            'users.roles.datatable.empty_table': 'Tidak ada data peran'
        }
    };

    var currentLanguage = 'en';
    var userId = '{{ (int) auth()->id() }}';
    var languageStorageKey = 'itapp.portal.preferences.v1.user.' + userId;
    var englishButton = document.getElementById('userRolesLanguageEnglish');
    var indonesianButton = document.getElementById('userRolesLanguageIndonesian');

    function getLanguage() {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            if (!raw) {
                return 'en';
            }

            var parsed = JSON.parse(raw);
            return parsed && parsed.language === 'id' ? 'id' : 'en';
        } catch (error) {
            return 'en';
        }
    }

    function saveLanguage(language) {
        try {
            var raw = window.localStorage.getItem(languageStorageKey);
            var parsed = raw ? JSON.parse(raw) : {};
            parsed.language = language === 'id' ? 'id' : 'en';
            window.localStorage.setItem(languageStorageKey, JSON.stringify(parsed));
        } catch (error) {
            // Keep silent if localStorage is unavailable.
        }
    }

    function getLabel(key, fallback) {
        var dictionary = translations[currentLanguage] || translations.en;
        return dictionary[key] || fallback || key;
    }

    function applyLanguage(language) {
        currentLanguage = language === 'id' ? 'id' : 'en';
        var dictionary = translations[currentLanguage] || translations.en;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function(node) {
            var key = node.getAttribute('data-i18n');
            if (dictionary[key]) {
                node.textContent = dictionary[key];
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-title]'), function(node) {
            var key = node.getAttribute('data-i18n-title');
            if (dictionary[key]) {
                node.setAttribute('title', dictionary[key]);
            }
        });

        if (englishButton && indonesianButton) {
            englishButton.classList.toggle('active', currentLanguage === 'en');
            indonesianButton.classList.toggle('active', currentLanguage === 'id');
        }

        if (typeof window.userRolesRefreshRuntimeText === 'function') {
            window.userRolesRefreshRuntimeText();
        }
    }

    window.userRolesLabel = getLabel;
    window.userRolesDataTableLanguage = function() {
        return {
            search: getLabel('users.roles.datatable.search', 'Quick Search:'),
            zeroRecords: getLabel('users.roles.datatable.zero_records', 'No matching roles found'),
            emptyTable: getLabel('users.roles.datatable.empty_table', 'No role data available')
        };
    };

    if (englishButton && indonesianButton) {
        englishButton.addEventListener('click', function() {
            saveLanguage('en');
            applyLanguage('en');
        });

        indonesianButton.addEventListener('click', function() {
            saveLanguage('id');
            applyLanguage('id');
        });
    }

    applyLanguage(getLanguage());
})();

function parseRoleJsonPayload(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (!payload) {
        return [];
    }

    if (typeof payload === 'string') {
        try {
            return JSON.parse(payload);
        } catch (error) {
            return [];
        }
    }

    return [];
}

function showRoleDetails(button) {
    var roleName = $(button).data('role-name');
    var roleDisplay = $(button).data('role-display');
    var roleLevel = $(button).data('role-level');
    var roleUsers = $(button).data('role-users');
    var rolePermissions = $(button).data('role-permissions');
    var usersPayload = $(button).data('role-users-json');
    var permissionsPayload = $(button).data('role-permissions-json');

    $('#detailRoleName').text(roleName || '-');
    $('#detailRoleDisplay').text(roleDisplay || roleName || '-');
    $('#detailRoleLevel').text('LV ' + (roleLevel || 1));
    $('#detailRoleUsers').text(roleUsers || 0);
    $('#detailRolePermissions').text(rolePermissions || 0);

    displayRoleUsers(usersPayload);
    displayRolePermissions(permissionsPayload);
}

function displayRoleUsers(usersPayload) {
    var usersArray = parseRoleJsonPayload(usersPayload);

    if (!usersArray.length) {
        $('#roleUsersList').html(
            '<p class="text-muted"><em>' + window.userRolesLabel('users.roles.runtime.modal.no_users', 'No users assigned to this role') + '</em></p>'
        );
        return;
    }

    try {
        var usersList = $('<ul class="list-unstyled"></ul>');

        usersArray.forEach(function(user) {
            var userItem = $('<li>')
                .css('padding', '8px 0')
                .css('border-bottom', '1px solid #eee')
                .html(
                    '<strong>' + (user.name || '-') + '</strong><br>' +
                    '<small class="text-muted">' + (user.email || '-') + '</small>'
                );
            usersList.append(userItem);
        });

        $('#roleUsersList').html(usersList);
    } catch (error) {
        $('#roleUsersList').html(
            '<p class="text-danger"><em>' + window.userRolesLabel('users.roles.runtime.modal.users_error', 'Error loading users. Please try again.') + '</em></p>'
        );
    }
}

function displayRolePermissions(permissionsPayload) {
    var permissionsArray = parseRoleJsonPayload(permissionsPayload);

    if (!permissionsArray.length) {
        $('#rolePermissionsList').html(
            '<p class="text-muted"><em>' + window.userRolesLabel('users.roles.runtime.modal.no_permissions', 'No permissions assigned to this role') + '</em></p>'
        );
        return;
    }

    try {
        var permissionsList = $('<div class="row"></div>');

        permissionsArray.forEach(function(permission) {
            var permissionItem = $('<div class="col-md-6 role-permission-item">')
                .html('<span class="label label-success">' + (permission.name || '-') + '</span>');
            permissionsList.append(permissionItem);
        });

        $('#rolePermissionsList').html(permissionsList);
    } catch (error) {
        $('#rolePermissionsList').html(
            '<p class="text-danger"><em>' + window.userRolesLabel('users.roles.runtime.modal.permissions_error', 'Error loading permissions. Please try again.') + '</em></p>'
        );
    }
}

$(document).ready(function() {
    if ($.fn.DataTable && $('#rolesTable').length) {
        userRolesTable = $('#rolesTable').DataTable({
            paging: false,
            ordering: true,
            info: false,
            searching: true,
            language: window.userRolesDataTableLanguage(),
            columnDefs: [
                { orderable: false, targets: 5 }
            ]
        });
    }

    window.userRolesRefreshRuntimeText = function() {
        if (!userRolesTable || !userRolesTable.settings || !userRolesTable.settings()[0]) {
            return;
        }

        userRolesTable.settings()[0].oLanguage = window.userRolesDataTableLanguage();
        userRolesTable.draw(false);
    };

    window.userRolesRefreshRuntimeText();
});
</script>

<style>
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.2px;
        color: #ffffff;
        text-transform: uppercase;
    }

    .role-badge-level {
        font-size: 10px;
        opacity: 0.9;
        font-weight: 800;
    }

    .role-badge-lv0 {
        background: linear-gradient(135deg, #9CA3AF, #6B7280);
    }

    .role-badge-lv1 {
        background: linear-gradient(135deg, #64748B, #475569);
    }

    .role-badge-lv2 {
        background: linear-gradient(135deg, #06B6D4, #0891B2);
        box-shadow: 0 0 8px rgba(6, 182, 212, 0.35);
    }

    .role-badge-lv3 {
        background: linear-gradient(135deg, #10B981, #047857);
        box-shadow: 0 0 8px rgba(16, 185, 129, 0.35);
    }

    .role-badge-lv8 {
        background: linear-gradient(135deg, #F59E0B, #D97706);
        box-shadow: inset 0 0 2px rgba(255,255,255,0.7);
    }

    .role-badge-lv9 {
        background: linear-gradient(135deg, #EF4444, #B91C1C);
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.45);
    }

    .role-badge-lv10 {
        background: linear-gradient(135deg, #6D28D9, #10B981);
        box-shadow: 0 0 10px rgba(109, 40, 217, 0.35), 0 0 12px rgba(16, 185, 129, 0.35);
        animation: role-badge-glitch 2.6s infinite;
    }

    @keyframes role-badge-glitch {
        0%, 85%, 100% {
            transform: translate(0, 0);
            filter: hue-rotate(0deg);
        }
        88% {
            transform: translate(-1px, 0);
            filter: hue-rotate(8deg);
        }
        91% {
            transform: translate(1px, 0);
            filter: hue-rotate(-6deg);
        }
    }

    .badge-primary {
        background-color: #0073b7;
        color: #ffffff;
        padding: 5px 10px;
    }

    .badge-success {
        background-color: #00a65a;
        color: #ffffff;
        padding: 5px 10px;
    }

    .box-widget {
        margin-bottom: 20px;
    }

    .box-footer {
        display: flex;
        gap: 5px;
    }

    .role-permission-item {
        padding: 5px 0;
    }

    @media (max-width: 767px) {
        .content-header .pull-right {
            float: none !important;
            margin-top: 10px !important;
        }

        .btn-group-sm .btn {
            padding: 4px 8px;
            font-size: 11px;
        }
    }
</style>
@endsection