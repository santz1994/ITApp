<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortalPreferenceService
{
    private const DEFAULT_PREFERENCES = [
        'language' => 'en',
        'moduleOrder' => [],
        'hiddenModules' => [],
        'quickLinkKeys' => [],
    ];

    /**
     * Load portal preferences for a user.
     * Returns default preferences if none exist.
     */
    public function loadPreferences(User $user): array
    {
        $preferences = $user->portal_preferences;

        if (!$preferences || !is_array($preferences)) {
            return $this->getDefaultPreferences();
        }

        // Ensure all required keys exist
        return array_merge($this->getDefaultPreferences(), $preferences);
    }

    /**
     * Save portal preferences for a user.
     * Returns true on success, false on failure.
     */
    public function savePreferences(User $user, array $preferences): bool
    {
        try {
            DB::transaction(function () use ($user, $preferences) {
                // Validate and merge preferences
                $validated = $this->validateAndMergePreferences($preferences);
                
                // Update user preferences
                $user->update(['portal_preferences' => $validated]);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save portal preferences', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'preferences' => $preferences,
            ]);

            return false;
        }
    }

    /**
     * Reset portal preferences to default for a user.
     */
    public function resetPreferences(User $user): bool
    {
        return $this->savePreferences($user, $this->getDefaultPreferences());
    }

    /**
     * Merge user-provided preferences with defaults and validate structure.
     */
    private function validateAndMergePreferences(array $preferences): array
    {
        $defaults = $this->getDefaultPreferences();
        $merged = array_merge($defaults, $preferences);

        // Validate language
        $merged['language'] = in_array($merged['language'], ['en', 'id'], true) 
            ? $merged['language'] 
            : 'en';

        // Ensure arrays are properly typed
        $merged['moduleOrder'] = is_array($merged['moduleOrder']) ? $merged['moduleOrder'] : [];
        $merged['hiddenModules'] = is_array($merged['hiddenModules']) ? $merged['hiddenModules'] : [];
        $merged['quickLinkKeys'] = is_array($merged['quickLinkKeys']) ? $merged['quickLinkKeys'] : [];

        // Remove any null/empty values from arrays
        $merged['moduleOrder'] = array_values(array_filter($merged['moduleOrder'], 'is_string'));
        $merged['hiddenModules'] = array_values(array_filter($merged['hiddenModules'], 'is_string'));
        $merged['quickLinkKeys'] = array_values(array_filter($merged['quickLinkKeys'], 'is_string'));

        // Ensure unique values
        $merged['moduleOrder'] = array_values(array_unique($merged['moduleOrder']));
        $merged['hiddenModules'] = array_values(array_unique($merged['hiddenModules']));
        $merged['quickLinkKeys'] = array_values(array_unique($merged['quickLinkKeys']));

        return $merged;
    }

    /**
     * Get default preferences structure.
     */
    private function getDefaultPreferences(): array
    {
        return self::DEFAULT_PREFERENCES;
    }

    /**
     * Update a specific preference key for a user.
     * Example: updatePreference($user, 'language', 'id')
     */
    public function updatePreference(User $user, string $key, $value): bool
    {
        $current = $this->loadPreferences($user);
        
        if (!array_key_exists($key, $current)) {
            return false;
        }

        $current[$key] = $value;
        
        return $this->savePreferences($user, $current);
    }

    /**
     * Get a specific preference for a user.
     * Returns default value if key doesn't exist.
     */
    public function getPreference(User $user, string $key, $default = null)
    {
        $preferences = $this->loadPreferences($user);
        
        return $preferences[$key] ?? $default;
    }
}