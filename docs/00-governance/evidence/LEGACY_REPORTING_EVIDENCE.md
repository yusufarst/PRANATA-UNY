# Legacy reporting and depreciation evidence

Status: PROPOSED under PCR-001 (FRZ-001 change control) | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent

This record states the calculation behaviour of the legacy PRANATA page `index (1).html` ([SRC-027 / ASSET-12](ASSET_SOURCE_REVIEW.md)), re-read in full on 2026-11-01 at the Owner's direction (U-012 §5), and how V1 uses it. The page is the **primary legacy behavioural reference**. It is not accounting or legal authority, so GAP-007, GAP-009 and GAP-024 stay OPEN. Its formulas are the provisional DRAFT defaults of `asset.depreciation.uny` and `reporting.formats.uny` ([POLICY_REGISTRY §5](../../04-architecture/POLICY_REGISTRY.md#5-contoh-isi-lain-bentuk-bukan-nilai-resmi)), except where §9 records a correction.

## 1. Source and limits

| Item | Value |
|---|---|
| Inspected | Complete static reading of the HTML and inline JavaScript (CSV import, period engine, depreciation, BMU, Neraca BMU, five-year history, PDF and Excel export). The page was not executed against institutional data. |
| Comparison output | The Owner names an 84-page "Laporan Barang Milik Universitas (Gabungan)" for the third quarter of 2026 (U-012). It was **not available** to the planning session; the comparison is the local parity check of §10. |
| Excluded | The page hard-codes a signatory title, personal name and staff identifier in the PDF signature block. None is reproduced here or anywhere in the repository; V1 takes signatories from configured roles and assignment snapshots ([DOCUMENT_ENGINE](../../04-architecture/DOCUMENT_ENGINE.md)). No amounts or records are copied. |

## 2. Input dialect

- Semicolon-separated CSV; the header row is the first of the first 15 lines that contains `kd_barang`, `nama_barang` or `total harga perolehan`, and earlier lines are discarded.
- Columns read, by header name (first match, case-insensitive): `kd_barang` / `Kode Barang` (item code; rows without it are ignored); `Nama_barang` / `Uraian Barang`; `merk`; `flag_sak` (`Y` = intrakomptabel, `T` = ekstrakomptabel, any other value treated as `T`); `tgl_pembukuan` / `Tgl Pembukuan` / `tanggal_pembukuan` (posting date); `tgl_perolehan` / `Tgl Perolehan` (acquisition date); `tahun_anggaran`; `Penggolongan` (classification text; its first 7 characters are the classification code); `Gol`; `Umur Aset dalam Bulan` (useful life in months, per row); `Penyusutan per Bulan` (read, then ignored); `kuantitas` / `Kuantitas` (blank = 1); `Total Harga Perolehan` / `Nilai Perolehan` (acquisition value); `subsubsatker` (unit).
- Dates: `yyyy-mm-dd`, `dd/mm/yyyy` or `dd-mm-yyyy`; otherwise a four-digit year alone with month 1. A missing posting date is treated as January of the report year, and a missing acquisition date as January of the posting year.
- Amounts: `Rp` and spaces removed; Indonesian and US separators are inferred heuristically (a single dot followed by exactly three digits is read as a thousands separator).

## 3. Periods and cutoff

| Period | Months covered | Opening position at the end of month | Position date |
|---|---|---|---|
| Tahunan (Y) | 1–12 | 0 (31 Dec of the previous year) | 31 Dec |
| Semester 1 (S1) | 1–6 | 0 | 30 Jun |
| Semester 2 (S2) | 7–12 | 6 | 31 Dec |
| Triwulan 1 (Q1) | 1–3 | 0 | 31 Mar |
| Triwulan 3 (Q3) | 7–9 | 6 | 30 Sep |

Quarters 2 and 4, months and arbitrary dates are not offered. A row is included for report year *Y* and end month *E* unless its posting year is later than *Y*, or equal to *Y* with a posting month later than *E*. Only year and month are compared; the day is ignored. Years 2023–2050 are selectable.

## 4. Depreciation

Per source row, with acquisition value *v*, useful life *L* months, acquisition year/month *(ya, ma)* and posting year *yp*:

