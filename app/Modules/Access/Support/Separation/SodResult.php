<?php

namespace App\Modules\Access\Support\Separation;

/**
 * The outcome of the separation-of-duties rules for one assignment or one action: every rule that applied, whether it held,
 * and the rules that were broken. An assignment keeps it as its sod_check (DATA_MODEL section 9).
 */
final readonly class SodResult
{
    /**
     * @param  list<array{rule: SodRule, passed: bool}>  $checks
     */
    public function __construct(public array $checks) {}

    public function passed(): bool
    {
        return $this->violations() === [];
    }

    /**
     * @return list<SodRule>
     */
    public function violations(): array
    {
        return array_values(array_map(
            fn (array $check): SodRule => $check['rule'],
            array_filter($this->checks, fn (array $check): bool => ! $check['passed']),
        ));
    }

    /**
     * Why the assignment or action was refused, one sentence per broken rule.
     */
    public function explanation(): string
    {
        return implode(' ', array_map(
            fn (SodRule $rule): string => "{$rule->id}: {$rule->explanation()}",
            $this->violations(),
        ));
    }

    /**
     * @return array{passed: bool, evaluated_at: string, rules: list<array{id: string, source: string, simulation: bool, passed: bool}>}
     */
    public function toArray(): array
    {
        return [
            'passed' => $this->passed(),
            'evaluated_at' => now()->toIso8601String(),
            'rules' => array_map(fn (array $check): array => [
                'id' => $check['rule']->id,
                'source' => $check['rule']->source,
                'simulation' => $check['rule']->simulation,
                'passed' => $check['passed'],
            ], $this->checks),
        ];
    }
}
