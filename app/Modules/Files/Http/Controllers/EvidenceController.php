<?php

namespace App\Modules\Files\Http\Controllers;

use App\Modules\Files\Actions\AttachEvidence;
use App\Modules\Files\Actions\ReleaseEvidence;
use App\Modules\Files\Http\Requests\EvidenceUploadForm;
use App\Modules\Files\Models\FileLink;
use App\Modules\Files\Providers\FilesServiceProvider;
use App\Modules\Files\Support\EvidencePanel;
use App\Modules\Files\Support\FileOwnerTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PG-012 Bukti objek (INFORMATION_ARCHITECTURE section 3, RT-031, IX-011): the files linked to one row of an owning module,
 * named by its table and id, with "Unggah bukti" as the single primary action and the return to the exact parent. Who may
 * open, upload and release is decided by the owning module through its registered owner type; a kind of row no module
 * registered is not found.
 */
class EvidenceController
{
    public function __construct(private readonly FileOwnerTypes $owners) {}

    public function show(Request $request, string $type, int $id, EvidencePanel $panel): Response
    {
        abort_if($this->owners->find($type) === null, 404);
        Gate::authorize(FilesServiceProvider::VIEW_EVIDENCE, [$type, $id]);

        $props = $panel->for($request->user(), $type, $id);

        abort_if($props === null, 404);

        return Inertia::render('files/evidence', ['panel' => $props]);
    }

    public function store(EvidenceUploadForm $request, string $type, int $id, AttachEvidence $attach): RedirectResponse
    {
        Gate::authorize(FilesServiceProvider::MANAGE_EVIDENCE, [$type, $id]);

        $upload = $request->file('file');

        abort_unless($upload instanceof UploadedFile, 422);

        $attach->handle(
            $request->user(),
            $type,
            $id,
            $request->string('purpose')->value(),
            $upload,
            $request->filled('label') ? $request->string('label')->trim()->value() : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('files.uploaded')]);

        return back();
    }

    public function release(Request $request, string $type, int $id, FileLink $link, ReleaseEvidence $release): RedirectResponse
    {
        abort_if($this->owners->find($type) === null, 404);
        Gate::authorize(FilesServiceProvider::MANAGE_EVIDENCE, [$type, $id]);

        // A link of another row is not this page's to release.
        abort_if($link->owner_type !== $type || $link->owner_id !== $id, 404);

        $release->handle($request->user(), $link);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('files.released')]);

        return back();
    }
}
