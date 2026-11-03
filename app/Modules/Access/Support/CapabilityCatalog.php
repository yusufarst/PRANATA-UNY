<?php

namespace App\Modules\Access\Support;

use App\Models\User;

/**
 * The capability catalog of ACCESS_CONTROL section 4, the only list of capabilities (DATA_MODEL section 9). Each capability
 * names the scopes a grant may give it in, as the letters of the catalog; an empty list means the capability comes only from a
 * process assignment on the subject (section 5, TASK-015), never from a grant.
 *
 * The capabilities of the Vendor Portal (portal.*) come from the link of a vendor user to its company, not from a grant, and
 * join the catalog with the Vendor module. A later module adds its own capabilities here and to ACCESS_CONTROL together.
 */
final class CapabilityCatalog
{
    /**
     * @var array<string, string>
     */
    private const CAPABILITIES = [
        // 4.1 Administrasi
        'admin.users.manage' => 'G',
        'admin.access.grant' => 'G',
        'admin.access.approve' => 'G',
        'admin.org.manage' => 'G',
        'admin.reference.manage' => 'G',
        'admin.policy.author' => 'P',
        'admin.policy.activate' => 'P',
        'admin.templates.manage' => 'G',
        'admin.audit.read' => 'G',
        'admin.confidential.read' => 'G',
        'admin.system.monitor' => 'G',

        // 4.2 Pengadaan
        'procurement.need.create' => 'U',
        'procurement.need.submit' => 'U',
        'procurement.need.review' => 'SG',
        'procurement.rup.record' => 'SG',
        'procurement.package.view' => 'SG',
        'procurement.package.manage' => 'SG',
        'procurement.hps.prepare' => 'SG',
        'procurement.hps.set' => '',
        'procurement.method.suggest' => 'SG',
        'procurement.method.determine' => '',
        'procurement.activity.manage' => 'SG',
        'procurement.selection.manage' => '',
        'procurement.evaluation_scheme.manage' => '',
        'procurement.evaluation_scheme.freeze' => '',
        'procurement.opening.record' => '',
        'procurement.submission.record' => '',
        'procurement.submission.view_confidential' => '',
        'procurement.evaluation.record' => '',
        'procurement.evaluation.complete' => '',
        'procurement.clarification.manage' => '',
        'procurement.negotiation.record' => '',
        'procurement.result.propose' => '',
        'procurement.result.determine' => '',
        'procurement.result.review' => '',
        'procurement.sppbj.prepare' => '',
        'procurement.sppbj.check' => '',
        'procurement.sppbj.issue' => '',
        'procurement.contract.prepare' => '',
        'procurement.contract.approve' => '',
        'procurement.contract.record_signature' => '',
        'procurement.amendment.propose' => '',
        'procurement.amendment.review' => '',
        'procurement.amendment.decide' => '',
        'procurement.execution.record' => 'S',
        'procurement.spj.manage' => 'SG',
        'procurement.payment.track' => 'SG',
        'procurement.external_reference.record' => 'SG',
        'procurement.report' => 'SG',

        // 4.3 Penyedia (internal)
        'vendor.company.manage' => 'G',
        'vendor.documents.verify' => 'G',
        'vendor.dpt.decide' => 'G',
        'vendor.performance.review' => 'G',

        // 4.4 Gudang
        'inventory.request.create' => 'U',
        'inventory.request.decide' => '',
        'inventory.handover.record' => 'W',
        'inventory.return.receive' => 'W',
        'inventory.return.inspect' => 'W',
        'inventory.receipt.record' => 'W',
        'inventory.count.manage' => 'W',
        'inventory.adjustment.propose' => 'W',
        'inventory.adjustment.approve' => 'W',
        'inventory.transfer.dispatch' => 'W',
        'inventory.transfer.receive' => 'W',
        'inventory.item.manage' => 'WG',
        'inventory.view' => 'W',
        'inventory.report' => 'W',

        // 4.5 Aset, rekonsiliasi, intake, laporan
        'asset.view' => 'SG',
        'asset.register' => 'SG',
        'asset.recognize' => 'SG',
        'asset.mutation.propose' => 'S',
        'asset.mutation.decide' => 'S',
        'asset.verification.manage' => 'S',
        'asset.maintenance.record' => 'S',
        'asset.change.propose' => 'S',
        'asset.change.decide' => 'S',
        'asset.disposal.propose' => 'S',
        'asset.disposal.decide' => 'S',
        'asset.depreciation.run' => 'G',
        'asset.kdp.manage' => 'SG',
        'asset.report' => 'SG',
        'recon.case.manage' => 'SWG',
        'recon.case.accept' => 'SWG',
        'intake.batch.manage' => 'G',
        'intake.batch.decide' => 'G',
        'report.request' => 'G',
    ];

