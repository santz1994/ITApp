<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Return notifications and unread count for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // If no user or notifications not supported, return an empty payload
        if (!$user) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0
            ], 200);
        }

        // If notifications are available via the Notifiable trait, use them
        $notifications = [];
        $unreadCount = 0;

        // Use relations if available (prevents errors when notifications table is absent)
        if (method_exists($user, 'notifications')) {
            try {
                $notificationsData = $user->notifications()->orderBy('created_at', 'desc')->take(50)->get();
                $notifications = $notificationsData->map(function ($n) {
                    // Convert notification to array with relevant fields (id, type, data, read_at, created_at)
                    return [
                        'id' => $n->id,
                        'type' => $n->type,
                        'data' => $n->data,
                        'read_at' => $n->read_at,
                        'created_at' => $n->created_at
                    ];
                })->values();

                $unreadCount = method_exists($user, 'unreadNotifications') ? $user->unreadNotifications()->count() : 0;
            } catch (\Exception $e) {
                // In case the notifications table doesn't exist or errors, fallback to empty
                $notifications = [];
                $unreadCount = 0;
            }
        }

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => (int)$unreadCount
        ], 200);
    }
}
