# TASK-001 — Pagar pengaman eksekutor, runner dan verifikasi kemampuan (mode terawasi)

Status dokumen: P11 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Baseline: [TASK_PLAN](TASK_PLAN.md)

```text
STATUS: TODO
EXECUTION STAGE: —
RISK CLASS: HIGH
MILESTONE: M0 — Fondasi rekayasa
PARALLEL SAFE: NO
```

## OBJECTIVE

Membangun dan menguji perangkat eksekusi proyek untuk Claude Code di mesin Owner: hook pengaman, aturan izin terbatas, runner/watcher proyek, berkas checkpoint dan hold, serta verifikasi nyata atas kemampuan yang diasumsikan EXECUTOR_OPERATING_MODEL — sebelum mode otonom boleh diaktifkan.

## WHY THIS EXISTS

GOV-12 mewajibkan eksekusi otonom yang aman, terisolasi dan hanya memakai kemampuan yang telah diuji; dokumentasi Claude Code menandai beberapa perilaku (hook di Windows, wake sesi, atribusi) belum terverifikasi.

## DEPENDS ON

- Tidak ada

## BLOCKS

- TASK-002

## PREREQUISITES (READY GATE)

- Semua DEPENDS ON berstatus DONE dengan bukti
- Kandidat P4–P11 disetujui Owner, Planning Freeze tercatat, dan otorisasi eksekusi berlaku ([EXECUTION_CONTEXT](EXECUTION_CONTEXT.md))
- Gerbang READY AICWDF §22.8 diperiksa dan dicatat di TASK_PLAN
- Keputusan/prasyarat Owner: Owner hadir untuk mode terawasi dan menyetujui pengaturan izin serta `core.hooksPath` tingkat repositori
- Keputusan/prasyarat Owner: Owner memilih apakah runner berjalan via Task Scheduler atau dimulai manual
- Keputusan/prasyarat Owner: Owner telah memutuskan metode merge (EXECUTOR_OPERATING_MODEL §8.2)

## IN SCOPE

- `.claude/settings.json` proyek: aturan izin allow/deny yang membatasi ke direktori repositori PRANATA, perintah Git normal, artisan/composer/npm/docker compose proyek `pranata`
- Hook PreToolUse (Bash dan PowerShell) yang menolak: `git push --force`, perubahan `git config user.*`, operasi Docker global (`system/volume/network prune`, restart global), akses path proyek lain (MULTIPLECORP, BantuKasir, ELLIGBLE), koneksi ke host basis data non-`pranata`, dan berkas `.env` produksi
- Hook Stop dengan pemeriksaan `stop_hook_active` yang mencegah berhenti diam-diam saat Task aktif belum mencatat checkpoint, dan tidak pernah membuat loop tanpa kemajuan
- Berkas status `.pranata/executor/checkpoint.json`, `hold.json` (IMMEDIATE_SAFE_SHUTDOWN / SHUTDOWN_AFTER_CURRENT_TASK), `heartbeat` dan kunci tunggal watcher (diabaikan Git)
- Skrip runner/watcher PowerShell berskop proyek yang menjalankan sesi headless Claude Code per langkah Task dengan allowlist alat, batas giliran dan pembacaan hasil; deteksi idle berbasis bukti (heartbeat, proses, commit, log) dengan ambang kandidat 45 menit
- Uji kemampuan nyata dan pencatatan hasilnya: hook pada Windows (Git Bash/PowerShell), mode headless dengan prompt izin dinonaktifkan, perilaku setelah compaction, penjadwalan Windows Task Scheduler, pengaturan atribusi Git pada repositori uji sekali pakai (tidak boleh ada trailer AI), deteksi status review Codex melalui `gh`
- Penjaga atribusi berlapis: pengaturan atribusi proyek dinonaktifkan, hook Git `commit-msg` di `.githooks/` (diaktifkan dengan `core.hooksPath` tingkat repositori setelah persetujuan Owner), templat PR tanpa footer atribusi, pemeriksaan metadata pasca-commit
- Skrip `scripts/executor/merge-verified.ps1` (pemeriksaan EXECUTOR_OPERATING_MODEL §8.1 lalu merge dengan metode yang diputuskan Owner), `hold.ps1`, `release-hold.ps1`, `status.ps1`, `runner.ps1`, `watcher.ps1`
- Uji kemampuan C-01–C-13 (EXECUTOR_OPERATING_MODEL §13) termasuk perilaku PR merged setelah fast-forward dan ruleset status check pada repositori uji sekali pakai, subagen peninjau dalam mode headless, dan deteksi batas pemakaian
- Laporan verifikasi kemampuan di `docs/11-tasks/evidence/EXECUTOR_CAPABILITY_REPORT.md` (tanpa rahasia)

