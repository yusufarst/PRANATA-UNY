<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Draft policies (POLICY_REGISTRY section 3)
    |--------------------------------------------------------------------------
    |
    | Whether an official action may run on a draft policy version, marked as a simulation, while no version is officially in
    | force on its reference date. Off by default in production; production never allows it, whatever this value says.
    |
    */

    'allow_draft' => (bool) env('PRANATA_ALLOW_DRAFT_POLICIES', env('APP_ENV', 'production') !== 'production'),

];
