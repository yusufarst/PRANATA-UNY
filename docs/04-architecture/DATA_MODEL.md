# Model data PostgreSQL — PRANATA UNY

Status: P4 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P4

Dokumen ini memiliki **skema logis-implementasi PostgreSQL**: konvensi, tabel per modul, kolom, constraint, indeks, partisi, peran basis data dan aturan migrasi. Makna konsep tetap dimiliki P2 ([DOMAIN_GLOSSARY](../02-domain/DOMAIN_GLOSSARY.md), [BUSINESS_RULES](../02-domain/BUSINESS_RULES.md)); keadaan dan transisi dimiliki P3 ([STATE_TRANSITIONS](../03-workflows/STATE_TRANSITIONS.md)). Nilai status di bawah adalah kode teknis yang memetakan state P3; kode itu bukan aturan bisnis baru. Eksekutor membuat migrasi sesuai dokumen ini pada Task yang ditunjuk; perubahan material memerlukan TASK PLAN CHANGE dan, bila menyentuh ADR, persetujuan Owner.

## 1. Konvensi

| Hal | Aturan |
|---|---|
| Skema dan nama | Satu basis data `pranata`, skema `public`. Tabel jamak `snake_case` berprefiks modul (`proc_`, `inv_`, `asset_`, …); tabel kerangka Laravel tanpa prefiks. Indeks `<tabel>_<kolom>_idx`, unik `<tabel>_<kolom>_uq`, check `<tabel>_<aturan>_chk`. |
| Kunci | `id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY`. Akar agregat yang tampil di URL memiliki `public_id char(26) NOT NULL UNIQUE` (ULID dibuat aplikasi); URL tidak pernah memuat `id` internal. |
| Waktu | `timestamptz` disimpan UTC; tampilan Asia/Jakarta. Tanggal bisnis memakai `date`; tahun anggaran `smallint`. |
| Uang dan kuantitas | Nilai total `numeric(20,2)`; harga satuan `numeric(20,4)`; kuantitas `numeric(18,4)`; persentase `numeric(9,4)`. Aritmetika aplikasi memakai desimal presisi (brick/math), tidak pernah float. Aturan pembulatan berasal dari kebijakan. |
| Status | `varchar(40)` + `CHECK` daftar nilai; enum PHP sebagai sumber nilai; tanpa tipe ENUM PostgreSQL. |
| Audit kolom | `created_at`, `updated_at`, `created_by`, `updated_by` (FK `users`) pada tabel yang dapat diubah; `lock_version integer NOT NULL DEFAULT 0` pada agregat yang diedit bersamaan. |
| Penghapusan | Tidak ada hard delete record bisnis dan tidak ada `deleted_at`. Draf yang dibuang berstatus `DISCARDED`/`WITHDRAWN`. Penghapusan fisik hanya kelak menurut kebijakan retensi (GAP-023). FK default `ON DELETE RESTRICT`. |
| JSON | `jsonb` hanya untuk snapshot, konfigurasi, metadata dan bukti struktur yang tidak sering difilter. |
| Teks pencarian | Ekstensi `pg_trgm`; indeks GIN trigram pada nama/kode yang dicari. |
| Ekstensi | `pg_trgm`, `ltree`, `btree_gist`. Tanpa ekstensi berbayar/non-standar. |
| Penandaan kebijakan | Record yang dihasilkan dari aturan berversi menyimpan `policy_version_id` dan `is_simulation boolean NOT NULL DEFAULT false`. |
| Penandaan transisi | Setiap perubahan state menulis `workflow_transitions` dengan `transition_code` TR P3. |

Notasi kolom di tabel bawah: `→ tabel` berarti FK; `uq` unik; `nn` NOT NULL; nilai dalam kurung kurawal adalah daftar `CHECK`.

## 2. Peran basis data dan hak

| Peran | Dipakai oleh | Hak |
|---|---|---|
| `pranata_owner` | Migrasi (koneksi `migrator`) | Pemilik objek; DDL |
| `pranata_app` | Aplikasi web, worker, scheduler | `SELECT, INSERT, UPDATE` pada tabel biasa; **hanya `SELECT, INSERT`** pada tabel append-only (`audit_events`, `workflow_transitions`, `inv_stock_movements`, `asset_transactions`, `proc_contract_versions`, `asset_depreciation_lines`, `vendor_dpt_events`, `org_unit_history`); tanpa `DELETE`/`TRUNCATE` kecuali tabel kerangka (`sessions`, `cache`, `cache_locks`, `jobs`, `failed_jobs`, `job_batches`, `platform_idempotency_keys`, `platform_saved_views`, `intake_rows` sebelum penerimaan) |
| `pranata_readonly` | Laporan berat/analisis kelak | `SELECT` pada tabel yang diizinkan; tanpa tabel kredensial |
| `pranata_backup` | Cadangan fisik/WAL | Replikasi/backup sesuai alat P9 |

Trigger `forbid_update_delete()` pada tabel append-only menolak `UPDATE`/`DELETE` sebagai lapisan kedua. Koreksi dilakukan dengan record pembalik (`reversal_of_id`).

## 3. Tabel kerangka Laravel

`migrations`, `sessions` (driver database; `user_id` terindeks), `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens` (hanya dipakai bila SMTP diaktifkan), `notifications` (notifikasi in-app), tabel `pulse_*` (pemantauan). Struktur mengikuti versi Laravel saat bootstrap.

## 4. Platform

**platform_settings** — `key varchar(100) PK`, `value jsonb nn`, `updated_by → users`, `updated_at`.

**platform_idempotency_keys** — `id`, `user_id → users nn`, `idempotency_key uuid nn`, `route_name varchar(150) nn`, `request_sha256 char(64) nn`, `status {IN_PROGRESS, COMPLETED, FAILED}`, `result jsonb` (subjek/redirect), `created_at`, `expires_at nn`; `uq(user_id, idempotency_key)`; indeks `expires_at`. Dibersihkan scheduler setelah kedaluwarsa.

**platform_saved_views** — `id`, `public_id`, `user_id → users nn`, `list_key varchar(100) nn` (identitas daftar, misalnya `procurement.packages`), `name varchar(120) nn`, `filters jsonb nn` (hanya kunci filter yang ada di whitelist daftar; divalidasi ulang saat dipakai), `sort jsonb`, `columns jsonb`, `is_default boolean nn default false`, `created_at`, `updated_at`; `uq(user_id, list_key, name)`; `uq(user_id, list_key) WHERE is_default`. Milik pribadi pengguna; menyimpan kriteria, **bukan** hasil atau hak akses — hasil selalu dihitung ulang dengan scope akses saat ini. Boleh dihapus pemiliknya (preferensi, bukan record bisnis).

## 5. Identity

**users**

| Kolom | Tipe | Aturan |
|---|---|---|
| id, public_id | bigint, char(26) | konvensi |
| role_group | varchar(20) nn | {SUPER_ADMIN, INTERNAL, VENDOR} |
| username | varchar(60) nn | `uq lower(username)`; pola huruf/angka/titik/garis bawah |
| email | varchar(255) | `uq lower(email) WHERE email IS NOT NULL` |
| name | varchar(150) nn | |
| staff_identifier | varchar(40) | opsional; klasifikasi RESTRICTED (GAP-023) |
| password | varchar(255) nn | hash bawaan framework |
| status | varchar(20) nn | {ACTIVE, SUSPENDED, LOCKED, DEACTIVATED} |
| must_change_password | boolean nn default false | |
| password_changed_at, last_login_at, deactivated_at | timestamptz | |
| locale | varchar(5) nn default 'id' | {id, en} |
| two_factor_secret, two_factor_recovery_codes | text | terenkripsi aplikasi |
| two_factor_confirmed_at | timestamptz | |
| remember_token | varchar(100) | |
| created_at, updated_at, created_by, lock_version | | |

