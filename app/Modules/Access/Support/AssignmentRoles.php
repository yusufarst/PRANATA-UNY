<?php

namespace App\Modules\Access\Support;

/**
 * The assignment roles of ACCESS_CONTROL section 5 and the capabilities each one gives on its object (the capabilities of
 * section 4 marked "Penugasan"). A role is a technical label: which official function may hold it is a matter of policy
 * (GAP-003/004/014), never of this catalog.
 *
 * A role binds to the kinds of object named here; a module registers its objects under one of these kinds
 * ({@see AssignmentSubjects}). A decision role decides business (INV-021): a SUPER_ADMIN never holds one (SOD-08), and its
 * holder is a privileged account for TOTP and an assisted reset (SEC-21).
 */
final class AssignmentRoles
{
    public const PACKAGE = 'package';

    public const CONTRACT = 'contract';

    public const WAREHOUSE_REQUEST = 'warehouse_request';

    public const RETURN = 'return';

    public const ASSET_CASE = 'asset_case';

    public const RECON_CASE = 'recon_case';

    /**
     * Role => [kinds of object, whether it decides business].
     *
     * @var array<string, array{list<string>, bool}>
     */
    private const ROLES = [
        'PPK' => [[self::PACKAGE, self::CONTRACT], true],
        'PLANNER' => [[self::PACKAGE], false],
        'POKJA_CHAIR' => [[self::PACKAGE], true],
        'POKJA_MEMBER' => [[self::PACKAGE], true],
        'PEJABAT_PENGADAAN' => [[self::PACKAGE], true],
        'PROCUREMENT_AGENT' => [[self::PACKAGE], false],
        'METHOD_DETERMINER' => [[self::PACKAGE], true],
        'HPS_SETTER' => [[self::PACKAGE], true],
        'WINNER_DETERMINER' => [[self::PACKAGE], true],
        'SPPBJ_PREPARER' => [[self::PACKAGE], false],
        'SPPBJ_CHECKER' => [[self::PACKAGE], false],
        'SPPBJ_ISSUER' => [[self::PACKAGE], true],
        'SPPBJ_SIGNER' => [[self::PACKAGE], true],
        'CONTRACT_SIGNER' => [[self::CONTRACT], true],
        'AMENDMENT_DECIDER' => [[self::CONTRACT], true],
        'GOODS_RECEIVER' => [[self::CONTRACT], true],
        'QUALITY_CONTROLLER' => [[self::CONTRACT], false],
        'PJPHP' => [[self::CONTRACT], true],
        'PPHP' => [[self::CONTRACT], true],
        'TECHNICAL_TEAM' => [[self::CONTRACT], false],
        'TECHNICAL_REVIEWER' => [[self::PACKAGE], false],
        'REQUEST_APPROVER' => [[self::WAREHOUSE_REQUEST], true],
        'RETURN_INSPECTOR' => [[self::RETURN], false],
        'ASSET_DECIDER' => [[self::ASSET_CASE], true],
        'RECON_OWNER' => [[self::RECON_CASE], false],
        'RECON_ACCEPTOR' => [[self::RECON_CASE], true],
    ];

