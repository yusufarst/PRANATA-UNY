<?php

namespace App\Modules\Access\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * A named set of capabilities that a grant gives at once (ACCESS_CONTROL section 9). A privileged profile needs a second
 * person to approve each grant of it.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string|null $workspace
 * @property bool $is_privileged
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AccessProfile extends Model
{
    public const ACTIVE = 'ACTIVE';

    public const INACTIVE = 'INACTIVE';

    protected $table = 'access_profiles';

    protected $guarded = ['id'];

    /**
     * @return list<string>
     */
    public function capabilities(): array
    {
        return DB::table('access_profile_capabilities')
            ->where('profile_id', $this->id)
            ->orderBy('capability')
            ->pluck('capability')
            ->map(fn (mixed $capability): string => (string) $capability)
            ->values()
            ->all();
    }

    protected function casts(): array
    {
        return [
            'is_privileged' => 'boolean',
        ];
    }
}
