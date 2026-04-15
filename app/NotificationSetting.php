<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NotificationSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'category',
        'description',
    ];

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("notification_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value by key
     */
    public static function set($key, $value, $category = 'general', $description = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'category' => $category,
                'description' => $description,
            ]
        );

        Cache::forget("notification_setting_{$key}");

        return $setting;
    }

    /**
     * Check if notification type is enabled
     */
    public static function isEnabled($key)
    {
        $value = self::get($key, 'false');
        return $value === 'true' || $value === '1' || $value === true;
    }

    /**
     * Get all settings by category
     */
    public static function getByCategory($category)
    {
        return self::where('category', $category)->get();
    }
}
