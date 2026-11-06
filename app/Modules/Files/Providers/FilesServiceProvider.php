<?php

namespace App\Modules\Files\Providers;

use App\Models\User;
use App\Modules\Files\Console\VerifySealedFilesCommand;
use App\Modules\Files\Models\FileObject;
use App\Modules\Files\Owners\AppointmentEvidenceOwner;
use App\Modules\Files\Owners\AssignmentEvidenceOwner;
use App\Modules\Files\Support\FileAccess;
use App\Modules\Files\Support\FileOwnerTypes;
use App\Modules\Files\Support\PrivateFileStorage;
use App\Modules\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Gate;

class FilesServiceProvider extends ModuleServiceProvider
{
    /**
     * Downloading or previewing a file: Gate::authorize(DOWNLOAD, $file). Decided by the owning modules of its references.
     */
    public const DOWNLOAD = 'files.download';

    /**
     * Opening the evidence page of a row: Gate::authorize(VIEW_EVIDENCE, [$ownerType, $ownerId]).
     */
    public const VIEW_EVIDENCE = 'files.evidence.view';

    /**
     * Uploading a file for a row and releasing one: Gate::authorize(MANAGE_EVIDENCE, [$ownerType, $ownerId]).
     */
    public const MANAGE_EVIDENCE = 'files.evidence.manage';

    public function register(): void
    {
        $this->app->singleton(FileOwnerTypes::class);
        $this->app->singleton(PrivateFileStorage::class, function (Application $app): PrivateFileStorage {
            $disk = config('files.disk');

            return new PrivateFileStorage($app->make(FilesystemManager::class), is_string($disk) && $disk !== '' ? $disk : 'local');
        });
    }

    public function boot(): void
    {
        parent::boot();

        Gate::define(self::DOWNLOAD, fn (User $user, FileObject $file): bool => $this->app->make(FileAccess::class)->allowsDownload($user, $file));

        Gate::define(self::VIEW_EVIDENCE, fn (User $user, string $type, int $id): bool => $this->app->make(FileOwnerTypes::class)
            ->find($type)?->allowsRead($user, $id) ?? false);

        Gate::define(self::MANAGE_EVIDENCE, fn (User $user, string $type, int $id): bool => $this->app->make(FileOwnerTypes::class)
            ->find($type)?->allowsManage($user, $id) ?? false);

        // The V1 owners of decree evidence (TASK-016): appointments and process assignments.
        $owners = $this->app->make(FileOwnerTypes::class);
        $owners->register($this->app->make(AppointmentEvidenceOwner::class));
        $owners->register($this->app->make(AssignmentEvidenceOwner::class));

        if ($this->app->runningInConsole()) {
            $this->commands([VerifySealedFilesCommand::class]);
        }

        // The daily re-verification of sealed files (CONCURRENCY_AND_INTEGRITY section 7 (e)).
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command(VerifySealedFilesCommand::class)->dailyAt('02:30')->withoutOverlapping();
        });
    }
}
