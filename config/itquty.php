<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Canonical Role Names
    |--------------------------------------------------------------------------
    |
    | Only these roles are allowed to be created/assigned/shown in user and
    | role management surfaces. Additional legacy roles may exist in older
    | databases, but they are treated as non-canonical.
    |
    */
    'canonical_roles' => array_values(array_filter(array_map(
        static fn ($value) => strtolower(trim((string) $value)),
        explode(',', env('ITQUTY_CANONICAL_ROLES', 'guest,user,receptionist,human-resources,director,administrator,developer'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Legacy Role Aliases
    |--------------------------------------------------------------------------
    |
    | Map legacy role names used by old routes/middleware/tests into the
    | canonical Project.md role names.
    |
    */
    'role_aliases' => [
        'super-admin' => 'developer',
        'super_admin' => 'developer',
        'admin' => 'administrator',
        'management' => 'director',
        'human resources' => 'human-resources',
        'human_resources' => 'human-resources',
        'hr' => 'human-resources',
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Role Access Levels (LV)
    |--------------------------------------------------------------------------
    */
    'role_levels' => [
        'guest' => 0,
        'user' => 1,
        'receptionist' => 2,
        'human-resources' => 3,
        'director' => 8,
        'administrator' => 9,
        'developer' => 10,
    ],
];
