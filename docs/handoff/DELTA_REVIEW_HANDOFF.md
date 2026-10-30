# Handoff pemeriksaan delta — koreksi Second Review `48c2bc5`

Status: P0–P11 planning candidate — VERIFYING (AUTH-012) | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Untuk: Second Review Agent independen (ChatGPT)

Sesuai GOV-11, koreksi atas review substantif diverifikasi dengan **pemeriksaan delta**: memeriksa apakah setiap temuan tertutup dan apakah koreksi menimbulkan masalah baru, bukan mereview ulang seluruh kandidat. Handoff lengkap tetap [SECOND_REVIEW_HANDOFF](SECOND_REVIEW_HANDOFF.md).

## 1. Identitas

| Unsur | Nilai |
|---|---|
| Repositori | `https://github.com/yusufarst/PRANATA-UNY`, cabang `docs/p0-p11-planning`, PR draft #1 (tidak untuk di-merge sebelum keputusan Owner) |
| Identitas yang direview sebelumnya | `48c2bc5417d28dfb4820b63d551a75e2d5754d61` — verdict **CORRECTIONS_REQUIRED**: SR-P1-01–SR-P1-05, SR-P2-01 |
| Identitas delta | SHA HEAD cabang yang dilaporkan bersama permintaan ini: satu commit koreksi di atas `48c2bc5` (dokumen tidak dapat memuat hash commit yang memuatnya) |
| Diff yang direview | `git diff 48c2bc5..<SHA delta>` |
| Status | Semua kandidat tetap VERIFYING / belum disetujui; 0 Task READY; GAP-001–GAP-029 OPEN; tidak ada kode aplikasi |

## 2. Peta koreksi per temuan