**identity_password_resets** (pemulihan dibantu admin) — `id`, `user_id → users nn`, `channel {ADMIN_ASSISTED, SELF_SERVICE_EMAIL}`, `requested_by → users`, `identity_check_method varchar(60)`, `identity_check_note text`, `temp_secret_hash varchar(255)`, `status {ISSUED, USED, EXPIRED, REVOKED}`, `expires_at nn`, `used_at`, `created_at`. Rahasia sementara hanya ditampilkan sekali; pengguna wajib mengganti password saat masuk.

## 6. Organization

**org_units** — `id`, `public_id`, `code varchar(40) uq nn`, `name varchar(200) nn`, `short_name varchar(80)`, `unit_type varchar(40) nn` (nilai dari referensi; generik, OD-04), `parent_id → org_units`, `path ltree nn`, `depth smallint nn`, `status {ACTIVE, INACTIVE}`, `effective_from date nn`, `effective_to date`, `legacy_codes jsonb`, kolom audit, `lock_version`. Indeks: GIST(`path`), `parent_id`, GIN trigram `name`. `path` dihitung aplikasi saat simpan; perpindahan induk memperbarui subtree dalam satu transaksi.

**org_unit_history** (append-only) — `id`, `org_unit_id → org_units nn`, `change_type {CREATE, RENAME, MOVE, ACTIVATE, DEACTIVATE, CORRECT}`, `before jsonb`, `after jsonb`, `effective_from date nn`, `decision_ref varchar(150)`, `changed_by`, `changed_at`.

**org_positions** (jabatan/fungsi resmi sebagai referensi) — `id`, `code varchar(60) uq` (misalnya `KABAG_ALP`, `KEPALA_GUDANG_ALP`), `name varchar(200)`, `org_unit_id → org_units`, `description text`, `status {ACTIVE, INACTIVE}`.

**org_appointments** — `id`, `user_id → users nn`, `position_id → org_positions nn`, `org_unit_id → org_units`, `effective_from date nn`, `effective_to date`, `decree_ref varchar(150)`, `evidence_file_id → file_objects`, `status {ACTIVE, ENDED}`, kolom audit. Penunjukan menjadi saran penugasan dan nama penanda tangan; **bukan izin** (OD-28).

## 7. Reference

| Tabel | Kolom utama | Catatan |
|---|---|---|
| ref_uoms | `code varchar(20) PK`, `name_id`, `name_en`, `decimals smallint` | Satuan |
| ref_item_categories | `id`, `code uq`, `name`, `parent_id`, `path ltree`, `account_code varchar(40)` (GAP-005/022), `status`, `effective_from/to` | Kategori barang |
| ref_funding_sources | `id`, `code uq`, `name`, `description`, `status` | Pemetaan ke rezim melalui kebijakan (GAP-026), bukan kolom |
| ref_procurement_types | `code PK` {GOODS, CONSTRUCTION, CONSULTING, OTHER_SERVICES}, `name_id`, `name_en` | Label per rezim di kebijakan (jasa lainnya ↔ jasa non konsultansi) |
| ref_locations | `id`, `public_id`, `code uq`, `name`, `building`, `floor`, `room`, `org_unit_id`, `parent_id`, `path ltree`, `status` | Lokasi aset |
| ref_calendar_days | `day date PK`, `is_working_day boolean nn`, `note` | Hari kerja untuk tenggat yang tervalidasi; dipelihara admin |

## 8. Policy

**policy_packs** — `id`, `key varchar(120) uq nn` (misalnya `procurement.regime.pr34-2023`), `family varchar(60) nn` (daftar keluarga di POLICY_REGISTRY), `title`, `description`, `steward_position_id → org_positions`, `created_at`.

**policy_versions**

| Kolom | Tipe | Aturan |
|---|---|---|
| id, public_id | | |
| pack_id | → policy_packs nn | |
| version_no | integer nn | `uq(pack_id, version_no)` |
| status | varchar(20) nn | {DRAFT, IN_REVIEW, ACTIVE, SUPERSEDED, RETIRED, REJECTED} |
| effective_from, effective_to | date | wajib saat ACTIVE |
| schema_version | varchar(20) nn | versi skema JSON keluarga |
| content | jsonb nn | divalidasi JSON Schema keluarga |
| content_sha256 | char(64) nn | |
| source_refs | jsonb nn | `[{"source":"SRC-048","locator":"Pasal 25 ayat (1)"}]` |
| gap_refs | jsonb nn | `["GAP-026"]` |
| resolution_evidence | jsonb | `[{"gap":"GAP-026","decision_ref":"…","file_id":…}]`; wajib lengkap untuk ACTIVE |
| created_by, submitted_by, reviewed_by, activated_by, retired_by | → users (`created_by nn`) | `CHECK (status <> 'ACTIVE' OR activated_by IS NOT NULL)`; `CHECK (activated_by IS NULL OR (activated_by <> created_by AND activated_by IS DISTINCT FROM submitted_by))` — pengesah tidak boleh pembuat atau pengaju, dan tidak lolos bila kosong |
| submitted_at, activated_at, retired_at | timestamptz | |
| notes | text | |
| lock_version | integer | |

`EXCLUDE USING gist (pack_id WITH =, daterange(effective_from, effective_to, '[)') WITH &&) WHERE (status = 'ACTIVE')` — tidak ada dua versi ACTIVE yang berlaku tumpang tindih untuk satu paket.

**policy_test_cases** — `id`, `pack_id → policy_packs nn`, `name`, `input jsonb nn`, `expected jsonb nn`, `source_refs jsonb`, `created_by`. Aktivasi mensyaratkan semua kasus uji lulus terhadap versi kandidat.

## 9. Access

**access_profiles** — `id`, `code varchar(60) uq`, `name`, `description`, `workspace varchar(30)`, `is_privileged boolean nn default false`, `status`.

**access_profile_capabilities** — `profile_id → access_profiles`, `capability varchar(100)`, `PK(profile_id, capability)`. Daftar kapabilitas didefinisikan di kode (ACCESS_CONTROL).

**access_grants** — `id`, `user_id → users nn`, `profile_id → access_profiles`, `capability varchar(100)`, `CHECK (num_nonnulls(profile_id, capability) = 1)`, `scope_type {GLOBAL, ORG_UNIT, ORG_UNIT_SUBTREE, WAREHOUSE, WORKSPACE, POLICY_FAMILY}`, `scope_id bigint`, `scope_key varchar(80)` (kunci keluarga kebijakan untuk `POLICY_FAMILY`), `requires_approval boolean nn` (diisi dari `access_profiles.is_privileged` atau kapabilitas istimewa saat grant dibuat), `effective_from date nn`, `effective_to date`, `reason text nn`, `granted_by → users nn`, `approved_by → users`, `status {PENDING_APPROVAL, ACTIVE, REVOKED, EXPIRED}`, `revoked_by`, `revoked_at`, `created_at`. `CHECK (approved_by IS NULL OR approved_by <> granted_by)`; `CHECK (status <> 'ACTIVE' OR NOT requires_approval OR approved_by IS NOT NULL)`; `CHECK ((scope_type = 'POLICY_FAMILY') = (scope_key IS NOT NULL))`. Indeks `(user_id, status)`, `(scope_type, scope_id)`, `(scope_type, scope_key)`.

**process_assignments** — `id`, `subject_type varchar(80) nn`, `subject_id bigint nn`, `assignment_role varchar(60) nn` (katalog ACCESS_CONTROL), `user_id → users nn`, `effective_from timestamptz nn`, `effective_to timestamptz`, `decree_ref varchar(150)`, `evidence_file_id → file_objects`, `sod_check jsonb nn` (hasil evaluasi aturan pemisahan saat penugasan), `assigned_by → users nn`, `status {ACTIVE, ENDED, REVOKED}`, `created_at`. Indeks `(subject_type, subject_id, assignment_role, status)`, `(user_id, status)`.

## 10. Work dan Workflow

