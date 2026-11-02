<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Exceptions\StaleVersionException;
use LogicException;

/**
 * Optimistic concurrency of an aggregate that users edit at the same time (COMMAND_AND_API_CONTRACTS section 5). The table
 * holds lock_version integer NOT NULL DEFAULT 0 (DATA_MODEL section 1).
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasLockVersion
{
    /**
     * Write the changed attributes only while the row still has the version the user saw, and raise the version by one.
     * This is a single UPDATE ... WHERE id = ? AND lock_version = ?, so model events do not fire.
     *
     * @throws StaleVersionException when another command changed the row first; nothing is written
     */
    public function saveAtVersion(int $seenVersion): void
    {
        if (! $this->exists) {
            throw new LogicException('Only a stored row has a version to check.');
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $next = $seenVersion + 1;
        $changes = [...$this->getDirty(), 'lock_version' => $next];

        $updated = $this->newQueryWithoutScopes()
            ->whereKey($this->getKey())
            ->where('lock_version', $seenVersion)
            ->toBase()
            ->update($changes);

        if ($updated === 0) {
            throw StaleVersionException::of($this);
        }

        $this->setAttribute('lock_version', $next);
        $this->syncOriginal();
    }
}
