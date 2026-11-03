<?php

namespace Database\Seeders;

use App\Modules\Access\Models\AccessProfile;
use App\Modules\Access\Support\CapabilityCatalog;
use App\Modules\Access\Support\ScopeType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * The initial access profiles of ACCESS_CONTROL section 9. A Super Admin may change them later, so a profile whose code exists
 * is left as it is; only a missing one is created. Reference data, not synthetic: safe in every environment.
 *
 * A profile is privileged when it holds an admin.* capability or a decision capability that a GLOBAL grant may give.
 */
class AccessProfileSeeder extends Seeder
{
    /**
     * Code => [name, workspace, capabilities].
     *
     * @var array<string, array{string, string, list<string>}>
     */
    public const PROFILES = [
        'UNIT_NEED_OPERATOR' => ['Operator Usulan Unit', 'procurement', [
            'procurement.need.create',
            'procurement.need.submit',
            'inventory.request.create',
        ]],
        'PROCUREMENT_STAFF' => ['Staf Pengadaan Pusat', 'procurement', [
            'procurement.package.view',
            'procurement.package.manage',
            'procurement.hps.prepare',
            'procurement.method.suggest',
            'procurement.activity.manage',
            'procurement.rup.record',
            'procurement.external_reference.record',
            'procurement.spj.manage',
            'procurement.payment.track',
            'procurement.report',
            'vendor.company.manage',
        ]],
        'DPT_VERIFIER' => ['Verifikator DPT', 'procurement', [
            'vendor.documents.verify',
            'vendor.dpt.decide',
        ]],
        'WAREHOUSE_OFFICER' => ['Petugas Gudang', 'warehouse', [
            'inventory.handover.record',
            'inventory.return.receive',
            'inventory.receipt.record',
            'inventory.count.manage',
            'inventory.view',
        ]],
        'WAREHOUSE_ADMIN' => ['Admin Gudang', 'warehouse', [
            'inventory.handover.record',
            'inventory.return.receive',
            'inventory.receipt.record',
            'inventory.count.manage',
            'inventory.view',
            'inventory.item.manage',
            'inventory.adjustment.propose',
            'inventory.transfer.dispatch',
            'inventory.transfer.receive',
            'inventory.report',
        ]],
        'UNIT_ASSET_OPERATOR' => ['Operator Aset Unit', 'asset', [
            'asset.view',
            'asset.register',
            'asset.mutation.propose',
            'asset.verification.manage',
            'asset.maintenance.record',
        ]],
        'CENTRAL_ASSET_STAFF' => ['Staf Aset Pusat', 'asset', [
            'asset.view',
            'asset.register',
            'asset.mutation.propose',
            'asset.verification.manage',
            'asset.maintenance.record',
            'asset.change.propose',
            'asset.disposal.propose',
            'asset.kdp.manage',
            'asset.report',
            'recon.case.manage',
            'intake.batch.manage',
        ]],
        'LEADERSHIP_READ' => ['Pimpinan (baca)', 'procurement', [
            'procurement.package.view',
            'procurement.report',
            'asset.view',
            'asset.report',
            'inventory.view',
            'inventory.report',
        ]],
        'ACCESS_ADMIN' => ['Admin Akses', 'administration', [
            'admin.users.manage',
            'admin.access.grant',
        ]],
    ];

    public function run(): void
    {
        foreach (self::PROFILES as $code => [$name, $workspace, $capabilities]) {
            if (AccessProfile::query()->where('code', $code)->exists()) {
                continue;
            }

            foreach ($capabilities as $capability) {
                if (! CapabilityCatalog::isGrantable($capability)) {
                    throw new LogicException("Profile {$code} lists {$capability}, which no grant can give.");
                }
            }

            $profile = AccessProfile::query()->create([
                'code' => $code,
                'name' => $name,
                'workspace' => $workspace,
                'is_privileged' => self::isPrivileged($capabilities),
                'status' => AccessProfile::ACTIVE,
            ]);

            DB::table('access_profile_capabilities')->insert(array_map(
                fn (string $capability): array => ['profile_id' => $profile->id, 'capability' => $capability],
                $capabilities,
            ));
        }
    }

    /**
     * @param  list<string>  $capabilities
     */
    public static function isPrivileged(array $capabilities): bool
    {
        foreach ($capabilities as $capability) {
            if (str_starts_with($capability, 'admin.')
                || (CapabilityCatalog::allows($capability, ScopeType::Global) && CapabilityCatalog::isDecision($capability))) {
                return true;
            }
        }

        return false;
    }
}
