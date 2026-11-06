<?php

namespace App\Modules\Files\Actions;

use App\Models\User;
use App\Modules\Files\Events\FileUnlinked;
use App\Modules\Files\Models\FileLink;
use App\Modules\Files\Support\FileOwnerTypes;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;
use Illuminate\Support\Facades\DB;

/**
 * Release a link that is not sealed, once (DATA_MODEL section 12). The caller may manage the files of the owner row. The
 * file and the link stay as history; the owner's evidence column moves to its newest remaining active link.
 */
final class ReleaseEvidence extends Action
{
    public function __construct(private readonly FileOwnerTypes $owners) {}

    public function handle(User $actor, FileLink $link): void
    {
        $this->transaction(function () use ($actor, $link): void {
            DB::select('select pg_advisory_xact_lock(hashtext(?))', ["file_links:{$link->owner_type}:{$link->owner_id}"]);
            $link->refresh();
            $owner = $this->owners->find($link->owner_type);

            if ($owner === null) {
                throw DomainRuleViolation::because('files.owner_unknown');
            }

            if (! $link->isActive()) {
                throw DomainRuleViolation::because('files.link_not_active');
            }

            // The database refuses it as well; this gives the user a reason instead of a server error.
            if (DB::table('file_sealed_owner_types')->where('owner_type', $link->owner_type)->exists()) {
                throw DomainRuleViolation::because('files.link_sealed');
            }

            // unlinked_at comes from the database clock, whatever is written here.
            $link->forceFill(['unlinked_by' => $actor->id, 'unlinked_at' => now()])->save();
            $link->refresh();

            $newest = FileLink::query()
                ->ownedBy($link->owner_type, $link->owner_id)
                ->active()
                ->orderByDesc('linked_at')
                ->orderByDesc('id')
                ->value('file_id');

            $owner->evidenceChanged($link->owner_id, is_numeric($newest) ? (int) $newest : null, $actor);

            FileUnlinked::dispatch($link, $actor);
        });
    }
}
