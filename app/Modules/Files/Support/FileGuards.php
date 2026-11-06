<?php

namespace App\Modules\Files\Support;

use App\Modules\Platform\Support\IntegrityTriggers;

/**
 * The SQLSTATEs of the file guards of DATA_MODEL section 12, so tests and callers can tell them from privilege and
 * constraint errors (as {@see IntegrityTriggers} does for the generic ones).
 */
final class FileGuards
{
    /**
     * guard_file_object_change(): the content identity of a file changed, the classification was lowered, the scan status went
     * back to NOT_SCANNED, or a file object was deleted.
     */
    public const OBJECT_CHANGE = 'PN006';

    /**
     * guard_file_link_change(): the anchors of a link changed, a released link changed again, an owner type is not a table, a
     * sealed owner row is missing, or a link of a sealed owner type was changed or deleted.
     */
    public const LINK_CHANGE = 'PN007';

    /**
     * verify_file_link_manifest(): the capture seal of a sealed owner did not match at commit (the same code as the generic
     * verify_capture_manifest()).
     */
    public const CAPTURE_MANIFEST = IntegrityTriggers::CAPTURE_MANIFEST;
}
