<?php

use App\Models\User;
use App\Modules\Access\Actions\AssignProcessRole;
use App\Modules\Access\Contracts\AssignmentSubjectType;
use App\Modules\Access\Support\AccessSubject;
use App\Modules\Access\Support\AssignmentRoles;
use App\Modules\Access\Support\AssignmentSubjects;
use App\Modules\Files\Contracts\FileOwnerType;
use App\Modules\Files\Data\OwnerDescription;
use App\Modules\Files\Exceptions\FileAlreadyExistsException;
use App\Modules\Files\Exceptions\FileIntegrityException;
use App\Modules\Files\Models\FileLink;
use App\Modules\Files\Models\FileObject;
use App\Modules\Files\Support\FileIntegrity;
use App\Modules\Files\Support\FileOwnerTypes;
use App\Modules\Files\Support\PrivateFileStorage;
use App\Modules\Organization\Models\OrgAppointment;
use App\Modules\Organization\Models\OrgPosition;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Support\DatabaseRoles;
use Database\Seeders\AccessProfileSeeder;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Http\Testing\File as TestingFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\DatabaseSession;
use Tests\TestCase;

/*
 * Private files (TASK-016, ADR-0009): the evidence page PG-012 of an appointment and of a process assignment, the upload
 * that stores bytes once at a random private path with the identity the server computed, the validation of kind, name,
 * size and signature in both languages, the authorized and verified download with its audit, the fail-closed integrity
 * incident, the daily re-verification of sealed files and the release of a link (TAC-01 to TAC-04, TAC-07).
 */

const PDF_BYTES = "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n";

const PNG_BYTES_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

beforeEach(function () {
    $this->seed([OrganizationSeeder::class, AccessProfileSeeder::class]);
    Storage::fake((string) config('files.disk'));

    $this->admin = User::factory()->inRoleGroup(User::SUPER_ADMIN)->withTwoFactor()->create(['name' => 'Admin Organisasi']);
    $this->kaprodi = User::factory()->withTwoFactor()->create(['name' => 'Kaprodi TI', 'username' => 'kaprodi.ti']);
    $this->appointment = OrgAppointment::query()->create([
        'user_id' => $this->kaprodi->id,
        'position_id' => OrgPosition::query()->where('code', 'KAPRODI_TI')->value('id'),
        'org_unit_id' => OrgUnit::query()->where('code', 'FT.TI')->value('id'),
        'effective_from' => BusinessDate::today()->toDateString(),
        'decree_ref' => 'SK/REK/2026/045',
        'status' => OrgAppointment::ACTIVE,
    ]);
});

function pdfUpload(string $name = 'sk.pdf', string $bytes = PDF_BYTES): TestingFile
{
    return UploadedFile::fake()->createWithContent($name, $bytes);
}

function pngUpload(string $name = 'sk.png'): TestingFile
{
    return UploadedFile::fake()->createWithContent($name, base64_decode(PNG_BYTES_BASE64, true) ?: '');
}

/**
 * @param  array<string, mixed>  $data
 */
function uploadEvidence(TestCase $test, string $type, int $id, array $data): TestResponse
{
    return $test->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])
        ->post(route('files.evidence.store', ['type' => $type, 'id' => $id]), ['purpose' => 'DECREE', ...$data]);
}

function releaseEvidence(TestCase $test, FileLink $link): TestResponse
{
    return $test->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])
        ->post(route('files.evidence.release', ['type' => $link->owner_type, 'id' => $link->owner_id, 'link' => $link->id]));
}

function evidenceUrl(string $type, int $id): string
{
    return route('files.evidence.show', ['type' => $type, 'id' => $id]);
}

/**
 * The bytes a download answered with; a verified copy is a file on disk until it is sent.
 */
function downloadedBytes(TestResponse $response): string
{
    $file = $response->baseResponse->getFile();

    return (string) file_get_contents($file->getPathname());
}

