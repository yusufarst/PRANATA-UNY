<?php

namespace App\Modules\Platform\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The correlation id of one request (COMMAND_AND_API_CONTRACTS section 3): sent back as X-Request-Id and written to the log
 * context, audit events and workflow transitions. The server makes it; an id sent by the client is never trusted.
 */
final class RequestId
{
    /**
     * The request attribute that carries the id.
     */
    public const ATTRIBUTE = 'request_id';

    public const HEADER = 'X-Request-Id';

    /**
     * The id of the request: one per request, made on first use.
     */
    public static function of(Request $request): string
    {
        $id = $request->attributes->get(self::ATTRIBUTE);

        if (! is_string($id) || ! Str::isUuid($id)) {
            $id = (string) Str::uuid();
            $request->attributes->set(self::ATTRIBUTE, $id);
        }

        return $id;
    }
}
