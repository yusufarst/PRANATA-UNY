# Handoff pemeriksaan delta — koreksi Second Review `48c2bc5` dan amandemen temuan lapangan pengadaan

Status: P0–P11 planning candidate — VERIFYING (AUTH-012) | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT)

Sesuai GOV-11, koreksi atas review substantif diverifikasi dengan **pemeriksaan delta**: memeriksa apakah setiap temuan tertutup dan apakah koreksi menimbulkan masalah baru, bukan mereview ulang seluruh kandidat. Handoff lengkap tetap [SECOND_REVIEW_HANDOFF](SECOND_REVIEW_HANDOFF.md). Permintaan ini mencakup dua bagian:

- **Bagian A** — koreksi atas temuan Second Review `48c2bc5` (commit `bfa6501`), belum pernah diperiksa.
- **Bagian B** — amandemen temuan lapangan pengadaan dari Owner (U-011) di atas `bfa6501`. Karena amandemen ini mengubah model pemilihan, evaluasi dan penghargaan, bagian B meminta **review terfokus pada area pengadaan** (GOV-11: perubahan makna/arsitektur material), bukan review ulang seluruh kandidat.

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Repositori | `https://github.com/yusufarst/PRANATA-UNY`, cabang `docs/p0-p11-planning`, PR draft #1 (tidak untuk di-merge sebelum keputusan Owner) |
| Identitas yang direview sebelumnya | `48c2bc5417d28dfb4820b63d551a75e2d5754d61` — verdict **CORRECTIONS_REQUIRED**: SR-P1-01–SR-P1-05, SR-P2-01 |
| Bagian A | `bfa650185eaa9168f71e43a52bd6a48115a30fae` — satu commit koreksi di atas `48c2bc5`; diff `git diff 48c2bc5..bfa6501` |
| Bagian B | SHA HEAD cabang yang dilaporkan bersama permintaan ini — satu commit di atas `bfa6501` (dokumen tidak dapat memuat hash commit yang memuatnya); diff `git diff bfa6501..<SHA HEAD>` |
| Status | Semua kandidat tetap VERIFYING / belum disetujui; 0 Task READY; GAP-001–GAP-029 OPEN; tidak ada kode aplikasi |

## 2. Bagian A — peta koreksi per temuan

