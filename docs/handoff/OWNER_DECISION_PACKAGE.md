# Paket keputusan Owner — PRANATA UNY

Status: P0–P11 planning candidate — VERIFYING (AUTH-012) | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Disusun menurut GOV-11

Paket ini mengumpulkan **semua keputusan Owner** yang dibutuhkan agar perencanaan P0–P11 dapat disetujui, dibekukan dan dieksekusi. Setiap butir memuat rekomendasi, alternatif, konsekuensi dan dokumen yang terdampak. Diam tidak pernah dianggap persetujuan. Keputusan teknis biasa sudah diambil Planning Agent di dalam batas arahan Owner dan tidak diulang di sini.

Draf keputusan di Project claude.ai yang dibuat sesi-sesi sebelumnya (antara lain `FORMULIR_KEPUTUSAN_OWNER_FT-01`, `JALUR_TERCEPAT_KE_PRODUKSI_FT-01`, `DRAF_D-1_R2.4`, berkas REC-REM-01) **tidak mengikat** dan digantikan oleh paket ini (GOV-10).

## Cara menjawab

Balas dengan nomor butir dan pilihan, misalnya `D-01 setuju; D-05 a; D-08 A; D-12 sesuai rekomendasi; D-19 VPS klien`. Butir yang belum dijawab tetap terbuka dan hanya menahan Task yang bergantung padanya.

## A. Persetujuan dan otorisasi