1. Monthly amount *m* = *v* / *L* exactly (0 when *L* = 0). The source column `Penyusutan per Bulan` is ignored.
2. Elapsed months at the end of month *M* of year *Y*: *n(Y, M)* = (*Y* − *ya*) · 12 + *M* − *ma*, clamped to [0, *L*]. The acquisition month itself is not depreciated; depreciation starts in the month after acquisition.
3. Opening accumulated *A₀* = min(*n(Y, P)* · *m*, *v*), where *P* is the opening month of §3; closing accumulated *A₁* = min(*n(Y, E)* · *m*, *v*).
4. Special case: when *Y* = *yp* and *P* = 0 (Y, S1, Q1), *A₀* = 0. Catch-up depreciation of an acquisition posted later than it was acquired therefore falls entirely into the period's expense. No such rule exists for S2 and Q3.
5. Period expense = max(0, *A₁* − *A₀*); book value = max(0, *v* − *A₁*); zero residual value. Rows acquired after *Y* are not depreciated. Ekstrakomptabel rows are depreciated like intrakomptabel rows whenever *L* > 0.
6. No rounding in the calculation. Values are shown and exported as Rupiah with 0 decimals, and totals are sums of unrounded values, so printed rows need not add up to printed totals.

## 5. BMU (Laporan Barang Milik Universitas)

A row is **opening** when it was posted before the period: posting year < *Y*, or posting year = *Y* and posting month ≤ *P*. Otherwise it is a **mutation** in the period. Closing = opening + mutation, for quantity and for acquisition value. Values are gross acquisition values, not book values. Only additions appear as mutations. A disposal or transfer out is simply absent from the source export, so the row disappears from every historical period; a negative source value would be summed as it is. Variants: Intrakomptabel (`Y`), Ekstrakomptabel (`T`) and Gabungan. Rows are marked trend up, down or stable.

## 6. Aggregation and grouping

Rows are aggregated by item code `kd_barang` alone. Name, flag, classification, useful life and dates come from the first row of that code. Groups are the 7-character classification code, named from a 12-entry dictionary (peralatan dan mesin, kendaraan dan alat angkut, gedung dan bangunan, jalan, irigasi, jaringan, aset tetap lainnya, aset tidak digunakan dalam operasi for equipment and for buildings, software, lisensi, aset tidak berwujud lainnya); an unknown code takes the text after position 7 as its name. A code with mixed intra/ekstra rows or different classifications is therefore attributed to a single flag and group in the Gabungan totals — the "duplicate classification keys" issue named by the Owner. The unit filter uses the row's current `subsubsatker`, not the unit at the report date.

## 7. Neraca BMU

Per classification code: BMU value (closing gross value) − accumulated depreciation = net value, with Intra, Ekstra and Gabungan variants and the position date of §3. It is an **asset position statement of Barang Milik Universitas**, not the University's complete financial balance sheet.

## 8. History, heuristics and exports

- Five-year history per asset row: years *Y*−4 to *Y*, with year-end positions for earlier years and the selected period for *Y*, and opening accumulated, expense, accumulated and book value per year.
- Heuristics: age band, "condition" and "recommendation" (including "Lelang / Hapus") derived from remaining depreciation life.
- PDF exports (A4 landscape for depreciation and BMU, portrait for Neraca) carry the directorate name, the report title with variant, the period or position date, grouped rows with totals, and a signature block dated with the export date. Excel exports have two title rows and a flattened group column.

## 9. V1 treatment

| Topic | Legacy behaviour | V1 provisional default (DRAFT, configurable) | Reason |
|---|---|---|---|
| Method and residual | Straight line, zero residual, accumulated ≤ value | Same | Kept |
| Start | Month after the acquisition month | `start_anchor: ACQUISITION_DATE`, `start_rule: MONTH_AFTER_ANCHOR`; `RECOGNITION_DATE` remains selectable | Kept; GAP-007 open (Owner provisional input SRC-052 said "after recognition") |
| Useful life | Per asset row from the source | `useful_life_source: ASSET_RECORD_THEN_CLASSIFICATION`: migrated assets keep their recorded life; new assets take it from the classification policy | Kept for history; policy-driven for new assets |
| Rounding | None; 0 decimals at display | `rounding: {scale: 2, mode: HALF_UP, basis: CUMULATIVE}`: accumulated = round(*v* · *n* / *L*); period amount = difference of rounded accumulated values; reports shown with 0 decimals from stored values | No drift between line sums and accumulated; legacy parity within Rp1 per row |
| Back-dated acquisition | Catch-up in the posting period for Y/S1/Q1 only | `catch_up: IN_POSTING_PERIOD` for every period type | Legacy S2/Q3 inconsistency not reproduced |
| Ekstrakomptabel depreciation | Depreciated when life > 0 | `apply_to_flags: ["INTRA", "EKSTRA"]` | Kept as legacy default; treatment open under GAP-007/009 |
| Cutoff | Posting year and month | As-of date with day precision; month-end period ends give the legacy result | Superset |
| Missing or unreadable dates | Report-year January assumed | Validation error at intake; no silent default | Data-quality defect not reproduced |
| Periods | Y, S1, S2, Q1, Q3 | Y, S1, S2, Q1–Q4 and any as-of date; Q3 keeps July–September with the 30 June opening | Superset |
| BMU mutation | Additions only; removed rows vanish from history | Net mutation = additions + internal transfers in − internal transfers out − reductions, by report scope (§9.1); closing = opening + net mutation and equals the asset position report of the same scope and date | Historical periods stay correct after disposal and transfer |
| Internal transfer between UNY units | Not represented: the row moves to its current unit in every period, past and future | Reported by scope (§9.1): the origin unit shows a transfer out and the receiving unit a transfer in, on the effective date of the unit change; for UNY as a whole, and for any scope that contains both units, the movement nets to zero and is neither an addition nor a reduction | Unit, consolidated and intermediate views reconcile without double counting |
| Aggregation key | Item code alone, attributes from the first row | (classification code, item code, flag); group per 7-character code; the 12 names are DRAFT data of `asset.classification` | Mixed codes no longer misattributed |
| Unit | Current unit of the row | Unit on the report date from the dated unit history (`CHANGE_UNIT` transactions); as-of positions for any past date are reproducible | Historical correctness |
| Neraca BMU | Gross − accumulated per classification | Same, titled as an asset position statement | Not a financial balance sheet |
| History | Five years per row | Five-year schedule per asset in PG-309 | Kept |
| Heuristics | Condition and disposal recommendation from remaining life | Not reproduced | Condition is not determined by depreciation age (PRODUCT_OVERVIEW principle 4) |
| Signatory | Hard-coded person | Role and assignment snapshot from configuration | Privacy and authority |
| Exports | PDF and Excel per variant | Same report family through the export queue (TASK-023) | Kept |

