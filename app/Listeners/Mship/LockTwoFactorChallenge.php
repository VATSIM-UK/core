<?php

namespace App\Listeners\Mship;

use App\Notifications\Mship\TwoFactorChallengeLocked;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Events\TwoFactorAuthenticationEvent;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;

class LockTwoFactorChallenge
{
    public const LOCKOUT_MINUTES = 15;

    private const MAX_FAILURES = 5;

    public static function lockKey(int|string $key): string
    {
        return 'two-factor-lock:'.$key;
    }

    public static function failuresKey(int|string $key): string
    {
        return 'two-factor-failures:'.$key;
    }

    /**
     * Count consecutive failed challenge attempts and lock the challenge once too
     * many fail in a row. A valid code clears the count.
     */
    public function handle(TwoFactorAuthenticationEvent $event): void
    {
        $failuresKey = self::failuresKey($event->user->id);

        if ($event instanceof ValidTwoFactorAuthenticationCodeProvided) {
            RateLimiter::clear($failuresKey);

            return;
        }

        RateLimiter::hit($failuresKey, self::LOCKOUT_MINUTES * 60);

        if (RateLimiter::attempts($failuresKey) < self::MAX_FAILURES) {
            return;
        }

        RateLimiter::clear($failuresKey);
        RateLimiter::hit(self::lockKey($event->user->id), self::LOCKOUT_MINUTES * 60);

        $event->user->notify(new TwoFactorChallengeLocked);
    }
}
