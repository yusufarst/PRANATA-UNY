<?php

namespace App\Modules\Files\Actions;

use App\Models\User;
use App\Modules\Files\Events\FileUploaded;
use App\Modules\Files\Models\FileLink;
use App\Modules\Files\Models\FileObject;
use App\Modules\Files\Rules\PrivateFileRule;
use App\Modules\Files\Support\FileOwnerTypes;
use App\Modules\Files\Support\PrivateFileStorage;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Store an upload as a new file object and link it to one row of an owning module (IX-011, COMMAND_AND_API_CONTRACTS
 * section 7). The caller validated the upload for the purpose ({@see PrivateFileRule}) and may manage the files of the row.
 *
 * The bytes are written first, to a new random path that is never overwritten; what the database then records is the
 * size and SHA-256 of the bytes read back from that path, never a value of the client. If the transaction fails, the bytes
 * stay as orphans until a retention policy says otherwise (GAP-023). Different content is always a new file object.
 */
final class AttachEvidence extends Action
{
    public function __construct(
        private readonly FileOwnerTypes $owners,
        private readonly PrivateFileStorage $storage,
    ) {}

    public function handle(User $actor, string $ownerType, int $ownerId, string $purpose, UploadedFile $upload, ?string $label): FileLink
    {
        $owner = $this->owners->find($ownerType);

        if ($owner === null || $owner->describe($ownerId) === null) {
            throw DomainRuleViolation::because('files.owner_unknown');
        }

        if (! in_array($purpose, $owner->purposes(), true)) {
            throw DomainRuleViolation::because('files.purpose_not_allowed');
        }

        $source = $upload->getRealPath();

        if ($source === false) {
            throw DomainRuleViolation::because('files.validation.invalid_upload');
        }

        // The signature of the bytes, as the validation read it; the client's declaration is ignored.
        $mimeType = PrivateFileRule::detectMimeType($source) ?? 'application/octet-stream';
        $stored = $this->storage->writeNew($source);

        return $this->transaction(function () use ($actor, $owner, $ownerType, $ownerId, $purpose, $upload, $label, $mimeType, $stored): FileLink {
            // The links of one row change one at a time, so the owner's evidence column always follows the newest link.
            DB::select('select pg_advisory_xact_lock(hashtext(?))', ["file_links:{$ownerType}:{$ownerId}"]);

            $file = FileObject::query()->create([
                'disk' => $stored->disk,
                'path' => $stored->path,
                'original_name' => mb_substr($upload->getClientOriginalName(), 0, 255),
                'mime_type' => mb_substr($mimeType, 0, 150),
                'size_bytes' => $stored->sizeBytes,
                'sha256' => $stored->sha256,
                'classification' => $owner->classification(),
                'scan_status' => FileObject::NOT_SCANNED,
                'uploaded_by' => $actor->id,
                'uploaded_at' => now(),
            ]);

            $link = FileLink::query()->create([
                'file_id' => $file->id,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'purpose' => $purpose,
                'label' => $label,
                'linked_by' => $actor->id,
            ]);

            // linked_at comes from the database clock.
            $link->refresh();
            $owner->evidenceChanged($ownerId, $file->id, $actor);

            // Inside the transaction, so the file, its link and the audit record commit or roll back together (fail closed).
            FileUploaded::dispatch($file, $link, $actor);

            return $link;
        });
    }
}
