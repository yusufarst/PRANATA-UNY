<?php

namespace App\Modules\Platform\Exceptions;

use App\Modules\Platform\Support\CommandMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

/**
 * 409 for a command that cannot run now (COMMAND_AND_API_CONTRACTS section 4, CONCURRENCY_AND_INTEGRITY section 6): the same
 * idempotency key is still in progress, or a row lock of another user did not come free in time. Nothing was changed.
 */
final class CommandBusyException extends RuntimeException
{
    public const IN_PROGRESS = 'in_progress';

    public const LOCKED = 'busy';

    private function __construct(public readonly string $reason, ?Throwable $previous = null)
    {
        parent::__construct("The command is refused for now: {$reason}.", 409, $previous);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function locked(Throwable $previous): self
    {
        return new self(self::LOCKED, $previous);
    }

    /**
     * An Inertia visit needs a page to show, so it goes back to the form with the message; other clients get the 409.
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        $message = CommandMessage::of($this->reason);
        $errors = [$this->reason === self::IN_PROGRESS ? '_idempotency' : '_domain' => [$message]];

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            return response()->json(['message' => $message, 'errors' => $errors], 409);
        }

        return back(303)->withErrors($errors);
    }
}