    /**
     * The roles whose active assignment on the object gives the capability there (ACCESS_CONTROL section 4). A capability
     * of the catalog that is not listed comes from a grant only.
     *
     * @var array<string, list<string>>
     */
    private const CAPABILITIES = [
        'procurement.hps.set' => ['HPS_SETTER'],
        'procurement.method.determine' => ['METHOD_DETERMINER'],
        'procurement.activity.manage' => ['POKJA_CHAIR', 'PEJABAT_PENGADAAN', 'PLANNER'],
        'procurement.selection.manage' => ['POKJA_CHAIR', 'PEJABAT_PENGADAAN', 'PLANNER'],
        'procurement.evaluation_scheme.manage' => ['POKJA_CHAIR', 'PEJABAT_PENGADAAN'],
        'procurement.evaluation_scheme.freeze' => ['POKJA_CHAIR', 'PEJABAT_PENGADAAN'],
        'procurement.opening.record' => ['POKJA_CHAIR', 'POKJA_MEMBER', 'PEJABAT_PENGADAAN'],
        'procurement.submission.record' => ['POKJA_CHAIR', 'POKJA_MEMBER', 'PEJABAT_PENGADAAN'],
        'procurement.submission.view_confidential' => ['POKJA_CHAIR', 'POKJA_MEMBER', 'PEJABAT_PENGADAAN'],
        'procurement.evaluation.record' => ['POKJA_MEMBER', 'PEJABAT_PENGADAAN', 'TECHNICAL_REVIEWER'],
        'procurement.evaluation.complete' => ['POKJA_CHAIR', 'PEJABAT_PENGADAAN'],
        'procurement.clarification.manage' => ['POKJA_CHAIR', 'POKJA_MEMBER', 'PEJABAT_PENGADAAN', 'PPK'],
        'procurement.negotiation.record' => ['POKJA_CHAIR', 'POKJA_MEMBER', 'PEJABAT_PENGADAAN', 'PPK'],
        'procurement.result.propose' => ['POKJA_CHAIR', 'PEJABAT_PENGADAAN'],
        'procurement.result.determine' => ['WINNER_DETERMINER'],
        'procurement.result.review' => ['PPK'],
        'procurement.sppbj.prepare' => ['SPPBJ_PREPARER'],
        'procurement.sppbj.check' => ['SPPBJ_CHECKER'],
        'procurement.sppbj.issue' => ['SPPBJ_ISSUER'],
        'procurement.contract.prepare' => ['PPK'],
        'procurement.contract.approve' => ['PPK'],
        'procurement.contract.record_signature' => ['CONTRACT_SIGNER'],
        'procurement.amendment.propose' => ['PPK'],
        'procurement.amendment.review' => ['PPK'],
        'procurement.amendment.decide' => ['AMENDMENT_DECIDER'],
        'procurement.execution.record' => ['PPK', 'GOODS_RECEIVER', 'QUALITY_CONTROLLER', 'PJPHP', 'PPHP', 'TECHNICAL_TEAM'],
        'vendor.performance.review' => ['PPK'],
        'inventory.request.decide' => ['REQUEST_APPROVER'],
        'inventory.return.inspect' => ['RETURN_INSPECTOR'],
        'asset.mutation.decide' => ['ASSET_DECIDER'],
        'asset.change.decide' => ['ASSET_DECIDER'],
        'asset.disposal.decide' => ['ASSET_DECIDER'],
        'recon.case.manage' => ['RECON_OWNER'],
        'recon.case.accept' => ['RECON_ACCEPTOR'],
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::ROLES);
    }

    public static function exists(string $role): bool
    {
        return array_key_exists($role, self::ROLES);
    }

    /**
     * @return list<string>
     */
    public static function kindsOf(string $role): array
    {
        return self::ROLES[$role][0] ?? [];
    }

    /**
     * The roles an object of the kind may carry.
     *
     * @return list<string>
     */
    public static function forKind(string $kind): array
    {
        return array_values(array_filter(self::all(), fn (string $role): bool => in_array($kind, self::kindsOf($role), true)));
    }

    public static function isDecision(string $role): bool
    {
        return self::ROLES[$role][1] ?? false;
    }

    /**
     * @return list<string>
     */
    public static function decisionRoles(): array
    {
        return array_values(array_filter(self::all(), self::isDecision(...)));
    }

    /**
     * The roles that give the capability on their object; none when only a grant gives it.
     *
     * @return list<string>
     */
    public static function givingCapability(string $capability): array
    {
        return self::CAPABILITIES[$capability] ?? [];
    }

    /**
     * @return list<string>
     */
    public static function capabilitiesWithRoles(): array
    {
        return array_keys(self::CAPABILITIES);
    }
}
