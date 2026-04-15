<?php

namespace App\Listeners;

use App\Services\AuditLogService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;

class AuditAuthEventListener
{
    /**
     * The audit log service instance.
     *
     * @var AuditLogService
     */
    protected $auditLogService;

    /**
     * Create the event listener.
     */
    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event)
    {
        $this->auditLogService->logAuthAction(
            'login',
            $event->user->id,
            "User '{$event->user->name}' logged in successfully"
        );
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event)
    {
        if ($event->user) {
            $this->auditLogService->logAuthAction(
                'logout',
                $event->user->id,
                "User '{$event->user->name}' logged out"
            );
        }
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailedLogin(Failed $event)
    {
        $email = $event->credentials['email'] ?? 'unknown';
        
        $this->auditLogService->logAuthAction(
            'failed_login',
            null,
            "Failed login attempt for email: {$email}"
        );
    }

    /**
     * Handle user registration events.
     */
    public function handleRegistered(Registered $event)
    {
        $this->auditLogService->logAuthAction(
            'register',
            $event->user->id,
            "New user '{$event->user->name}' registered"
        );
    }

    /**
     * Handle password reset events.
     */
    public function handlePasswordReset(PasswordReset $event)
    {
        $this->auditLogService->logAuthAction(
            'password_reset',
            $event->user->id,
            "User '{$event->user->name}' reset their password"
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailedLogin',
            Registered::class => 'handleRegistered',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
