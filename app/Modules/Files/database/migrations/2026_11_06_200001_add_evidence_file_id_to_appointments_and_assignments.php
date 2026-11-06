<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * evidence_file_id of org_appointments and process_assignments (DATA_MODEL sections 8 and 9): the decree that appoints or
 * assigns, as a stored file. Added by the migration of the owner of file_objects (DATA_MODEL section 19 rule 5), because the
 * referenced table has to exist first. The owning modules keep the column in step with the links of their rows
 * (TASK-016; Organization and Access contracts).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            alter table org_appointments add column evidence_file_id bigint references file_objects (id);
            alter table process_assignments add column evidence_file_id bigint references file_objects (id);
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            alter table process_assignments drop column if exists evidence_file_id;
            alter table org_appointments drop column if exists evidence_file_id;
            SQL);
    }
};