**work_items** — `id`, `public_id`, `module varchar(30) nn`, `subject_type`, `subject_id`, `subject_public_id`, `kind varchar(80) nn`, `state {OPEN, DONE, CANCELLED}`, `owner_user_id → users`, `owner_assignment_role varchar(60)`, `owner_scope_type varchar(30)`, `owner_scope_id bigint`, `waiting_on jsonb`, `next_action_key varchar(120)`, `missing_evidence jsonb`, `blocked_reason_key varchar(120)`, `gap_refs jsonb`, `due_on date` (hanya bila tenggat tervalidasi), `priority smallint default 0`, `created_at`, `closed_at`, `closed_by`. Indeks `(owner_user_id, state)`, `(owner_assignment_role, owner_scope_type, owner_scope_id, state)`, `(subject_type, subject_id)`.

**workflow_transitions** (append-only) — `id`, `subject_type nn`, `subject_id nn`, `workflow_code varchar(10) nn` (misalnya `WF-030`), `transition_code varchar(10) nn` (misalnya `TR-295`), `from_state varchar(40)`, `to_state varchar(40) nn`, `actor_user_id → users`, `acted_at timestamptz nn`, `reason text`, `policy_version_id → policy_versions`, `is_simulation boolean nn`, `metadata jsonb`, `request_id uuid`. Indeks `(subject_type, subject_id, acted_at)`.

## 11. Numbering

**numbering_schemes** — `id`, `code varchar(60) uq`, `document_type varchar(60) nn`, `policy_version_id → policy_versions nn`, `pattern varchar(200) nn` (misalnya `{seq:4}/{unit}/{doc}/{year}`), `scope_keys jsonb nn`, `reset_rule {YEARLY, NEVER}`, `gapless boolean nn`, `status {ACTIVE, RETIRED}`.

**numbering_counters** — `id`, `scheme_id → numbering_schemes nn`, `scope_key varchar(200) nn`, `next_value bigint nn`, `uq(scheme_id, scope_key)`. Dikunci `SELECT … FOR UPDATE` saat alokasi.

**document_numbers** — `id`, `scheme_id nn`, `scope_key nn`, `sequence_value bigint`, `number_text varchar(200) nn`, `status {RESERVED, USED, VOIDED}`, `origin {SUGGESTED, MANUAL_OVERRIDE}`, `subject_type`, `subject_id`, `reserved_by`, `reserved_at`, `confirmed_by`, `confirmed_at`, `voided_by`, `voided_at`, `void_reason`. `uq(scheme_id, number_text)`; `uq(scheme_id, scope_key, sequence_value) WHERE sequence_value IS NOT NULL`. Override manual tetap melalui constraint unik sehingga tabrakan selalu terdeteksi (OD-29).

## 12. Files, Documents dan Audit

**file_objects** — `id`, `public_id`, `disk varchar(30) nn`, `path varchar(500) uq nn`, `original_name varchar(255) nn`, `mime_type varchar(150) nn`, `size_bytes bigint nn`, `sha256 char(64) nn`, `classification {INTERNAL, CONFIDENTIAL, RESTRICTED}`, `scan_status {NOT_SCANNED, CLEAN, INFECTED, ERROR}`, `uploaded_by → users`, `uploaded_at`. Indeks `sha256`.

**file_links** — `id`, `file_id → file_objects nn`, `owner_type nn`, `owner_id nn`, `purpose varchar(60) nn`, `label varchar(200)`, `linked_by`, `linked_at`, `unlinked_by`, `unlinked_at`. Melepas tautan tidak menghapus berkas atau riwayat.

**document_templates** — `id`, `code varchar(80) uq`, `document_type varchar(60) nn`, `regime_key varchar(60)`, `method_code varchar(30)`, `procurement_type varchar(30)`, `title`, `status {ACTIVE, RETIRED}`.

**document_template_versions** — `id`, `template_id nn`, `version_no nn`, `file_id → file_objects nn` (DOCX), `placeholder_schema jsonb nn`, `policy_version_id → policy_versions`, `status {DRAFT, ACTIVE, RETIRED}`, `created_by nn`, `activated_by`, `activated_at`; `uq(template_id, version_no)`; `CHECK (activated_by IS NULL OR activated_by <> created_by)`; `CHECK (status <> 'ACTIVE' OR activated_by IS NOT NULL)`.

**generated_documents** — `id`, `public_id`, `subject_type nn`, `subject_id nn`, `document_type nn`, `template_version_id nn`, `revision_no integer nn`, `data_snapshot jsonb nn`, `data_sha256 char(64) nn`, `number_id → document_numbers`, `status {DRAFT, FINAL, SUPERSEDED, VOIDED}` (siklus isi), `is_simulation boolean nn`, `docx_file_id`, `pdf_file_id`, `generated_by`, `generated_at`, `finalized_by`, `finalized_at`, `issued_by`, `issued_at` (tindakan penerbitan, opsional), `superseded_by_id → generated_documents`, `lock_version`. Indeks `(subject_type, subject_id, document_type)`. Penerbitan dan bukti tanda tangan (`document_signatures`) adalah tindakan terpisah atas dokumen FINAL; wajib/tidaknya dan urutannya per jenis dokumen berasal dari kebijakan `procurement.documents` — tanpa urutan bawaan (STATE_TRANSITIONS WF-028 TR-274). Status tampilan ("final", "final · diterbitkan", "final · ditandatangani", "lengkap") diturunkan dari tindakan yang tercatat.

**document_signatures** — `id`, `generated_document_id nn`, `signer_role varchar(60) nn`, `signer_user_id`, `signer_name_snapshot varchar(150) nn`, `signer_position_snapshot varchar(200)`, `signed_on date nn`, `method {WET_SIGNATURE_SCAN, SIGNED_PDF_UPLOAD}`, `evidence_file_id nn`, `recorded_by`, `recorded_at`.

**document_packs** dan **document_pack_items** — paket SPJ/kelengkapan: `document_packs(id, public_id, subject_type, subject_id, pack_type, policy_version_id, status {COLLECTING, COMPLETE, ASSEMBLED, SUBMITTED, RETURNED, ACCEPTED}, assembled_file_id, …)`; `document_pack_items(pack_id, requirement_code, generated_document_id, file_id, is_required, is_present, position)`.

**audit_events** — berpartisi `RANGE (occurred_at)` bulanan; `id bigint GENERATED ALWAYS AS IDENTITY`, `occurred_at timestamptz nn`, `actor_user_id`, `actor_role_group`, `action varchar(100) nn`, `module varchar(30) nn`, `subject_type`, `subject_id`, `subject_public_id`, `workspace`, `reason text`, `changes jsonb` (field sebelum/sesudah, tanpa rahasia), `metadata jsonb`, `request_id uuid`, `ip inet`, `user_agent varchar(255)`; `PRIMARY KEY (id, occurred_at)`. Indeks per partisi `(subject_type, subject_id, occurred_at DESC)`, `(actor_user_id, occurred_at DESC)`, `(action, occurred_at DESC)`. Partisi dibuat scheduler tiga bulan ke depan dan partisi default menangkap sisa.

## 13. Vendor