describe('the evidence page of an appointment (RT-031, AUTHZ-44)', function () {
    test('opens for the organization administrator with the upload, and for the appointed person to read only', function () {
        $this->actingAs($this->admin)
            ->get(evidenceUrl('org_appointments', $this->appointment->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('files/evidence')
                ->where('panel.owner.type', 'org_appointments')
                ->where('panel.owner.id', $this->appointment->id)
                ->where('panel.owner.title', 'Koordinator Program Studi Teknik Informatika · Kaprodi TI')
                ->where('panel.owner.subtitle', 'SK SK/REK/2026/045')
                ->where('panel.owner.parent_url', fn (string $url) => str_contains($url, '/admin/organization?unit='))
                ->where('panel.can.upload', true)
                ->where('panel.files', [])
                ->where('panel.purposes.0.purpose', 'DECREE')
                ->where('panel.purposes.0.extensions', ['pdf', 'jpg', 'jpeg', 'png']));

        // The appointed person may not open PG-505, so the way back is the dashboard (no dead link).
        $this->actingAs($this->kaprodi)
            ->get(evidenceUrl('org_appointments', $this->appointment->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('panel.can.upload', false)
                ->where('panel.owner.parent_url', route('dashboard', absolute: false)));
    });

    test('is refused to anyone else, and an unknown row or kind is not found', function () {
        $this->get(evidenceUrl('org_appointments', $this->appointment->id))->assertRedirect(route('login'));

        foreach ([User::factory()->withTwoFactor()->create(), User::factory()->inRoleGroup(User::VENDOR)->create()] as $user) {
            $this->actingAs($user)->get(evidenceUrl('org_appointments', $this->appointment->id))->assertForbidden();
            uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload()])->assertForbidden();
        }

        $this->actingAs($this->admin);
        $this->get(evidenceUrl('org_appointments', 999999))->assertNotFound();
        $this->get(evidenceUrl('no_such_table', 1))->assertNotFound();
        // A command on a row that does not exist fails closed: nobody may manage it.
        uploadEvidence($this, 'org_appointments', 999999, ['file' => pdfUpload()])->assertForbidden();
        uploadEvidence($this, 'no_such_table', 1, ['file' => pdfUpload()])->assertNotFound();

        expect(FileObject::query()->count())->toBe(0);
    });

    test('every file route needs sign-in and the TOTP of a privileged account, and the commands are idempotent', function () {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'files.'))
            ->keyBy(fn ($route) => $route->getName());

        expect($routes->keys()->all())->toEqualCanonicalizing([
            'files.download',
            'files.evidence.show',
            'files.evidence.store',
            'files.evidence.release',
        ]);

        foreach ($routes as $name => $route) {
            expect($route->gatherMiddleware())->toContain('auth', 'two-factor');

            if (in_array($name, ['files.evidence.store', 'files.evidence.release'], true)) {
                expect($route->gatherMiddleware())->toContain('idempotent');
            }
        }

        // COMMAND_AND_API_CONTRACTS section 9: uploads and downloads are rate limited per user.
        expect($routes['files.evidence.store']->gatherMiddleware())->toContain('throttle:files-upload')
            ->and($routes['files.download']->gatherMiddleware())->toContain('throttle:files-download');
    });
});

