<?php

namespace App\Modules\Platform\Exceptions;

use App\Modules\Platform\Support\CommandMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * 409 for a command sent with a lock_version that is no longer current (COMMAND_AND_API_CONTRACTS sections 3 and 5). The
 * command changed nothing; the user sees the latest data and the conflict dialog (IX-002).
 */
final class StaleVersionException extends RuntimeException
{
    private ?string $component = null;

    /**
     * @var array<string, mixed>
     */
    private array $props = [];

    public function __construct(public readonly string $table, public readonly int|string $id)
    {
        parent::__construct("{$table} {$id} was changed by another command.", 409);
    }

    public static function of(Model $model): self
    {
        $key = $model->getKey();

        return new self($model->getTable(), is_int($key) || is_string($key) ? $key : '?');
    }

    /**
     * Answer with this page and its latest props; without one, the visit goes back to the page it came from, which loads them.
     *
     * @param  array<string, mixed>  $props
     */
    public function showing(string $component, array $props = []): self
    {
        $this->component = $component;
        $this->props = $props;

        return $this;
    }

    public function render(Request $request): Response
    {
        $message = CommandMessage::of('conflict');

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            return response()->json(['message' => $message, 'errors' => ['lock_version' => [$message]]], 409);
        }

        if ($this->component === null) {
            return back(303)->withErrors(['lock_version' => $message]);
        }

        return Inertia::render($this->component, [...$this->props, 'errors' => ['lock_version' => $message]])
            ->toResponse($request)
            ->setStatusCode(409);
    }
}