| Tabel | Kolom dan aturan |
|---|---|
| **vendor_companies** | `id`, `public_id`, `legal_name nn`, `trade_name`, `legal_form {PT, CV, FIRMA, KOPERASI, PERORANGAN, LAINNYA}`, `tax_number varchar(30)` (RESTRICTED; `uq WHERE NOT NULL`), `business_number varchar(30)`, `address text`, `city`, `province`, `phone`, `email`, `website`, `is_small_business boolean`, `status {DRAFT, SUBMITTED, VERIFIED, INACTIVE, BLOCKED}`, kolom audit, `lock_version`. GIN trigram `legal_name`. |
| **vendor_contacts** | `id`, `company_id nn`, `name nn`, `position`, `phone`, `email`, `is_primary boolean`, `status`. |
| **vendor_user_links** | `id`, `company_id nn`, `user_id uq nn` (role_group VENDOR), `link_role {OWNER, STAFF}`, `status {ACTIVE, REVOKED}`, `linked_by`, `linked_at`. |
| **vendor_documents** | `id`, `company_id nn`, `document_type varchar(60) nn` (katalog dari kebijakan DPT, Lampiran I PR 34/2023), `file_id nn`, `document_number`, `issued_on`, `valid_until`, `verification_status {PENDING, VERIFIED, REJECTED, EXPIRED}`, `verified_by`, `verified_at`, `verification_note`, `supersedes_id`. |
| **vendor_dpt_records** | `id`, `company_id nn`, `regime_key varchar(60) nn`, `status {APPLIED, UNDER_VERIFICATION, REGISTERED, SUSPENDED, EXCLUDED, LAPSED}`, `registered_on`, `last_updated_on`, `excluded_on`, `exclusion_decree_ref`, `reregistration_allowed_on`, `lock_version`; `uq(company_id, regime_key)`. |
| **vendor_dpt_events** (append-only) | `id`, `dpt_record_id nn`, `event_type {APPLY, DOCUMENT_CHECK, FIELD_VERIFICATION, REGISTER, DATA_UPDATE, PERFORMANCE_REVIEW, SUSPEND, EXCLUDE, REINSTATE, LAPSE}`, `decision_ref`, `note`, `actor_user_id`, `occurred_at`. |
| **vendor_performance_reviews** | `id`, `company_id nn`, `contract_id → proc_contracts`, `period_label`, `criteria jsonb` (dari kebijakan), `result jsonb`, `reviewed_by`, `reviewed_at`, `policy_version_id`, `is_simulation`. |

## 14. Procurement

### 14.1 Anggaran dan kebutuhan

| Tabel | Kolom dan aturan |
|---|---|
| **proc_budget_references** | `id`, `public_id`, `fiscal_year nn`, `funding_source_id → ref_funding_sources nn`, `org_unit_id nn`, `budget_document_type {RBA, DIPA, RKA, OTHER}`, `budget_line_code varchar(100)`, `description`, `recorded_ceiling numeric(20,2)`, `source_reference varchar(150)`, `evidence_file_id`, `status {RECORDED, VERIFIED, SUPERSEDED}`, `verified_by`, `verified_at`, `lock_version`. Hanya rujukan; bukan buku keuangan (BR-035). |
| **proc_commitments** | `id`, `budget_reference_id nn`, `subject_type {PACKAGE, CONTRACT, AMENDMENT}`, `subject_id nn`, `amount numeric(20,2) nn CHECK > 0`, `status {PROPOSED, ACTIVE, RELEASED, CONSUMED}`, `released_by`, `release_reason`, `finance_verified_by`, `finance_verified_at`, kolom audit. Peringatan bila jumlah aktif melebihi `recorded_ceiling`; tidak memblokir (GAP-028). |
| **proc_needs** | `id`, `public_id`, `org_unit_id nn`, `fiscal_year nn`, `title nn`, `description`, `estimated_total numeric(20,2)`, `requested_by nn`, `status` (state WF-003), `submitted_at`, kolom audit, `lock_version`. |
| **proc_need_lines** | `id`, `need_id nn`, `line_no nn`, `description nn`, `specification text`, `quantity numeric(18,4) nn`, `uom_code → ref_uoms`, `estimated_unit_price numeric(20,4)`, `estimated_total numeric(20,2)`; `uq(need_id, line_no)`. |
| **proc_rup_references** | `id`, `public_id`, `rup_code varchar(60) nn`, `fiscal_year nn`, `title`, `procurement_type`, `announced_method varchar(30)`, `pagu numeric(20,2)`, `funding_source_id`, `org_unit_id`, `announced_on`, `evidence_file_id`, `status {RECORDED, REVISED, CANCELLED_EXTERNALLY}`, `supersedes_id`; `uq(rup_code, fiscal_year) WHERE status <> 'REVISED'`. Tautan `proc_rup_need_links(rup_reference_id, need_id)`. |

### 14.2 Paket, HPS dan penetapan metode

| Tabel | Kolom dan aturan |
|---|---|
| **proc_packages** | `id`, `public_id`, `package_number varchar(80) uq`, `title nn`, `fiscal_year nn`, `org_unit_id nn`, `procurement_type → ref_procurement_types nn`, `regime_key varchar(60)`, `budget_reference_id`, `pagu_amount numeric(20,2)`, `estimated_value numeric(20,2)`, `hps_total numeric(20,2)` (proyeksi HPS yang ditetapkan), `method_code {PL, QUOTATION, TENDER, PENUNJUKAN_LANGSUNG, E_PURCHASING}` (proyeksi dari penetapan berstatus DETERMINED; null sebelum penetapan), `contract_type varchar(40)`, `is_multi_year boolean nn default false`, `rup_reference_id`, `status` (state WF-005), kolom audit, `lock_version`. Indeks `(fiscal_year, status)`, `org_unit_id`, `method_code`, GIN trigram `title`. |
| **proc_package_items** | `id`, `package_id nn`, `line_no nn`, `description nn`, `specification text`, `quantity nn`, `uom_code`, `unit_estimate numeric(20,4)`, `total_estimate numeric(20,2)`, `item_category_id`, `downstream_hint {ASSET, INVENTORY, KDP, EXPENSE, UNKNOWN}` (saran, bukan klasifikasi resmi); `uq(package_id, line_no)`. |
| **proc_package_need_links** | `package_id`, `need_id`, `need_line_id`; `PK(package_id, need_id, need_line_id)`. |
| **proc_package_step_plans** | `id`, `package_id nn`, `plan_version nn`, `regime_key nn`, `method_code nn`, `procurement_type nn`, `policy_version_id nn` (versi `procurement.documents`/`procurement.methods` yang membangkitkan rencana), `is_simulation nn`, `steps jsonb nn` (urutan langkah: `step_key`, `applicability {REQUIRED, OPTIONAL, NOT_APPLICABLE, CONDITIONAL}`, `condition_note`, `document_type_codes`, `assignment_types`), `generated_at nn`, `generated_by`, `superseded_at`, `supersede_reason`; `uq(package_id, plan_version)`; `uq(package_id) WHERE superseded_at IS NULL`. Dibangkitkan ulang (versi baru) bila metode/jenis/rezim atau versi kebijakan berubah; versi lama tetap untuk jejak. Status pelaksanaan langkah tidak disimpan di sini, melainkan diturunkan dari objek proses terkait (jadwal, dokumen, hasil). |
| **proc_hps** | `id`, `public_id`, `package_id nn`, `version_no nn`, `status {DRAFT, SUBMITTED, RETURNED, SET, SUPERSEDED}`, `basis_note text`, `subtotal numeric(20,2)`, `tax_amount numeric(20,2)`, `total numeric(20,2)`, `rounding_rule varchar(30)`, `prepared_by`, `set_by`, `set_at`, `policy_version_id`, `is_simulation`, `lock_version`; `uq(package_id, version_no)`; `uq(package_id) WHERE status = 'SET'`. |
| **proc_hps_lines** | `id`, `hps_id nn`, `package_item_id`, `line_no nn`, `component_type {ITEM, PERSONNEL, NON_PERSONNEL, OVERHEAD, OTHER}`, `description nn`, `quantity nn`, `uom_code`, `unit_price numeric(20,4) nn`, `line_total numeric(20,2) nn`, `price_source {MARKET_SURVEY, CATALOG, PREVIOUS_CONTRACT, QUOTE, STANDARD_COST, OTHER}`, `source_reference text`, `evidence_file_id`, `detail jsonb` (komponen konsultansi; rumus khusus hanya setelah kebijakan ACTIVE, GAP-019). |
| **proc_method_determinations** | `id`, `public_id`, `package_id nn`, `sequence_no nn`, `status {SUGGESTED, DETERMINED, SUPERSEDED, CANCELLED}`, `regime_key nn`, `policy_version_id nn`, `is_simulation nn`, `basis jsonb nn` (jenis, nilai dasar, tanggal acuan, sumber dana, kriteria kualitatif + bukti), `suggestion jsonb nn` (metode memenuhi syarat + rujukan, tidak memenuhi + alasan, flag tumpang tindih), `determined_method_code`, `deviation_reason text`, `overlap_reason text`, `determined_by`, `determined_at`, `superseded_by_id`, `supersede_reason`, `lock_version`; `uq(package_id, sequence_no)`; `uq(package_id) WHERE status = 'DETERMINED'`; `CHECK (status <> 'DETERMINED' OR determined_by IS NOT NULL)`. |