| ID | Keputusan | Rekomendasi | Alternatif dan konsekuensi | Terdampak |
|---|---|---|---|---|
| D-01 | Persetujuan batch atas (1) amandemen remediasi P0–P3 AUTH-010, (2) amandemen kompatibilitas P0–P3 AUTH-012, (3) kandidat P4–P11, masing-masing pada identitas yang direview (SHA cabang; manifest remediasi) | Setujui bersama **setelah** Second Review berstatus READY_FOR_OWNER_APPROVAL dan koreksi delta selesai | Persetujuan per fase: lebih lambat, isi sama. Menolak satu bagian menahan bagian yang bergantung padanya | [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md), [PHASE_STATUS](../PHASE_STATUS.md) |
| D-02 | Konsolidasi gerbang cadangan: APPR-005 (persetujuan remediasi) dan AUTH-011 (publikasi remediasi) | Catat APPR-005 untuk remediasi, APPR-006 untuk kandidat AUTH-012, dan AUTH-011 sebagai otorisasi merge cabang `docs/p0-p11-planning` ke `main` dengan metode D-08 — dalam satu keputusan | Memisahkan publikasi remediasi lebih dahulu: menambah satu putaran tanpa manfaat karena kandidat sudah satu cabang | APPROVAL_RECORDS, [GIT_WORKFLOW](../00-governance/GIT_WORKFLOW.md) |
| D-03 | Planning Freeze dan baseline 78 Task | Bekukan segera setelah merge D-02 diverifikasi; TASK-001 menjadi READY | Menunda freeze: eksekusi belum dapat dimulai | [TASK_PLAN](../11-tasks/TASK_PLAN.md) |
| D-04 | Otorisasi eksekusi berdiri dan ruang lingkupnya | Bertahap: TASK-001 mode terawasi (Owner hadir) → otonom M0–M2 (TASK-002–TASK-023) → diperluas ke M3 setelah Owner melihat hasil M0–M2; merge mengikuti D-08 | Otonom penuh sejak awal: lebih cepat, risiko kemampuan yang belum terbukti. Terawasi terus: aman tetapi menuntut kehadiran Owner | [EXECUTOR_OPERATING_MODEL §1](../11-tasks/EXECUTOR_OPERATING_MODEL.md#1-prasyarat-aktivasi) |

## B. Produk dan struktur

| ID | Keputusan | Rekomendasi | Alternatif dan konsekuensi | Terdampak |
|---|---|---|---|---|
| D-05 | Ruang kerja **Gudang** terpisah. OD-02 menyebut ruang kerja Aset, Pengadaan, Portal Penyedia dan Super Admin; kandidat P7 menambah Gudang (serta Beranda sebagai pusat tugas) | (a) Gudang sebagai ruang kerja sendiri: staf gudang ALP/PSRT punya pekerjaan dan akses berbeda (OD-25/26/31, OD-28) | (b) Gudang sebagai bagian ruang kerja Aset: perlu perubahan IA dan navigasi; akses tetap dipisah per gudang. Pilihan (a) dicatat sebagai OD baru yang memperhalus OD-02 | [INFORMATION_ARCHITECTURE](../07-ux-design/INFORMATION_ARCHITECTURE.md) |
| D-06 | Model rilis produksi | (a) Satu go-live setelah R4, sesuai V1_SCOPE (V1 diterima bila semua CAP terbukti) | (b) Bertahap: R1 Pengadaan ke produksi lebih dahulu setelah UAT-1 — manfaat lebih cepat, tetapi mengubah batas rilis P1 dan menambah operasi produksi lebih awal; V1 tetap baru diterima setelah semua CAP | [RELEASE_PLAN §1](../10-release/RELEASE_PLAN.md#1-increment) |
| D-07 | Penggunaan sampel data riil yang dianonimkan untuk UAT-3 Aset di staging | Izinkan hanya sampel yang dianonimkan menurut EVIDENCE_POLICY dan disetujui Owner per sampel | Hanya data sintetis: lebih aman, tetapi UAT-3 kurang mewakili data historis | [UAT_PLAN](../10-release/UAT_PLAN.md), [PRODUCTION_DATA_SAFETY](../00-governance/PRODUCTION_DATA_SAFETY.md) |

## C. Git dan eksekusi

| ID | Keputusan | Rekomendasi | Alternatif dan konsekuensi | Terdampak |
|---|---|---|---|---|
| D-08 | Metode merge ke `main` (berlaku juga untuk cabang perencanaan) | **A — fast-forward terverifikasi**: `main` dimajukan tepat ke HEAD PR yang sudah lulus CI dan review; author dan committer tetap Owner saja (GOV-08) | **B — merge GitHub**: proteksi "PR wajib" ditegakkan platform, tetapi commit merge ber-committer GitHub; perlu penerimaan eksplisit bahwa ini tidak melanggar GOV-08 | [EXECUTOR_OPERATING_MODEL §8.2](../11-tasks/EXECUTOR_OPERATING_MODEL.md#82-metode-merge-keputusan-owner) |
| D-09 | Alur cabang berbasis trunk (PR Task langsung ke `main`; staging dari SHA `main`; produksi hanya dari tag rilis) menggantikan default AICWDF `develop` → `main` | Setujui: sesuai siklus GOV-12 (verifikasi `main` remote), lebih sederhana, gerbang staging/UAT tetap melalui tag | Pakai `develop`: dua cabang panjang, rekonsiliasi DONE tidak di `main` sampai rilis | [RELEASE_PLAN §4](../10-release/RELEASE_PLAN.md#4-versi-dan-cabang), [QUALITY_GATES §4](../08-testing/QUALITY_GATES.md#4-pipeline-ci-github-actions) |
| D-10 | Ketersediaan review PR Codex untuk repositori ini pada akun Owner | Pakai bila sudah tersedia tanpa biaya tambahan; bila tidak, review pengganti subagen segar dipakai permanen | Mengaktifkan layanan berbayar baru: memerlukan persetujuan biaya terpisah | [EXECUTOR_OPERATING_MODEL §6.3](../11-tasks/EXECUTOR_OPERATING_MODEL.md#63-review-pr-codex) |
| D-11 | Cara runner dimulai dan jendela eksekusi | Watcher via Penjadwal Tugas Windows; Owner menetapkan jam mesin menyala tanpa tidur | Mulai manual satu perintah: lebih sederhana, eksekusi berhenti saat Owner tidak memulai | [EXECUTOR_OPERATING_MODEL §9](../11-tasks/EXECUTOR_OPERATING_MODEL.md#9-runner-watcher-checkpoint-dan-pemulihan) |

## D. Keamanan

| ID | Keputusan | Rekomendasi | Catatan |
|---|---|---|---|
| D-12 (SEC-D1) | TOTP wajib untuk Super Admin dan profil akses istimewa | Ya | Opsional untuk pengguna lain |
| D-13 (SEC-D2) | Batas idle sesi | 60 menit; 30 menit Super Admin | |
| D-14 (SEC-D3) | Profil password | AICWDF-COMPAT-8 | Profil lebih kuat bila ada kewajiban institusi |
| D-15 (SEC-D4) | Antivirus ClamAV untuk unggahan | Aktif bila RAM server ≥ 16 GB; jika tidak, status `NOT_SCANNED` ditampilkan | Memakai RAM ±1–2 GB |
| D-16 (SEC-D5) | Pemeriksaan password bocor via layanan eksternal | OFF | Menghindari ketergantungan eksternal |
| D-17 (SEC-D6) | SMTP untuk reset/notifikasi | Opsional; sistem berfungsi tanpa SMTP | Bila ada SMTP institusi, dipakai |
| D-18 (SEC-D7) | Penyerahan kode aktivasi akun penyedia | Diserahkan langsung oleh petugas pengadaan berwenang | Terkait GAP-015 |

Rincian: [SECURITY_MODEL §11](../05-security/SECURITY_MODEL.md#11-keputusan-owner-terkait-keamanan).

## E. Infrastruktur, operasi dan UAT

Dibutuhkan sebelum TASK-040 (staging), TASK-076 (produksi) dan TASK-077 (cadangan offsite); tidak menahan Task lain.

| ID | Keputusan | Rekomendasi | Terdampak |
|---|---|---|---|
| D-19 | Penyedia server produksi dan staging | VPS yang dibiayai klien atau server UNY; spesifikasi usulan di INFRASTRUCTURE §4, divalidasi uji beban staging | [INFRASTRUCTURE](../09-operations/INFRASTRUCTURE.md) |
| D-20 | Domain/subdomain dan TLS | Subdomain UNY bila tersedia; TLS otomatis | INFRASTRUCTURE, SECURITY_MODEL §7 |
| D-21 | Lokasi cadangan offsite | Server UNY kedua (tanpa biaya tambahan); penyimpanan objek berbayar hanya dengan persetujuan biaya | INFRASTRUCTURE, [COST_POLICY](../00-governance/COST_POLICY.md) |
| D-22 | Operator produksi (jabatan) dan penerima peringatan | Ditunjuk Owner; peringatan ke email/kanal yang dipilih | INFRASTRUCTURE, [OPERATIONS_RUNBOOK](../09-operations/OPERATIONS_RUNBOOK.md) |
| D-23 | Pemeriksa uptime eksternal | Gratis/self-hosted pada host terpisah milik klien | INFRASTRUCTURE |
| D-24 | Penandatangan UAT per tahap (jabatan, bukan nama) | UAT-1: pimpinan Pengadaan yang ditunjuk dengan pemeriksaan PPK, bisnis dan Keuangan (OD-32); UAT-2: kepala Gudang ALP dan PSRT; UAT-3: pimpinan Aset bersama Keuangan; UAT-4: Owner | [UAT_PLAN](../10-release/UAT_PLAN.md) |

## F. Penyelesaian GAP

Tidak ada GAP yang diselesaikan oleh perencanaan. Fitur dibangun dengan kebijakan DRAFT berlabel simulasi; aksi resmi di produksi menunggu jawaban resmi ([TRACEABILITY_MATRIX — Perlakuan GAP](../11-tasks/TRACEABILITY_MATRIX.md#perlakuan-gap)). Urutan penyelesaian setiap aturan: Owner memperoleh jawaban resmi → GAP dicatat RESOLVED dengan bukti → steward menyusun versi kebijakan → pemeriksa yang berbeda mengaktifkannya ([POLICY_REGISTRY](../04-architecture/POLICY_REGISTRY.md)).

**D-25 — Peran penjawab per kelompok** (jabatan, bukan nama pribadi):

| Kelompok | GAP | Usulan peran penjawab |
|---|---|---|
| G-1 Pengadaan dan regulasi | GAP-001, 002, 003, 004, 013, 017, 019, 026, 027, 029 | Kepala Bagian ALP bersama PPK/Pejabat Pengadaan yang ditunjuk; unit hukum untuk tafsir PR 34/2023 |
| G-2 Keuangan dan akuntansi | GAP-005, 006, 007, 008, 009, 010, 018, 028 | Bagian Keuangan/akuntansi bersama pengelola aset |
| G-3 Aset dan pelaporan | GAP-022, 024 | Pengelola BMU/aset dan pelaporan |
| G-4 Gudang | GAP-025 | Kepala Gudang ALP dan PSRT |
| G-5 Organisasi, akses dan data pribadi | GAP-014, 015, 021, 023 | Bagian organisasi/kepegawaian, TI dan arsip/hukum |
| G-6 Migrasi dan kinerja | GAP-011, 012, 016 | Owner bersama pemilik data sumber dan TI |

GAP-020 tidak relevan untuk V1 (OD-24).

**D-26 — Pertanyaan tertutup prioritas** (memengaruhi UAT-1 Pengadaan dan inti V1). Jawaban dicatat dengan sumber resmi; jawaban Owner yang bersifat sementara tetap dicatat sebagai masukan, bukan resolusi.

| GAP | Pertanyaan | Pilihan jawaban | Bukti yang diminta |
|---|---|---|---|
| GAP-026 | Untuk setiap sumber dana UNY (PNBP/pendapatan PTN-BH sendiri, alokasi APBN, hibah, kerja sama), rezim mana yang berlaku? Apakah PR 34/2023 masih berlaku tanpa perubahan? | PR 34/2023 / Perpres 16/2018 jo. 12/2021 jo. 46/2025 / aturan pemberi dana; berlaku ya/tidak | Salinan instrumen terkini yang ditandatangani |
| GAP-027 (a) | Konsultansi > Rp300 jt s.d. Rp400 jt: Quotation (Pasal 26(1)b) atau Tender (Pasal 27(1)b)? | Quotation / Tender / ditetapkan pejabat per paket dengan alasan | Catatan tafsir bertanggal |
| GAP-027 (b) | Bukti Pengadaan Langsung > Rp20 jt s.d. Rp50 jt: bentuk menurut Lampiran III A.2.d–e atau Pasal 30(1)a? | Lampiran / Pasal | Catatan tafsir |
| GAP-027 (c) | Dasar jaminan pelaksanaan: HPS (Pasal 21(3)c) atau pagu (Pasal 35 ayat 6)? | HPS / pagu | Catatan tafsir |
| GAP-027 (d) | Pelaksana Penunjukan Langsung: Pejabat Pengadaan atau Pokja Pemilihan? | Pejabat Pengadaan / Pokja / menurut nilai | Catatan tafsir |
| GAP-027 (e) | "Seleksi" pada ambang penetapan pemenang oleh PA (Pasal 9(2)a.13) dibaca sebagai metode apa? | Tender untuk konsultansi / lainnya | Catatan tafsir |
| GAP-029 | Untuk setiap metode, kanal resmi mana yang dipakai dan apakah penawaran/klarifikasi/pengumuman boleh dilakukan di PRANATA? | Kanal per metode; di PRANATA: tidak / sebagian / ya | Konfirmasi tertulis pengelola pengadaan |
| GAP-003/004 | Siapa penyusun, pemeriksa, penerbit dan penanda tangan SPPBJ per metode; siapa menetapkan metode dan HPS? | Peran per langkah (default dari masukan Owner SRC-052) | Prosedur resmi terkini |
| GAP-017 | Varian dokumen dan penomoran per metode yang berlaku sekarang | Daftar varian + skema nomor | Templat resmi terkini |
| GAP-028 | Sumber anggaran resmi untuk pemeriksaan internal (RBA/RKA, aplikasi anggaran, DIPA) dan siapa memverifikasi/melepas komitmen | Sumber + peran | Konfirmasi Keuangan |
| GAP-024 | Apa kepanjangan, definisi dan bentuk laporan BMU, dan siapa yang mewajibkannya? | Definisi + contoh format resmi | Instrumen/format resmi |
| GAP-025 | Daftar resmi gudang, rantai persetujuan permintaan per jenis, bukti serah terima/konfirmasi, kewenangan transfer dan penyesuaian opname | Aturan per gudang ALP/PSRT | Prosedur gudang resmi |
| GAP-009 | Apakah ambang kapitalisasi Rp8.000.000 (masukan sementara Owner) berlaku untuk semua kategori, dengan pembanding "lebih dari"? | Ya / berbeda per kategori | Instrumen kebijakan akuntansi |
| GAP-007 | Penyusutan dimulai bulan berikutnya setelah pengakuan (masukan sementara Owner): dikonfirmasi? Metode dan masa manfaat per klasifikasi? | Ya/tidak + tabel masa manfaat | Kebijakan akuntansi resmi |

Pertanyaan lengkap dan bukti per GAP ada di [GAP_REGISTER](../00-governance/GAP_REGISTER.md).

## G. Ringkasan dampak bila belum dijawab

| Belum dijawab | Yang tertahan |
|---|---|
| D-01–D-04 | Seluruh eksekusi |
| D-08 | Merge cabang perencanaan dan setiap PR Task |
| D-05 | TASK-006 (AppShell) dan Task UI Gudang hanya dapat mengikuti IA kandidat |
| D-19–D-23 | TASK-040, TASK-076, TASK-077; UAT di staging |
| D-24 | Pelaksanaan UAT |
| D-25/D-26 | Aktivasi resmi aturan di produksi; penerimaan V1 untuk AC-16 (GAP-024) |
