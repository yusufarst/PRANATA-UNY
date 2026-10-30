# Handoff pemeriksaan delta — koreksi Second Review `a4bc1af`

Status: P0–P11 planning candidate — VERIFYING (AUTH-012) | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT)

Sesuai GOV-11, koreksi atas review substantif diverifikasi dengan **pemeriksaan delta sempit**: apakah tiga temuan SR-D2-P1-01–SR-D2-P1-03 tertutup secara substansi dan apakah koreksinya menimbulkan masalah baru. Review `a4bc1af` telah mencatat enam temuan `48c2bc5` tertutup di tingkat spesifikasi dan mengonfirmasi amandemen temuan lapangan U-011; bagian itu tidak perlu diperiksa ulang kecuali tersentuh koreksi ini. Handoff lengkap tetap [SECOND_REVIEW_HANDOFF](SECOND_REVIEW_HANDOFF.md).

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Repositori | `https://github.com/yusufarst/PRANATA-UNY`, cabang `docs/p0-p11-planning`, PR draft #1 (tidak untuk di-merge sebelum keputusan Owner) |
| Identitas yang direview sebelumnya | `a4bc1af79a57b74d7259e972042a28a91ed10cb7` — verdict **CORRECTIONS_REQUIRED**: SR-D2-P1-01, SR-D2-P1-02, SR-D2-P1-03 (semua P1) |
| Identitas koreksi | SHA HEAD cabang yang dilaporkan bersama permintaan ini — satu commit di atas `a4bc1af` (dokumen tidak dapat memuat hash commit yang memuatnya); diff `git diff a4bc1af..<SHA HEAD>` |
| Status | Semua kandidat tetap VERIFYING / belum disetujui; 0 Task READY; GAP-001–GAP-029 OPEN; tidak ada kode aplikasi |

## 2. Disposisi dan peta koreksi

