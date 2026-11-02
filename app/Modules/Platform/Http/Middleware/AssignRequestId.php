<?php

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Support\RequestId;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives every request its correlation id first, so the log lines, audit events and the response of the request all carry it.
 */
class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = RequestId::of($request);

        Log::shareContext(['request_id' => $id]);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set(RequestId::HEADER, $id);

        return $response;
    }
}
