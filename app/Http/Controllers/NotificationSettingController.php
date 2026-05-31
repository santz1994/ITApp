<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NotificationSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_notification_settings']);
    }

    /**
     * Display notification settings
     */
    public function index(): View
    {
        $settings = NotificationSetting::orderBy('category')->orderBy('key')->get();
        
        // Group settings by category
        $groupedSettings = $settings->groupBy('category');
        
        return view('admin.notification-settings', compact('groupedSettings'));
    }

    /**
     * Update notification settings
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            // Get all existing settings
            $allSettings = NotificationSetting::pluck('key')->toArray();
            
            // Loop through all settings and update their values
            foreach ($allSettings as $key) {
                // Checkbox fields: if not present in request, set to false
                if (str_contains($key, '_enabled') || str_starts_with($key, 'email_') || str_starts_with($key, 'whatsapp_') || str_starts_with($key, 'telegram_')) {
                    $value = $request->has($key) ? '1' : '0';
                } else {
                    // Text fields: get from request or keep existing value
                    $value = $request->input($key);
                }
                
                if ($value !== null) {
                    $setting = NotificationSetting::where('key', $key)->first();
                    if ($setting) {
                        $setting->update(['value' => $value]);
                    }
                }
            }
            
            // Clear cache
            Cache::forget('notification_settings');
            
            return redirect()->route('notification-settings.index')
                           ->with('success', 'Notification settings updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
