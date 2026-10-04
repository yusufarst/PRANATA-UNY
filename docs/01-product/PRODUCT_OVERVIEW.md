# Product overview — PRANATA UNY

Status: APPROVED | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P1 | Approval: [APPR-002](../00-governance/APPROVAL_RECORDS.md#appr-002)

Dokumen ini disusun berdasarkan [AUTH-004](../00-governance/APPROVAL_RECORDS.md#auth-004) dan kandidat yang direview disetujui Owner melalui [APPR-002](../00-governance/APPROVAL_RECORDS.md#appr-002), dengan seluruh hipotesis, usulan dan kewajiban validasinya tetap dipertahankan. Otorisasi perencanaan sendiri bukan persetujuan hasil P1. Arah Owner yang sudah berlaku tetap dimiliki [DECISION_LOG](../00-governance/DECISION_LOG.md); seluruh GAP-001–GAP-020 tetap OPEN dalam [GAP_REGISTER](../00-governance/GAP_REGISTER.md). P2–P11 dan implementasi belum diotorisasi.

## Identitas dan nilai produk

**PRANATA UNY adalah satu aplikasi web dan satu ekosistem data bersama untuk menghubungkan kebutuhan unit, pengadaan, penyedia, hasil serah terima, aset/persediaan/KDP, rekonsiliasi, serta pelaporan dan bukti audit.** Pengguna memperoleh ruang kerja yang sesuai dengan pekerjaannya tanpa memisahkan hubungan data antarproses (OD-01–OD-03).

Asset Workspace, Procurement Workspace, Vendor Portal, dan Super Admin merupakan pengalaman khusus di dalam produk yang sama. Organization Unit adalah konsep organisasi umum yang dapat mencakup fakultas, direktorat, lembaga, biro, unit, dan struktur lain di masa depan (OD-04). P1 tidak menetapkan entitas, mekanisme hierarki, atau tabel. Pengadaan dari unit dapat masuk ke operasi pengadaan pusat yang relevan; urutan persetujuan resmi tetap perlu divalidasi (OD-05; GAP-001).

Nilai utamanya: pengguna dapat menjawab **“Apa yang harus saya kerjakan sekarang, siapa yang sedang ditunggu, dan bukti apa yang diperlukan?”** dari data proses yang sama. Pengelola dapat melihat kesiapan, pekerjaan tertunda, dan masalah lintas unit sesuai aksesnya. Penyedia dapat berpartisipasi tanpa berulang kali mengisi data perusahaan yang sudah tersedia. Petugas aset/persediaan/KDP dapat menggunakan hasil pengadaan yang relevan sebagai bahan awal pencatatan, kemudian melengkapi dan memvalidasinya.

## Masalah yang hendak diselesaikan dan kekuatan bukti

P0 memuat inspeksi terbatas atas sumber historis, bukan studi waktu kerja atau survei pengguna. Karena itu, masalah berikut membedakan arah Owner, observasi sumber, dan hipotesis dampak. Belum ada angka dasar mengenai keterlambatan, tingkat kesalahan, jumlah lembur, atau penghematan.

| Kelompok masalah | Dasar yang tersedia | Implikasi produk dan batas kesimpulan |
|---|---|---|
| Input berulang, spreadsheet/dokumen terpisah, pertukaran file manual | OD-07/08; [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families) menunjukkan Input/Mail Merge dan banyak keluaran dokumen pada SRC-028/029; SRC-025/037 menunjukkan bahan SPJ/handoff | Data terstruktur dan dokumen perlu terhubung. Sumber membuktikan bentuk bahan kerja, bukan frekuensi input ulang atau bahwa semua proses sekarang memakai file yang sama. |
| Kepemilikan proses, pihak yang ditunggu, tindakan berikutnya dan handoff unit–pusat kurang jelas; pertanyaan “sudah sampai mana?” berulang | OD-05/07/19; [PR-F02/03](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f03-rup-and-organizational-request-handoffs-are-candidate-evidence) dan GAP-001–004 menunjukkan tanggung jawab/prosedur belum tervalidasi | Tampilkan status, pemilik pekerjaan, pihak yang ditunggu, tindakan berikutnya dan bukti. P1 tidak memilih rantai tanda tangan atau penanggung jawab formal dari template. |
| Komunikasi/status penyedia terpencar dan informasi perusahaan berulang | U-002; OD-23; SRC-028/029 mempunyai kelompok data penyedia/kontak dan dokumen partisipasi; SRC-013–017/032/042/043/045 mempunyai keluaran pengadaan | Profil perusahaan dapat digunakan ulang dan partisipasi tetap berhubungan dengan paket. Besarnya duplikasi, kanal komunikasi resmi dan aturan onboarding belum terbukti. |
| Input aset unit terlambat/membingungkan, kantor pusat menunggu kiriman, masalah baru terlihat dekat penutupan periode, koreksi dan lembur menumpuk | OD-07/19 dan U-002; SRC-026/031/034–036/044 menunjukkan laporan, rekap dan keluarga koreksi/lifecycle | Buat kesiapan unit, permintaan perbaikan dan pengecualian terlihat lebih awal. Sumber belum membuktikan waktu penyelesaian atau penyebab setiap keterlambatan; pengurangan lembur adalah hasil yang ingin diuji. |
| Pengadaan lemah terhubung dengan pencatatan aset/persediaan/KDP dan rekonsiliasi Finance | OD-03/06; SRC-025/029, SRC-031/034 dan [PR-U05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#candidate-unresolved-questions-for-the-central-gap-register); GAP-010/018 | Hubungkan asal perolehan, kontrak, serah terima, draft downstream, rekonsiliasi dan laporan. Bukti belum menentukan peristiwa akuntansi atau siapa yang mengesahkan hasilnya. |
| Data historis heterogen, koreksi/reporting sulit ditelusuri, manajemen sulit melihat kesiapan universitas | OD-07/16/19; SRC-018/019/027/030/031/034–036/044/046; [asset review](../00-governance/evidence/ASSET_SOURCE_REVIEW.md) | Validasi data, pengecualian yang dapat ditindaklanjuti, hubungan sumber dan riwayat harus tersedia. Tidak ada hasil audit menyeluruh, rekonsiliasi angka, atau benchmark sejuta baris yang sudah lulus. |

Sumber yang diklasifikasikan OBSERVED_CURRENT_PROCESS dapat berupa bahan historis yang penggunaan terkininya belum dikonfirmasi. Nama file, menu, formula, atau kepala surat tidak otomatis menjadi aturan resmi. [REFERENCE_COVERAGE](REFERENCE_COVERAGE.md) memiliki pemetaan sumber–kapabilitas dan batas pembuktiannya.

## Hasil yang dituju

| ID | Hasil produk | Kapabilitas terkait |
|---|---|---|
| GOAL-01 | Unit, petugas pusat, penyedia dan pengelola memahami pekerjaan, pihak yang ditunggu dan kesiapan tanpa harus mencari status di file terpisah | CAP-02–CAP-07, CAP-09, CAP-17 |
| GOAL-02 | Data valid dipakai ulang pada keluaran dokumen dan handoff; operator melengkapi data yang benar-benar belum tersedia | CAP-07–CAP-10, CAP-15 |
| GOAL-03 | Pengadaan sampai hasil aset/persediaan/KDP mempunyai hubungan asal, bukti, pemilik pekerjaan dan riwayat yang dapat ditelusuri | CAP-04–CAP-17, CAP-19 |
| GOAL-04 | Kualitas data dan masalah rekonsiliasi terlihat sebelum laporan dianggap siap; sumber/rule yang belum valid tidak berubah menjadi angka final secara diam-diam | CAP-12–CAP-17, CAP-19 |
| GOAL-05 | Pekerjaan pada skala yang dituju tetap dapat dilakukan dengan pencarian, daftar yang terbatas, dan umpan balik operasi panjang | CAP-16–CAP-18, CAP-20 |
| GOAL-06 | Pengguna Indonesia memperoleh pengalaman kerja yang jelas, nyaman, dapat diakses dan selaras dengan arah visual/motion Owner; English tetap tersedia | CAP-01–CAP-03, CAP-20 |

## Stakeholder dan kebutuhan kerjanya

Tabel ini memetakan kebutuhan produk; bukan daftar role global, matriks izin, atau penetapan jabatan resmi. Satu orang dapat memiliki beberapa tanggung jawab dan akses ruang kerja sesuai ketentuan yang kelak disetujui P2/P5 (OD-09/20; GAP-014).

| Kelompok stakeholder | Kebutuhan utama | Ruang kerja terkait / contoh tanggung jawab kandidat |
|---|---|---|
| Pengusul/pengguna internal Organization Unit | Mengajukan kebutuhan, memahami status dan permintaan revisi, melengkapi bukti, mengetahui handoff berikutnya | Procurement dan/atau Asset; kandidat Internal User |
| Pengelola unit | Mengelola kesiapan kiriman unit, data aset/lokasi/tanggung jawab, koreksi dan inventarisasi sesuai kewenangan | Asset dan Procurement; kandidat Unit Admin |
| Petugas pengadaan pusat | Menerima usulan lintas jenis unit, mengelola paket/RUP/proses, aktivitas, penyedia, dokumen, kontrak dan handoff | Procurement; kandidat Central Operator |
| Pemeriksa/pemberi persetujuan dan tim proses | Menelaah bukti, meminta perbaikan, melaksanakan tanggung jawab yang ditugaskan, melihat hasil dan pengecualian | Ruang kerja sesuai penugasan; kandidat Reviewer / Approver |
| Penyedia lama maupun baru, termasuk PIC perusahaan | Onboarding, profil dan dokumen kualifikasi yang dapat digunakan ulang, partisipasi paket, jadwal/tindakan, status dan bukti pengiriman | Vendor Portal; kandidat Vendor; hubungan akun–perusahaan/PIC belum diputuskan |
| Petugas aset, persediaan dan KDP pusat | Menyelesaikan draft hasil perolehan, mengelola register/ledger/lifecycle, memeriksa kiriman unit, menindaklanjuti masalah data | Asset; kandidat Central Operator dengan tanggung jawab yang relevan |
| Finance/bendahara dan penanggung jawab rekonsiliasi | Menelusuri SPJ/payment tracking, klasifikasi dan laporan, menilai selisih serta kesiapan periode | Asset/Procurement sesuai akses; penanggung jawab/signoff resmi masih GAP-010 |
| Pimpinan universitas dan pimpinan unit | Melihat kesiapan, backlog, pengecualian dan hasil pelaporan dalam cakupan yang diizinkan | Ringkasan ruang kerja; tidak otomatis memperoleh akses ke seluruh detail sensitif |
| Pemeriksa/auditor yang diotorisasi | Menelusuri asal, perubahan, bukti dan laporan untuk pemeriksaan | Akses penelaahan yang kelak divalidasi; tidak menambah role global wajib saat P1 |
| Owner/pengelola sistem | Mengakses semua ruang kerja, Organization Unit, pengguna, master/configuration dan administrasi sistem | Super Admin; kemampuan ini wajib di tingkat produk (OD-10) |

PPK, KPA, Pejabat Pengadaan, Pokja, Tim Teknis, Pemeriksa/Penerima dan posisi serupa menjadi kandidat penugasan/kapabilitas/tanggung jawab proses. P1 tidak otomatis mengubah tiap jabatan menjadi role aplikasi atau memberikan kewenangan tanda tangan (GAP-003/004/014).

## Prinsip produk yang harus dijaga

1. **Satu produk, data bersama.** Ruang kerja khusus menjaga konteks pengguna sambil mempertahankan hubungan lintas siklus hidup. Pengguna dengan beberapa akses dapat berpindah ruang kerja dengan akun yang sama; pilihan ruang kerja tidak memberikan izin (OD-01–03/20).
2. **Data once, reuse many times.** Data domain/proses yang valid mendukung status pekerjaan, dokumen, draft downstream dan bukti laporan. Dokumen umumnya merupakan keluaran/bukti data proses, sambil tetap memungkinkan dokumen eksternal yang sah ditautkan sebagai bukti. P1 tidak menganggap semua dokumen dapat dibuat otomatis (OD-08; GAP-017/019).
3. **Pekerjaan lebih dahulu.** Sajikan tindakan tertunda, tenggat yang relevan, pemilik pekerjaan, pihak yang ditunggu, revisi, pengecualian dan kesiapan. Jika tidak ada pihak yang ditunggu atau tindakan yang berlaku, jelaskan keadaan itu; jangan menampilkan status tanpa makna (OD-19).
4. **Makna dan sejarah dipertahankan.** Koreksi, reklasifikasi, pengembangan, penghapusan, KDP, ledger persediaan dan nilai buku tidak dipersamakan. Kondisi fisik tidak ditentukan oleh usia penyusutan; nilai buku nol tidak berarti rusak atau boleh dihapus (U-002; GAP-005–009).
5. **Mandiri pada V1.** Akun lokal Laravel-native, username/email dan password, sesi, pemulihan password, throttling serta penanganan password aman mengikuti arah Owner. UNY SSO/domain/API tidak menjadi syarat penggunaan; mekanisme akun/pemulihan dan keamanan detail tetap P5/P9 (OD-11–13; GAP-015).
6. **Dapat dipakai pada skala nyata.** Arah ~200 pengguna dan ~1.000.000 baris aset adalah konteks produk, bukan klaim 200 pengguna serentak atau kapasitas yang sudah teruji (OD-16/22; GAP-016). Pengguna tidak diminta memuat/menangani seluruh data di browser untuk pekerjaan biasa.
7. **Pengalaman kerja jelas dan nyaman.** Bahasa Indonesia sederhana sebagai default, English dan pengalih bahasa, mobile-first/responsive/adaptive, aksesibilitas, kepadatan yang sesuai kerja operasional, serta return/cancel yang kontekstual merupakan bagian nilai produk (OD-17; ENGINEERING_PRINCIPLES).
8. **Biaya terjaga.** Target tambahan biaya produksi rutin mendekati nol di luar infrastruktur/domain yang dibiayai klien. Kapabilitas inti tidak diam-diam bergantung pada SaaS berbayar; pengecualian memerlukan Owner ([COST_POLICY](../00-governance/COST_POLICY.md); OD-18).

## Pandangan kapabilitas ujung ke ujung

Peta kerja produk: **Kebutuhan → Usulan Pengadaan → RUP → Proses Pengadaan → Penyedia → Kontrak → Pelaksanaan → Serah Terima / BAST → SPJ / Payment Tracking → Aset / Persediaan / KDP → Rekonsiliasi → Pelaporan / Finance / Audit** (OD-06).

Peta ini menunjukkan hubungan nilai dan bukti, bukan urutan resmi yang wajib diterapkan pada semua metode. Tahap dapat membutuhkan pengulangan, pengecualian atau hubungan yang berbeda setelah domain/workflow tervalidasi. Peristiwa yang menimbulkan tanggung jawab atau kapitalisasi belum dipilih (GAP-001–004/018).

[V1_SCOPE](V1_SCOPE.md) memiliki katalog CAP-01–CAP-20 dan batas MUST/SHOULD/FUTURE/OUT. [ACCEPTANCE_CRITERIA](ACCEPTANCE_CRITERIA.md) memiliki syarat terukur untuk menilai hasil produk. P1 tidak membuat workflow state, route, schema, desain layar atau Task dari peta ini.

## Arah pengalaman produk

Owner menetapkan video SRC-047/VIS-04 sebagai **referensi utama visual, interaksi dan motion** (OD-21; U-002). PRANATA diarahkan memiliki karakter bersih, modern, profesional, komposisi modular berbasis kartu, ruang yang rapi, hierarki tenang, serta transisi yang halus dan terkendali. Arah itu harus diterjemahkan untuk pekerjaan pengadaan/aset, pengguna Indonesia, aksesibilitas, layar kecil dan data operasional besar.

Catatan tentang apa yang benar-benar terlihat, batas inspeksi temporal, dan kewajiban review P7 dimiliki [PRODUCT_EXPERIENCE_DIRECTION](PRODUCT_EXPERIENCE_DIRECTION.md). Preferensi Owner, observasi video, dan keputusan desain P7 harus dibedakan. P1 tidak menyalin produk referensi, menetapkan token akhir, memilih seluruh pola halaman, atau membakukan durasi/easing animasi.

Observasi sumber yang dicatat dalam review video meliputi shell desktop dengan sidebar berkelompok dan konteks organisasi, pencarian serta tindakan yang mudah ditemukan, kartu modular pada surface terang dengan kontras yang terarah, kelompok tugas/jadwal/pengecualian, panel kontekstual pada detail/inbox, command-search modal dan komposisi kalender–panel event. Reveal/transisi yang terlihat menjadi bahan terjemahan P7. Rekaman demo juga memakai perubahan framing/zoom, sehingga bukan bukti latency aplikasi, perilaku mobile, keyboard atau aksesibilitas.

Pengalaman masuk membedakan konteks Asset, Procurement dan Vendor; pengguna dengan akses lintas ruang kerja perlu dapat berpindah tanpa akun/aplikasi terpisah. Backend tetap menentukan akses. Letak pemilih ruang kerja, susunan login, navigasi dan komponen final menjadi kewajiban P7 bersama hasil P5.

## Batas produk

V1 yang diusulkan mencakup nilai ujung ke ujung dan pengelolaan data/laporan inti; bukan sekadar kumpulan form pencatatan. Pemindahan transaksi ke Asset/Persediaan/KDP harus tetap berbasis data yang memenuhi aturan validasi kelak. Handoff berbentuk draft terisi merupakan hipotesis pengalaman produk yang perlu divalidasi, bukan izin mengakui aset secara otomatis (OD-23; GAP-018).

V1 tidak menggantikan SPSE/LPSE/SiRUP, sistem Finance umum, bank/pencairan dana, HR, atau layanan identitas institusi. Referensi/status/bukti dari proses eksternal yang relevan dapat dicatat lokal tanpa integrasi langsung. Produk melacak SPJ/pembayaran; produk tidak mengklaim mengeksekusi pembayaran. Layanan peminjaman fasilitas tidak masuk usulan V1; GAP-020 tetap OPEN sampai keputusan Owner. Rincian FUTURE dan OUT dimiliki V1_SCOPE.

## Ukuran keberhasilan yang diusulkan

Ukuran di bawah adalah **target usulan untuk review Owner**, bukan data observasi atau janji capaian sebelum pengujian. Skenario, peserta, sampel data, periode dan kondisi ukur harus disetujui saat P8/P10 diotorisasi. Syarat acceptance dan target respons hanya dimiliki ACCEPTANCE_CRITERIA; tabel ini menghubungkannya dengan hasil bisnis.

| ID | Ukuran hasil | Cara menilai kelak / batas |
|---|---|---|
| SM-01 | 100% skenario handoff/action yang dipilih menunjukkan asal unit, status, pemilik pekerjaan, pihak yang ditunggu atau penjelasan tidak berlaku, tindakan berikutnya dan bukti yang relevan | Review skenario GOAL-01 terhadap acceptance CAP-03/04/07/09/17. Nilai tidak boleh dipenuhi dengan label kosong atau dugaan penanggung jawab. |
| SM-02 | 100% field yang disepakati dapat digunakan ulang dalam sampel menghasilkan data yang konsisten pada dokumen/draft downstream, dengan asal dan kebutuhan pelengkapan jelas | Review GOAL-02; bandingkan field terpilih, bukan semua kolom seluruh workbook. Kebijakan versi/koreksi disetujui kemudian. |
| SM-03 | 100% sampel perjalanan yang dipilih dapat ditelusuri dari asal/perolehan ke hasil downstream/laporan dan bukti perubahan yang berlaku | Review GOAL-03 dan CAP-19; cakupan audit serta akses ditetapkan P2/P5, tanpa menampilkan data lintas cakupan secara bebas. |
| SM-04 | Nol selisih yang tidak dijelaskan dalam sampel rekonsiliasi yang disepakati; nol data yang gagal validasi diterima diam-diam sebagai data/laporan final | Review GOAL-04; selisih yang mempunyai penjelasan tetap perlu disposition/signoff sesuai aturan tervalidasi. Tidak mengklaim data historis sekarang sudah bersih. |
| SM-05 | Peserta UAT terkait dapat menyelesaikan skenario inti tanpa bantuan fasilitator, dengan hasil benar dan status/tindakan berikutnya dapat dijelaskan; ambang target usulan dimiliki NFR-P04 | Usulan hasil produktivitas GOAL-01/06; ukuran sampel dan rubrik harus disepakati agar persentase bermakna. Tidak menetapkan susunan layar atau waktu latihan pada P1. |
| SM-06 | Ekspektasi respons daftar/pencarian serta umpan balik pekerjaan panjang terpenuhi pada workload representatif skala produk | Gunakan target usulan NFR-P01–NFR-P04 dalam ACCEPTANCE_CRITERIA untuk GOAL-05. ~200 total pengguna tidak otomatis berarti ~200 concurrent sessions (GAP-016). |
| SM-07 | Waktu input ulang, waktu tunggu handoff, kesalahan yang ditemukan mendekati tutup periode, dan lembur pelaporan dapat dibandingkan sebelum/sesudah penggunaan | Kumpulkan baseline dengan unit/pusat/Finance sebelum menilai dampak GOAL-01–04; target penurunan angka belum ditetapkan karena baseline tidak ada. |

## Risiko, asumsi dan kewajiban handoff

| Risiko/asumsi | Implikasi / pemilik validasi pada fase kelak |
|---|---|
| Sumber SOP/template/menu/workbook historis belum membuktikan prosedur dan versi resmi | GAP-001–004/013/017/019; P2/P3 dengan custodian pengadaan menentukan metode, tanggung jawab, nomor/varian dan makna dokumen. |
| Pengadaan Langsung/Tender dan varian relevan adalah usulan cakupan awal berdasarkan bahan yang tersedia, bukan hasil validasi seluruh applicability UNY | P2/P3 mengonfirmasi kebutuhan/metode resmi. Jika kewajiban yang berlaku memerlukan cakupan lain, ajukan revisi scope kepada Owner; jangan mengabaikan kewajiban resmi demi batas usulan P1. |
| Accounting, cutoff, kode persediaan, P01/P02 dan koreksi/kapitalisasi belum sahih | GAP-005–009; P2 dengan ahli aset/persediaan/Finance. Tidak membuat formula, mapping atau nilai fisik dari kode legacy pada P1. |
| Handoff BAST/payment/downstream dan signoff rekonsiliasi belum mempunyai makna/tanggung jawab final | GAP-010/018; P2/P3 memastikan peristiwa, bukti, penerima pekerjaan dan pengecualian; P5 menentukan akses. |
| Data historis, sumber saat cutover dan kapasitas belum tervalidasi | GAP-011/012/016; P4/P6/P8/P9/P10 kelak menentukan rencana ukur, validasi dan transisi aman. V1 bukan alasan mengabaikan sejarah. |
| Compact roles, provisioning dan recovery belum disetujui secara rinci | GAP-014/015; P2/P5/P9 tetap mempertahankan lokal, backend authorization dan pemulihan yang dapat dipakai tanpa hidden institutional/paid dependency. |
| Pengalaman premium dapat berubah menjadi komposisi terlalu kosong/animasi yang mengganggu atau tabel yang sulit dipakai | P7 menguji terjemahan referensi utama pada pekerjaan nyata, aksesibilitas, reduced motion, ID/EN dan perangkat berbeda; P1 tidak mendesain token. |
| Peluang peminjaman dari sumber dapat memperluas lingkup tanpa keputusan | GAP-020 tetap OPEN; diusulkan FUTURE, tidak ada alur/role/fitur peminjaman wajib dalam V1. |

Tidak ada kesimpulan dalam dokumen ini yang menutup GAP. Kebutuhan MUST yang bergantung pada aturan belum tervalidasi adalah kewajiban rilis yang harus dipenuhi melalui perencanaan dan persetujuan fase terkait, bukan placeholder yang boleh langsung dieksekusi. P1 kini DONE — APPROVED melalui APPR-002; produk belum dinyatakan siap dibangun atau siap dirilis.
