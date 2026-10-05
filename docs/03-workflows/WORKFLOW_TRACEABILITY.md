# Ketertelusuran workflow — PRANATA UNY

Status: APPROVED (APPR-004) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P3 | Preparation: [AUTH-008](../00-governance/APPROVAL_RECORDS.md#auth-008) (HISTORICAL / COMPLETED) | Approval/checkpoint: [APPR-004](../00-governance/APPROVAL_RECORDS.md#appr-004) / [AUTH-009](../00-governance/APPROVAL_RECORDS.md#auth-009)

Dokumen ini memiliki **pemetaan P3 dan penilaian dampak workflow seluruh GAP**. [WORKFLOW_CATALOG](WORKFLOW_CATALOG.md) adalah indeks; [WORKFLOW_CONTRACTS](WORKFLOW_CONTRACTS.md) memiliki tujuan/batas/handoff; [STATE_TRANSITIONS](STATE_TRANSITIONS.md) memiliki keadaan/TR; [ROUTE_CONTRACTS](ROUTE_CONTRACTS.md) memiliki RT; [INTERACTION_CONTRACTS](INTERACTION_CONTRACTS.md) memiliki IX. Semua ID di sini adalah locator perencanaan, bukan Tasks atau kriteria penerimaan aplikasi yang sudah diuji.

[DECISION_LOG](../00-governance/DECISION_LOG.md) tetap memiliki OD-01–OD-23. [V1_SCOPE](../01-product/V1_SCOPE.md) dan [ACCEPTANCE_CRITERIA](../01-product/ACCEPTANCE_CRITERIA.md) tetap memiliki 20 CAP, 27 AC dan target NFR usulan. [DOMAIN_GLOSSARY](../02-domain/DOMAIN_GLOSSARY.md) / [DOMAIN_MODEL](../02-domain/DOMAIN_MODEL.md) memiliki 77 DC/9 area; [BUSINESS_RULES](../02-domain/BUSINESS_RULES.md) adalah satu-satunya pemilik wording/status/authority **29 BR / 18 INV**. P3 tidak mengganti rumusan sumber-sumber approved tersebut. [GAP_REGISTER](../00-governance/GAP_REGISTER.md) tetap memiliki status kanonik: semua 20 OPEN, tidak ada resolution atau GAP baru.

## Sumber yang dipakai dan batas pembuktian

P3 memakai **review aman yang telah tercatat**, bukan menginspeksi/mengunggah ulang original. Label PROC/ASSET/SRC di matriks berikut mengarah ke locators di sumber pemilik di bawah. File/page/sheet/range yang disebut memberi jejak pengamatan, tidak mengesahkan authority UNY.

| Pemilik bukti / locator tepat | Penggunaan P3 | Kekuatan dan batas |
|---|---|---|
| [AUTH-008](../00-governance/APPROVAL_RECORDS.md#auth-008), instruction Owner P3 §§4–46/51–64 | Mandat kontrak operational, safe skeleton, route/return dan interaction completeness | OWNER AUTHORIZATION untuk P3 saja; bukan APPR P3 atau jawaban prosedur/kebijakan resmi |
| [PR-F03](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f03-rup-and-organizational-request-handoffs-are-candidate-evidence): PROC-06 tabel 6 baris 3–8; PROC-08 tabel 1 baris 3–4; PROC-04 tabel 1 item a–l; PROC-13 halaman 1–3 | WF-003/004 intake/RUP serta asal–paket | OBSERVED_CURRENT_PROCESS; lane/template dan publication/date wording tidak membuktikan rantai resmi seluruh jenis unit |
| [PR-F02](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f02-sppbj-responsibility-needs-a-precise-activity-distinction): PROC-02 tabel 6 step 14; PROC-10 tabel 6 step 11; PROC-12 tabel 1 item l; PROC-22 halaman 1 | WF-011 empat responsibility SPPBJ | OBSERVED_CURRENT_PROCESS; preparation dan issuance/signing dapat berbeda, tetapi semua pemetaan per metode tetap belum disahkan |
| [PR-F05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f05-historical-procurement-outputs-need-method-specific-validation): PROC-14–22 halaman 1–2, khusus PROC-21 heading tender/body Pengadaan Langsung | WF-005/008–010 titik ekstensi metode, evaluation/result evidence | OBSERVED_CURRENT_PROCESS; bukan mandatory sequence, scoring, deadline, atau applicability dari filename |
| [Procurement file inventory](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#file-level-inventory): PROC-23–27 halaman 1–2 | WF-006 opening/workshop HPS/review desain/penawaran/negosiasi | OBSERVED_CURRENT_PROCESS; undangan menunjukkan bahan kegiatan, bukan kehadiran atau gate wajib universal |
| [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families): PROC-30 Input A2:KV2, AU2:BX2, CX2:DS2, DT2:HN2, KO2:KV2; Isian Nomor A2:AN2; Mail Merge; SPJ PL C1267; PROC-31 baris 1–2 | WF-007/008 reuse penyedia; WF-012–016 hubungan kontrak/progress/BAST/SPJ; WF-026/028 provenance/trust | OBSERVED_CURRENT_PROCESS; header/formula/cache tidak membuktikan nilai equivalence, field resmi, klausul/nomor/signature atau external dependency trust |
| [PR-U05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#candidate-unresolved-questions-for-the-central-gap-register): PROC-02 steps 16–20; PROC-04 item k–l; PROC-30 KO2:KV2; PROC-32/ASSET-09 Sheet1 A1:R22/heading A3:R5 | WF-014/015/016 batas acceptance/handover/payment/downstream | OBSERVED_CURRENT_PROCESS; BAST/scan/treasurer label tidak memilih event eligible, capitalisation, penerima pekerjaan atau signoff |
| [Asset source inventory](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#source-level-inventory): ASSET-03 Neraca A6:E7/Laporan Aset A6:K8/Lap. Penyusutan A6:J7; ASSET-08 metadata/representative headers; ASSET-11 halaman 1–2 | WF-017–021 register/lokasi/condition/change/removal | OBSERVED_CURRENT_PROCESS; format/menu adalah vocabulary, bukan condition scale, approval atau journal/period rules |
| [Inventory discrepancy](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#inventory-transaction-code-discrepancy): ASSET-01 Sheet2 K; ASSET-02 kolom 11; ASSET-04 Neraca Psd E6/C44; ASSET-05 Sheet1 F4 | WF-023/026/025/027 raw P01/P02 dan dependent ledger/report block | OBSERVED_CURRENT_PROCESS; tidak menjawab alias/version/typo/distinct atau sign/mapping |
| [Correction/development/KDP findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#corrections-development-reclassification-and-kdp): ASSET-05 Sheet1 A3:G19; ASSET-11 halaman 1–2; ASSET-04 KDP; ASSET-06 GD. KDP A1:E1 | WF-020/024 pembedaan perubahan/KDP | OBSERVED_CURRENT_PROCESS; kategori/heading tidak menetapkan capitalization timing, progress-to-value atau historical correction effects |
| [Capitalization findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#capitalization-reference-and-intraextra-classification): ASSET-10 Akun C4:D5/Kapitalisasi B1:C11; ASSET-12 baris 1250–1254/1294–1295 | WF-016/017/020/024 dependent classification block | Reference historis / LEGACY_IMPLEMENTATION_BEHAVIOR; tidak ada instrument/effective date/comparator terverifikasi; nominal minima tidak disalin |
| [Legacy period/depreciation findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#legacy-report-period-and-depreciation-behavior): ASSET-12 selector 516–521, engine 1212–1435, detail 2470–2529 | WF-022/027 perbedaan tanggal posisi/rentang/periodik dan policy block | LEGACY_IMPLEMENTATION_BEHAVIOR; bukan accounting authority, daily policy, format BMU atau benchmark sejuta baris |
| [Historical/reconciliation findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#historical-import-and-reconciliation-constraints): ASSET-01/02 duplicate location heading; ASSET-04 Intra/Intra New; ASSET-06–08 recap/variant; ASSET-09 SPJ tracking | WF-025/026 perbandingan, provenance, ambiguity dan cutover boundary | Struktur sumber OBSERVED_CURRENT_PROCESS; bagian historical constraints berklasifikasi INFERENCE dari observasi/OD. Tidak memilih source winner, duplicate identity, Finance owner/signoff atau write ownership |
| [PRODUCT_EXPERIENCE_DIRECTION](../01-product/PRODUCT_EXPERIENCE_DIRECTION.md#temuan-visual-dan-interaksi-yang-benar-benar-terlihat), SRC-047/VIS-04 sekitar 13–15,16–21,35–44 detik | RT/IX konteks search–detail, panel, aktivitas dan orientasi aplikasi | SOURCE OBSERVATION sesuai batas P1; tidak membuktikan focus trap, mobile/accessibility, runtime save, layout/token/motion P7 |

Authority rule rinci tetap dibaca pada BR/INV pemiliknya. Sumber bukti lokal tidak diautentikasi menjadi policy current dalam sesi P3. Batas source safety mengikuti [EVIDENCE_POLICY](../00-governance/EVIDENCE_POLICY.md); semua raw tetap ignored, read-only dan tidak menjadi calon tracked artifacts.

## Jejak utama setiap workflow

Setiap baris menunjuk **saat aturan perlu dinilai**, tanpa menyalin wording BR/INV. DC pendukung lintas workflow (aktor, tanggung jawab, work context, evidence, revision, provenance, policy dan lifecycle) dirinci pada cakupan konsep di bawah. CAP/AC penuh dipetakan balik pada tabel berikutnya.

| WF / RT utama | DC utama | BR yang dinilai | INV yang dijaga | CAP / AC utama | Sumber / GAP yang mengendalikan |
|---|---|---|---|---|---|
| WF-001 / RT-001 | DC-002/004 | BR-027 | INV-016 | CAP-01; AC-01/26 | OD-11/12; ASSET-10 credential-risk presence saja; GAP-015 |
| WF-002 / RT-002 | DC-001–009 | BR-001/002/003 | INV-001/002 | CAP-02/03/20; AC-02/03/04/24 | OD-01/02/04/09/10/20; GAP-014 |
| WF-003 / RT-003 | DC-001/021/022 | BR-001/003/005 | INV-006/017 | CAP-04; AC-03/05/06 | PR-F03 PROC-04/06/08/13; GAP-001/002/003/013 |
| WF-004 / RT-004 | DC-021–024 | BR-003/005/025/027 | INV-006/018 | CAP-04/05; AC-06/07/26 | PR-F03 PROC-06/08; GAP-002/013 |
| WF-005 / RT-005 | DC-024–027/032/036–041 | BR-002/003/004/005/010/025 | INV-002/006/018 | CAP-05; AC-05/07 | PR-F05 PROC-14–22/30; GAP-002/003/013/017/019 |
| WF-006 / RT-006 | DC-024/042 | BR-003/004/006/009 | INV-002/005/006 | CAP-06; AC-05/08 | PROC-23–27 p1–2; GAP-003/013/017 |
| WF-007 / RT-007 | DC-002/028–031 | BR-007/009/029 | INV-003/004/006 | CAP-07; AC-09 | PR-F04 Input AU2:BX2; GAP-014/015/017 |
| WF-008 / RT-008 | DC-028–035 | BR-003/004/007/008/026 | INV-003/004/006/017 | CAP-03/07/19; AC-05/09/10/22 | Corrected approved AC-09/10; PR-F04/05; GAP-003/014/015/017 |
| WF-009 / RT-009 | DC-033/036–038 | BR-003/004/009/010 | INV-004/005/006 | CAP-05/06/08; AC-07/08 | PROC-15/17/18/27/30; GAP-003/013/017/019 |
| WF-010 / RT-010 | DC-024/028/039 | BR-004/009/010/025 | INV-005/006/018 | CAP-05/08; AC-07/11 | PROC-19–21 p1–2; GAP-003/013/017 |
| WF-011 / RT-011 | DC-008/040/014/015 | BR-002/004/010/011 | INV-002/005/006 | CAP-05/08/09; AC-07/11/12 | PR-F02 exact step locators; GAP-003/004/013/017 |
| WF-012 / RT-012 | DC-024/028/041 | BR-004/009/010/012/025 | INV-005/006/017/018 | CAP-08/09; AC-11/12 | PROC-30 CX2:DS2/DT2:HN2; GAP-003/017/019 |
| WF-013 / RT-013 | DC-041/043 | BR-003/009/012 | INV-006/017 | CAP-09; AC-05/12 | PR-F04 contract/progress output families; GAP-003/017/018/019 |
| WF-014 / RT-014 | DC-044–046 | BR-009/010/012/013 | INV-005/006/012 | CAP-09/10; AC-12/13 | PR-U05 steps16–20/k–l/KO2:KV2; GAP-003/017/018 |
| WF-015 / RT-015 | DC-047/048 | BR-003/009/010/012 | INV-005/006 | CAP-09; AC-05/12 | PROC-30 SPJ families; PROC-32/ASSET-09 A3:R5; GAP-010/017/018/019 |
| WF-016 / RT-016 | DC-049/050/051/065/070 | BR-003/013/018/021/025 | INV-006/009/011/012/018 | CAP-10/11/14/15; AC-11/13/18 | PR-U05; ASSET-04/06; GAP-008/009/014/018 |
| WF-017 / RT-017 | DC-051–055 | BR-013/014/015/018/029 | INV-008/012/015 | CAP-11; AC-13/14 | ASSET-03/08/11; GAP-008/009/011/014/018 |
| WF-018 / RT-018 | DC-051/053–055 | BR-014/015/016/026 | INV-008/017 | CAP-11/12; AC-14/15 | ASSET-11 p1–2; GAP-008/014 |
| WF-019 / RT-019 | DC-051/056/057 | BR-003/014/015/016 | INV-007/008/017 | CAP-11/12; AC-14/15 | AC-14/15 boundary; ASSET-11 vocabulary; GAP-008/013/014 |
| WF-020 / RT-020 | DC-053/058–060 | BR-014/016/018/025/026 | INV-008/017/018 | CAP-12/15/19; AC-15/18/22 | ASSET-05/11/12; GAP-008/009/010/013 |
| WF-021 / RT-021 | DC-051/056/061 | BR-014/015/016/026 | INV-007/008/017 | CAP-12; AC-15 | ASSET-11 p1–2; AC-15 boundary; GAP-008/013/014 |
| WF-022 / RT-022 | DC-019/020/062/064/076 | BR-017/022/025 | INV-007/013/018 | CAP-13/17; AC-16/20 | ASSET-03/11/12; GAP-007/008/013 |
| WF-023 / RT-023 | DC-065–069 | BR-019/020/023/025/026 | INV-009/010/014/017/018 | CAP-14/17; AC-17/20 | ASSET-01/02/04/05 exact discrepancy locators; GAP-005/006/010/011/013 |
| WF-024 / RT-024 | DC-041/043/049–053/070 | BR-013/016/018/021/025 | INV-006/008/011/012/018 | CAP-10/15; AC-13/18 | ASSET-04 KDP/06 GD. KDP/11; PROC-30; GAP-008/009/010/018 |
| WF-025 / RT-025 | DC-019/071/072 | BR-003/022/023/025/026 | INV-014/017/018 | CAP-03/17/19; AC-05/20/22 | ASSET-04–09 scoped locators; GAP-005–010/012/013/018 per source |
| WF-026 / RT-026 | DC-018/020/073–075 | BR-010/024/025/026/029 | INV-010/015/016/017/018 | CAP-16/19; AC-19/22 | ASSET-01/02/04/08/10/12; PROC-30/31; GAP-011/012/013/016/019 |
| WF-027 / RT-027 | DC-019/020/064/068/070–072/076 | BR-017/018/019/020/022/023/025 | INV-009/010/013/014/018 | CAP-13/17/18; AC-16/17/20/21 | ASSET-03/04/11/12; U-002 BMU need; GAP-005–010/013/016 |
| WF-028 / RT-028 | DC-010–018/020 | BR-004/009/010/025/026 | INV-005/006/017/018 | CAP-08/19; AC-11/22 | PR-F04 Input/Mail Merge/Isian Nomor/SPJ PL C1267; GAP-013/017/019 |

RT-029 search, RT-030 history dan RT-031 evidence memberi jejak lintas konteks WF-002–028; scope serta confidentiality tetap prerequisite P5. Mapping bukan izin membaca semua objek dalam satu ekosistem.

## Cakupan seluruh konsep domain

Pengelompokan berikut mencakup DC-001–DC-077 tepat sebagai **konsep yang direferensikan**, bukan klaim seluruh konsep diimplementasikan atau membutuhkan state machine sendiri. Konsep pendukung dapat dipakai beberapa workflow tanpa membuat canonical definition kedua.

| Area P2 / seluruh DC yang terkait | WF dan kontrak lintas workflow | Makna kontribusi P3 |
|---|---|---|
| Organisasi/aktor/pekerjaan DC-001–009 | WF-001/002/003/005/007/008/016/017/025; konteks owner/waiting seluruh WF operasional | Menempatkan responsibility concept pada action/context; tidak menghasilkan role/permission mapping |
| Data/dokumen/bukti/kebijakan DC-010–020 dan DC-077 | Semua WF operasional; WF-028; IX-010–013; RT-030/031 | Source/version, evidence, history, policy applicability dan state/display distinctions sesuai pemilik masing-masing |
| Kebutuhan/pengadaan/kontrak DC-021–027 dan DC-036–048 | WF-003–006/009–015/028 | Trigger/precondition/loop dan gate metode/pihak resmi yang belum diketahui |
| Penyedia/partisipasi DC-028–035 | WF-007/008, terhubung WF-005/009/010/012 | Company/PIC/account/participation berbeda; submission/revision/receipt/outcome sama ditelusuri |
| Klasifikasi/handoff DC-049–050 | WF-016/017/023/024 | Classified draft hypothesis, unmet prerequisites dan definitive acceptance tetap berbeda |
| Aset/lifecycle DC-051–064 | WF-017–022, terhubung WF-016/024/027 | Registrasi/placement/condition/change/disposal/report request sesuai aturan yang applicable |
| Persediaan DC-065–069 | WF-023, terhubung WF-016/025/026/027 | Enam transaction families, observed count, accepted ledger dan stock view terpisah |
| KDP DC-070 | WF-024, terhubung WF-013/016/017/025/027 | Progress/completion candidate berbeda dari definitive asset |
| Rekonsiliasi/intake/laporan DC-071–076 | WF-025/026/027 | Scope, comparison, validation issue, acceptance dan temporal meaning mempunyai jejak masing-masing |

## Cakupan balik seluruh CAP

| CAP | WF / RT yang memberi makna operational | IX/EP utama dan batas |
|---|---|---|
| CAP-01 | WF-001/RT-001 | IX-019 konteks masuk; eligibility/session/recovery detail GAP-015 tetap P5/P9 |
| CAP-02 | WF-002/003; RT-002/003 dan rute pemilik record | IX-016/017/021; unit generic/akses Super Admin sebagai arah produk, enforcement P5 |
| CAP-03 | Semua WF operasional; terutama003/008/015/025 | IX-003–009/015/021; EP menjelaskan owner/waiting/next ketika action tertahan |
| CAP-04 | WF-003/004; RT-003/004 | IX-001–006/011/012; EP-001/002/003; official approval GAP-001 |
| CAP-05 | WF-004/005/008–011; RT terkait | IX-003–010/012; EP-003/004; urutan/applicability tiap metode belum resmi |
| CAP-06 | WF-006/009; RT-006/009 | IX-020/010/011/012; activity applicability tidak universal |
| CAP-07 | WF-007/008; RT-007/008 | IX-001–006/011/012; EP-001/002; receipt/revision tidak sama eligibility/award |
| CAP-08 | WF-028 serta011/012/014/015; RT-028 | IX-010–013; EP-001/002/003/004/007; final source/version tetap explainable |
| CAP-09 | WF-012–015; RT-012–015 | IX-002–012; EP-001/002/007; payment evidence tracking saja |
| CAP-10 | WF-016 dengan014/017/023/024; RT-016 | IX-014/021/007; EP-008; klasifikasi/definitive gates GAP-008/009/018 |
| CAP-11 | WF-017/018/019; RT-017–019 | IX-001/002/007/011–013; EP-005/006; identity/placement/custodian berbeda |
| CAP-12 | WF-019/020/021; RT-019–021 | IX-002–009/011–013/015; EP-001/003/007; official accounting/disposal authority blocked |
| CAP-13 | WF-022/027; RT-022/027 | IX-018; EP-003/010; official output memerlukan policy/format termasuk BMU |
| CAP-14 | WF-023; RT-023 | IX-001–013/015; EP-003/005/009; actual dictionary/mapping P01/P02 blocked |
| CAP-15 | WF-024, terhubung013/016/017; RT-024 | IX-002/007/011/012/014/021; EP-003/008; belum selesai tetap KDP bila applicable |
| CAP-16 | WF-026; RT-026 | IX-003/006–008/011–013/015; EP-005/006/010; structural pass tidak menerima semantics |
| CAP-17 | WF-025/027 dan023/024; RT-025/027 | IX-015/018/021; EP-003/009/010; explanation/zero difference bukan official signoff |
| CAP-18 | RT-029 dan daftar/detail seluruh RT; WF-025/026/027 | IX-017/018; EP-010; NFR timing/workload tidak difinalkan P3 |
| CAP-19 | RT-030/031, WF-008/018/020/025/026/028 dan history semua workflow material | IX-011–013; EP-007; retention/audit access P5/P9 |
| CAP-20 | WF-002; semua RT/IX | IX-016/017/019; accessible action, textual outcome, deterministic return; visual/ID-EN design P7 |

## Cakupan balik seluruh AC

Ini **coverage kontrak perencanaan**, bukan PASS aplikasi, UAT, accounting arithmetic, official procurement atau capacity. Kriteria approved tidak diubah. AC-09/10 mencakup koreksi approved P1-PROD-01 secara utuh.

| AC | WF / RT / IX yang relevan | Bukti kontrak yang harus dapat direview / prasyarat tetap |
|---|---|---|
| AC-01 | WF-001; RT-001; IX-019 | Referensi arah akun lokal/return; account provisioning/recovery/session rinci P5/P9/GAP-015 |
| AC-02 | WF-002; RT-002; IX-016 | Switch mempertahankan konteks shared record dan tidak memberi hak baru; P5 enforcement |
| AC-03 | WF-003/002; RT-003/002 | Asal Unit Organisasi generic retained; tidak ada faculty-only route; GAP-001/014 |
| AC-04 | WF-002; RT-002/030/031 dan owning route | Super Admin entry ke owning workspace/context serta traceability; administration action details P5/P7 |
| AC-05 | WF-002–028; semua RT utama | Status/current owner/waiting/next/evidence/blocked reason dan no-waiting explanation pada kontrak, bukan guessed responsibility |
| AC-06 | WF-003/004; RT-003/004; IX-003–006 | Same request, reason/required correction, resubmission dan reuse baseline; approval path GAP-001 |
| AC-07 | WF-004/005/008–011; RT terkait | Request/RUP/package/material/offer/evaluation/result/SPPBJ relation; applicability gate per metode GAP-003/017 |
| AC-08 | WF-006; RT-006; IX-020/010/011 | Package-linked schedule/participants/invitation/outcome; child return ke package; tidak menjadi mandatory gate |
| AC-09 | WF-007/008; RT-007/008; IX-003–006 | Existing/new company/PIC/evidence reuse; requested submission/revision tanpa unrelated re-entry; validity/representation GAP-014/015/017 |
| AC-10 | WF-008; RT-008/030/031; IX-003–006/012 | Same submission/revision receipt, outcome/status konsisten vendor/operator; each version evidence/history; protected other-provider info tetap prerequisite |
| AC-11 | WF-028/016 dan011/012/014/015; IX-010/013/021 | Source/version/variant and historical output context; no competing silent truth; GAP-017/018/019 |
| AC-12 | WF-012–015; RT-012–015 | Contract/progress/inspection/BAST/SPJ/payment evidence dan party-next; tidak mengirim/otorisasi funds |
| AC-13 | WF-014/016/017/023/024; IX-014/021/007 | Classification pending→eligible draft→completion/validation sesuai kontrak blocked; BAST tidak membuat definitive record |
| AC-14 | WF-017/018/019; RT-017–019/030/031 | Origin/placement/custodian/movement/observation evidence; physical condition independen; identity GAP-011 |
| AC-15 | WF-019/020/021; IX-002/007/011–013 | Condition/maintenance/change/disposal reason, before/after dan gate authority; book zero tidak otomatis removal |
| AC-16 | WF-022/027; RT-022/027; IX-018 | Applicable policy/cutoff/provenance; unsupported accounting output blocked; official BMU definition/format/example belum tersedia |
| AC-17 | WF-023/025/027; RT-023/025/027 | Separate accepted ledger / six tx families / comparison; raw dictionary/P01/P02 gaps tidak dinormalisasi |
| AC-18 | WF-013/016/024/017; RT terkait | Source/progress/KDP/completion/asset-draft relation; no premature definitive asset; capitalization remains blocked |
| AC-19 | WF-026; RT-026/030/031; IX-013/015 | Source→candidate→structural/semantic issues→traceable cleansing/reject/accept; ambiguity retained; cutover authority GAP-012 |
| AC-20 | WF-025/027; RT-025/027; IX-015/018 | Scope/unit/source/time/difference/action/evidence; no resolved difference without explanation; institutional signoff GAP-010 |
| AC-21 | RT-029 dan owning list/detail; WF-025/026/027; EP-010 | Find partial results, preserve list/search context, accepted≠complete long operation; GAP-016 workload/timing P6/P8/P9 |
| AC-22 | Semua workflow material; RT-030/031; IX-011–013 | Actor/time/context/reason/provenance/related outcome; privacy/scope conceptual boundary, technical audit/retention P5/P9 |
| AC-23 | Semua RT/IX, WF-002 | Indonesian-first state/action text dan context-preserving language switch requirement; locale/layout details P7/P8 |
| AC-24 | Semua RT/IX | Reachable action, textual state, success/failure/return, contextual cancel; visual/focus/responsive implementation P7/P8 |
| AC-25 | WF-002/006; RT-029/owning detail; IX-016/017/020 | Persistent context/search/detail continuity/activity intent dari recorded VIS-04; final shell/components/motion P7 |
| AC-26 | WF-001/004/005/012–016/028 serta seluruh MUST | Local evidence/reference path tanpa future integration; official external obligations must still be validated |
| AC-27 | Seluruh WF/RT/IX | Tidak menambah recurring paid dependency; recovery/cost validation P5/P9; COST_POLICY tetap canonical |

## Seluruh arah Owner ke kontrak P3

| OD canonical | Jejak P3 / batas yang dipertahankan |
|---|---|
| [OD-01](../00-governance/DECISION_LOG.md#od-01) | WF-002/016/025; RT owning context; satu aplikasi/shared lifecycle, bukan pemisahan system |
| [OD-02](../00-governance/DECISION_LOG.md#od-02) | WF-002; RT workspace ownership dan contextual switch di seluruh route |
| [OD-03](../00-governance/DECISION_LOG.md#od-03) | WF-014/016/017/023/024/025; IX-021; source/outcome relation dan correction evidence |
| [OD-04](../00-governance/DECISION_LOG.md#od-04) | WF-003; RT-003; BR-001; unit generic tanpa faculty-only model |
| [OD-05](../00-governance/DECISION_LOG.md#od-05) | WF-003/004; GAP-001 named routing/approval gate retained |
| [OD-06](../00-governance/DECISION_LOG.md#od-06) | WF-003–028 integrated families; WF-005 method extensions; map nilai tidak dipaksakan sebagai sequence resmi |
| [OD-07](../00-governance/DECISION_LOG.md#od-07) | WF-003/008/016/025/028; party-next/revision/reuse/exception; tidak mengklaim penghematan terukur |
| [OD-08](../00-governance/DECISION_LOG.md#od-08) | WF-028; IX-010/013; provenance/source/version bagi reuse/output |
| [OD-09](../00-governance/DECISION_LOG.md#od-09) | Responsibility concept setiap WF; INV-002; tidak memberi role baru tiap jabatan |
| [OD-10](../00-governance/DECISION_LOG.md#od-10) | WF-002/RT-002 dan owning record route; Owner Super Admin access direction; final controls P5 |
| [OD-11](../00-governance/DECISION_LOG.md#od-11) | WF-001/RT-001/IX-019 reference-only local entry; provisioning/recovery/session P5 |
| [OD-12](../00-governance/DECISION_LOG.md#od-12) | WF-001/004/028 dan AC-26 mapping; institutional identity/domain/API FUTURE |
| [OD-13](../00-governance/DECISION_LOG.md#od-13) | BR-027 mapped seluruh workflow; no speculative adapters/implementation; later integration boundary only when justified |
| [OD-14](../00-governance/DECISION_LOG.md#od-14) | WF-022/026/027; ASSET-12 remains legacy evidence; GAP-007/013 authority gate |
| [OD-15](../00-governance/DECISION_LOG.md#od-15) | WF-022/027; four time families; no daily formula/universal filter |
| [OD-16](../00-governance/DECISION_LOG.md#od-16) | RT-029/list-detail; WF-025–027; IX-017/018; EP-010 intent; capacity decisions P4/P6/P8/P9 |
| [OD-17](../00-governance/DECISION_LOG.md#od-17) | Indonesian-first states/actions; AC-23; language switch preserves same workflow/context, visual localization P7 |
| [OD-18](../00-governance/DECISION_LOG.md#od-18) | AC-27 mapping semua WF; no paid dependency introduced; cost inventory P9 |
| [OD-19](../00-governance/DECISION_LOG.md#od-19) | Semua WF operasional/TR: status, owner, waiting, next/evidence, reason when unknown/no action |
| [OD-20](../00-governance/DECISION_LOG.md#od-20) | WF-002/RT-002/IX-016, conceptual route prerequisite; switch bukan permission |
| [OD-21](../00-governance/DECISION_LOG.md#od-21) | WF-002/006; RT-029/owning detail; recorded SRC-047 context/panel/search/calendar intent only; final visual/motion P7 |
| [OD-22](../00-governance/DECISION_LOG.md#od-22) | CAP-18/AC-21; NFR proposals byte-preserved; ~200 population bukan concurrency; GAP-016 |
| [OD-23](../00-governance/DECISION_LOG.md#od-23) | WF-006/007/008/016/023/024/027/028; provider reuse, ledger, KDP, report/BMU need, downstream hypothesis qualifications |

## Cakupan balik seluruh BR dan INV

Tabel memberi lokasi evaluasi aturan/invariant di workflow dan interaction; **wording/status/authority tetap hanya di tautan BR/INV**. Tidak ada rule official blocked yang berubah menjadi executable karena tercantum di sini.

| BR canonical | WF / kontrak yang harus menilai applicability |
|---|---|
| [BR-001](../02-domain/BUSINESS_RULES.md#br-001) | WF-002/003/017/026 — identitas asal dan konteks organisasi |
| [BR-002](../02-domain/BUSINESS_RULES.md#br-002) | WF-002/005/011 dan responsibility context setiap WF |
| [BR-003](../02-domain/BUSINESS_RULES.md#br-003) | Semua WF operasional/TR/IX — party-waiting-next dan uncertainty |
| [BR-004](../02-domain/BUSINESS_RULES.md#br-004) | WF-005/006/008–012/028 — titik ekstensi/action/doc applicability |
| [BR-005](../02-domain/BUSINESS_RULES.md#br-005) | WF-003/004/005 — need/request/RUP/package origin |
| [BR-006](../02-domain/BUSINESS_RULES.md#br-006) | WF-006; IX-020 — activity context/outcome |
| [BR-007](../02-domain/BUSINESS_RULES.md#br-007) | WF-007/008; IX-002/003/005/006 — reuse dan version validity |
| [BR-008](../02-domain/BUSINESS_RULES.md#br-008) | WF-008; IX-003–006/012 — submission/revision/receipt/outcome |
| [BR-009](../02-domain/BUSINESS_RULES.md#br-009) | WF-006–016/028; IX-010/011/013 — source/output/evidence conflict |
| [BR-010](../02-domain/BUSINESS_RULES.md#br-010) | WF-005/009–015/026/028 — variant/field/number/signature trust gate |
| [BR-011](../02-domain/BUSINESS_RULES.md#br-011) | WF-011 — empat responsibility dan unresolved SPPBJ gate |
| [BR-012](../02-domain/BUSINESS_RULES.md#br-012) | WF-012–015 — contract/progress/inspection/handover/SPJ/payment relation |
| [BR-013](../02-domain/BUSINESS_RULES.md#br-013) | WF-014/016/017/023/024; IX-014/021 — classification/draft/acceptance |
| [BR-014](../02-domain/BUSINESS_RULES.md#br-014) | WF-017–021; IX-012/013 — current representation dan underlying history |
| [BR-015](../02-domain/BUSINESS_RULES.md#br-015) | WF-017/018/019/021 — placement/custodian/observed condition/maintenance |
| [BR-016](../02-domain/BUSINESS_RULES.md#br-016) | WF-018/020/021/024 — jenis change/removal dan accounting gate |
| [BR-017](../02-domain/BUSINESS_RULES.md#br-017) | WF-022/027; IX-018 — depreciation policy gate |
| [BR-018](../02-domain/BUSINESS_RULES.md#br-018) | WF-016/017/020/024/027 — recognition/classification/capitalization gate |
| [BR-019](../02-domain/BUSINESS_RULES.md#br-019) | WF-023/027 — accepted ledger dan stock semantics |
| [BR-020](../02-domain/BUSINESS_RULES.md#br-020) | WF-023/026/025/027 — dictionary/raw mapping applicability |
| [BR-021](../02-domain/BUSINESS_RULES.md#br-021) | WF-016/024/017 — KDP completion/draft relation |
| [BR-022](../02-domain/BUSINESS_RULES.md#br-022) | WF-022/025/027 — four time families dan output context |
| [BR-023](../02-domain/BUSINESS_RULES.md#br-023) | WF-023/025/027; IX-015/018 — case/difference/acceptance |
| [BR-024](../02-domain/BUSINESS_RULES.md#br-024) | WF-026; IX-013/015 — candidate/validation/cleansing/acceptance |
| [BR-025](../02-domain/BUSINESS_RULES.md#br-025) | WF-004/005/010/012/016/020/022–028 — applicable policy version/provenance |
| [BR-026](../02-domain/BUSINESS_RULES.md#br-026) | Semua workflow material; IX-009/012/013; EP-007 — disposition/history |
| [BR-027](../02-domain/BUSINESS_RULES.md#br-027) | Semua WF/RT/IX; khusus001/004/028 — local reference independence/future boundary |
| [BR-028](../02-domain/BUSINESS_RULES.md#br-028) | WORKFLOW_CATALOG scope exclusion; tidak ada borrowing WF/RT/IX |
| [BR-029](../02-domain/BUSINESS_RULES.md#br-029) | WF-007/017/023/024/026; EP-006 — identity/duplicate ambiguity |

| INV canonical | WF / interaction / exception yang menjaga batas |
|---|---|
| [INV-001](../02-domain/BUSINESS_RULES.md#inv-001) | WF-002; seluruh route prerequisite; IX-016/017 |
| [INV-002](../02-domain/BUSINESS_RULES.md#inv-002) | WF-005/006/011; responsibility context semua WF |
| [INV-003](../02-domain/BUSINESS_RULES.md#inv-003) | WF-007/008; IX-002/003/005/006; source historical profile context |
| [INV-004](../02-domain/BUSINESS_RULES.md#inv-004) | WF-007/008/009; RT-008/029/030/031; no protected other-provider leakage |
| [INV-005](../02-domain/BUSINESS_RULES.md#inv-005) | WF-028 dan output/evidence WF-006–015; IX-010/011/013; EP-005 |
| [INV-006](../02-domain/BUSINESS_RULES.md#inv-006) | WF-007/008/016/028 dan semua reuse; IX-005/006/010/013/021 |
| [INV-007](../02-domain/BUSINESS_RULES.md#inv-007) | WF-019/021/022; condition/report output terpisah |
| [INV-008](../02-domain/BUSINESS_RULES.md#inv-008) | WF-017–021/024; RT-030; IX-012/013 |
| [INV-009](../02-domain/BUSINESS_RULES.md#inv-009) | WF-016/023/027; accepted ledger berbeda dari asset register/manual competing stock |
| [INV-010](../02-domain/BUSINESS_RULES.md#inv-010) | WF-023/026/025/027; EP-003/005; raw P01/P02 traceable |
| [INV-011](../02-domain/BUSINESS_RULES.md#inv-011) | WF-016/024/017; IX-014/021; no premature definitive asset |
| [INV-012](../02-domain/BUSINESS_RULES.md#inv-012) | WF-014/016/017/023/024; EP-008; BAST alone cannot definitive acceptance |
| [INV-013](../02-domain/BUSINESS_RULES.md#inv-013) | WF-022/027; IX-018; EP-003; unresolved accounting output blocked |
| [INV-014](../02-domain/BUSINESS_RULES.md#inv-014) | WF-023/025/027; IX-015; EP-009; explanation/evidence versus official signoff |
| [INV-015](../02-domain/BUSINESS_RULES.md#inv-015) | WF-017/026; IX-013/015; EP-005/006; explicit identity acceptance |
| [INV-016](../02-domain/BUSINESS_RULES.md#inv-016) | WF-001/026; historical source filtering boundary; no credential reproduction/import |
| [INV-017](../02-domain/BUSINESS_RULES.md#inv-017) | Semua workflow material; IX-009/012/013; EP-007; reason/supersession/affected outcome history |
| [INV-018](../02-domain/BUSINESS_RULES.md#inv-018) | WF-004/005/012/016/020/022–028; IX-010/013/018; applicable historical version explainable |

## Penilaian seluruh GAP dari perspektif workflow P3

**A** = dapat diselesaikan dengan authority/evidence yang tersedia; **B** = safe workflow skeleton/decision point dapat diperjelas, jawaban resmi tetap belum tersedia; **C** = keputusan utama di luar P3; **D** = jawaban authoritative yang diminta tetap sepenuhnya blocked. Kelas bukan status GAP. Tidak ada authority baru yang menutup GAP dalam sesi ini.

| GAP / kelas P3 | WF/RT terdampak dan decision point yang belum tersedia | Klarifikasi aman / sumber yang mengendalikan | Bukti/resolver yang diperlukan |
|---|---|---|---|
| GAP-001 / B | WF-003/004; RT-003/004 — **jalur persetujuan dan routing unit→pusat** | Intake/completeness/revision tidak mengklaim official approval; asal generic retained. PR-F03 PROC-04/06/08/13; OD-04/05 | Owner + authorized procurement: approved current routing, delegation, unit variants, exceptions |
| GAP-002 / B | WF-004/005; RT-004/005 — **RUP preparation/publication/revision/cancel handoff** | Associate reference/version tanpa mengklaim publication; dependent progression tetap applicable-policy gate. PROC-06 t6 rows3–8/PROC-08 t1 row3 | Current approved RUP procedure, publication/change/cancel responsibility dan source/version |
| GAP-003 / B | WF-003/005/006/008–015; RT terkait — **aktor/urutan per metode** | Common package context + conditional method extensions; semua assignment unknown visible. PR-F05 PROC-14–27/30 | Procurement specialist + Owner: responsibility/method/action/receipt/review/delegation matrix berbasis evidence, tanpa P5 enforcement sekarang |
| GAP-004 / B | WF-011; RT-011 — **empat pemetaan SPPBJ** | Draft/check/issue/sign responsibility dibedakan tanpa pemilihan universal. PR-F02 PROC-02 step14/PROC-10 step11/PROC-12 iteml | Approved procedure memetakan masing-masing responsibility per metode/delegasi |
| GAP-005 / B | WF-023/025/026/027; RT terkait — **kamus transaksi diterima** | Enam transaction families dan raw-code review safe skeleton; official sign/effect masih blocked. ASSET-01/02/04/05 | Inventory/Finance versioned dictionary, sign, effective dates, historical applicability/examples |
| GAP-006 / D | WF-023/025/026/027 — **actual P01/P02 interpretation/mapping** | Preserve raw/issue provenance; tidak menganggap safe interface sebagai jawaban mapping. Sheet2 K/CSV11 vs Neraca Psd E6/C44/journal F4 | Inventory/Finance authoritative discrepancy explanation dan approved reversible mapping/reconciliation |
| GAP-007 / B | WF-022/027; RT-022/027 — **policy/cutoff applicable** | Four temporal families, valid input/error/policy block; unsupported result tidak tampak resmi. ASSET-03/12; OD-14/15 | Asset/Finance approved instrument/effective dates/cutoff/rounding dan validated examples termasuk arbitrary-date applicability |
| GAP-008 / B | WF-016–024/027 — **accounting/historical-period effect controlled change/KDP** | Proposal/reason/before-after/authority gate; tidak mengisi jurnal, restatement atau capitalization timing. ASSET-05/11/12 | Asset/Finance approved change/reversal/period semantics, KDP completion dan reconciliable examples |
| GAP-009 / B | WF-016/017/020/024/027 — **recognition/capitalization applicability** | Classification pending dan policy gate, tanpa minima/equality assumption. ASSET-10 Akun C4:D5/Kapitalisasi B1:C11 | Approved instrument/version/category/comparator/currency/basis/effective/historical rules |
| GAP-010 / B | WF-015/023/024/025/027 — **owner/waiting/signoff/cadence/output authority** | Assigned follow-up jika tervalidasi, evidence/penjelasan dan acceptance gate terpisah; no Finance signoff guess. ASSET-04–09 | Owner + Finance/Asset/Procurement/Persediaan: responsibility, expected relationship, exception/period closure acceptance evidence |
| GAP-011 / B | WF-017/023/024/026 — **source identity/format/duplicate/acceptance** | Structural/semantic checks, ambiguity/duplicate decision dan compare safe skeleton; no schema/import now. ASSET-01/02/08/12, PROC-30/31 | Later authorized source inventory/approved samples, identity, field semantics, validation, reversible reconciliation |
| GAP-012 / B | WF-025/026/027 — **cutover source/write-owner/acceptance boundary** | P3 now names authoritative-source/write-ownership decision gate and prevents candidate acceptance/official comparison that assumes it. P2 C→P3 B; actual cutover sequence tetap P9/P10. OD-01/03; ASSET-06/08 | Approved cutover/write ownership/reconciliation/fallback/rollback/signoff plan in separately authorized P9/P10 |
| GAP-013 / D | Semua WF dependent official procedure/policy, terutama003–028 — **current source/issuer/version/applicability proof** | Provenance/candidate labels retained; P3 cannot authenticate sources by writing workflow. PR-F01, ASSET-04/10/12 | Source custodian confirms current approved version/issuer/applicability/supersession/latest HTML provenance |
| GAP-014 / B | WF-002/003/005/007/008/011/016–026; owning RT — **responsibility assignment/representation versus authorization** | Function/unit/actor unknown stated at action boundary; no permission matrix; privacy prerequisite remains. OD-09/10/20; P2 responsibility dimensions | Owner validates compact roles/scope/assignment/segregation/representation with P5 scenarios |
| GAP-015 / C | WF-001/007/008; RT-001/007/008 — **local account eligibility/provisioning/recovery** | Account entry is reference-only; profile flow does not imply open self-registration or account representation. OD-11/12; ASSET-10 risk-only | Owner/security/operations in P5/P9: usable local account/recovery contracts, binding external profile if any |
| GAP-016 / C | WF-025/026/027; RT-029/large lists — **workload/operation budgets/capacity** | Long-operation accepted/progress/result/failure semantics and partial list navigation only; no timer/queue/index implementation. OD-16/22; AC-21/NFR proposals | P4/P6/P8/P9 representative workload, funded capacity, concurrency and benchmark/operating contract |
| GAP-017 / B | WF-005–015/028 — **method variant/number/signature/evidence** | Applicable/unknown/required/optional/not-applicable contract and output block; no inference from filename. PROC-21 p1; PROC-30 Isian Nomor | Procurement custodian: current variant/clause/number/issuer/signers/applicability/external-reference rules |
| GAP-018 / B | WF-014/015/016/017/023/024 — **eligible handover/payment trigger and responsibility** | Distinct inspection/acceptance/handover/BAST and classification/draft/definitive gate; no auto record. PROC-02 steps16–20/PROC-04 k–l/PROC-30 KO2:KV2 | Procurement/Asset/Persediaan/Finance validate event/evidence/assignment/exception/correction handoff |
| GAP-019 / B | WF-005/009/012/013/015/026/028 — **canonical source fields/output trust** | Required-values/source-version validation and unresolved variant/dependency block; no copied formula/clause. PROC-30 SPJ PL C1267; PROC-31 headers | Authorized domain review of canonical fields/formulas/clauses/dependency/cache reliability and official examples |
| GAP-020 / C | Catalog scope exclusion; **no borrowing WF/RT/IX** | Remains FUTURE/outside V1; no procedure resurrection from source availability. PR-F06 PROC-28/29; BR-028 | Separate Owner scope decision first, then current custodian procedure if included later |

Counts: **20 reviewed; A 0 / B 15 / C 3 / D 2; resolved 0; OPEN 20; new 0**. B: GAP-001–005/007–012/014/017–019. C: GAP-015/016/020. D: GAP-006/013. **15 partially clarified** means operational skeleton/decision visibility in P3, not 15 resolved gaps or official partial approvals. P2's historical classification remains intact. GAP-012 moves from P2 C to P3 B because P3 can identify acceptance/write-ownership boundaries; its cutover implementation and actual official plan remain unavailable/outside authorization.

## Framework adoption and handoff evidence

| AICWDF v4.3 locator | P3 application / boundary |
|---|---|
| [§14.1](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#141-workflow-contract) | Actor/entry/precondition/paths/state/evidence/end-state and conceptual recovery are in workflow/TR contracts. Unknown official actor/order is BLOCKED_BY_GAP; authorization is conceptual authorized-actor prerequisite only. |
| [§14.2](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#142-route-contract) | RT/IX/WF links and result/return intent prevent orphan actions. Owner's P3 boundary defers HTTP Method/Handler implementation, permission matrix and endpoint contracts to P4/P5/P6. |
| [§14.3](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#143-interaction-contract) | Every specified action/form/navigation has context, success/validation/error/back-cancel/evidence effects; inactive/hidden state has applicable reason. Implementation/runtime interaction tests are later P7/P8, no clickable UI exists now. |
| [§13](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#13-p2--domain-model--business-rules) | P2 deliberately deferred exact transition wording; P3 now owns safe workflow state/TR definitions. Retention/deletion/anonymization/restore execution remains P5/P9/P10, not inferred from generic cancel. |
| [§18.4–18.6](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#184-back-action) | Deterministic child return and list/search/filter/sort/page/selected-context continuity are route/interaction obligations. Final page presentation/components/mobile layout remain P7. |
| [§18.9](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#189-required-states) | Loading/empty/success/validation/access/system/network outcome intent mapped through RT/IX/EP; visual implementation and browser evidence remain unverified. |

Adversarial P3 review must check coverage against these locators and source-authority qualifications, especially WF-008 submission/revision consistency, WF-011 four SPPBJ gates, WF-016 blocked classification, WF-023 raw P01/P02, WF-024 completion, WF-025 signoff and WF-027 time/policy. [WORKFLOW_DECISION_REQUESTS](WORKFLOW_DECISION_REQUESTS.md) groups exact unresolved decisions; answers require source/version/date/custodian/actual approval and targeted impact review before any owning specification or GAP changes.

P4 receives conceptual context/identity relationships only; P5 receives authorized-actor/privacy/assignment needs; P6 receives conflict-sensitive/long-operation and consistency needs without mechanisms; P7 receives route/return/action/accessibility/language intent without visual design. P8–P10 later validate critical journeys, policy examples, operating/source trust and acceptance. No later-phase work starts now. Independent review against the exact P3 manifest is the next safe action; P3 remains VERIFYING, P4 NOT AUTHORIZED, freeze NOT REACHED, Tasks NONE, execution NOT AUTHORIZED and application NONE.
