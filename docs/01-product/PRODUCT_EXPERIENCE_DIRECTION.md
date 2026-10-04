# Arah pengalaman produk

Status: APPROVED | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P1 | Authorization: [AUTH-004](../00-governance/APPROVAL_RECORDS.md#auth-004) | Approval: [APPR-002](../00-governance/APPROVAL_RECORDS.md#appr-002)

Dokumen ini menetapkan arah pengalaman produk dan batas penerimaan **CAP-20** pada [V1_SCOPE](V1_SCOPE.md), berdasarkan mandat P1 [U-002](../00-governance/SOURCE_INVENTORY.md#u-002-p1-owner-request) dan [AUTH-004](../00-governance/APPROVAL_RECORDS.md#auth-004). Dokumen ini bukan desain layar, Design System, kontrak rute, atau izin memulai P7. Kriteria penerimaan produk dimiliki [ACCEPTANCE_CRITERIA](ACCEPTANCE_CRITERIA.md); ruang lingkup dimiliki [V1_SCOPE](V1_SCOPE.md).

## Pemisahan arah Owner, pengamatan, dan keputusan desain

| Label | Makna dan batas kewenangan |
|---|---|
| OWNER DIRECTION | `OWNER_APPROVED_DECISION`: U-002 §14 menetapkan video sebagai referensi utama visual/interaksi/motion, dan §15–16 menetapkan konteks workspace serta pengalaman yang mendahulukan tindakan. Arah ini berlaku; persetujuan artefak P1 tercatat terpisah melalui APPR-002 dan tidak menjadi keputusan desain P7. [OD-21](../00-governance/DECISION_LOG.md#od-21) mencatat arah referensi baru; OD-01–OD-20 tetap berlaku. |
| SOURCE OBSERVATION | Temuan visual terbatas dari frame video yang benar-benar didekode dan dilihat. Ini adalah pengamatan atas demonstrasi produk referensi, bukan bukti proses operasional UNY, kebenaran data, implementasi yang berjalan, atau prosedur resmi. Label ini menerangkan metode pemeriksaan, bukan klasifikasi baru yang menggantikan [EVIDENCE_POLICY](../00-governance/EVIDENCE_POLICY.md). |
| P7 DESIGN DECISION | Pilihan PRANATA tentang token, komponen, kepadatan, navigasi, responsive, accessibility, atau motion belum dibuat. Terjemahan yang diusulkan di bawah berklasifikasi `INFERENCE`, harus diuji dan diperinci pada P7 setelah diotorisasi, dengan aturan bisnis/akses dari fase pemiliknya. |

OWNER DIRECTION: PRANATA mengadopsi kecanggihan visual, antarmuka modern yang bersih, komposisi modular berbasis kartu, jarak yang rapi, hierarki yang tenang, interaksi yang halus, kualitas transisi, dan kesan profesional dari referensi. Terjemahannya harus cocok untuk UNY, pengguna Indonesia, Asset Workspace, Procurement Workspace, Vendor Portal, Super Admin, aksesibilitas, perangkat kecil, data operasional besar, dan pekerjaan yang produktif. Produk tetap satu aplikasi dengan data bersama (OD-01–OD-03). Nama produk, data, layanan, dan fitur bisnis pada video tidak menjadi ruang lingkup PRANATA hanya karena tampak di referensi.

## Identitas dan pemeriksaan video utama

Sumber: **SRC-047 / VIS-04**, `reference-inputs/PRANATA_PRIMARY_UI_UX_MOTION_REFERENCE.mp4`; lihat [SOURCE_INVENTORY](../00-governance/SOURCE_INVENTORY.md). Ini sumber baru yang berbeda dari SRC-033 / VIS-03, video login historis yang pada P0 hanya diperiksa metadatanya. Catatan historis tersebut tidak diganti.

| Pemeriksaan lokal pada 2026-10-04 | Hasil |
|---|---|
| Identitas byte asli | 18.799.610 byte; SHA-256 `9a67e4c5dd4125425a8995162ba08ccb42473633d25545fbd42a1c124b72005c` |
| Container dan track | Parser atom MP4 berbasis pustaka standar Python membaca durasi movie 47,484 detik; track video `avc1`/H.264, ukuran tampilan metadata 2400 × 1800, 2.849 sampel pada 60 fps, durasi track 47,483333 detik. Tidak ditemukan track audio. |
| Decode dan pemeriksaan representatif | VLC lokal yang sudah tersedia, interface dummy, software decoding, jendela tersembunyi; pemutaran setengah kecepatan dan frame dropping/skipping dinonaktifkan. 48 frame representatif pada jarak nominal 1 detik mencakup sekitar 0–47 detik; seluruh contact sheet dilihat. Frame awal beresolusi penuh juga dilihat. |
| Pemeriksaan temporal | Empat cuplikan sekitar 12–16, 21–24, 39–41, dan 44–47 detik, dengan satu frame per 6 sampel sumber (sekitar 10 fps). Total 132 frame cuplikan dilihat sebagai urutan contact sheet untuk perubahan modal, halaman, panel, dan reveal dashboard. |
| Penanganan | Original dibaca tanpa diubah; seluruh gambar/log sementara berada dalam direktori lokal yang diabaikan Git, lalu dibersihkan. Tidak ada frame, raw video, data referensi, atau dump sensitif yang disalin ke dokumen terlacak atau diunggah ke layanan eksternal. Tidak ada paket aplikasi yang dipasang. |

Waktu di bawah adalah jangkar **perkiraan** untuk menemukan bagian sumber. Sampling dan seek lokal tidak digunakan untuk menetapkan durasi/easing animasi secara presisi. Percobaan decode awal dengan hardware acceleration gagal; percobaan penuh awal pada kecepatan normal tidak menjadi dasar waktu karena kehilangan frame. Pemeriksaan final memakai 48 frame tanpa dropping/skipping dan cuplikan temporal di atas. Output scene VLC dapat memiliki ukuran raster/padding berbeda dari ukuran tampilan metadata; raster hasil decode tidak dipakai sebagai kontrak viewport PRANATA.

Skill DesainPakeAI dibaca untuk metode pemeriksaan referensi. Pengambilan panduan `evidence-and-reference` melalui klien skill gagal dengan `INVALID_CREDENTIAL_FILE`; tidak ada panduan authenticated yang berhasil dimuat, sehingga tidak diklaim sebagai sumber desain. Pemeriksaan lokal tetap memakai kebijakan bukti repository. Skill tidak memberi izin memperluas P1 atau mengunggah sumber.

## Temuan visual dan interaksi yang benar-benar terlihat

Semua baris pada tabel ini adalah **SOURCE OBSERVATION**. Interpretasi untuk PRANATA dipisahkan pada bagian berikutnya.

| Jangkar sumber perkiraan | Pengamatan yang aman | Batas bukti |
|---|---|---|
| 0–7 dan 45–47 detik | Shell desktop menempatkan sidebar kiri yang tetap, pilihan konteks organisasi di atas, pencarian, kelompok navigasi, indikator halaman aktif berbentuk pill gelap, ringkasan kecil dan identitas akun di bawah. Header konten memiliki judul dan aksi di kanan. | Perubahan halaman terlihat; sidebar collapse, menu mobile, perpindahan workspace, login, dan aturan akses tidak ditunjukkan. |
| 0–7 dan 45–47 detik | Dashboard memakai permukaan netral terang, kartu dengan sudut membulat, satu kartu gelap yang dominan, aksen warna terbatas, tipografi dengan ukuran/berat bertingkat, serta pemisahan ruang yang konsisten. Kartu metrik, grafik ringkas, ringkasan tahapan, dan daftar berbaris memiliki hierarki berbeda. | Warna, radius, font, ukuran, contrast ratio, dan token tidak diukur. Ini referensi visual, bukan token UNY yang disetujui. |
| 8–12 detik | Halaman kerja berikutnya mengelompokkan tindakan berdasarkan alasan tindak lanjut, menampilkan daftar utama, jadwal, penanda masalah, dan ringkasan pendukung. Tindakan dekat dengan konteks item; detail sekunder tidak mendominasi seluruh halaman. | Makna status dan aturan prioritas produk referensi tidak berlaku otomatis untuk PRANATA. Tidak ada bukti data skala besar. |
| 13–15 detik; cuplikan 12–16 | Pencarian berbentuk modal/palette di tengah; latar meredup dan blur, permukaan modal muncul lalu hasil dikelompokkan. Baris terpilih memiliki sorotan yang jelas. Pemilihan hasil berlanjut ke detail dengan kontinuitas visual. | Yang terlihat adalah modal pencarian. Form dialog lain, validasi, focus trap, Escape, pengembalian fokus, dan perilaku screen reader tidak diverifikasi. |
| 16–21 detik | Detail record memiliki header identitas dan aksi, tab/area aktivitas utama, panel atribut ringkas, serta kartu konteks/tugas terkait di sisi lain. Komposisi memisahkan riwayat utama dari informasi pendukung. | Ini bukan spesifikasi final jumlah kolom/tab/detail PRANATA. Tidak ada bukti child-flow back/cancel yang lengkap. |
| 22–34 detik; cuplikan 21–24 | Tampilan inbox menggabungkan daftar item, isi percakapan, composer, dan panel konteks kanan. Isi dan panel muncul berurutan. Aksi kontekstual serta umpan balik singkat setelah tindakan terlihat. | Layanan komunikasi, AI, kanal eksternal, dan integrasi pada demonstrasi tidak menambah kemampuan V1. Keberhasilan penyimpanan/pengiriman backend tidak terbukti. |
| 35–44 detik; cuplikan 39–41 | Kalender memakai grid waktu, blok kegiatan dengan perbedaan visual, kontrol rentang/tampilan, pilihan kegiatan, dan panel detail kanan. Panel melebar/muncul sambil grid tetap menjadi konteks; isi detail menyusul secara bertahap. | Panel konteks terlihat; drawer mobile atau overlay yang bergeser dari luar viewport tidak terbukti. Kalender bukan kewajiban meniru jadwal atau aturan event produk referensi. |
| Seluruh rentang; cuplikan 44–47 | Pergantian halaman memakai perubahan opacity/reveal bertahap; shell membantu mempertahankan orientasi. Kartu, angka/grafik, baris, hasil pencarian, dan panel disajikan dalam urutan yang terkoordinasi. Gerak sering memberi fokus pada konten yang baru berubah. | Sumber juga memakai zoom/pan kamera presentasi. Gerak kamera bukan bukti responsive reflow atau animasi yang harus ada dalam aplikasi. Tidak ada pengukuran frame pacing, latensi jaringan, atau waktu respons aplikasi nyata. |

Kesan respons yang halus berasal dari urutan visual yang koheren dan umpan balik yang tampak. Rekaman tidak membuktikan performa langsung, penerimaan pengguna, aksesibilitas, kemampuan perangkat kecil, atau keberhasilan setiap tombol/rute.

## Terjemahan ke PRANATA yang harus dievaluasi pada P7

Baris berikut adalah **P7 DESIGN DECISION** yang masih perlu dirancang; arah tujuannya berasal dari Owner, sedangkan bentuk adaptasinya adalah `INFERENCE` P1.

| Arah | Terjemahan yang diperlukan | Yang tetap belum diputuskan |
|---|---|---|
| Shell dan workspace yang terasa khusus | Orientasi organisasi/workspace, halaman aktif, dan aksi relevan harus jelas; setiap workspace memakai bahasa visual yang konsisten dalam satu aplikasi. Super Admin dapat memahami konteks lintas workspace/unit tanpa menyamakan pilihan UX dengan izin. | Struktur menu, urutan, komponen switcher, rute, breakpoint, dan aturan navigasi per izin. |
| Komposisi modular dan hierarki tenang | Gunakan kartu untuk ringkasan/tindakan atau konteks yang terbantu oleh pengelompokan. Ledger persediaan, aset, daftar paket, dan hasil pencarian perlu bentuk daftar/tabel yang nyaman dipindai; satu juta baris tidak ditampilkan sebagai satu juta kartu atau dimuat seluruhnya ke browser. | Token warna/UNY branding, tipografi, density mode, komponen, dan pola tabel. |
| Detail tetap memiliki konteks | Operator harus dapat meninjau item, bukti, riwayat, pihak yang bertanggung jawab, serta tindakan terkait tanpa kehilangan asal daftar. Pada perangkat kecil, informasi sekunder harus tetap dapat dicapai dengan urutan baca dan jalur kembali yang jelas. | Pemilihan panel/drawer/halaman detail, kontrak back/cancel, posisi aksi, dan state persistence terperinci. |
| Pencarian dan tindakan yang dekat dengan pekerjaan | Pencarian/filter harus membantu menemukan item sesuai konteks akses. Aksi utama harus menyatakan hasilnya dengan bahasa Indonesia yang sederhana; label, status, dan perubahan konteks harus tetap jelas dalam English. | Cakupan pencarian, field/filter, command palette, mekanisme shortcut, dan kontrak state. |
| Motion yang membantu orientasi | Gerak menunjukkan perubahan fokus, hasil tindakan, atau hubungan konteks; pengguna dapat melanjutkan pekerjaan tanpa menunggu reveal dekoratif. Pengurangan motion tetap mempertahankan informasi/status yang sama. | Durasi/easing/token motion, perilaku reduced motion, primitive/transisi, dan verifikasi teknis. |
| Kalender dan dashboard operasional | Jadwal dapat mendukung CAP kegiatan pengadaan setelah aturan pemiliknya tervalidasi. Dashboard harus membantu membaca pekerjaan tertunda, batas waktu, kesiapan unit, backlog, revisi, dan pengecualian yang dapat ditindaklanjuti. | Metrik, cara mengurutkan prioritas, aturan deadline, representasi periode, dan layar final. |

## Login, konteks workspace, dan tindakan berikutnya

**OWNER DIRECTION**, bukan pengamatan login dari video: pengalaman awal membedakan Asset, Procurement, dan Vendor; pengguna yang berhak pada beberapa workspace dapat berpindah konteks dalam satu aplikasi/akun yang sama. Pilihan workspace tidak pernah memberi izin. Backend menentukan akses, termasuk akses Super Admin yang diputuskan Owner; rincian izin/provisioning tetap milik P2/P5. V1 tetap memakai autentikasi lokal Laravel-native dengan username/email dan password, session, recovery, throttling, dan secure password handling, tanpa ketergantungan SSO/LDAP/domain/API identitas UNY (OD-11–OD-13, OD-20). Bentuk login dan switcher belum ditentukan.

**OWNER DIRECTION**: pengalaman utama menjawab “Apa yang perlu saya kerjakan sekarang?”. Produk harus dapat menjelaskan status saat ini, pemilik tindakan, pihak yang ditunggu, tindakan berikutnya, batas waktu bila relevan, masalah/revisi, dan bukti/dokumen terkait. Penerapannya berbeda menurut konteks: Unit Admin melihat kesiapan dan permintaan revisi; operator pusat melihat kiriman unit/backlog/pengecualian; reviewer melihat pekerjaan yang membutuhkan tinjauan; vendor melihat kewajiban dan status partisipasi; Super Admin melihat konteks lintas unit/workspace. Ini arah kebutuhan stakeholder, bukan pemetaan izin final atau penetapan urutan persetujuan resmi UNY.

Data yang belum ada, status yang belum diketahui, dan aturan yang masih menunggu validasi harus dinyatakan jelas. Antarmuka tidak boleh menampilkan langkah/approval/angka kesiapan yang seolah pasti dengan mengisi kekosongan aturan GAP-001–GAP-020. Penentuan perilaku bisnis tetap berada di fase pemilik dan [GAP_REGISTER](../00-governance/GAP_REGISTER.md).

## Batas penerimaan CAP-20 untuk P7 dan V1

P7 harus menghasilkan bukti desain yang dapat ditinjau terhadap referensi dan arah di atas. Kriteria berikut menetapkan hasil pengalaman produk; angka token, rute, breakpoint, dan mekanisme implementasinya tetap ditetapkan pada fase pemilik yang diotorisasi.

| Batas penerimaan | Bukti yang kelak diperlukan |
|---|---|
| Referensi diterjemahkan dengan sengaja | P7 menunjukkan pemetaan shell, hierarki/spacing, ringkasan modular, list/detail, panel/modal, pencarian, dan motion ke kebutuhan PRANATA serta menjelaskan adaptasi yang berbeda karena aksesibilitas, institusi, atau data besar. Tidak cukup menyebut “modern/premium”. |
| Konteks dan tindakan terbaca | Pada perjalanan kritis yang nantinya disetujui, pengguna dapat mengenali workspace/unit, status, pemilik tindakan, pihak yang ditunggu, tindakan berikutnya, dan bukti terkait; deadline/masalah/revisi terlihat bila relevan dan diizinkan. Kondisi tidak diketahui dinyatakan, bukan ditebak. |
| Akses tetap dikendalikan backend | Login/pilihan/perpindahan workspace memberi orientasi yang jelas; konteks yang dipilih tidak membuka data atau aksi di luar akses pengguna. Layar mengikuti kontrak P5 ketika tersedia. |
| Nyaman pada mobile dan desktop | P7 memperlihatkan perjalanan kritis pada viewport kecil dan desktop, termasuk teks Indonesia/English panjang, daftar operasional, panel/detail, dan keadaan kosong/error/loading. Konten/tindakan penting tetap dapat dijangkau, tanpa tumpang tindih atau clipping yang menutup pekerjaan. Desktop tetap produktif. |
| Aksesibilitas dapat diperiksa | Kontrak P7 mencakup keyboard, fokus yang terlihat, urutan baca, modal/panel, label, contrast, zoom, status selain warna, dan reduced motion; hasil harus diuji saat implementasi tersedia. Video tidak dianggap lulus pemeriksaan ini. |
| Motion membantu pekerjaan | Perubahan halaman/modal/panel memberikan kontinuitas dan umpan balik; motion tidak menunda akses ke tindakan, menyembunyikan kegagalan, atau menjadi satu-satunya penjelas status. Pilihan pengurangan motion tetap memberi hasil yang dapat dipahami. |
| Interaksi dan jalur kembali lengkap | Tidak ada tombol/rute/form/link mati; disabled/hidden state punya alasan yang dapat dipahami. Detail/create/edit memiliki back/cancel yang kontekstual, dan pengguna tidak kehilangan konteks daftar tanpa alasan. P3/P7 menetapkan kontrak; perjalanan kritis diverifikasi dengan browser E2E saat aplikasi tersedia. |
| Daftar besar dan operasi panjang tetap dapat dipakai | Pengguna dapat menemukan item melalui search/filter/navigasi daftar, memahami lingkup hasil, dan menerima status/progress/hasil atau kegagalan yang jelas untuk import/reporting yang lama. Tidak mengharuskan pemrosesan seluruh dataset di browser. Anggaran performa dan workload diatur dokumen NFR serta P4/P6/P8/P9. |
| Bahasa dan konsistensi lintas workspace | Default `id-ID`, dukungan `en`, dan language switch jelas; istilah dan aksi sederhana, terjemahan tidak merusak layout. Bahasa visual/interaction conventions konsisten di keempat pengalaman workspace sesuai [ENGINEERING_PRINCIPLES](../00-governance/ENGINEERING_PRINCIPLES.md). |

Tidak ada pemeriksaan browser, accessibility, contrast, responsive, atau performa aplikasi yang dilakukan pada P1 ini karena aplikasi belum ada. Statusnya **NOT VERIFIED**, bukan PASS. P1 kini DONE — APPROVED melalui APPR-002; P2–P11, planning freeze, execution Tasks, dan implementasi tetap belum diotorisasi oleh dokumen ini.
