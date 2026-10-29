# Kontrol akses — kelompok peran, kapabilitas, cakupan, penugasan dan pemisahan tugas

Status: P5 candidate — VERIFYING (AUTH-012), belum disetujui | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P5

Dokumen ini memiliki **model otorisasi PRANATA**: kelompok peran global yang ringkas, katalog kapabilitas, cakupan grant, penugasan proses per objek, aturan pemisahan tugas, isolasi data per ruang kerja/gudang/penyedia, batas Super Admin, kesesuaian frontend–backend dan skenario regresi otorisasi. Dasar: [OD-09](../00-governance/DECISION_LOG.md#od-09), [OD-10](../00-governance/DECISION_LOG.md#od-10), [OD-20](../00-governance/DECISION_LOG.md#od-20), [OD-28](../00-governance/DECISION_LOG.md#od-28), BR-034, INV-001/004/021. Pemetaan jabatan resmi ke fungsi per metode tetap GAP-003/014 dan dikelola sebagai kebijakan `procurement.authority` / `warehouse.operations` / `asset.lifecycle`.

## 1. Lapisan keputusan izin

```text
can(actor, capability, subject?) :=
  1. actor.status = ACTIVE dan sesi sah (TOTP terpenuhi bila diwajibkan)
  2. kelompok peran mengizinkan kapabilitas (tabel 2)
  3. ada grant ACTIVE untuk kapabilitas itu yang cakupannya mencakup subjek
       ATAU kapabilitas dipenuhi oleh penugasan proses ACTIVE pada subjek (tabel 5)
  4. aturan pemisahan tugas tidak dilanggar untuk subjek itu (bagian 6)
  5. guard keadaan workflow (TR P3) dan gerbang kebijakan terpenuhi
  6. untuk aksi berbasis kebijakan resmi: versi kebijakan ACTIVE (di produksi)
hasil: ALLOW | DENY(reason_key) — DENY tidak pernah membocorkan isi subjek
```

Kueri daftar memakai turunan yang sama dalam bentuk SQL (`visibleTo($actor)`) sehingga daftar, detail, pencarian, ekspor dan unduhan konsisten.

## 2. Kelompok peran global

| Kelompok | Makna | Batas keras |
|---|---|---|
| SUPER_ADMIN | Pengelola sistem (OD-10): pengguna, akses, organisasi, referensi, kebijakan, template, pemantauan | Tidak dapat menerima penugasan proses keputusan bisnis, tidak dapat menetapkan metode/pemenang/persetujuan/tanda tangan/penerimaan (INV-021); membaca informasi rahasia hanya lewat IX-024; orang yang juga pegawai memakai akun INTERNAL terpisah |
| INTERNAL | Pegawai UNY | Semua kemampuan berasal dari grant/penugasan; dasar bawaan hanya profil sendiri, inbox sendiri, permintaan sendiri |
| VENDOR | Pengguna penyedia yang tertaut ke satu perusahaan | Hanya Portal Penyedia; hanya data perusahaan dan partisipasinya; tidak pernah melihat penyedia lain |

## 3. Ruang kerja

| Ruang kerja | Pengguna | Muncul bila |
|---|---|---|
| Beranda | Semua | Selalu (inbox, dashboard per peran, permintaan saya) |
| Pengadaan | INTERNAL | Ada grant `procurement.*` atau penugasan proses pengadaan |
| Gudang | INTERNAL | Ada grant `inventory.*` pada ≥1 gudang; permintaan barang sendiri tersedia dari Beranda tanpa grant khusus bila kebijakan gudang mengizinkan unit pemohon |
| Aset | INTERNAL | Ada grant `asset.*` |
| Portal Penyedia | VENDOR | Tautan perusahaan aktif |
| Administrasi | SUPER_ADMIN dan admin terdelegasi | Ada grant `admin.*` |

Memilih ruang kerja tidak memberi izin (OD-20); pengalihan ruang kerja hanya menampilkan konteks yang diizinkan. Ruang kerja "Gudang" adalah konteks pengalaman baru untuk kapabilitas OD-25/31 — perubahan dari empat konteks OD-02 ini memerlukan konfirmasi Owner ([OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md)); bila ditolak, fungsi Gudang ditempatkan di ruang kerja Aset tanpa mengubah model izin.

## 4. Katalog kapabilitas

Cakupan yang diizinkan: G = GLOBAL, U = ORG_UNIT, S = ORG_UNIT_SUBTREE, W = WAREHOUSE, K = WORKSPACE. "Penugasan" berarti kapabilitas hanya berlaku bila aktor juga memiliki penugasan proses yang sesuai pada objek.

### 4.1 Administrasi

| Kapabilitas | Cakupan | Catatan |
|---|---|---|
| `admin.users.manage` | G | Buat/nonaktifkan akun, reset dibantu admin |
| `admin.access.grant` | G | Membuat grant; grant istimewa menunggu `admin.access.approve` orang lain |
| `admin.access.approve` | G | Menyetujui grant istimewa; tidak untuk grant yang dibuatnya sendiri |
| `admin.org.manage` | G | WF-029 F1/F2 (keputusan berlaku tetap BLOCKED_BY_GAP-021) |
| `admin.reference.manage` | G | WF-029 F3 |
| `admin.policy.author` | G | Menyusun/mengajukan versi kebijakan |
| `admin.policy.activate` | G | Mengaktifkan versi; bukan penyusunnya |
| `admin.templates.manage` | G | Template dokumen (aktivasi maker-checker) |
| `admin.audit.read` | G | Membaca audit non-rahasia |
| `admin.confidential.read` | G | IX-024: baca rahasia beralasan, teraudit |
| `admin.system.monitor` | G | Pulse, antrean, kesehatan |

### 4.2 Pengadaan

| Kapabilitas | Cakupan | Catatan |
|---|---|---|
| `procurement.need.create` / `.submit` | U | Usulan unit (WF-003) |
| `procurement.need.review` | S, G | Penerimaan/pemeriksaan kelengkapan pusat |
| `procurement.rup.record` | S, G | WF-004 |
| `procurement.package.view` | S, G | Baca paket dalam cakupan |
| `procurement.package.manage` | S, G | Buat/ubah paket, item, referensi anggaran |
| `procurement.hps.prepare` | S, G | Menyusun HPS |
| `procurement.hps.set` | Penugasan | Fungsi penetap HPS menurut kebijakan |
| `procurement.method.suggest` | S, G | Meminta saran (TR-312) |
| `procurement.method.determine` | Penugasan `METHOD_DETERMINER` | TR-313/315 |
| `procurement.activity.manage` | S, G / penugasan | Aktivitas, undangan |
| `procurement.submission.record` | Penugasan pemilihan | Mencatat kiriman eksternal |
| `procurement.submission.view_confidential` | Penugasan pemilihan | Penawaran rahasia |
| `procurement.evaluation.record` | Penugasan `POKJA_MEMBER`/`PEJABAT_PENGADAAN` | |
| `procurement.result.propose` / `.determine` | Penugasan sesuai `procurement.authority` | Penetapan pemenang menurut nilai |
| `procurement.sppbj.prepare` / `.check` / `.issue` | Penugasan masing-masing | Empat tanggung jawab (GAP-004) |
| `procurement.contract.prepare` / `.approve` / `.record_signature` | Penugasan | WF-012 |
| `procurement.amendment.propose` / `.review` / `.decide` | Penugasan | WF-033 |
| `procurement.execution.record` | Penugasan/S | Progress, pemeriksaan, BAST |
| `procurement.spj.manage` / `procurement.payment.track` | S, G | WF-015 |
| `procurement.external_reference.record` | S, G | Kanal resmi |
| `procurement.report` | S, G | Laporan pengadaan |

### 4.3 Penyedia

| Kapabilitas | Cakupan | Catatan |
|---|---|---|
| `vendor.company.manage` | G | Data perusahaan oleh petugas |
| `vendor.documents.verify` | G | Verifikasi dokumen |
| `vendor.dpt.decide` | G / penugasan | Keputusan DPT menurut kebijakan |
| `vendor.performance.review` | G | Evaluasi kinerja |
| `portal.profile.manage` | Perusahaan sendiri | VENDOR |
| `portal.documents.upload` | Perusahaan sendiri | VENDOR |
| `portal.participation.view` | Perusahaan sendiri | VENDOR |
| `portal.submission.create` | Perusahaan sendiri | VENDOR; hanya bila kebijakan kanal mengizinkan (GAP-029, default OFF) |

### 4.4 Gudang

| Kapabilitas | Cakupan | Catatan |
|---|---|---|
| `inventory.request.create` | U (unit pemohon) | Membuat permintaan untuk unit sendiri |
| `inventory.request.decide` | Penugasan/rantai kebijakan per gudang | TR-293 |
| `inventory.handover.record` | W | Petugas gudang |
| `inventory.return.receive` / `.inspect` | W / penugasan | TR-303/304 |
| `inventory.receipt.record` | W | Penerimaan barang masuk |
| `inventory.count.manage` | W | Opname |
| `inventory.adjustment.propose` / `.approve` | W / penugasan | Penyesuaian |
| `inventory.transfer.dispatch` / `.receive` | W | Transfer antar gudang |
| `inventory.item.manage` | W, G | Katalog barang |
| `inventory.view` | W | Stok/ledger gudang |
| `inventory.report` | W | Laporan gudang |

### 4.5 Aset, rekonsiliasi, intake, laporan

| Kapabilitas | Cakupan | Catatan |
|---|---|---|
| `asset.view` | S, G | Register dalam subtree |
| `asset.register` / `asset.recognize` | S, G / penugasan | WF-016/017 |
| `asset.mutation.propose` / `.decide` | S / penugasan | WF-018 |
| `asset.verification.manage` | S | WF-019 |
| `asset.maintenance.record` | S | WF-019 |
| `asset.change.propose` / `.decide` | S / penugasan | WF-020 |
| `asset.disposal.propose` / `.decide` | S / penugasan | WF-021 |
| `asset.depreciation.run` | G | WF-022 |
| `asset.kdp.manage` | S, G | WF-024 |
| `asset.report` | S, G | |
| `recon.case.manage` / `recon.case.accept` | S, W, G / penugasan | WF-025 |
| `intake.batch.manage` / `intake.batch.decide` | G | WF-026 |
| `report.request` | Turunan cakupan modul | Ekspor tercatat |

## 5. Penugasan proses

`process_assignments` mengikat pengguna ke objek (paket, kontrak, permintaan, kasus) dengan peran penugasan. Peran adalah label teknis; fungsi resmi yang boleh memegangnya ditentukan kebijakan.

| Peran penugasan | Objek | Fungsi resmi kandidat (PR 34/2023, EVIDENCE_SUPPORTED) |
|---|---|---|
| `PPK` | Paket/kontrak | PPK |
| `PLANNER` | Paket | Perencana Pengadaan |
| `POKJA_CHAIR`, `POKJA_MEMBER` | Paket | Pokja Pemilihan |
| `PEJABAT_PENGADAAN` | Paket | Pejabat Pengadaan |
| `PROCUREMENT_AGENT` | Paket | Agen Pengadaan |
| `METHOD_DETERMINER` | Paket | Ditentukan `procurement.authority` (GAP-003) |
| `HPS_SETTER` | Paket | Ditentukan kebijakan (kandidat PPK) |
| `WINNER_DETERMINER` | Paket | Pokja/Pejabat/PA menurut nilai (kebijakan) |
| `SPPBJ_PREPARER`, `SPPBJ_CHECKER`, `SPPBJ_ISSUER`, `SPPBJ_SIGNER` | Paket | GAP-004 |
| `CONTRACT_SIGNER`, `AMENDMENT_DECIDER` | Kontrak | Menurut kebijakan |
| `GOODS_RECEIVER`, `QUALITY_CONTROLLER`, `PJPHP`, `PPHP`, `TECHNICAL_TEAM` | Kontrak | Penerima Barang, Pengendali Kualitas, PjPHP/PPHP, Tim Teknis |
| `REQUEST_APPROVER` | Permintaan gudang | Rantai `warehouse.operations` (mis. pimpinan unit pemohon, Kabag ALP — teramati SRC-058, belum disahkan) |
| `RETURN_INSPECTOR` | Pengembalian | `warehouse.operations` |
| `ASSET_DECIDER` | Mutasi/perubahan/penghapusan | `asset.lifecycle` |
| `RECON_OWNER`, `RECON_ACCEPTOR` | Kasus | `reconciliation` |

Penugasan dibuat oleh pengguna berwenang (kapabilitas `procurement.package.manage`, admin gudang, dsb.) dengan rujukan SK bila ada; sistem dapat **menyarankan** pengguna dari `org_appointments`, tetapi saran bukan penugasan. Rantai keputusan gudang dapat dipetakan dari penunjukan jabatan secara otomatis hanya bila kebijakan ACTIVE menyatakannya. Pelaksana tugas (Plt.)/delegasi dicatat sebagai penunjukan/penugasan berjangka dengan rujukan; tidak ada delegasi implisit (GAP-001/014).

## 6. Pemisahan tugas

| ID | Aturan | Sumber | Penegakan |
|---|---|---|---|
| SOD-01 | PPK ≠ Pejabat Pengadaan/anggota Pokja pada paket yang sama | PR 34/2023 Pasal 9 ayat (1) huruf g (kebijakan `procurement.authority`) | Saat penugasan dan saat aksi |
| SOD-02 | PPK ≠ PjPHP/PPHP pada paket yang sama | Sama | Sama |
| SOD-03 | PPK ≠ PPSPM/Bendahara | Sama; jabatan keuangan di luar PRANATA dicatat sebagai penunjukan | Peringatan + blok penugasan bila penunjukan tercatat |
| SOD-04 | Pembuat versi kebijakan ≠ pengesah | CAP-23 | Constraint DB + aplikasi |
| SOD-05 | Pembuat grant istimewa ≠ penyetuju | P5 | Constraint DB + aplikasi |
| SOD-06 | Pemohon barang ≠ pemberi persetujuan atas permintaannya sendiri | Kontrol bawaan P5; dapat diubah hanya oleh kebijakan gudang ACTIVE | Aplikasi |
| SOD-07 | Petugas yang menyerahkan barang ≠ penerima yang mengonfirmasi | P5 | Aplikasi |
| SOD-08 | SUPER_ADMIN tidak memegang penugasan keputusan bisnis | INV-021 | Aplikasi |
| SOD-09 | Penyusun templat ≠ pengesah templat | P4 | Constraint DB + aplikasi |

Aturan SOD-01–03 berasal dari kebijakan rezim sehingga dapat berbeda untuk rezim lain; SOD-04–09 adalah kontrol sistem. Pelanggaran tidak dapat dilewati dengan peringatan.

## 7. Visibilitas data per modul

| Modul | Aturan baca |
|---|---|
| Pengadaan | Paket terlihat bila grant `procurement.package.view` mencakup unit pemilik paket, atau pengguna memiliki penugasan pada paket. Unit pemohon melihat status usulannya dan ringkasan paket terkait tanpa dokumen rahasia. |
| Penyedia (internal) | Petugas dengan `vendor.*` melihat data perusahaan; dokumen RESTRICTED disamarkan kecuali kebutuhan verifikasi. |
| Portal Penyedia | Hanya perusahaan tertaut, partisipasi, undangan, dokumen yang dibagikan dan status/hasil yang diumumkan; tidak pernah penawaran/evaluasi penyedia lain. |
| Gudang | Stok, ledger dan antrean hanya untuk gudang dalam grant `W`; pemohon melihat permintaannya; penerima melihat serah terima untuknya. Petugas Gudang ALP tidak melihat Gudang PSRT tanpa grant terpisah (OD-28). |
| Aset | Register dalam subtree unit grant; kustodian melihat aset yang dipegangnya; pengguna Pengadaan tanpa grant aset tidak melihat data aset dan sebaliknya (OD-28). |
| Rekonsiliasi | Menurut cakupan kasus; sumber terlarang ditampilkan sebagai ringkasan yang sah (RT kontrak crossing). |
| Laporan/ekspor | Isi ekspor = filter yang terlihat dan scope pengguna; tidak ada ekspor yang melampaui scope. |
| Audit | `admin.audit.read`; peristiwa rahasia hanya ringkasan kecuali pembacaan teraudit. |

## 8. Kesesuaian frontend–backend

- Server mengirim peta izin per halaman/objek (`can.approve`, `can.handover`, …) dan daftar ruang kerja/menu yang diizinkan; React hanya menampilkan aksi bila izin ada.
- Aksi yang terlihat tetapi terblokir oleh GAP/kebijakan ditampilkan dengan alasan dan langkah aman (IX kontrak bersama), bukan disembunyikan, kecuali keberadaannya membocorkan informasi.
- Setiap rute aksi tetap diperiksa backend; uji otomatis memastikan akses langsung tanpa izin menghasilkan 403/404.
- Objek di luar cakupan dijawab 404 bila keberadaan objek itu sendiri sensitif (penawaran, data penyedia lain); 403 bila keberadaannya sudah diketahui pengguna (misalnya tombol aksi pada objek yang ia lihat).

## 9. Profil akses awal (dapat diubah Super Admin)

| Profil | Kapabilitas inti | Cakupan khas |
|---|---|---|
| Operator Usulan Unit | `procurement.need.*`, `inventory.request.create` | U |
| Staf Pengadaan Pusat | `procurement.package.*`, `procurement.hps.prepare`, `procurement.method.suggest`, `procurement.activity.manage`, `procurement.rup.record`, `procurement.external_reference.record`, `procurement.spj.manage`, `procurement.payment.track`, `procurement.report`, `vendor.company.manage` | G |
| Verifikator DPT | `vendor.documents.verify`, `vendor.dpt.decide` (dengan penugasan bila kebijakan mensyaratkan) | G |
| Petugas Gudang ALP / PSRT | `inventory.handover.record`, `inventory.return.receive`, `inventory.receipt.record`, `inventory.count.manage`, `inventory.view` | W (gudang masing-masing) |
| Admin Gudang | Petugas Gudang + `inventory.item.manage`, `inventory.adjustment.propose`, `inventory.transfer.*`, `inventory.report` | W |
| Operator Aset Unit | `asset.view`, `asset.register`, `asset.mutation.propose`, `asset.verification.manage`, `asset.maintenance.record` | S |
| Staf Aset Pusat | Operator Aset + `asset.change.propose`, `asset.disposal.propose`, `asset.kdp.manage`, `asset.report`, `recon.case.manage`, `intake.batch.manage` | G |
| Pimpinan (baca) | `procurement.package.view`, `asset.view`, `inventory.view`, `*.report` | S/G/W |
| Admin Akses | `admin.users.manage`, `admin.access.grant` | G (istimewa) |

Profil yang memuat `admin.*` atau cakupan GLOBAL atas kapabilitas keputusan ditandai `is_privileged` dan memerlukan persetujuan orang kedua.

## 10. Skenario regresi otorisasi

| ID | Skenario |
|---|---|
| AUTHZ-01 | Pengguna tanpa grant Pengadaan tidak melihat ruang kerja/menu Pengadaan dan akses langsung ke rute paket → 403/404 |
| AUTHZ-02 | Grant `ORG_UNIT_SUBTREE` melihat paket subunit tetapi tidak unit saudara |
| AUTHZ-03 | Pengguna Pengadaan tanpa grant Aset tidak dapat membuka/mencari/mengekspor aset, dan sebaliknya |
| AUTHZ-10 | Petugas Gudang ALP tidak melihat stok/permintaan/ledger PSRT, termasuk lewat pencarian global dan ekspor |
| AUTHZ-11 | Pemohon tidak dapat menyetujui permintaannya sendiri (SOD-06) |
| AUTHZ-12 | Serah terima melebihi stok ditolak tanpa mengubah data (INV-020) |
| AUTHZ-20 | Admin tidak dapat memberi dirinya grant; grant istimewa menunggu penyetuju kedua |
| AUTHZ-21 | Pembuat versi kebijakan tidak dapat mengaktifkannya |
| AUTHZ-22 | Grant kedaluwarsa/dicabut langsung tidak berlaku pada permintaan berikutnya |
| AUTHZ-23 | SUPER_ADMIN tidak dapat ditugaskan sebagai `METHOD_DETERMINER`/`WINNER_DETERMINER`/penanda tangan |
| AUTHZ-30 | Penyedia A tidak dapat melihat penawaran, dokumen atau identitas partisipan penyedia B melalui rute, API, unduhan atau ID tebakan |
| AUTHZ-31 | Super Admin membuka penawaran hanya setelah alasan; audit tercatat; tanpa ekspor |
| AUTHZ-32 | Bila penulisan audit gagal, isi rahasia tidak ditampilkan |
| AUTHZ-33 | Penyedia tidak dapat mengirim penawaran di PRANATA bila kebijakan kanal menonaktifkannya |
| AUTHZ-34 | Penugasan PPK dan Pokja pada paket sama ditolak (SOD-01) |
| AUTHZ-40 | Penetapan metode hanya oleh penugasan `METHOD_DETERMINER` pada paket itu dan hanya dengan kebijakan ACTIVE di produksi |
| AUTHZ-41 | Penetapan pemenang oleh fungsi sesuai ambang nilai kebijakan |
| AUTHZ-42 | Empat tanggung jawab SPPBJ tidak dapat dijalankan oleh satu orang bila kebijakan memisahkannya |
| AUTHZ-43 | Ekspor laporan tidak memuat baris di luar scope pengguna |
| AUTHZ-44 | Unduhan berkas melalui `public_id` berkas milik subjek lain ditolak |
| AUTHZ-45 | Perubahan cakupan grant tercermin konsisten di daftar, detail, pencarian, ekspor dan dashboard |

Matriks pengujian lengkap dibangkitkan dari katalog kapabilitas × profil × cakupan pada P8.
