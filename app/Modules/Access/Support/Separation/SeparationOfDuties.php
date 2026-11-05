<?php

namespace App\Modules\Access\Support\Separation;

use App\Models\User;
use App\Modules\Access\Contracts\SodRuleSource;
use App\Modules\Access\Models\ProcessAssignment;
use App\Modules\Access\Support\AccessSubject;
use App\Modules\Access\Support\AssignmentRoles;
use LogicException;

/**
 * The separation-of-duties rules of ACCESS_CONTROL section 6, checked when someone is assigned and again when someone acts
 * (layer 4 of section 1). The system controls SOD-04 to SOD-09 are written here as data; a module or a policy package adds
 * its own rules through a {@see SodRuleSource}, such as SOD-01 to SOD-03 of procurement.authority (TASK-028), and the same
 * three kinds of {@see SodRule} decide them without code for each rule (TAC-05). A broken rule is never waived by a warning.
 *
 * An action is named by its capability, or by the key a module gives an action that needs none, such as the confirmation of
 * a handover by its receiver; the module passes the people who already took part in the object as its participants.
 */
final class SeparationOfDuties
{
    /**
     * @var list<SodRuleSource>
     */
    private array $sources = [];

    public function addSource(SodRuleSource $source): void
    {
        $this->sources[] = $source;
    }

    /**
     * The system controls, then the rules of every source as they hold now.
     *
     * @return list<SodRule>
     */
    public function rules(): array
    {
        $rules = self::systemRules();

        foreach ($this->sources as $source) {
            foreach ($source->rules() as $rule) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Whether the user may take the role on the object: the roles they already hold there, the people who took part in it and
     * their role group.
     */
    public function forAssignment(User $user, string $role, AccessSubject $subject): SodResult
    {
        $held = array_values(array_diff($this->heldRoles($user, $subject, inForceOnly: false), [$role]));
        $checks = [];

        foreach ($this->rules() as $rule) {
            $passed = match ($rule->kind) {
                SodRule::EXCLUSIVE_ROLES => match (true) {
                    in_array($role, $rule->roles, true) && array_intersect($held, $rule->otherRoles) !== [] => false,
                    in_array($role, $rule->otherRoles, true) && array_intersect($held, $rule->roles) !== [] => false,
                    in_array($role, $rule->roles, true) || in_array($role, $rule->otherRoles, true) => true,
                    default => null,
                },
                SodRule::DISTINCT_PARTICIPANTS => in_array($role, $rule->roles, true)
                    ? ! in_array($user->id, $subject->participantIds($rule->participants), true)
                    : null,
                SodRule::EXCLUDED_ROLE_GROUPS => in_array($role, $rule->roles, true)
                    ? ! in_array($user->role_group, $rule->roleGroups, true)
                    : null,
                default => throw new LogicException("Unknown kind of separation rule: {$rule->kind}."),
            };

            if ($passed !== null) {
                $checks[] = ['rule' => $rule, 'passed' => $passed];
            }
        }

        return new SodResult($checks);
    }

    /**
     * Whether the actor may take the action on the subject now, with the roles they hold on it in force at this moment.
     */
    public function forAction(User $actor, string $action, AccessSubject $subject): SodResult
    {
        $held = $this->heldRoles($actor, $subject, inForceOnly: true);
        $giving = AssignmentRoles::givingCapability($action);
        $checks = [];

        foreach ($this->rules() as $rule) {
            $passed = match ($rule->kind) {
                // Both sides held at once, whichever was assigned first or before the rule came into force.
                SodRule::EXCLUSIVE_ROLES => self::holdsBothSides($held, $rule)
                    ? false
                    : (array_intersect($held, [...$rule->roles, ...$rule->otherRoles]) !== [] ? true : null),
                SodRule::DISTINCT_PARTICIPANTS => in_array($action, $rule->actions, true) || array_intersect($giving, $rule->roles) !== []
                    ? ! in_array($actor->id, $subject->participantIds($rule->participants), true)
                    : null,
                SodRule::EXCLUDED_ROLE_GROUPS => array_intersect($held, $rule->roles) !== []
                    ? ! in_array($actor->role_group, $rule->roleGroups, true)
                    : null,
                default => throw new LogicException("Unknown kind of separation rule: {$rule->kind}."),
            };

            if ($passed !== null) {
                $checks[] = ['rule' => $rule, 'passed' => $passed];
            }
        }

        return new SodResult($checks);
    }

    /**
     * SOD-04 to SOD-09, the controls of the system (ACCESS_CONTROL section 6). SOD-05 is also a constraint of access_grants,
     * and SOD-04 and SOD-09 become constraints of their tables with the modules that own them.
     *
     * @return list<SodRule>
     */
    public static function systemRules(): array
    {
        return [
            new SodRule('SOD-04', SodRule::DISTINCT_PARTICIPANTS, actions: ['admin.policy.activate'], participants: ['policy_author', 'policy_submitter']),
            new SodRule('SOD-05', SodRule::DISTINCT_PARTICIPANTS, actions: ['admin.access.approve'], participants: ['grant_granter', 'grant_holder']),
            new SodRule('SOD-06', SodRule::DISTINCT_PARTICIPANTS, roles: ['REQUEST_APPROVER'], actions: ['inventory.request.decide'], participants: ['requester']),
            new SodRule('SOD-07', SodRule::DISTINCT_PARTICIPANTS, actions: ['inventory.handover.confirm'], participants: ['handover_officer']),
            new SodRule('SOD-08', SodRule::EXCLUDED_ROLE_GROUPS, roles: AssignmentRoles::decisionRoles(), roleGroups: [User::SUPER_ADMIN]),
            new SodRule('SOD-09', SodRule::DISTINCT_PARTICIPANTS, actions: ['admin.templates.activate'], participants: ['template_author']),
        ];
    }

    /**
     * @param  list<string>  $held
     */
    private static function holdsBothSides(array $held, SodRule $rule): bool
    {
        foreach (array_intersect($held, $rule->roles) as $one) {
            foreach (array_intersect($held, $rule->otherRoles) as $other) {
                if ($one !== $other) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * The roles the user holds on the object: in force now, or ACTIVE at all, so that an assignment which starts later still
     * counts when someone else is assigned.
     *
     * @return list<string>
     */
    private function heldRoles(User $user, AccessSubject $subject, bool $inForceOnly): array
    {
        if (! $subject->hasObject()) {
            return [];
        }

        $query = ProcessAssignment::query()
            ->onObject((string) $subject->objectType, (int) $subject->objectId)
            ->where('user_id', $user->id);

        if ($inForceOnly) {
            $query->inForce();
        } else {
            $query->where('status', ProcessAssignment::ACTIVE);
        }

        return array_values(array_unique(array_map('strval', $query->pluck('assignment_role')->all())));
    }
}