### 9.1 Movements by report scope

A report scope is a set of UNY units: one unit, a subtree, or the whole university. For every asset movement in the period:

| Movement | Source in V1 | Effect on a scope |
|---|---|---|
| Acquisition from outside UNY (purchase, donation, transfer in from another entity, construction completion, found) | `asset_transactions` REGISTER/RECOGNIZE; `acquisition_type` | Addition to the scope that holds the asset |
| Internal transfer between UNY units | WF-018 `asset_mutation_lines(from_org_unit_id, to_org_unit_id)` posted as `CHANGE_UNIT` on its `effective_date` | Origin in scope, destination outside: transfer out. Destination in scope, origin outside: transfer in. Both in scope, including UNY as a whole: no movement — nets to zero |
| Disposal or transfer out of UNY | WF-021 `asset_disposals` (`disposal_method` AUCTION, DESTRUCTION, TRANSFER, GRANT, OTHER) posted as `DISPOSAL` | Reduction in the scope that holds the asset; reduces the UNY total |
| Correction or development | WF-020 transactions with `value_delta` | Addition or reduction of value in the holding scope |

Rules:

1. Each asset belongs to exactly one unit on any date, given by the dated unit history. Opening and closing positions use the unit on the opening and closing dates. An asset is never counted in two units on the same date.
2. A transfer carries the asset's gross value and accumulated depreciation together. Depreciation is calculated per asset and is unchanged by a transfer. In unit scopes, a period's depreciation line is attributed to the unit that holds the asset at the end of that line's period. Consolidated expense is the sum of the lines and does not change because of transfers.
3. For each scope and period: closing value = opening value + additions + transfers in − transfers out − reductions; closing accumulated = opening accumulated + period expense + accumulated transferred in − accumulated transferred out − accumulated on reductions. For UNY as a whole the transfer terms are zero.
4. The sum of the closing positions of all units equals the UNY closing position, on every date.

Every number produced under a DRAFT version carries the simulation label until a version of `reporting.formats` and `asset.depreciation` is officially effective on the report's reference date (D-28). Official activation needs the GAP-024 format authority and the GAP-007/009 accounting answers.

## 10. Parity checks

1. **Test oracle in code.** Tests implement §3–§7 from this record (the HTML is never copied into the repository) and compare V1 against the oracle on synthetic data, including mixed-flag codes, back-dated acquisitions, life 0, acquisitions after the report year, Q3 and S2 openings, and disposals. Internal transfers have no legacy equivalent; they are tested against the identities of §9.1.
2. **Local parity with real outputs.** On the Owner's machine and outside the repository, the executor runs the same comparison against the Owner's source CSV export and the 84-page Q3 2026 BMU PDF supplied locally. Only the outcome is recorded: pass/fail per report variant, and the count and category of differences (rounding, cutoff, classification key, disposal, data quality). No amounts, item names or personal data are recorded.
3. A difference explained by a §9 correction is accepted. Any other difference is reported to the Owner as a finding, not adjusted silently.

## 11. GAP status

GAP-007 (depreciation policy), GAP-009 (capitalization and intra/ekstra classification) and GAP-024 (official BMU definition, format and mandate) stay **OPEN**. This record supplies the legacy formulas the Owner said are already in the HTML, so V1 does not ask for them again. It does not establish their legal or accounting authority.