Disposisi lengkap beserta hasil verifikasi pada `48c2bc5` ada di [P0_P11_PLANNING_QUALITY_GATE — Independent Second Review](../00-governance/evidence/P0_P11_PLANNING_QUALITY_GATE.md#independent-second-review-of-48c2bc5). Semua temuan terbukti (SUBSTANTIATED) dan dikoreksi; cacat sekelas yang ditemukan saat verifikasi ikut dikoreksi.

| Temuan | Inti koreksi | Tempat memeriksa | Uji regresi |
|---|---|---|---|
| SR-P1-01 Run penyusutan | Unique run resmi mencakup FAILED; tanpa SUPERSEDED untuk run resmi; CANCELLED hanya tanpa baris; protokol lanjutan run yang sama (VALIDATION → LINES → PROJECTION, checkpoint tahan lama, compare-and-set `FAILED → RUNNING` atas permintaan berwenang, penjaga worker usang, proyeksi aditif berpenjaga `last_depreciation_period`, periode berurutan); koreksi resmi menunggu kebijakan | [CONCURRENCY_AND_INTEGRITY §6.1](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#61-protokol-run-penyusutan-resmi), §3–§4; [DATA_MODEL §16](../04-architecture/DATA_MODEL.md#16-asset) (`asset_depreciation_runs`, `asset_depreciation_lines`, kolom proyeksi `asset_assets`), §19 aturan 5; [TASK-066](../11-tasks/TASK-066.md) | DI-01–DI-04 |
| SR-P1-02 Konsistensi induk | FK komposit `(rujukan, lingkup) → induk(id, lingkup)`; kolom lingkup tidak dapat diubah; constraint trigger untuk perusahaan kontrak = pemenang dan penugasan kontribusi SPPBJ pada paket yang sama; satu WINNER dan peringkat unik per hasil. Di `bfa6501` satu pemenang per paket dinyatakan sebagai asumsi rancangan dengan pertanyaan Owner D-26; **bagian B menggantikannya** dengan lingkup penghargaan (OD-35) dan membuat perusahaan kontrak = pemenang menjadi FK deklaratif | [DATA_MODEL §1](../04-architecture/DATA_MODEL.md#1-konvensi), [§14.3](../04-architecture/DATA_MODEL.md#143-pemilihan), [§14.4](../04-architecture/DATA_MODEL.md#144-kontrak-dan-pelaksanaan), [§15](../04-architecture/DATA_MODEL.md#15-inventory); TASK-033–TASK-037, TASK-046, TASK-052–TASK-054 | DI-05–DI-08, DI-17, DI-19 |
| SR-P1-03 Pembalikan dan identitas peristiwa | Unique identitas sumber pada `inv_stock_movements` dan `asset_transactions` (lepas dari kunci idempotensi); satu pembalikan penuh per entri dengan trigger pencocok; pembalikan stok membawa sumber koreksi dan alasan; pembalikan aset hanya atas transaksi terakhir yang belum dibalik; constraint trigger per jenis sumber; kolom ID gerakan sirkular dihapus | DATA_MODEL §1, §15, §16; [CONCURRENCY_AND_INTEGRITY §3–§4](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#3-invarian-yang-ditegakkan-basis-data); [ADR-0004](../adr/ADR-0004-append-only-ledgers.md); TASK-050, TASK-051, TASK-053–TASK-056, TASK-059 | DI-09–DI-11 |
| SR-P1-04 Versi template | Jendela berlaku `[effective_from, effective_to)` dengan `EXCLUDE` atas ACTIVE/SUPERSEDED untuk template, kebijakan dan klasifikasi; aktivasi atomik di bawah kunci induk; tanpa tanggal mundur (P3 TR-286); pemilihan template paling spesifik yang deterministik; keadaan WF-029 S4/S5/S6 diturunkan; satu skema penomoran ACTIVE per jenis dokumen | [DOCUMENT_ENGINE](../04-architecture/DOCUMENT_ENGINE.md); [POLICY_REGISTRY §2–§3](../04-architecture/POLICY_REGISTRY.md#2-lifecycle-versi); DATA_MODEL §1, §8, §11, §12, §16; TASK-018, TASK-020, TASK-021, TASK-058 | DI-12, DI-13, DI-18, DI-20 |
| SR-P1-05 Baris kontrak | Baris kontrak append-only per versi dengan FK `(contract_id, version_no)` dan unique per versi; rancangan di `draft_terms` sampai SIGNED; versi ORIGINAL dan versi adendum dibuat lengkap dalam satu transaksi; sekelas: baris HPS, penerimaan, opname, transfer, serah terima gudang dan BAST beku setelah status final | DATA_MODEL §1, §2, [§14.2](../04-architecture/DATA_MODEL.md#142-paket-hps-dan-penetapan-metode), §14.4, §15, §19 aturan 5; TASK-029, TASK-037, TASK-045, TASK-046, TASK-051, TASK-053, TASK-055, TASK-056 | DI-14, DI-15 |
| SR-P2-01 Provenans kebijakan majemuk | `policy_version_uses` append-only per subjek dan keluarga; rencana langkah mencatat `reference_date` dan `generation_reason`; regenerasi per keluarga penyumbang sebagai versi rencana baru; penetapan metode mencatat versi metode dan rezim | POLICY_REGISTRY §3; DATA_MODEL §1, §8, §14.2; [ADR-0003](../adr/ADR-0003-versioned-policy-registry.md); TASK-018, TASK-028, TASK-030 | DI-16 |

Katalog uji regresi: [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib), termasuk uji negatif langsung ke basis data dengan peran `pranata_app` dan uji konkurensi dua koneksi. Pemetaan DI → Task ada di [TRACEABILITY_MATRIX](../11-tasks/TRACEABILITY_MATRIX.md#skenario-integritas-data-di).

## 3. Bagian B — amandemen temuan lapangan pengadaan (U-011)

**Masukan.** Pada 2026-10-30 Owner menyerahkan temuan dari pegawai pengadaan UNY ([U-011, SRC-068](../00-governance/SOURCE_INVENTORY.md#u-011-owner-procurement-field-findings-of-2026-10-30)) bersama PR 34/2023 (identik dengan SRC-048). Memorandum itu adalah referensi, bukan desain wajib. Owner menetapkan: evaluasi penawaran adalah prioritas utama ([OD-36](../00-governance/DECISION_LOG.md#od-36)); satu paket dapat mempunyai beberapa pemenang per item/lot bila diizinkan ([OD-35](../00-governance/DECISION_LOG.md#od-35)).

**Tafsir yang diverifikasi.** Setiap pernyataan regulasi pada memorandum diperiksa ulang ke lapisan teks SRC-048 dengan locator ([RG-07](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled)). Yang bersumber resmi: undangan Quotation ≥ 3 penyedia DPT melalui pbj.uny.ac.id; kriteria evaluasi ditetapkan dalam dokumen pengadaan (harga terendah, sistem nilai, skor berbobot, biaya kepemilikan); pembukaan tidak menggugurkan; isi penawaran tidak diubah; klarifikasi sebagai dasar menerima/menggugurkan; isi BA evaluasi; pemenang dan cadangan; tender terbatas/dua tahap gagal bila lulus kualifikasi < 3; Penunjukan Langsung gagal bila negosiasi tidak sepakat; E-Purchasing dengan negosiasi teknis/harga (termurah lebih dahulu bila ≥ 2 penyedia); reviu PPK sebelum kontrak; pemanggilan calon pemenang kedua/ketiga bila jaminan tidak diserahkan; penilaian kinerja penyedia sesudah pekerjaan selesai. Yang **praktik, bukan aturan**: tiga penawaran pada Pengadaan Langsung; rincian dokumen konstruksi (RKS, SDP); peran pbj.uny.ac.id di luar undangan. Penghargaan per lot/item **tidak** diatur eksplisit (hanya kontrak payung dengan satu atau lebih penyedia), sehingga kapabilitasnya berasal dari OD-35 dan batas prosedurnya menjadi pertanyaan validasi kustodian di bawah GAP-003/017/027. Tidak ada GAP baru; tidak ada GAP yang diselesaikan.

**Konflik dengan P0–P3 yang disetujui.** Tidak ditemukan: DC-024 menyatakan paket "bukan satu penyedia"; kontrak P3 memakai "scope" generik; hasil resmi tetap TR-084/TR-092 BLOCKED_BY_GAP. Karena itu P1/P2 hanya mendapat tambahan amandemen dan P3 hanya catatan cara membaca, tanpa state/TR/RT/IX baru.

| Area | Perubahan | Tempat memeriksa | Uji regresi |
|---|---|---|---|
| P0 | U-011/SRC-068 dengan hash; OD-35/OD-36; RG-07 (resmi vs praktik); pertanyaan validasi per GAP yang ada | [SOURCE_INVENTORY](../00-governance/SOURCE_INVENTORY.md#u-011-owner-procurement-field-findings-of-2026-10-30), [DECISION_LOG](../00-governance/DECISION_LOG.md#od-35), [REGULATORY_SOURCE_REVIEW RG-07](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled), [GAP_REGISTER](../00-governance/GAP_REGISTER.md#procurement-field-findings-u-011) | — |
| P1–P3 | AC-33–AC-36; catatan AC-30 (UAT-1 mencakup evaluasi penawaran sintetis yang dicatat petugas); DC-088–DC-091; BR-036–BR-039; INV-023/024; catatan cara membaca WF-005–WF-015 | [ACCEPTANCE_CRITERIA](../01-product/ACCEPTANCE_CRITERIA.md#amandemen-kompatibilitas-2026-10-30), [BUSINESS_RULES](../02-domain/BUSINESS_RULES.md#br-036), [WORKFLOW_CONTRACTS](../03-workflows/WORKFLOW_CONTRACTS.md#tambahan-2026-10-30--temuan-lapangan-pengadaan) | — |
| P4 model data | Lingkup penghargaan; pemilihan (pemilihan ulang atas lingkup gagal); skema evaluasi beku dengan tahap/kriteria; penawaran imutabel per lingkup dengan rincian harga; pembukaan tanpa kolom gugur; putaran evaluasi; hasil kriteria dan tahap; klarifikasi; negosiasi per lingkup; opsi katalog; hasil berversi per lingkup; peringkat dengan mode penghargaan; reviu hasil; SPPBJ dan kontrak per pemenang dengan lingkup aktif eksklusif; baris kontrak berlingkup; pemeriksaan berpenugasan; permintaan pembayaran; penilaian kinerja penyedia | [DATA_MODEL §14.3](../04-architecture/DATA_MODEL.md#143-pemilihan), [§14.4](../04-architecture/DATA_MODEL.md#144-kontrak-dan-pelaksanaan), [§13](../04-architecture/DATA_MODEL.md#13-vendor), §1, §2, §14.2, §19 aturan 5 | DI-05–DI-07, DI-17, DI-21–DI-30 |
| P4 kebijakan dan dokumen | Keluarga `procurement.evaluation` dan `vendor.performance`; matriks penerapan langkah/dokumen dengan `PENDING_VALIDATION`; katalog dokumen evaluasi, per pemenang, kinerja, permintaan pembayaran dan dokumen teknis unggahan | [POLICY_REGISTRY §1](../04-architecture/POLICY_REGISTRY.md#1-keluarga-kebijakan), [§5](../04-architecture/POLICY_REGISTRY.md#5-contoh-isi-lain-bentuk-bukan-nilai-resmi); [DOCUMENT_ENGINE §3](../04-architecture/DOCUMENT_ENGINE.md#3-katalog-jenis-dokumen-awal) | DI-16 |
| P5 | Kapabilitas pemilihan, skema, pembukaan, penyelesaian evaluasi, klarifikasi/negosiasi, reviu hasil; peran `TECHNICAL_REVIEWER`; visibilitas evaluasi dan portal; AUTHZ-46–AUTHZ-51 | [ACCESS_CONTROL §4.2](../05-security/ACCESS_CONTROL.md#42-pengadaan), [§10](../05-security/ACCESS_CONTROL.md#10-skenario-regresi-otorisasi) | AUTHZ-46–AUTHZ-51 |
| P6 | Invarian eksklusivitas lingkup, imutabilitas penawaran, skema beku, alasan wajib; urutan kunci dengan `proc_selections`; enam race baru; PB-12 | [CONCURRENCY_AND_INTEGRITY §2–§4](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#3-invarian-yang-ditegakkan-basis-data), [PERFORMANCE_AND_SCALE §2](../06-api-performance/PERFORMANCE_AND_SCALE.md#2-anggaran-performa) | DI-22, DI-25, DI-27 (K) |
| P7 | Ruang kerja evaluasi (matriks, panel kriteria, penampil penawaran baca-saja teraudit, kemajuan, perbandingan berlabel simulasi, tanpa aksi tetapkan pemenang); PG-122–PG-124; J-19/J-20; komposit baru | [INTERACTION_PATTERNS §10](../07-ux-design/INTERACTION_PATTERNS.md#10-ruang-kerja-evaluasi), [INFORMATION_ARCHITECTURE §3.2](../07-ux-design/INFORMATION_ARCHITECTURE.md#32-pengadaan), [USER_JOURNEYS](../07-ux-design/USER_JOURNEYS.md) | J-19, J-20 |
| P8/P10 | DI-21–DI-30, DI-05/06/07/17 diperbarui; UAT-1 mencakup evaluasi; UAT-P13/P14 | [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib), [UAT_PLAN](../10-release/UAT_PLAN.md) | — |
| P11 | 21 kontrak Task diperbarui tanpa Task baru (jumlah tetap 78; TASK-032 menjadi HIGH; TASK-046 kini bergantung juga pada TASK-042); prasyarat Owner D-26 pada TASK-035 dihapus karena dijawab OD-35 | [TASK-032](../11-tasks/TASK-032.md)–[TASK-037](../11-tasks/TASK-037.md), [TASK_PLAN](../11-tasks/TASK_PLAN.md), [TRACEABILITY_MATRIX](../11-tasks/TRACEABILITY_MATRIX.md) | — |
| Keputusan Owner | Pertanyaan ya/tidak jumlah pemenang di D-26 dihapus; pertanyaan validasi kustodian (lot/item, evaluasi, dokumen per metode/jenis, pbj.uny.ac.id, kinerja penyedia) ditambahkan; catatan tafsir UAT-1 pada D-24 | [OWNER_DECISION_PACKAGE](OWNER_DECISION_PACKAGE.md) | — |

## 4. Pertanyaan pemeriksaan

Bagian A:

1. Apakah setiap temuan SR tertutup secara substansi — constraint basis data yang efektif, kontrak lintas fase, kriteria penerimaan Task dan uji regresi — bukan hanya teks?
2. Apakah koreksi memperkenalkan aturan pengadaan, akuntansi, organisasi atau keamanan baru? Perhatikan larangan aktivasi bertanggal mundur (diturunkan dari P3 TR-286), penundaan koreksi run penyusutan sampai kebijakan tersedia (BR-017, GAP-007/008; WF-022 TR-214), dan run berurutan menurut periode (apakah periode boleh dilewati tetap urusan kebijakan GAP-007). Asumsi satu pemenang per paket dari `bfa6501` sudah digantikan bagian B.
3. Apakah urutan pembuatan skema tetap sah (DATA_MODEL §19 aturan 5) dan apakah keadaan WF-022 dan WF-029 P3 yang disetujui hanya dipetakan, tidak diubah?

Bagian B:

4. **Pemisahan resmi dan praktik** — Apakah ada praktik staf (tiga penawaran PL, RKS/SDP, fungsi pbj.uny.ac.id) atau tafsir memorandum yang terbaca sebagai aturan wajib? Apakah locator RG-07 cocok dengan SRC-048?
5. **Beberapa pemenang** — Apakah rantai lingkup → penawaran → hasil kriteria/tahap → peringkat → SPPBJ → kontrak → baris kontrak → serah terima benar-benar mencegah lingkup dihargakan dua kali dan dokumen hilir melintasi lingkup pemenang lain (FK komposit, unique parsial, trigger), sambil tetap mendukung kontrak payung bila kebijakan mengizinkan? Apakah aktivasi cadangan per lingkup aman terhadap SPPBJ/kontrak lingkup lain?
6. **Integritas evaluasi** — Apakah skema beku, penawaran imutabel, pembukaan tanpa gugur, alasan wajib, derivasi hasil tahap tanpa penimpaan dan putaran evaluasi ulang cukup dan konsisten dengan P3 (TR-081–TR-085 dan TR-084 BLOCKED_BY_GAP)? Apakah ada skor/bobot/ambang yang dikarang aplikasi?
7. **Kegunaan evaluasi** — Apakah ruang kerja evaluasi (INTERACTION_PATTERNS §10) dan J-19/UAT-P13 menjawab prioritas Owner dengan sedikit klik, jejak bukti dan tanpa aksi yang tampak resmi sebelum sah?
8. **Konsistensi lintas fase dan Task** — Apakah DATA_MODEL, POLICY_REGISTRY, ACCESS_CONTROL, CONCURRENCY_AND_INTEGRITY, halaman P7, DI dan 21 Task yang diperbarui saling cocok; apakah pemilik DI dan urutan pembuatan skema benar?
9. **Kewenangan** — Apakah perubahan tetap dalam AUTH-012, tanpa menyelesaikan GAP, tanpa mengubah kata-kata P0–P3 yang disetujui, dan tanpa mengambil keputusan yang seharusnya milik Owner atau kustodian?

## 5. Yang tidak berubah

- Kata-kata P0–P3 yang disetujui tidak diubah; P1/P2 hanya mendapat butir tambahan di bagian amandemen, P3 hanya catatan cara membaca dan hitungan.
- Stack, modul, model akses tiga kelompok peran, ADR, rencana rilis dan model eksekutor tidak berubah. Jumlah Task tetap 78; tidak ada Task baru; urutan ID dan arah dependensi tetap.
- Keputusan Owner D-01–D-29 tetap; isi D-26 diperbarui seperti di atas dan D-24 mendapat catatan tafsir.

## 6. Bukti pemeriksaan mandiri

[P0_P11_PLANNING_QUALITY_GATE](../00-governance/evidence/P0_P11_PLANNING_QUALITY_GATE.md) diperbarui: QG-P-01–QG-P-15 dijalankan ulang untuk bagian B (tautan/anchor, ID termasuk keluarga DI, cakupan termasuk AC-01–AC-36 dan DI-01–DI-30, urutan skema, kepemilikan DI, pelestarian byte berkas tata kelola, identitas Git). Hasil identitas dan atribusi untuk commit bagian B dilaporkan bersama permintaan ini.

## 7. Format hasil yang diminta

```text
VERDICT: READY_FOR_OWNER_APPROVAL | CORRECTIONS_REQUIRED
IDENTITAS YANG DIREVIEW: <SHA HEAD>
DISPOSISI TEMUAN BAGIAN A: SR-P1-01 … SR-P2-01 = CLOSED | OPEN (alasan)
TEMUAN BAGIAN B DAN TEMUAN BARU (bila ada):
| ID | Tingkat | Berkas § | Masalah | Koreksi yang diminta |
CATATAN UNTUK OWNER: <hal yang memerlukan keputusan Owner>
```

Laporan dan pertanyaan kepada Owner ditulis dalam Bahasa Indonesia. Verdict bukan persetujuan Owner.
