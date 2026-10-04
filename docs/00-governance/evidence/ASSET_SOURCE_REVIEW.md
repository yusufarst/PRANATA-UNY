# Asset, inventory, finance, and legacy source review

Status: APPROVED | Custodian: Planning Agent | Inspection date: 2026-10-04 (Asia/Jakarta). This review does not approve accounting policy, define future workflows, or authorize P1-P11 work.

## Inspection scope and handling

All source files below remain in the Git-ignored `reference-inputs/` directory. Only this sanitized review is durable repository evidence. No raw source, credential value, personal record, provider record, or financial transaction amount is reproduced here.

The review used the bundled Python runtime, standard-library XLSX ZIP/XML parsing and CSV parsing, and `pypdf`. The Spreadsheets and PDF skills informed the read-only workflow. No workbook was edited, recalculated, rendered, exported, or saved. XML dimensions describe stored ranges and may include formatting or blanks; they are not verified business-record counts.

Workbook inspection covered sheet metadata, structural headers, formula presence/function categories, and targeted categorical code checks. Arbitrary record values were withheld. Credential material was initially treated as metadata/header evidence; later confirmation reported only a boolean for nonempty literal password-column content. The separate `Akun` and `Kapitalisasi` reference sheets were selectively inspected for account/category/threshold structure. No credential values were disclosed. Numeric minima in those reference tables are deliberately omitted from this document.

PDF inspection covered complete extracted text on both pages. PDF page layout was not visually reviewed. Legacy HTML review was static code inspection of the locations stated below; the application was not launched and its calculations were not validated against an authoritative accounting baseline. There is no claim of successful financial reconciliation or comprehensive migration readiness.

The source classifications below describe evidentiary use. `OBSERVED_CURRENT_PROCESS` includes historical operational artifacts whose continuing use has not been confirmed. A familiar report title or a workbook named for BPK does not establish a legally authoritative policy source.

## Source-level inventory

Labels in this review are stable local evidence labels. The project-wide source inventory may cross-reference them.