    /**
     * The last word of a decision capability: a GLOBAL grant of one is privileged (ACCESS_CONTROL section 9).
     */
    private const DECISION_VERBS = ['approve', 'accept', 'activate', 'decide', 'determine', 'issue', 'set'];

    /**
     * The workspace a capability belongs to, by its first word (ACCESS_CONTROL section 3).
     */
    private const WORKSPACES = [
        'admin' => 'administration',
        'procurement' => 'procurement',
        'vendor' => 'procurement',
        'inventory' => 'warehouse',
        'asset' => 'asset',
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::CAPABILITIES);
    }

    public static function exists(string $capability): bool
    {
        return array_key_exists($capability, self::CAPABILITIES);
    }

    /**
     * The scopes a grant may give the capability in; none for a capability that comes only from an assignment.
     *
     * @return list<ScopeType>
     */
    public static function scopes(string $capability): array
    {
        $letters = self::CAPABILITIES[$capability] ?? '';

        return array_map(ScopeType::fromLetter(...), $letters === '' ? [] : str_split($letters));
    }

    public static function allows(string $capability, ScopeType $scope): bool
    {
        return in_array($scope, self::scopes($capability), true);
    }

    public static function isGrantable(string $capability): bool
    {
        return self::scopes($capability) !== [];
    }

    public static function isDecision(string $capability): bool
    {
        $words = explode('.', $capability);

        return in_array(end($words), self::DECISION_VERBS, true);
    }

    /**
     * Whether a grant of the capability in the scope needs a second person to approve it: every admin.* capability, and a
     * decision capability in the GLOBAL scope (ACCESS_CONTROL section 9, SOD-05).
     */
    public static function isPrivileged(string $capability, ScopeType $scope): bool
    {
        return str_starts_with($capability, 'admin.') || ($scope === ScopeType::Global && self::isDecision($capability));
    }

    public static function workspaceOf(string $capability): ?string
    {
        return self::WORKSPACES[strstr($capability, '.', true) ?: $capability] ?? null;
    }

    /**
     * The capabilities a role group may hold at all (ACCESS_CONTROL section 2, layer 2 of section 1). A SUPER_ADMIN manages the
     * system and never decides business (INV-021); an INTERNAL user may also be a delegated administrator; a VENDOR user holds
     * only the Vendor Portal, which is outside this catalog.
     */
    public static function roleGroupAllows(string $roleGroup, string $capability): bool
    {
        return match ($roleGroup) {
            User::SUPER_ADMIN => str_starts_with($capability, 'admin.'),
            User::INTERNAL => true,
            default => false,
        };
    }

    /**
     * Whether a SUPER_ADMIN holds the capability by its role group, without a grant: the GLOBAL administration of the system
     * (OD-10). A policy-family capability still needs its POLICY_FAMILY grant (AUTHZ-25).
     */
    public static function heldBySuperAdmin(string $capability): bool
    {
        return str_starts_with($capability, 'admin.') && self::scopes($capability) === [ScopeType::Global];
    }
}
