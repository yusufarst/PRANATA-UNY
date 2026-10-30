# Policy Registry — aturan resmi sebagai data berversi

Status: P4 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P4 | ADR: [ADR-0003](../adr/ADR-0003-versioned-policy-registry.md)

Policy Registry adalah mekanisme agar PRANATA dapat dibangun lengkap **tanpa mengarang aturan resmi**. Setiap nilai yang bergantung pada instrumen institusi atau regulasi — ambang metode, pejabat penetap, varian dokumen, penomoran, rantai persetujuan gudang, ambang kapitalisasi, metode penyusutan, kode transaksi — disimpan sebagai **versi kebijakan** dengan rujukan sumber, rujukan GAP dan status. Kode aplikasi hanya mengetahui *struktur* aturan (skema JSON per keluarga) dan cara mengevaluasinya. Ini mewujudkan CAP-23, BR-031 dan INV-022.

## 1. Keluarga kebijakan

| Keluarga (`family`) | Isi | Paket contoh (`key`) | GAP gerbang | Konsumen |
|---|---|---|---|---|
| `procurement.regime` | Pemetaan sumber dana + tanggal → rezim; daftar rezim | `procurement.regime.uny` | GAP-026 | WF-032 |
| `procurement.methods` | Per rezim: metode, pita nilai per jenis pengadaan, kriteria kualitatif, syarat DPT, kanal resmi, penanda tumpang tindih | `procurement.methods.pr34-2023`, `procurement.methods.perpres16-2018-46-2025` | GAP-026/027/029 | WF-032, WF-005 |
| `procurement.authority` | Fungsi penetap metode, penyusun/penetap HPS, penetap pemenang menurut nilai, empat tanggung jawab SPPBJ, penanda tangan per dokumen | `procurement.authority.pr34-2023` | GAP-003/004/014 | WF-005–015/032 |
| `procurement.documents` | Varian dokumen per rezim/metode/jenis, dokumen wajib per langkah, kelengkapan SPJ, pemetaan template | `procurement.documents.pr34-2023` | GAP-017/019 | WF-011–015/028 |
| `procurement.contract` | Bentuk bukti perjanjian menurut nilai, jaminan, batas adendum dan pemberi pengecualian, denda keterlambatan, skema pembayaran | `procurement.contract.pr34-2023` | GAP-026/027 | WF-012/013/033 |
| `procurement.hps` | Kewajiban/pengecualian HPS, pajak, pembulatan, komponen konsultansi | `procurement.hps.pr34-2023` | GAP-019/027 | HPS |
| `procurement.evaluation` | Per rezim × metode × varian × jenis: metode evaluasi yang diizinkan dan tahapan bawaan, mode kualifikasi dan pemasukan, minimum undangan/peserta yang bersumber aturan, praktik setempat yang belum disahkan (informatif), pembanding HPS dan aturan koreksi aritmetik, mode penghargaan lingkup (beberapa penyedia hanya bila diizinkan), jumlah cadangan, aturan negosiasi (mis. E-Purchasing termurah lebih dahulu; kegagalan Penunjukan Langsung), penerapan reviu hasil sebelum kontrak | `procurement.evaluation.pr34-2023` | GAP-003/013/017/027/029 | WF-006, WF-008–WF-010 |
| `vendor.performance` | Kriteria dan skala penilaian kinerja penyedia (dasar PR 34/2023: kualitas, kuantitas, waktu), fungsi penilai menurut nilai, keterkaitan dengan evaluasi DPT | `vendor.performance.pr34-2023` | GAP-003/026 | WF-007, WF-014 |
| `procurement.budget` | Sumber rujukan anggaran, aturan komitmen dan pelepasan | `procurement.budget.uny` | GAP-028 | BR-035 |
| `vendor.dpt` | Katalog dokumen pendaftaran, langkah verifikasi, masa berlaku, evaluasi, pengeluaran | `vendor.dpt.pr34-2023` | GAP-026 | WF-007/008 |
| `warehouse.operations` | Per gudang: jenis permintaan, rantai keputusan, bentuk bukti serah terima/konfirmasi, aturan penutupan, kriteria pemeriksaan pengembalian, kewenangan transfer/penyesuaian | `warehouse.operations.alp`, `warehouse.operations.psrt` | GAP-025/023 | WF-023/030/031 |
| `inventory.codes` | Kamus kode transaksi institusi dan pemetaan kode historis | `inventory.codes.uny` | GAP-005/006 | WF-023/026, laporan |
| `asset.capitalization` | Ambang kapitalisasi, aturan tanggal pengakuan | `asset.capitalization.uny` | GAP-008/009 | WF-016/017 |
| `asset.depreciation` | Metode, masa manfaat per klasifikasi, awal penyusutan, pembulatan, cutoff | `asset.depreciation.uny` | GAP-007 | WF-022/027 |
| `asset.classification` | Pemetaan klasifikasi ke akun/kategori laporan | `asset.classification.uny` | GAP-022 | WF-029 F6, laporan |
| `asset.lifecycle` | Rantai keputusan mutasi, koreksi, pengembangan, reklasifikasi, penghapusan | `asset.lifecycle.uny` | GAP-008/013/014 | WF-018–021 |
| `reconciliation` | Jenis kasus, pemilik, penerima hasil, periode | `reconciliation.uny` | GAP-010/012 | WF-025 |
| `organization.maintenance` | Kewenangan pemeliharaan per keluarga F1–F9 | `organization.maintenance.uny` | GAP-021 | WF-029 |
| `numbering` | Skema nomor per jenis dokumen | `numbering.uny` | GAP-017 | Numbering |
| `reporting.formats` | Format laporan resmi (termasuk BMU setelah definisinya diketahui) | `reporting.formats.uny` | GAP-007–010/013/024 | WF-027 |
| `retention` | Jadwal retensi/arsip/anonimisasi | `retention.uny` | GAP-023 | P9/P10 (kelak) |