Disposisi lengkap beserta hasil verifikasi pada `48c2bc5` ada di [P0_P11_PLANNING_QUALITY_GATE — Independent Second Review](../00-governance/evidence/P0_P11_PLANNING_QUALITY_GATE.md#independent-second-review-of-48c2bc5). Semua temuan terbukti (SUBSTANTIATED) dan dikoreksi; cacat sekelas yang ditemukan saat verifikasi ikut dikoreksi.

| Temuan | Inti koreksi | Tempat memeriksa | Uji regresi |
|---|---|---|---|
| SR-P1-01 Run penyusutan | Unique run resmi mencakup FAILED; tanpa SUPERSEDED untuk run resmi; CANCELLED hanya tanpa baris; protokol lanjutan run yang sama (VALIDATION → LINES → PROJECTION, checkpoint tahan lama, compare-and-set `FAILED → RUNNING` atas permintaan berwenang, penjaga worker usang, proyeksi aditif berpenjaga `last_depreciation_period`, periode berurutan); koreksi resmi menunggu kebijakan | [CONCURRENCY_AND_INTEGRITY §6.1](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#61-protokol-run-penyusutan-resmi), §3–§4; [DATA_MODEL §16](../04-architecture/DATA_MODEL.md#16-asset) (`asset_depreciation_runs`, `asset_depreciation_lines`, kolom proyeksi `asset_assets`), §19 aturan 5; [TASK-066](../11-tasks/TASK-066.md) | DI-01–DI-04 |
| SR-P1-02 Konsistensi induk | FK komposit `(rujukan, lingkup) → induk(id, lingkup)`; kolom lingkup tidak dapat diubah; constraint trigger untuk perusahaan kontrak = pemenang dan penugasan kontribusi SPPBJ pada paket yang sama; satu WINNER dan peringkat unik per hasil; satu pemenang per paket dinyatakan sebagai **asumsi rancangan**, bukan aturan, dengan pertanyaan Owner baru | [DATA_MODEL §1](../04-architecture/DATA_MODEL.md#1-konvensi), [§14.3](../04-architecture/DATA_MODEL.md#143-pemilihan), [§14.4](../04-architecture/DATA_MODEL.md#144-kontrak-dan-pelaksanaan), [§15](../04-architecture/DATA_MODEL.md#15-inventory); [OWNER_DECISION_PACKAGE D-26](OWNER_DECISION_PACKAGE.md#f-penyelesaian-gap); TASK-033–TASK-037, TASK-046, TASK-052–TASK-054 | DI-05–DI-08, DI-17, DI-19 |
| SR-P1-03 Pembalikan dan identitas peristiwa | Unique identitas sumber pada `inv_stock_movements` dan `asset_transactions` (lepas dari kunci idempotensi); satu pembalikan penuh per entri dengan trigger pencocok; pembalikan stok membawa sumber koreksi dan alasan; pembalikan aset hanya atas transaksi terakhir yang belum dibalik; constraint trigger per jenis sumber; kolom ID gerakan sirkular dihapus | DATA_MODEL §1, §15, §16; [CONCURRENCY_AND_INTEGRITY §3–§4](../06-api-performance/CONCURRENCY_AND_INTEGRITY.md#3-invarian-yang-ditegakkan-basis-data); [ADR-0004](../adr/ADR-0004-append-only-ledgers.md); TASK-050, TASK-051, TASK-053–TASK-056, TASK-059 | DI-09–DI-11 |
| SR-P1-04 Versi template | Jendela berlaku `[effective_from, effective_to)` dengan `EXCLUDE` atas ACTIVE/SUPERSEDED untuk template, kebijakan dan klasifikasi; aktivasi atomik di bawah kunci induk; tanpa tanggal mundur (P3 TR-286); pemilihan template paling spesifik yang deterministik; keadaan WF-029 S4/S5/S6 diturunkan; satu skema penomoran ACTIVE per jenis dokumen | [DOCUMENT_ENGINE](../04-architecture/DOCUMENT_ENGINE.md); [POLICY_REGISTRY §2–§3](../04-architecture/POLICY_REGISTRY.md#2-lifecycle-versi); DATA_MODEL §1, §8, §11, §12, §16; TASK-018, TASK-020, TASK-021, TASK-058 | DI-12, DI-13, DI-18, DI-20 |
| SR-P1-05 Baris kontrak | Baris kontrak append-only per versi dengan FK `(contract_id, version_no)` dan unique per versi; rancangan di `draft_terms` sampai SIGNED; versi ORIGINAL dan versi adendum dibuat lengkap dalam satu transaksi; sekelas: baris HPS, penerimaan, opname, transfer, serah terima gudang dan BAST beku setelah status final | DATA_MODEL §1, §2, [§14.2](../04-architecture/DATA_MODEL.md#142-paket-hps-dan-penetapan-metode), §14.4, §15, §19 aturan 5; TASK-029, TASK-037, TASK-045, TASK-046, TASK-051, TASK-053, TASK-055, TASK-056 | DI-14, DI-15 |
| SR-P2-01 Provenans kebijakan majemuk | `policy_version_uses` append-only per subjek dan keluarga; rencana langkah mencatat `reference_date` dan `generation_reason`; regenerasi per keluarga penyumbang sebagai versi rencana baru; penetapan metode mencatat versi metode dan rezim | POLICY_REGISTRY §3; DATA_MODEL §1, §8, §14.2; [ADR-0003](../adr/ADR-0003-versioned-policy-registry.md); TASK-018, TASK-028, TASK-030 | DI-16 |

Katalog uji regresi: [TEST_STRATEGY §10](../08-testing/TEST_STRATEGY.md#10-skenario-integritas-data-wajib) — DI-01–DI-20, termasuk uji negatif langsung ke basis data dengan peran `pranata_app` dan uji konkurensi dua koneksi. Pemetaan DI → Task juga ada di [TRACEABILITY_MATRIX](../11-tasks/TRACEABILITY_MATRIX.md#skenario-integritas-data-di).

## 3. Pertanyaan pemeriksaan delta

1. Apakah setiap temuan tertutup secara substansi — constraint basis data yang efektif, kontrak lintas fase, kriteria penerimaan Task dan uji regresi — bukan hanya teks?
2. Apakah koreksi memperkenalkan aturan pengadaan, akuntansi, organisasi atau keamanan baru? Perhatikan khususnya: asumsi satu pemenang per paket (asumsi rancangan + pertanyaan D-26 + prasyarat READY TASK-035); larangan aktivasi bertanggal mundur (diturunkan dari P3 TR-286 "tidak pernah backdate menimpa riwayat"); penundaan koreksi run penyusutan sampai kebijakan tersedia (BR-017, GAP-007/008; WF-022 TR-214 "permintaan baru tidak menimpa hasil final"); run berurutan menurut periode (apakah periode boleh dilewati tetap urusan kebijakan GAP-007).
3. Apakah ada kontradiksi baru antara DATA_MODEL, CONCURRENCY_AND_INTEGRITY, POLICY_REGISTRY, DOCUMENT_ENGINE, TEST_STRATEGY §10 dan kontrak Task?
4. Apakah urutan pembuatan skema tetap sah (kolom FK yang ditambahkan kemudian menurut DATA_MODEL §19 aturan 5) dan apakah setiap DI dimiliki Task yang tepat?
5. Apakah keadaan WF-022 dan WF-029 P3 yang disetujui hanya dipetakan, tidak diubah?

## 4. Yang tidak berubah

- Tidak ada perubahan pada dokumen P0–P3 yang disetujui maupun kandidat amandemen P0–P3; perubahan hanya pada kandidat P4, P6, P8, P11, paket keputusan Owner, bukti mutu dan dokumen status/handoff.
- Arsitektur, modul, stack, model akses, perintah P6, halaman P7, rencana rilis dan model eksekutor tidak diubah. Jumlah Task tetap 78; dependensi dan kelas risiko tidak berubah.
- Keputusan Owner D-01–D-29 tidak berubah, kecuali satu pertanyaan tambahan di D-26 tentang jumlah pemenang per paket dan dampaknya pada READY TASK-035.

## 5. Bukti pemeriksaan mandiri

[P0_P11_PLANNING_QUALITY_GATE](../00-governance/evidence/P0_P11_PLANNING_QUALITY_GATE.md) diperbarui: QG-P-01–QG-P-15 dijalankan ulang (tautan/anchor, ID termasuk keluarga DI, cakupan termasuk DI-01–DI-20, urutan skema, kepemilikan DI, identitas Git). Hasil identitas dan atribusi untuk commit koreksi dilaporkan bersama permintaan ini.

## 6. Format hasil yang diminta

```text
VERDICT: READY_FOR_OWNER_APPROVAL | CORRECTIONS_REQUIRED
IDENTITAS YANG DIREVIEW: <SHA delta>
DISPOSISI TEMUAN: SR-P1-01 … SR-P2-01 = CLOSED | OPEN (alasan)
TEMUAN BARU (bila ada):
| ID | Tingkat | Berkas § | Masalah | Koreksi yang diminta |
CATATAN UNTUK OWNER: <hal yang memerlukan keputusan Owner>
```

Laporan dan pertanyaan kepada Owner ditulis dalam Bahasa Indonesia. Verdict bukan persetujuan Owner.