describe('uploading (TAC-01, TAC-04, TAC-07)', function () {
    test('stores the bytes once at a random private path, records the identity the server computed, links the file and points the appointment at it', function () {
        $this->actingAs($this->admin);

        uploadEvidence($this, 'org_appointments', $this->appointment->id, [
            'file' => pdfUpload(),
            'label' => 'SK pengangkatan',
            // Whatever the client claims about the bytes is not read.
            'sha256' => str_repeat('f', 64),
            'size_bytes' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $file = FileObject::query()->sole();
        $link = FileLink::query()->sole();
        $disk = Storage::disk((string) config('files.disk'));

        expect($file->sha256)->toBe(hash('sha256', PDF_BYTES))
            ->and($file->size_bytes)->toBe(strlen(PDF_BYTES))
            ->and($file->mime_type)->toBe('application/pdf')
            ->and($file->original_name)->toBe('sk.pdf')
            ->and($file->classification)->toBe(FileObject::INTERNAL)
            ->and($file->scan_status)->toBe(FileObject::NOT_SCANNED)
            ->and($file->uploaded_by)->toBe($this->admin->id)
            ->and($file->disk)->toBe((string) config('files.disk'))
            ->and($file->path)->toMatch('#^\d{4}/\d{2}/[0-9a-f]{32}$#')
            ->and($disk->get($file->path))->toBe(PDF_BYTES)
            ->and($link->file_id)->toBe($file->id)
            ->and($link->owner_type)->toBe('org_appointments')
            ->and($link->owner_id)->toBe($this->appointment->id)
            ->and($link->purpose)->toBe('DECREE')
            ->and($link->label)->toBe('SK pengangkatan')
            ->and($link->linked_by)->toBe($this->admin->id)
            ->and($link->isActive())->toBeTrue()
            ->and($this->appointment->refresh()->evidence_file_id)->toBe($file->id);

        $audit = DB::table('audit_events')->where('action', 'file.uploaded')->sole();

        expect($audit->actor_user_id)->toBe($this->admin->id)
            ->and($audit->subject_type)->toBe('file_objects')
            ->and($audit->subject_public_id)->toBe($file->public_id)
            ->and(json_decode((string) $audit->metadata, true)['sha256'])->toBe($file->sha256);

        // The page lists it with its download and preview, and the unit page says the appointment has its decree.
        $this->get(evidenceUrl('org_appointments', $this->appointment->id))
            ->assertInertia(fn (Assert $page) => $page->has('panel.files', 1)
                ->where('panel.files.0.original_name', 'sk.pdf')
                ->where('panel.files.0.download_url', route('files.download', $file->public_id, absolute: false))
                ->where('panel.files.0.preview_url', route('files.download', ['file' => $file->public_id, 'inline' => 1], absolute: false))
                ->where('panel.files.0.can_release', true)
                ->where('panel.files.0.uploaded_by', 'Admin Organisasi'));

        $this->get(route('organization.admin.index', ['unit' => OrgUnit::query()->where('code', 'FT.TI')->value('public_id')]))
            ->assertInertia(fn (Assert $page) => $page->where('selected.appointments.0.has_evidence', true));
    });

    test('the same bytes uploaded again become a new file at a new path, and the storage never overwrites a path', function () {
        $this->actingAs($this->admin);
        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload('sk-1.pdf')])->assertSessionHasNoErrors();
        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload('sk-2.pdf')])->assertSessionHasNoErrors();

        $files = FileObject::query()->orderBy('id')->get();

        expect($files)->toHaveCount(2)
            ->and($files[0]->sha256)->toBe($files[1]->sha256)
            ->and($files[0]->path)->not->toBe($files[1]->path)
            // The newest link decides the evidence column.
            ->and($this->appointment->refresh()->evidence_file_id)->toBe($files[1]->id);

        $storage = app(PrivateFileStorage::class);
        $stream = fopen('php://memory', 'r+b');
        fwrite($stream, 'ditimpa');
        rewind($stream);

        expect(fn () => $storage->writeAt($files[0]->path, $stream))->toThrow(FileAlreadyExistsException::class)
            ->and(Storage::disk((string) config('files.disk'))->get($files[0]->path))->toBe(PDF_BYTES);
    });

    test('refuses a kind outside the allowlist, a second extension, an empty file, a file over the limit and bytes that do not match the extension, in both languages (SEC-10, SEC-13)', function () {
        $this->actingAs($this->admin);
        $refusals = [
            [UploadedFile::fake()->createWithContent('sk.exe', 'MZ'), 'Jenis berkas tidak diizinkan. Jenis yang diterima: pdf, jpg, jpeg, png.'],
            [UploadedFile::fake()->createWithContent('sk', PDF_BYTES), 'Jenis berkas tidak diizinkan. Jenis yang diterima: pdf, jpg, jpeg, png.'],
            [pdfUpload('sk.php.pdf'), 'Nama berkas memuat lebih dari satu ekstensi. Ganti nama berkas sehingga hanya ada satu ekstensi di akhir.'],
            [pdfUpload('sk.pdf', ''), 'Berkas kosong tidak dapat diunggah.'],
            // Real bytes: the size is read from the file on disk, not from what the client reports.
            [pdfUpload('besar.pdf', str_repeat('x', 10 * 1024 * 1024 + 1)), 'Ukuran berkas melebihi batas 10 MB.'],
            [pngUpload('sk.pdf'), 'Isi berkas tidak sesuai dengan ekstensi .pdf.'],
        ];

        foreach ($refusals as [$upload, $message]) {
            uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => $upload])->assertSessionHasErrors(['file' => $message]);
        }

        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload(), 'purpose' => 'OTHER'])->assertSessionHasErrors('purpose');

        $this->withSession(['locale' => 'en']);
        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pngUpload('sk.pdf')])
            ->assertSessionHasErrors(['file' => 'The content of the file does not match the extension .pdf.']);

        expect(FileObject::query()->count())->toBe(0)
            ->and(Storage::disk((string) config('files.disk'))->allFiles())->toBe([])
            ->and($this->appointment->refresh()->evidence_file_id)->toBeNull();

        // A number in the name is not a second extension.
        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload('SK No.12.pdf')])->assertSessionHasNoErrors();

        expect(FileObject::query()->sole()->original_name)->toBe('SK No.12.pdf');
    });

    test('the decree of a process assignment is uploaded by whoever may assign on its object, and the holder reads it', function () {
        app(AssignmentSubjects::class)->register(new class implements AssignmentSubjectType
        {
            public function key(): string
            {
                return 'probe.package';
            }

            public function kind(): string
            {
                return AssignmentRoles::PACKAGE;
            }

            public function assignCapability(): string
            {
                return 'procurement.package.manage';
            }

            public function idOf(string $reference): ?int
            {
                return $reference === 'PKT-1' ? 1 : null;
            }

            public function accessSubject(int $id): ?AccessSubject
            {
                return $id === 1 ? new AccessSubject(unitId: (int) OrgUnit::query()->where('code', 'FT.TI')->value('id')) : null;
            }

            public function describe(int $id): ?array
            {
                return $id === 1 ? ['reference' => 'PKT-1', 'label' => 'Pengadaan laptop'] : null;
            }
        });

        $holder = User::factory()->withTwoFactor()->create(['name' => 'Perencana']);
        $assignment = app(AssignProcessRole::class)->handle($this->admin, 'probe.package', 1, 'PLANNER', $holder, BusinessDate::today(), null, 'SK/REK/2026/101');

        $this->actingAs($this->admin);
        $this->get(evidenceUrl('process_assignments', $assignment->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('panel.owner.title', 'PLANNER · Perencana')
                ->where('panel.owner.subtitle', 'PKT-1 Pengadaan laptop · SK SK/REK/2026/101')
                ->where('panel.owner.parent_url', route('access.admin.assignments.create', ['type' => 'probe.package', 'reference' => 'PKT-1'], absolute: false))
                ->where('panel.can.upload', true));

        uploadEvidence($this, 'process_assignments', $assignment->id, ['file' => pngUpload()])->assertSessionHasNoErrors();

        expect($assignment->refresh()->evidence_file_id)->toBe(FileObject::query()->sole()->id);

        $this->get(route('access.admin.assignments.create', ['type' => 'probe.package', 'reference' => 'PKT-1']))
            ->assertInertia(fn (Assert $page) => $page->where('panel.assignments.0.id', $assignment->id)->where('panel.assignments.0.has_evidence', true));

        // The holder may not open PG-504, so the way back is the dashboard (no dead link).
        $this->actingAs($holder)->get(evidenceUrl('process_assignments', $assignment->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('panel.can.upload', false)
                ->where('panel.owner.parent_url', route('dashboard', absolute: false))
                ->has('panel.files', 1));
        uploadEvidence($this, 'process_assignments', $assignment->id, ['file' => pngUpload()])->assertForbidden();

        $this->actingAs(User::factory()->withTwoFactor()->create())->get(evidenceUrl('process_assignments', $assignment->id))->assertForbidden();
    });
});

