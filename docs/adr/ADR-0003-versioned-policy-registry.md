# ADR-0003 — Policy Registry berversi dengan aktivasi maker-checker dan label simulasi

Status: PROPOSED (P4 candidate, AUTH-012) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: belum ada — memerlukan persetujuan Owner atas kandidat P4

## Konteks dan bukti

Dua puluh sembilan GAP masih OPEN, termasuk ambang metode, kewenangan, varian dokumen, aturan gudang, kapitalisasi dan penyusutan. Owner mengarahkan aturan per rezim berbasis kebijakan berversi (OD-27), validasi sebelum aktivasi (OD-30) dan rantai persetujuan yang dapat dikonfigurasi (OD-31). Agen dilarang mengarang aturan resmi.

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Policy Registry berversi (dipilih) | Aplikasi lengkap dan teruji; jawaban GAP menjadi konfigurasi, bukan pengembangan ulang |
| Hard-code aturan setelah GAP terjawab | Menunda pengembangan, setiap perubahan regulasi butuh rilis |
| Konfigurasi bebas tanpa versi/maker-checker | Tidak dapat diaudit; risiko perubahan diam-diam |
| Mesin aturan generik (DSL/BPMN) | Berlebihan; sulit diuji dan dipahami |

## Keputusan dan alasan

Keluarga kebijakan dengan skema JSON tervalidasi, versi DRAFT→IN_REVIEW→ACTIVE→SUPERSEDED/RETIRED, aktivasi oleh orang berbeda dari pembuat dengan bukti resolusi GAP dan kasus uji yang lulus, jendela tanggal berlaku tanpa tumpang tindih (termasuk versi yang sudah digantikan, tanpa tanggal mundur), penyimpanan `policy_version_id` pada setiap hasil beserta semua versi penyumbang di `policy_version_uses` bila hasil bergantung pada beberapa keluarga, dan label simulasi untuk keluaran berbasis versi yang tidak berlaku resmi. Keberlakuan resmi dipisahkan dari status siklus hidup: versi berlaku resmi pada tanggal acuan bila disahkan (ACTIVE, atau SUPERSEDED yang jendelanya masih memuat tanggal itu) dan jendelanya memuat tanggal itu; semua gerbang aksi resmi, termasuk versi template, skema penomoran dan versi klasifikasi, memakai definisi ini ([POLICY_REGISTRY §3.1](../04-architecture/POLICY_REGISTRY.md#31-keberlakuan-resmi)). Di produksi, aksi resmi tanpa versi yang berlaku resmi pada tanggal acuannya tidak tersedia.

## Risiko

- Kebijakan tidak kunjung disahkan → fitur tidak dapat dipakai resmi di produksi; dimitigasi paket resolusi GAP.
- Skema keluarga kurang ekspresif untuk aturan baru → versi skema baru melalui TASK PLAN CHANGE.

## Biaya

Tidak ada.

## Dampak kompatibilitas dan data

Tabel `policy_*`; kolom `policy_version_id`/`is_simulation` pada record hasil.

## Migrasi dan rollback

Bila dibatalkan sebelum produksi, aturan harus di-hard-code dengan GAP terbuka — tidak dapat diterima; tidak ada rollback yang aman selain penggantian desain.

## Yang terdampak

POLICY_REGISTRY, DATA_MODEL, WF-029/030/031/032/033, CAP-23, AC-32, banyak Task.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
