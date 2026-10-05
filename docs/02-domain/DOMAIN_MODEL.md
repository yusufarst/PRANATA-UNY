# Domain model — PRANATA UNY

Status: APPROVED | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P2 | Preparation: [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006), completed | Approval: [APPR-003](../00-governance/APPROVAL_RECORDS.md#appr-003) | Checkpoint: [AUTH-007](../00-governance/APPROVAL_RECORDS.md#auth-007), automatically completed after verified publication

Dokumen ini memiliki **model konseptual, hubungan, kepemilikan makna, klasifikasi kebenaran dan identitas domain**. [DOMAIN_GLOSSARY](DOMAIN_GLOSSARY.md) memiliki definisi **77 konsep DC-001–DC-077**; [BUSINESS_RULES](BUSINESS_RULES.md) memiliki rumusan BR/INV. Rujukan aturan di bawah adalah locator ke pemiliknya, bukan rumusan kedua. [DOMAIN_RESPONSIBILITIES](DOMAIN_RESPONSIBILITIES.md), [DOMAIN_LIFECYCLES](DOMAIN_LIFECYCLES.md) dan [DOMAIN_TRACEABILITY](DOMAIN_TRACEABILITY.md) memperinci tanggung jawab, riwayat dan alasan sumber. P2 disetujui APPR-003 dengan seluruh kualifikasi tetap berlaku; persetujuan model konseptual ini tidak menetapkan kebijakan kelembagaan atau rancangan implementasi.

P0/P1 tetap DONE dan disetujui. AUTH-006 adalah otorisasi persiapan P2 yang telah selesai; AUTH-007 hanya mengizinkan checkpoint P2 sampai publikasi/verifikasi berhasil, lalu otomatis selesai; P3–P11 belum diotorisasi. Tidak ada tabel, field database, kunci, format nomor, workflow/state machine, route, desain layar, matriks izin, formula akuntansi, Task, freeze, atau aplikasi dalam model ini. AICWDF §13 diadaptasi menurut batas eksplisit Owner: makna lifecycle/history ada di P2; urutan/transisi rinci P3, penyimpanan P4, penegakan izin P5 dan mekanisme P6 tetap menunggu otorisasi.

## Authority dan cara membaca

Authority mengikuti [SOURCE_OF_TRUTH](../00-governance/SOURCE_OF_TRUTH.md) dan [EVIDENCE_POLICY](../00-governance/EVIDENCE_POLICY.md). Arah Owner tetap di [DECISION_LOG](../00-governance/DECISION_LOG.md); P1 di [V1_SCOPE](../01-product/V1_SCOPE.md)/[ACCEPTANCE_CRITERIA](../01-product/ACCEPTANCE_CRITERIA.md). [PROCUREMENT_SOURCE_REVIEW](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md) dan [ASSET_SOURCE_REVIEW](../00-governance/evidence/ASSET_SOURCE_REVIEW.md) mendukung keberadaan konsep, dengan locators terbatas dan tanpa pengesahan prosedur/akuntansi. Tidak ada sumber baru dinyatakan sebagai policy resmi atau GAP ditutup oleh pemodelan ini.

Status konsep mengikuti glossary. Hubungan/desain pemisahan yang tidak dinyatakan Owner adalah **PROPOSED**. Keberadaan kategori historis dapat **EVIDENCE_SUPPORTED** sementara applicability, kriteria penerimaan dan responsibility resminya **BLOCKED_BY_GAP**. Nama `APPROVED_OWNER_DIRECTION` menunjuk arah yang sudah berlaku, bukan persetujuan artefak P2. Status lengkap aturan dimiliki BUSINESS_RULES.

Istilah **record otoritatif domain** dalam tabel berikut adalah peran konseptual kandidat: informasi yang kelak diterima menurut aturan/otoritas yang divalidasi menjadi dasar representasi PRANATA. Ini tidak menyatakan data historis sekarang sudah valid, PRANATA sudah beroperasi, atau PRANATA mengalahkan dokumen/sistem resmi. Authority eksternal dan pemilik penulisan pada cutover masih GAP-012/013.

## Area domain dan kepemilikan konseptual

| Area | DC yang dicakup | Pemilik makna / bentuk tanggung jawab kandidat | Batas authority |
|---|---|---|---|
| Organisasi, aktor dan pekerjaan | DC-001–009 | Konteks organisasi dan penugasan menjelaskan asal, tanggung jawab, pihak tunggu dan pekerjaan; pemetaan aktor/tugas dirujuk ke DOMAIN_RESPONSIBILITIES. | Hierarki generic dari OD-04; unit asal dan unit penanggung jawab dapat berbeda. Approval path GAP-001 dan akses GAP-014. |
| Data, dokumen, bukti dan kebijakan | DC-010–020, DC-077 | Data/proses mempunyai asal; keluaran dan bukti mempunyai hubungan versi; custodian sumber/policy mengesahkan applicability yang relevan. | Dokumentasi/historical signature tidak otomatis policy. GAP-013/017/019; retensi/security rinci P5/P9/P10. |
| Kebutuhan, pengadaan dan kontrak | DC-021–027, DC-036–048 | Pemilik usulan dan pekerjaan paket/kontrak mempunyai tanggung jawab sesuai penugasan dan metode yang kelak disahkan. | Tidak memilih satu jabatan pusat untuk semua pekerjaan. GAP-001–004/013/017–019. |
| Penyedia dan partisipasi | DC-028–035 | Informasi perusahaan berkelanjutan; wakil/kontak dan pihak penerima/penelaah terkait bertanggung jawab pada konteks profil atau partisipasi yang sesuai. | Hubungan akun–perusahaan/PIC, eligibility dan visibility resmi GAP-003/014/015/017. Vendor Portal first-class dari P1. |
| Klasifikasi dan bahan handoff hilir | DC-049–050 | Penanggung jawab klasifikasi dan operator hilir yang kelak divalidasi menjelaskan applicability, pelengkapan dan penerimaan. | Draft terisi adalah hipotesis OD-23; pemicu/owner/accounting GAP-008/009/018. |
| Aset dan lifecycle historis | DC-051–064 | Penanggung jawab register/transaksi, kustodian dan pihak pemeriksa yang ditugaskan mempunyai konteks berbeda; Asset/Finance memvalidasi policy akuntansi. | Tidak memberi jabatan/peran izin otomatis. GAP-007–009/011/013/014/018. |
| Persediaan | DC-065–069 | Pengelola ledger/hitung fisik serta custodian kamus transaksi yang kelak divalidasi menjelaskan makna dan hasil. | Domain ledger terpisah; kamus/sign/mapping GAP-005/006, reconciliation GAP-010. |
| KDP | DC-070 | Penanggung jawab bukti pekerjaan/posisi KDP dan pihak validasi hasil definitif sesuai penugasan yang disahkan. | KDP terpisah dari aset definitif; recognition/value/completion GAP-008/009/018. |
| Rekonsiliasi, intake historis dan laporan | DC-071–076 | Kasus mempunyai pihak tindak lanjut/pihak tunggu; custodian sumber dan penerima hasil menjelaskan trust/periode/penerimaan. | Tidak menetapkan Finance sebagai signer universal, source precedence, cleansing atau cutover. GAP-010–013/019. |

Pembagian ini adalah area makna, bukan struktur modul aplikasi. Satu konsep dapat terkait beberapa area; bukti dan assignment lintas area tetap berada dalam satu ekosistem (OD-01–03). Tanggung jawab proses berbeda dari kepemilikan hukum, pengelolaan record dan izin aplikasi.

## Klasifikasi konsep dan sumber kebenaran

| Kode klasifikasi | Arti konseptual |
|---|---|
| **R — Record domain** | Dasar fakta/keputusan/konteks domain yang diterima setelah authority dan validasi berlaku. R tidak berarti approval otomatis atau sudah resmi sekarang. |
| **H — Riwayat transaksi/peristiwa** | Catatan tindakan, perubahan, hasil atau versi yang menjelaskan record pada waktu/konteks tertentu. Penerimaannya mengikuti aturan yang divalidasi. |
| **D — Representasi/nilai turunan** | Posisi, ringkasan, nilai atau tampilan yang dapat dijelaskan dari record/riwayat dan policy yang berlaku; bukan kebenaran bebas yang bersaing. |
| **O — Keluaran hasil generasi** | Dokumen/laporan yang dibentuk dari sumber dan versi tertentu; perubahan output mempunyai konteks sumber/revisi. |
| **E — Referensi/bukti eksternal** | Bukti atau rujukan dari luar PRANATA yang keaslian, authority, applicability dan penggunaannya perlu dinilai. |
| **S — Sumber historis/kandidat belum diterima** | Asal atau interpretasi kandidat yang mempertahankan nilai mentah, provenance dan keterbatasan trust. S belum menjadi R/H dengan keberadaan file saja. |

Gabungan kode menunjukkan konsep memerlukan pemisahan beberapa aspek, bukan satu record atau kolom gabungan. Pengelolaan fisik/teknis tidak dipilih di P2. Semua pemetaan klasifikasi adalah **PROPOSED**; batas keselamatannya berlandaskan arah Owner/P1 dan aturan kanonik yang dirujuk.

| ID / konsep | Klasifikasi | Hubungan / kualifikasi yang menjelaskan kebenaran |
|---|---|---|
| DC-001 Unit Organisasi | R + H | Identitas/konteks organisasi dan riwayat keberlakuan mendasari hubungan asal/tanggung jawab; struktur kini tidak menghapus konteks lama. |
| DC-002 Referensi Akun | R | Referensi identitas akun lokal untuk aktor; authority akun dan hubungannya P5/GAP-014/015. Bukan record perusahaan/pegawai institusi. |
| DC-003 Konteks Ruang Kerja | R | Konteks operasional bersama, terpisah dari penentuan akses. |
| DC-004 Aktor Domain | R | Referensi pihak pada tindakan/assignment; identitas dan eligibility formal tetap perlu validasi. |
| DC-005 Penugasan Tanggung Jawab | R + H | Assignment aktif dan versi/penggantiannya menjelaskan siapa bertanggung jawab untuk objek/waktu terkait. |
| DC-006 Keluarga Peran Aplikasi | R | Kandidat pengelompokan kemampuan aplikasi; tidak membuat formal position menjadi global role. |
| DC-007 Cakupan Organisasi | R | Konteks tanggung jawab yang dibedakan dari organisasi asal dan permission. |
| DC-008 Tanggung Jawab Formal | R + E | Hubungan tanggung jawab membutuhkan rujukan jabatan/delegasi/prosedur yang sahih; label sumber historis hanya E. |
| DC-009 Konteks Pekerjaan/Tindakan | D + R | Penjelasan current owner/waiting/next action bersumber dari assignment dan kedudukan pekerjaan yang diterima; fakta tanggung jawab berbeda dari ringkasannya. |
| DC-010 Bukti | E + H | Bahan eksternal atau jejak internal terkait pernyataan/tindakan; klasifikasi authority dan versi tidak hilang. |
| DC-011 Data Domain Terstruktur | R | Informasi yang diterima dan maknanya disepakati menjadi sumber reuse. Kolom mentah belum otomatis R. |
| DC-012 Dokumen Hasil Generasi | O | Terhubung ke data/versi/varian sumber; tidak menciptakan fakta domain yang bersaing. |
| DC-013 Dokumen Eksternal | E | Referensi/unggahan dengan provenance dan hasil penilaian isi/authority bila ada. |
| DC-014 Bukti Final/Bertanda Tangan | E + H | Bahan dokumen dan jejak penetapan/penerimaan final atau signature; status final dan signature dinilai terpisah. |
| DC-015 Varian Dokumen | R + E | Rujukan bentuk/applicability lokal yang didasarkan pada authority eksternal ketika tervalidasi; varian historis belum otomatis diterima. |
| DC-016 Riwayat Revisi | H | Hubungan versi dan alasan sebelum/sesudah menjadi konteks record/keluaran. |
| DC-017 Bukti Audit | H | Jejak tindakan material yang terkait R/H/O/E; bukan pengganti bukti bisnis atau akses bebas. |
| DC-018 Asal-usul Sumber | H + E | Hubungan origin/locator/versi dan keputusan interpretasi atau penerimaan. |
| DC-019 Konteks Waktu Pelaporan | R | Dasar penjelasan cutoff/period/range; semantik yang dipilih mengikuti jenis hasil/policy yang berlaku. |
| DC-020 Kebijakan Berversi | E + R | Authority aturan dari sumber tervalidasi dibedakan dari penetapan applicability dan versi yang dirujuk record lokal. |
| DC-021 Kebutuhan | R | Konteks hasil/alasan unit yang dapat terkait usulan, tetap dibedakan dari paket. |
| DC-022 Usulan Pengadaan | R + H | Pengajuan dan riwayat pelengkapan/revisi/keputusan terkait mempunyai identitas asal. |
| DC-023 Referensi Perencanaan/RUP | E + R | Referensi/bukti RUP eksternal dan hubungan lokal ke usulan/paket; bukti publikasi/responsibility belum disahkan. |
| DC-024 Paket Pengadaan | R + H | Konteks paket dan riwayat tindakan/hasil/metode dengan asal yang dapat ditelusuri. |
| DC-025 Metode Pengadaan | E + R | Konteks applicability yang mengacu prosedur sahih; pemilihan keluarga awal P1 bukan policy semua metode. |
| DC-026 Spesifikasi/KAK | R + E | Data/rujukan lingkup yang diterima dan bahan eksternal yang terkait; field wajib belum dipilih. |
| DC-027 Konteks HPS | R + E | Data/bahan/rujukan estimate yang relevan; formula/penetap resmi masih GAP. |
| DC-028 Penyedia/Perusahaan | R + H | Identitas perusahaan berkelanjutan dan perubahan profilnya, terpisah dari hubungan paket. |
| DC-029 Kontak/PIC Penyedia | R + H | Hubungan kontak/wakil dengan perusahaan dan perubahan relevan; bukan perusahaan atau hak representasi otomatis. |
| DC-030 Profil Penyedia | D | Representasi informasi perusahaan/kontak/bukti yang berlaku; dasar R/H/E dan konteks validitas dapat ditelusuri. |
| DC-031 Bukti Kualifikasi | E + H | Bahan legal/kualifikasi, versi dan penilaian yang relevan; file tersedia bukan eligibility diterima. |
| DC-032 Partisipasi Penyedia | R + H | Hubungan perusahaan–paket, outcome/status dan jejaknya. Tidak menggandakan identitas perusahaan. |
| DC-033 Pengiriman/Penawaran | H + R + E | Fakta pengiriman dan isi terstruktur/bukti yang dikirim; hasil penerimaan/evaluasi tetap konsep berbeda. |
| DC-034 Revisi Penyedia | H + R + E | Hubungan pengiriman revisi dengan isi/bukti dan versi asal; bukan penimpaan diam-diam. |
| DC-035 Tindakan Penyedia | R + H + D | Tanggung jawab/hasil yang ditetapkan dan jejaknya dibedakan dari ringkasan pending/status. |
| DC-036 Evaluasi | H + R + E | Penelaahan/hasil dan dasar/bukti terkait; aturan penilaian resmi belum ditetapkan. |
| DC-037 Klarifikasi | H + E | Peristiwa/penjelasan dan bukti relevan; applicability per metode. |
| DC-038 Negosiasi | H + R + E | Peristiwa, hasil dan bukti; bukan pembenaran angka/rumus dari artefak historis. |
| DC-039 Hasil Pengadaan/Award | R + H + O + E | Keputusan/hasil yang diterima berbeda dari letter/report generated atau eksternal yang membuktikannya. |
| DC-040 SPPBJ | O + E + H | Keluaran/referensi dan jejak pekerjaan terhadap surat; penerbitan/signature bukan akibat otomatis hasil pengadaan. |
| DC-041 Kontrak/SPK | R + H + O + E | Data/hubungan kontrak diterima, riwayat perubahan, keluaran dan dokumen eksternal dibedakan. Authority klausul/varian masih GAP. |
| DC-042 Kegiatan/Event | R + H + E | Konteks jadwal/tujuan/peserta dan catatan pelaksanaan/hasil serta undangan/bukti terkait. |
| DC-043 Pelaksanaan/Progress | H + R + E | Fakta/progress yang diterima beserta peristiwa/bukti; hasil value/capitalization bukan turunan otomatis progress. |
| DC-044 Pemeriksaan | H + R + E | Peristiwa, hasil pemeriksaan dan bukti terkait; authority penerimaan/signature belum dipilih. |
| DC-045 Serah Terima | H + R | Konteks/peristiwa dan hasil yang dinilai sesuai dasar yang berlaku, berbeda dari dokumen BAST. |
| DC-046 BAST | O + E | Keluaran/bukti serah terima dengan asal, versi dan applicability; keberadaannya bukan record aset. |
| DC-047 Pelacakan SPJ | R + H + D + E | Fakta kelengkapan/status/bukti dan jejak dibedakan dari ringkasan kesiapan; penerimaan Finance belum diasumsikan. |
| DC-048 Pelacakan Pembayaran | R + H + E | Referensi/status/bukti pembayaran yang ditelusuri; bukan eksekusi transfer atau kebenaran ledger Finance. |
| DC-049 Keputusan Klasifikasi Downstream | R + H | Keputusan, applicability, dasar/evidence dan revisi diperlukan; isi keputusan aktual terblokir oleh GAP. |
| DC-050 Draft Downstream | S + D | Kandidat hilir menggunakan ulang data sumber; bukan R/H definitif sebelum dasar/penerimaan terpenuhi. |
| DC-051 Aset | R + D | Identitas/record diterima dibedakan dari representasi kini yang menjelaskan transaksi, posisi dan bukti. |
| DC-052 Asal Perolehan | R + E + H | Hubungan sumber/event perolehan, termasuk historis bila tersedia; tidak memaksa paket PRANATA untuk semua aset. |
| DC-053 Transaksi Aset | H | Catatan perolehan/perubahan/removal diterima yang menjelaskan lifecycle; kamus/accounting resmi masih GAP. |
| DC-054 Penempatan/Lokasi | R + H + D | Referensi tempat, riwayat penempatan diterima dan posisi pada konteks waktu dibedakan. |
| DC-055 Penanggung Jawab Aset | R + H | Assignment kustodian dan riwayatnya; bukan kepemilikan hukum atau permission. |
| DC-056 Verifikasi/Kondisi Fisik | H + R + E | Peristiwa pemeriksaan, hasil observasi diterima dan bukti kondisi/ketidaksesuaian. |
| DC-057 Pemeliharaan | H + E | Pekerjaan/bukti maintenance terkait aset; effect accounting menunggu authority. |
| DC-058 Koreksi Aset | H + R + E | Keputusan/perubahan dan dasar/bukti yang menjelaskan fakta sebelumnya; jurnal/period treatment belum dipilih. |
| DC-059 Pengembangan | H + R + E | Konteks perubahan dan evidence, dengan kemungkinan hubungan KDP; capitalization/depreciation effect terblokir. |
| DC-060 Reklasifikasi | H + R + E | Hubungan klasifikasi asal/tujuan dan alasan/bukti; bukan perolehan baru atau location-only mutation. |
| DC-061 Penghapusan/Removal | H + R + E | Tindakan/keputusan terhadap pencatatan/objek dan buktinya; catatan historis dibedakan dari tindakan disposal fisik. |
| DC-062 Kebijakan Penyusutan/Amortisasi | E + R | Policy/version/applicability yang menjelaskan value; isi authoritative instrument belum tersedia tervalidasi. |
| DC-063 Kebijakan Kapitalisasi | E + R | Policy/version/applicability klasifikasi; minima dari workbook tidak diangkat menjadi aturan. |
| DC-064 Nilai Buku/Pelaporan | D | Hasil dari record/transaksi dan policy/cutoff yang disahkan; perhitungan resmi tetap GAP-007/008/009. |
| DC-065 Item Persediaan | R | Identitas item untuk ledger persediaan; tidak mewakili aset tetap atau KDP. |
| DC-066 Transaksi Persediaan | H | Ledger diterima dengan jejak kode/asli dan konteks sumber; sign/effect menunggu kamus sahih. |
| DC-067 Versi Kamus Kode/Pemetaan | E + R + H | Authority kamus, keputusan pemetaan dan riwayat interpretasi berbeda; mapping aktual P01/P02 belum dipilih. |
| DC-068 Posisi Stok | D | Posisi dari ledger/semantik diterima pada lingkup/waktu terkait; saldo mandiri yang bersaing tidak diasumsikan. |
| DC-069 Hitung Fisik Persediaan | H + R + E | Peristiwa/observasi dan bukti dibandingkan dengan posisi ledger; keputusan adjustment terpisah. |
| DC-070 KDP | R + H + D + E | Record konteks KDP, bukti/progress/riwayat dan posisi value turunan dibedakan; aset definitif hasilnya adalah konsep lain. |
| DC-071 Kasus Rekonsiliasi | R + H | Lingkup, sumber pembanding, expected relationship, tanggung jawab dan jejak penerimaan/resolution. |
| DC-072 Selisih/Pengecualian | R + H + D | Perbedaan yang ditemukan, penjelasan/disposition dan riwayat tindak lanjut; hasil comparison turunan berbeda dari keputusan penerimaan. |
| DC-073 Sumber Import | S + E | Dataset asli berprovenance, dengan authority/currency/trust dan keterbatasan yang dinyatakan. |
| DC-074 Kandidat Import | S | Kandidat interpretasi/cleansing yang terkait sumber asli; penerimaan belum terjadi dengan keberadaan kandidat. |
| DC-075 Masalah Validasi | R + H | Fakta masalah/ambiguitas dan jejak keputusan terhadapnya, terkait sumber/kandidat/record yang relevan. |
| DC-076 Laporan | D + O + E | Hasil internal turunan/generasi atau laporan eksternal pembanding; record bisnis asal tetap dapat ditelusuri. |
| DC-077 Makna Siklus Hidup/Status | R + D | Kedudukan record yang diterima dan penjelasannya; tidak membuat status universal atau transition machine. |

## Hubungan organisasi, konteks dan tanggung jawab

Unit asal DC-001 menjelaskan **dari mana kebutuhan/usulan berasal**. Unit penanggung jawab menjelaskan **unit yang menanggung pekerjaan/objek pada konteks tertentu**. Keduanya boleh merujuk Unit Organisasi yang sama atau berbeda; jumlah, aturan perubahan, dan kewenangan hubungan itu belum dibekukan. Identitas unit dan hubungan historis diperlukan agar restrukturisasi/rename tidak menjadikan asal usulan/aset lama seolah berasal dari struktur baru. Active/inactive dan masa keberlakuan adalah **PROPOSED semantics**, bukan aturan penghapusan atau approval chain. Rujukan: [BR-001](BUSINESS_RULES.md#br-001), [BR-029](BUSINESS_RULES.md#br-029).

| Hubungan konseptual | Dasar / kualifikasi |
|---|---|
| Unit Organisasi ↔ induk/anak dan konteks keberlakuan | Arah generic OD-04; tidak menentukan root fakultas, jumlah level, jadwal resmi atau penyimpanan. Riwayat/validity model PROPOSED. |
| Referensi Akun ↔ Aktor Domain ↔ Penugasan Tanggung Jawab | Pemisahan PROPOSED yang mendukung actor/history P1; cardinality akun–wakil/perusahaan, eligibility dan representasi GAP-014/015. |
| Keluarga Peran Aplikasi, Konteks Ruang Kerja, Cakupan Organisasi, Penugasan, Tanggung Jawab Formal | Dimensi yang berbeda dan dapat menjelaskan tanggung jawab aktor; kombinasi bukan permission matrix. OD-09/20, GAP-003/014; [BR-002](BUSINESS_RULES.md#br-002), [INV-001](BUSINESS_RULES.md#inv-001), [INV-002](BUSINESS_RULES.md#inv-002). |
| Objek pekerjaan ↔ Konteks Pekerjaan/Tindakan ↔ assignment/bukti | Current owner/waiting/next action menjadi penjelasan domain dari konteks diterima; nilai belum diketahui/tidak berlaku dibedakan. OD-19, AC-05; [BR-003](BUSINESS_RULES.md#br-003). |

Tidak ada pemetaan yang menyimpulkan pengusul = approver, unit asal = unit penerima, Super Admin = penandatangan bisnis, atau pilihan workspace = authority. Formal responsibility PPK/KPA/Pokja/Pejabat Pengadaan/Tim Teknis/Pemeriksa/Penerima tetap memerlukan metode, delegation dan dasar yang valid, lalu penegakan P5. Konsep tanggung jawab penyiap, pemeriksa, penerbit dan signer dirujuk ke DOMAIN_RESPONSIBILITIES; model tidak memilih aktornya.

## Hubungan pengadaan dan penyedia

Semua hubungan berikut adalah **konseptual**, bukan urutan wajib. Hubungan jumlah kebutuhan–usulan–RUP–paket, penggabungan/pemisahan, serta kapan referensi/hasil berlaku belum diputuskan. Keberadaan istilah/event dalam sumber tidak menjadikan setiap konsep wajib di setiap metode.

| Hubungan | Makna hubungan / provenance | Authority dan aturan kanonik |
|---|---|---|
| Kebutuhan ↔ Usulan Pengadaan ↔ Referensi Perencanaan/RUP ↔ Paket | Asal dan bahan perlu ditelusuri; identitas kebutuhan, pengajuan, planning reference dan paket tetap berbeda. Tidak menentukan official handoff/publikasi. | OD-05/06; PR-F03/PROC-04/06/08/13; GAP-001/002; [BR-005](BUSINESS_RULES.md#br-005). |
| Paket ↔ Metode ↔ bahan Spesifikasi/KAK/HPS, kegiatan dan keluaran yang berlaku | Applicability menjadi bagian dari makna hubungan, dengan status tervalidasi/usulan/belum diketahui. Metode awal P1 Pengadaan Langsung/Tender bukan prosedur universal. | PR-F05/PROC-14–22/30; GAP-003/013/017/019; [BR-004](BUSINESS_RULES.md#br-004), [BR-010](BUSINESS_RULES.md#br-010). |
| Paket ↔ Kegiatan/Event ↔ peserta, jadwal, tujuan, undangan dan bukti hasil | Meeting/workshop/review/opening/clarification/negotiation adalah contoh kategoris. Kegiatan dapat mendukung objek lain, dengan konteks paket tetap terlacak. | OD-23, AC-08; PROC-23–27; GAP-003/017; [BR-006](BUSINESS_RULES.md#br-006). |
| Penyedia/Perusahaan ↔ Kontak/PIC ↔ Profil/Bukti Kualifikasi | Company-level identity/informasi berkelanjutan dapat digunakan ulang; validitas, representasi PIC dan versi bukti tetap konteks tersendiri. | OD-23, AC-09; PR-F04 Input!AU2:BX2; GAP-014/015/017; [BR-007](BUSINESS_RULES.md#br-007), [INV-003](BUSINESS_RULES.md#inv-003). |
| Penyedia ↔ Partisipasi ↔ Paket | Partisipasi merupakan hubungan company–package dan riwayat pekerjaan khusus paket. Pengulangan putaran, uniqueness partisipasi, onboarding dan eligibility belum ditentukan. | P1 CAP-07/AC-09/10; GAP-003/014/015/017; [BR-008](BUSINESS_RULES.md#br-008). |
| Partisipasi ↔ Pengiriman/Penawaran ↔ Revisi ↔ Tindakan/hasil/status | Pengiriman dan revisi mempunyai hubungan asal dan evidence/history; receipt/outcome yang sama dapat dijelaskan konsisten bagi penyedia dan operator terkait yang berwenang. Status receipt, accepted content, evaluation dan award adalah klaim yang berbeda. | Corrected approved AC-09/10; GAP-003/014/017; [BR-008](BUSINESS_RULES.md#br-008), [INV-004](BUSINESS_RULES.md#inv-004). |
| Pengiriman/penawaran ↔ Evaluasi/Klarifikasi/Negosiasi ↔ Hasil/Award | Hubungan informasi, kegiatan, dasar penelaahan dan bukti hasil hanya bila metode/prosedur yang berlaku menghendakinya. Tidak menetapkan scoring, kriteria, deadline, threshold atau urutan. | PR-F05/PROC-15–21/30; GAP-003/013/017/019; [BR-004](BUSINESS_RULES.md#br-004). |
| Paket/Hasil ↔ SPPBJ ↔ Kontrak/SPK | Hubungan sumber/hasil/dokumen dan tanggung jawab tersendiri; tidak menyatakan semua output berlaku atau menentukan siapa drafter/issuer/signer. | PR-F02/04, PROC-02/04/09–13/22/30; GAP-004/017; [BR-011](BUSINESS_RULES.md#br-011), [BR-012](BUSINESS_RULES.md#br-012). |
| Paket/Kontrak ↔ Pelaksanaan/Progress ↔ Pemeriksaan/Serah Terima ↔ BAST/SPJ/Payment Tracking | Objek/peristiwa, hasil yang dinilai, bukti keluaran dan tracking berkaitan tetapi tidak dipersamakan. Hubungan bukan syarat payment, treasury authority atau pemicu accounting. | PR-U05/PROC-02/04/30/32; ASSET-09; GAP-003/010/017–019; [BR-012](BUSINESS_RULES.md#br-012). |

**PROPOSED pola reuse penyedia:** partisipasi menggunakan referensi informasi perusahaan/bukti yang berlaku, dengan **snapshot/version reference** hanya bila perlu menjelaskan nilai yang berlaku pada konteks paket/pengiriman tertentu. Snapshot bukan perusahaan baru. Perubahan profil kini berbeda dari isi historis submission, dan kebutuhan pembaruan tidak berarti mengetik ulang informasi lain yang masih berlaku. Aturan validitas/version/applicability rinci tetap GAP-014/017. Visibility P5 memastikan hubungan data bersama tidak membuka perusahaan/penawaran/evaluasi pihak lain; model ini tidak membuat matriks akses.

SPPBJ mempertahankan empat makna tanggung jawab—penyiap, pemeriksa, penerbit, signer—tanpa universal mapping ke Pokja atau PPK. PR-F02 adalah perbedaan posisi drafting versus establishment/signature yang belum direkonsiliasi, bukan lisensi memilih salah satu source. Ejaan `SPBJ` dalam diagram dipertahankan sebagai locator istilah historis di glossary; output resmi tidak dinormalisasi dari ejaan itu.

## Data, dokumen, bukti dan versi

Data terstruktur, output generated, evidence uploaded, bukti final/signed dan riwayat versi mempunyai peran terpisah (DC-010–016). PR-F04 menunjukkan Input → Mail Merge → banyak keluarga output sebagai **struktur historis teramati**, bukan canonical field, formula, clause, numbering atau rule penerbitan. P2 mengambil makna reuse dari OD-08/23; tidak mengambil formula workbook.

| Hubungan | Kualifikasi |
|---|---|
| Data diterima ↔ versi sumber ↔ varian dokumen ↔ keluaran | Provenance menghubungkan output dengan data/rule/versi yang melahirkannya. Draft, versi superseded dan klaim final tidak dicampur. |
| Dokumen eksternal ↔ objek/tindakan ↔ penilaian evidence | Bahan yang diunggah dapat mendukung fakta, tetapi penilaian authority/penerimaan terpisah. Konflik isi dengan record memerlukan penjelasan/keputusan, bukan override diam-diam. |
| Bukti final/signed ↔ hasil/tanggung jawab yang berlaku | Signature, authenticity, penerbitan dan finality membutuhkan authority/applicability yang relevan; tidak dinyatakan lulus hanya dari label atau scan. |
| Revisi ↔ versi asal/baru ↔ reason/actor/time/evidence | Versi menjelaskan asal reuse dan hubungan perubahan dengan output/hilir. Pilihan mekanisme dan retention bukan P2. |

Rujukan kanonik: [BR-009](BUSINESS_RULES.md#br-009), [BR-010](BUSINESS_RULES.md#br-010), [INV-005](BUSINESS_RULES.md#inv-005), [INV-006](BUSINESS_RULES.md#inv-006). Ketergantungan GAP-013/017/019 tetap terbuka. Tanda tangan digital dan storage/document service eksternal adalah FUTURE sesuai P1, bukan prasyarat reuse V1. Nomor/format template, signer, klausul dan masa berlaku tidak dipilih.

## Hubungan pengadaan dengan aset, persediaan dan KDP

**PROPOSED pola bahan handoff** menghubungkan hasil perolehan/serah terima yang diperiksa dengan keputusan klasifikasi, draft hasil hilir, penanggung jawab pelengkapan dan bukti penerimaan menurut aturan yang kelak disahkan. Ini hubungan makna dan provenance, bukan sequence/transisi state atau pemicu otomatis. Dasar OD-03/23, CAP-10, AC-13/18; [BR-013](BUSINESS_RULES.md#br-013), [INV-012](BUSINESS_RULES.md#inv-012).

| Konsep handoff | Hubungan yang dapat dijelaskan | Yang masih BLOCKED_BY_GAP |
|---|---|---|
| Sumber perolehan, kontrak, progress, hasil pemeriksaan/serah terima dan bukti | Data yang dapat digunakan ulang mempunyai sumber/version yang diketahui; BAST adalah salah satu jenis bukti, berbeda dari keputusan pengakuan. | Event eligible, isi evidence wajib, responsibility dan boundary payment/acceptance: GAP-018 serta GAP-003/017/019. |
| Keputusan Klasifikasi Downstream DC-049 | Penjelasan jenis hasil yang sesuai—Asset, Persediaan, KDP, atau belum dapat ditentukan—dan dasar/applicability; tidak mengisi pilihan dari filename atau label akun. | Classification/recognition/threshold, comparator, historical applicability: GAP-008/009/018. |
| Draft Downstream DC-050 | Bahan awal yang menghubungkan data sumber terpilih dengan informasi hilir yang belum lengkap/belum diterima. | Approval hipotesis operasional, syarat pelengkapan/validasi, siapa bertanggung jawab dan acceptance record: GAP-008/009/014/018. |
| Record hilir yang diterima dan riwayat handoff | Hubungan asal dan penerimaan/exception membantu menjelaskan hasil; koreksi sumber/hilir tetap mempunyai konteks versi dan tindak lanjut. | Accounting effect, reversal, konflik/cutover authority dan correction responsibility: GAP-008/010/012/018. |

Tiga hasil hilir mempunyai makna berbeda. **Aset** berkaitan dengan identitas/register dan lifecycle. **Persediaan** berkaitan dengan item, ledger dan posisi stok. **KDP** mempertahankan konteks pekerjaan belum definitif serta hubungannya dengan progress/hasil penyelesaian. P2 tidak menyatakan setiap hasil pengadaan wajib menjadi salah satu record definitif, bahwa payment/BAST mengakibatkan kapitalisasi, atau bahwa pekerjaan yang belum selesai sudah menjadi aset hasil penyelesaian. Aturan kanonik: [INV-009](BUSINESS_RULES.md#inv-009), [INV-011](BUSINESS_RULES.md#inv-011), [INV-012](BUSINESS_RULES.md#inv-012).

## Aset: identitas, posisi kini dan riwayat

DC-051 mewakili identitas/record aset yang diterima; representasi kini dapat dijelaskan dari transaksi/peristiwa yang diterima (DC-053), asal perolehan (DC-052), penempatan/kustodian (DC-054/055), observasi fisik (DC-056), pemeliharaan dan perubahan terkait (DC-057–061). Model ini lifecycle/ledger-oriented sesuai bukti ASSET-11 dan P1, bukan hanya master statis. Rujukan [BR-014](BUSINESS_RULES.md#br-014), [BR-015](BUSINESS_RULES.md#br-015), [INV-008](BUSINESS_RULES.md#inv-008).

| Hubungan | Kualifikasi sumber/authority |
|---|---|
| Aset ↔ Asal Perolehan ↔ Transaksi/Bukti | ASSET-11 page 1 menunjukkan opening, purchase/acquisition, inbound transfer, grant, KDP/direct completion, reclassification, correction, development, outbound transfer dan removal. Ini contoh kategori teramati; kamus final dan dampak resmi GAP-008/011/013. |
| Aset ↔ riwayat penempatan ↔ posisi tempat/unit pada konteks waktu | Posisi kini adalah representasi dari penempatan yang diterima; record penempatan lama tetap menjelaskan posisi historis. Konflik waktu/sumber dan semantics movement belum dipilih: GAP-008/011/014. Tidak menetapkan last-write-wins. |
| Aset ↔ assignment kustodian ↔ tanggung jawab pada waktu tertentu | Custodian berbeda dari location, unit asal, legal owner dan approver. Pemilihan/pewarisan responsibility/permission GAP-014/018. |
| Aset ↔ verifikasi fisik/kondisi ↔ evidence dan maintenance | Kondisi fisik berasal dari observasi/pemeriksaan yang relevan, dengan jenis/kriteria yang perlu disahkan. Nilai accounting DC-064 menjelaskan hal berbeda. [INV-007](BUSINESS_RULES.md#inv-007). |
| Aset ↔ koreksi/pengembangan/reklasifikasi/removal ↔ before/after/evidence | Konsep dibedakan berdasarkan makna perubahan; alasan, source, time/reporting context dan responsibility dapat menjelaskan riwayat. Journal, depreciation effect dan closed-period treatment masih GAP-008/009; [BR-016](BUSINESS_RULES.md#br-016). |
| Aset/transaksi ↔ policy/version/cutoff ↔ nilai pelaporan | Policy penyusutan/amortisasi/kapitalisasi memberi dasar interpretasi saat tervalidasi; hasil belum boleh dianggap angka resmi saat authority tidak tersedia. GAP-007/008/009/013; [BR-017](BUSINESS_RULES.md#br-017), [BR-018](BUSINESS_RULES.md#br-018), [INV-013](BUSINESS_RULES.md#inv-013). |

P2 tidak mengesahkan condition scale, taxonomy transaksi lengkap, nilai minima, formula/rate, tanggal mulai, rounding, residual value atau daily proration. ASSET-12 engine/month-based arithmetic dan fallback missing dates tetap **LEGACY_IMPLEMENTATION_BEHAVIOR**. ASSET-10 capitalization table tetap **observasi historis**. Kepastian condition, penghapusan atau kelayakan pengakuan tidak disimpulkan dari dua sumber itu.

## Persediaan dan KDP

| Hubungan | Authority / ketidakpastian |
|---|---|
| Item Persediaan DC-065 ↔ Transaksi DC-066 ↔ versi kamus/pemetaan DC-067 ↔ Posisi Stok DC-068 | Posisi stok menjelaskan ledger yang diterima dengan lingkup/waktu relevan; makna saldo awal/penerimaan/pengeluaran/adjustment bukan formula kode yang sudah sahih. GAP-005/006/010/011; [BR-019](BUSINESS_RULES.md#br-019), [BR-020](BUSINESS_RULES.md#br-020), [INV-009](BUSINESS_RULES.md#inv-009). |
| Kode sumber mentah ↔ Source Provenance ↔ interpretation/mapping decision | Kode dan sumber historis tetap dapat dibandingkan dengan pemetaan berversi yang terpisah. Kamus/sign/effective date dan mapping aktual masih BLOCKED_BY_GAP. |
| Hitung Fisik DC-069 ↔ Posisi Stok ↔ Selisih/Kasus Rekonsiliasi | Observasi fisik dan hasil ledger berbeda; adjustment menjadi transaksi hanya menurut dasar/penerimaan yang divalidasi, bukan dari dugaan kode opname. |
| KDP DC-070 ↔ asal pengadaan/kontrak ↔ pelaksanaan/progress/bukti ↔ hasil penyelesaian/aset yang relevan | Identitas/konteks KDP mempertahankan pekerjaan belum definitif; dasar nilai, recognition, completion dan kapitalisasi belum dipilih. GAP-008/009/018; [BR-021](BUSINESS_RULES.md#br-021), [INV-011](BUSINESS_RULES.md#inv-011). |

Konflik **P01/P02** dilokasikan pada ASSET-01 `Sheet2` kolom K dan ASSET-02 kolom 11 versus ASSET-04 `Neraca Psd!E6/C44` dan ASSET-05 `Sheet1!F4`. Raw inventory menunjukkan P01 dan konteks opname; heading laporan/journal menunjukkan P02. Model kamus/mapping dapat diusulkan, tetapi pilihan apakah alias, versi, typo atau operasi berbeda belum dibuat. [INV-010](BUSINESS_RULES.md#inv-010) memiliki rumusan keselamatan; GAP-005/006 tetap OPEN. Angka transaksi dan identitas sumber tidak disalin.

## Pelaporan, rekonsiliasi dan intake historis

| Hubungan | Makna / batas |
|---|---|
| Record/transaksi ↔ policy/version ↔ Konteks Waktu Pelaporan ↔ Laporan | Laporan merupakan representasi/keluaran atas lingkup dan waktu yang dapat dijelaskan. Position/as-of berbeda dari transaction range; monthly/quarter/semester/annual/custom hanya bila semantik applicable disahkan. [BR-022](BUSINESS_RULES.md#br-022), [INV-013](BUSINESS_RULES.md#inv-013). |
| Kasus Rekonsiliasi ↔ sumber A/B, expected relationship, lingkup dan cutoff ↔ Selisih/Pengecualian | Dasar perbandingan, responsibility/waiting, evidence, penjelasan/disposition dan acceptance menjelaskan hasil kasus. Bukan equality formula atau signoff Finance yang diinventaris dari judul workbook. GAP-010/012; [BR-023](BUSINESS_RULES.md#br-023), [INV-014](BUSINESS_RULES.md#inv-014). |
| Sumber Import ↔ kandidat ↔ Masalah Validasi ↔ keputusan interpretasi/penerimaan ↔ record/history tujuan | Kandidat mempertahankan link ke source/version dan keputusan perubahan/rejection/acceptance. Duplicate/identity/cleansing/trust dan source precedence belum dipilih. Tidak mendefinisikan staging table atau ordered import workflow. GAP-011/012/013/019; [BR-024](BUSINESS_RULES.md#br-024), [INV-015](BUSINESS_RULES.md#inv-015), [INV-016](BUSINESS_RULES.md#inv-016). |
| Kebijakan Berversi ↔ record/transaksi historis ↔ interpretasi/report | Rule/version yang berlaku dapat menjelaskan hasil historis, termasuk kamus kode, accounting/capitalization, method applicability, classification dan variant. Tidak mengisi actual effective dates atau policy terbaru secara retrospektif. [BR-025](BUSINESS_RULES.md#br-025), [INV-018](BUSINESS_RULES.md#inv-018). |

BMU di DC-076 adalah kebutuhan laporan eksplisit Owner/P1; corpus belum memberi definisi/format resminya. Neraca, laporan aset/mutasi/persediaan dan penyusutan teramati melalui ASSET-03/04/11, dengan temporal meaning/form/cross-report equality yang tetap perlu authority. ASSET-04 `Intra` dan `Intra New`, repeated sheet variants ASSET-08 dan duplicate location headings ASSET-01/02 menunjukkan persoalan provenance/precedence; model tidak memilih versi pemenang. ASSET-06/07/08 menunjukkan recap/reconciliation/audit-support structure, bukan official signoff. GAP-007–013 tetap controlling.

Kredensial sensitif sumber ASSET-10 bukan kandidat data akun aktif. Tidak ada nilai password, personal/provider record, transaksi keuangan, atau external-workbook target path disalin dalam model. Structured/source evidence yang aman digunakan sebagai locator; raw tetap ignored. Tidak ada aktivitas import/cutover atau akses production.

## Identitas dan keunikan konseptual

Rujukan kanonik [BR-029](BUSINESS_RULES.md#br-029). Identitas diperlukan untuk menunjuk **konsep yang sama di berbagai konteks dan waktu**. Nama, nomor, code dan filename merupakan kandidat label/evidence, bukan aturan unik atau format identifier yang disahkan. Pemodelan tidak menentukan primary/foreign keys, unique constraints, panjang kode, sequence, checksum atau label/barcode.

| Konsep | Identitas yang perlu dapat dibedakan | Ketidakpastian yang dipertahankan |
|---|---|---|
| Unit Organisasi DC-001 | Unit yang sama versus nama/struktur/parent pada waktu berbeda; asal historis versus unit tanggung jawab kini. | Rename, merger/split, validity dates dan hubungan legacy–new unit belum ditetapkan; GAP-001/011/014. |
| Aktor/Akun/Assignment DC-002/004/005 | Identitas akun, pihak bisnis dan penugasan proses/tanggung jawab berbeda. | Multi-actor/account relationships, provisioning, representation/delegation dan uniqueness akun P5/GAP-014/015. |
| Penyedia/Perusahaan, PIC DC-028/029 | Perusahaan berkelanjutan, kontak/wakilnya dan perubahan profil/bukti; bukan perusahaan per paket. | Nomor legal/tax/name tidak ditetapkan sebagai aturan unik; merging duplicate identities/representation GAP-014/015/017. |
| Usulan/RUP/Paket DC-022/023/024 | Pengajuan asal, rujukan planning eksternal dan pekerjaan paket; identity tetap terkait walau bahan/revisi berubah. | Official numbering dan multiplicity/split/consolidation belum dipilih; GAP-001/002/017. |
| Partisipasi/Pengiriman/Revisi DC-032/033/034 | Hubungan perusahaan–paket, peristiwa pengiriman dan versi revisi berbeda; status/receipt yang sama merujuk pengiriman yang sama. | Putaran, replacement/cancellation, uniqueness participation dan official deadlines GAP-003/014/017. |
| Kontrak/SPK DC-041 | Konteks agreement, dokumentasi final dan perubahan/addendum terkait tidak dipersamakan hanya dari nomor file. | Nomor/variant/authority dan hubungan contract multiplicity GAP-017/019. |
| Aset DC-051 | Objek/register diterima berbeda dari row recap, label kategori, transaksi dan representasi current/historical. | Legacy code/item/date kombinasi belum disahkan untuk uniqueness/deduplication; unit of registration/component treatment GAP-008/009/011. |
| Item Persediaan DC-065 | Item ledger, lokasi/lingkup, transaksi dan kode jenis transaksi berbeda. | Identitas per item/batch/unit/location, duplicate location semantics dan equivalence across exports GAP-005/006/011. |
| KDP DC-070 | Konteks pekerjaan/nilai KDP, bukti/progress, hasil penyelesaian dan aset hasilnya mempunyai identitas/links berbeda. | Aggregation/split into asset, recognition and historical mappings GAP-008/009/011/018. |
| Sumber/Kandidat DC-073/074 | Original dataset/version/locator, candidate interpretation dan target diterima berbeda; satu label file tidak menjamin record sama. | Currency/trust, file equivalence, duplicate handling and cutover precedence GAP-011/012/013/019. |
| Dokumen/Bukti/Kasus/Laporan DC-010–016/071/076 | Source/version, artefak output, context final/signed, comparison case dan report run/period dapat dirujuk tanpa conflation. | Official numbering, acceptance/signoff, retention/privacy rules GAP-010/013/017/019; mekanisme P4/P5/P9/P10. |

## Siklus hidup, sejarah dan audit

DC-077 menyediakan bahasa kedudukan record; semantik create/edit/cancel/supersede/archive/restore/delete/anonymize per area dimiliki [DOMAIN_LIFECYCLES](DOMAIN_LIFECYCLES.md). [BR-026](BUSINESS_RULES.md#br-026), [INV-017](BUSINESS_RULES.md#inv-017) memiliki batas riwayat/audit. Tidak ada transition trigger/guard, retention years, permission matrix atau soft-delete mechanism dipilih di model ini.

Revision/correction, cancellation, supersession, archival dan deletion memiliki makna yang berbeda. Auditability menghubungkan actor/responsibility, waktu, organization/context, origin, alasan, related evidence dan before/after bila applicable; mekanisme teknis dan privacy akses tetap P4/P5/P9. Pending/cancelled/superseded tidak membuat history-bearing record hilang atau evidence menjadi tidak terlacak. Detail legal retention/anonymization dan cara restore tetap membutuhkan authority; konsep tidak memberi izin penghapusan produksi.

## Area yang belum dapat ditetapkan dan handoff fase

| Area unresolved | GAP / dasar | Yang dapat dipertahankan dalam P2 | Handoff saat fase diotorisasi |
|---|---|---|---|
| Unit–central approval, RUP ownership/publication, method applicability dan responsibility | GAP-001/002/003/013; OD-05/06, PR-F01/03/05 | Identity, context, provenance dan pemisahan responsibility; bukan procedure resmi. | Validasi custodian/Owner; P3 urutan/transisi, P5 enforcement. |
| SPPBJ, varian/klausul/nomor/signature dan field/formula consultancy | GAP-004/017/019; PR-F02/04/05 | Pemisahan drafter/checker/issuer/signer dan output/evidence dari data; source conflict tercatat. | Domain validation; P3 applicable documents, P5 authority, P6 calculation/technical contracts kelak. |
| Kamus transaksi Persediaan dan P01/P02 | GAP-005/006; AS inventory discrepancy | Raw provenance, ledger identity, versioned dictionary/mapping concept; mapping aktual terblokir. | Custodian Inventory/Finance; P3 proses, P6 interpretasi/perhitungan, P10 historical handling. |
| Depreciation/cutoff, capitalization, correction/development/reclassification/KDP | GAP-007/008/009/013; ASSET-03/10/11/12 | Konsep policy/version/cutoff, transaction history dan derived value; tanpa formula/journal/threshold. | Accounting instrument/examples; P3 lifecycle, P6 calculations, P8/P10 accepted examples. |
| Downstream eligibility/trigger/owner dan reconciliation signoff | GAP-010/018; OD-23, PR-U05, ASSET-04/06/09 | Classified draft hypothesis, evidence/exception/responsibility context; bukan definitive outcome otomatis. | Validasi Procurement/Asset/Inventory/Finance; P3 procedures, P5 authority, P6 consistency, P10 acceptance. |
| Historical identity/trust/source precedence dan cutover | GAP-011/012/013/019 | Source/candidate/issues/acceptance distinctions, original values and decisions traceable. | P4/P6/P8/P10 intake/migration validation; P9/P10 cutover ownership. |
| Compact roles/assignments dan account contracts | GAP-014/015; OD-09/11/20 | Dimensi tanggung jawab terpisah dan account reference lokal; candidate family tetap belum frozen. | P5 final roles/permissions/accounts; P9 recovery/channel operations. |
| Workload/capacity | GAP-016; OD-16/22 | DC tidak menentukan architecture/performance; skala P1 tetap konteks produk. | P4/P6/P8/P9 representative workload/capacity; bukan benchmark P2. |
| Facility borrowing | GAP-020; P1 FUT-04 | Tidak ditambahkan sebagai capability/domain V1; penggunaan kata BAST dalam sumber borrowing tidak mengubah scope. | Keputusan Owner terpisah bila relevan; [BR-028](BUSINESS_RULES.md#br-028). |

Integrasi SiRUP/SPSE/LPSE/Finance/HR/SSO/LDAP/UNY API dan digital signature tetap FUTURE/reference-only menurut P1/OD-12/13; model hubungan referensi/evidence lokal tidak merancang integrasi atau meniadakan kewajiban resmi di kanal eksternal yang berlaku. [BR-027](BUSINESS_RULES.md#br-027) memiliki batasnya.

**Hasil P2 model:** bahasa/hubungan/classification disetujui APPR-003 dengan kualifikasinya; GAP yang memblokir aturan resmi tetap OPEN. Exact next safe action setelah checkpoint terverifikasi: Owner separately authorizes P3 — Workflows, Routes & Interactions. P3 tetap NOT AUTHORIZED; stop after P2.
