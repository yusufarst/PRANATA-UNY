# Handoff Second Review terfokus — PCR-002 (pengembangan tanpa Docker lokal)

Status: PCR-002 — lihat [APPROVAL_RECORDS PCR-002](../00-governance/APPROVAL_RECORDS.md#pcr-002) | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT)

Review ini **terfokus dan proporsional** (FRZ-001, GOV-11): hanya delta PCR-002 di atas `main` yang sudah terbit. Baseline P0–P11, PCR-001 dan AUTH-013 tidak direview ulang.

## 0. Koreksi atas review `793ad80` (REVISE)

Review terfokus atas `793ad801d724e7a202cf911dba9d385d5df9ef4a` memberi verdict **REVISE** dengan tiga temuan; bagian lain PCR-002 dinilai dapat diterima. Satu commit koreksi di atas `793ad80`; review delta hanya memeriksa ketiga koreksi ini (`git diff 793ad80..<SHA HEAD>`).

| Temuan | Koreksi | Tempat memeriksa |
|---|---|---|
| PCR002-SR-P1-01 Bukti merge bootstrap tidak terkait pasti dengan HEAD PR yang diuji | Prosedur fail-closed: run dicari lewat API Actions untuk PR dan SHA HEAD (run dan attempt terbaru); himpunan job wajib per Task; semua job `success`; job `bootstrap-gate` mengunggah `bootstrap-evidence` (nomor PR, SHA HEAD, SHA base, SHA uji-merge sintetis, tree); gerbang mencocokkan HEAD saat ini, `main` saat ini dan tree HEAD. Run hilang, basi, gagal, dibatalkan, dilewati atau konflik ditolak. Kemampuan baru C-16 wajib VERIFIED pada PR uji sekali pakai sebelum merge TASK-002. `pull_request` dan permission baca-saja tetap. | [QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara); [EXECUTOR_OPERATING_MODEL §8.4, §13 C-16](../11-tasks/EXECUTOR_OPERATING_MODEL.md#84-mode-bootstrap-sebelum-ci-ada); [TASK-002](../11-tasks/TASK-002.md) IN SCOPE, TAC-06, TEST REQUIREMENTS |
| PCR002-SR-P2-01 `.gitattributes` starter kit bertentangan dengan GOV-09 | `.gitattributes` bawaan starter kit tidak di-stage atau di-commit; impor memakai `stage.ps1` dan pemeriksaan byte GOV-09; bila diperlukan, keputusan Owner terpisah dengan diff terbatas. Baris risiko diperbaiki. | [TASK-002](../11-tasks/TASK-002.md) IN SCOPE dan DO NOT DO; bagian 5 di bawah |
| PCR002-SR-P2-02 Pemeriksaan PR-wajib dan nightly tidak dibedakan | Tabel klasifikasi per pemeriksaan bootstrap: tujuan dan kelas gerbang. Semua pemeriksaan kebenaran dan integritas tetap PR-wajib di `ci-fast`/`ci-e2e`; smoke Gotenberg dan Pest grup `gotenberg` di `ci-e2e` (wajib bila terpicu, termasuk setiap perubahan Gotenberg); hanya integrasi Gotenberg mendalam di `ci-nightly`, dinyatakan tidak dicakup `ci-gate`. | [QUALITY_GATES §4 dan §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara); [TASK-005](../11-tasks/TASK-005.md) IN SCOPE, TAC-06; [DOCUMENT_ENGINE](../04-architecture/DOCUMENT_ENGINE.md) |

Tidak berubah: 78 Task, dependensi, keputusan OD-37, izin jalur pengaman, prosedur paritas TASK-071, perlakuan D-19/C-14/C-15 dan semua bagian lain yang sudah diterima.

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Base (`origin/main`, terverifikasi) | `ef21d09074a0e08dd4054dcdabd2f9caf566e721` (TASK-001 DONE) |
| Head | SHA HEAD cabang `docs/pcr-002-docker-free-development` yang dilaporkan bersama permintaan ini: `793ad80` ditambah satu commit koreksi; diff penuh `git diff ef21d09..<SHA HEAD>` |
| Keputusan Owner | OD-37 dari U-013 (2026-11-01): tanpa Docker/Compose, basis data, layanan atau runner Actions self-hosted untuk PRANATA di PC Owner; runner GitHub-hosted sebagai lingkungan utama uji berat; PostgreSQL dan Gotenberg boleh sebagai service container GitHub-hosted; tanpa perubahan global PHP/Composer/PostgreSQL/PATH/variabel lingkungan/layanan; tanpa layanan berbayar; tanpa data privat di GitHub publik |
| Masukan utama | Usulan TASK PLAN CHANGE eksekutor di [CURRENT_HANDOFF](CURRENT_HANDOFF.md) (kini ditandai INCORPORATED) |
| Keadaan | Belum diterima dan belum terbit. TASK-002 dan TASK-003 BLOCKED (gerbang publikasi PCR-002). |

## 2. Inti perubahan

1. **Lingkaran bootstrap diputus.** Workflow sementara `.github/workflows/bootstrap.yml` ([QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)) dibuat TASK-002, diperluas TASK-003 dan TASK-004, lalu digabung ke `ci.yml` dan dihapus TASK-005. Pemicunya `pull_request`, jadi run berjalan dari PR draft tanpa workflow di `main` lebih dahulu.
2. **TASK-002 tanpa PHP lokal.** Job sementara `scaffold` membuat aplikasi dari starter kit di runner dan mengunggah artefak sumber + lockfile. Eksekutor mengunduh, meninjau dan meng-commit dengan identitas Owner; workflow tidak pernah commit atau push. Job `app-boot` membuktikan boot, `migrate:fresh` + rollback, `GET /login` 200, registrasi 404, Pest, Pint, ESLint, tsc dan screenshot dua viewport. PHP/Composer portabel lingkup proyek hanya jalan cadangan.
3. **TASK-003 tanpa Compose.** Job `db-integration`: major PostgreSQL yang dipatok, ekstensi `pg_trgm`/`ltree`/`btree_gist`, `numeric`/`timestamptz`, Pest paralel, DatabaseGuard, health dan konversi Gotenberg. Pengaman eksekutor diperketat: klien/URL basis data lokal ditolak.
4. **TASK-004** membuktikan `composer qa`/`npm run qa` dan uji negatif di job `quality`. **TASK-005** memetakan setiap pemeriksaan bootstrap ke `ci.yml`/`ci-nightly.yml`, menghapus `bootstrap.yml` (TAC-06 baru) dan mempertahankan `ci-gate`, cache dan ruleset.
5. **Gerbang merge bootstrap** fail-closed (QUALITY_GATES §4.1): run terbaru untuk PR dan SHA HEAD saat ini, semua job `success`, SHA HEAD/base/tree uji-merge cocok, ditambah review dan kesaksian Owner. Skrip merge `-Bootstrap` diperluas TASK-002 dan dibuktikan sebagai C-16 pada PR uji sekali pakai.
6. **Izin jalur pengaman** dipersempit per Task, hanya SUPERVISED (EXECUTOR_OPERATING_MODEL §5): TASK-002 `bootstrap.yml` + gerbang `-Bootstrap` skrip merge dan ujinya; TASK-003 `bootstrap.yml` + aturan basis data lokal di `policy.ps1` dan uji guard (hanya memperketat); TASK-004 `bootstrap.yml`. Larangan mode otonom tidak berubah.
7. **Paritas BMU TASK-071**: oracle TypeScript murni (Node saja). CI publik membandingkan V1 dengan golden output sintetis. Paritas privat di PC Owner memakai CSV dan ekspor XLSX/teks PDF di `.pranata/private/parity/`. Yang dicatat hanya lulus/gagal, kategori selisih dan profil pola tanpa nilai, dan setiap kategori pola riil wajib punya kasus sintetis.
8. **C-14 SUPERSEDED / NOT APPLICABLE** (tidak VERIFIED). C-15 tetap prasyarat Owner kemudian; runner headless Claude Code lokal ≠ runner Actions self-hosted.
9. **D-19**: staging Docker Compose tidak boleh di PC Owner. Lokasi staging dikonfirmasi Owner sebelum TASK-040, yang memang sudah mensyaratkan keputusan server staging. Tidak ada keputusan yang dikarang.

## 3. Berkas berubah (28)

| Kelompok | Berkas |
|---|---|
| Kontrak Task | `docs/11-tasks/TASK-002.md`, `TASK-003.md`, `TASK-004.md`, `TASK-005.md`, `TASK-071.md` |
| Model eksekusi | `docs/11-tasks/EXECUTOR_OPERATING_MODEL.md` (§2, §5, §7, §8.4, §9.1, §10, §11, §13), `EXECUTOR_BOOTSTRAP.md` (§3, §4), `EXECUTION_CONTEXT.md` (§5), `evidence/EXECUTOR_CAPABILITY_REPORT.md` (baris C-14) |
| Uji, operasi, perkakas | `docs/08-testing/QUALITY_GATES.md` (§2.1, §4, §4.1 baru), `docs/08-testing/TEST_STRATEGY.md` (§4, §8), `docs/09-operations/INFRASTRUCTURE.md` (§1–§4), `docs/00-governance/TOOLCHAIN.md` (bagian baru) |
| Dokumen/arsitektur | `docs/04-architecture/DOCUMENT_ENGINE.md`, `docs/adr/ADR-0005-document-generation.md`, `docs/04-architecture/ARCHITECTURE.md` (satu baris risiko), `docs/06-api-performance/CONCURRENCY_AND_INTEGRITY.md` (§8) |
| Bukti legacy | `docs/00-governance/evidence/LEGACY_REPORTING_EVIDENCE.md` (§10, status, satu baris §1) |
| Tata kelola | `docs/00-governance/DECISION_LOG.md` (OD-37, tambahan di akhir), `DECISION_INDEX.md`, `APPROVAL_RECORDS.md` (PCR-002, tambahan di akhir), `SOURCE_INVENTORY.md` (U-013) |
| Status dan handoff | `docs/11-tasks/TASK_PLAN.md`, `docs/PHASE_STATUS.md`, `AGENTS.md`, `docs/handoff/CURRENT_HANDOFF.md`, `docs/handoff/OWNER_DECISION_PACKAGE.md` (catatan D-19), `docs/handoff/PCR-002_REVIEW_HANDOFF.md` (baru) |

Tidak berubah: berkas di `.github/`, `.claude/`, `.githooks/`, `scripts/` (perubahan pengaman dikerjakan Task dalam mode SUPERVISED, bukan oleh PCR ini), kata-kata P0–P3 yang disetujui, 78 ID Task dan seluruh DEPENDS ON.

## 4. Pemetaan kriteria penerimaan (direktif U-013)

| # | Syarat Owner | Dipenuhi di |
|---|---|---|
| 1 | Tanpa Docker/Compose lokal; tidak mengganggu Docker, WSL atau proyek lain | OD-37; INFRASTRUCTURE §2; EXECUTOR_OPERATING_MODEL §10, §11; TASK-003 TAC-01 dan DO NOT DO |
| 2 | Tanpa runner Actions self-hosted | OD-37; EXECUTOR_OPERATING_MODEL §9.1, §11; QUALITY_GATES §4, §4.1; INFRASTRUCTURE §2; TASK-005 |
| 3 | GitHub-hosted sebagai lingkungan uji utama; PostgreSQL dan Gotenberg service container | QUALITY_GATES §4.1; TEST_STRATEGY §4, §8; TASK-002/003/004 |
| 4 | Lokal hanya alat ringan; tanpa perubahan global/PATH/variabel lingkungan/layanan | TOOLCHAIN (bagian baru); INFRASTRUCTURE §2; EXECUTOR_OPERATING_MODEL §11 baris Sistem; DO NOT DO TASK-002–004 |
| 5 | Tanpa layanan berbayar | INFRASTRUCTURE §2 (Biaya); TOOLCHAIN; gitleaks tanpa lisensi berbayar (TASK-004); TASK-005 |
| 6 | Data institusi/berkas privat/kredensial tidak ke GitHub publik | QUALITY_GATES §4 (keamanan workflow, artefak publik); LEGACY_REPORTING_EVIDENCE §10; TASK-071 TAC-08 |
| 7 | Laravel, React, PostgreSQL, modular monolith, gerbang kebijakan, kualitas dan keamanan tetap | ARCHITECTURE stack tidak berubah; TASK-003 TAC-03; QUALITY_GATES §2.1 |
| 8 | TASK-002 tanpa PHP global; bukti boot dan halaman masuk tetap | TASK-002 IN SCOPE, TAC-01, TAC-06 |
| 9 | TASK-003 tanpa Docker; PostgreSQL GitHub-hosted; guard dan semantik PostgreSQL tetap | TASK-003 OBJECTIVE, IN SCOPE, TAC-01–TAC-05 |
| 10 | TASK-004 di GitHub-hosted dengan uji positif dan negatif | TASK-004 TAC-01–TAC-03 |
| 11 | TASK-005 konsolidasi ke `ci.yml`, `ci-gate`, cache, proteksi cabang | TASK-005 IN SCOPE, TAC-01–TAC-06 |
| 12 | Workflow bootstrap sempit; izin jalur pengaman presisi tanpa melemahkan mode otonom | QUALITY_GATES §4.1; EXECUTOR_OPERATING_MODEL §5, §11; APPROVAL_RECORDS PCR-002 |
| 13 | Bukti GitHub-hosted asli; uji yang tidak dijalankan tidak pernah PASS | QUALITY_GATES §2.1, §4.1; TEST_STRATEGY §8; EXECUTOR_OPERATING_MODEL §8.4; EXECUTION_CONTEXT §5 |
| 14 | Urutan dependensi tetap | TASK_PLAN (DEPENDS ON tidak berubah) |
| 15 | Gotenberg: pengujian GitHub-hosted terpisah dari deploy produksi | DOCUMENT_ENGINE; ADR-0005; ARCHITECTURE; QUALITY_GATES §4 |
| 16 | Paritas TASK-071 privat; regresi sintetis di CI | LEGACY_REPORTING_EVIDENCE §10; TASK-071 IN SCOPE, TAC-08, TEST REQUIREMENTS |
| 17 | 78 ID Task dan kebutuhan bisnis tetap | TASK_PLAN; APPROVAL_RECORDS PCR-002 |
| 18 | C-14 superseded, bukan VERIFIED; C-15 tetap prasyarat Owner, bukan runner self-hosted | EXECUTOR_OPERATING_MODEL §13; EXECUTOR_CAPABILITY_REPORT; TOOLCHAIN |
| 19 | FRZ-001/GOV-10; persetujuan Owner tercatat | DECISION_LOG OD-37; DECISION_INDEX; SOURCE_INVENTORY U-013; APPROVAL_RECORDS PCR-002 |
| 20 | Status dan handoff; AUTH-013 tetap; prompt utama tidak dikirim ulang | TASK_PLAN; PHASE_STATUS; CURRENT_HANDOFF; AGENTS |

## 5. Penilaian risiko ringkas

| Risiko | Mitigasi |
|---|---|
| Run `pull_request` menguji commit uji-merge sintetis, bukan HEAD | Prosedur fail-closed QUALITY_GATES §4.1: run dicari lewat API Actions untuk PR dan SHA HEAD; artefak `bootstrap-evidence` mencatat SHA HEAD, SHA base, SHA uji-merge dan tree; tree uji = tree HEAD karena `main` leluhur HEAD; run hilang, basi, gagal, dibatalkan atau dilewati ditolak; dibuktikan sebagai C-16 pada PR uji sebelum merge TASK-002 |
| Integritas kerangka dari artefak | Dibuat installer resmi di runner pada PR yang sama; diff ditinjau sebelum commit; lockfile; audit dependensi di `quality`; tanpa secret |
| Artefak repositori publik dapat diunduh | Hanya sumber publik, lockfile dan screenshot sintetis; tanpa `.env`, `APP_KEY` atau data riil |
| Umpan balik lebih lambat (putaran CI) | PR draft dibuka lebih awal; commit dikumpulkan; `timeout-minutes`; Node lokal untuk pemeriksaan cepat |
| Akhir baris saat mengekstrak artefak di Windows (`core.autocrlf=true` global) | `.gitattributes` bawaan starter kit tidak di-stage atau di-commit (GOV-09); impor memakai `stage.ps1` dan pemeriksaan byte GIT_WORKFLOW; bila berkas itu diperlukan, keputusan Owner terpisah dengan diff terbatas; diff ditinjau |
| Pengetatan guard memblokir perintah sah | Uji guard positif dan negatif di TASK-003 |
| Paritas tidak langsung (V1 = oracle sintetis, oracle = legacy riil) | Profil pola memaksa kasus sintetis untuk setiap kategori riil; uji ujung-ke-ujung privat opsional di lingkungan Owner |
| Konflik D-19 staging | Hanya dibatasi; keputusan lokasi staging dicadangkan untuk Owner sebelum TASK-040; M0–M2 tidak terdampak |
| Kebijakan runner gratis GitHub berubah | Repositori publik saat ini gratis; alternatif berbayar hanya dengan persetujuan Owner |

## 6. Pertanyaan pemeriksaan

1. Apakah workflow bootstrap memutus lingkaran TASK-002–TASK-005 dengan bukti GitHub-hosted asli dan aturan workflow yang aman?
2. Apakah izin jalur pengaman cukup sempit dan larangan mode otonom tetap utuh?
3. Apakah TAC TASK-002–TASK-005 dapat diuji tanpa Docker, PHP, Composer atau PostgreSQL lokal, tanpa melemahkan gerbang?
4. Apakah prosedur paritas TASK-071 layak dan menjaga privasi?
5. Apakah perlakuan D-19 dan C-14/C-15 benar tanpa keputusan yang dikarang?
6. Apakah ada konflik dengan keputusan P0–P11 yang disetujui, FRZ-001 atau AUTH-013?

## 7. Format hasil

```text
VERDICT: APPROVED | APPROVED WITH CONDITIONS | REVISE | BLOCKED
IDENTITAS YANG DIREVIEW: <SHA HEAD cabang PCR-002>
TEMUAN:
| ID | Tingkat P0–P3 | Berkas § | Masalah | Koreksi yang diminta |
CATATAN UNTUK OWNER: <bila ada>
```

Dengan verdict yang dapat diterima, PCR-002 diterbitkan melalui fast-forward `main` terverifikasi, lalu commit transisi status yang terbit dengan cara yang sama (TASK-002 READY, TASK-003 TODO). Laporan kepada Owner ditulis dalam Bahasa Indonesia.