Ketiga temuan diverifikasi ulang secara mandiri pada berkas di `a4bc1af` dan **terbukti** (SUBSTANTIATED); cacat sekelas yang ditemukan saat verifikasi ikut dikoreksi. Alasan verifikasi lengkap ada di [P0_P11_PLANNING_QUALITY_GATE — Independent Second Review of `a4bc1af`](../00-governance/evidence/P0_P11_PLANNING_QUALITY_GATE.md#independent-second-review-of-a4bc1af).

| Temuan | Hasil verifikasi pada `a4bc1af` | Inti koreksi | Tempat memeriksa | Uji regresi |
|---|---|---|---|---|
| SR-D2-P1-01 Keberlakuan kebijakan | Terbukti. Aktivasi penerus menandai pendahulu SUPERSEDED walaupun penerus baru berlaku kemudian, sementara tabel aturan produksi, ARCHITECTURE, ADR-0003, ACCESS_CONTROL, dokumen P6/P7/P10/P11, guard P3 kandidat WF-030–WF-033 dan prasyarat setiap Task bergerbang "ACTIVE". Kesalahan sebaliknya juga mungkin: pembacaan "versi ACTIVE" memakai penerus terjadwal terlalu dini. Sekelas: skema penomoran berganti menurut status | Satu definisi **keberlakuan resmi** terpisah dari status siklus hidup: disahkan (ACTIVE/SUPERSEDED) dan `effective_from ≤ d < effective_to`; `status` tidak pernah menjadi gerbang; `PolicyHandle.official` menggantikan `status`; tanggal acuan dinyatakan kontrak Action (bawaan tanggal bisnis Asia/Jakarta saat commit); resolusi di dalam transaksi dengan tinjau ulang bila versi berubah; cache berkunci tanggal acuan; reproduksi historis dari versi tersimpan; SUPERSEDED dalam jendela = WF029-S5. Definisi sama untuk versi template, versi klasifikasi dan penomoran (skema kini per versi kebijakan `numbering`) | [POLICY_REGISTRY §2, §3, §3.1, §6, §7](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi); [DATA_MODEL §1, §8, §11, §12, §16](../04-architecture/DATA_MODEL.md#1-konvensi); [DOCUMENT_ENGINE](../04-architecture/DOCUMENT_ENGINE.md); [ARCHITECTURE §6–§8.1](../04-architecture/ARCHITECTURE.md#81-open-gap-sebagai-gerbang-aktivasi); [ADR-0003](../adr/ADR-0003-versioned-policy-registry.md); [ACCESS_CONTROL](../05-security/ACCESS_CONTROL.md) langkah 6, SOD-06, AUTHZ-40; [CONCURRENCY_AND_INTEGRITY §3–§4](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#4-race-condition-yang-ditangani); [COMMAND_AND_API_CONTRACTS §2–§3](../06-api-performance/COMMAND_AND_API_CONTRACTS.md#2-perintah-action); baris kandidat P3 (STATE_TRANSITIONS WF-030–WF-033, WORKFLOW_CONTRACTS, INTERACTION/ROUTE_CONTRACTS, EXCEPTION_REVISION_PATTERNS, WORKFLOW_DECISION_REQUESTS); RELEASE_PLAN; RB-09; TASK-013, 018, 020, 021, 029, 030, 050, 052, 055, 058, 061, 067, 071, 078 dan prasyarat gerbang 70 Task | DI-32; DI-12, DI-13, DI-18, DI-20 |
| SR-D2-P1-02 Batas akhir dan keterlambatan | Terbukti. `is_late` hanya dibatasi satu arah, `deadline_at` salinan opsional, tidak ada batas akhir yang berwenang per pemilihan/sampul, dan entri pembukaan maupun hasil evaluasi tidak bergantung pada waktu terima — penawaran 14.15 berstatus RECEIVED/`is_late = false` dapat dibuka dan dievaluasi | Tabel append-only `proc_submission_deadlines` (berwenang = versi tertinggi; ADDENDUM hanya sebelum batas lewat; sesudahnya hanya EXTENSION_DECISION tercatat bila kebijakan yang berlaku resmi mengizinkan; tanpa versi sesudah pembukaan; kunci pemilihan). Tanpa penanda terlambat tersimpan: tepat waktu ⇔ `received_at < c` atau (inklusif dan `received_at = c`), batas inklusif dari kebijakan (DRAFT: inklusif, menunggu validasi). Waktu terima: jam basis data untuk kanal PRANATA, bukti untuk kanal eksternal. Catatan pembukaan/penutupan merujuk versi terakhir dan menyalin batas lewat FK; `CHECK` tepat waktu pada entri; constraint trigger tertunda menyelaraskan status; hasil evaluasi ber-FK ke entri yang dibuka; klarifikasi/negosiasi hanya untuk penawaran yang dibuka | [DATA_MODEL §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan) (`proc_submission_deadlines`, `proc_submissions`, `proc_submission_scopes`, `proc_opening_records`, `proc_opening_entries`, hasil kriteria/tahap, klarifikasi, negosiasi, paragraf invarian); CONCURRENCY_AND_INTEGRITY §3, §4, §9; [POLICY_REGISTRY §5](../04-architecture/POLICY_REGISTRY.md#5-contoh-isi-lain-bentuk-bukan-nilai-resmi); [ACCESS_CONTROL §4.2](../05-security/ACCESS_CONTROL.md#42-pengadaan); [INTERACTION_PATTERNS §10](../07-ux-design/INTERACTION_PATTERNS.md#10-ruang-kerja-evaluasi) (PG-123); J-19; UAT-P13; RG-07; GAP_REGISTER; D-26 (baris validasi); TASK-032, TASK-033, TASK-034 | DI-31 (a–g); DI-23, DI-24 |
| SR-D2-P1-03 Penerapan kriteria per lingkup | Terbukti. `scope_id` kriteria boleh kosong tanpa aturan yang mengikat lingkup hasil pada penerapan kriteria atau pada `per_scope` tahap; sekelas: peserta dapat dinilai pada lot yang tidak ditawarnya dan makna lingkup kosong tidak didefinisikan | Tahap membawa `per_scope` dan `envelope`; kriteria mewarisinya lewat FK dan tidak lagi punya `scope_id`; tabel `proc_evaluation_criterion_scopes` (hanya untuk kriteria per lingkup; "semua lot" disimpan eksplisit; minimal satu baris saat pembekuan); hasil menyalin `per_scope` lewat FK dengan `CHECK (per_scope = (scope_id IS NOT NULL))`, FK ke penerapan `(criterion_id, scope_id)` dan ke lingkup yang ditawar `(submission_id, scope_id)`; hasil tahap per lingkup hanya bila ada kriteria berlaku; gugur per peserta vs per lingkup; peringkat mensyaratkan PASS pada setiap tahap gugur yang berlaku. Tanpa skor, bobot atau ambang baru | DATA_MODEL §14.3 (tahap, kriteria, `proc_evaluation_criterion_scopes`, hasil kriteria/tahap, peringkat; paragraf penerapan); CONCURRENCY_AND_INTEGRITY §3; INTERACTION_PATTERNS §10 (PG-111, PG-122); TASK-032, TASK-034, TASK-035 | DI-07, DI-22, DI-25, DI-05, DI-28 |

Katalog uji regresi: [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib); DI-31 memuat skenario yang diminta peninjau: penawaran terlambat disisipkan langsung ke basis data dengan status RECEIVED (tanpa kolom penanda yang dapat dipalsukan), lalu percobaan pembukaan dan evaluasi. DI-07 memuat kriteria lot A yang dicatat untuk lot B, melalui basis data langsung dan aplikasi. DI-32 memuat V1 berlaku/V2 terjadwal, V2 berlaku pada 00.00 WIB, dan reproduksi historis. Pemetaan DI → Task ada di [TRACEABILITY_MATRIX](../11-tasks/TRACEABILITY_MATRIX.md#skenario-integritas-data-di).

## 3. Pilihan rancangan yang perlu dinilai

1. **Status bukan gerbang.** Versi SUPERSEDED tetap berlaku resmi sampai `effective_to`; versi terjadwal (disahkan, belum berlaku) tidak pernah dipakai lebih awal. Kebijakan, template, klasifikasi dan penomoran memakai satu definisi; uji arsitektur menolak seleksi atau gerbang menurut `status` saja.
2. **Tanggal acuan per Action.** Bawaan tanggal bisnis Asia/Jakarta saat commit; Action yang terikat tanggal subjek (tanggal acuan paket, tanggal generasi, tanggal finalisasi) memakai tanggal itu. Perubahan versi berlaku antara pratinjau dan commit memaksa tinjau ulang.
3. **Batas akhir berwenang, keterlambatan dihitung.** Satu sumber per pemilihan dan sampul, append-only; pembukaan membekukannya. Batas menit inklusif atau tidak **bukan** dinyatakan sebagai aturan resmi: diambil dari kebijakan, DRAFT bernilai inklusif dengan status menunggu validasi, karena PR 34/2023 meminta penawaran diterima "sebelum" batas akhir tetapi hanya menolak yang masuk "setelah" batas akhir (Lampiran III B.3.b.4.b–d). Perpanjangan sesudah batas lewat hanya keputusan eksplisit dan tidak diizinkan kebijakan DRAFT. Pertanyaan validasi dicatat di GAP-017/029 tanpa GAP baru.
4. **Pembukaan dan penutupan.** Status DRAFT pada catatan pembukaan dihapus (catatan dan entri dibuat dalam satu transaksi) agar invarian dapat diperiksa saat commit; penutupan penerimaan (CLOSING) dipakai bagi sampul tanpa pembukaan formal menurut kebijakan; sampul yang tidak dibuka (NOT_OPENED) hanya bila kebijakan menetapkannya dan selalu beralasan (aturan sampul kedua belum tervalidasi).
5. **Kelayakan evaluasi lewat FK.** Hasil kriteria/tahap yang dinilai merujuk entri pembukaan `opened = true`; penawaran terlambat tidak pernah mempunyai entri karena `CHECK` memakai salinan `received_at`.
6. **Lingkup kosong.** `scope_id` kosong tepat bila tahap per peserta; tahap per lingkup selalu berlingkup eksplisit, termasuk lingkup PACKAGE tunggal.

## 4. Pertanyaan pemeriksaan

1. **SR-D2-P1-01** — Masih adakah gerbang yang membaca status saja, jalur yang memakai versi terjadwal lebih awal, atau penolakan versi SUPERSEDED yang masih berlaku — di kebijakan, template, penomoran, klasifikasi, kontrak Action atau Task?
2. **SR-D2-P1-02** — Apakah kombinasi FK, `CHECK`, trigger insert dan constraint trigger tertunda benar-benar mencegah skenario peninjau (penawaran 14.15 disisipkan sebagai RECEIVED) dibuka, dievaluasi, diklarifikasi atau dinegosiasikan, dan mencegah penawaran tepat waktu dikecualikan atau batas akhir diubah diam-diam? Apakah perlakuan menit batas cukup netral terhadap aturan yang belum tervalidasi?
3. **SR-D2-P1-03** — Dapatkah kriteria lot A dicatat untuk lot B, peserta dinilai pada lot yang tidak ditawarnya, atau hasil tanpa lingkup dicatat untuk tahap per lingkup?
4. **Regresi** — Apakah koreksi menimbulkan inkonsistensi baru antar-dokumen, FK tanpa unique pasangan, urutan pembuatan skema yang salah, pemilik DI yang tidak cocok, aturan pengadaan yang dikarang, atau perubahan kata-kata P0–P3 yang disetujui?

## 5. Yang tidak berubah

- Kata-kata P0–P3 yang disetujui tidak diubah; baris P3 yang disunting hanyalah baris kandidat AUTH-012 (setiap baris yang dihapus diperiksa tidak ada di `0e3f7de`).
- Stack, modul, model akses, daftar ADR, model eksekutor, jumlah Task (78), ID dan arah dependensi tetap; tidak ada Task baru, GAP baru atau GAP yang diselesaikan.
- Keputusan Owner D-01–D-29 tetap. D-26 tidak menanyakan ulang jumlah pemenang (dijawab OD-35); baris validasi kustodian tentang batas akhir penawaran ditambahkan. D-28 hanya diperjelas memakai istilah "berlaku resmi" tanpa mengubah makna. Catatan D-24 kini menyebut UAT-1 dengan penawaran sintetis yang dicatat petugas sebagai tafsir Planning Agent sampai Owner menyatakan lain. D-01–D-03 tetap belum disetujui; D-04, D-08, D-27 dan D-28 tetap keputusan Owner.

## 6. Bukti pemeriksaan mandiri

[P0_P11_PLANNING_QUALITY_GATE](../00-governance/evidence/P0_P11_PLANNING_QUALITY_GATE.md) diperbarui: QG-P-01–QG-P-15 dijalankan ulang (tautan/anchor, ID termasuk DI-31/DI-32, cakupan termasuk DI-01–DI-32, urutan pembuatan skema termasuk dua tabel baru, kepemilikan DI, pelestarian byte dan baris P0–P3 yang disetujui) dan QG-P-16 baru memeriksa bahwa setiap FK komposit di DATA_MODEL mempunyai unique pasangan pada tabel tujuan. Hasil identitas dan atribusi commit koreksi dilaporkan bersama permintaan ini.

## 7. Format hasil yang diminta

```text
VERDICT: READY_FOR_OWNER_APPROVAL | CORRECTIONS_REQUIRED
IDENTITAS YANG DIREVIEW: <SHA HEAD>
DISPOSISI: SR-D2-P1-01, SR-D2-P1-02, SR-D2-P1-03 = CLOSED | OPEN (alasan)
TEMUAN BARU (bila ada):
| ID | Tingkat | Berkas § | Masalah | Koreksi yang diminta |
CATATAN UNTUK OWNER: <hal yang memerlukan keputusan Owner>
```

Laporan dan pertanyaan kepada Owner ditulis dalam Bahasa Indonesia. Verdict bukan persetujuan Owner.
