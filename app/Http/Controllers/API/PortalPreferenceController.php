<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PortalPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PortalPreferenceController extends Controller
{
    protected PortalPreferenceService $preferenceService;

    public function __construct(PortalPreferenceService $preferenceService)
    {
        $this->middleware('auth:sanctum,web');
        $this->preferenceService = $preferenceService;
    }

    /**
     * Get the current user's portal preferences.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $preferences = $this->preferenceService->loadPreferences($user);

        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    /**
     * Save portal preferences for the current user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'language' => 'nullable|in:en,id',
            'theme' => 'nullable|in:light,dark',
            'moduleOrder' => 'nullable|array',
            'moduleOrder.*' => 'string',
            'hiddenModules' => 'nullable|array',
            'hiddenModules.*' => 'string',
            'quickLinkKeys' => 'nullable|array',
            'quickLinkKeys.*' => 'string',
        ]);

        $user = Auth::user();
        $preferences = $request->only([
            'language',
            'theme',
            'moduleOrder',
            'hiddenModules',
            'quickLinkKeys',
        ]);

        $success = $this->preferenceService->savePreferences($user, $preferences);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Portal preferences saved successfully.',
                'data' => $this->preferenceService->loadPreferences($user),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to save portal preferences.',
        ], 500);
    }

    /**
     * Update a specific preference for the current user.
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'value' => 'required',
        ]);

        $user = Auth::user();
        $success = $this->preferenceService->updatePreference($user, $key, $request->input('value'));

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Preference updated successfully.',
                'data' => $this->preferenceService->loadPreferences($user),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update preference.',
        ], 400);
    }

    /**
     * Reset portal preferences to defaults for the current user.
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();
        $success = $this->preferenceService->resetPreferences($user);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Portal preferences reset to defaults.',
                'data' => $this->preferenceService->loadPreferences($user),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reset preferences.',
        ], 500);
    }

    /**
     * Get a specific preference for the current user.
     */
    public function show(string $key): JsonResponse
    {
        $user = Auth::user();
        $value = $this->preferenceService->getPreference($user, $key);

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    }
}