describe('downloading (TAC-02, SEC-11, SEC-12)', function () {
    beforeEach(function () {
        $this->actingAs($this->admin);
        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload('SK Kaprodi.pdf')])->assertSessionHasNoErrors();
        $this->file = FileObject::query()->sole();
    });

    test('sends the verified bytes as an attachment with nosniff, inline only for a safe type, and records a confidential download', function () {
        $response = $this->get(route('files.download', $this->file->public_id))->assertOk();

        expect($response->headers->get('Content-Type'))->toBe('application/pdf')
            ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
            ->and($response->headers->get('Content-Disposition'))->toStartWith('attachment; filename="SK Kaprodi.pdf"')
            ->and($response->headers->get('Cache-Control'))->toContain('no-store')
            ->and(downloadedBytes($response))->toBe(PDF_BYTES)
            ->and(DB::table('audit_events')->where('action', 'file.downloaded')->exists())->toBeFalse();

        $inline = $this->get(route('files.download', ['file' => $this->file->public_id, 'inline' => 1]))->assertOk();

        expect($inline->headers->get('Content-Disposition'))->toStartWith('inline; filename="SK Kaprodi.pdf"');

        // A type that is not previewable is always an attachment, whatever the query says.
        DB::statement("update file_objects set classification = 'CONFIDENTIAL' where id = ?", [$this->file->id]);
        $zip = app(PrivateFileStorage::class)->writeNew(tap(tempnam(sys_get_temp_dir(), 'zip'), fn ($path) => file_put_contents($path, "PK\x05\x06".str_repeat("\0", 18))));
        $archive = FileObject::query()->create([
            'disk' => $zip->disk, 'path' => $zip->path, 'original_name' => 'lampiran.docx', 'mime_type' => 'application/zip',
            'size_bytes' => $zip->sizeBytes, 'sha256' => $zip->sha256, 'classification' => FileObject::CONFIDENTIAL, 'uploaded_by' => $this->admin->id, 'uploaded_at' => now(),
        ]);
        FileLink::query()->create(['file_id' => $archive->id, 'owner_type' => 'org_appointments', 'owner_id' => $this->appointment->id, 'purpose' => 'DECREE', 'linked_by' => $this->admin->id]);

        $forced = $this->get(route('files.download', ['file' => $archive->public_id, 'inline' => 1]))->assertOk();

        // A name without a space is a token, which the header carries unquoted.
        expect($forced->headers->get('Content-Disposition'))->toStartWith('attachment; filename=lampiran.docx');

        $this->get(route('files.download', $this->file->public_id))->assertOk();

        $audits = DB::table('audit_events')->where('action', 'file.downloaded')->orderBy('id')->get();

        expect($audits)->toHaveCount(2)
            ->and($audits[0]->subject_public_id)->toBe($archive->public_id)
            ->and($audits[1]->subject_public_id)->toBe($this->file->public_id)
            ->and($audits[1]->actor_user_id)->toBe($this->admin->id)
            ->and(json_decode((string) $audits[1]->metadata, true)['classification'])->toBe('CONFIDENTIAL');
    });

    test('is refused without the subject permission, and the bytes are reachable through no other address', function () {
        $this->actingAs($this->kaprodi)->get(route('files.download', $this->file->public_id))->assertOk();

        foreach ([User::factory()->withTwoFactor()->create(), User::factory()->inRoleGroup(User::VENDOR)->create()] as $user) {
            $this->actingAs($user)->get(route('files.download', $this->file->public_id))->assertForbidden();
        }

        // A released link no longer lets anyone read through it.
        $this->actingAs($this->admin);
        releaseEvidence($this, FileLink::query()->sole())->assertSessionHasNoErrors();
        $this->actingAs($this->kaprodi)->get(route('files.download', $this->file->public_id))->assertForbidden();

        $this->actingAs($this->admin);
        $this->get(route('files.download', strtolower((string) Str::ulid())))->assertNotFound();
        $this->get('/storage/'.$this->file->path)->assertNotFound();
        $this->get('/'.$this->file->path)->assertNotFound();

        $this->post(route('logout'));
        $this->get(route('files.download', $this->file->public_id))->assertRedirect(route('login'));
    });

    test('the application works with the privileges of pranata_app for an upload, a scan result and a release', function () {
        // Without the handler, a privilege refusal inside a command reaches this test as its SQLSTATE instead of a 500 page.
        $this->withoutExceptionHandling();

        $state = DatabaseSession::sqlStateAs(DatabaseRoles::APP, function () {
            uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pngUpload()])->assertSessionHasNoErrors();
            DB::table('file_objects')->where('id', $this->file->id)->update(['scan_status' => FileObject::CLEAN]);
            releaseEvidence($this, FileLink::query()->where('file_id', $this->file->id)->sole())->assertSessionHasNoErrors();

            // The effects, seen inside the savepoint before it is rolled back.
            expect(FileObject::query()->count())->toBe(2)
                ->and(FileObject::query()->where('id', $this->file->id)->value('scan_status'))->toBe(FileObject::CLEAN)
                ->and(FileLink::query()->where('file_id', $this->file->id)->sole()->isActive())->toBeFalse()
                ->and($this->appointment->refresh()->evidence_file_id)->toBe(FileObject::query()->where('original_name', 'sk.png')->sole()->id);
        });

        expect($state)->toBeNull();
    });
});