### 14.3 Pemilihan

| Tabel | Kolom dan aturan |
|---|---|
| **proc_activities** | `id`, `public_id`, `package_id nn`, `activity_type {EXPLANATION, BID_OPENING, EVALUATION, CLARIFICATION, NEGOTIATION, FIELD_REVIEW, ANNOUNCEMENT, DEBRIEFING, OTHER}`, `title`, `scheduled_start timestamptz`, `scheduled_end timestamptz`, `location`, `channel {OFFLINE, ONLINE, EXTERNAL_SYSTEM}`, `external_reference`, `status {PLANNED, HELD, CANCELLED, RESCHEDULED}`, `outcome_summary text`, `minutes_document_id → generated_documents`, kolom audit, `lock_version`. |
| **proc_activity_participants** | `activity_id`, `participant_type {USER, VENDOR_COMPANY, EXTERNAL}`, `user_id`, `company_id`, `name_snapshot`, `role`, `attendance {INVITED, PRESENT, ABSENT}`. |
| **proc_invitations** | `id`, `package_id nn`, `company_id nn`, `invited_on date`, `channel {PBJ_UNY, LETTER, EMAIL, EXTERNAL_SYSTEM, OTHER}`, `external_reference`, `document_id`, `status {INVITED, DECLINED, RESPONDED, NO_RESPONSE}`; `uq(package_id, company_id)`. |
| **proc_participations** | `id`, `public_id`, `package_id nn`, `company_id nn`, `status` (state WF-008), `dpt_check jsonb` (status DPT saat dicek), kolom audit, `lock_version`; `uq(package_id, company_id)`. |
| **proc_submissions** | `id`, `public_id`, `participation_id nn`, `submission_no nn`, `kind {OFFER, REVISION, CLARIFICATION_RESPONSE, QUALIFICATION}`, `channel {PRANATA, EXTERNAL_RECORDED}` (default `EXTERNAL_RECORDED`, GAP-029), `offered_amount numeric(20,2)`, `corrected_amount numeric(20,2)`, `received_at nn`, `received_by`, `receipt_number`, `is_confidential boolean nn default true`, `status {RECEIVED, UNDER_REVIEW, ACCEPTED_FOR_EVALUATION, RETURNED_INCOMPLETE}`, `supersedes_id`; `uq(participation_id, submission_no)`. Berkas penawaran berklasifikasi CONFIDENTIAL. |
| **proc_evaluations** | `id`, `package_id nn`, `participation_id nn`, `stage {ADMINISTRATIVE, TECHNICAL, PRICE, QUALIFICATION, COMBINED}`, `result {PASS, FAIL, NOT_APPLICABLE, PENDING}`, `score numeric(9,4)`, `ranking integer`, `reasons text`, `evaluated_by`, `evaluated_at`, `policy_version_id`, `is_simulation`. |
| **proc_negotiations** | `id`, `package_id nn`, `participation_id nn`, `round_no nn`, `amount_before`, `amount_after`, `agreed boolean`, `notes`, `minutes_document_id`, `held_at`. |
| **proc_results** | `id`, `public_id`, `package_id nn`, `status {DRAFT, PROPOSED, DETERMINED, ANNOUNCED, FAILED, CANCELLED}`, `awarded_amount numeric(20,2)`, `determined_by`, `determined_at`, `determination_basis jsonb` (siapa berwenang menurut nilai/kebijakan), `announcement_reference`, `announced_on`, `failure_reason`, `policy_version_id`, `is_simulation`, `lock_version`; `uq(package_id) WHERE status IN ('DETERMINED','ANNOUNCED')`. |
| **proc_result_rankings** | `result_id`, `participation_id`, `rank`, `role {WINNER, RESERVE_1, RESERVE_2}`; `PK(result_id, participation_id)`. |
| **proc_sppbj** | `id`, `public_id`, `package_id nn`, `result_id nn`, `status {DRAFT, IN_PROGRESS, COMPLETE, CANCELLED}` (diturunkan dari kontribusi), `document_id`, `required_acts jsonb nn` (dari `procurement.authority` saat dibuat), `act_sequence jsonb` (null = tanpa urutan wajib), `guarantee_due_on date`, `policy_version_id`, `is_simulation nn`, `cancel_reason`, kolom audit, `lock_version`. Empat tanggung jawab dipisah (GAP-004); tidak ada urutan bawaan antara pemeriksaan, penerbitan dan penandatanganan (STATE_TRANSITIONS WF-011: TR-102–104 tidak saling menjadi prasyarat universal). |
| **proc_sppbj_acts** | `id`, `sppbj_id nn`, `act_type {PREPARED, CHECKED, ISSUED, SIGNED}`, `actor_user_id → users nn`, `assignment_id → process_assignments`, `acted_on date nn`, `evidence_file_id → file_objects`, `note`, `status {VALID, VOIDED}`, `void_reason`, `recorded_by`, `recorded_at`; `uq(sppbj_id, act_type) WHERE status = 'VALID'`. Urutan hanya ditegakkan bila `act_sequence` terisi; pelaku setiap kontribusi diperiksa terhadap penugasan dan aturan SoD kebijakan. |

### 14.4 Kontrak dan pelaksanaan

