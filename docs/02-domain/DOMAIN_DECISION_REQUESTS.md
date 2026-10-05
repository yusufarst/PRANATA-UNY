# Domain decision requests — PRANATA UNY

Status: APPROVED | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P2 | Preparation: [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006), completed | Approval: [APPR-003](../00-governance/APPROVAL_RECORDS.md#appr-003) | Checkpoint: [AUTH-007](../00-governance/APPROVAL_RECORDS.md#auth-007), automatically completed after verified publication

Dokumen ini mengelompokkan kebutuhan validasi yang belum tersedia; bukan pengganti [GAP_REGISTER](../00-governance/GAP_REGISTER.md), bukan keputusan Owner baru, dan bukan permintaan memulai fase berikutnya. Semua GAP tetap OPEN. [DOMAIN_TRACEABILITY](DOMAIN_TRACEABILITY.md) memiliki kelas kontribusi P2 tiap GAP; [BUSINESS_RULES](BUSINESS_RULES.md) memiliki rumusan/status aturan. DR-01–DR-06 adalah locators kebutuhan keputusan, **bukan execution Tasks**.

Tidak ada pertanyaan tambahan yang wajib dijawab untuk merampungkan kandidat semantik P2 ini. Pada review/validasi berikutnya, mintalah bukti berikut secara berkelompok kepada Owner bersama custodian/domain specialist yang relevan. Safe default berarti menahan klaim/tindakan resmi yang bergantung pada aturan belum tervalidasi; tidak berarti mengganti aturan itu dengan proposal agent.

## DR-01 — Kewenangan pengadaan, RUP, metode dan dokumen

- **Keputusan/bukti dibutuhkan:** prosedur pengadaan dan RUP yang current/approved untuk seluruh jenis Unit Organisasi yang relevan, metode awal P1 yang benar-benar berlaku, pembagian tanggung jawab/penugasan/delegasi, serta daftar varian inti dokumen/nomor/penandatangan. Untuk SPPBJ, validasi penyusun, pemeriksa, penerbit dan penanda tangan secara terpisah per metode.
- **Mengapa:** sumber draft/historis membuktikan konsep dan variasi, tetapi metadata persetujuan belum tersedia; lane Pokja preparation dan PPK issuance/signing belum dapat menjadi universal mapping. Tender-labelled output bercampur direct-procurement wording.
- **Terblokir:** GAP-001/002/003/004/017, tergantung GAP-013/019; official procedure/action authority/variant applicability, BR-004/005/010/011; AC-06/07/08/11/12.
- **Bukti tersedia:** [PROCUREMENT_SOURCE_REVIEW](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md), PR-F01–05; PROC-02 table 6 step 14, PROC-06 table 6 rows 3–8, PROC-08 table 1 row 3, PROC-10 step 11, PROC-12 item l, PROC-21/22 dan PROC-30 Input/Isian Nomor. Semua masih observation.
- **Safe default:** gunakan konsep/referensi/bukti dengan status uncertainty; jangan mengklaim urutan/nomor/signature/issuer resmi atau menjalankan aksi resmi bergantung pada mapping yang belum disahkan. Validasi tidak mensyaratkan integrasi SiRUP/SPSE/LPSE V1.

## DR-02 — Kebijakan Asset/Finance dan batas downstream/KDP

- **Keputusan/bukti dibutuhkan:** instrumen accounting berlaku dengan versi/effective dates, depreciation/amortization commencement/cutoff/rate/life/rounding termasuk perlakuan tanggal harian; capitalization category/comparator/currency/basis; efek correction/development/reclassification/removal/KDP dan historical periods. Procurement/Asset/Finance bersama mengonfirmasi inspected acceptance/handover/payment event, classification, penerima tanggung jawab dan penerimaan record hilir.
- **Mengapa:** legacy arithmetic, menu dan threshold table tidak memberikan authority accounting; BAST dan progress menunjukkan bukti yang berbeda dari pengakuan definitif.
- **Terblokir:** GAP-007/008/009/018 dan authority GAP-013; BR-013/016/017/018/021/022; AC-13–16/18/20; official report/definitive recognition.
- **Bukti tersedia:** [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md), ASSET-03/04/05/06/10/11/12; [procurement review](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md), PROC-02 steps 16–20, PROC-04 k–l, PROC-30 KO2:KV2. BMU need berasal dari U-002/OD-23; bentuk/otoritas output belum terverifikasi.
- **Safe default:** pertahankan kandidat/draft/provenance dan perbedaan Asset/Persediaan/KDP; jangan membuat nilai resmi, daily formula, threshold, premature classification atau penerimaan definitif dari bukti tunggal. Minta contoh hasil otoritatif yang dapat direkonsiliasi bersama policy.

## DR-03 — Kamus Persediaan dan P01/P02

- **Keputusan/bukti dibutuhkan:** Finance/inventory custodian menyediakan versioned dictionary dengan meaning/sign/effective date/historical rules dan menjelaskan P01/P02 sebagai alias, beda versi, typo atau operasi berbeda, disertai approved mapping dan reconciliation examples.
- **Mengapa:** raw stream dan report/journal memberikan kode opname berbeda tanpa penerbit kamus yang tervalidasi.
- **Terblokir:** GAP-005/006; BR-019/020; accepted ledger calculation, normalization/import/reconciliation semantics; AC-17/19/20.
- **Bukti tersedia:** [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#inventory-transaction-code-discrepancy), ASSET-01 Sheet2 column K / ASSET-02 column 11, ASSET-04 Neraca Psd E6/C44, ASSET-05 Sheet1 F4.
- **Safe default:** raw code dan provenance tetap dibedakan dari canonical interpretation; dependent official mapping/stock calculation tidak dijalankan sampai validation tersedia.

## DR-04 — Organisasi, penugasan dan representasi aktor

- **Keputusan/bukti dibutuhkan:** Owner mengonfirmasi compact role families, Organization Unit scope, process/package assignment, formal/specific-action responsibility, delegation/replacement dan segregation need; validasi siapa mewakili perusahaan/PIC. Account provisioning/recovery detail menjadi handoff P5/P9 tersendiri, bukan kontrak P2.
- **Mengapa:** enam dimensi pada [DOMAIN_RESPONSIBILITIES](DOMAIN_RESPONSIBILITIES.md) dapat menjelaskan konteks tetapi tidak menetapkan entitlement atau kewenangan jabatan.
- **Terblokir:** GAP-014; terkait GAP-003/004 serta GAP-015 di luar P2; BR-002/007/008; exact backend policies/actor representation; AC-02/03/04/09/10/22.
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

GAP-016 workload/capacity adalah kewajiban P4/P6/P8/P9 dan tidak ditanyakan sebagai keputusan domain P2. GAP-020 borrowing tetap FUTURE/di luar V1 yang disetujui; tidak membutuhkan re-intake atau penambahan capability P2. Detailed account/retention/security/operations decisions memerlukan fase berotorisasi terpisah. Setiap jawaban kelak harus memiliki source/version/date, actual approval/decision reference dan targeted impact review sebelum mengubah record pemilik/GAP. Review kandidat P2 tetap langkah aman berikutnya.