describe('integrity (TAC-04, TAC-07, SEC-22)', function () {
    beforeEach(function () {
        $this->actingAs($this->admin);
        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload()])->assertSessionHasNoErrors();
        $this->file = FileObject::query()->sole();
        $this->disk = Storage::disk((string) config('files.disk'));
    });

    test('a file whose bytes changed on disk is not sent: a localized 500 with the request id, an audit event and no content', function () {
        $this->disk->put($this->file->path, 'isi yang diubah');

        $response = $this->get(route('files.download', $this->file->public_id))->assertStatus(500);

        expect($response->getContent())->not->toContain('isi yang diubah')
            ->and($response->getContent())->not->toContain('%PDF');
        $response->assertInertia(fn (Assert $page) => $page->component('errors/show')
            ->where('status', 500)
            ->where('description', __('files.integrity_failed'))
            ->has('requestId'));

        $audit = DB::table('audit_events')->where('action', 'file.integrity_failed')->sole();
        $metadata = json_decode((string) $audit->metadata, true);

        expect($audit->actor_user_id)->toBe($this->admin->id)
            ->and($audit->subject_public_id)->toBe($this->file->public_id)
            ->and($metadata['reason'])->toBe(FileIntegrityException::MISMATCH)
            ->and($metadata['during'])->toBe('download')
            ->and($metadata['found']['sha256'])->toBe(hash('sha256', 'isi yang diubah'))
            ->and($metadata['expected']['sha256'])->toBe($this->file->sha256)
            // The subjects of the incident: the appointment, once, through its link and its evidence column.
            ->and($metadata['references'])->toEqual([['owner_type' => 'org_appointments', 'owner_id' => $this->appointment->id]])
            // Nothing is repaired, replaced or released.
            ->and($this->file->refresh()->sha256)->toBe(hash('sha256', PDF_BYTES))
            ->and(FileLink::query()->sole()->isActive())->toBeTrue()
            ->and($this->disk->get($this->file->path))->toBe('isi yang diubah');

        $this->get(route('files.download', ['file' => $this->file->public_id, 'inline' => 1]))->assertStatus(500);

        expect(DB::table('audit_events')->where('action', 'file.integrity_failed')->count())->toBe(2);
    });

    test('a file missing from disk fails the same way, and the server-side read of a damaged file fails closed too', function () {
        $this->disk->delete($this->file->path);

        $this->get(route('files.download', $this->file->public_id))->assertStatus(500);

        expect(json_decode((string) DB::table('audit_events')->where('action', 'file.integrity_failed')->sole()->metadata, true)['reason'])->toBe(FileIntegrityException::MISSING);

        $integrity = app(FileIntegrity::class);

        expect(fn () => $integrity->read($this->file))->toThrow(FileIntegrityException::class);

        $this->disk->put($this->file->path, 'rusak');

        expect(fn () => $integrity->read($this->file))->toThrow(FileIntegrityException::class);

        $this->disk->put($this->file->path, PDF_BYTES);

        expect($integrity->read($this->file))->toBe(PDF_BYTES);
    });

    test('the daily job reports a sealed file whose bytes changed as an incident and repairs nothing; a sealed file that is intact passes', function () {
        // A probe sealed owner, registered as its migration would register it; the seal itself is checked at commit only.
        DB::statement('set local role '.DatabaseRoles::OWNER);
        DB::statement('create table seal_probe_owners (id bigint primary key, capture_manifest_sha256 char(64) not null)');
        DB::statement('create function seal_probe_manifest(p_owner bigint) returns text language sql stable as $$ select repeat(\'0\', 64) $$');
        DB::table('file_sealed_owner_types')->insert(['owner_type' => 'seal_probe_owners', 'seal_function' => 'seal_probe_manifest', 'registered_in' => 'PrivateFilesTest']);
        DB::statement('reset role');
        DB::table('seal_probe_owners')->insert(['id' => 1, 'capture_manifest_sha256' => str_repeat('0', 64)]);

        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pngUpload('utuh.png')])->assertSessionHasNoErrors();
        $intact = FileObject::query()->where('original_name', 'utuh.png')->sole();

        foreach ([$this->file, $intact] as $file) {
            FileLink::query()->create(['file_id' => $file->id, 'owner_type' => 'seal_probe_owners', 'owner_id' => 1, 'purpose' => 'OFFER', 'linked_by' => $this->admin->id]);
        }

        $this->disk->put($this->file->path, PDF_BYTES.'ditambah');

        $this->artisan('files:verify')
            ->expectsOutputToContain($this->file->public_id.': MISMATCH')
            ->expectsOutputToContain('checked 2 sealed file(s), 1 integrity incident(s)')
            ->assertExitCode(1);

        $audit = DB::table('audit_events')->where('action', 'file.integrity_failed')->sole();

        expect($audit->subject_public_id)->toBe($this->file->public_id)
            ->and(json_decode((string) $audit->metadata, true)['during'])->toBe('verify')
            ->and($this->disk->get($this->file->path))->toBe(PDF_BYTES.'ditambah')
            ->and($this->file->refresh()->sha256)->toBe(hash('sha256', PDF_BYTES))
            ->and(FileLink::query()->where('file_id', $this->file->id)->whereNull('unlinked_at')->count())->toBe(2);

        $this->disk->put($this->file->path, PDF_BYTES);

        $this->artisan('files:verify')->expectsOutputToContain('checked 2 sealed file(s), 0 integrity incident(s)')->assertExitCode(0);
    });
});