| Tabel | Kolom dan aturan |
|---|---|
| **proc_contracts** | `id`, `public_id`, `package_id nn`, `sppbj_id`, `company_id nn`, `contract_form {RECEIPT, NOTA, PURCHASE_ORDER, SPK, CONTRACT, ORDER_LETTER}`, `contract_type {LUMP_SUM, UNIT_PRICE, MIXED, DESIGN_BUILD, PERFORMANCE_BASED, COST_PLUS_FEE, FRAMEWORK, TIME_BASED, TURNKEY, OTHER}`, `number_id → document_numbers`, `signed_on`, `start_on`, `end_on`, `original_value numeric(20,2) nn`, `current_value numeric(20,2) nn`, `current_version_no integer nn default 1`, `is_multi_year`, `payment_scheme {MONTHLY, TERM, LUMP_SUM}`, `retention_percent numeric(9,4)`, `status {DRAFT, UNDER_REVIEW, APPROVED, SIGNED, ACTIVE, COMPLETED, TERMINATED, CANCELLED}`, kolom audit, `lock_version`. |
| **proc_contract_versions** (append-only) | `id`, `contract_id nn`, `version_no nn`, `origin {ORIGINAL, AMENDMENT}`, `amendment_id`, `effective_on nn`, `value numeric(20,2) nn`, `end_on`, `terms_snapshot jsonb nn`, `created_at`; `uq(contract_id, version_no)`. |
| **proc_contract_lines** | `id`, `contract_id nn`, `version_no nn`, `line_no nn`, `package_item_id`, `description`, `quantity`, `uom_code`, `unit_price numeric(20,4)`, `line_total numeric(20,2)`. |
| **proc_guarantees** | `id`, `contract_id nn`, `guarantee_type {PERFORMANCE, MAINTENANCE, ADVANCE_PAYMENT, BID, WARRANTY_CERTIFICATE}`, `issuer`, `guarantee_number`, `amount`, `valid_from`, `valid_until`, `evidence_file_id`, `status {EXPECTED, RECEIVED, VERIFIED, RETURNED, CLAIMED, EXPIRED}`, `verified_by`, `verified_at`. |
| **proc_amendments** | `id`, `public_id`, `contract_id nn`, `sequence_no nn`, `change_types jsonb nn`, `reason text nn`, `before_snapshot jsonb nn`, `after_snapshot jsonb nn`, `value_delta numeric(20,2) nn`, `new_value numeric(20,2) nn`, `new_end_on`, `increase_percent numeric(9,4)`, `exceeds_policy_limit boolean nn`, `exception_required boolean nn`, `policy_version_id`, `is_simulation`, `status` (state WF-033), `decided_by`, `decided_at`, `document_id`, `signed_on`, `effective_on`, `lock_version`; `uq(contract_id, sequence_no)`. |
| **proc_progress_reports** | `id`, `contract_id nn`, `report_date nn`, `period_label`, `physical_progress_percent`, `financial_progress_percent`, `issues text`, `follow_up text`, `status {DRAFT, SUBMITTED, REVIEWED}`, `reported_by`, `reviewed_by`. |
| **proc_inspections** | `id`, `public_id`, `contract_id nn`, `inspection_type {QUALITY, ADMINISTRATIVE, PHO, FHO, GOODS_CHECK}`, `inspected_on`, `result {ACCEPTED, ACCEPTED_WITH_NOTES, REJECTED}`, `notes`, `document_id`, `status {DRAFT, RECORDED, SUPERSEDED}`. |
| **proc_handovers** | `id`, `public_id`, `contract_id nn`, `handover_type {PARTIAL, FINAL, PHO, FHO}`, `handover_date nn`, `document_id`, `status {DRAFT, SIGNED, SUPERSEDED}`, `maintenance_until date`, `downstream_status {PENDING, CLASSIFIED, HANDED_OFF, NOT_APPLICABLE}`, `lock_version`. |
| **proc_handover_lines** | `id`, `handover_id nn`, `contract_line_id`, `description`, `quantity`, `uom_code`, `value numeric(20,2)`, `classification {ASSET, INVENTORY, KDP, EXPENSE, UNCLASSIFIED}` (gate GAP-008/009/018), `downstream_ref_type`, `downstream_ref_id`. |
| **proc_payments** | `id`, `contract_id nn`, `term_no`, `invoice_number`, `invoice_date`, `gross_amount`, `retention_amount`, `penalty_amount`, `tax_amount`, `net_amount`, `status {PLANNED, SUBMITTED, VERIFIED, PAYMENT_RECORDED, REJECTED}`, `payment_reference varchar(100)` (SP2D atau rujukan lain), `paid_on`, `evidence_file_id`, `recorded_by`. Pelacakan saja; tanpa transfer dana. |
| **proc_penalties** | `id`, `contract_id nn`, `penalty_type {LATE}`, `days_late integer`, `rate numeric(12,8)` (dari kebijakan), `base_amount`, `amount`, `policy_version_id`, `is_simulation`, `status {COMPUTED, CONFIRMED, CANCELLED_BY_DECISION}`. |
| **proc_external_references** | `id`, `subject_type nn`, `subject_id nn`, `channel {SIRUP, LPSE, SPSE, E_CATALOG, E_MARKET_UNY, PBJ_UNY, WEBSITE, OTHER}`, `reference_code`, `url`, `recorded_on`, `evidence_file_id`, `note`, kolom audit. |

## 15. Inventory

| Tabel | Kolom dan aturan |
|---|---|
| **inv_warehouses** | `id`, `public_id`, `code varchar(20) uq` (misalnya `ALP`, `PSRT`), `name nn`, `operating_org_unit_id → org_units` (GAP-025), `location text`, `status {PLANNED, ACTIVE, INACTIVE}`, `policy_pack_key varchar(120)`, kolom audit, `lock_version`. |
| **inv_items** | `id`, `public_id`, `code varchar(60) uq`, `name nn`, `description`, `uom_code nn`, `category_id`, `account_code varchar(40)` (GAP-005), `is_active boolean`, `image_file_id`, kolom audit. GIN trigram `name`, `code`. |
| **inv_warehouse_items** | `warehouse_id`, `item_id`, `is_stocked boolean`, `min_quantity numeric(18,4)`, `bin_location varchar(60)`; `PK(warehouse_id, item_id)`. |
| **inv_stock_movements** (ledger append-only) | `id`, `public_id`, `warehouse_id nn`, `item_id nn`, `movement_type {OPENING, RECEIPT, ISSUE, RETURN_IN, ADJUSTMENT_IN, ADJUSTMENT_OUT, TRANSFER_OUT, TRANSFER_IN, CORRECTION_IN, CORRECTION_OUT}`, `quantity numeric(18,4) nn CHECK > 0`, `direction smallint nn CHECK IN (-1, 1)`, `unit_cost numeric(20,4)`, `total_cost numeric(20,2)`, `occurred_at nn` (waktu fisik), `posted_at nn default now()`, `posted_by`, `source_type nn`, `source_id nn`, `source_line_id`, `reversal_of_id`, `raw_code varchar(20)` (kode historis apa adanya, INV-010), `balance_after numeric(18,4) nn` (saldo menurut urutan posting `id`, untuk pemeriksaan integritas), `policy_version_id`, `is_simulation boolean nn default false`, `request_id uuid`, `note`. Indeks `(warehouse_id, item_id, occurred_at, id)`, `(source_type, source_id)`, BRIN `posted_at`. Kartu stok diurutkan `(occurred_at, id)` dengan saldo berjalan dihitung saat kueri. Gerakan dengan `occurred_at` lebih awal dari gerakan terakhir item–gudang (pencatatan terlambat) hanya diterima bila saldo menurut `occurred_at` pada titik itu dan setiap titik sesudahnya tetap ≥ 0, diperiksa di bawah kunci saldo yang sama; OPENING hanya sebelum gerakan lain item–gudang. |
| **inv_stock_balances** | `warehouse_id`, `item_id`, `quantity numeric(18,4) nn CHECK (quantity >= 0)` (INV-020), `last_movement_id`, `updated_at`; `PK(warehouse_id, item_id)`. Diperbarui dalam transaksi yang sama dengan movement, dengan `SELECT … FOR UPDATE` berurutan (P6). |
| **inv_requests** | `id`, `public_id`, `request_number varchar(80) uq`, `warehouse_id nn`, `request_type varchar(40) nn` (dari kebijakan gudang), `requester_user_id nn`, `requesting_org_unit_id nn`, `charge_org_unit_id`, `purpose text nn`, `status {DRAFT, SUBMITTED, RETURNED, APPROVED, PARTIALLY_HANDED_OVER, AWAITING_CONFIRMATION, COMPLETED, REJECTED, WITHDRAWN, CLOSED_NO_HANDOVER}` (state WF-030), `submitted_at`, `decision_cycle smallint nn default 1`, `current_step_no smallint`, `final_decided_at`, `policy_version_id`, `is_simulation`, kolom audit, `lock_version`. Indeks `(warehouse_id, status)`, `(requester_user_id, status)`. |
| **inv_request_decisions** | `id`, `request_id nn`, `decision_cycle smallint nn`, `step_no smallint nn`, `assignment_role varchar(60) nn`, `decided_by → users nn`, `decision {APPROVED, PARTIALLY_APPROVED, REJECTED, RETURNED}`, `reason text`, `line_quantities jsonb nn` (jumlah disetujui per baris; tidak boleh melebihi langkah sebelumnya), `policy_version_id`, `is_simulation nn`, `decided_at nn`; `uq(request_id, decision_cycle, step_no)`. Satu record per langkah rantai `warehouse.operations` (TR-293 berulang per langkah); permintaan APPROVED setelah langkah terakhir; RETURNED memulai siklus baru tanpa menghapus keputusan lama; `inv_request_lines.approved_qty` adalah proyeksi langkah terakhir. |
| **inv_request_lines** | `id`, `request_id nn`, `line_no nn`, `item_id nn`, `requested_qty nn CHECK > 0`, `approved_qty`, `handed_over_qty nn default 0`, `closed_qty nn default 0`, `close_reason`; `CHECK (approved_qty IS NULL OR handed_over_qty + closed_qty <= approved_qty)`. |
| **inv_handovers** | `id`, `public_id`, `handover_number uq`, `request_id nn`, `warehouse_id nn`, `handed_over_by nn`, `recipient_type {USER, EMPLOYEE_NO_ACCOUNT, ORG_UNIT}`, `recipient_user_id`, `recipient_name_snapshot varchar(150)`, `recipient_org_unit_id`, `handed_over_at nn`, `evidence_file_id`, `confirmation_status {PENDING, CONFIRMED, DISPUTED, CLOSED_WITHOUT_CONFIRMATION}`, `confirmed_by`, `confirmed_at`, `dispute_note`. |
| **inv_handover_lines** | `id`, `handover_id nn`, `request_line_id nn`, `item_id nn`, `quantity nn CHECK > 0`, `stock_movement_id nn`. |
| **inv_returns** | `id`, `public_id`, `return_number uq`, `warehouse_id nn`, `returned_by_user_id nn`, `org_unit_id`, `original_handover_id`, `reason text nn`, `declared_condition varchar(30)`, `status {DRAFT, SUBMITTED, RECEIVED_FOR_INSPECTION, ACCEPTED_USABLE, ACCEPTED_UNUSABLE, PARTIALLY_USABLE, REJECTED, WITHDRAWN}` (state WF-031), `received_by`, `received_at`, `inspected_by`, `inspected_at`, `inspection_note`, `policy_version_id`, `lock_version`. |
| **inv_return_lines** | `id`, `return_id nn`, `item_id nn`, `declared_qty nn`, `received_qty`, `usable_qty`, `unusable_qty`, `stock_movement_id`; `CHECK (usable_qty IS NULL OR usable_qty + coalesce(unusable_qty,0) <= received_qty)`. |
| **inv_receipts** | `id`, `public_id`, `receipt_number uq`, `warehouse_id nn`, `source_kind {PROCUREMENT_HANDOVER, PAYMENT_DOCUMENT, DONATION, OPENING, OTHER}`, `source_reference`, `received_on nn`, `received_by`, `status {DRAFT, VERIFIED, POSTED, CANCELLED}`, `evidence_file_id`, `lock_version`; baris `inv_receipt_lines(id, receipt_id, item_id, quantity, unit_cost, stock_movement_id)`. |
| **inv_counts** | `id`, `public_id`, `warehouse_id nn`, `count_date nn`, `scope {FULL, PARTIAL}`, `status {PLANNED, COUNTING, SUBMITTED, REVIEWED, ADJUSTMENT_PROPOSED, CLOSED}`, `counted_by`, `reviewed_by`; baris `inv_count_lines(id, count_id, item_id, system_qty_snapshot, counted_qty, difference, explanation, adjustment_movement_id)`. Hitung tidak otomatis menjadi penyesuaian (WF-023). |
| **inv_transfers** | `id`, `public_id`, `from_warehouse_id nn`, `to_warehouse_id nn CHECK <> from`, `status {DRAFT, DISPATCHED, RECEIVED, DISCREPANCY, CANCELLED}`, `dispatched_at`, `received_at`, kolom audit; baris `inv_transfer_lines(id, transfer_id, item_id, qty_dispatched, qty_received, out_movement_id, in_movement_id)`. Kewenangan transfer GAP-025. |