Daftar ini dapat bertambah melalui TASK PLAN CHANGE; setiap keluarga baru memerlukan skema JSON, evaluator dan kasus uji.

## 2. Lifecycle versi

```mermaid
stateDiagram-v2
  [*] --> DRAFT
  DRAFT --> IN_REVIEW: ajukan (penyusun)
  IN_REVIEW --> DRAFT: kembalikan
  IN_REVIEW --> REJECTED: tolak
  IN_REVIEW --> ACTIVE: aktifkan (pengesah ≠ penyusun, bukti GAP lengkap, kasus uji lulus)
  ACTIVE --> SUPERSEDED: versi penerus diaktifkan (jendela ditutup)
  ACTIVE --> RETIRED: dihentikan beralasan
```

| Aturan | Isi |
|---|---|
| Maker-checker | Pengesah tidak boleh sama dengan pembuat atau pengaju dan tidak boleh kosong (constraint DB yang tidak lolos bila kosong + pemeriksaan aplikasi). Keduanya memerlukan kapabilitas berbeda yang bercakupan keluarga: `admin.policy.author` dan `admin.policy.activate` dengan cakupan `POLICY_FAMILY` ([ACCESS_CONTROL §4](../05-security/ACCESS_CONTROL.md#4-katalog-kapabilitas), AUTHZ-25). |
| Bukti | Status ACTIVE mensyaratkan `resolution_evidence` untuk setiap `gap_refs`: rujukan keputusan resmi (nomor/tanggal instrumen, keputusan Owner yang tercatat di repositori) dan berkas bukti. |
| Kasus uji | Semua `policy_test_cases` paket harus lulus terhadap versi kandidat sebelum aktivasi; hasil disimpan di audit. |
| Tanggal berlaku | `effective_from` wajib; jendela versi ACTIVE dan SUPERSEDED tidak pernah tumpang tindih (EXCLUDE constraint). Aktivasi versi penerus menutup jendela versi terbuka pada `effective_from` penerus dan menandainya SUPERSEDED dalam transaksi yang sama, di bawah kunci baris paket; versi SUPERSEDED tetap dipakai untuk tanggal acuan di dalam jendelanya sehingga hasil historis dapat direproduksi, sedangkan RETIRED tidak pernah dipilih. `effective_from` tidak boleh sebelum tanggal aktivasi ([STATE_TRANSITIONS TR-286](../03-workflows/STATE_TRANSITIONS.md#wf-029): tidak pernah backdate menimpa riwayat); tanggal berlaku resmi instrumen tetap dicatat pada rujukan sumber versi. Versi baru tidak mengubah record lama yang sudah merujuk versi lama (INV-017/018). |
| Keadaan WF-029 | Status data mencatat keputusan siklus hidup; keadaan P3 diturunkan dari status dan jendela terhadap hari ini: ACTIVE dengan `effective_from` setelah hari ini = WF029-S4 (disetujui, belum berlaku); jendela memuat hari ini = S5 (berlaku); SUPERSEDED dengan `effective_to` paling lambat hari ini = S6 (digantikan). TR-286/TR-287 ditulis ke `workflow_transitions` oleh job harian `maintenance` saat tanggal efektif tercapai; resolusi tidak bergantung pada job itu karena selalu memilih menurut jendela. Pola yang sama dipakai versi template dokumen (F8) dan versi klasifikasi aset (F6). |
| Imutabilitas | `content` tidak dapat diubah setelah IN_REVIEW; perubahan berarti versi baru. |
| Audit | Setiap perubahan status tercatat di `audit_events` dan `workflow_transitions` (keluarga WF-029 F4). |

### 2.1 Hubungan dengan GAP_REGISTER

Aktivasi di aplikasi **tidak** menutup GAP di repositori dan sebaliknya penutupan GAP tidak otomatis mengaktifkan kebijakan. Pemakaian kebijakan DRAFT berlabel simulasi untuk membangun dan menguji fitur selama GAP terbuka adalah model usulan yang menunggu konfirmasi Owner (D-28). Urutan yang aman:

1. Owner memperoleh jawaban resmi dari pemegang kewenangan (paket pertanyaan di [OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md)).
2. Jawaban dicatat di repositori melalui disiplin GAP (sumber/tanggal/versi, keputusan Owner, review independen) sehingga status GAP menjadi RESOLVED.
3. Pengelola kebijakan menyusun versi yang isinya sesuai jawaban, dengan rujukan keputusan repositori.
4. Pengesah yang berbeda memeriksa dan mengaktifkan dengan bukti.

## 3. Resolusi dan evaluasi

```text
PolicyResolver::resolve(family, context) -> PolicyHandle
  context: { regime_key?, warehouse_code?, org_unit_id?, reference_date, allow_simulation }
  1. pilih paket menurut family + context (mis. regime_key, warehouse_code)
  2. pilih satu-satunya versi ACTIVE/SUPERSEDED yang jendelanya memuat reference_date
  3. bila tidak ada dan allow_simulation dan lingkungan mengizinkan DRAFT:
       pilih versi DRAFT/IN_REVIEW terbaru -> is_simulation = true
  4. bila tidak ada: PolicyUnavailable(family, gap_refs) -> aksi tidak tersedia + alasan
PolicyHandle: { version_id, status, is_simulation, content, citations }
```

Setiap evaluator mengembalikan `PolicyOutcome { value, citations[], policy_version_id, is_simulation }`; Action yang menyimpan hasil wajib menyimpan `policy_version_id` dan `is_simulation`. Bila hasil bergantung pada lebih dari satu keluarga (misalnya rencana langkah: `procurement.documents` + `procurement.methods` + `procurement.authority` + `procurement.evaluation`; penetapan metode: `procurement.methods` + `procurement.regime`; skema dan hasil evaluasi: `procurement.evaluation` + `procurement.authority`), setiap versi penyumbang dicatat di `policy_version_uses` dalam transaksi yang sama ([DATA_MODEL §8](DATA_MODEL.md#8-policy)). Record turunan dibangkitkan ulang menjadi versi baru — bukan ditimpa — bila input bisnisnya berubah atau versi yang berlaku pada tanggal acuannya untuk salah satu keluarga penyumbang berbeda dari yang tercatat; record lama mempertahankan rujukan versinya. Aturan lingkungan:

| Konfigurasi | Default produksi | Default non-produksi |
|---|---|---|
| `PRANATA_ALLOW_DRAFT_POLICIES` | `false` | `true` |
| Aksi resmi dengan kebijakan non-ACTIVE | Tidak tersedia | Tersedia, bertanda simulasi |
| Perhitungan saran/pratinjau dengan kebijakan non-ACTIVE | Tampil berlabel "Simulasi — kebijakan belum disahkan", tanpa record resmi | Sama |

## 4. Contoh isi: `procurement.methods.pr34-2023` (DRAFT)

Contoh di bawah memperlihatkan bentuk data; nilainya disalin dari PR 34/2023 sebagai **DRAFT** dengan rujukan dan tetap memerlukan konfirmasi kustodian (GAP-026/027) sebelum ACTIVE. Rincian sumber: [REGULATORY_SOURCE_REVIEW](../00-governance/evidence/REGULATORY_SOURCE_REVIEW.md).

```json
{
  "regime_key": "PR34_2023",
  "type_labels": { "OTHER_SERVICES": "Jasa Non Konsultansi" },
  "methods": [
    { "code": "PL",
      "bands": [
        { "types": ["GOODS","CONSTRUCTION","OTHER_SERVICES"], "max": "700000000.00", "inclusive_max": true, "cite": "Pasal 25 ayat (1) huruf a" },
        { "types": ["CONSULTING"], "max": "300000000.00", "inclusive_max": true, "cite": "Pasal 25 ayat (1) huruf b" } ],
      "qualitative_any": ["STANDARD_GOODS","LOW_RISK","MARKET_AVAILABLE","DAILY_OPERATIONAL"],
      "qualitative_cite": "Pasal 25 ayat (1) huruf c–f",
      "requires_dpt": false },
    { "code": "QUOTATION",
      "bands": [
        { "types": ["GOODS","CONSTRUCTION","OTHER_SERVICES"], "min_exclusive": "700000000.00", "max": "2000000000.00", "cite": "Pasal 26 ayat (1) huruf a" },
        { "types": ["CONSULTING"], "min_exclusive": "300000000.00", "max": "400000000.00", "cite": "Pasal 26 ayat (1) huruf b" } ],
      "qualitative_any": ["LOW_RISK_HIGH_COST","HIGH_RISK_MEDIUM_COST_FEW_PROVIDERS"],
      "requires_dpt": true, "min_invited": 3, "channel": "PBJ_UNY", "cite_channel": "Lampiran III B" },
    { "code": "TENDER",
      "bands": [
        { "types": ["GOODS","CONSTRUCTION","OTHER_SERVICES"], "min_exclusive": "2000000000.00", "cite": "Pasal 27 ayat (1) huruf a" },
        { "types": ["CONSULTING"], "min_exclusive": "300000000.00", "cite": "Pasal 27 ayat (1) huruf b" } ],
      "qualitative_any": ["COMPLEX_HIGH_TECH","NEW_WORK_TYPE"],
      "requires_dpt": true, "variants": ["OPEN","LIMITED","TWO_STAGE"] },
    { "code": "PENUNJUKAN_LANGSUNG", "criteria_only": true, "criteria_ref": "Pasal 28 (keadaan tertentu a–o; kriteria khusus a–g)", "requires_dpt": true },
    { "code": "E_PURCHASING", "catalog_only": true, "max": "15000000000.00", "channel": "E_MARKET_UNY", "cite": "Pasal 29; Lampiran III E angka 7" }
  ],
  "overlaps": [ { "types": ["CONSULTING"], "range": ["300000000.00","400000000.00"], "methods": ["QUOTATION","TENDER"], "gap": "GAP-027(a)", "requires_human_choice": true } ]
}
```

Kasus uji minimal paket ini: barang Rp700.000.000 → PL memenuhi; barang Rp700.000.001 → Quotation memenuhi, PL tidak; konsultansi Rp350.000.000 → Quotation dan Tender memenuhi dengan penanda tumpang tindih wajib-pilih; barang Rp2.000.000.001 → Tender; E-Purchasing hanya bila barang tercantum di katalog dan ≤ Rp15.000.000.000.

## 5. Contoh isi lain (bentuk, bukan nilai resmi)

| Paket | Bentuk isi |
|---|---|
| `warehouse.operations.alp` | `request_types[{code, label, approval_chain:[{step, assignment_role, scope}]}]`, `handover_evidence:{recipient_without_account: "SIGNED_RECEIPT_UPLOAD"}`, `confirmation:{close_without_confirmation_after_working_days: null}`, `return_inspection:{criteria:[…], inspector_role}`, `transfer:{approver_role}`, `count_adjustment:{approver_role}` |
| `asset.capitalization.uny` | `threshold:{amount, comparison:"GREATER_THAN"}`, `recognition_date_rule:"PAYMENT_DOCUMENT_DATE"`, `donation_rule:…` — nilai provisional Owner (Rp8.000.000) tercatat sebagai masukan SRC-052, bukan nilai ACTIVE |
| `asset.depreciation.uny` | `method:"STRAIGHT_LINE"`, `start_rule:"MONTH_AFTER_RECOGNITION"`, `useful_life_months_by_classification:{…}`, `rounding:{scale:2, mode:"HALF_UP"}`, `cutoff:{…}` |
| `procurement.authority.pr34-2023` | `method_determiner_roles`, `hps_setter_role`, `winner_setter:[{min_exclusive, role}]`, `sppbj:{preparer, checker, issuer, signer}`, `sod:[{a:"PPK", b:"POKJA_MEMBER", scope:"PACKAGE"}, …]` |
| `procurement.evaluation.pr34-2023` | `methods:{QUOTATION:{evaluation_methods:["LOWEST_PRICE","MERIT_POINT"], submission_modes:["ONE_ENVELOPE"], min_invited_dpt:3, cite:"Lampiran III B.3.a.1.c; B.3.b.1"}, TENDER:{evaluation_methods:["LOWEST_PRICE","WEIGHTED_SCORE","MERIT_POINT","TOTAL_COST_OF_OWNERSHIP"], qualification_modes:[…], variants:{LIMITED:{min_qualified:3, on_below:"FAIL"}, TWO_STAGE:{min_qualified:3, on_below:"FAIL"}}}, PENUNJUKAN_LANGSUNG:{evaluation_methods:["REQUIREMENT_AND_NEGOTIATION"], on_no_agreement:"FAIL"}, PL:{evaluation_methods:["SPEC_AND_BEST_PRICE"], practice:{quotations:3, status:"PENDING_VALIDATION", enforce:false, source:"U-011"}}, E_PURCHASING:{evaluation_methods:["CATALOG_COMPARISON"], negotiate_cheapest_first_when_providers_at_least:2, cite:"Lampiran III E.3–4"}}`, `opening:{may_disqualify:false}`, `award_modes:{MULTIPLE_FRAMEWORK:{contract_types:["FRAMEWORK"], cite:"Pasal 31 ayat (7)"}}`, `reserves:null` (GAP), `arithmetic_correction:null` (GAP), `result_review:{QUOTATION:"PPK", TENDER:"PPK", cite:"Pasal 9 ayat (2) huruf c angka 3"}` |
| `vendor.performance.pr34-2023` | `criteria:["QUALITY","QUANTITY","TIMELINESS"]` (Lampiran I E.3), `scale:null` (GAP-003), `assessor:{default:"PPK", cite:"Pasal 9 ayat (2) huruf c angka 4"}`, `feeds_dpt_evaluation:true` |
| `procurement.documents.pr34-2023` (matriks penerapan) | `rules:[{when:{method, variant, procurement_type, value_band}, step_key, documents:[{code, applicability, reason, source_ref}]}]` dengan `applicability` salah satu dari REQUIRED, OPTIONAL, CONDITIONAL, NOT_APPLICABLE, PENDING_VALIDATION. Contoh bentuk: jenis CONSTRUCTION menambah gambar, RKS, BoQ/RAB dan spesifikasi teknis sebagai `PENDING_VALIDATION` sampai kustodian memastikan (GAP-017); dokumen pemilihan minimum Quotation/Tender/Penunjukan Langsung (Lampiran III B.3.a.4, C.4.a.1.g, D.2.a.4) sebagai REQUIRED dengan rujukan; aturan yang lebih spesifik mengalahkan yang umum; kombinasi tanpa aturan tampil `PENDING_VALIDATION`, tidak pernah diam-diam wajib atau tidak berlaku |

## 6. Antarmuka pengelolaan

Konteks RT-032 (Administrasi → Kebijakan): daftar paket per keluarga dengan versi ACTIVE dan DRAFT, editor isi berbasis formulir yang dibangkitkan dari skema JSON (bukan editor JSON bebas untuk pengguna non-teknis), panel rujukan sumber dan GAP, unggah bukti, hasil kasus uji, perbandingan versi (diff), riwayat dan tombol aktivasi yang hanya muncul bagi pengesah yang berhak. Detail halaman di [INFORMATION_ARCHITECTURE](../07-ux-design/INFORMATION_ARCHITECTURE.md).

## 7. Pengujian

- Validasi skema untuk setiap keluarga (isi tidak valid tidak dapat disimpan).
- Evaluator diuji dengan kasus uji tabel (golden cases) termasuk batas inklusif/eksklusif dan tumpang tindih.
- Uji maker-checker (pembuat tidak dapat mengaktifkan), EXCLUDE tumpang tindih, dan penolakan aksi resmi di produksi tanpa ACTIVE.
- Uji bahwa record lama tetap merujuk versi lamanya setelah versi baru aktif.
- Uji label simulasi pada layar, ekspor dan dokumen (AC-32).
