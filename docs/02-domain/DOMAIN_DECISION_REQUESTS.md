# Domain decision requests — PRANATA UNY

Status: Approved baseline: published 0e3f7de · Amendments approved: APPR-005 (AUTH-010 remediation), APPR-006 (AUTH-012 compatibility amendment) | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P2 | Preparation: [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006), completed | Approval: [APPR-003](../00-governance/APPROVAL_RECORDS.md#appr-003) | Checkpoint: [AUTH-007](../00-governance/APPROVAL_RECORDS.md#auth-007), automatically completed after verified publication

Dokumen ini mengelompokkan kebutuhan validasi yang belum tersedia; bukan pengganti [GAP_REGISTER](../00-governance/GAP_REGISTER.md), bukan keputusan Owner baru, dan bukan permintaan memulai fase berikutnya. Semua GAP tetap OPEN. [DOMAIN_TRACEABILITY](DOMAIN_TRACEABILITY.md) memiliki kelas kontribusi P2 tiap GAP; [BUSINESS_RULES](BUSINESS_RULES.md) memiliki rumusan/status aturan. DR-01–DR-07 adalah locators kebutuhan keputusan, **bukan execution Tasks**.

Tidak ada pertanyaan tambahan yang wajib dijawab untuk merampungkan kandidat semantik P2 ini. Pada review/validasi berikutnya, mintalah bukti berikut secara berkelompok kepada Owner bersama custodian/domain specialist yang relevan. Safe default berarti menahan klaim/tindakan resmi yang bergantung pada aturan belum tervalidasi; tidak berarti mengganti aturan itu dengan proposal agent.

## DR-01 — Kewenangan pengadaan, RUP, metode dan dokumen

- **Keputusan/bukti dibutuhkan:** prosedur pengadaan dan RUP yang current/approved untuk seluruh jenis Unit Organisasi yang relevan, metode awal P1 yang benar-benar berlaku, pembagian tanggung jawab/penugasan/delegasi, serta daftar varian inti dokumen/nomor/penandatangan. Untuk SPPBJ, validasi penyusun, pemeriksa, penerbit dan penanda tangan secara terpisah per metode.
- **Mengapa:** sumber draft/historis membuktikan konsep dan variasi, tetapi metadata persetujuan belum tersedia; lane Pokja preparation dan PPK issuance/signing belum dapat menjadi universal mapping. Tender-labelled output bercampur direct-procurement wording.
- **Terblokir:** GAP-001/002/003/004/017, tergantung GAP-013/019; official procedure/action authority/variant applicability, BR-004/005/010/011; AC-06/07/08/11/12.
- **Bukti tersedia:** [PROCUREMENT_SOURCE_REVIEW](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md), PR-F01–05; PROC-02 table 6 step 14, PROC-06 table 6 rows 3–8, PROC-08 table 1 row 3, PROC-10 step 11, PROC-12 item l, PROC-21/22 dan PROC-30 Input/Isian Nomor. Semua masih observation.
- **Safe default:** gunakan konsep/referensi/bukti dengan status uncertainty; jangan mengklaim urutan/nomor/signature/issuer resmi atau menjalankan aksi resmi bergantung pada mapping yang belum disahkan. Validasi tidak mensyaratkan integrasi SiRUP/SPSE/LPSE V1.

## DR-02 — Kebijakan Asset/Finance dan batas downstream/KDP

- **Keputusan/bukti dibutuhkan:** instrumen accounting berlaku dengan versi/effective dates, depreciation/amortization commencement/cutoff/rate/life/rounding termasuk perlakuan tanggal harian; capitalization category/comparator/currency/basis; efek correction/development/reclassification/removal/KDP dan historical periods. Procurement/Asset/Finance bersama mengonfirmasi inspected acceptance/handover/payment event, classification, penerima tanggung jawab dan penerimaan record hilir. Taksonomi Klasifikasi Aset berversi beserta pemetaannya ke kategori/akun/pelaporan, versi dan tanggal berlaku (GAP-022); definisi/ekspansi, bentuk, makna temporal dan kewenangan BMU (GAP-024).
- **Mengapa:** legacy arithmetic, menu dan threshold table tidak memberikan authority accounting; BAST dan progress menunjukkan bukti yang berbeda dari pengakuan definitif.
- **Terblokir:** GAP-007/008/009/018/022/024 dan authority GAP-013; BR-013/016/017/018/021/022; AC-13–16/18/20; official report/definitive recognition.
- **Bukti tersedia:** [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md), ASSET-03/04/05/06/10/11/12; [procurement review](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md), PROC-02 steps 16–20, PROC-04 k–l, PROC-30 KO2:KV2. BMU need berasal dari U-002/OD-23; bentuk/otoritas output belum terverifikasi.
- **Safe default:** pertahankan kandidat/draft/provenance dan perbedaan Asset/Persediaan/KDP; jangan membuat nilai resmi, daily formula, threshold, premature classification atau penerimaan definitif dari bukti tunggal. Minta contoh hasil otoritatif yang dapat direkonsiliasi bersama policy.

## DR-03 — Kamus Persediaan dan P01/P02

- **Keputusan/bukti dibutuhkan:** Finance/inventory custodian menyediakan versioned dictionary dengan meaning/sign/effective date/historical rules dan menjelaskan P01/P02 sebagai alias, beda versi, typo atau operasi berbeda, disertai approved mapping dan reconciliation examples. Untuk operasi multi-gudang (OD-25): daftar gudang resmi, kustodian, semantik transaksi, bukti pengeluaran dan bentuk penerima, kewenangan transfer antar gudang, perlakuan in-transit dan selisih, persetujuan hitung fisik/penyesuaian serta format laporan gudang (GAP-025).
- **Mengapa:** raw stream dan report/journal memberikan kode opname berbeda tanpa penerbit kamus yang tervalidasi.
- **Terblokir:** GAP-005/006/025; BR-019/020/030; accepted ledger calculation, normalization/import/reconciliation semantics; AC-17/19/20.
- **Bukti tersedia:** [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#inventory-transaction-code-discrepancy), ASSET-01 Sheet2 column K / ASSET-02 column 11, ASSET-04 Neraca Psd E6/C44, ASSET-05 Sheet1 F4.
- **Safe default:** raw code dan provenance tetap dibedakan dari canonical interpretation; dependent official mapping/stock calculation tidak dijalankan sampai validation tersedia.

## DR-04 — Organisasi, penugasan dan representasi aktor

- **Keputusan/bukti dibutuhkan:** Owner mengonfirmasi compact role families, Organization Unit scope, process/package assignment, formal/specific-action responsibility, delegation/replacement dan segregation need; validasi siapa mewakili perusahaan/PIC. Account provisioning/recovery detail menjadi handoff P5/P9 tersendiri, bukan kontrak P2. Kewenangan pemeliharaan struktur/riwayat Organization Unit, penugasan/penugasan ulang dan data referensi/master, beserta tanggal efektif dan supersession-nya (GAP-021).
- **Mengapa:** enam dimensi pada [DOMAIN_RESPONSIBILITIES](DOMAIN_RESPONSIBILITIES.md) dapat menjelaskan konteks tetapi tidak menetapkan entitlement atau kewenangan jabatan.
- **Terblokir:** GAP-014/021; terkait GAP-003/004 serta GAP-015 di luar P2; BR-002/007/008; exact backend policies/actor representation; AC-02/03/04/09/10/22.
- **Bukti tersedia:** [OD-09/10/20](../00-governance/DECISION_LOG.md#od-09), OD-23, P1 stakeholder needs; historical position labels PROC-06/30 hanya supporting context.
- **Safe default:** jangan menafsirkan keluarga role, pilihan workspace atau label jabatan sebagai izin; jangan mengasumsikan akses informasi penyedia lain atau self-registration. Owner Super Admin direction dipertahankan tanpa pengecualian audit/bisnis baru.

## DR-05 — Rekonsiliasi, waiting party dan penerimaan periode

- **Keputusan/bukti dibutuhkan:** Owner bersama Finance/Asset/Persediaan/Procurement mengonfirmasi scope/source comparisons, hubungan yang diharapkan, penanggung jawab/waiting party, exception resolution, acceptance/signoff, cadence dan official report/period finality. Validasi equality/continuity yang dipakai dengan authoritative examples.
- **Mengapa:** combined reports/recaps/journal menunjukkan kebutuhan rekonsiliasi, belum membuktikan siapa menandatangani atau apa yang membuat hasil/period final.
- **Terblokir:** GAP-010; terkait GAP-007/012; BR-022/023; AC-05/16/17/20, official resolved/period-closure claims.
- **Bukti tersedia:** [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md), ASSET-03–09 dan historical import/reconciliation findings; PROC-32/ASSET-09 SPJ/treasurer labels bukan signoff policy.
- **Safe default:** catat lingkup, sumber, selisih, penjelasan dan uncertainty; jangan menyatakan selisih resolved atau laporan/period resmi final hanya karena angka sama atau explanation sudah diketik.

## DR-06 — Keberlakuan sumber, historical intake dan cutover authority

- **Keputusan/bukti dibutuhkan:** source custodian mengonfirmasi issuer/current version/applicability/supersession/latest legacy artifact; validate canonical source-field/output meanings dan workbook dependency trust. Authorized later planning menetapkan identity/duplicate/cleansing/accepted sample semantics serta system/write ownership, reconciliation/fallback/rollback/signoff selama cutover.
- **Mengapa:** heterogeneous headers, duplicate location names, multiple report variants, cached workbook outputs dan external reference tidak membuktikan authoritative values atau migration readiness.
- **Terblokir:** GAP-011/012/013/019; BR-010/024/025/029; AC-11/19/20; dependent policy authority dan import/cutover acceptance. Technical import/cutover implementation berada P4/P6/P8/P9/P10.
- **Bukti tersedia:** [SOURCE_INVENTORY](../00-governance/SOURCE_INVENTORY.md), [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md), ASSET-01/02/04/08/12; [PROCUREMENT_SOURCE_REVIEW](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md), PR-F01/04, PROC-30 SPJ PL C1267 / PROC-31 header correspondence.
- **Safe default:** source/candidate/validation decision tetap terpisah dari accepted domain record; ambiguous identity/value dan historical precedence tetap uncertainty; jangan overwrite raw source, import credential plaintext, atau menentukan production write ownership dari proposal.

## DR-07 — Retensi, privasi, arsip dan material sensitif kredensial

- **Keputusan/bukti dibutuhkan:** kewajiban retensi, arsip, penghapusan, anonimisasi dan data pribadi yang berlaku bagi record PRANATA, termasuk identitas penerima pengeluaran menurut OD-25, serta penanganan material historis yang sensitif kredensial. Bukti yang diperlukan: kewajiban hukum/institusi yang berlaku, jadwal retensi yang disetujui, aturan penghapusan/anonimisasi dan penanganan material sensitif kredensial yang disetujui.
- **Mengapa:** disposisi lifecycle P2 menahan restore/soft-delete/hard-delete/anonymize (B-pemulihan/B-retensi/B-privasi); konteks penerima OD-25 tidak boleh menjadi field data pribadi tanpa aturan; sumber historis tertentu memuat pola kredensial plaintext.
- **Terblokir:** GAP-023; konsep dan lifecycle terdampak: DC-077 (makna siklus hidup/status), konteks penerima pengeluaran (BR-030) dan sumber historis (DC-073/074; INV-016); BR-026, INV-017.
- **Bukti tersedia:** OD-25 (U-005); kode disposisi pada [DOMAIN_LIFECYCLES](DOMAIN_LIFECYCLES.md); batas sumber sensitif pada [SOURCE_INVENTORY](../00-governance/SOURCE_INVENTORY.md) dan [PRODUCTION_DATA_SAFETY](../00-governance/PRODUCTION_DATA_SAFETY.md). Tidak ada instrumen retensi/privasi yang diinspeksi.
- **Safe default:** catat hanya identitas/konteks minimum yang disyaratkan aturan yang disetujui; jangan mendefinisikan field data pribadi, menghapus/menganonimkan record atau mereproduksi kredensial. Fase pemilik P2/P5/P9/P10; permintaan ini tidak menjawabnya.

GAP-016 workload/capacity adalah kewajiban P4/P6/P8/P9 dan tidak ditanyakan sebagai keputusan domain P2. GAP-020 borrowing tetap FUTURE/di luar V1 yang disetujui (OD-24); tidak membutuhkan re-intake atau penambahan capability P2. Detailed account/retention/security/operations decisions memerlukan fase berotorisasi terpisah. Setiap jawaban kelak harus memiliki source/version/date, actual approval/decision reference dan targeted impact review sebelum mengubah record pemilik/GAP. Status fase dan otorisasi terkini dimiliki PHASE_STATUS dan CURRENT_HANDOFF.

## Amandemen kompatibilitas 2026-10-30

Disusun di bawah [AUTH-012](../00-governance/APPROVAL_RECORDS.md#auth-012); status **VERIFYING / belum disetujui**. Isi yang disetujui APPR-003 tidak diubah; bagian ini menambahkan cakupan DC-080–DC-087, BR-031–BR-035, INV-020–INV-022 dan GAP-026–GAP-029. Tidak ada GAP yang diselesaikan.

### DR-08 — Rezim regulasi, inkonsistensi instrumen dan kanal resmi

- **Keputusan/bukti dibutuhkan:** pemetaan setiap sumber dana UNY ke rezim pengadaan dan tanggal efektifnya; keberlakuan PR 34/2023 dan perubahan Perpres; tafsir tertulis atas GAP-027 huruf a–e; kanal resmi per metode/langkah.
- **Mengapa:** BR-031 hanya aman bila rezim dan versi kebijakan diketahui; DC-080/081 tidak boleh diisi dari ambang nasional untuk paket rezim UNY.
- **Terblokir:** GAP-026/027/029; DC-080/081/087; BR-031/034; INV-022.
- **Bukti tersedia:** SRC-048–SRC-050, SRC-052 (provisional), [REGULATORY_SOURCE_REVIEW](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md).
- **Safe default:** versi kebijakan DRAFT berlabel simulasi; penetapan metode oleh manusia dengan alasan.

### DR-09 — Anggaran, komitmen dan operasi gudang

- **Keputusan/bukti dibutuhkan:** sumber rujukan anggaran resmi, identifier dan tanggung jawab komitmen (GAP-028); aturan operasional gudang ALP/PSRT untuk permintaan, serah terima, konfirmasi, pengembalian dan pemeriksaan (GAP-025).
- **Mengapa:** BR-033/035 dan INV-020 memerlukan isi kebijakan yang tervalidasi sebelum aksi resmi di produksi.
- **Terblokir:** GAP-025/028, terkait GAP-023; DC-082/084–086.
- **Bukti tersedia:** OD-26/30/31, SRC-058.
- **Safe default:** catat referensi dan bukti; peringatkan tanpa memblokir berdasarkan saldo keuangan; tidak ada saldo stok negatif.

### DR-10 — Evaluasi, lingkup penghargaan dan pascapenghargaan

- **Keputusan/bukti dibutuhkan:** metode evaluasi yang diizinkan dan bawaan per metode/jenis, aturan koreksi aritmetik dan pembanding HPS, ambang lulus, jumlah pemenang cadangan, masa sanggah/debriefing dan penanda tangan berita acara; metode/jenis yang mengizinkan penghargaan per lot atau item dan cara pengumuman, SPPBJ serta kontraknya; daftar dokumen per metode × jenis × nilai (termasuk arti RKS/SDP); fungsi pbj.uny.ac.id; skala penilaian kinerja penyedia dan dokumen permintaan pembayaran.
- **Mengapa:** BR-036–BR-039 dan INV-023/024 aman sebagai kapabilitas, tetapi aksi resmi memerlukan aturan per metode yang tervalidasi; Owner sudah menetapkan bahwa beberapa pemenang per paket harus didukung (OD-35).
- **Terblokir:** GAP-003/013/017/018/026/027/029; DC-036/039/088–091.
- **Bukti tersedia:** SRC-048 ([RG-07](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled)), SRC-068 (temuan lapangan, praktik), SRC-052 (provisional).
- **Safe default:** skema dari dokumen pengadaan dibekukan; perhitungan berlabel simulasi; praktik setempat hanya informatif; tidak ada penetapan pemenang otomatis.
