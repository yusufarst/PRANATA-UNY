<?php

namespace Database\Seeders;

use App\Modules\Organization\Models\OrgPosition;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Models\OrgUnitHistory;
use App\Modules\Organization\Support\UnitPath;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * A synthetic organization for development and tests (TASK-013): a few levels of units of different types, so nothing assumes
 * a structure of faculties only (OD-04), and a few positions. No real names or decrees. Safe to repeat: a unit or position
 * whose code exists is left as it is.
 */
class OrganizationSeeder extends Seeder
{
    public const EFFECTIVE_FROM = '2026-01-01';

    /**
     * Code => [name, short name, type, parent code], parents before children.
     *
     * @var array<string, array{string, ?string, string, ?string}>
     */
    public const UNITS = [
        'UNIV' => ['Universitas Contoh', 'UC', 'UNIVERSITAS', null],
        'REKTORAT' => ['Rektorat', null, 'PIMPINAN', 'UNIV'],
        'FT' => ['Fakultas Teknik', 'FT', 'FAKULTAS', 'UNIV'],
        'FT.TI' => ['Program Studi Teknik Informatika', 'TI', 'PRODI', 'FT'],
        'FT.TS' => ['Program Studi Teknik Sipil', 'TS', 'PRODI', 'FT'],
        'LPPM' => ['Lembaga Penelitian dan Pengabdian kepada Masyarakat', 'LPPM', 'LEMBAGA', 'UNIV'],
        'BUK' => ['Biro Umum dan Keuangan', 'BUK', 'BIRO', 'UNIV'],
        'BUK.PBJ' => ['Bagian Pengadaan Barang/Jasa', 'PBJ', 'BAGIAN', 'BUK'],
    ];

    /**
     * Code => [name, unit code].
     *
     * @var array<string, array{string, string}>
     */
    public const POSITIONS = [
        'REKTOR' => ['Rektor', 'REKTORAT'],
        'DEKAN_FT' => ['Dekan Fakultas Teknik', 'FT'],
        'KAPRODI_TI' => ['Koordinator Program Studi Teknik Informatika', 'FT.TI'],
        'KEPALA_BUK' => ['Kepala Biro Umum dan Keuangan', 'BUK'],
        'KEPALA_PBJ' => ['Kepala Bagian Pengadaan Barang/Jasa', 'BUK.PBJ'],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('OrganizationSeeder is forbidden in production.');
        }

        foreach (self::UNITS as $code => [$name, $shortName, $type, $parentCode]) {
            if (OrgUnit::query()->where('code', $code)->exists()) {
                continue;
            }

            $parent = $parentCode === null ? null : OrgUnit::query()->where('code', $parentCode)->firstOrFail();

            $unit = new OrgUnit([
                'code' => $code,
                'name' => $name,
                'short_name' => $shortName,
                'unit_type' => $type,
                'parent_id' => $parent?->id,
                'status' => OrgUnit::ACTIVE,
                'effective_from' => self::EFFECTIVE_FROM,
            ]);

            $unit->public_id = $unit->newUniqueId();
            $unit->path = UnitPath::below($parent, $unit->public_id);
            $unit->depth = UnitPath::depth($unit->path);
            $unit->save();
            $unit->setRelation('parent', $parent);

            $unit->history()->create([
                'change_type' => OrgUnitHistory::CREATE,
                'before' => null,
                'after' => $unit->snapshot(),
                'effective_from' => self::EFFECTIVE_FROM,
                'decision_ref' => 'SINTETIS/001',
                'changed_at' => now(),
            ]);
        }

        foreach (self::POSITIONS as $code => [$name, $unitCode]) {
            OrgPosition::query()->firstOrCreate(['code' => $code], [
                'name' => $name,
                'org_unit_id' => OrgUnit::query()->where('code', $unitCode)->value('id'),
                'status' => OrgPosition::ACTIVE,
            ]);
        }
    }
}
