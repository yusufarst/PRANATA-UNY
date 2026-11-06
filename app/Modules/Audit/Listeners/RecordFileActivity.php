<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Audit\Support\AuditLogger;
use App\Modules\Files\Events\FileDownloaded;
use App\Modules\Files\Events\FileIntegrityFailed;
use App\Modules\Files\Events\FileUnlinked;
use App\Modules\Files\Events\FileUploaded;
use Illuminate\Events\Dispatcher;

/**
 * The audit trail of private files (SECURITY_MODEL sections 6 and 9, TASK-016): uploads and releases, every download of a
 * CONFIDENTIAL or RESTRICTED file, and every integrity incident. The file object is the subject.
 *
 * Synchronous, so a failed write fails the Action that caused the event; a download is recorded before its first byte.
 */
final class RecordFileActivity
{
    private const MODULE = 'files';

    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            FileUploaded::class => 'uploaded',
            FileUnlinked::class => 'unlinked',
            FileDownloaded::class => 'downloaded',
            FileIntegrityFailed::class => 'integrityFailed',
        ];
    }

    public function uploaded(FileUploaded $event): void
    {
        $this->audit->record('file.uploaded', self::MODULE, $event->file, metadata: [
            'link_id' => $event->link->id,
            'owner_type' => $event->link->owner_type,
            'owner_id' => $event->link->owner_id,
            'purpose' => $event->link->purpose,
            'label' => $event->link->label,
            'original_name' => $event->file->original_name,
            'mime_type' => $event->file->mime_type,
            'size_bytes' => $event->file->size_bytes,
            'sha256' => $event->file->sha256,
            'classification' => $event->file->classification,
        ], actor: $event->actor);
    }

    public function unlinked(FileUnlinked $event): void
    {
        $this->audit->record('file.unlinked', self::MODULE, $event->link->file, metadata: [
            'link_id' => $event->link->id,
            'owner_type' => $event->link->owner_type,
            'owner_id' => $event->link->owner_id,
            'purpose' => $event->link->purpose,
            'unlinked_at' => $event->link->unlinked_at?->toIso8601String(),
        ], actor: $event->actor);
    }

    public function downloaded(FileDownloaded $event): void
    {
        $this->audit->record('file.downloaded', self::MODULE, $event->file, metadata: [
            'classification' => $event->file->classification,
            'inline' => $event->inline,
            'original_name' => $event->file->original_name,
            'sha256' => $event->file->sha256,
        ], actor: $event->actor);
    }

    public function integrityFailed(FileIntegrityFailed $event): void
    {
        $this->audit->record('file.integrity_failed', self::MODULE, $event->file, metadata: [
            'reason' => $event->reason,
            'during' => $event->during,
            'expected' => ['size_bytes' => $event->file->size_bytes, 'sha256' => $event->file->sha256],
            'found' => $event->found,
            'disk' => $event->file->disk,
            // The subjects of the incident: the rows that reference the file (COMMAND_AND_API_CONTRACTS section 7).
            'references' => $event->references,
        ], actor: $event->actor);
    }
}
