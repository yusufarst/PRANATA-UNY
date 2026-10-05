# Domain lifecycle semantics — PRANATA UNY

Status: APPROVED | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P2 | Preparation: [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006), completed | Approval: [APPR-003](../00-governance/APPROVAL_RECORDS.md#appr-003) | Checkpoint: [AUTH-007](../00-governance/APPROVAL_RECORDS.md#auth-007), automatically completed after verified publication

Dokumen ini memiliki arti lifecycle/disposisi konseptual. Istilah/definisi konsep dimiliki [DOMAIN_GLOSSARY](DOMAIN_GLOSSARY.md); hubungan, klasifikasi kebenaran dan identitas konseptual dimiliki [DOMAIN_MODEL](DOMAIN_MODEL.md). Rumusan kewajiban riwayat/koreksi/retensi dimiliki [BR-026](BUSINESS_RULES.md#br-026), [INV-017](BUSINESS_RULES.md#inv-017) dan aturan terkait dalam [BUSINESS_RULES](BUSINESS_RULES.md). Kata status di sini adalah label makna kandidat; tidak menetapkan state aplikasi, urutan, transition guards atau izin. P2 DONE — APPROVED under APPR-003 dengan semua kualifikasi disposition tetap berlaku; P3–P11 dan implementasi NOT AUTHORIZED.

## Kosakata lifecycle generik

| Label makna kandidat | Arti domain | Batas |
|---|---|---|
| DRAFT / calon | Bahan kerja/calon yang belum memperoleh penerimaan atau finalitas yang disyaratkan domainnya. | Bukan angka/hasil resmi atau bukti bahwa penetapan final pasti terjadi. |
| ACTIVE / berlaku | Masih relevan untuk penggunaan yang didefinisikan dalam konteksnya. | Keberlakuan profil, unit, kebijakan dan penugasan mempunyai makna berbeda. |
| INACTIVE / tidak berlaku untuk pekerjaan baru | Konteks tidak lagi dipakai untuk tujuan baru tertentu. | Belum menentukan pemicu, hak memakai record lama, atau aturan tanggal. |
| COMPLETED / selesai dalam lingkupnya | Pekerjaan atau hasil terkait memenuhi makna penyelesaian yang nanti divalidasi. | Penyelesaian pengadaan, pemeriksaan, KDP, pembayaran dan rekonsiliasi tidak saling menggantikan. |
| SUPERSEDED / digantikan | Ada versi/record pengganti dengan hubungan asal yang dapat dijelaskan. | Berbeda dari menghapus versi lama atau membatalkan semua akibatnya. |
| CANCELLED / dibatalkan | Pekerjaan/hasil dinyatakan dibatalkan dalam lingkup tertentu. | Makna dan dampak domain, pihak berwenang dan akibat historis tetap menunggu aturan. |
| ARCHIVED / diarsipkan | Record berada dalam konteks penyimpanan/akses riwayat. | Bukan pembatalan, penghapusan atau persetujuan otomatis atas record. |
| DEFINITIVE / diterima/final dalam lingkup tertentu | Klaim domain telah melewati penerimaan yang memang disyaratkan. | Status dokumen bertanda tangan, record diterima dan periode final adalah klaim berbeda; GAP terkait tetap mengendalikan. |

DC-077 memiliki konsep makna status; setiap domain hanya menggunakan makna yang relevan. Tidak semua label berlaku pada semua konsep. Hubungan kandidat dalam OD-06 bukan lifecycle resmi untuk semua metode.

## Makna lifecycle per area utama

| Area / konsep | Makna yang perlu dijaga | Riwayat dan batas finalitas | Aturan / kewajiban berikutnya |
|---|---|---|---|
| Unit Organisasi — DC-001 | Unit dapat tetap dikenal secara historis ketika struktur/nama/relevansinya berubah. Unit asal dan unit penanggung jawab mempunyai konteks waktu sendiri. | Makna aktif/inaktif kandidat; restrukturisasi bukan penggantian asal lama dengan asal baru tanpa penjelasan. | BR-001; GAP-001/014; P4/P5 menetapkan representasi/cakupan kelak. |
| Penyedia/profil/PIC — DC-028–031 | Relevansi perusahaan, kontak, profil dan bukti kualifikasi dapat berbeda menurut waktu dan konteks. | Perubahan profil tidak otomatis mengubah representasi pada partisipasi historis; masa berlaku resmi belum ditentukan. | BR-007; INV-003; GAP-014/015/017. |
| Kebutuhan/usulan/RUP/paket — DC-021–027 | Bahan kebutuhan/usulan, referensi RUP dan pekerjaan paket memiliki konteks/klaim penyelesaian berbeda. | Revisi, pembatalan atau penggantian memerlukan hubungan asal. Finalitas salah satu bagian belum menetapkan finalitas bagian lain. | BR-004/005; GAP-001–004/013/017; P3 memilih prosedur yang tervalidasi. |
| Partisipasi/pengiriman/revisi/tindakan penyedia — DC-032–035 | Partisipasi terkait perusahaan dan paket tertentu; penerimaan kiriman, revisi dan hasil penilaian mempunyai arti berbeda. | Riwayat pengiriman/revisi terkait konteks dan hasil yang sama; keputusan visibilitas tetap P5. Penyelesaian tindakan belum membuktikan eligibility/award. | BR-008; INV-004; GAP-003/014/017; AC-09/10. |
| Kontrak/SPK/pelaksanaan — DC-041/043/047/048 | Dokumen kontrak dan bukti progres, kelengkapan SPJ serta status pembayaran menjelaskan klaim yang berbeda. | Perubahan/addendum perlu konteks versi dan bukti. SPJ lengkap atau payment tracking bukan instruksi pencairan. | BR-012; GAP-003/010/017/018/019; P3. |
| Pemeriksaan/serah terima/BAST — DC-044–046 | Hasil pemeriksaan, penerimaan dan dokumen BAST terhubung namun memiliki arti berbeda. | Finalitas bukti tidak langsung menetapkan jenis record downstream atau pengakuan akuntansi. | BR-012/013; INV-012; GAP-018. |
| Keputusan klasifikasi/draft downstream — DC-049/050 | Calon pencatatan memperoleh konteks jenis tujuan dan sisa pelengkapan yang diperlukan. | Makna draft dibedakan dari penerimaan record hilir; titik penerimaan/pemicu resmi belum dipilih. | BR-013; INV-006/012; GAP-008/009/018; AC-13. |
| Aset/transaksi/penempatan/kondisi — DC-051–064 | Posisi saat ini berkaitan dengan peristiwa/transaksi yang diterima dan observasi kondisi terkait. Koreksi, pengembangan, reklasifikasi dan removal mempunyai makna terpisah. | Status pemakaian, kondisi fisik, nilai pelaporan dan removal tidak saling menentukan. Dampak akuntansi serta periode koreksi tetap belum ditetapkan. | BR-014–018; INV-007/008; GAP-007/008/009/018; AC-14–16. |
| Item/ledger Persediaan — DC-065–069 | Transaksi, hitung fisik dan posisi stok adalah klaim berbeda dalam ledger Persediaan. | Penggantian kamus/mapping tidak menghapus konteks kode historis. Finalitas transaksi/posisi menunggu semantik kamus yang disetujui. | BR-019/020; INV-009/010; GAP-005/006/011; AC-17. |
| KDP — DC-070 | Konteks pekerjaan belum selesai dan hasil penyelesaian mempunyai makna berbeda dari register aset definitif. | Penyelesaian, perubahan nilai dan pengalihan yang berlaku perlu bukti/policy, tidak dipilih dari label kontrak/progres. | BR-021; INV-011; GAP-008/009/018; AC-18. |
| Kasus rekonsiliasi/selisih — DC-071/072 | Kasus mempunyai lingkup perbandingan, pihak tindak lanjut, penjelasan selisih dan konteks penerimaan hasil bila berlaku. | Dicatatnya penjelasan, penanganan selisih dan signoff resmi berbeda; cadence/period closure menunggu authority. | BR-023; INV-014; GAP-010/012; AC-20. |
| Sumber/kandidat import/masalah — DC-073–075 | Kandidat, keputusan cleansing, penolakan dan record yang diterima mempunyai status kebenaran berbeda. | Asal, masalah, alasan keputusan dan nilai historis membentuk konteks penerimaan; identitas/duplikasi dan cutover belum final. | BR-024/029; INV-015/016; GAP-011/012/013/019; AC-19. |
| Data terstruktur/dokumen/varian/revisi — DC-010–018 | Klaim data sumber, keluaran generasi, bukti eksternal, dokumen final dan versi revisi dibedakan. | Keluaran lama dapat digantikan dengan hubungan versi; finalitas/tanda tangan dan numbering menunggu aturan varian. Bukti tersimpan belum otomatis mengesahkan isi. | BR-009/010/026; INV-005/006; GAP-017/019; AC-11/22. |
| Kebijakan/kamus/konteks waktu/laporan — DC-019/020/062/063/067/076 | Keberlakuan aturan menurut waktu dan cakupan menjelaskan keluaran pada cutoff/rentang tertentu. | Penggantian policy tidak menetapkan tanggal efektif nyata. Penyajian laporan bukan penerimaan periode; format/angka resmi menunggu authority. | BR-017/018/020/022/025; INV-013/018; GAP-005–010/013; AC-16/20. |

## Disposisi konseptual setiap konsep penting

[AICWDF v4.3 §13](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#13-p2--domain-model--business-rules) meminta cakupan create, edit, archive, restore, soft-delete, hard-delete dan anonymization. AUTH-006 membatasi P2 pada arti domain; kontrak eksekusi, retensi/keamanan, mekanisme penyimpanan dan cutover tetap milik P3/P4/P5/P9/P10 setelah otorisasi terpisah. Matriks ini mencakup seluruh DC-001–DC-077, termasuk kelompok konsep pendukung. Semua **P** adalah PROPOSED disposition untuk validasi, **B** berarti belum dapat ditetapkan, dan **R** berarti konsep rekonstruksi/keluaran turunan. Tidak ada sel yang memberi izin atau kesiapan implementasi.

| Kode | Arti pada matriks |
|---|---|
| P-bahan | Pembuatan/pelengkapan kandidat relevan secara semantik; penerimaan resmi dan pelakunya belum ditetapkan. |
| P-versi | Perubahan melalui konsep revisi/penggantian atau koreksi yang dapat dijelaskan; perlakuan tepat belum dipilih. |
| P-riwayat | Pengarsipan secara konseptual mempertahankan konteks bukti dan asal; syarat/retensi belum ditetapkan. |
| B-pemulihan | Pemulihan akses/record/arsip belum disahkan; pemulihan bukan aktivasi, finalitas atau pemunduran proses otomatis. |
| B-retensi | Soft-delete/hard-delete belum disahkan untuk record yang relevan; kebijakan retensi dan dampak referensi harus diputuskan P5/P9/P10. |
| B-privasi | Anonymization belum disahkan atas record sumber; kebutuhan privasi dan keutuhan bukti harus divalidasi P5/P9/P10. |
| B-domain | Disposisi membutuhkan aturan/kewenangan domain yang belum tersedia; GAP pada baris mengendalikan. |
| R | Rekonstruksi/generasi keluaran berdasarkan sumber yang diterima; edit manual sebagai kebenaran terpisah belum ditetapkan. |

Untuk sumber historis, bukti eksternal dan bukti final/bertanda tangan, **P-versi hanya berarti koreksi konteks/interpretasi yang ditelusuri atau penambahan versi bukti baru yang terhubung**. Ini tidak berarti mengedit isi original, menimpa bukti final, atau mengubah raw source di `reference-inputs/`; original tetap read-only dan hubungan versi asal tetap dipertahankan. Untuk representasi turunan, perubahan dijelaskan melalui sumber/riwayat yang mendasarinya, bukan edit manual nilai keluaran sebagai kebenaran baru.

| Konsep (cakupan ID) | Create | Edit | Archive | Restore | Soft-delete | Hard-delete | Anonymize | Batas utama |
|---|---|---|---|---|---|---|---|---|
| Unit Organisasi (DC-001) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-001/014; konteks organisasi historis. |
| Akun/aktor (DC-002/004) | B-domain | B-domain | B-domain | B-domain | B-retensi | B-retensi | B-privasi | GAP-014/015; account lifecycle P5/P9. |
| Workspace/peran/cakupan/tanggung jawab formal (DC-003/006–008) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-014; tidak memberi kemampuan baru. |
| Penugasan/konteks pekerjaan/status (DC-005/009/077) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-001–004/010/014; status dan penugasan resmi P3/P5. |
| Bukti eksternal/final/revisi/audit/provenance (DC-010/013/014/016–018) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-013/017/019; identitas bukti/revisi terdahulu. |
| Data terstruktur/keluaran generasi/varian (DC-011/012/015) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-017/019; final/signature B-domain. |
| Konteks waktu/kebijakan (DC-019/020/062/063/067) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-005–009/013/017; actual policy version/effective date B-domain. |
| Kebutuhan/usulan/RUP/paket/metode/KAK/HPS (DC-021–027) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-001–003/013/017; penerimaan/batal B-domain. |
| Perusahaan/PIC/kualifikasi (DC-028/029/031) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-014/015/017; kelayakan/validity B-domain. |
| Partisipasi/kiriman/revisi/tindakan penyedia (DC-032–035) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-003/014/017; diterima/ditolak/batal B-domain. |
| Evaluasi/klarifikasi/negosiasi/award/SPPBJ (DC-036–040) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-003/004/017; keputusan/issuance B-domain. |
| Kontrak/event/progress/pemeriksaan/serah terima/BAST/SPJ/payment tracking (DC-041–048) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-003/010/017–019; finalitas B-domain. |
| Keputusan klasifikasi/draft downstream (DC-049/050) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-008/009/018; klasifikasi/penerimaan B-domain. |
| Aset/asal/transaksi/penempatan/kustodian/kondisi/maintenance/koreksi/development/reclass/removal (DC-051–061) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-008/009/014/018; posting/akibat resmi B-domain. |
| Profil penyedia/nilai pelaporan/posisi stok/laporan (DC-030/064/068/076) | R | R | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-005–010/013/014/017; validity/kalkulasi/finalitas B-domain. |
| Item/transaksi/hitung fisik Persediaan (DC-065/066/069) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-005/006/011; makna kode/ledger B-domain. |
| KDP (DC-070) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-008/009/018; nilai/penyelesaian B-domain. |
| Kasus rekonsiliasi/selisih (DC-071/072) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-010/012; penerimaan/period closure B-domain. |
| Sumber/kandidat/masalah import (DC-073–075) | P-bahan | P-versi | P-riwayat | B-pemulihan | B-retensi | B-retensi | B-privasi | GAP-011/012/013/019; penerimaan/write authority B-domain. |

Istilah soft-delete dan hard-delete dicatat untuk kelengkapan keputusan lifecycle; tidak memilih teknik implementasi. Durasi simpan, pemusnahan sah, restore, anonymization, pembatalan dan koreksi terhadap periode terdahulu **belum mempunyai aturan eksekusi**. Safe boundary mengikuti BR-026/INV-017 dan [PRODUCTION_DATA_SAFETY](../00-governance/PRODUCTION_DATA_SAFETY.md); executor kelak tidak boleh memperlakukan sel PROPOSED/B sebagai izin.

## Perbedaan tindakan terhadap riwayat

| Disposisi | Pertanyaan semantik yang dijawab | Yang masih perlu validasi |
|---|---|---|
| Koreksi | Klaim/nilai apa yang diperbaiki dan bagaimana perubahan dijelaskan? | Efek akuntansi, reversal dan periode historis GAP-008. |
| Pembatalan | Dalam lingkup apa pekerjaan/hasil dibatalkan? | Kewenangan, akibat pada tanggungan/hasil/bukti dan aturan tiap metode GAP-001–004/017/018. |
| Penggantian/supersession | Versi mana menggantikan versi sebelumnya dan pada konteks apa? | Nomor/versi final, source precedence, applicable policy GAP-013/017/019. |
| Pengarsipan | Bagaimana record tetap mempunyai konteks bukti historis? | Ketersediaan, masa simpan dan akses P5/P9/P10. |
| Pemulihan | Klaim apa yang dapat dikembalikan tanpa mengaburkan perubahan yang sudah terjadi? | Restore policy, authority dan reconciliation P3/P5/P9/P10. |
| Penghapusan/anonymization | Bagian informasi mana boleh dihilangkan atau diubah identitasnya secara sah? | Kewajiban retensi/privasi, konsekuensi bukti dan izin P5/P9/P10. Penghapusan aset secara bisnis (DC-061) berbeda dari penghapusan record. |

[DOMAIN_RESPONSIBILITIES](DOMAIN_RESPONSIBILITIES.md) menjelaskan pelaku/tanggung jawab yang perlu ditelusuri; [DOMAIN_TRACEABILITY](DOMAIN_TRACEABILITY.md) memiliki dependent GAP dan acceptance. Penentuan tepat kapan, siapa, dengan guards apa, dan bagaimana disposition dilakukan merupakan kewajiban fase berikutnya, belum diotorisasi oleh kandidat ini.
