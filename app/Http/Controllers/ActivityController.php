<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Update user activity status
     */
    public function updateActivity(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $activity = $request->input('activity', 'active');
        $status = $request->input('status', 'online');

        // Simple activity update - store in session
        session(['user_activity' => [
            'activity' => $activity,
            'status' => $status,
            'timestamp' => now()
        ]]);

        return response()->json([
            'success' => true,
            'message' => 'Activity updated successfully',
            'data' => [
                'activity' => $activity,
                'status' => $status,
                'timestamp' => now()->toISOString(),
            ]
        ]);
    }

}