| Label | Safe original filename | Category and inspected locations | Classification | What it may inform | Caveats and further validation |
| --- | --- | --- | --- | --- | --- |
| ASSET-01 | `Data Persediaan 2025.xlsx` | Inventory transaction export. Sheet `Sheet2`, stored range `A1:U24505`; header `A1:U1`; categorical transaction-code column `K`; targeted code checks at `K19421:K19554`, `K22624`, and `K24303:K24311`. | `OBSERVED_CURRENT_PROCESS` | Inventory import vocabulary, transaction-code provenance, location and account/report classification fields. | Historical export, not a transaction-code dictionary. Header repeats `nama_lokasi` at `B1` and `R1`; the meanings of the two locations require confirmation. No financial totals or record identities reviewed. P01/P02 discrepancy requires validation. |
| ASSET-02 | `Data Persediaan 2025(Sheet2).csv` | Inventory CSV export. Row 1 has 21 column labels; column 11 is `jns_trn`. Parsed with comma delimiter; UTF-8 decoding rejected the file and Windows-1252 decoding succeeded. Targeted transaction-code comparison with ASSET-01 covered column 11. | `OBSERVED_CURRENT_PROCESS` | Export format and same inventory-code discrepancy. | Transaction-code sequence matched ASSET-01; this does not establish full-cell or financial equivalence. Confirm original encoding, quoting, duplicate-header mapping, and official code meanings before a later import plan. |
| ASSET-03 | `Format Laporan SIMASET.xlsx` | Report-format workbook. `Neraca` stored range `A1:E47`, header `A6:E7`; `Laporan Aset` `A1:K42`, header `A6:K8`; `Lap. Penyusutan` `A1:J41`, header `A6:J7`. | `OBSERVED_CURRENT_PROCESS` | Balance/report presentation vocabulary, quantity/value mutation columns, depreciation and book-value columns. | No formulas were found in these three sheets. A format does not establish calculation policy, mandatory reporting scope, approval authority, or current official template status. |
| ASSET-04 | `Lap. Gabungan Thn 2025.xlsx` | Historical asset/report workbook. Sheet metadata inspected for `Gabungan` (`A1:H1956`), `KDP` (`A1:F42`), `Intra` (`A1:J1918`), `Intra New` (`A1:J1916`), `Ekstra` (`A1:H250`), `Neraca` (`A1:G43`), and `Neraca Psd` (`A1:J49`). Header rows 5-7 inspected; `Neraca Psd!D6`, `E6`, `C44` checked selectively. | `OBSERVED_CURRENT_PROCESS` | Combined/intra/extra reports, KDP and inventory balance reporting, code headings and evidence of multiple working report versions. | `Intra` and `Intra New` coexist without a validated precedence rule. Formula presence was inventoried, not audited or recalculated. `Neraca Psd!E6` explicitly labels physical opname with P02, conflicting with raw inventory evidence. No financial totals copied. |
| ASSET-05 | `jurnalsda-690000-122025.xlsx` | Journal/report export. `Sheet1` (`A3:G19`) has `jns_trn` at `B3` and code headings `B4:F4`; `jurnalsda-690000-122025` (`A1:L137`) has structural header `A1:L1`; targeted code checks in column `J` and categorical correction-account terms in column `I`. | `OBSERVED_CURRENT_PROCESS` | Journal linkage vocabulary, code interpretation conflicts, evidence that correction records appear in accounting exports. | Code headings include P02. Formal meaning, debit/credit conventions, origin, period and reconciliation authority are not validated. The review did not audit amounts, journal balancing or source totals. |
| ASSET-06 | `Rekap Bersama B 2024 Share.xlsx` | Historical reconciliation/capital-expenditure workbook. All six sheet metadata inspected: `5320102` (`A1:J68`), `5320104` (`A1:H13`), `5330101` (`A1:H207`), `Sheet3` (`A3:B21`), `GD. KDP` (`A1:G19`), and `Sheet2` (`A1`). Structural/header checks at `5320102!F3:G3`, `5320104!G3`, `5330101!F4:G4`, `GD. KDP!A1:E1`. | `OBSERVED_CURRENT_PROCESS` | Historical classification, unit/description fields and KDP-related recap structure. | File title and account-number sheet names alone do not prove Finance ownership, approval or reconciled status. Sum/subtotal formula presence was observed; amounts, formula correctness and cross-file matching were not validated. |
| ASSET-07 | `Rekap Pemeriksaan BPK (REVITALISASI) 2024.xlsx` | Historical audit-support recap. `BELANJA MODAL` stored range `A2:F314`; header `A2:F2`, particularly category labels at `A2`, `C2`, `E2`, `F2`. | `OBSERVED_CURRENT_PROCESS` | Audit-support evidence fields and the need to trace expenditure descriptions and dates. | A workbook named for BPK is not itself a BPK ruling or accounting policy. No governing instrument, audit sign-off or reconciled totals established by this inspection. Personal/provider/transaction values withheld. |
| ASSET-08 | `Rekap Revitalisasi.xlsx` | Historical capitalization/item-detail recap. All 37 sheet metadata and allowlisted labels within rows 1-30 inspected. Representative headers: `REKAP1!B3:F3`, `1!B4:G4`, `2!A4:G5`, `4!A3:E3`, `6!A3:F3`, `13!A3:F3`, `15!B3:F3`, `18!B4:H5`, `25!A2:D2`, `33!A4:H4`. | `OBSERVED_CURRENT_PROCESS` | Recap-to-item-detail evidence, unit/location/quantity/price-field vocabulary and repeated working-sheet variants. | Sheets with variant names, large stored ranges and different layouts are present. Header/metadata inspection does not establish duplicate records, authoritative version, completeness, or reconciled balances. No item descriptions, parties or amounts copied. |
| ASSET-09 | `Flow Test.xlsx` | Small operational evidence/flow workbook. `Sheet1` stored range `A1:R22`; structural labels in `A3:E3` and `P3:R3` identify SPJ description, nominal, provider, procurement documents, scan, treasurer and notes; `G5` contains the generic label NIB. | `OBSERVED_CURRENT_PROCESS` | Evidence of procurement-document/SPJ/treasurer information appearing together. | The source may inform later questions about handoff and document reuse. It does not prove an official approval sequence or responsibility matrix. Internal/provider/financial record values were not reproduced. |
| ASSET-10 | `User SIMASET.xlsx` | Credential-sensitive historical administration/reference workbook. `User SIMASET` stored range `A1:J28`: `A4` Username, `B4` Password; only nonempty-literal password presence confirmed. Separate `Akun` (`A1:F13`) reference headers `B3:D3` and category/threshold references `C4:D5`; `Kapitalisasi` (`A1:C11`) title `B1`, category/minimum-value headers `B4:C4`, category references `B5:C11` inspected selectively. | `OBSERVED_CURRENT_PROCESS` | Security-pattern evidence and existence of candidate capitalization/account reference tables. | Historical workbook contains plaintext credential data; V1 must not reproduce this security pattern. No credential or personal values copied. The threshold tables have no verified authoritative instrument/effective date in the inspected locations; category application and amount/comparison semantics remain unapproved. |
| ASSET-11 | `Susunan Menu Aplikasi SIMASET.pdf` | Menu reference. Complete text extracted/read from pages 1-2; page 1 covers acquisition/change/disposal/KDP menus and page 2 covers registers, depreciation/amortization and reporting. | `OBSERVED_CURRENT_PROCESS` | Existing category names, distinction between correction/development/reclassification and links to KDP/report evidence. | Menu names describe available functions, not official accounting treatment, permission grants or procedural order. Visual layout and actual application operation were not reviewed. |
| ASSET-12 | `index (1).html` | Legacy PRANATA implementation. Static inspection covered UI period selector 516-521; CSV upload/parser 1136-1196; category map 863-895; shared report engine 1212-1435; data-state/browser-array references 899-907 and 1199-1204; detail calculation 2358-2377 and 2470-2529. | `LEGACY_IMPLEMENTATION_BEHAVIOR` | Report-period behavior, calculation provenance, aggregation/filtering behavior and limits of legacy browser processing. | Implementation comments and outputs are not authoritative accounting policy. No runtime/accounting verification performed. Daily as-of treatment, threshold authority and transaction-aware correction/reclassification semantics cannot be inferred as approved rules. |