## 16. Asset

| Tabel | Kolom dan aturan |
|---|---|
| **asset_classification_versions** | `id`, `code varchar(60) uq`, `title`, `effective_from nn`, `effective_to`, `status {DRAFT, ACTIVE, SUPERSEDED}`, `source_refs jsonb`, `activated_by` (≠ pembuat). |
| **asset_classification_nodes** | `id`, `version_id nn`, `code varchar(60) nn`, `name nn`, `parent_id`, `level smallint`, `account_code varchar(40)` (GAP-022), `uq(version_id, code)`. Parameter penyusutan/kapitalisasi tidak disimpan di sini; berada di kebijakan menurut kode klasifikasi. |
| **asset_assets** | `id`, `public_id`, `asset_number varchar(80) uq nn`, `legacy_identifiers jsonb`, `classification_node_id nn`, `name nn`, `description`, `brand`, `model`, `serial_number`, `specification text`, `acquisition_type {PURCHASE, DONATION, TRANSFER_IN, CONSTRUCTION_COMPLETION, FOUND, OTHER}`, `acquisition_date`, `acquisition_source jsonb` (kontrak/BAST/dokumen), `acquisition_value numeric(20,2)`, `capitalized_value numeric(20,2)`, `condition_code varchar(30)`, `status {DRAFT, REGISTERED, IN_USE, IDLE, UNDER_REPAIR, PROPOSED_FOR_DISPOSAL, DISPOSED, LOST}`, `org_unit_id nn`, `location_id`, `custodian_user_id`, `custodian_name_snapshot`, `recognition_status {NOT_RECOGNIZED, PENDING_POLICY, RECOGNIZED}`, `recognized_on`, `depreciation_start_period date`, `accumulated_depreciation numeric(20,2) nn default 0`, `book_value numeric(20,2)`, `valuation_is_simulation boolean nn default false`, `last_verified_on`, kolom audit, `lock_version`. Indeks: `(org_unit_id, status, id)`, `(classification_node_id, id)`, `(location_id)`, `(custodian_user_id)`, `(acquisition_date)`, GIN trigram `name`, `asset_number`, `serial_number`. |
| **asset_transactions** (append-only) | `id`, `asset_id nn`, `transaction_type {REGISTER, RECOGNIZE, MOVE_LOCATION, CHANGE_CUSTODIAN, CHANGE_UNIT, CONDITION_UPDATE, CORRECTION, DEVELOPMENT, RECLASSIFICATION, DISPOSAL, IMPORT, REVERSAL}`, `effective_date nn`, `posted_at nn`, `posted_by`, `before jsonb`, `after jsonb`, `value_delta numeric(20,2)`, `source_type`, `source_id`, `document_id`, `decision_ref`, `reason`, `policy_version_id`, `is_simulation`, `reversal_of_id`. Indeks `(asset_id, effective_date, id)`, BRIN `posted_at`. |
| **asset_mutations** + **asset_mutation_lines** | Usulan perpindahan WF-018: `asset_mutations(id, public_id, status, requested_by, decided_by, effective_date, document_id, reason, lock_version)`; `asset_mutation_lines(mutation_id, asset_id, from_org_unit_id, to_org_unit_id, from_location_id, to_location_id, from_custodian_user_id, to_custodian_user_id)`. |
| **asset_verifications** + **asset_verification_lines** | Inventarisasi WF-019: kepala `(id, public_id, scope jsonb, period_label, status, opened_by, closed_by)`; baris `(verification_id, asset_id, found boolean, observed_condition_code, observed_location_id, photo_file_id, note, verified_by, verified_at)`. |
| **asset_maintenance_records** | `id`, `asset_id nn`, `maintenance_type`, `performed_on`, `vendor_company_id`, `cost numeric(20,2)`, `description`, `may_be_development boolean` (memicu telaah WF-020, bukan otomatis), kolom audit. |
| **asset_change_requests** | WF-020: `id`, `public_id`, `asset_id nn`, `change_type {CORRECTION, DEVELOPMENT, RECLASSIFICATION}`, `before jsonb nn`, `after jsonb nn`, `reason nn`, `status`, `decided_by`, `effective_date`, `policy_version_id`, `is_simulation`, `lock_version`. |
| **asset_disposals** + **asset_disposal_lines** | WF-021: kepala `(id, public_id, proposal_number uq, status, reason, disposal_method {AUCTION, DESTRUCTION, TRANSFER, GRANT, OTHER}, decree_ref, decided_on, lock_version)`; baris `(disposal_id, asset_id, book_value_snapshot, condition_snapshot, appraisal_value)`. |
| **asset_depreciation_runs** | `id`, `public_id`, `period date nn` (awal bulan), `policy_version_id nn`, `is_simulation nn`, `status {QUEUED, RUNNING, COMPLETED, FAILED, SUPERSEDED}`, `scope jsonb`, `asset_count`, `total_amount numeric(20,2)`, `requested_by`, `started_at`, `finished_at`, `error`, `projection_applied_at`. `uq(period) WHERE is_simulation = false AND status IN ('QUEUED', 'RUNNING', 'COMPLETED')` — run resmi kedua ditolak saat dibuat. Baris ditulis per chunk; proyeksi nilai aset diperbarui hanya pada tahap penyelesaian dan dapat dilanjutkan bila gagal (idempoten terhadap baris yang sudah ada). |
| **asset_depreciation_lines** (append-only) | Berpartisi `LIST (fiscal_year)`: `fiscal_year smallint nn`, `run_id nn`, `asset_id nn`, `period date nn`, `method varchar(30) nn`, `amount numeric(20,2) nn`, `accumulated_after numeric(20,2) nn`, `book_value_after numeric(20,2) nn`, `is_simulation boolean nn`; `PRIMARY KEY (fiscal_year, run_id, asset_id)`; `uq(fiscal_year, asset_id, period) WHERE is_simulation = false`. Proyeksi `asset_assets.accumulated_depreciation/book_value` diperbarui hanya oleh run non-simulasi pada tahap penyelesaian. |
| **asset_kdp_projects** | WF-024: `id`, `public_id`, `name nn`, `org_unit_id`, `status {IN_PROGRESS, COMPLETION_CANDIDATE, COMPLETED, CANCELLED}`, `accumulated_cost numeric(20,2) nn default 0`, `completion_check jsonb` (serah terima terverifikasi, siap pakai, persetujuan, telaah keuangan — OD-30(4)), `completed_on`, `lock_version`; `asset_kdp_cost_entries(kdp_id, source_type, source_id, amount, recorded_on)`; `asset_kdp_results(kdp_id, asset_id)`. |