describe('releasing a link (TAC-06)', function () {
    beforeEach(function () {
        $this->actingAs($this->admin);
        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload('pertama.pdf')])->assertSessionHasNoErrors();
        uploadEvidence($this, 'org_appointments', $this->appointment->id, ['file' => pdfUpload('kedua.pdf')])->assertSessionHasNoErrors();
        $this->links = FileLink::query()->orderBy('id')->get();
    });

    test('releases once, moves the evidence column to the newest remaining file, keeps the history and is audited', function () {
        $newest = $this->links[1];
        $older = $this->links[0];

        expect($this->appointment->refresh()->evidence_file_id)->toBe($newest->file_id);

        releaseEvidence($this, $newest)->assertSessionHasNoErrors()->assertRedirect();

        expect($newest->refresh()->isActive())->toBeFalse()
            ->and($newest->unlinked_by)->toBe($this->admin->id)
            ->and($this->appointment->refresh()->evidence_file_id)->toBe($older->file_id)
            ->and(FileObject::query()->count())->toBe(2)
            ->and(DB::table('audit_events')->where('action', 'file.unlinked')->sole()->subject_public_id)->toBe($newest->file->public_id);

        releaseEvidence($this, $newest)->assertSessionHasErrors('_domain');

        $this->get(evidenceUrl('org_appointments', $this->appointment->id))
            ->assertInertia(fn (Assert $page) => $page->has('panel.files', 2)
                ->where('panel.files.0.active', true)
                ->where('panel.files.1.active', false)
                ->where('panel.files.1.can_release', false)
                ->where('panel.files.1.unlinked_by', 'Admin Organisasi'));

        releaseEvidence($this, $older)->assertSessionHasNoErrors();

        expect($this->appointment->refresh()->evidence_file_id)->toBeNull();
    });

    test('is refused to anyone who may not manage the row, and a row that does not exist fails closed', function () {
        $link = $this->links[0];

        $this->actingAs($this->kaprodi);
        releaseEvidence($this, $link)->assertForbidden();

        // Nobody may manage a row that does not exist, so the link is never reached through another address.
        $this->actingAs($this->admin);
        $this->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])
            ->post(route('files.evidence.release', ['type' => 'org_appointments', 'id' => $this->appointment->id + 1, 'link' => $link->id]))
            ->assertForbidden();

        expect($link->refresh()->isActive())->toBeTrue();
    });

    test('a link of a sealed owner type shows no release and is refused with a reason', function () {
        DB::statement('set local role '.DatabaseRoles::OWNER);
        DB::statement('create table seal_probe_owners (id bigint primary key, capture_manifest_sha256 char(64) not null)');
        DB::table('file_sealed_owner_types')->insert(['owner_type' => 'seal_probe_owners', 'seal_function' => 'seal_probe_manifest', 'registered_in' => 'PrivateFilesTest']);
        DB::statement('reset role');
        DB::table('seal_probe_owners')->insert(['id' => 1, 'capture_manifest_sha256' => str_repeat('0', 64)]);

        app(FileOwnerTypes::class)->register(new class implements FileOwnerType
        {
            public function ownerType(): string
            {
                return 'seal_probe_owners';
            }

            public function purposes(): array
            {
                return ['OFFER'];
            }

            public function classification(): string
            {
                return FileObject::CONFIDENTIAL;
            }

            public function describe(int $ownerId, User $actor): ?OwnerDescription
            {
                return $ownerId === 1 ? new OwnerDescription('Kiriman uji', null, '/home', 'Beranda') : null;
            }

            public function allowsRead(User $actor, int $ownerId): bool
            {
                return true;
            }

            public function allowsManage(User $actor, int $ownerId): bool
            {
                return true;
            }

            public function referencing(int $fileId): array
            {
                return [];
            }

            public function evidenceChanged(int $ownerId, ?int $fileId, User $actor): void {}
        });

        $sealed = FileLink::query()->create(['file_id' => $this->links[0]->file_id, 'owner_type' => 'seal_probe_owners', 'owner_id' => 1, 'purpose' => 'OFFER', 'linked_by' => $this->admin->id]);

        $this->get(evidenceUrl('seal_probe_owners', 1))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('panel.can.upload', false)->where('panel.files.0.can_release', false));

        releaseEvidence($this, $sealed)->assertSessionHasErrors(['_domain' => __('files.link_sealed')]);

        // The upload is hidden on the page and refused by the server before any byte is written (ACCESS_CONTROL section 8).
        $before = Storage::disk((string) config('files.disk'))->allFiles();
        uploadEvidence($this, 'seal_probe_owners', 1, ['file' => pdfUpload(), 'purpose' => 'OFFER'])
            ->assertSessionHasErrors(['_domain' => __('files.owner_sealed')]);

        expect($sealed->refresh()->isActive())->toBeTrue()
            ->and(FileObject::query()->count())->toBe(2)
            ->and(Storage::disk((string) config('files.disk'))->allFiles())->toBe($before);
    });
});
