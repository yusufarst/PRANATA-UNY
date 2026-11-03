<?php

return [
    'cannot_grant_self' => 'You cannot give or approve access for yourself.',
    'account_deactivated' => 'This account is deactivated and cannot receive access.',
    'scope_not_allowed' => 'This scope cannot be used for the selected access. Choose another scope.',
    'role_group_not_allowed' => 'The role group of this user cannot hold the capability :capability.',
    'scope_unknown' => 'The unit, warehouse or policy family of this scope was not found.',
    'effective_from_in_past' => 'The start date cannot be earlier than today.',
    'effective_to_before_start' => 'The end date must be after the start date.',
    'profile_inactive' => 'This access profile is no longer active. Choose another profile.',
    'grant_not_pending' => 'This access no longer waits for approval. Reload the page to see the latest state.',
    'cannot_approve_own_grant' => 'Access that you gave must be approved by another administrator.',
    'grant_not_revocable' => 'This access is already revoked or expired.',
    'denied' => [
        'inactive_account' => 'The account is not active.',
        'unknown_capability' => 'This capability is unknown.',
        'role_group' => 'Your role group does not hold this capability.',
        'assignment_required' => 'This capability applies only through an assignment on its object.',
        'no_grant' => 'You do not have access for this capability yet.',
        'out_of_scope' => 'This data lies outside the scope of your access.',
    ],
];
