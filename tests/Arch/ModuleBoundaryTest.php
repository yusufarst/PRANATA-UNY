<?php

/*
 * The dependency rules of ARCHITECTURE sections 4 and 5. A module may use the modules it declares and what
 * those modules declare in turn. Reporting and Administration read from every core module.
 */
const MODULE_DEPENDENCIES = [
    'Platform' => [],
    'Identity' => ['Platform'],
    'Organization' => ['Platform'],
    'Policy' => ['Identity'],
    'Access' => ['Identity', 'Organization', 'Policy'],
    'Reference' => ['Organization'],
    'Work' => ['Access'],
    'Audit' => ['Identity'],
    'Workflow' => ['Access', 'Audit'],
    'Numbering' => ['Policy'],
    'Files' => ['Access'],
    'Documents' => ['Files', 'Numbering', 'Policy'],
    'Vendor' => ['Identity', 'Files', 'Workflow', 'Audit'],
    'Procurement' => ['Organization', 'Reference', 'Policy', 'Documents', 'Numbering', 'Work', 'Vendor', 'Workflow', 'Audit'],
    'Inventory' => ['Organization', 'Reference', 'Policy', 'Work', 'Workflow', 'Audit'],
    'Asset' => ['Organization', 'Reference', 'Policy', 'Workflow', 'Audit'],
    'Reconciliation' => ['Inventory', 'Asset', 'Procurement'],
    'Intake' => ['Files'],
    'Reporting' => ['Procurement', 'Vendor', 'Inventory', 'Asset', 'Reconciliation', 'Intake'],
    'Administration' => ['Procurement', 'Vendor', 'Inventory', 'Asset', 'Reconciliation', 'Intake', 'Reporting'],
];

// Handoffs through the receiving module's contract only (WF-016; Intake accepts into its target modules).
const CONTRACT_ONLY_DEPENDENCIES = [
    'Procurement' => ['Asset', 'Inventory'],
    'Intake' => ['Procurement', 'Vendor', 'Inventory', 'Asset', 'Reference', 'Organization'],
];

// Other modules are reached through their contracts, queries, data and events, never their internals.
const MODULE_INTERNALS = ['Actions', 'Models', 'Policies', 'Http', 'Jobs', 'Listeners', 'Rules', 'Providers'];

/**
 * @return list<string>
 */
function moduleReach(string $module): array
{
    $reach = [];
    $pending = MODULE_DEPENDENCIES[$module];

    while ($pending !== []) {
        $next = array_pop($pending);

        if (! in_array($next, $reach, true)) {
            $reach[] = $next;
            array_push($pending, ...MODULE_DEPENDENCIES[$next]);
        }
    }

    return $reach;
}

test('the declared module dependencies have no cycle', function () {
    foreach (array_keys(MODULE_DEPENDENCIES) as $module) {
        expect(moduleReach($module))->not->toContain($module);
    }
});

foreach (array_keys(MODULE_DEPENDENCIES) as $module) {
    $others = array_values(array_diff(array_keys(MODULE_DEPENDENCIES), [$module]));
    $contractOnly = CONTRACT_ONLY_DEPENDENCIES[$module] ?? [];
    $forbidden = array_values(array_diff($others, moduleReach($module)));

    arch("{$module} uses no module outside its declared dependencies")
        ->expect("App\\Modules\\{$module}")
        ->not->toUse(array_map(fn (string $other) => "App\\Modules\\{$other}", $forbidden))
        ->ignoring(array_map(fn (string $other) => "App\\Modules\\{$other}\\Contracts", $contractOnly));

    $internals = [];

    foreach ($others as $other) {
        foreach (MODULE_INTERNALS as $internal) {
            $internals[] = "App\\Modules\\{$other}\\{$internal}";
        }
    }

    arch("{$module} uses no internals of another module")
        ->expect("App\\Modules\\{$module}")
        ->not->toUse($internals);
}
