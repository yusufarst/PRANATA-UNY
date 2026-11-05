<?php

namespace App\Modules\Access\Http\Controllers;

use App\Models\User;
use App\Modules\Access\Actions\AssignProcessRole;
use App\Modules\Access\Actions\CloseAssignment;
use App\Modules\Access\Http\Requests\AssignmentForm;
use App\Modules\Access\Http\Requests\CloseAssignmentForm;
use App\Modules\Access\Models\ProcessAssignment;
use App\Modules\Access\Providers\AccessServiceProvider;
use App\Modules\Access\Support\AssignmentPanel;
use App\Modules\Access\Support\AssignmentRoles;
use App\Modules\Access\Support\AssignmentSubjects;
use App\Modules\Organization\Support\BusinessDate;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PG-504 Penugasan proses (INFORMATION_ARCHITECTURE section 3.6, RT-002): the list of assignments on every kind of object, the
 * object an administrator opens to assign people on it, and the commands of the assignment panel that module pages reuse.
 *
 * The list and the object page need can:access.administer_assignments. Assigning and closing need the right to assign on the
 * object itself ({@see AssignmentSubjects::mayAssign()}), so a module user assigns from the panel on their own object.
 */
class AssignmentController
{
    private const STATUSES = [ProcessAssignment::ACTIVE, ProcessAssignment::ENDED, ProcessAssignment::REVOKED];

    public function __construct(private readonly AssignmentSubjects $subjects) {}

    public function index(Request $request): Response
    {
        Gate::authorize(AccessServiceProvider::ADMINISTER_ASSIGNMENTS);

        $q = $request->query('q');
        $role = $request->query('role');
        $filters = [
            'q' => is_string($q) ? trim($q) : '',
            'status' => in_array($request->query('status'), self::STATUSES, true) ? (string) $request->query('status') : '',
            'role' => is_string($role) && AssignmentRoles::exists($role) ? $role : '',
        ];

        $assignments = ProcessAssignment::query()
            ->with('user:id,public_id,username,name')
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%'.addcslashes(mb_strtolower($filters['q']), '\\%_').'%';

                $query->whereHas('user', fn ($query) => $query->where(fn ($query) => $query
                    ->whereRaw('lower(username) like ?', [$term])
                    ->orWhereRaw('lower(name) like ?', [$term])));
            })
            // An ACTIVE assignment past its end has lapsed: it is listed with the ended ones.
            ->when($filters['status'] === ProcessAssignment::ACTIVE, fn ($query) => $query->current())
            ->when($filters['status'] === ProcessAssignment::ENDED, fn ($query) => $query->where(fn ($query) => $query
                ->where('status', ProcessAssignment::ENDED)
                ->orWhere(fn ($query) => $query->lapsed())))
            ->when($filters['status'] === ProcessAssignment::REVOKED, fn ($query) => $query->where('status', ProcessAssignment::REVOKED))
            ->when($filters['role'] !== '', fn ($query) => $query->where('assignment_role', $filters['role']))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // The list only reads; an assignment closes from the panel of its object.
        $rows = AssignmentPanel::rows(array_values($assignments->getCollection()->all()), false);

        foreach ($rows as $index => $row) {
            $assignment = $assignments->getCollection()->get($index);
            $rows[$index]['subject'] = $assignment === null ? null : $this->describe($assignment);
        }

        return Inertia::render('admin/assignments/index', [
            'assignments' => [...$assignments->toArray(), 'data' => $rows],
            'filters' => $filters,
            'roles' => array_map(fn (string $role): array => ['value' => $role, 'decision' => AssignmentRoles::isDecision($role)], AssignmentRoles::all()),
            'types' => $this->types(),
        ]);
    }

    /**
     * The object an administrator opens by its type and reference, with its assignment panel.
     */
    public function create(Request $request, AssignmentPanel $panel): Response
    {
        Gate::authorize(AccessServiceProvider::ADMINISTER_ASSIGNMENTS);

        $type = is_string($request->query('type')) ? (string) $request->query('type') : '';
        $reference = is_string($request->query('reference')) ? trim((string) $request->query('reference')) : '';
        $id = $type === '' || $reference === '' ? null : $this->subjects->find($type)?->idOf($reference);

        return Inertia::render('admin/assignments/create', [
            'types' => $this->types(),
            'query' => ['type' => $type, 'reference' => $reference],
            'panel' => $id === null ? null : $panel->for($request->user(), $type, $id),
            'notFound' => $reference !== '' && $id === null,
        ]);
    }

    public function store(AssignmentForm $request, AssignProcessRole $assign): RedirectResponse
    {
        $type = $request->string('subject_type')->value();
        $id = $this->subjects->find($type)?->idOf($request->string('subject')->value());

        // Only someone who may open every kind of object learns that a reference does not exist (ACCESS_CONTROL section 8).
        abort_if($id === null, Gate::allows(AccessServiceProvider::ADMINISTER_ASSIGNMENTS) ? 404 : 403);
        Gate::authorize(AccessServiceProvider::ASSIGN, [$type, $id]);

        $user = User::query()->where('username', $request->string('username')->trim()->value())->first();

        if ($user === null) {
            throw ValidationException::withMessages(['username' => __('access.user_unknown')]);
        }

        $assign->handle(
            $request->user(),
            $type,
            $id,
            $request->string('assignment_role')->value(),
            $user,
            self::date($request->string('effective_from')->value()) ?? BusinessDate::today(),
            $request->filled('effective_to') ? self::date($request->string('effective_to')->value()) : null,
            $request->filled('decree_ref') ? $request->string('decree_ref')->trim()->value() : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('access.assignment_saved')]);

        return back();
    }

    public function close(CloseAssignmentForm $request, ProcessAssignment $assignment, CloseAssignment $closeAssignment): RedirectResponse
    {
        Gate::authorize(AccessServiceProvider::ASSIGN, [$assignment->subject_type, $assignment->subject_id]);

        $status = $request->string('status')->value();

        // A StaleVersionException goes back to the page with errors.lock_version, whether PG-504 or a module page, and its
        // conflict dialog loads the latest state of the object.
        $closeAssignment->handle($request->user(), $assignment, $status, $request->string('reason')->trim()->value(), $request->integer('lock_version'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __($status === ProcessAssignment::REVOKED ? 'access.assignment_revoked' : 'access.assignment_ended')]);

        return back();
    }

    /**
     * @return list<array{key: string, kind: string}>
     */
    private function types(): array
    {
        return array_map(fn ($type): array => ['key' => $type->key(), 'kind' => $type->kind()], $this->subjects->all());
    }

    /**
     * @return array{type: string, reference: string|null, label: string|null}
     */
    private function describe(ProcessAssignment $assignment): array
    {
        $described = $this->subjects->find($assignment->subject_type)?->describe($assignment->subject_id);

        // A type whose module is not installed shows by its type and id only.
        return [
            'type' => $assignment->subject_type,
            'reference' => $described['reference'] ?? null,
            'label' => $described['label'] ?? null,
        ];
    }

    private static function date(string $value): ?CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $value, BusinessDate::TIMEZONE) ?: null;
    }
}
