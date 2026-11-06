<?php

namespace App\Modules\Files\Console;

use App\Modules\Files\Events\FileIntegrityFailed;
use App\Modules\Files\Exceptions\FileIntegrityException;
use App\Modules\Files\Models\FileObject;
use App\Modules\Files\Support\FileIntegrity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The daily re-verification of CONCURRENCY_AND_INTEGRITY section 7 (e): every file linked to a row of a sealed owner type has
 * its stored bytes hashed again and compared with its recorded size and SHA-256. A difference is recorded as an integrity
 * incident (audit event file.integrity_failed and an operations alert) and never repaired, replaced or released here.
 */
class VerifySealedFilesCommand extends Command
{
    protected $signature = 'files:verify';

    protected $description = 'Verify the stored bytes of every file linked to a sealed owner against its recorded identity';

    public function handle(FileIntegrity $integrity): int
    {
        $failures = 0;
        $checked = 0;

        $files = FileObject::query()
            ->whereExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('file_links')
                ->join('file_sealed_owner_types', 'file_sealed_owner_types.owner_type', '=', 'file_links.owner_type')
                ->whereColumn('file_links.file_id', 'file_objects.id')
                ->whereNull('file_links.unlinked_at'))
            ->orderBy('id');

        foreach ($files->lazyById(200) as $file) {
            $checked++;

            try {
                $integrity->check($file);
            } catch (FileIntegrityException $exception) {
                $failures++;

                // Each incident commits on its own, so a later failure of the run never loses the ones recorded before.
                DB::transaction(fn () => FileIntegrityFailed::dispatch($file, $exception->reason, $exception->found, 'verify', null));
                Log::critical('file.integrity_failed', ['file' => $file->public_id, 'reason' => $exception->reason, 'during' => 'verify']);
                $this->error("{$file->public_id}: {$exception->reason}");
            }
        }

        $this->info("files:verify checked {$checked} sealed file(s), {$failures} integrity incident(s).");

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
