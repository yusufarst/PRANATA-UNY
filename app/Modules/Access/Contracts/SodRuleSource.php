<?php

namespace App\Modules\Access\Contracts;

use App\Modules\Access\Support\Separation\SodRule;

/**
 * Separation-of-duties rules that come from outside the system controls, such as the regime rules SOD-01 to SOD-03 of the policy
 * family procurement.authority (TASK-028). The source gives the rules that hold now, with their policy version as source and
 * whether that version is only a simulation.
 */
interface SodRuleSource
{
    /**
     * @return iterable<SodRule>
     */
    public function rules(): iterable;
}
