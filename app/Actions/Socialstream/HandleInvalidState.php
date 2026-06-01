<?php

declare(strict_types=1);

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use Laravel\Socialite\Two\InvalidStateException;

class HandleInvalidState implements HandlesInvalidState
{
    public function handle(InvalidStateException $exception, ?callable $callback = null): void
    {
        if ($callback) {
            $callback($exception);

            return;
        }

        throw $exception;
    }
}
