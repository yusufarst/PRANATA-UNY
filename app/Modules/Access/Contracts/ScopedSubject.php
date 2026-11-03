<?php

namespace App\Modules\Access\Contracts;

use App\Modules\Access\Support\AccessSubject;

/**
 * A model that a capability is checked on, such as a package or an asset: it says which unit, warehouse or policy family it
 * belongs to, so Gate::authorize('asset.view', $asset) checks the scope of the grant against it.
 */
interface ScopedSubject
{
    public function accessSubject(): AccessSubject;
}