ASSET-08 sheet names inspected: `REKAP1`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `10`, `11`, `12`, `13`, `13 (2)`, `14`, `15`, `17`, `17 (2)`, `18`, `19`, `20`, `21`, `22`, `23`, `24`, `25`, `Detail1`, `Sheet1`, `25 (2)`, `27`, `28`, `29`, `30`, `31`, `33`, `34`, `Sheet33`.

## Safe observed findings

### Inventory transaction-code discrepancy

Classification: `OBSERVED_CURRENT_PROCESS` for each source observation; `UNRESOLVED_GAP` for official code meaning and mapping.

ASSET-01 `Sheet2!K1` identifies the transaction-code field as `jns_trn`. Its categorical code scan found P01 and no P02. ASSET-02 column 11 has the same code sequence as the workbook; P01 occurs 144 times in both source forms. This is a structural/code aggregate, not a financial statistic. Selective descriptions at workbook rows 22624 and 24303-24311 associated with P01 contain the categorical word `opname`; descriptions and record identities are withheld.

ASSET-04 `Neraca Psd!D6` explicitly says `Pembelian (M02)`, while `E6` explicitly says `Hasil Opname Fisik (P02)`. `C44` also contains P02. ASSET-05 `Sheet1!B4:F4` contains K01, K09, M01, M02 and P02, and P02 appears in the journal transaction-code column.

