<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthSecurityLogger
{
    private const LOG_NAME = 'auth';

    public function loginSucceeded(Authenticatable $user, Request $request): void
    {
        $this->write('login_succeeded', $request, [
            'user_id' => $user->getAuthIdentifier(),
        ], $user);
    }

    public function loginFailed(Request $request): void
    {
        $this->write('login_failed', $request);
    }

    public function passwordResetRequested(Request $request, bool $knownUser): void
    {
        $this->write('password_reset_requested', $request, [
            'known_user' => $knownUser,
        ]);
    }

    public function passwordResetFailed(Request $request): void
    {
        $this->write('password_reset_failed', $request);
    }

    public function passwordResetSucceeded(User $user): void
    {
        $this->write('password_reset_succeeded', request(), [
            'user_id' => $user->getKey(),
        ], $user);
    }

    public function passwordChanged(Authenticatable $user): void
    {
        $this->write('password_changed', request(), [
            'user_id' => $user->getAuthIdentifier(),
        ], $user);
    }

    /**
     * Persist a single authentication security event. Only salted hashes of
     * the submitted email are stored — never the raw email, token, or password.
     */
    private function write(string $event, Request $request, array $properties = [], ?Authenticatable $causer = null): void
    {
        try {
            $activity = activity(self::LOG_NAME)
                ->event($event)
                ->withProperties(array_merge([
                    'email_hash' => $this->emailHash($request->input('email')),
                    'ip' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 255),
                ], $properties));

            if ($causer) {
                $activity->causedBy($causer);
            }

            $activity->log($event);
        } catch (\Throwable $e) {
            // Authentication must never break because auditing failed.
            report($e);
        }
    }

    private function emailHash(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        return hash('sha256', Str::lower(trim($email)) . '|' . (string) config('app.key'));
    }
}
