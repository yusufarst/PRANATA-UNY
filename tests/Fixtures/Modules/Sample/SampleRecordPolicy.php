<?php

namespace Tests\Fixtures\Modules\Sample;

use App\Models\User;

class SampleRecordPolicy
{
    public function view(User $user, SampleRecord $record): bool
    {
        return $user->id === $record->ownerId;
    }
}
