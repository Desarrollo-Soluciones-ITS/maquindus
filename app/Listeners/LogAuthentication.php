<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogAuthentication
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login|Logout|Failed $event): void
    {
        if ($event instanceof Login) {
            $this->handleLogin($event);
        }

        if ($event instanceof Logout) {
            $this->handleLogout($event);
        }

        if ($event instanceof Failed) {
            $this->handleFailed($event);
        }
    }

    protected function handleLogin(Login $event): void
    {
        $userEmail = $event->user->email ?? 'Email no disponible';

        activity('Autenticación')
            ->event('authenticated')
            ->causedBy($event->user)
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log("{$userEmail} - Inició sesión desde la IP " . request()->ip());
    }

    protected function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $userEmail = $event->user->email ?? 'Email no disponible';

            activity('Autenticación')
                ->event('logged_out')
                ->causedBy($event->user)
                ->withProperties([
                    'ip_address' => request()->ip(),
                ])
                ->log("{$userEmail} - Cerró sesión.");
        }
    }

    protected function handleFailed(Failed $event): void
    {
        $attemptedEmail = $event->credentials['email'] ?? 'Desconocido';

        activity('Autenticación')
            ->event('login_failed')
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'attempted_email' => $attemptedEmail,
            ])
            ->log("{$attemptedEmail} - Inicio de sesión fallido desde la IP " . request()->ip());
    }
}
