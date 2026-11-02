<?php

namespace App\Modules\Platform\Exceptions;

use App\Modules\Platform\Support\CommandMessage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * 503 for a command that still hit a deadlock or serialization failure after its retries (CONCURRENCY_AND_INTEGRITY section 6).
 * Every attempt was rolled back, so nothing of it was saved.
 */
final class CommandRetriesExhaustedException extends HttpException
{
    public const REASON = 'retry_exhausted';

    public function __construct(Throwable $previous)
    {
        parent::__construct(503, CommandMessage::of(self::REASON), $previous);
    }
}