The observed discrepancy concerns physical-opname coding. It is not evidence that P01 and P02 are purchase codes. The reviewed sources do not establish whether the distinction reflects export/system version, a mapping error, different transaction families, or another rule. No code normalization has been approved. Official definitions for the other observed transaction codes also remain unverified.

### Legacy report-period and depreciation behavior

Classification: `LEGACY_IMPLEMENTATION_BEHAVIOR`; official policy remains `UNRESOLVED_GAP`.

ASSET-12 selector lines 516-521 offers annual, semesters 1/2 and quarters 1/3. The reviewed UI/engine does not offer quarter 2/4, monthly or arbitrary daily as-of selection. Engine lines 1216-1229 assign month endpoints to the offered periods. Posting-date cutoff at lines 1256-1278 compares year and month, not a day-of-month cutoff.

At lines 1335-1359 the engine reads useful life in months and an imported monthly depreciation field, then replaces that monthly rate with acquisition value divided by life. Lines 1373-1386 calculate elapsed months from acquisition year/month, cap used months to life, cap accumulation to acquisition value, and prevent negative period expense/book value. Date parsing fallback defaults missing posting information to the selected year and January; acquisition parsing falls back to posting year and January (1259-1272, 1300-1330). The detail view repeats month-based calculations at 2470-2529.

These are code observations. They do not approve depreciation start timing, daily proration, posting versus acquisition treatment, rounding, missing-date defaults, residual value, corrections, reclassification, development or early disposal behavior. Owner direction OD-14/OD-15 already requires that distinction.

### Capitalization reference and intra/extra classification

Classification: `OBSERVED_CURRENT_PROCESS` for the workbook reference table; `LEGACY_IMPLEMENTATION_BEHAVIOR` for HTML; authority/applicability remain `UNRESOLVED_GAP`.

ASSET-10 `Kapitalisasi!B1:C11` is a category table headed minimum acquisition value. Category rows distinguish buildings, machinery/equipment, several vehicle classes, and road/irrigation/network assets. `Akun!C4:D5` describes below-capitalization expense-account categories and textual less-than thresholds. The related equipment/building category entries are broadly consistent across these two sheets; the unusual number-separator notation in `Akun` still needs confirmation. No numeric minima are reproduced here.

The inspected locations do not establish a signed regulation/decision, issuer, effective date, threshold basis, equality rule, tax inclusion, component grouping, or applicability to development and corrections. Table presence is evidence to locate authoritative policy, not approval of the table itself.

ASSET-12 lines 1250-1254 and 1294-1295 derive intra/extra classification from imported `flag_sak` values Y/T. An unrecognized flag becomes T. The reviewed engine does not apply category/value capitalization minima. Imported flags, accounting-policy authority and classification validation therefore remain separate questions.

### Corrections, development, reclassification and KDP

Classification: `OBSERVED_CURRENT_PROCESS` for menu/report categories; treatment remains `UNRESOLVED_GAP`.

ASSET-11 page 1 lists acquisition completion with KDP and direct completion, reclassification in/out, correction (+/-), direct development, development with KDP, and KDP correction/disposal functions. Page 2 lists depreciation/amortization, journal transactions, balance and mutation reporting. ASSET-04 has a KDP report sheet, and ASSET-06 has a `GD. KDP` recap sheet. ASSET-05 contains correction-account category terms.

ASSET-12 lines 1395-1422 classify a row into opening stock or period mutation using posting year/month, then aggregate quantity/value. The reviewed engine does not interpret transaction-code categories for separate correction, reclassification, development or disposal accounting. It is insufficient evidence for the official accounting treatment of those actions. A menu list also does not establish the correct journal or approval sequence.

### Historical import and reconciliation constraints

Classification: `INFERENCE`, based on the stated observations and Owner directions OD-03/OD-16.