## 17. Reconciliation, Intake dan Reporting

| Tabel | Kolom dan aturan |
|---|---|
| **recon_cases** | `id`, `public_id`, `case_type {ASSET_SUBUNIT, INVENTORY_COUNT, INVENTORY_TRANSFER, PROCUREMENT_ASSET, FINANCE_ASSET, OTHER}`, `scope jsonb nn` (unit/gudang/periode/as-of), `status` (state WF-025), `owner_user_id`, `opened_by`, `opened_at`, `accepted_by`, `accepted_at` (GAP-010), `summary`, `lock_version`. |
| **recon_differences** | `id`, `case_id nn`, `left_ref jsonb`, `right_ref jsonb`, `difference_type`, `quantity_delta`, `value_delta`, `explanation`, `resolution {UNRESOLVED, EXPLAINED, CORRECTION_LINKED, ADJUSTMENT_PROPOSED}`, `linked_ref jsonb`, `resolved_by`, `resolved_at`. |
| **intake_batches** | `id`, `public_id`, `target {ASSET, INVENTORY_OPENING, INVENTORY_MOVEMENT, ITEM, VENDOR, ORG_UNIT, PROCUREMENT_HISTORY}`, `source_file_id nn`, `source_system {SIMASET, INVENTORY_APP, SPREADSHEET, OTHER}`, `source_description`, `mapping_profile jsonb nn`, `status {UPLOADED, PARSING, PARSED, VALIDATING, VALIDATED, DECISIONS_PENDING, ACCEPTING, ACCEPTED, PARTIALLY_ACCEPTED, REJECTED, FAILED}`, hitungan baris, `policy_version_id`, `created_by`, `decided_by`. Satu batch aktif per target pada satu waktu (P6). |
| **intake_rows** | `id`, `batch_id nn`, `row_number integer nn`, `raw jsonb nn` (nilai asli apa adanya), `normalized jsonb`, `status {PENDING, VALID, INVALID, DUPLICATE_CANDIDATE, AMBIGUOUS, ACCEPTED, REJECTED, SKIPPED}`, `target_ref_type`, `target_ref_id`, `decided_by`, `decision_reason`; `uq(batch_id, row_number)`; indeks `(batch_id, status)`. Kredensial dalam sumber tidak pernah dibawa ke `users` (INV-016). |
| **intake_issues** | `id`, `batch_id nn`, `row_id`, `severity {ERROR, WARNING, INFO}`, `code nn`, `field`, `message_key nn`, `details jsonb`. |
| **report_requests** | `id`, `public_id`, `report_code nn`, `parameters jsonb nn`, `time_semantics {AS_OF, RANGE, PERIODIC, RECONCILIATION}`, `output_format {XLSX, CSV, PDF}`, `status {QUEUED, RUNNING, COMPLETED, FAILED, EXPIRED}`, `output_file_id`, `row_count`, `policy_version_ids jsonb`, `is_simulation`, `requested_by nn`, `requested_at`, `started_at`, `finished_at`, `error_code`. |

## 18. Volume dan strategi partisi

| Tabel | Perkiraan volume | Strategi |
|---|---|---|
| asset_assets | ~1.000.000 | Indeks komposit sesuai filter + keyset `(…, id)`; tanpa partisi |
| asset_transactions | jutaan bertambah per tahun | Indeks `(asset_id, effective_date, id)`; BRIN `posted_at`; partisi bila >50 juta (keputusan P6/P9) |
| asset_depreciation_lines | hingga ~12 juta per tahun | Partisi LIST per tahun anggaran |
| audit_events | puluhan juta per tahun | Partisi bulanan; retensi menurut GAP-023 |
| inv_stock_movements | puluhan ribu per tahun | Indeks biasa |
| intake_rows | sampai jutaan per batch besar | Indeks per batch; pembersihan staging menurut retensi |
| Lainnya | ribuan–ratusan ribu | Indeks biasa |

## 19. Aturan migrasi

1. Setiap perubahan skema lewat migrasi Laravel berurutan, dapat direview, dijalankan dengan koneksi `migrator`; aplikasi memakai `pranata_app`.
2. Pola expand–contract untuk perubahan pada tabel berisi data; tidak ada perubahan destruktif tanpa cadangan terverifikasi, rencana rollback dan persetujuan rilis (P10).
3. Transformasi data dilakukan oleh command terpisah yang idempoten, dapat dijalankan ulang dan diuji pada salinan sintetis; tidak pernah oleh agen terhadap produksi ([PRODUCTION_DATA_SAFETY](../00-governance/PRODUCTION_DATA_SAFETY.md)).
4. Uji migrasi di CI: `migrate:fresh` penuh, `migrate:rollback` untuk migrasi baru yang reversibel, dan pemeriksaan hak peran `pranata_app` pada tabel append-only.
5. Kolom FK ke tabel yang dibuat oleh Task kemudian ditambahkan oleh migrasi Task pemilik tabel tujuan, bukan dibuat lebih awal tanpa FK: `evidence_file_id` pada `org_appointments` dan `process_assignments` ditambahkan saat `file_objects` dibuat.
6. Seeder hanya memuat data referensi teknis, kebijakan **DRAFT** beserta rujukan sumber, dan data sintetis untuk non-produksi; tidak ada data pribadi atau data transaksi riil.

## 20. Pemetaan ke P2/P3

| Konsep P2 | Tabel utama |
|---|---|
| DC-001/005/007 | org_units, process_assignments, access_grants |
| DC-010–018 | file_objects, file_links, generated_documents, document_signatures, workflow_transitions, audit_events |
| DC-020/080 | policy_packs, policy_versions |
| DC-021–027/081/082 | proc_needs, proc_rup_references, proc_packages, proc_hps, proc_method_determinations, proc_budget_references, proc_commitments |
| DC-028–035/087 | vendor_*, proc_participations, proc_submissions |
| DC-036–048/083 | proc_evaluations, proc_negotiations, proc_results, proc_sppbj, proc_sppbj_acts, proc_contracts, proc_contract_versions, proc_amendments, proc_progress_reports, proc_inspections, proc_handovers, document_packs, proc_payments |
| DC-049/050 | proc_handover_lines (klasifikasi), draf di modul tujuan |
| DC-051–064/078 | asset_* |
| DC-065–069/079/084–086 | inv_* |
| DC-070 | asset_kdp_* |
| DC-071–076 | recon_*, intake_*, report_requests |
| DC-077 | kolom `status` + workflow_transitions |
