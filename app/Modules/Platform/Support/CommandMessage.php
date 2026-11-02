<?php

namespace App\Modules\Platform\Support;

/**
 * The text of a command message from lang/{id,en}/command.php, in the language of the request.
 */
final class CommandMessage
{
    /**
     * @param  array<string, string|int>  $replace
     */
    public static function of(string $reason, array $replace = []): string
    {
        $text = __("command.{$reason}", $replace);

        return is_string($text) ? $text : "command.{$reason}";
    }
}
