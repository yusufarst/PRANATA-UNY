<?php

namespace App\Modules\Files\Support;

use App\Models\User;
use App\Modules\Files\Models\FileLink;
use App\Modules\Files\Models\FileObject;
use Illuminate\Support\Facades\DB;

/**
 * The props of the evidence panel of one owner row (PG-012, EvidenceList.tsx and FileDropzone.tsx): the row and its parent,
 * the files linked to it with their history, what may be uploaded, and whether the actor may upload or release. The server
 * checks every command again.
 */
final class EvidencePanel
{
    public function __construct(private readonly FileOwnerTypes $owners) {}

    /**
     * @return array<string, mixed>|null null when the type is not registered or the row does not exist
     */
    public function for(User $actor, string $ownerType, int $ownerId): ?array
    {
        $owner = $this->owners->find($ownerType);
        $described = $owner?->describe($ownerId);

        if ($owner === null || $described === null) {
            return null;
        }

        $mayManage = $owner->allowsManage($actor, $ownerId);
        $sealed = DB::table('file_sealed_owner_types')->where('owner_type', $ownerType)->exists();
        $links = FileLink::query()
            ->ownedBy($ownerType, $ownerId)
            ->with('file')
            ->orderByRaw('(unlinked_at is null) desc')
            ->orderByDesc('linked_at')
            ->orderByDesc('id')
            ->get();
        $people = User::query()
            ->whereIn('id', $links->flatMap(fn (FileLink $link): array => array_filter([$link->file->uploaded_by, $link->unlinked_by]))->unique()->values()->all())
            ->pluck('name', 'id');

        return [
            'owner' => ['type' => $ownerType, 'id' => $ownerId, ...$described->toArray()],
            'files' => $links->map(fn (FileLink $link): array => [
                'link_id' => $link->id,
                'public_id' => $link->file->public_id,
                'original_name' => $link->file->original_name,
                'mime_type' => $link->file->mime_type,
                'size_bytes' => $link->file->size_bytes,
                'sha256' => $link->file->sha256,
                'classification' => $link->file->classification,
                'scan_status' => $link->file->scan_status,
                'purpose' => $link->purpose,
                'label' => $link->label,
                'uploaded_by' => $link->file->uploaded_by === null ? null : ($people[$link->file->uploaded_by] ?? null),
                'uploaded_at' => $link->file->uploaded_at->toIso8601String(),
                'active' => $link->isActive(),
                'unlinked_by' => $link->unlinked_by === null ? null : ($people[$link->unlinked_by] ?? null),
                'unlinked_at' => $link->unlinked_at?->toIso8601String(),
                'download_url' => route('files.download', $link->file->public_id, absolute: false),
                'preview_url' => $link->file->isPreviewable() ? route('files.download', ['file' => $link->file->public_id, 'inline' => 1], absolute: false) : null,
                // A link of a sealed owner has no release action at all (DATA_MODEL section 12).
                'can_release' => $mayManage && $link->isActive() && ! $sealed,
            ])->all(),
            'purposes' => array_map(fn (string $purpose): array => UploadProfile::for($purpose)->toArray(), $owner->purposes()),
            'can' => ['upload' => $mayManage && ! $sealed],
            'scan_statuses' => [FileObject::NOT_SCANNED, FileObject::CLEAN, FileObject::INFECTED, FileObject::ERROR],
        ];
    }
}
