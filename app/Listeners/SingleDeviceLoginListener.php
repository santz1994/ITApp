<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

class SingleDeviceLoginListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event - invalidate other sessions when user logs in
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $currentSessionId = Session::getId();
        // If sessions table doesn't exist (testing DB), skip invalidation
        try {
            if (! Schema::hasTable('sessions')) {
                return;
            }

            // Get all active sessions for this user (excluding current session)
            $otherSessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->get();
                
            // Delete other sessions from database
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->delete();
        } catch (\Exception $e) {
            // Non-fatal in test environments where DB schema may differ; log for triage
            logger()->warning('SingleDeviceLoginListener: could not invalidate sessions', ['error' => $e->getMessage()]);
            return;
        }
            
        // Log the action for security audit
        logger()->info('Single device login: invalidated other sessions', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'current_session' => $currentSessionId,
            'invalidated_sessions' => $otherSessions->count(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