ASSET-12 holds uploaded rows in browser memory (`rawData`, 899 and 1183), filters those rows in the browser (1234), and aggregates by item code in a `Map` (1338). It imports semicolon-separated CSV (1169-1172). ASSET-02 is comma-separated and uses different inventory field names, so direct compatibility with the legacy asset master parser is not established. These source families represent different data shapes; this is not a finding that the inventory export is defective.

Duplicate location headers in ASSET-01/02, multiple intra report variants in ASSET-04 and repeated working sheets in ASSET-08 require provenance and precedence validation before later migration planning. Browser-only processing is not a demonstrated capacity solution for Owner direction OD-16. No performance benchmark, key strategy, cutover rule, migration specification or future architecture was produced in P0.

ASSET-06/07/08/09 show historical recap and evidence structures but do not establish who owns reconciliation, what makes a period final, or which system wins during corrections/cutover. Those questions require Owner and domain validation in later authorized planning.

## Candidate questions for the canonical gap register

These questions are evidence input to `GAP_REGISTER.md`. That register owns project-wide gap status, assignment and closure; this review does not maintain a competing decision/gap workflow.

| Evidence question | Supporting sources/locations | Validation needed before later approval |
| --- | --- | --- |
| What is the official inventory transaction-code dictionary, especially P01 versus P02 physical-opname coding? | ASSET-01/02 transaction column; ASSET-04 `Neraca Psd!D6:E6`; ASSET-05 `Sheet1!B4:F4`. | Asset/inventory/Finance confirmation, dictionary source and version, sample reconciliation by an authorized domain reviewer. |
| What is the authoritative depreciation/amortization policy for each report cutoff, including an arbitrary daily as-of date? | ASSET-03 depreciation headings; ASSET-12 1216-1229 and 1335-1386. | Governing instrument, applicability and effective dates, acquisition/posting start convention, rounding/residual life and cutoff semantics. |
| What instrument governs capitalization and intra/extra classification? | ASSET-10 `Akun!C4:D5`, `Kapitalisasi!B1:C11`; ASSET-12 1250-1254. | Issuer/authority, effective date, category bases, amount/comparison interpretation and exceptional transactions; confirm legacy flag semantics. |
| How do correction, reclassification, development, KDP completion and disposal affect accounting and historical reports? | ASSET-11 p1-p2; ASSET-04 KDP; ASSET-05 correction categories; ASSET-12 1395-1422. | Official business/accounting treatment and responsible domain review. Do not substitute legacy row aggregation for policy. |
| Who owns Finance/asset reconciliation and period finality? | ASSET-06/07/08 recap structures; ASSET-04 report variants; ASSET-09 document/SPJ/treasurer labels. | Official responsibility, source precedence, finality/sign-off evidence and correction ownership. |
| Which historical versions and fields are authoritative for migration/cutover? | ASSET-01/02 duplicate location labels and export shapes; ASSET-04 intra variants; ASSET-08 repeated sheets; ASSET-12 parser/aggregation. | Source owners, versions, record identity and field dictionary, source-of-truth precedence during cutover, supported input formats and quality checks. |
| What retention and remediation apply to credential-sensitive source material? | ASSET-10 credential table presence. | Confirm custodianship and handling of historical credentials without disclosing values. Secure V1 credential handling must follow approved Owner direction and later authorized security planning. |

## Coverage limits and continuation boundary

All 12 files assigned to this review received the bounded inspection stated above. No assigned file was unsupported by format. This is partial substantive inspection of the workbooks and static HTML, not an audit of every record/formula. Financial totals, record matching, formula recalculation, workbook visuals, PDF visuals, legacy runtime behavior and current institutional applicability remain unverified.

Procurement SOPs and procurement artifact files, `Input - Konsultansi.xlsx`/`.csv`, facility-loan documents, screenshots and video are outside this review's file ownership. Their absence here does not imply missing project-wide inspection; consult the canonical source inventory and other evidence reviews.

P0 may record the observations and unresolved questions. P1-P11 remain unauthorized until the Owner approves the required continuation. No application code, schema, dependencies, execution Task, commit or push was created by this review.
