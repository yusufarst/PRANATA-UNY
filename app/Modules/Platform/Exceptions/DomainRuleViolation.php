<?php

namespace App\Modules\Platform\Exceptions;

use Illuminate\Validation\ValidationException;

/**
 * 422 for a business prerequisite the command does not meet (COMMAND_AND_API_CONTRACTS section 3): the message of the reason
 * goes to errors._domain, and the reason key and its context stay with the exception for the log.
 */
final class DomainRuleViolation extends ValidationException
{
    public string $reasonKey = '';

    /**
     * @var array<string, string|int>
     */
    public array $context = [];

    /**
     * @param  string  $reasonKey  a translation key, such as inventory.stock_insufficient
     * @param  array<string, string|int>  $context  the replacements of the message, such as the GAP or the missing evidence
     */
    public static function because(string $reasonKey, array $context = []): self
    {
        $text = __($reasonKey, $context);

        $exception = self::withMessages(['_domain' => [is_string($text) ? $text : $reasonKey]]);
        $exception->reasonKey = $reasonKey;
        $exception->context = $context;

        return $exception;
    }
}
