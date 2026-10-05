# Workflow contracts — PRANATA UNY

Status: APPROVED (APPR-004) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P3 | Preparation: [AUTH-008](../00-governance/APPROVAL_RECORDS.md#auth-008) (HISTORICAL / COMPLETED) | Approval/checkpoint: [APPR-004](../00-governance/APPROVAL_RECORDS.md#appr-004) / [AUTH-009](../00-governance/APPROVAL_RECORDS.md#auth-009)

Dokumen ini memiliki **tujuan, batas, pemicu, prasyarat, bukti, penyelesaian dan handoff WF-001–WF-028**. [WORKFLOW_CATALOG](WORKFLOW_CATALOG.md) adalah indeks. [STATE_TRANSITIONS](STATE_TRANSITIONS.md) memiliki satu-satunya rumusan state dan tindakan/transisi TR; tabel yang ditautkan pada setiap kontrak adalah bagian kontrak tersebut, bukan salinan state machine. [ROUTE_CONTRACTS](ROUTE_CONTRACTS.md) memiliki tujuan navigasi RT, [INTERACTION_CONTRACTS](INTERACTION_CONTRACTS.md) hasil interaksi IX, dan [EXCEPTION_REVISION_PATTERNS](EXCEPTION_REVISION_PATTERNS.md) pola EP.

Makna DC, tanggung jawab dan aturan tetap dimiliki P2: [DOMAIN_GLOSSARY](../02-domain/DOMAIN_GLOSSARY.md), [DOMAIN_RESPONSIBILITIES](../02-domain/DOMAIN_RESPONSIBILITIES.md), dan [BUSINESS_RULES](../02-domain/BUSINESS_RULES.md). ID di sini bukan Tasks, enum, route framework atau izin aplikasi. P3 APPROVED under APPR-004 dengan semua kualifikasi tetap; P4–P11 belum diotorisasi, freeze NOT REACHED, Tasks NONE, execution NOT AUTHORIZED, implementasi NONE.

## Cara membaca dan kontrak lintas workflow

Authority klasifikasi P3 dan batasnya dirujuk ke [WORKFLOW_CATALOG](WORKFLOW_CATALOG.md). `APPROVED_PRODUCT_FLOW` berarti hasil/interaksi yang diminta P1/Owner; hanya bagian tersebut mempunyai dasar persetujuan. `APPROVED_DOMAIN_INVARIANT` menunjuk batas yang sudah disetujui P2. Rangkaian lokal baru tetap `PROPOSED_WORKFLOW`; `EVIDENCE_SUPPORTED` membuktikan keberadaan konsep; `BLOCKED_BY_GAP` tidak boleh ditafsirkan sebagai urutan resmi yang sudah tersedia. Satu workflow dapat mempunyai beberapa klasifikasi dengan lingkup berbeda.

Semua pekerjaan operasional mempunyai konteks asal, state yang dirujuk, penanggung jawab saat ini, pihak yang ditunggu atau alasan tidak ada, tindakan berikut yang berlaku, bukti yang masih diperlukan dan alasan revisi/blocked. Tenggat hanya dicatat bila sumber/applicability sahih, tanpa SLA buatan. Nama pihak pada kontrak adalah **tipe tanggung jawab P2**, bukan pemetaan jabatan/peran atau pemberian izin. Jika aktor aktual belum tervalidasi, tampilkan fungsi belum dipetakan dan GAP; pengelola sumber/kebijakan perlu menyediakan keputusan. Jangan memberi tugas resmi kepada aktor tebakan.

Revisi mempertahankan identitas pekerjaan dan hubungan versi, alasan serta bukti. Hasil lama/final tidak ditimpa ketika sumber berubah. Penolakan/pembatalan resmi, supersession material dan efek hilir hanya boleh mengikuti authority yang berlaku; kontrak dapat menerima bukti keputusan eksternal yang tervalidasi. Menghentikan bahan kerja lokal tidak menghapus record material atau berarti membatalkan pengadaan/aset. Detail disposition P2 dan BR-026/INV-017 tetap berlaku. EP-001–EP-010 menyediakan perilaku kegagalan yang dirujuk setiap workflow; status simpan/penerimaan dan pekerjaan yang masih perlu dilakukan harus dinyatakan jelas.

Seluruh kontrak menggunakan bukti/provenance yang aman, hubungan actor–waktu–konteks–alasan–hasil dan sebelum/sesudah jika relevan (DC-010/016/017/018). Bukti tersedia, hasil diterima, dokumen diterbitkan dan signoff resmi merupakan klaim terpisah. Prasyarat resmi yang belum diketahui tetap blocked; adanya interaksi pencatatan tidak membuktikan kelayakan bisnisnya.

Handoff fase yang berlaku pada **setiap WF**: P4 merepresentasikan identitas, hubungan data dan konteks sumber; P5 memvalidasi aktor, cakupan/representasi akun, otorisasi dan pembaca bukti; P6 memvalidasi konflik, consistency, retry, operasi panjang dan perhitungan setelah policy sahih; P7 menentukan halaman/panel, aksesibilitas, ID/EN, navigasi dan presentasi status. P3 hanya memberi makna hasil dan konteks, tanpa menentukan mekanisme itu. P8/P10 kelak menguji skenario acceptance. Referensi resmi dapat dicatat manual dengan provenance tanpa menjadikan SSO/LDAP/SiRUP/SPSE/Finance/HR/email institusi/tanda tangan digital/storage eksternal sebagai dependency V1 (BR-027; AC-26/27).

<a id="wf-001"></a>

## WF-001 — Konteks masuk dan akun

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Memulai atau mengakhiri penggunaan akun lokal dalam satu aplikasi; rujukan konteks masuk, keluar, profil akun dan pemulihan. Kontrak akun/security rinci tetap P5/P9. |
| Authority / dasar | APPROVED_PRODUCT_FLOW untuk hasil CAP-01/AC-01, OD-11/12; BLOCKED_BY_GAP untuk eligibility, provisioning dan recovery channel GAP-015. |
| Pemicu / prasyarat | Pengguna membuka entry, perlu autentikasi, mengakhiri sesi atau memerlukan bantuan akun; hanya kontrak akun tervalidasi yang dapat menyatakan sesi sahih. |
| DC / tanggung jawab | DC-002/003/004/009; pengguna pemilik konteks akun dan pengelola akun yang nanti tervalidasi. |
| State / action contract | Context-only, tanpa state machine autentikasi baru; [WF-001 / TR-001–TR-003](STATE_TRANSITIONS.md#wf-001). |
| Bukti / owner–waiting–next | Hasil entry dan konteks tujuan yang boleh dibawa; pengguna menunggu hasil kontrak P5 atau bantuan pengelola akun bila blocked. Jangan menampilkan password/token sebagai bukti. |
| Revisi / kegagalan / batal | Kegagalan entry mempertahankan tujuan aman, menjelaskan sesi belum diterima; pemulihan mengikuti kanal sahih. Cancel kembali ke entry; keluar mengakhiri sesi menurut P5. EP-002/003. |
| Selesai / hasil / handoff | Konteks sahih menuju WF-002 atau tujuan yang sudah dapat diakses; keluar kembali entry. Tidak membuat akun dari kredensial historis. |
| RT / IX / dependencies | RT-001; IX-019, IX-017; BR-002/003/027; INV-001/016; CAP-01/02/20; AC-01/02/23/24/26; GAP-014/015. |

<a id="wf-002"></a>

## WF-002 — Pilih dan pindah ruang kerja

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Memilih konteks Asset, Procurement, Vendor Portal atau Super Admin dan melanjutkan pekerjaan dengan satu akun. |
| Authority / dasar | APPROVED_PRODUCT_FLOW OD-01/02/20, AC-02; APPROVED_DOMAIN_INVARIANT INV-001; pemetaan tujuan/default adalah PROPOSED_WORKFLOW. |
| Pemicu / prasyarat | Entry selesai atau pengguna ingin pindah; tujuan dan konteks tujuan harus berada dalam akses yang kelak divalidasi P5. |
| DC / tanggung jawab | DC-001–009; pengguna memilih, tanggung jawab bisnis record tetap pada penugasan asalnya. |
| State / actions | [WF-002 / TR-011–TR-014](STATE_TRANSITIONS.md#wf-002). |
| Bukti / owner–waiting–next | Pilihan konteks tidak merupakan business approval; pengguna memegang navigasi, tidak ada pihak tunggu bisnis baru. Bila tidak tersedia, alasan dan tujuan aman diberikan. |
| Revisi / kegagalan / batal | Cancel pilihan mempertahankan ruang kerja asal dan data belum selesai; kehilangan akses mengikuti RT/IX tanpa membuka rincian sensitif. EP-003/005. |
| Selesai / hasil / handoff | Tujuan memiliki konteks jelas; record bersama tetap merujuk identitas sama. Super Admin membuka record dalam konteks pemiliknya dan dapat kembali ke konteks asal. |
| RT / IX / dependencies | RT-002; IX-016/017/021; BR-001/002/003; INV-001/002; CAP-02/03/19/20; AC-02/03/04/05/22/23/24; GAP-014. |

<a id="wf-003"></a>

## WF-003 — Kebutuhan dan intake usulan unit

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Unit Organisasi menyampaikan kebutuhan/usulan; unit dan pusat mengikuti kelengkapan serta revisi pekerjaan yang sama. Tidak membuat route khusus fakultas. |
| Authority / dasar | APPROVED_PRODUCT_FLOW untuk asal generik, pengiriman dan revisi AC-03/06; PROPOSED_WORKFLOW untuk pencatatan intake; approval/routing resmi BLOCKED_BY_GAP GAP-001. PR-F03/PROC-04/06/08/13 mendukung konsep. |
| Pemicu / prasyarat | Kebutuhan unit tersedia; identitas unit asal, konteks kebutuhan dan bahan pendukung dapat dijelaskan. Penerima pusat adalah fungsi belum dipetakan bila GAP-001 belum terjawab. |
| DC / tanggung jawab | DC-001/005/009/010/016/018/021/022/024; pihak asal/pengaju, operator penerima intake dan penelaah kelengkapan yang ditugaskan. |
| State / actions | [WF-003 / TR-021–TR-026](STATE_TRANSITIONS.md#wf-003). |
| Bukti / owner–waiting–next | Bukti pengajuan/revisi/receipt, origin, alasan kelengkapan dan hasil intake; owner berganti menurut pekerjaan yang tercatat, bukan hierarchy approval tebakan. |
| Revisi / kegagalan / batal | Permintaan perbaikan menunjuk data/bukti yang kurang; pengaju melengkapi tanpa membuat usulan dasar baru. Official approval/routing ditahan EP-003; missing evidence EP-002; revision EP-001; withdrawal lokal EP-007. |
| Selesai / hasil / handoff | Lingkup intake selesai ketika receipt/hasil kelengkapan dan next action tercatat. Ini belum approval resmi/paket; referensi WF-004/005 hanya setelah guard yang berlaku tersedia. |
| RT / IX / dependencies | RT-003; IX-001–007/009/011–013/021 sesuai TR; BR-001/003/005/026/029; INV-006/017; CAP-03/04/05/19; AC-03/05/06/07/22/24; GAP-001/002/003/013/014. |

<a id="wf-004"></a>

## WF-004 — Asosiasi referensi perencanaan/RUP

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Menghubungkan kebutuhan/usulan dan paket dengan rujukan RUP yang dapat ditelusuri; mencatat provenance/version/ketidakpastian. |
| Authority / dasar | PROPOSED_WORKFLOW untuk asosiasi; EVIDENCE_SUPPORTED PR-F03/PROC-06 tabel 6 baris 3–8, PROC-08 tabel 1 baris 3; seluruh sequence/publikasi/revisi resmi BLOCKED_BY_GAP GAP-002. |
| Pemicu / prasyarat | Referensi RUP diberikan atau hubungan planning perlu ditinjau; asal usulan/paket dan versi/bukti rujukan diketahui. |
| DC / tanggung jawab | DC-018/020/021–025; operator yang ditugaskan dan pengelola sumber/perencanaan; creator, approver, operator SiRUP dan publisher belum dipetakan. |
| State / actions | [WF-004 / TR-031–TR-034](STATE_TRANSITIONS.md#wf-004). |
| Bukti / owner–waiting–next | Bukti hubungan/asli-versi, penilaian applicability dan alasan missing/invalid; operator menunggu custodian perencanaan jika validitas belum diketahui. |
| Revisi / kegagalan / batal | Koreksi asosiasi mempertahankan rujukan lama; tidak mengaku mengubah/cancel RUP eksternal. EP-002/003/005/007; dependent package progression hanya blocked bila rule tervalidasi mensyaratkannya. |
| Selesai / hasil / handoff | Referensi dapat ditelaah dari usulan/paket, dengan keterbatasan jelas. Hasil asosiasi tidak menyatakan RUP telah dipublikasikan. SiRUP API FUTURE. |
| RT / IX / dependencies | RT-004; IX-002/006/009/011–013/021; BR-004/005/025/027; INV-006/018; CAP-04/05/19; AC-06/07/22/26; GAP-001/002/003/013. |

<a id="wf-005"></a>

## WF-005 — Kerangka paket dan ekstensi metode

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Memelihara konteks paket dan menghubungkan pekerjaan yang berlaku. Kerangka common berisi preparation/context, participation/submission, evaluation/clarification/negotiation, result, SPPBJ, contract, execution, inspection/handover, SPJ/payment dan downstream sebagai **extension points**, bukan urutan universal. |
| Authority / dasar | APPROVED_PRODUCT_FLOW untuk keterlacakan AC-07; PROPOSED_WORKFLOW untuk koordinasi common; applicability/order/actors resmi BLOCKED_BY_GAP GAP-003/017, authority GAP-013. |
| Pemicu / prasyarat | Paket perlu dibuat/dilengkapi dari konteks asal yang dapat dijelaskan; metode berupa konteks tervalidasi atau unknown yang terlihat. KAK/spesifikasi/HPS tidak diasumsikan wajib sama. |
| DC / tanggung jawab | DC-005/009/018/020/021–027/032–048/049/050; penanggung jawab paket, operator dan fungsi per extension sesuai penugasan tervalidasi. |
| State / actions | [WF-005 / TR-041–TR-045](STATE_TRANSITIONS.md#wf-005); method guards pada bagian tersebut mengendalikan setiap extension WF-006–WF-016/028. |
| Bukti / owner–waiting–next | Origin, konteks metode, bahan persiapan, versi, evidences dan next action masing-masing bagian. Paket tidak punya satu owner resmi universal; owner current mengikuti pekerjaan yang aktif dan diketahui. |
| Revisi / kegagalan / batal | Revisi konteks menunjukkan bagian/bukti/hilir terdampak tanpa otomatis mengubah final lama. Unknown applicability EP-004; policy EP-003; conflicting source EP-005; cancellation EP-007. |
| Selesai / hasil / handoff | Penutupan paket resmi tetap blocked sampai metode menentukan applicable completion/evidence/authority. Ringkasan dapat menunjukkan bagian selesai/tertunda/unknown secara jujur. Semua child kembali ke paket yang sama. |
| RT / IX / dependencies | RT-005; IX-001/002/006/009–013/015/017/020/021; BR-002–006/009–013/025/027/029; INV-001/002/005/006/012/017/018; CAP-03/05/06/08/09/10/19; AC-05/07/08/11/12/13/22/24/26; GAP-002/003/004/013/014/017/018/019. |

<a id="wf-006"></a>

## WF-006 — Aktivitas/event paket

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Membuat/menjadwalkan aktivitas, menghubungkan paket dan peserta, merujuk undangan/bahan, mencatat kejadian serta bukti hasil. Meeting, workshop, opening, review, klarifikasi, negosiasi dan technical review adalah kategori kontekstual. |
| Authority / dasar | EVIDENCE_SUPPORTED PROC-23–27/PR-F05; APPROVED_PRODUCT_FLOW AC-08; pencatatan PROPOSED_WORKFLOW, mandatory gate BLOCKED_BY_GAP GAP-003/017. |
| Pemicu / prasyarat | Aktivitas terkait suatu paket dibutuhkan; tujuan, jadwal bila ada, participant set dan responsible activity dapat dijelaskan. Invitation participant berbeda dari attendance. |
| DC / tanggung jawab | DC-004/005/009/010/012/013/024/025/042; penanggung jawab kegiatan, penyusun bahan/undangan, peserta dan pencatat hasil. |
| State / actions | [WF-006 / TR-051–TR-055](STATE_TRANSITIONS.md#wf-006). |
| Bukti / owner–waiting–next | Jadwal/perubahan, konteks peserta, undangan yang applicable, occurrence, hasil, pekerjaan lanjutan. Pengelola kegiatan menunggu kontribusi peserta/hasil yang benar-benar diminta. |
| Revisi / kegagalan / batal | Reschedule mempertahankan jejak; missing result tetap pending, tidak dianggap telah hadir/selesai. EP-001/002/004/007. Undangan/hasil resmi memakai WF-028, tanpa kanal email wajib. |
| Selesai / hasil / handoff | Hasil kejadian/batal dan next action tercatat; tidak otomatis meluluskan evaluasi atau paket. Kembali ke paket asal; kalender dan presentation P7. |
| RT / IX / dependencies | RT-006; IX-001/002/009–013/020/021; BR-003/004/006/009/026; INV-002/005/006/017; CAP-03/06/08/19; AC-05/08/11/22/24/26; GAP-003/013/014/017. |

<a id="wf-007"></a>

## WF-007 — Penyedia lama/baru dan profil yang dapat dipakai ulang

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Membentuk/melengkapi konteks perusahaan, PIC/contact dan qualification evidence yang relevan; penyedia lama menggunakan konteks valid yang tersedia, penyedia baru mengajukan bahan profil. Akun/representasi dan partisipasi tetap berbeda. |
| Authority / dasar | APPROVED_PRODUCT_FLOW untuk reuse/perbaikan AC-09; PROPOSED_WORKFLOW untuk review profil; eligibility/validity/representation BLOCKED_BY_GAP GAP-014/015/017. PR-F04 Input AU2:BX2 mendukung company/PIC. |
| Pemicu / prasyarat | Penyedia baru perlu profil atau penyedia lama perlu pembaruan; relationship company–PIC–account harus sesuai kontrak tervalidasi, tidak otomatis self-registration. |
| DC / tanggung jawab | DC-002/005/028–032/010/016/018; aktor penyedia, operator yang ditugaskan, penelaah profil/qualification dan pengelola sumber bila validitas unknown. |
| State / actions | [WF-007 / TR-061–TR-066](STATE_TRANSITIONS.md#wf-007). |
| Bukti / owner–waiting–next | Versi profil/bukti, hasil completeness, alasan update, sumber validitas dan scope reuse; penyedia menunggu review, reviewer menunggu bukti yang diminta. |
| Revisi / kegagalan / batal | Update hanya informasi yang perlu dan menjelaskan alasan; perusahaan tidak dibuat ulang untuk paket. EP-001/002/003/006/007; konflik identitas tidak digabung dari nama saja. |
| Selesai / hasil / handoff | Profil mempunyai konteks reuse yang dapat dijelaskan; bukan eligibility universal. WF-008 menggunakan referensi/versi applicable tanpa mengubah kiriman historis. Return ke profil/partisipasi yang memicu update. |
| RT / IX / dependencies | RT-007; IX-001–006/009/011–013/021; BR-002/007/008/009/029; INV-003/004/006; CAP-03/07/08/19; AC-05/09/10/11/22/24; GAP-003/014/015/017. |

<a id="wf-008"></a>

## WF-008 — Partisipasi, pengiriman dan revisi penyedia

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Penyedia mengirim informasi/dokumen applicable dari partisipasinya; pusat relevan menerima, menelaah dan meminta revisi jika berlaku; penyedia mengirim ulang dengan bukti/history dan outcome konsisten. |
| Authority / dasar | APPROVED_PRODUCT_FLOW AC-09/10 setelah APPR-002/P1-PROD-01; APPROVED_DOMAIN_INVARIANT INV-003/004/006. Tahap/metode, deadline dan official acceptance BLOCKED_BY_GAP GAP-003/014/017. |
| Pemicu / prasyarat | Tindakan partisipasi yang diminta mempunyai package/company context, applicability, bahan khusus paket dan bukti yang diperlukan. Company/PIC valid reused; nilai package-specific tetap pada paket. |
| DC / tanggung jawab | DC-002/005/009/028–035/010/016–018/024/025; aktor penyedia, operator penerima pusat yang relevan dan penelaah yang ditugaskan. |
| State / actions | [WF-008 / TR-071–TR-077](STATE_TRANSITIONS.md#wf-008). |
| Bukti / owner–waiting–next | Setiap submission/revision terhubung ke receipt, alasan revisi, outcome dan riwayat yang sama. Penerimaan teknis/konteks kiriman tidak berarti lulus evaluasi. Penyedia dan pusat melihat outcome yang konsisten dengan detail menurut akses. |
| Revisi / kegagalan / batal | Alasan menunjuk kekurangan dan next action penyedia; resubmit mempertahankan versi lama dan tidak meminta unrelated company re-entry. EP-001/002/004/005/007. Gagal receipt tidak ditampilkan diterima; jangan retry jika penerimaan unknown sebelum memeriksa hasil. |
| Selesai / hasil / handoff | Tindakan/pengiriman selesai pada scope outcome tercatat dan history dapat ditelusuri; award belum disimpulkan. Handoff WF-009 hanya menurut applicability. Return ke partisipasi asal atau paket asal bagi pusat. |
| RT / IX / dependencies | RT-008; IX-001–009/011–013/021 sesuai TR; BR-002/003/004/007/008/026; INV-003/004/006/017; CAP-03/07/19; AC-05/09/10/22/24/26; GAP-003/014/015/017. |

<a id="wf-009"></a>

## WF-009 — Evaluasi, klarifikasi dan negosiasi

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Menautkan review/hasil evaluasi, permintaan dan jawaban klarifikasi serta negotiation evidence ketika metode membolehkannya. Ketiga konsep tidak selalu urutan atau gate wajib. |
| Authority / dasar | EVIDENCE_SUPPORTED PROC-15/17/18/26/27; PROPOSED_WORKFLOW untuk pencatatan; kriteria/actor/order/score/negotiation applicability BLOCKED_BY_GAP GAP-003/013/017. |
| Pemicu / prasyarat | Kiriman/penawaran applicable tersedia; dasar penelaahan, responsibility, peserta dan batas jawaban/perubahan harus tervalidasi sebelum klaim keputusan resmi. |
| DC / tanggung jawab | DC-005/009/010/016/020/024/025/032–038/042; penelaah, penanggung jawab pekerjaan, pihak yang menjawab dan pencatat hasil; mapping jabatan unknown tetap visible. |
| State / actions | [WF-009 / TR-081–TR-085](STATE_TRANSITIONS.md#wf-009). |
| Bukti / owner–waiting–next | Rujukan kiriman/versi/dasar, pertanyaan–jawaban, hasil negosiasi jika applicable dan alasan hasil. Reviewer menunggu jawaban atau custodian metode; jawaban tidak otomatis revisi penawaran. |
| Revisi / kegagalan / batal | Kekurangan dikembalikan ke WF-008 bila revision diizinkan; review baru mempunyai hubungan sebelum/sesudah. EP-001/002/003/004/007. Tidak mengarang scoring atau legal deadline. |
| Selesai / hasil / handoff | Penelaahan/bukti dan posisi keputusan yang masih diperlukan dapat dijelaskan; hasil resmi hanya sesuai guard sahih. WF-010 menautkan hasil tanpa auto-award. Return paket/participation review asal. |
| RT / IX / dependencies | RT-009; IX-004/006–009/011–013/020/021 sesuai guard; BR-002/003/004/006/008/009/025; INV-004/005/006/017; CAP-03/05/06/07/19; AC-05/07/08/10/22/24; GAP-003/013/014/017/019. |

<a id="wf-010"></a>

## WF-010 — Hasil pengadaan/award

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Mencatat bahan hasil serta bukti penetapan sesuai metode; membedakan rekomendasi/review dari hasil yang mempunyai authority. |
| Authority / dasar | EVIDENCE_SUPPORTED PROC-19–21/PR-F05; PROPOSED_WORKFLOW untuk candidate record; official award BLOCKED_BY_GAP GAP-003/013/017. |
| Pemicu / prasyarat | Hasil penelaahan atau bukti eksternal tersedia; package, provider/hasil, metode, dasar keputusan dan provenance dapat dihubungkan. |
| DC / tanggung jawab | DC-005/010/014/016/018/020/024/025/028/036–039; penyusun hasil, penelaah, pemberi keputusan yang aktornya perlu divalidasi. |
| State / actions | [WF-010 / TR-091–TR-094](STATE_TRANSITIONS.md#wf-010). |
| Bukti / owner–waiting–next | Bahan/versi hasil, sumber authority, keputusan dan exception; penanggung jawab paket menunggu keputusan/fungsi resmi belum dipetakan jika belum ada dasar. |
| Revisi / kegagalan / batal | Revisi/rejection/withdrawal tidak menghilangkan bahan lama atau secara otomatis membatalkan kontrak. EP-001/003/004/005/007; filename tender dengan body direct tetap ambiguity. |
| Selesai / hasil / handoff | Status hasil diterima dalam scope rule yang tervalidasi; SPPBJ/kontrak hanya extension applicable, tidak otomatis dihasilkan/diberi authority. Return paket sama. |
| RT / IX / dependencies | RT-010; IX-001/002/006–009/010–013/021 sesuai guard; BR-004/009/010/025/026; INV-005/006/017/018; CAP-05/08/09/19; AC-07/11/12/22/24; GAP-003/013/014/017. |

<a id="wf-011"></a>

## WF-011 — Pekerjaan SPPBJ dengan tanggung jawab terpisah

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Memperlihatkan bahan SPPBJ dan pekerjaan penyusunan, pemeriksaan, penerbitan serta penandatanganan sebagai kontribusi berbeda. Tidak menetapkan urutan resmi di antara penerbitan/signature. |
| Authority / dasar | EVIDENCE_SUPPORTED PR-F02; BR-011 tetap BLOCKED_BY_GAP. Empat actor mappings, method applicability, order dan evidence resmi tetap GAP-004/017; tidak memilih universal PPK/Pokja/Pejabat Pengadaan. |
| Pemicu / prasyarat | Konteks hasil tersedia dan SPPBJ applicability memerlukan validasi; local draft dapat disiapkan dengan label draft jika sumber/data dapat dijelaskan. |
| DC / tanggung jawab | DC-005/008/009/010–016/018/024/025/039/040; **penyusun ≠ pemeriksa ≠ penerbit ≠ penanda tangan** pada tingkat tanggung jawab, tanpa mengharuskan empat orang sebelum rule sahih. |
| State / actions | [WF-011 / TR-101–TR-106](STATE_TRANSITIONS.md#wf-011). Kontribusi pemeriksaan/penerbitan/signature mempunyai guards masing-masing, bukan satu universal linear path. |
| Bukti / owner–waiting–next | Data/varian/versi draft, check outcome, issuance evidence dan signature authority masing-masing; pemilik tiap pekerjaan unresolved diperlihatkan bersama GAP-004, menunggu custodian pengadaan. |
| Revisi / kegagalan / batal | Koreksi draft kembali ke sumber/versi; klaim issue/sign/final tetap ditahan bila dasar tidak ada. EP-001/003/004/005/007. Signed evidence tidak ditimpa revisi draft. |
| Selesai / hasil / handoff | Penyelesaian resmi memerlukan semua kontribusi yang benar-benar disyaratkan metode/variant; tidak dapat ditetapkan sekarang. Tautan kontrak bukan efek issuance otomatis. Return paket asal. |
| RT / IX / dependencies | RT-011; IX-002/006–009/010–013/021 sesuai guard; BR-002/004/009/010/011/026; INV-002/005/006/017; CAP-05/08/09/19; AC-07/11/12/22/24; GAP-003/004/013/014/017/019. |

<a id="wf-012"></a>

## WF-012 — Kontrak/SPK dan perubahannya

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Menggunakan ulang context package/provider/result pada bahan kontrak/SPK, menautkan review/approval/signature applicable, active contract evidence dan perubahan. |
| Authority / dasar | APPROVED_PRODUCT_FLOW untuk tracking AC-12; PROPOSED_WORKFLOW untuk preparation; varian, nomor, klausul, signer/order/method BLOCKED_BY_GAP GAP-003/017/019. PR-F04 mendukung keluarga output. |
| Pemicu / prasyarat | Konteks hasil yang applicable dan bahan kontrak tersedia; setiap klaim berlaku mempunyai rujukan keputusan/dokumen diterima. SPPBJ tidak wajib universal. |
| DC / tanggung jawab | DC-005/010–018/020/024/025/028/039/041; penyusun, pemeriksa/penelaah dan pihak pemberi persetujuan/signature yang belum dipetakan resmi. |
| State / actions | [WF-012 / TR-111–TR-115](STATE_TRANSITIONS.md#wf-012). |
| Bukti / owner–waiting–next | Sumber terstruktur, versi varian, draft, review, bukti yang menyatakan kontrak berlaku dan addendum/change link; penyusun menunggu review atau authority kontrak. |
| Revisi / kegagalan / batal | Koreksi data sumber dan draft sesuai authority; change pada kontrak berlaku adalah konteks versi berbeda dan official effect tetap blocked. EP-001/002/003/004/005/007. |
| Selesai / hasil / handoff | Kontrak yang berlaku sesuai rule dapat dirujuk WF-013–015/024. Generate draft tidak menyatakan kontrak aktif. Return paket atau kontrak asal dari change child. |
| RT / IX / dependencies | RT-012; IX-001/002/006–009/010–013/021 sesuai guard; BR-004/009/010/012/025/026; INV-005/006/017/018; CAP-05/08/09/15/19; AC-07/11/12/18/22/24; GAP-003/004/013/014/017/019. |

<a id="wf-013"></a>

## WF-013 — Pelaksanaan dan progress

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Mencatat pelaksanaan/progress, isu, corrective evidence dan calon hasil selesai yang dibutuhkan pengadaan; tanpa project-management umum. |
| Authority / dasar | APPROVED_PRODUCT_FLOW untuk tracking CAP-09/AC-12; pencatatan PROPOSED_WORKFLOW, completion acceptance BLOCKED_BY_GAP GAP-003/017/018/019. |
| Pemicu / prasyarat | Konteks paket/kontrak yang applicable tersedia, evidence progress mempunyai asal/waktu/penanggung jawab. Tidak menyimpulkan percent/value tervalidasi dari label sumber. |
| DC / tanggung jawab | DC-005/009/010/016/018/024/041/043/072; penanggung jawab pekerjaan, operator pencatat, penelaah hasil dan pihak penyedia bila applicable. |
| State / actions | [WF-013 / TR-121–TR-125](STATE_TRANSITIONS.md#wf-013). |
| Bukti / owner–waiting–next | Update, bukti progress, alasan issue/correction, completion candidate; owner menunggu kontribusi pelaksana/reviewer yang ditugaskan atau authority hasil. |
| Revisi / kegagalan / batal | Correction mempunyai hubungan versi dan alasan; issue terbuka tidak dihilangkan demi status selesai. EP-001/002/003/007; cancel kontrak resmi tidak diputuskan oleh pencatat progress. |
| Selesai / hasil / handoff | Tracking menghasilkan candidate inspection WF-014 atau KDP progress WF-024 sesuai applicability. Progress tidak menyatakan paid, BAST, recognition atau downstream final. Return kontrak/paket sama. |
| RT / IX / dependencies | RT-013; IX-001/002/004–006/009/011–013/015/021; BR-003/012/021/026; INV-006/011/012/017; CAP-03/09/15/19; AC-05/12/18/22/24; GAP-003/008/014/017/018/019. |

<a id="wf-014"></a>

## WF-014 — Pemeriksaan, penerimaan, serah terima dan BAST

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Mencatat examination, acceptance/receipt, handover dan BAST sebagai empat klaim terkait dengan bukti/responsibility masing-masing, tanpa menjadikannya satu aksi. |
| Authority / dasar | EVIDENCE_SUPPORTED PR-U05/PROC-02 steps 16–20, PROC-04 k–l, PROC-30 KO2:KV2; PROPOSED_WORKFLOW untuk tracking; official guards BLOCKED_BY_GAP GAP-003/017/018. INV-012 adalah APPROVED_DOMAIN_INVARIANT. |
| Pemicu / prasyarat | Candidate hasil/pekerjaan tersedia; objects, sources, pihak serta jenis klaim evidence jelas. Urutan, pemeriksa/penerima dan acceptance criteria belum dipetakan. |
| DC / tanggung jawab | DC-005/009/010–018/024/041/043–046/049/050; pemeriksa hasil, penerima, pihak serah terima, pencatat serta drafter/checker/signer dokumen bila applicable. |
| State / actions | [WF-014 / TR-131–TR-136](STATE_TRANSITIONS.md#wf-014). Reception, handover dan BAST evidence dicatat sebagai kontribusi dengan guards terpisah. |
| Bukti / owner–waiting–next | Hasil pemeriksaan, exception, bukti acceptance/receipt, handover dan BAST/provenance; pihak yang menunggu diketahui per kontribusi atau explicit unresolved GAP-018. |
| Revisi / kegagalan / batal | Hasil kurang dikembalikan untuk perbaikan WF-013; bukti final tetap terkait versi lama. EP-001/002/003/007/008. BAST saja tidak mengaktifkan downstream. |
| Selesai / hasil / handoff | Scope resmi selesai hanya bila kontribusi/rule applicable terpenuhi; WF-016 menerima candidate eligible yang masih memerlukan validasi trigger/classification, WF-015 hanya hubungan bukti. Return paket/kontrak/hasil asal. |
| RT / IX / dependencies | RT-014; IX-004/006–009/010–015/021 sesuai guard; BR-002/009/010/012/013; INV-005/006/012/017; CAP-08/09/10/19; AC-11/12/13/22/24; GAP-003/013/014/017/018/019. |

<a id="wf-015"></a>

## WF-015 — Kelengkapan SPJ dan pelacakan pembayaran

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Memperlihatkan kelengkapan bahan SPJ, referensi/status pembayaran, bukti, owner dan pihak tunggu dalam paket/kontrak yang sama. |
| Authority / dasar | APPROVED_PRODUCT_FLOW CAP-09/AC-12; EVIDENCE_SUPPORTED PROC-30/32/ASSET-09; tracking PROPOSED_WORKFLOW, Finance acceptance/rules BLOCKED_BY_GAP GAP-010/017/019. |
| Pemicu / prasyarat | Bahan SPJ atau bukti/status pembayaran perlu dicatat; source, paket/kontrak, scope klaim dan versi jelas. Syarat pembayaran tidak diturunkan dari progress/BAST. |
| DC / tanggung jawab | DC-005/009/010–018/024/041/043–048; operator bahan SPJ, penelaah kelengkapan dan pihak penyedia/status evidence; official Finance function belum dipetakan. |
| State / actions | [WF-015 / TR-141–TR-145](STATE_TRANSITIONS.md#wf-015). |
| Bukti / owner–waiting–next | Checklist berdasarkan rule tervalidasi, kekurangan, receipt/status reference dan evidence payment claim; waiting dapat penyedia/pihak Finance menurut assignment atau unresolved GAP-010. |
| Revisi / kegagalan / batal | Perbaikan bahan menunjuk alasan; conflicting payment status memerlukan comparison/provenance, bukan auto-paid. EP-001/002/003/005/007. |
| Selesai / hasil / handoff | Scope tracking menyimpan status yang didukung bukti dan outstanding action. PRANATA tidak mentransfer dana, mengotorisasi treasury atau menetapkan pajak; rekonsiliasi WF-025 ketika applicable. Return paket/kontrak. |
| RT / IX / dependencies | RT-015; IX-002/004–006/009–013/015/021; BR-003/009/010/012/023; INV-005/006/014/017; CAP-03/08/09/17/19; AC-05/11/12/20/22/24; GAP-003/010/013/014/017/018/019. |

<a id="wf-016"></a>

## WF-016 — Klasifikasi dan handoff draft downstream

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Meninjau eligible source outcome, menahan klasifikasi yang belum diketahui, menggunakan data relevant menjadi draft Asset/Persediaan/KDP dan meneruskan sisa pekerjaan kepada operator hilir. |
| Authority / dasar | PROPOSED_WORKFLOW/product hypothesis OD-23/AC-13; APPROVED_DOMAIN_INVARIANT INV-012; actual trigger/classification/recognition/owner BLOCKED_BY_GAP GAP-008/009/018. |
| Pemicu / prasyarat | Outcome perolehan/hasil/handover diusulkan sebagai source; bukti dan applicability diperiksa, tidak cukup BAST, book value, filename atau account label. |
| DC / tanggung jawab | DC-005/009/010/016/018/020/044–050/051/063/065/070; penanggung jawab sumber, pengambil keputusan klasifikasi yang belum dipetakan, operator pelengkap/reviewer hilir. |
| State / actions | [WF-016 / TR-151–TR-156](STATE_TRANSITIONS.md#wf-016). |
| Bukti / owner–waiting–next | Source/version, eligibility/classification basis, sisa informasi, penugasan handoff dan receipt hilir; unknown tetap terlihat, source owner menunggu custodian decision atau receiving operator. |
| Revisi / kegagalan / batal | EP-008/003/006/007; correction source/klasifikasi menunjukkan draft/record/outputs impacted dan membutuhkan review, tanpa overwrite hasil definitif. |
| Selesai / hasil / handoff | Handoff selesai dalam scope draft setelah tujuan/owner/missing information/receipt dapat ditelusuri. Definitiveness diselesaikan dalam WF-017/023/024 menurut rules, tidak di WF-016. Kedua workspace merujuk source dan draft sama; return sumber tersedia. |
| RT / IX / dependencies | RT-016; IX-006/007/009/011–015/021; BR-013/018/021/025/029; INV-006/009/011/012/017/018; CAP-10/11/14/15/19; AC-11/13/14/17/18/22/24; GAP-008/009/010/014/018. |

<a id="wf-017"></a>

## WF-017 — Penerimaan register aset

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Melengkapi dan meninjau candidate asset dari downstream, historical intake atau acquisition source lain yang disetujui; provenance, identitas, lokasi dan kustodian jelas sebelum penerimaan definitif. |
| Authority / dasar | APPROVED_PRODUCT_FLOW CAP-11/AC-14; PROPOSED_WORKFLOW untuk pelengkapan; recognition/identity/duplicate/acceptance BLOCKED_BY_GAP GAP-008/009/011/018. ASSET-08/11 mendukung konsep. |
| Pemicu / prasyarat | Candidate/source applicable tersedia; keputusan klasifikasi, makna perolehan, identitas, penempatan/tanggung jawab dan evidence sesuai rules tervalidasi. |
| DC / tanggung jawab | DC-001/005/010/016–018/049–056/063/074/075; operator yang ditugaskan, kustodian, penelaah dan penerima record yang belum dipetakan. |
| State / actions | [WF-017 / TR-161–TR-166](STATE_TRANSITIONS.md#wf-017). |
| Bukti / owner–waiting–next | Source/perolehan/version, duplicate review, sisa completion, lokasi/custodian evidence dan keputusan acceptance; operator menunggu missing source atau reviewer. |
| Revisi / kegagalan / batal | EP-001/002/003/006/007; tidak menciptakan nomor aset atau merge identity dari label. Candidate rejected/withdrawn tetap terlacak; asset accepted diubah lewat WF-020. |
| Selesai / hasil / handoff | Record definitif hanya sesuai validated recognition/acceptance. Asset Workspace memperlihatkan relation source/draft/history; Procurement dapat menelusuri outcome sesuai akses. Return originating handoff/intake/register. |
| RT / IX / dependencies | RT-017; IX-001–009/011–013/015/021 sesuai guard; BR-013/014/015/018/024/029; INV-006/008/012/015/017; CAP-10/11/16/19; AC-13/14/19/22/24; GAP-008/009/011/013/014/018. |

<a id="wf-018"></a>

## WF-018 — Perpindahan/mutasi aset

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Mengajukan, meninjau dan menautkan accepted movement/mutation terhadap representasi aset dan history. Pindah lokasi merupakan satu konteks; bukan seluruh makna mutasi. |
| Authority / dasar | APPROVED_PRODUCT_FLOW AC-14/15; PROPOSED_WORKFLOW proposal/review; official types/chain/accounting BLOCKED_BY_GAP GAP-008/014. |
| Pemicu / prasyarat | Aset teridentifikasi dan perubahan konteks diperlukan; current/target placement atau jenis mutasi yang applicable, alasan dan evidence diketahui. |
| DC / tanggung jawab | DC-001/005/010/016–018/051/053–055/072; pengaju, kustodian asal/tujuan, operator dan reviewer sesuai assignment, tanpa fixed approval chain. |
| State / actions | [WF-018 / TR-171–TR-175](STATE_TRANSITIONS.md#wf-018). |
| Bukti / owner–waiting–next | Context sebelum/sesudah, alasan, dasar authority, penerimaan dan tanggal bermakna; pengaju menunggu review/kontribusi tujuan atau unresolved authority. |
| Revisi / kegagalan / batal | EP-001/003/005/007; conflict with historical placement tetap exception, tidak last-write-wins. Official cancellation/reversal accepted move membutuhkan keputusan. |
| Selesai / hasil / handoff | Accepted change sesuai rule memperbarui representasi relevant dan menautkan history; tidak menghapus lokasi lama. Child kembali aset yang sama; cross-unit responsibility visible tanpa granting akses. |
| RT / IX / dependencies | RT-018; IX-001–009/011–013/021 sesuai guard; BR-001/003/014/015/016/026; INV-008/017; CAP-03/11/12/19; AC-05/14/15/22/24; GAP-008/011/014/018. |

<a id="wf-019"></a>

## WF-019 — Verifikasi fisik, inventarisasi dan pemeliharaan aset

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Mencatat observation/check asset, evidence kondisi/keberadaan, discrepancy dan maintenance record yang relevant; tindakan tindak lanjut mempunyai scope sendiri. |
| Authority / dasar | APPROVED_PRODUCT_FLOW AC-14/15; APPROVED_DOMAIN_INVARIANT INV-007; pencatatan PROPOSED_WORKFLOW, skala/kriteria/acceptance BLOCKED_BY_GAP GAP-008/013. |
| Pemicu / prasyarat | Pekerjaan verifikasi atau maintenance terkait aset diperlukan; objek, scope, waktu, pengamat dan evidence dapat dijelaskan, kondisi unknown tetap unknown. |
| DC / tanggung jawab | DC-005/009/010/016–018/051/054–057/072; pemeriksa/penanggung jawab pekerjaan, kustodian dan operator follow-up. |
| State / actions | [WF-019 / TR-181–TR-185](STATE_TRANSITIONS.md#wf-019). |
| Bukti / owner–waiting–next | Observation asli, evidence, ketidaksesuaian, pekerjaan maintenance dan hasilnya; pemeriksa menunggu akses objek/bukti, pihak follow-up menunggu correction/reconciliation evidence. |
| Revisi / kegagalan / batal | EP-001/002/003/005/007; revisi observation punya reason/history. Tidak menilai rusak dari depreciation age/useful life/book value/zero, tidak mengarang condition scale. |
| Selesai / hasil / handoff | Hasil observed scope tercatat; discrepancy belum resolved sampai WF-020/025 beres sesuai rule. Maintenance tidak otomatis capitalization. Return aset/check scope sama. |
| RT / IX / dependencies | RT-019; IX-001/002/006/009/011–013/015/021; BR-003/014/015/016; INV-007/008/014/017; CAP-03/11/12/19; AC-05/14/15/22/24; GAP-008/009/010/013/014. |

<a id="wf-020"></a>

## WF-020 — Koreksi, pengembangan dan reklasifikasi aset

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Controlled-change proposal dengan jenis maksud jelas, reason/evidence, review/decision, before–after dan hubungan resulting representation. Ketiga jenis tidak dipersamakan. |
| Authority / dasar | EVIDENCE_SUPPORTED ASSET-05/11; PROPOSED_WORKFLOW untuk proposal/trace; accounting effects, period treatment dan decision authority BLOCKED_BY_GAP GAP-008/009/014. |
| Pemicu / prasyarat | Fakta salah/perubahan development/reclassification diusulkan; source/current/target meaning dan impacted time/report/outcome diketahui atau uncertainty ditandai. |
| DC / tanggung jawab | DC-005/010/016–020/051/053/057–064/070/072; pengaju/operator, reviewer dan decision responsibility yang tervalidasi nanti, custodian Asset/Finance untuk policy. |
| State / actions | [WF-020 / TR-191–TR-195](STATE_TRANSITIONS.md#wf-020). |
| Bukti / owner–waiting–next | Alasan, source, before/after, scope waktu, keputusan/authority dan links hilir/outputs affected; operator menunggu reviewer atau policy custodian. |
| Revisi / kegagalan / batal | EP-001/003/005/007; accepted record tidak diedit sebagai draft bebas. Tidak menetapkan jurnal/depreciation effect/historical reopening atau policy winner. |
| Selesai / hasil / handoff | Change diterima hanya setelah scope/rule/authority sahih; history menjelaskan resulting representation. Accounting blocked tetap terlihat. KDP relation WF-024, comparison WF-025, report WF-027 bila applicable; return aset/change origin. |
| RT / IX / dependencies | RT-020; IX-001–009/011–013/015/021 sesuai guard; BR-014/016/017/018/021/025/026; INV-006/008/011/017/018; CAP-12/13/15/19; AC-15/16/18/22/24; GAP-007/008/009/010/013/014. |

<a id="wf-021"></a>

## WF-021 — Usulan penghapusan/removal

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Mencatat proposal, kondisi/business basis, validation/decision evidence dan lifecycle history removal yang applicable; physical disposal dan keluar pencatatan dibedakan. |
| Authority / dasar | EVIDENCE_SUPPORTED ASSET-11; PROPOSED_WORKFLOW proposal; eligibility/criteria/authority/effects BLOCKED_BY_GAP GAP-008/013/014. INV-007/017 APPROVED_DOMAIN_INVARIANT. |
| Pemicu / prasyarat | Ada alasan bisnis/observasi sahih untuk proposal; identitas objek, scope tindakan dan bukti disebut. Age/book value zero bukan trigger eligibility. |
| DC / tanggung jawab | DC-005/010/016–020/051/053/055/056/061/064; pengaju, kustodian, reviewer dan decision responsibility yang belum dipetakan. |
| State / actions | [WF-021 / TR-201–TR-205](STATE_TRANSITIONS.md#wf-021). |
| Bukti / owner–waiting–next | Dasar kondisi/business, decision/authority dan bukti actual removal yang berbeda dari proposal; owner menunggu authority/evidence tindakan yang benar-benar berlaku. |
| Revisi / kegagalan / batal | EP-001/003/007; rejected/cancelled proposal tidak menghapus aset/history; accepted removal reversal membutuhkan rule, tidak simple restore. |
| Selesai / hasil / handoff | Keputusan dan bukti lifecycle yang tervalidasi tertelusur, history tetap ada. Tidak ada rule disposal otomatis atau record deletion. Return aset yang sama; reconciliation/report effects menurut authority. |
| RT / IX / dependencies | RT-021; IX-001/002/006–009/011–013/015/021 sesuai guard; BR-015/016/026; INV-007/008/017; CAP-12/19; AC-15/22/24; GAP-008/010/013/014. |

<a id="wf-022"></a>

## WF-022 — Permintaan/view penyusutan dan amortisasi

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Meminta/menelaah keluaran applicable dengan policy/cutoff context; membedakan request accepted dari output tersedia dan resmi. Tidak menetapkan perhitungan. |
| Authority / dasar | APPROVED_PRODUCT_FLOW AC-16; APPROVED_DOMAIN_INVARIANT INV-013/018; request path PROPOSED_WORKFLOW, actual policy BLOCKED_BY_GAP GAP-007/008/013. |
| Pemicu / prasyarat | Pengguna memilih jenis/lingkup/waktu yang applicable; supported temporal semantics dan instrument policy sahih dibutuhkan sebelum hasil official. |
| DC / tanggung jawab | DC-019/020/051/053/062–064/076; pengguna requester, pengelola sumber/kebijakan Asset/Finance, penanggung jawab operasi hasil. |
| State / actions | [WF-022 / TR-211–TR-214](STATE_TRANSITIONS.md#wf-022). |
| Bukti / owner–waiting–next | Request scope, policy/version/cutoff, sumber, exceptions dan output provenance; requester menunggu operasi atau custodian policy jika blocked. |
| Revisi / kegagalan / batal | EP-003/005/010; unsupported today/custom date tidak diberi hasil resmi. Koreksi input membuat request/context berbeda; historical final output tidak ditimpa. Abandon pre-acceptance boleh, cancellation operasi diterima menunggu P6. |
| Selesai / hasil / handoff | Output sahih tersedia dan konteks/evidence dapat dijelaskan; unresolved policy menghasilkan alasan blocked, tanpa official estimate. WF-027 memiliki empat semantic families; return report request/filter yang sama. |
| RT / IX / dependencies | RT-022; IX-012/013/015/018; BR-017/018/022/025; INV-007/013/018; CAP-13/17/18/19; AC-16/20/21/22/24; GAP-007/008/009/010/013/016. |

<a id="wf-023"></a>

## WF-023 — Keluarga transaksi Persediaan

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Enam keluarga: opening/baseline, receipt/addition, usage/release, physical count/adjustment, correction dan reconciliation. Record ledger, observation, posisi dan keputusan tetap distinct; reconciliation memakai WF-025. |
| Authority / dasar | APPROVED_PRODUCT_FLOW CAP-14/AC-17; PROPOSED_WORKFLOW untuk proposal/receipt; meaning/sign/accepted ledger/mapping BLOCKED_BY_GAP GAP-005/006. INV-009/010 APPROVED_DOMAIN_INVARIANT. |
| Pemicu / prasyarat | Transaksi/observation/source historical relevan; item, source, scope lokasi/waktu, family intent dan dictionary applicable harus dapat dijelaskan. Raw P01/P02 tidak diisi maknanya dari dugaan. |
| DC / tanggung jawab | DC-005/010/016–020/065–069/071/072/074/075; operator ledger, pihak count, custodian dictionary, reviewer serta penanggung jawab rekonsiliasi. |
| State / actions | [WF-023 / TR-221–TR-226](STATE_TRANSITIONS.md#wf-023); guards family pada bagian itu mencegah uniform posting. |
| Bukti / owner–waiting–next | Source/record, raw code/versi, makna yang tervalidasi, alasan transaksi/correction, physical-count evidence terpisah dan keputusan; owner menunggu dictionary/ambiguity resolution atau reviewer. |
| Revisi / kegagalan / batal | EP-001/003/005/006/007/009; koreksi bukan manual override posisi stok. Unresolved mapping tetap pending, tidak masuk official ledger result. |
| Selesai / hasil / handoff | Ledger accepted hanya dengan semantics/authority sahih; posisi mengikuti ledger diterima dan reconciliation. Count tidak otomatis adjustment; sumber procurement/intake tetap tertaut. Return item/ledger/handoff asal. |
| RT / IX / dependencies | RT-023; IX-001–009/011–013/015/018/021 sesuai guard; BR-019/020/023/024/025/029; INV-009/010/014/015/017/018; CAP-14/16/17/19; AC-17/19/20/22/24; GAP-005/006/010/011/012/013/014. |

<a id="wf-024"></a>

## WF-024 — KDP, progress dan calon penyelesaian

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Menghubungkan construction/acquisition context, KDP tracking, progress/evidence, completion candidate dan eligible asset downstream draft bila rule membolehkan. |
| Authority / dasar | APPROVED_PRODUCT_FLOW CAP-15/AC-18; APPROVED_DOMAIN_INVARIANT INV-011/012; tracking PROPOSED_WORKFLOW, recognition/value/completion/capitalization BLOCKED_BY_GAP GAP-008/009/018. ASSET-04/06/11 mendukung vocabulary. |
| Pemicu / prasyarat | KDP applicability perlu diperiksa dari source contract/execution/historical; kontrak konstruksi saja tidak membuat KDP. Sources, identity/context dan policy basis harus jelas. |
| DC / tanggung jawab | DC-005/010/016–020/041/043/049–053/063/070; operator KDP, penanggung jawab pekerjaan, reviewer completion/classification dan custodian Asset/Finance. |
| State / actions | [WF-024 / TR-231–TR-236](STATE_TRANSITIONS.md#wf-024). |
| Bukti / owner–waiting–next | Source, progress, issues, candidate completion, decision and link to draft/outcome; unfinished masih tracking, pihak menunggu actual completion/authority ditampilkan. |
| Revisi / kegagalan / batal | EP-001/003/007/008; completion claim without accepted basis remains blocked. No progress-to-value rule, premature Asset or automatic capitalization. |
| Selesai / hasil / handoff | KDP scope closure dan asset eligibility memerlukan validated evidence/rules; applicable candidate diteruskan WF-016/017 sebagai draft. History KDP dan final accepted result tetap berbeda; return KDP/source context. |
| RT / IX / dependencies | RT-024; IX-001/002/004–009/011–015/021 sesuai guard; BR-013/016/018/021/025/029; INV-006/011/012/017/018; CAP-10/15/19; AC-13/18/22/24; GAP-008/009/010/011/014/018. |

<a id="wf-025"></a>

## WF-025 — Rekonsiliasi dan selisih

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Memulai perbandingan scope/source/temporal context sesuai expected relationship sahih, mencatat differences, ownership, explanation/evidence dan disposition/signoff yang applicable. |
| Authority / dasar | APPROVED_PRODUCT_FLOW AC-20; PROPOSED_WORKFLOW comparison/follow-up; official institutional owner/waiting/signoff/cadence/output BLOCKED_BY_GAP GAP-010. INV-014 APPROVED_DOMAIN_INVARIANT. |
| Pemicu / prasyarat | Kesiapan/perbedaan sumber perlu ditelaah; sources/version, scope/time dan expected relation sahih dipilih, tanpa tolerance/equality buatan. GAP sumber dapat memblokir perbandingan official. |
| DC / tanggung jawab | DC-005/009/010/016–020/064/068/070–072/076; penanggung jawab kasus, pihak kontribusi sumber, penelaah serta penerima/signoff yang aktornya belum dipetakan. |
| State / actions | [WF-025 / TR-241–TR-246](STATE_TRANSITIONS.md#wf-025). |
| Bukti / owner–waiting–next | Scope/source comparison, perbedaan, assignments, alasan/evidence, decision/disposition dan signoff bila required; owner menunggu kontribusi/evidence/custodian authority per case. |
| Revisi / kegagalan / batal | EP-003/005/007/009/010; explanation tidak otomatis resolved; recompare preserves run/version dan reopen relation. Angka sama tidak menetapkan official signoff. |
| Selesai / hasil / handoff | Semua differences yang diklaim resolved mempunyai traceable explanation/evidence dan applicable acceptance. Signoff/period closure tetap blocked jika rule unknown. Crossworkspace links membatasi akses; evidence child kembali case sama. |
| RT / IX / dependencies | RT-025; IX-006–009/011–013/015/018/021 sesuai guard; BR-003/022/023/025/026; INV-013/014/017/018; CAP-03/14/16/17/18/19; AC-05/17/19/20/21/22/24; GAP-005/006/007/008/009/010/011/012/013/014/016/018. |

<a id="wf-026"></a>

## WF-026 — Intake historis dan cleansing

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Source dataset → candidate intake → structural/semantic validation bila rule tersedia → ambiguity/duplicate review → cleansing/acceptance/rejection decision → target domain candidate/record serta reconciliation evidence. Ini alur produk, bukan import implementation. |
| Authority / dasar | APPROVED_PRODUCT_FLOW safety AC-19; PROPOSED_WORKFLOW validation stages; format/identity/trust/cleansing/acceptance/cutover BLOCKED_BY_GAP GAP-011/012/013/019. INV-015/016 APPROVED_DOMAIN_INVARIANT. |
| Pemicu / prasyarat | Source approved for future intake with safe provenance/version, proposed target/domain and applicable validation basis; current P3 tidak menjalankan import atau menyentuh produksi. |
| DC / tanggung jawab | DC-005/010/016–020/067/073–075/077 dan target domain; pengelola source, operator intake, penelaah identity/semantics, decision responsibility dan reconciliation owner. |
| State / actions | [WF-026 / TR-251–TR-257](STATE_TRANSITIONS.md#wf-026). |
| Bukti / owner–waiting–next | Source identity/locator/version, validation results, original-versus-proposed interpretation, duplicate rule, decisions per scope, accepted/rejected summary dan reconciliation link. Operator menunggu issue resolver/custodian rules. |
| Revisi / kegagalan / batal | EP-001/003/005/006/007/009/010; raw source tidak ditimpa; corrected/rejected tetap traceable. Plaintext credentials bukan kandidat password PRANATA; missing identity rule tidak menjadi duplicate verdict. |
| Selesai / hasil / handoff | Future accepted target record hanya menurut domain rules dan trust/cutover authority; summary tidak menyatakan production migrated/final sebelum reconciliation acceptance. Return source/intake run/issue scope sama. |
| RT / IX / dependencies | RT-026; IX-001/002/006–009/011–013/015/018/021 sesuai guard; BR-020/024/025/026/029; INV-010/015/016/017/018; CAP-16/17/18/19; AC-19/20/21/22/24/26; GAP-005/006/011/012/013/014/015/016/019. |

<a id="wf-027"></a>

## WF-027 — Permintaan dan penelaahan laporan menurut makna waktu

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Memilih report family/lingkup, temporal input sahih, sources/policy/cutoff, melihat progress/result/exception dan menelusuri hasil. Empat families diperinci di STATE_TRANSITIONS. BMU tetap nama kebutuhan dengan definisi/form unknown. |
| Authority / dasar | APPROVED_PRODUCT_FLOW CAP-13/17/18; PROPOSED_WORKFLOW request/view; official values/form/temporal policy BLOCKED_BY_GAP GAP-007/008/009/010/013. INV-013/018 APPROVED_DOMAIN_INVARIANT. |
| Pemicu / prasyarat | Pengguna memerlukan position/as-of, transaction range, periodic atau reconciliation report; tipe dan konteks waktu dipilih sesuai applicability, tidak semua filters pada setiap report. |
| DC / tanggung jawab | DC-005/009/018–020/062–064/068/070–072/076; requester, pengelola sumber/policy, operator hasil dan reviewer/signoff bila applicable. |
| State / actions | [WF-027 / TR-261–TR-265](STATE_TRANSITIONS.md#wf-027), termasuk temporal-family contract. |
| Bukti / owner–waiting–next | Scope/time/source/version/policy/cutoff, operation receipt, progress atau alasan tidak terukur, exceptions/result provenance. Requester menunggu operasi; policy blocked menunggu custodian, bukan data kosong. |
| Revisi / kegagalan / batal | EP-003/005/009/010; no-data adalah hasil dengan scope/context jelas, berbeda dari invalid inputs/missing policy/failure. Request berubah tidak menimpa historical final output. Stop pre-acceptance hanya meninggalkan request draft; accepted-operation cancellation P6. |
| Selesai / hasil / handoff | Hasil tersedia dan provenance/error/exception dapat dijelaskan; official finality/signoff tidak diperoleh dari generation. Return request/source list/filter yang sama, outcome dapat ditemukan lagi; tidak full dataset ke browser. |
| RT / IX / dependencies | RT-027; IX-011–013/015/018/021; BR-017/018/019/020/022/023/025/027; INV-009/010/013/014/018; CAP-13/14/15/17/18/19/20; AC-16/17/18/20/21/22/23/24/26/27; GAP-005/006/007/008/009/010/012/013/016. |

<a id="wf-028"></a>

## WF-028 — Generasi, review dan versi dokumen

| Unsur kontrak | Spesifikasi |
|---|---|
| Tujuan / lingkup | Memilih applicable variant, memvalidasi nilai sumber, membuat draft, review/correct source atau draft menurut authority, lalu menautkan final/issue evidence yang memang berlaku. |
| Authority / dasar | APPROVED_PRODUCT_FLOW reuse AC-11; PROPOSED_WORKFLOW preparation/review; official variant/fields/clauses/number/signature/method/order BLOCKED_BY_GAP GAP-017/019 dan GAP-003/004 menurut dokumen. PR-F04 mendukung derivation structure. |
| Pemicu / prasyarat | Workflow induk memerlukan output applicable; source/version, variant/version dan required values valid. Filename atau worksheet tidak memilih official variant. |
| DC / tanggung jawab | DC-005/010–018/020 dan domain induk; penyusun, pemeriksa/penelaah, penerbit dan signer sesuai applicability yang belum dipetakan universal. |
| State / actions | [WF-028 / TR-271–TR-276](STATE_TRANSITIONS.md#wf-028). |
| Bukti / owner–waiting–next | Source/variant/output versions, validation issues, review/correction reasons, issue/final/sign evidence terpisah. Drafter menunggu source completion/review/custodian variant; tidak mengaku sah dari generation. |
| Revisi / kegagalan / batal | EP-001/002/003/004/005/007/010; perubahan source mengharuskan impact/context review; final lama retain provenance, revisi menjadi output terhubung. External final conflicting tidak diam-diam dikalahkan input terstruktur. |
| Selesai / hasil / handoff | Draft selesai pada generation sukses; final/issuance hanya jika authority terverifikasi. Dokumen kembali ke workflow/record/child context yang memicu generation dengan hasil/versi jelas. Digital signature FUTURE. |
| RT / IX / dependencies | RT-028; IX-002/006–013/015/021 sesuai guard; BR-004/009/010/011/025/026/027; INV-005/006/017/018; CAP-05/06/08/09/19; AC-07/08/11/12/22/24/26; GAP-003/004/013/014/017/019. |

## Batas dan next action kandidat

Seluruh GAP-001–GAP-020 tetap OPEN; GAP-020 tidak mengaktifkan borrowing workflow V1 (BR-028). Tidak ada procedure/actor/threshold/formula atau GAP resolution baru. Rangkaian yang tertahan tetap berguna sebagai konteks pekerjaan/bukti dan alasan blocked; tidak memberikan implementasi readiness. [WORKFLOW_TRACEABILITY](WORKFLOW_TRACEABILITY.md) memiliki pemetaan lengkap dan [WORKFLOW_DECISION_REQUESTS](WORKFLOW_DECISION_REQUESTS.md) kebutuhan validasi terkelompok. Next safe action: review independen kandidat P3 terhadap manifest, kemudian Owner approval atau bounded corrections/delta review. **Stop setelah P3 VERIFYING; P4 tidak diotorisasi.**