## OUT OF SCOPE

- Aktivasi mode otonom (memerlukan otorisasi eksekusi Owner terpisah)
- Kode aplikasi Laravel/React
- Perubahan konfigurasi Git global atau proyek lain
- Layanan berbayar

## REFERENCES

- [EXECUTION_CONTEXT](EXECUTION_CONTEXT.md)
- [EXECUTOR_OPERATING_MODEL](EXECUTOR_OPERATING_MODEL.md)
- [EXECUTOR_BOOTSTRAP](EXECUTOR_BOOTSTRAP.md)
- [GOV-08/GOV-12](../00-governance/DECISION_LOG.md#gov-12--autonomous-execution-operating-model-designed-now-activated-later)
- [GIT_WORKFLOW](../00-governance/GIT_WORKFLOW.md)
- [INFRASTRUCTURE §2](../09-operations/INFRASTRUCTURE.md#2-pengembangan-lokal-terisolasi)

## TRACEABILITY

- Keputusan Owner: GOV-12, GOV-08

## EXPECTED MODULES / FILE AREAS

- `.claude/settings.json`
- `.claude/hooks/*`
- `.githooks/commit-msg`
- `.github/pull_request_template.md`
- `scripts/executor/*.ps1`
- `.pranata/ (gitignored)`
- `docs/11-tasks/evidence/`

## BUSINESS RULES

- Tidak ada aturan bisnis baru; aturan P2 tetap berlaku

## ARCHITECTURE RULES

- Semua perangkat berskop proyek; tidak menyentuh pengaturan pengguna global kecuali dicatat dan disetujui Owner

## SECURITY / PERMISSION REQUIREMENTS

- Hook deny diuji dengan percobaan perintah terlarang dan dibuktikan menolak
- Tidak ada token/kredensial yang disimpan di repositori atau log
- Atribusi Git: commit uji di repositori sekali pakai diperiksa metadata mentahnya; tidak ada Co-Authored-By/trailer AI

## DATABASE IMPACT ASSESSMENT

- Schema change: NONE
- Migration required: NO
- Tables: —
- Existing data transformation: NONE
- Direct production DB access: FORBIDDEN
- Data-loss risk: NONE KNOWN

## MODULARITY / EXTENSIBILITY

- Module boundary affected: —
- Stable core kept provider-neutral: YES
- External/vendor-specific code isolated: N/A
- Replaceable adapter/contract required: NO
- Provider lock-in risk: NONE
- Future extension impact: MEDIUM
- Speculative abstraction avoided: YES

## AUTH IMPACT

- Authentication change: NONE
- Authentication implementation: N/A
- Auth profile: N/A
- Google sign-in: OFF (OD-11/12)
- Google registration behavior: N/A
- Google credentials owner action required: NO
- Authorization change: NONE
- Provider/library-specific leakage into domain: NO

## COST IMPACT

- New recurring cost: NONE
- Paid third-party service: NO
- Free/open-source alternative: N/A
- Estimated additional monthly production cost: 0
- Owner approval required: NO

## MCP SESSION PROFILE

- Required MCP servers: tidak ada; Context7 bila tersedia untuk topik versi
- MCP servers that should remain OFF: cloud/infra, Figma, basis data jarak jauh, browser MCP (E2E memakai paket Playwright proyek)
- Native CLI/package preferred over MCP: YES (artisan, composer, npm, Playwright, gh)
- Post-Task deactivation/cleanup: tutup proses/kontainer dev yang dimulai Task; jangan sentuh proyek lain

## TOOL REQUIREMENTS

- Context7: REQUIRED (fallback: dokumentasi resmi terkini)
- Graphify: OPTIONAL (belum terverifikasi; gunakan analisis terarah `rg`)
- Browser E2E: N/A
- Missing safe tool behavior: AUTO-INSTALL/AUTO-CONFIGURE dalam lingkup proyek, catat di TOOLCHAIN

## GRAPHIFY / IMPACT ANALYSIS

- Graph freshness: N/A bila Graphify tidak tersedia
- Pre-change questions: modul/tabel/rute/izin mana yang disentuh; uji mana yang terdampak
- Expected affected areas: .claude/settings.json, .claude/hooks/*, .githooks/commit-msg, .github/pull_request_template.md, scripts/executor/*.ps1, .pranata/ (gitignored), docs/11-tasks/evidence/

## CONTEXT7

- Topics to verify: Claude Code hooks, settings, permissions, headless mode (dokumentasi resmi terkini)
- Version-sensitive assumptions: API framework/paket diverifikasi terhadap versi terpasang sebelum dipakai

## UI / UX REQUIREMENTS

- N/A (tanpa antarmuka pengguna)

## ROUTE IMPACT

- N/A

## INTERACTION CONTRACTS

- N/A

## NAVIGATION CONTRACT

- N/A

## ACCEPTANCE CRITERIA

Kriteria lokal Task memakai `TAC-nn`; kriteria produk `AC-nn`/NFR ada di TRACEABILITY.

- TAC-01 Semua perintah terlarang pada daftar uji ditolak oleh hook pada Windows, dengan bukti keluaran
- TAC-02 Runner dapat memulai, menghentikan aman (hold) dan melanjutkan dari checkpoint tanpa kehilangan status Task
- TAC-03 Watcher tunggal: instance kedua menolak berjalan; heartbeat dan idle terdeteksi dari bukti
- TAC-04 Laporan kemampuan mencatat setiap kemampuan sebagai VERIFIED, FAILED atau NOT AVAILABLE beserta bukti; tidak ada kemampuan yang diklaim tanpa uji
- TAC-05 Commit uji tidak memuat atribusi AI dan memakai identitas Owner; hook `commit-msg` menolak pesan uji yang memuat trailer atribusi
- TAC-06 Skrip merge menolak merge bila salah satu pemeriksaan §8.1 gagal (uji negatif) dan hook menolak push langsung ke `main` di luar skrip
- TAC-07 EXECUTOR_OPERATING_MODEL diperbarui bila hasil uji berbeda dari asumsi (melalui TASK PLAN CHANGE bila memengaruhi Task lain)

## TEST REQUIREMENTS

- Lint: PSScriptAnalyzer untuk skrip PowerShell bila tersedia
- Typecheck: N/A (aplikasi belum ada)
- Unit: Uji skrip hook dengan masukan contoh (izin/tolak)
- Integration: Uji end-to-end runner pada repositori uji sekali pakai
- Production build: N/A (aplikasi belum ada)
- Security: Daftar uji perintah terlarang
- Other: Uji hold IMMEDIATE_SAFE_SHUTDOWN dan SHUTDOWN_AFTER_CURRENT_TASK

## TEST CHECKLIST

Item yang tidak relevan ditandai `N/A` dengan alasan singkat, tidak dicentang.

- [ ] Lint
- [ ] Typecheck
- [ ] Relevant automated tests
- [ ] Authorization
- [ ] Route integrity
- [ ] Interaction behavior
- [ ] Browser E2E if required
- [ ] Responsive/adaptive if required
- [ ] Visual breathing room/density if UI
- [ ] Production build
- [ ] Security checks if required
- [ ] Database safety review
- [ ] Final diff review

## REVIEW REQUIREMENT

HIGH — swa-review terstruktur + review PR otomatis (Codex) + review independen kedua; lulus bila tidak ada temuan P0/P1 terbuka ([QUALITY_GATES §3](../08-testing/QUALITY_GATES.md#3-gerbang-review-berbasis-risiko))

## DEFINITION OF DONE

- Semua ACCEPTANCE CRITERIA terbukti dengan bukti tercatat
- Definition of Done [QUALITY_GATES §2](../08-testing/QUALITY_GATES.md#2-definition-of-done) untuk semua Task + §2.3 HIGH
- PR ke `main` lulus `ci-gate` pada HEAD terakhir dan review sesuai REVIEW REQUIREMENT, di-merge, lalu `main` remote diverifikasi memuat merge tersebut ([EXECUTOR_OPERATING_MODEL §8](EXECUTOR_OPERATING_MODEL.md#8-merge-dan-rekonsiliasi-done))
- TASK_PLAN dan CURRENT_HANDOFF diperbarui; checklist dicentang hanya bila DONE

## COMPLETION EVIDENCE

- Test outputs:
- Build result:
- Route evidence:
- Interaction evidence:
- Browser evidence:
- Localization evidence:
- Review status (Codex / independen, temuan P0–P3):
- Cost evidence/exception if applicable:
- Files changed:
- PR / CI run:
- Commit SHA:
- Notes:

## SAFE NEXT TASK

- TASK-002

## DO NOT DO

- Jangan mengaktifkan mode otonom
- Jangan memakai mode yang melewati semua izin tanpa hook deny yang terbukti bekerja
- Jangan menyentuh direktori, kontainer atau basis data proyek lain
- Jangan memperluas ruang lingkup tanpa TASK PLAN CHANGE
- Jangan mengakses basis data produksi atau proyek lain
- Jangan menambahkan atribusi AI pada commit/PR
