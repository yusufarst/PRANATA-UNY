# Domain traceability — PRANATA UNY

Status: APPROVED | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P2 | Preparation: [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006), completed | Approval: [APPR-003](../00-governance/APPROVAL_RECORDS.md#appr-003) | Checkpoint: [AUTH-007](../00-governance/APPROVAL_RECORDS.md#auth-007), automatically completed after verified publication

Dokumen ini memiliki pemetaan P2 dan penilaian kontribusi P2 terhadap GAP; tidak memiliki rumusan aturan atau status penutupan GAP. [DOMAIN_MODEL](DOMAIN_MODEL.md) memiliki DC-001–DC-077; [BUSINESS_RULES](BUSINESS_RULES.md) memiliki rumusan, status, authority, sumber dan kualifikasi BR/INV. [V1_SCOPE](../01-product/V1_SCOPE.md) tetap memiliki 20 CAP; [ACCEPTANCE_CRITERIA](../01-product/ACCEPTANCE_CRITERIA.md) tetap memiliki 27 AC dan 4 target NFR usulan. Semua definisi produk P1 dipertahankan dengan prasyarat validasinya.

## Pemilik sumber dan batas otoritas

| Label yang dipakai di bawah | Referensi pemilik / locators | Kekuatan dan batas |
|---|---|---|
| OD | [DECISION_LOG](../00-governance/DECISION_LOG.md), OD-01–OD-23; [DECISION_INDEX](../00-governance/DECISION_INDEX.md) | OWNER_APPROVED_DECISION; kualifikasi candidate, FUTURE, hypothesis dan unresolved tetap berlaku. |
| AUTH-006 / U-003 | [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md#auth-006) dan [SOURCE_INVENTORY U-003](../00-governance/SOURCE_INVENTORY.md#u-003-p2-owner-request), permintaan Owner P2 bagian 7–39/45–49 | Arah Owner untuk semantik/safety P2; tidak menyatakan kandidat domain sudah disetujui atau kebijakan resmi UNY tervalidasi. |
| PROC | [PROCUREMENT_SOURCE_REVIEW](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md), PROC-01–32; PR-F01–06 dan locators table/page/sheet di dalamnya | OBSERVED_CURRENT_PROCESS; dokumen/template historis belum membuktikan versi resmi, prosedur wajib, matrix responsibility atau formula yang benar. |
| ASSET | [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md), ASSET-01–12 dan safe observed findings | ASSET-01–11 OBSERVED_CURRENT_PROCESS; ASSET-12 LEGACY_IMPLEMENTATION_BEHAVIOR. Kode/formula/menu bukan policy; no financial audit/recalculation. |
| U/SRC/FW/VIS | [SOURCE_INVENTORY](../00-governance/SOURCE_INVENTORY.md); [REFERENCE_COVERAGE](../01-product/REFERENCE_COVERAGE.md); [PRODUCT_EXPERIENCE_DIRECTION](../01-product/PRODUCT_EXPERIENCE_DIRECTION.md) | Memiliki identitas/provenance dan dukungan P1; VIS-04/SRC-047 primarily P7. P2 tidak menginspeksi ulang video atau raw sources. |
| GAP | [GAP_REGISTER](../00-governance/GAP_REGISTER.md), GAP-001–GAP-020 | Semua OPEN. Register memiliki pertanyaan, resolver/evidence yang dibutuhkan dan closure; matriks P2 di bawah hanya kontribusi semantik dan handoff. |

Status aturan dibaca di katalog kanonik: **APPROVED_OWNER_DIRECTION** berlaku hanya sebagai arah Owner dalam lingkupnya; **EVIDENCE_SUPPORTED** adalah pengamatan; **PROPOSED** belum diputuskan; **BLOCKED_BY_GAP** tidak dapat menjadi rule eksekusi; **FUTURE/OUT_OF_SCOPE** menjaga batas produk. P2 tidak menemukan sumber lokal yang menyelesaikan pertanyaan current formal authority. Tabel berikut adalah locators, bukan salinan rumusan BR/INV atau source review.

## Seluruh kapabilitas P1 ke domain dan rule

| CAP | Konsep DC utama | Rule / invariant locator | OD / sumber | GAP dan handoff |
|---|---|---|---|---|
| CAP-01 | DC-002/004 | BR-027; INV-016; batas akun pada DOMAIN_RESPONSIBILITIES | OD-11/12; SRC-046/ASSET-10 | GAP-015; kontrak akun/sesi/recovery P5/P9, account reference saja dalam P2. |
| CAP-02 | DC-001–009 | BR-001/002; INV-001/002 | OD-01–05/09/10/20 | GAP-001/014; P4 struktur, P5 akses, P7 konteks pengalaman. |
| CAP-03 | DC-005/009/032–035/071/072/077 | BR-003/008/023 | OD-07/19/23; PROC-32/ASSET-09 | GAP-001–004/010/014; P3 tanggung jawab/pekerjaan berikut, P7 penyajian. |
| CAP-04 | DC-001/021–024/010/016 | BR-001/003/005 | OD-04–08; PROC-04/06/08/13 | GAP-001/002/003/013; P3 intake/approval resmi. |
| CAP-05 | DC-021–027/032/033/036–041 | BR-004/005/010/011 | OD-06/23; PROC-01–22/30 | GAP-002–004/013/017/019; P3 metode/sequence, P5 authority. |
| CAP-06 | DC-004/005/024/042/010/012/013 | BR-006 | OD-23; PROC-23–27 | GAP-003/013/017; P3 applicability/tanggung jawab, P7 kalender hanya nanti. |
| CAP-07 | DC-002/028–035/010/016 | BR-007/008; INV-003/004 | OD-19/23; PROC-14/17/22/30 Input AU2:BX2 | GAP-003/014/015/017; P3 onboarding/participation, P5 representasi dan visibility. |
| CAP-08 | DC-010–018/024/040/041/046/047 | BR-009/010; INV-005/006 | OD-08/23; PROC-30 Input A2:KV2 / Isian Nomor A2:AN2 / Mail Merge; PROC-32 | GAP-017/019; P3 varian, P5 authority, P4/P6 implementasi reuse nanti. |
| CAP-09 | DC-024/028/041/043–048 | BR-012 | OD-06/23; PROC-02/04/30/32 | GAP-003/004/010/013/017–019; P3 official meaning/tanggung jawab. |
| CAP-10 | DC-044–046/049/050/051/065/070 | BR-013/021; INV-006/011/012 | OD-03/06/23; PROC-30 KO2:KV2; ASSET-04/06 | GAP-008/009/010/018; P3 eligible handoff, P6 konsistensi nanti. |
| CAP-11 | DC-051–056/010/016/017 | BR-014/015/029; INV-008 | OD-03/07/14/23; ASSET-03/08/11 | GAP-008/011/014/018; P3 mutation/register, P5 assignment. |
| CAP-12 | DC-053/056–061 | BR-015/016; INV-007/008 | OD-07/19/23; ASSET-03/05/11 | GAP-008/009/010/013/014; P3 official lifecycle, P5 authority. |
| CAP-13 | DC-019/020/051/053/062–064/076 | BR-017/018/022/025; INV-007/013/018 | OD-14/15/23; ASSET-03/04/05/10/11/12; U-002 untuk BMU | GAP-007/008/009/010/013; policy/format/example validation, P8/P10 penerimaan nanti. |
| CAP-14 | DC-065–069/018/020 | BR-019/020; INV-009/010 | OD-23; ASSET-01/02/04/05 | GAP-005/006/010/011; P3 accepted ledger meaning, P6/P10 mappings nanti. |
| CAP-15 | DC-041/043/049–053/070 | BR-021/013/016; INV-011/012 | OD-06/23; ASSET-04/06/11; PROC-30 | GAP-008/009/010/018; P3 penyelesaian/handoff, accounting validation. |
| CAP-16 | DC-018/073–075/020/016 | BR-024/029; INV-015/016 | OD-14/16/23; ASSET-01/02/08/12; PROC-30/31 | GAP-011/012/013/016/019; P4/P6/P8/P10 import/trust/cutover. |
| CAP-17 | DC-019/064/068/070–072/076 | BR-022/023/025; INV-013/014/018 | OD-06/07/15/19/23; ASSET-03–09 | GAP-005–013/018; P3 signoff, P8/P10 authoritative report acceptance. |
| CAP-18 | DC-009/073–077 | BR-003/024/022 (meaning only) | OD-16/22; ASSET-12; NFR-P01–04 | GAP-016; P4/P6/P8/P9 workload/capacity; P2 does not define search/job architecture or performance. |
| CAP-19 | DC-005/010/016–020 and related domain records | BR-026; INV-006/008/015/017/018 | OD-03/06/07/08/23; PROC-30; ASSET-05–08/11 | GAP-008/010–014/017–019; P5/P9 audit visibility/retention, P4 storage later. |
| CAP-20 | DC-001/003/009/077; preferred terms in glossary | BR-003; INV-001 (domain meaning only) | OD-17/19/20/21; SRC-047/VIS-04; ENGINEERING_PRINCIPLES | P7/P8 UI/localization/accessibility/motion; no design output P2. |

## Seluruh acceptance P1 dan batas kontribusi P2

Tabel ini menunjukkan dukungan dan prasyarat, tidak menilai AC sebagai PASS aplikasi. AC-09/AC-10 menggunakan versi P1 yang disetujui APPR-002 setelah P1-PROD-01; seluruh pengiriman/revisi dan hasil konsisten tetap tercakup. Rumusan acceptance dan ukuran NFR tidak diubah.

| AC | CAP | P2 concept/rule handoff | Yang belum diselesaikan / fase berikutnya |
|---|---|---|---|
| AC-01 | CAP-01 | DC-002/004 account/actor reference; INV-016; BR-027 | GAP-015; P5/P9 eligibility, identity, session, recovery/throttling. |
| AC-02 | CAP-02 | DC-001–009; BR-001/002; INV-001 | GAP-014; P5 pemberian akses; P7 workspace experience. |
| AC-03 | CAP-02/04 | DC-001/007/021/022; BR-001/005 | GAP-001/014; P4 structure, P5 scope. |
| AC-04 | CAP-02/19 | DC-002–009/017; BR-002/026; OD-10 | GAP-014; P5 Super Admin controls and traceability. |
| AC-05 | CAP-03 | DC-005/009/077; BR-003; responsibility semantics | GAP-001–003/010; P3 action/waiting/next meaning; P7 presentation. |
| AC-06 | CAP-04 | DC-021–024/016; BR-005/009 | GAP-001/002/003/013; P3 official correction/handoff. |
| AC-07 | CAP-05/08 | DC-023–027/033/036–040/010–018; BR-004/005/010/011 | GAP-002–004/013/017/019; P3 method applicability and official procedure. |
| AC-08 | CAP-06 | DC-024/042/004/005/010; BR-006 | GAP-003/017; P3 applicable activity, participants/responsibility; P7 later. |
| AC-09 | CAP-07 | DC-028–035; BR-007/008; INV-003 | GAP-014/015/017; P3 onboarding/submission/revision applicability; P5 eligibility/representation. |
| AC-10 | CAP-07/03/19 | DC-032–035/009/010/016/017/077; BR-003/008/026; INV-004 | GAP-003/014/017; P3 shared outcome/status meaning; P5 authorized vendor/operator visibility. |
| AC-11 | CAP-08/10 | DC-011–018/049/050; BR-009/010/013; INV-005/006 | GAP-017/018/019; P3 variants/authority, later consistency implementation. |
| AC-12 | CAP-09 | DC-041/043–048/009; BR-012 | GAP-003/004/010/017–019; P3 official actor/event mapping. |
| AC-13 | CAP-10 | DC-044–046/049/050/051/065/070; BR-013; INV-012 | GAP-008/009/018; P3 eligibility/classification/acceptance; P6 consistency. |
| AC-14 | CAP-11 | DC-051–056/010/016; BR-014/015; INV-007/008 | GAP-008/011/018; P3 register/mutation/custodian. |
| AC-15 | CAP-12/19 | DC-056–061/010/016/017; BR-015/016/026; INV-007/008 | GAP-008/009/010; P3 official lifecycle; P5 approvals. |
| AC-16 | CAP-13/17 | DC-019/020/062–064/076; BR-017/018/022; INV-013/018 | GAP-007/008/009/010/013; official policy/format incl BMU and authoritative examples before P8 acceptance. |
| AC-17 | CAP-14 | DC-065–069; BR-019/020; INV-009/010 | GAP-005/006/010/011; actual code dictionary/ledger reconciliation. |
| AC-18 | CAP-15/10 | DC-041/043/049–053/070; BR-021/013; INV-011/012 | GAP-008/009/018; P3 accounting/definitive completion. |
| AC-19 | CAP-16 | DC-018/073–075; BR-024/029; INV-015/016 | GAP-011/012/013/019; P4/P6/P10 import identity/trust/cutover. |
| AC-20 | CAP-17 | DC-019/071/072/076/009; BR-022/023; INV-014 | GAP-007/010/012; accepted temporal behavior and signoff, P3/P10. |
| AC-21 | CAP-18 | DC-009/073–077 context/result meaning only | GAP-016; P4/P6/P8/P9 workload, operations/capacity; NFR-P01–04 remain P1 proposals. |
| AC-22 | CAP-19 | DC-010/016–018 and related actor/context; BR-026; lifecycle dispositions | GAP-014; P5/P9 audit visibility/retention/security, P4 storage. |
| AC-23 | CAP-20 | Preferred Indonesian and English terms in DOMAIN_GLOSSARY | P7/P8 localization/switch/layout validation; OD-17. |
| AC-24 | CAP-20 | DC-009/077 meaningful work/status terminology | P3/P7 interactions/return, P7/P8 responsive and accessibility checks; no UI certification. |
| AC-25 | CAP-20 | DC-003/009/077 for context and work only | OD-21; P7/P8 visual/motion/reference adaptation; no raw video replay P2. |
| AC-26 | CAP-01–20 | DC-013/018/023 external reference/evidence; BR-027 | OD-12/13; P3 validates applicable official duties in external channels; P4/P5/P9 keep V1 independence. |
| AC-27 | CAP-01–20 | Domain model adds no mandatory paid source/integration; COST_POLICY locator | OD-18; P9 cost inventory/alternatives, GAP-015 recovery channel. No new paid approval. |

## Seluruh Owner directions

| OD (canonical record) | P2 trace / later obligation |
|---|---|
| [OD-01](../00-governance/DECISION_LOG.md#od-01) | Shared DC relationships and provenance; CAP-02/19; P4 integrated architecture later. |
| [OD-02](../00-governance/DECISION_LOG.md#od-02) | DC-003; BR-002; CAP-02; P7 workspace presentation. |
| [OD-03](../00-governance/DECISION_LOG.md#od-03) | DC-049/050 and shared origin; BR-013; INV-006/012; CAP-10/19. |
| [OD-04](../00-governance/DECISION_LOG.md#od-04) | DC-001/007; BR-001; CAP-02/04; AC-03. |
| [OD-05](../00-governance/DECISION_LOG.md#od-05) | DC-021–024; BR-005; GAP-001/002 remain; CAP-04. |
| [OD-06](../00-governance/DECISION_LOG.md#od-06) | Conceptual relationship coverage across DC-021–076; no official ordered workflow; BR-004/012/013/021/023. |
| [OD-07](../00-governance/DECISION_LOG.md#od-07) | DC-009/071/072; BR-003/023; CAP-03/17; no measured benefit claim. |
| [OD-08](../00-governance/DECISION_LOG.md#od-08) | DC-011–018; BR-009; INV-005/006; CAP-08/19. |
| [OD-09](../00-governance/DECISION_LOG.md#od-09) | DC-005–009; BR-002; INV-002; responsibility dimensions; GAP-014. |
| [OD-10](../00-governance/DECISION_LOG.md#od-10) | DC-006 Super Admin direction; BR-002; AC-04; P5 controls remain. |
| [OD-11](../00-governance/DECISION_LOG.md#od-11) | DC-002 account reference; CAP-01/AC-01; P5 local-auth contract and GAP-015. |
| [OD-12](../00-governance/DECISION_LOG.md#od-12) | BR-027; DC-013/018/023 references; AC-26; institutional integration FUTURE. |
| [OD-13](../00-governance/DECISION_LOG.md#od-13) | BR-027; AC-26; P4 boundaries only when justified/authorized. |
| [OD-14](../00-governance/DECISION_LOG.md#od-14) | BR-017/024; ASSET-12 remains legacy behavior; GAP-007/013. |
| [OD-15](../00-governance/DECISION_LOG.md#od-15) | DC-019/062/076; BR-017/022; INV-013; GAP-007. |
| [OD-16](../00-governance/DECISION_LOG.md#od-16) | CAP-16/18; P4/P6/P8/P9 scale validation; GAP-016 remains outside P2 capacity decisions. |
| [OD-17](../00-governance/DECISION_LOG.md#od-17) | Indonesian preferred glossary with English equivalents; AC-23; P7/P8 language experience. |
| [OD-18](../00-governance/DECISION_LOG.md#od-18) | [COST_POLICY](../00-governance/COST_POLICY.md); AC-27; no paid dependency/exception added. |
| [OD-19](../00-governance/DECISION_LOG.md#od-19) | DC-005/009/077; BR-003; AC-05/10/20; P3/P7 exact process/presentation. |
| [OD-20](../00-governance/DECISION_LOG.md#od-20) | DC-003/006/007/005; BR-002; INV-001; AC-02. |
| [OD-21](../00-governance/DECISION_LOG.md#od-21) | DC-009/077 only domain implications; AC-25; P7 visual/motion reference preserved. |
| [OD-22](../00-governance/DECISION_LOG.md#od-22) | CAP-18/AC-21; NFR-P01–04 unchanged; GAP-016, P4/P6/P8/P9 validation. |
| [OD-23](../00-governance/DECISION_LOG.md#od-23) | Provider DC-028–035, Persediaan DC-065–069, KDP DC-070, downstream DC-049/050, package/event/document/report relationships; BR-006–025; hypothesis qualification retained. |

## Penilaian setiap GAP oleh P2

**A** = dapat diselesaikan sekarang dengan authority/evidence tersedia; **B** = semantik dapat diperjelas sebagian tetapi pertanyaan resmi belum terjawab; **C** = keputusan utama di luar kepemilikan P2; **D** = jawaban yang diminta tetap sepenuhnya terblokir. Pengamanan konseptual bukan jawaban atas conflict atau bukti current authority. Semua 20 status kanonik tetap **OPEN**, tidak ada RESOLVED/CLOSED/new GAP dan tidak ada perubahan register dari penilaian ini.

| GAP | Kelas | Kontribusi P2 / tetap terblokir | Source / rule locator | Validasi atau pemilik fase berikutnya |
|---|---|---|---|---|
| GAP-001 | B | Unit asal, penanggung jawab dan pengaju dibedakan; approval path resmi belum tersedia. | OD-04/05; PROC-04/06/08/13; BR-001/005 | Owner + procurement representative: route, delegation, exception; P3/P5. |
| GAP-002 | B | Need/request/RUP/package dibedakan; ownership persiapan/publikasi/revisi/cancel RUP belum dipilih. | PROC-06/08; BR-005 | Current approved RUP procedure + domain confirmation; P3. |
| GAP-003 | B | Method applicability, assignment dan fungsi tanggung jawab dibedakan; aktor per metode tetap belum disahkan. | PROC-01–27/30; BR-002/004/006 | Procurement specialist + Owner; P3/P5. |
| GAP-004 | B | Preparer/checker/issuer/signer dipisahkan; semua pemetaan resmi tetap belum ditetapkan. | PROC-02/10/12/22; PR-F02; BR-011 | Current authoritative procedure per method/delegation; P3/P5. |
| GAP-005 | B | Konsep versioned dictionary, raw code dan ledger terpisah; meaning/sign/effective date aktual belum tersedia. | ASSET-01/02/04/05; BR-019/020 | Finance/inventory custodian: dictionary + historical versions + examples; P3/P6. |
| GAP-006 | D | Tidak ada authority yang menjawab apakah P01/P02 alias/typo/version/distinct. Raw/report provenance dapat ditelusuri; actual mapping sepenuhnya blocked. | ASSET-01 Sheet2 K / ASSET-02 column 11 versus ASSET-04 Neraca Psd E6/C44 / ASSET-05 Sheet1 F4; INV-010/BR-020 | Inventory/Finance approves explanation and reversible mapping with reconciliation; P6/P10. |
| GAP-007 | B | Policy version dan jenis cutoff/periode dibedakan; formula, commencement, daily treatment, rate/life/rounding belum disahkan. | ASSET-03/12; OD-14/15; BR-017/022 | Approved accounting instrument + authoritative examples; P3/P6/P8. |
| GAP-008 | B | Correction/development/reclassification/removal/KDP dibedakan dari current representation; efek akuntansi/historical period tetap blocked. | ASSET-05/11/12; BR-014/016/021 | Asset/Finance policy, reversals and reconciliable examples; P3/P6/P10. |
| GAP-009 | B | Konsep policy/version/scope/comparator/basis tersedia; threshold value/applicability tidak dipilih. | ASSET-10 Akun C4:D5 / Kapitalisasi B1:C11; BR-018 | Approved instrument, category, comparator, units and dates; P5/P10. |
| GAP-010 | B | Kasus/selisih/tindak lanjut/waiting/signoff dibedakan; Finance owner/cadence/official output belum ditetapkan. | ASSET-04–09; BR-023 | Owner + Finance/Asset/Procurement confirm official responsibility/evidence; P3/P7/P10. |
| GAP-011 | B | Source/candidate/issue/cleansing/accepted record and identity uncertainty dibedakan; accepted format, full field meaning/duplicates/migration belum final. | ASSET-01/02/08/12; PROC-30/31; BR-024/029 | Later source inventory and approved samples/identity/validation; P4/P6/P8/P10. |
| GAP-012 | C | P2 dapat menandai uncertainty asal; authority/write ownership during cutover merupakan keputusan P3/P9/P10. | OD-01/03; ASSET-06/08; BR-024/026 | Approved cutover/write ownership, reconciliation/fallback/signoff. |
| GAP-013 | D | Available evidence tidak menjawab sumber mana current/formally authoritative/latest HTML. Semua dependent official rule tetap membutuhkan proof. | PR-F01; ASSET-04/10/12; BR-004/010/017/018/025 | Source custodian confirms issuer/version/applicability/supersession; all dependent phases. |
| GAP-014 | B | Enam dimensi responsibility tersedia; role list/assignment/scope/segregation/permissions final tetap belum approved. | OD-09/10/20; BR-002; INV-001/002 | Owner validates model and scenarios; P5 backend policy. |
| GAP-015 | C | Account reference saja; provisioning/eligibility/recovery/security merupakan keputusan P5/P9. | OD-11/12; ASSET-10; AC-01; INV-016 | Owner/security/operations current local contract and usable recovery channel. |
| GAP-016 | C | Konteks skala produk dipertahankan; workload/capacity/budgets belum dapat diputuskan P2. | OD-16/22; ASSET-12; AC-21/NFR-P01–04 | P4/P6/P8/P9 representative workload, funded capacity and measurement. |
| GAP-017 | B | Document/source/generated/signed/variant and method context dibedakan; clauses/numbering/signature/variant actual belum approved. | PROC-21 versus method wording; PROC-30 Isian Nomor; BR-009/010/011 | Procurement custodian authoritative variants/numbering/signers; P3/P5. |
| GAP-018 | B | Classification decision/draft/domain acceptance tanggung jawab terpisah; inspected acceptance/handover/payment trigger resmi belum dipilih. | PROC-02 steps 16–20 / PROC-04 k–l / PROC-30 KO2:KV2; BR-013; INV-012 | Procurement/Asset/Finance validate trigger/owner/exception; P3/P6. |
| GAP-019 | B | Source-field/output-trust distinction diperjelas; canonical field/clauses/formula/dependency/cached values actual tetap belum tervalidasi. | PROC-30 SPJ PL C1267 and PROC-31; BR-010/024 | Authorized domain review and authoritative output/field examples; P3/P6/P10. |
| GAP-020 | C | P1 future/outside V1 boundary dipertahankan; relevance/procedure borrowing tidak menjadi domain V1 baru. | PROC-28/29; BR-028; V1_SCOPE FUT-04 | Separate future Owner scope authorization, then custodian validation/P3/P5 if included. |

Counts: **20 reviewed; A 0; B 14; C 4; D 2; resolved 0; OPEN 20; new gaps 0**. B = GAP-001–005/007–011/014/017–019; C = GAP-012/015/016/020; D = GAP-006/013. Klasifikasi ini adalah hasil review P2 atas ruang kontribusi, tidak mengubah status resmi atau menambahkan authority.

## Konflik dan keputusan yang tidak boleh diasumsikan

| Konflik / uncertainty | Evidence A dan B | Kontribusi yang aman / dependent decision |
|---|---|---|
| SPPBJ preparation versus issuance/signature | PROC-10 table 6 step 11 Pokja preparation; PROC-02 table 6 step 14 PPK issuance/signing; PROC-12 caveat | Responsibility distinctions in DOMAIN_RESPONSIBILITIES; BR-011; GAP-004 tetap. |
| P01 versus P02 | ASSET-01/02 raw stream P01; ASSET-04/05 report/journal opname P02 | Source provenance and dictionary/mapping concept; BR-020/INV-010; GAP-005/006 tetap. |
| Tender heading versus direct-procurement body | PROC-21 heading and body; PROC-14/20 method examples | Applicability concept BR-004/010; GAP-003/017; no interchangeable procedure. |
| Legacy arithmetic versus accounting authority | ASSET-12 engine/static behavior; OD-14/15 and absent current approved instrument | Policy/time concepts BR-017/022; GAP-007/013; no formula/daily inference. |
| Historical thresholds versus policy authority | ASSET-10 Akun/Kapitalisasi reference; no verified instrument/effective date | Capitalization policy concept BR-018; GAP-009/013; no numeric minima copied. |
| Workbook output/dependency versus trusted structured data | PROC-30 formula/output families + SPJ PL C1267; PROC-31 corresponding headers | Structured/output/source-trust distinctions BR-009/010/024; GAP-019; header match does not resolve value/formula trust. |

Keputusan yang dibutuhkan dikelompokkan dalam [DOMAIN_DECISION_REQUESTS](DOMAIN_DECISION_REQUESTS.md); tidak memerlukan sesi pertanyaan baru untuk menyelesaikan paket semantik P2. P2 disetujui APPR-003 dengan seluruh status/authority/dependency tetap berlaku. Safe next action setelah checkpoint terverifikasi: Owner separately authorizes P3 — Workflows, Routes & Interactions. P3 tetap NOT AUTHORIZED.
