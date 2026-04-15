<?php

namespace PHPSTORM_META {

    // Tell PhpStorm about Spatie Laravel Permission methods on User model
    override(\App\User::class, map([
        'hasRole' => '@',
        'hasAnyRole' => '@', 
        'hasAllRoles' => '@',
        'getRoleNames' => '@',
        'assignRole' => '@',
        'removeRole' => '@',
        'syncRoles' => '@',
        'hasPermissionTo' => '@',
        'hasAnyPermission' => '@',
        'hasAllPermissions' => '@',
        'givePermissionTo' => '@',
        'revokePermissionTo' => '@',
        'syncPermissions' => '@',
    ]));

    // Define return types for common Laravel Permission methods
    expectedReturnValues(\App\User::class . '::hasRole', true, false);
    expectedReturnValues(\App\User::class . '::hasAnyRole', true, false);
    expectedReturnValues(\App\User::class . '::hasAllRoles', true, false);
    expectedReturnValues(\App\User::class . '::hasPermissionTo', true, false);
    expectedReturnValues(\App\User::class . '::hasAnyPermission', true, false);
    expectedReturnValues(\App\User::class . '::hasAllPermissions', true, false);

    // Auth::user() with role methods
    registerArgumentsSet('user_roles', 'admin', 'super-admin', 'user', 'management');
    expectedArguments(\App\User::class . '::hasRole', 0, argumentsSet('user_roles'));
    expectedArguments(\App\User::class . '::hasAnyRole', 0, argumentsSet('user_roles'));
    expectedArguments(\App\User::class . '::hasAllRoles', 0, argumentsSet('user_roles'));
    expectedArguments(\App\User::class . '::assignRole', 0, argumentsSet('user_roles'));
    expectedArguments(\App\User::class . '::removeRole', 0, argumentsSet('user_roles'));
    expectedArguments(\App\User::class . '::syncRoles', 0, argumentsSet('user_roles'));
}