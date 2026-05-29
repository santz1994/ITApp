<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    const CACHE_TTL = 3600;

    public static function getRoles()
    {
        return Cache::remember('roles_all', self::CACHE_TTL, function () {
            return \App\Role::query()->canonical()->orderBy('name')->get();
        });
    }

    public static function getRoleByName($roleName)
    {
        return Cache::remember("roles_name_{$roleName}", self::CACHE_TTL, function () use ($roleName) {
            return \App\Role::query()->canonical()->where('name', $roleName)->first();
        });
    }

    public static function getDivisions()
    {
        return Cache::remember('divisions_all', self::CACHE_TTL, function () {
            return \App\Division::orderBy('name')->get();
        });
    }

    public static function getUsers()
    {
        return Cache::remember('users_active', self::CACHE_TTL, function () {
            return User::where('is_active', 1)->orderBy('name')->get();
        });
    }

    public static function getAdmins()
    {
        return Cache::remember('admins_all', self::CACHE_TTL, function () {
            return User::role(['admin', 'super-admin'])->where('is_active', 1)->orderBy('name')->get();
        });
    }

    public static function clearStaticDataCache()
    {
        $keys = ['roles_all', 'divisions_all', 'users_active', 'admins_all'];
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public static function warmUpCaches()
    {
        self::getRoles();
        self::getDivisions();
        self::getUsers();
        self::getAdmins();

        return ['status' => 'success', 'message' => 'All caches warmed up', 'timestamp' => now()];
    }

    public static function clearAllSystemCache()
    {
        self::clearStaticDataCache();
        return ['status' => 'success', 'message' => 'All system caches cleared', 'timestamp' => now()];
    }
}
