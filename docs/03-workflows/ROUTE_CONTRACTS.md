# Route and navigation contracts — PRANATA UNY

Status: APPROVED (APPR-004) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P3 | Preparation: [AUTH-008](../00-governance/APPROVAL_RECORDS.md#auth-008) (HISTORICAL / COMPLETED) | Approval/checkpoint: [APPR-004](../00-governance/APPROVAL_RECORDS.md#appr-004) / [AUTH-009](../00-governance/APPROVAL_RECORDS.md#auth-009)

Dokumen ini memiliki **31 kontrak rute konseptual RT-001–RT-031**, termasuk kesinambungan konteks dan jalur kembali. Key rute adalah nama intent perencanaan, bukan URL, Laravel route, kontrak API atau rancangan halaman. Satu kontrak dapat mempunyai konteks daftar, detail dan tindakan anak; ini tidak menetapkan jumlah halaman atau endpoint. ID konseptual menunjuk objek domain yang perlu dapat dibedakan menurut [DOMAIN_MODEL](../02-domain/DOMAIN_MODEL.md#identitas-dan-keunikan-konseptual), bukan format identifier atau kunci database.

[WORKFLOW_CATALOG](WORKFLOW_CATALOG.md) memiliki index WF; [WORKFLOW_CONTRACTS](WORKFLOW_CONTRACTS.md) memiliki tujuan/proses; [STATE_TRANSITIONS](STATE_TRANSITIONS.md) memiliki wording state/transition; [INTERACTION_CONTRACTS](INTERACTION_CONTRACTS.md) memiliki hasil tindakan IX; [EXCEPTION_REVISION_PATTERNS](EXCEPTION_REVISION_PATTERNS.md) memiliki pola EP. Rute membuka konteks tersebut tanpa menciptakan proses/aturan tandingan. [BUSINESS_RULES](../02-domain/BUSINESS_RULES.md) tetap sole owner wording BR/INV.

## Kontrak yang berlaku pada seluruh RT

Seluruh entry RT di bawah **wajib mewarisi** kontrak ini. Rujukan prerequisite adalah persyaratan konseptual, bukan keputusan siapa mendapat izin. P5 kelak menetapkan pembaca/tindakan/penegakan, P7 kelak menetapkan navigasi dan penyajian.

Daftar IX pada baris **Blocked / handoff** adalah locator navigasi terkait yang **non-exhaustive**, termasuk tindakan anak/lintas konteks; bukan daftar aksi yang diizinkan langsung oleh rute. Seluruh applicability pemanggil tetap ditelusuri dari **RT / IX / dependencies** di kontrak WF terkait ke kontrak IX dan TR kanonik; locator RT yang ringkas tidak mengecualikan interaksi applicable di pemilik tersebut.

| Aspek wajib | Hasil navigasi yang disyaratkan |
|---|---|
| Konteks | Kenali workspace, unit asal atau scope yang relevan, tipe/identitas objek, versi bila diperlukan, dan pekerjaan yang sedang dibuka. Current owner, waiting party, next action atau alasan belum diketahui/tidak berlaku mengikuti WF dan BR-003; rute tidak mengisi aktor/approval yang hilang. |
| Akses/prerequisite | Pembaca harus memiliki akses ke konteks/objek yang dimaksud; tindakan mutasi memerlukan otorisasi tersendiri. Workspace switch, Super Admin entry, deep link, search, evidence dan history tidak menambah kewenangan bisnis. Batas BR-002 / INV-001/002/004 berlaku; GAP-014/P5 tetap OPEN. |
| Deep link | Detail dengan ID konteks yang cukup dapat ditemukan kembali, termasuk ketika masuk dari luar daftar. Lakukan penilaian sesi/akses/ketersediaan yang sama; ID hilang tidak diganti dengan objek lain. Jika perlu masuk lokal, intended destination hanya diteruskan setelah konteks/akses valid, melalui RT-001/RT-002. |
| Return | Pertahankan parent yang tepat, workspace/unit, filter/query/urutan/bagian daftar dan konteks waktu yang relevan bila masih valid dan diizinkan. Return bukan sekadar browser Back; jalur eksplisit harus tersedia meski entry adalah deep link. Fallback: parent sah → daftar sah tipe objek pada workspace pemilik → konteks kerja sah RT-002. Jelaskan bila konteks asal tidak dapat dipulihkan. |
| Empty | Nyatakan belum ada hasil/record anak pada lingkup yang dipilih; bedakan hasil filter kosong dari objek belum ada. Tawarkan mengubah filter atau tindakan create yang memang diizinkan dan dikontrak IX-001, tanpa menjadikan empty sebagai completed. |
| Not found | Bila target/parent/versi tidak ditemukan atau tidak dapat dipastikan, jangan membuka record yang mirip atau auto-create. Nyatakan konteks tidak dapat dibuka secukupnya; sediakan parent/daftar/context fallback di atas yang masih diizinkan. |
| Forbidden | Jangan menampilkan isi, count, metadata terlindungi atau identitas penyedia lain untuk menjelaskan penolakan. Nyatakan akses tidak tersedia dalam konteks yang aman dan sediakan return sah; jangan menawarkan workspace switch sebagai jalan mengatasi izin. Kebijakan membedakan not-found/forbidden yang aman menjadi P5. |
| Unavailable | Gangguan akses data/hasil harus memberi tahu apa yang gagal dan apakah ada tindakan sebelumnya yang tersimpan/diterima, bila status itu diketahui. Bila belum diketahui, nyatakan belum dapat dipastikan; buka hasil/history sah sebelum mencoba tindakan lagi. Navigasi aman dan retry pembacaan boleh ditawarkan; retry tindakan mengikuti IX/EP-010. |
| Blocked | Konteks yang aman dibaca tetap dapat dibuka; aksi dependent menjelaskan gate, GAP, bukti/keputusan yang diperlukan, owner/waiting yang diketahui atau belum ditetapkan, dan langkah aman. Unknown applicability bukan not-applicable. Tidak memberi shortcut official acceptance/finalisasi. |
| Anak/panel/modal | Completion/cancel menutup konteks anak dan mengembalikan pengguna ke parent yang sama. Jika input belum disimpan, IX-002/IX-009 menentukan penjelasan kehilangan input/penyimpanan. Mekanisme harus dapat dijangkau tanpa hover/color/motion; state change disampaikan tekstual, P7 menentukan detail. |
| Integrasi | Rute V1 tidak membutuhkan SSO/LDAP/SiRUP/SPSE/LPSE/Finance/HR/email institusi/digital signature/storage eksternal. Bukti/referensi manual dari kanal resmi bisa dibuka sesuai validasi, tanpa mengklaim melakukan aksi eksternal. FUTURE absence tidak mengubah record lokal menjadi unavailable. |

Key rute dan bentuk alur anak di bawah berklasifikasi **PROPOSED_WORKFLOW**. Kebutuhan kesinambungan/akses/workspace berasal dari **APPROVED_PRODUCT_FLOW** OD-01/02/17/19/20/21, CAP-02/03/18/19/20, AC-02/05/21–26; pembatas domain diwarisi dari **APPROVED_DOMAIN_INVARIANT** dengan ID yang dirujuk. Pengesahan candidate P3 tidak menjadikan key sebagai implementasi atau prosedur resmi. Setiap GAP di bawah tetap OPEN; gate official yang disebut adalah **BLOCKED_BY_GAP**.

## Kontrak entry, workspace dan procurement

## RT-001

**Key konseptual:** `account.entry-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Entry lintas workspace: mengenali masuk/keluar, konteks akun/sesi lokal dan tujuan kerja. Intended workspace/route-context opsional; referensi akun hanya sesudah konteks lokal valid. |
| Entry / prerequisite / deep link | Entry aplikasi, sesi yang perlu masuk kembali, atau return dari RT lain. Kontrak akun/sesi/provisioning/recovery P5/GAP-015; bukan open self-registration. Deep link menyimpan tujuan yang boleh dibuka lalu dinilai ulang. |
| Return / empty | Setelah hasil masuk yang valid, tujuan sah yang dimaksud atau RT-002. Keluar mengarah ke entry lokal dan menjelaskan status sesi sesuai kontrak P5. Tanpa tujuan, tampilkan pilihan konteks kerja sah; tanpa akses, jelaskan keadaan dan tindak lanjut tanpa mengarang pemberi akses. |
| Blocked / handoff | Recovery/channel/eligibility belum ditetapkan tetap gate GAP-015; tidak dialihkan ke SSO wajib. WF-001; IX-019; P5 akun/sesi/akses, P7 entry serta kontinuitas/fokus, P9 saluran recovery kelak. |

## RT-002

**Key konseptual:** `workspace.context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset, Procurement, Vendor atau Super Admin: memilih/switch konteks kerja pada akun yang sama. Workspace dan scope yang diizinkan; target objek opsional. |
| Entry / prerequisite / deep link | RT-001, switch konteks, atau handoff lintas workspace. Akses target harus ada menurut P5; deep link boleh menuju objek pada owning workspace bila akses tersedia. |
| Return / empty | Kembali ke konteks asal yang masih sah dengan input/status simpan yang dijelaskan IX-016. Jika target objek tidak tersedia, daftar sah workspace tujuan; jika workspace tujuan tidak sah, tetap pada asal sah. Tidak mempunyai akses workspace dijelaskan tanpa membuka data. |
| Blocked / handoff | Penugasan/akses unresolved GAP-014 tidak diselesaikan oleh switch. WF-002; IX-016/021; P5 akses/scopes, P7 orientasi workspace/scope dan return lintas workspace. |

## RT-003

**Key konseptual:** `procurement.request-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement: membuat/menelusuri kebutuhan dan usulan Unit Organisasi generik sampai intake. Unit asal untuk create; ID Usulan Pengadaan untuk detail, Need/evidence yang terkait bila ada. |
| Entry / prerequisite / deep link | Daftar usulan, pekerjaan tertunda/revisi, intake pusat, search. Pembaca/pengaju dalam scope yang sah; deep link mempertahankan usulan yang sama dan origin unit. |
| Return / empty | Create/cancel → daftar asal unit/intake yang sama; kebutuhan/evidence/revisi anak → usulan yang sama; usulan dari search → prior search. Usulan/pengajuan belum ada berbeda dari kiriman belum diterima atau filter kosong. |
| Blocked / handoff | Official routing/approval GAP-001, RUP GAP-002, aktor/metode/source GAP-003/013 tetap terlihat tanpa faculty-only route. WF-003; IX-001–006/011/012; P5 scope intake, P7 list/detail/revision continuity. |

## RT-004

**Key konseptual:** `procurement.rup-reference-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement: mengaitkan/menelaah referensi perencanaan/RUP. ID usulan atau paket parent; ID referensi/versi bila sudah ada. |
| Entry / prerequisite / deep link | Usulan/paket atau pencarian konteks planning yang boleh dilihat. Hubungan ke parent/provenance diperlukan; deep link tidak mengklaim publikasi SiRUP. |
| Return / empty | Association/review/cancel → usulan atau paket asal dengan referensi yang sama. Bila parent tidak diberikan, daftar planning sah dan kaitan parent yang boleh dibuka. Empty berarti belum ada referensi; bukan otomatis bebas prasyarat planning. |
| Blocked / handoff | Creator/approver/publication/revision/cancel resmi GAP-002 tetap unresolved; missing/invalid reference memblokir dependent progression hanya bila rule applicable sudah divalidasi. WF-004; IX-002/006/011; P5 tanggung jawab, P7 reference/parent return. |

## RT-005

**Key konseptual:** `procurement.package-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement: menelusuri skeleton paket, applicability metode dan seluruh anak proses. ID paket; unit/usulan/source context untuk create kandidat bila tersedia. |
| Entry / prerequisite / deep link | Daftar paket, usulan, backlog, search, detail hasil/kontrak atau handoff. Akses paket dan anak dinilai terpisah; deep link menampilkan konteks metode yang diketahui/unknown. |
| Return / empty | Anak RT-006/008–016/028 kembali ke paket yang sama atau subkonteks asal; paket kembali ke daftar/filter atau usulan/search asal sah. Empty anak adalah belum ada bukti/pekerjaan yang applicable, bukan langkah otomatis dilewati. |
| Blocked / handoff | GAP-002/003/004/013/017/019: named method extension gate; tidak memaksa semua tahap atau menebak metode dari filename. WF-005; IX-001/002/006/012/021; P5 akses per konteks, P7 orientasi package/anak. |

## RT-006

**Key konseptual:** `procurement.activity-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement, Vendor untuk bagian yang diizinkan: schedule/participants/occurrence/outcome aktivitas paket. ID paket parent; ID aktivitas untuk detail. |
| Entry / prerequisite / deep link | Paket, daftar jadwal/pekerjaan, invitation/evidence context atau search. Hubungan paket dan peserta perlu jelas; deep link menunjukkan paket asal, bukan kegiatan tanpa konteks. |
| Return / empty | Create/edit/outcome/cancel → aktivitas/paket asal; entry jadwal → rentang/konteks daftar jadwal asal yang masih sah. Undangan/evidence anak → aktivitas yang sama. Empty berarti belum ada kegiatan/hasil, bukan absence gate wajib. |
| Blocked / handoff | Applicability/invitation/actor GAP-003/013/017; tidak membuat aktivitas wajib universal. WF-006; IX-001/002/010/011/020; P5 peserta/bukti, P7 calendar/activity presentation serta return. |

## RT-007

**Key konseptual:** `provider.profile-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Vendor atau Procurement sesuai akses: profil perusahaan/PIC/kualifikasi penyedia existing/new. ID perusahaan untuk existing; kandidat identitas perusahaan untuk new; PIC/bukti/versi anak bila relevan. |
| Entry / prerequisite / deep link | Profil kerja, partisipasi RT-008, penelaahan pusat, search sah. Representasi akun/perusahaan sesuai P5/GAP-014/015; deep link tidak memberi onboarding/eligibility approval. |
| Return / empty | Update/revision/evidence → profil yang sama; bila entry partisipasi, kembali ke partisipasi/paket yang sama. New profile cancel → konteks origin sah. Empty berarti profil/bukti belum tersedia atau validitas belum diketahui; data valid lain tidak diminta ulang. |
| Blocked / handoff | GAP-014/015/017: representasi/eligibility/validity belum divalidasi; company ≠ PIC ≠ account ≠ participation. WF-007; IX-001–006/009/011–013/021 sesuai kontrak WF-007, termasuk submission profil IX-003; P5 relation/visibility, P7 reusable versus package context. |

## RT-008

**Key konseptual:** `provider.participation-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Vendor/Procurement: partisipasi, pengiriman, penerimaan pusat, revisi dan hasil/status yang konsisten. ID partisipasi serta paket/perusahaan terkait; ID pengiriman/revisi untuk anak spesifik. |
| Entry / prerequisite / deep link | Pending action penyedia, paket yang sah, profil, pusat receipt/review atau search. Pembaca harus berhak atas partisipasi terkait; deep link selalu menjaga company–package–submission yang sama. |
| Return / empty | Pengiriman/revisi/review anak → partisipasi yang sama dan paket terkait; profil update → kembali ke kebutuhan partisipasi asal tanpa re-entry unrelated data. Dari paket → paket asal; dari Vendor list → daftar/filter partisipasi asal. Empty kiriman berarti belum dikirim, bukan diterima/eligible/award. |
| Blocked / handoff | GAP-003/014/015/017: applicable requirement/deadline/actor tetap unresolved. Notfound/forbidden tidak mengungkap penyedia/penawaran/evaluasi lain. WF-008; IX-003–008/011/012; P5 visibility/relation, P7 receipt/revision/action/history continuity. |

## RT-009

**Key konseptual:** `procurement.assessment-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement; Vendor hanya permintaan/jawaban yang sah: evaluasi/klarifikasi/negosiasi yang applicable. ID paket serta partisipasi/pengiriman terkait; ID assessment/activity bila tersedia. |
| Entry / prerequisite / deep link | Paket, receipt review, clarification action/activity atau hasil. Konteks metode/dasar/penugasan diperlukan; deep link tidak membuka seluruh evaluasi ke penyedia. |
| Return / empty | Review/clarification/negotiation/evidence → subkonteks assessment asal atau partisipasi/paket yang sama; entry aktivitas → aktivitas asal. Empty berarti penelaahan/bukti belum ada atau tidak applicable dengan alasan tervalidasi; unknown tetap unknown. |
| Blocked / handoff | GAP-003/013/017/019: tidak menambahkan scoring, mandatory negotiation atau order resmi. WF-009; IX-004–008/011/020; P5 protected assessments, P7 contextual review/return. |

## RT-010

**Key konseptual:** `procurement.result-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement; Vendor bagian yang sah: hasil/penetapan yang berlaku. ID paket dan ID hasil/versi bila tersedia. |
| Entry / prerequisite / deep link | Paket, assessment, receipt/status atau search. Keputusan/authority/applicability harus jelas; deep link tidak membuat award dari dokumen saja. |
| Return / empty | Detail/dokumen/evidence/review → hasil/paket sama, lalu entry assessment/list sah. Empty berarti belum ada hasil yang dapat dibuka; absence output bukan cancelled. |
| Blocked / handoff | Award authorization/metode/varian GAP-003/013/017. WF-010; IX-006–010/011/012; P5 result visibility/authority, P7 result versus output context. |

## RT-011

**Key konseptual:** `procurement.sppbj-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement: pekerjaan drafting/checking/issuance/signature SPPBJ terpisah. ID paket/hasil parent; ID dokumen/versi bila tersedia. |
| Entry / prerequisite / deep link | Hasil/paket, dokumen atau penugasan spesifik. Deep link menampilkan tanggung jawab kegiatan yang diketahui/belum ditetapkan; label PPK/Pokja tidak cukup sebagai prerequisite. |
| Return / empty | Draft/review/evidence/cancel → konteks SPPBJ/hasil/paket sama. Empty draft/bukti final berbeda dari issuer/signer unknown. Parent fallback mengikuti hasil lalu paket sah. |
| Blocked / handoff | GAP-004 serta GAP-003/013/017; official issuer/signer/required sequence tetap blocked. WF-011; IX-002/006/007/010/011; P5 authority per activity/segregation kelak, P7 empat tanggung jawab dan return. |

## RT-012

**Key konseptual:** `procurement.contract-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement; Vendor bila sah: kontrak/SPK, sumber, review serta versi/perubahan. ID paket/hasil/perusahaan dan ID kontrak/versi bila tersedia. |
| Entry / prerequisite / deep link | Paket/hasil/SPPBJ jika applicable, dokumen, execution atau search. Deep link mempertahankan varian/provenance, tidak menyatakan setiap SPPBJ/kontrak wajib. |
| Return / empty | Draft/review/document/perubahan → kontrak versi yang dimaksud; kontrak → paket/hasil/list asal. Empty berarti belum ada kontrak/bukti applicable, bukan authority signing tersedia. |
| Blocked / handoff | GAP-003/004/017/019: numbering/signatory/clause/variant unknown; jangan issue sebagai official. WF-012; IX-001/002/006–010/011/012; P5 signatory/visibility, P7 structured source/document/version return. |

## RT-013

**Key konseptual:** `procurement.execution-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement; Vendor bila sah: progress, bukti pelaksanaan/issue dan kandidat completion. ID paket/kontrak; ID progress/issue/version untuk anak. |
| Entry / prerequisite / deep link | Kontrak/paket, pekerjaan tertunda, handover atau search. Hubungan progress dengan sumber pelaksanaan harus dapat dipastikan; deep link tidak mengklaim payment/capitalization. |
| Return / empty | Progress/evidence/correction → execution konteks sama; execution → kontrak/paket asal sah. Empty berarti belum ada progress/evidence pada scope, bukan progress nol/final berdasarkan dugaan. |
| Blocked / handoff | Official completion/acceptance/effects GAP-008/017/018/019. WF-013; IX-001/002/003/004/011/015; P5 submit/review scope, P7 progress/issue continuity. |

## RT-014

**Key konseptual:** `procurement.handover-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement serta downstream reader sah: pemeriksaan, penerimaan, serah terima, BAST terpisah. ID paket/kontrak/execution; ID inspection/handover/BAST version sesuai anak. |
| Entry / prerequisite / deep link | Execution/paket, bukti BAST, SPJ atau handoff RT-016. Deep link mengenali jenis claim yang dibuka; BAST bukan ID aset otomatis. |
| Return / empty | Inspection/acceptance/BAST/evidence → handover context/paket sama; datang dari downstream → sumber handoff yang sama. Empty hasil pemeriksaan/serah terima/BAST dijelaskan terpisah. |
| Blocked / handoff | GAP-003/017/018: trigger/owner/acceptance unknown; no definitive downstream from BAST. WF-014; IX-003/006/007/010/011/021; P5 inspection/receipt responsibility, P7 distinction dan source return. |

## RT-015

**Key konseptual:** `procurement.accountability-payment-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement atau Asset/Finance view yang sah: kelengkapan SPJ dan status/bukti payment tracking. ID paket/kontrak serta ID SPJ/payment reference bila ada. |
| Entry / prerequisite / deep link | Paket/kontrak/handover, backlog kelengkapan, reconciliation atau search. Deep link mempertahankan tracked reference, bukan instruksi transfer. |
| Return / empty | Kelengkapan/evidence/status update → tracking context sama; dari reconciliation → kasus asal; selain itu paket/kontrak/list asal. Empty berarti bukti/status belum tersedia; jangan menyimpulkan unpaid/paid tanpa dasar. |
| Blocked / handoff | GAP-010/017/018/019: acceptance Finance/payment rule unknown. WF-015; IX-002/004/006/010/011/012; P5 tracking readers/writers, P7 SPJ versus payment visibility. |

## Kontrak downstream, Asset dan ledger

## RT-016

**Key konseptual:** `downstream.handoff-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Procurement → Asset (Asset/Persediaan/KDP): classification, draft dan handoff outcome. ID sumber paket/kontrak/handover; ID kandidat klasifikasi/draft jika sudah ada. |
| Entry / prerequisite / deep link | Handover/paket, pekerjaan downstream, register/ledger/KDP terkait atau search sah. Deep link menjaga kedua konteks asal/tujuan yang boleh dilihat; akses tujuan dinilai lagi. |
| Return / empty | Classify/complete/review → handoff yang sama; tujuan → source context sah dan sebaliknya. Jika tujuan belum ada/terblokir, tetap di handoff asal dengan alasan; jika source tidak boleh dilihat, owning downstream context sah tanpa bocoran sumber. Empty draft berarti belum ada kandidat, bukan definitive record. |
| Blocked / handoff | GAP-008/009/014/018: tetap needs classification/blocked bila rule/owner/trigger unknown; no guessed Asset/Persediaan/KDP. WF-016; IX-001/006/007/014/021; P5 source/target visibility, P7 reciprocal return/fallback. |

## RT-017

**Key konseptual:** `assets.registration-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset: kandidat register/record aset, sumber, identitas, lokasi/kustodian dan penerimaan. ID kandidat/aset; sumber downstream/historis/perolehan lain yang relevan untuk create. |
| Entry / prerequisite / deep link | Register, RT-016 draft, RT-026 accepted-candidate context, search. Provenance dan identitas target harus jelas; deep link membedakan kandidat dari accepted record. |
| Return / empty | Completion/review/evidence → kandidat/aset sama; entry handoff/import → asal yang sama; deep link tanpa asal → register sah. Empty register/filter berbeda dari pending candidate/duplicate ambiguity. |
| Blocked / handoff | Definitive acceptance/identity/classification GAP-008/009/011/014/018; tidak membuat nomor aset atau memilih record mirip. WF-017; IX-001–003/006/007/011/013; P5 acceptance/scope, P7 candidate versus register continuity. |

## RT-018

**Key konseptual:** `assets.movement-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset: proposal/perubahan lokasi atau mutasi aset yang applicable. ID aset parent; ID movement/change/version bila ada. |
| Entry / prerequisite / deep link | Aset, pekerjaan perubahan, riwayat atau search. Parent aset dan jenis mutasi harus pasti; deep link tidak mengganti parent atau menganggap mutasi hanya lokasi. |
| Return / empty | Create/review/change/evidence/cancel → movement atau aset yang sama, lalu prior register/filter sah. Empty berarti belum ada riwayat/proposal movement; posisi current tidak diisi dari proposed movement. |
| Blocked / handoff | Official authority/effects GAP-008/011/014; accepted change berbeda dari proposal. WF-018; IX-001–009/011–013; P5 movement authority, P7 Asset → Movement → same Asset. |

## RT-019

**Key konseptual:** `assets.physical-observation-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset: observasi/verifikasi fisik, ketidaksesuaian dan bukti pemeliharaan. ID aset dan lingkup verifikasi; ID observasi/maintenance bila tersedia. |
| Entry / prerequisite / deep link | Aset, lingkup pemeriksaan, discrepancy atau search. Deep link menunjukkan jenis observasi/waktu dan aset yang sama; bukan nilai buku sebagai kondisi. |
| Return / empty | Observation/maintenance/evidence → observasi/aset atau lingkup pemeriksaan asal; discrepancy → case asal sah. Empty berarti belum diketahui/belum ada observasi, bukan baik/rusak dari umur. |
| Blocked / handoff | Skala/authority/effect GAP-008/013/014; no depreciation/zero-book-value inference. WF-019; IX-001/002/003/006/011/015; P5 inspection scope, P7 physical versus accounting context. |

## RT-020

**Key konseptual:** `assets.controlled-change-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset: koreksi, pengembangan, reklasifikasi sebagai jenis berbeda. ID aset/transaksi/source parent; ID proposal/version/periode yang dibahas. |
| Entry / prerequisite / deep link | Aset, discrepancy, KDP bila terkait, riwayat atau search. Jenis perubahan, before context, alasan dan sumber perlu jelas; deep link tidak berarti accounting posting tersedia. |
| Return / empty | Proposal/review/compare/evidence → perubahan sama; lalu aset/kasus/KDP asal sah. Empty berarti belum ada proposal/hasil accepted; tidak mengganti histori dengan current value tanpa jejak. |
| Blocked / handoff | GAP-008/009/010/013: official effect/journal/historical treatment unknown. WF-020; IX-001–009/011–015; P5 authority, P7 compare/controlled-change/source return. |

## RT-021

**Key konseptual:** `assets.removal-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset: proposal/dasar/keputusan penghapusan atau removal dalam scope yang jelas. ID aset; ID proposal/evidence/decision bila tersedia. |
| Entry / prerequisite / deep link | Aset, pekerjaan lifecycle, evidence/history atau search. Deep link membedakan disposal fisik, perubahan penggunaan dan pencatatan; no age/value eligibility shortcut. |
| Return / empty | Proposal/review/evidence/decision → removal context sama dan aset historis sah; record yang dipindah scope tetap dapat ditelusuri. Empty berarti proposal belum ada, bukan eligibility otomatis. |
| Blocked / handoff | Criteria/official authority GAP-008/013/014; no record deletion from business removal. WF-021; IX-001/003/006–009/011–013; P5 responsibility/retention, P7 lifecycle versus history. |

## RT-022

**Key konseptual:** `assets.depreciation-report-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset: permintaan/hasil pelaporan penyusutan/amortisasi yang applicable. Jenis laporan, scope, temporal context; ID hasil/operasi bila sudah ada. |
| Entry / prerequisite / deep link | Aset/laporan, kesiapan periode, reconciliation atau search hasil sah. Policy/version/cutoff yang diperlukan harus dapat dijelaskan; deep link hasil menjaga policy/source context asal. |
| Return / empty | Request/view → report context atau aset/kasus asal dengan temporal input yang sama bila sah. Empty output adalah data tidak ada pada scope; policy missing/invalid bukan zero/no-data report. |
| Blocked / handoff | GAP-007/008/009/010/013: angka official dependent blocked; today/custom bukan universal. WF-022; IX-018/012; P5 report access, P6 operation mechanics kelak, P7 temporal/block feedback. |

## RT-023

**Key konseptual:** `inventory.ledger-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset: item/ledger Persediaan, transaksi/hitung fisik/koreksi dan posisi yang diterima. ID item serta scope lokasi/waktu; ID transaksi/observasi/kandidat bila ada. |
| Entry / prerequisite / deep link | Daftar item/ledger, downstream draft, import/reconciliation atau search. Dictionary/applicability harus diketahui untuk claim accepted; deep link mempertahankan kode sumber terpisah dari interpretation. |
| Return / empty | Transaksi/count/evidence/correction → item/ledger dalam scope/waktu asal; dari handoff/import/case → asal sah. Empty ledger/filter berbeda dari unknown stock; raw unresolved code tidak menghasilkan saldo tebakan. |
| Blocked / handoff | GAP-005/006/010/011: P01/P02 mapping/effect unresolved; no silent normalization/manual competing balance. WF-023; IX-001–009/011–015/018; P5 ledger scope/acceptance, P7 raw versus interpreted context. |

## RT-024

**Key konseptual:** `kdp.tracking-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset dengan source Procurement yang sah: KDP, progress/evidence, completion candidate dan eligible downstream. ID KDP/kandidat; sumber kontrak/progress/handoff yang terkait. |
| Entry / prerequisite / deep link | KDP list, downstream, execution, completion/correction atau search. Deep link mengidentifikasi KDP berbeda dari Asset definitive serta sumber yang sah. |
| Return / empty | Progress/completion/evidence → KDP yang sama; handoff ke kandidat aset → return KDP/source yang sama bila sah. Empty berarti belum ada KDP/progress/hasil applicable; unfinished context tidak auto-convert. |
| Blocked / handoff | GAP-008/009/010/018: recognition/value/completion/trigger unknown. WF-024; IX-001–007/011/014/021; P5 KDP/downstream authority, P7 reciprocal KDP/source return. |

## Kontrak lintas proses dan keluaran

## RT-025

**Key konseptual:** `reconciliation.case-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset/Procurement sesuai sumber, Super Admin contextual view sah: scope/sumber/selisih/penjelasan/penerimaan kasus. ID kasus; scope/sumber/time untuk create; ID selisih anak bila tersedia. |
| Entry / prerequisite / deep link | Kesiapan, hasil perbandingan/laporan, register/ledger/SPJ/historical intake atau search. Deep link menjaga case dan expected relationship yang sama, bukan mengisi signoff owner. |
| Return / empty | Difference/evidence/review/history → kasus yang sama dan prior scope/temporal context; bukti RT-031 kembali ke case/difference tepat. Sumber → kembali case bila diizinkan. Empty difference tidak berarti official signoff/completion. |
| Blocked / handoff | GAP-010/012 serta rule sumber terkait: owner/waiting/signoff/cadence unknown. WF-025; IX-001/006/007/011–013/015/018/021; P5 cross-source visibility, P7 case/evidence continuity. |

## RT-026

**Key konseptual:** `historical.intake-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset/Procurement atau Super Admin context yang sah: sumber/kandidat/validasi/cleansing/hasil diterima atau ditolak. ID source-intake/kandidat/issue; format/source/version yang disepakati untuk request. |
| Entry / prerequisite / deep link | Intake, masalah validasi, calon tujuan/reconciliation atau search. Source provenance/authority dan later approved format required untuk penerimaan; deep link bukan izin import/cutover produksi. |
| Return / empty | Candidate/issue/compare/evidence → intake/kandidat yang sama; target accepted → sumber keputusan intake sah. Empty source/accepted count berbeda dari operation incomplete atau semua rejected; jelaskan hasil per lingkup. |
| Blocked / handoff | GAP-011/012/013/016/019: identity/trust/cutover unknown; plaintext credential never account import. WF-026; IX-003/006–009/011–013/015; P5 sensitive source, P6 long operation kelak, P7 issue/result continuity. |

## RT-027

**Key konseptual:** `reporting.context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Asset/Procurement sesuai report source dan Super Admin view sah: position/as-of, transaction range, periodic atau reconciliation report. Jenis + scope + temporal meaning; ID report/operation bila sudah ada. |
| Entry / prerequisite / deep link | Laporan, readiness/case, aset/ledger/KDP atau hasil operasi. Valid applicability/source/policy/cutoff required per family; deep link hasil mempertahankan provenance dan type/time asal. |
| Return / empty | Request/result/history/evidence → pilihan report yang sama; return ke case/record/source asal sah. Empty tidak mengubah missing policy menjadi laporan nol; tidak menganggap semua filter valid pada setiap family. |
| Blocked / handoff | GAP-005–010/013 sesuai report; BMU definition/form tetap unknown. WF-027; IX-018/011/012; P5 output access, P6 operation mechanism, P7 four temporal intents/empty/block/return. |

## RT-028

**Key konseptual:** `documents.context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Owning workspace dari parent: structured source/variant/generated draft/review/final evidence/version. ID parent domain, ID document/version bila ada; variant/source context untuk generate. |
| Entry / prerequisite / deep link | Paket/aktivitas/SPPBJ/kontrak/BAST/SPJ, report atau evidence/history. Parent, source/version dan applicability must distinguish; deep link final mempertahankan contextual provenance lama. |
| Return / empty | Generate/review/source-correction/evidence → parent/dokumen versi sama; editing source kembali ke kebutuhan dokumen asal dan menjelaskan apakah draft perlu dihasilkan ulang. Empty means no applicable output/evidence yet, not authority to choose historical template. |
| Blocked / handoff | GAP-013/017/019: numbering/clause/signer/variant unknown; final generation/issuance gate explicit. WF-028; IX-002/006–011/013; P5 document authority/visibility, P7 source/output/version return. |

## RT-029

**Key konseptual:** `search.allowed-contexts`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Workspace aktif atau cakupan lintas workspace yang sah: menemukan allowed record/context. Query + type/scope/filter/order/portion; ID target dan owning context untuk open. |
| Entry / prerequisite / deep link | Pencarian umum/quick find/daftar besar. Hasil/count/type/context hanya yang boleh dibaca; deep link query opsional, direct result mengikuti canonical RT objek. |
| Return / empty | Search → canonical detail RT → prior query/filter/portion where practical; cancel pencarian kembali ke pekerjaan pemanggil. Tanpa saved origin, owning list/context sah. Empty ialah tidak ada hasil dalam scope yang dipilih, bukan bukti record di scope lain tidak ada. |
| Blocked / handoff | Forbidden/unavailable result tidak bocorkan sensitive metadata atau membuka clone detail. IX-017; mendukung seluruh WF; GAP-014/016; P5 result scopes, P6 search mechanics/performance, P7 accessible find/open/return. |

## RT-030

**Key konseptual:** `history.parent-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Owning workspace: menelusuri action/version/before-after yang sah. ID parent domain dan history/change/version yang dimaksud. |
| Entry / prerequisite / deep link | Canonical detail, submission revision, document, correction, intake atau case. Deep link tetap mengikat history ke parent; authority/privacy per P5, bukan semua histori untuk semua pembaca. |
| Return / empty | History/compare → parent/versi asal sama; dari search → query asal. Empty menunjukkan jejak belum tersedia pada scope, bukan klaim tidak pernah berubah. Notfound versi tidak membuka current version sebagai pengganti diam-diam. |
| Blocked / handoff | Retention/reader/restore belum ditentukan P5/P9/P10; history viewer bukan restore/edit command. IX-012/013; seluruh WF material; BR-026/INV-017/018; P5 history scope, P7 parent/version continuity. |

## RT-031

**Key konseptual:** `evidence.parent-context`

| Aspek | Kontrak |
|---|---|
| Workspace / intent / target ID | Owning workspace: attach/view evidence dengan parent dan claim yang jelas. ID parent domain, ID bukti/versi bila ada; case + difference untuk reconciliation evidence. |
| Entry / prerequisite / deep link | Canonical parent, aktivitas, partisipasi, pemeriksaan, case, intake, document atau history. Deep link harus dapat mengenali provenance dan parent; file presence bukan valid authority. |
| Return / empty | Attach/view/cancel → parent tepat; Case → Difference → Evidence → same Difference/Case; Package → Activity → Evidence → same Activity/Package. Empty berarti bukti belum ada pada claim, bukan accepted claim. |
| Blocked / handoff | Parent/evidence unavailable tidak meminta repost seluruh source sensitif; reason/prerequisite/action ditentukan IX-011/EP-002/005. IX-011/012; seluruh WF evidence; GAP-013/017/019 sesuai claim; P5 privacy/readers, P7 contextual attach/view/return. |

## Kontrak crossing dan child-flow kritis

| Journey | Return/context yang harus dapat diverifikasi kelak |
|---|---|
| Paket → Aktivitas → Bukti | RT-005 → RT-006 → RT-031 → RT-006 → RT-005 dengan paket dan kegiatan yang sama; prior schedule context bila entry dari schedule. |
| Paket → Partisipasi → Pengiriman/Revisi → Profil | RT-005 → RT-008 → RT-007 → RT-008 → RT-005; revision selalu menunjuk submission/participation tepat, data company valid dipakai ulang. Vendor kembali ke daftar partisipasi asal bila entry bukan paket pusat; tidak memperoleh central-only data. |
| Aset → Movement → Bukti/History | RT-017 → RT-018 → RT-031/RT-030 → RT-018 → RT-017 untuk aset yang sama. Proposed movement tidak mengganti current position hanya karena dibuka. |
| Kasus → Selisih → Bukti → Sumber | RT-025 → RT-031 atau canonical RT sumber → kasus/selisih asal yang sama; scope/time perbandingan terjaga. Jika sumber forbidden, tetap case yang sah dengan penjelasan secukupnya. |
| Search → Detail → Search | RT-029 → canonical RT target → RT-029 dengan query/filter/order/portion asal bila praktis. Search cancel mengembalikan context pemanggil; entry deep link tanpa prior search kembali owning list. |
| Procurement → Draft Asset/Persediaan/KDP | RT-014/RT-005 → RT-016 → RT-017/RT-023/RT-024 yang applicable dan sah → RT-016/source yang sama. Bila target belum ada/blocked, tetap sumber/handoff; bila akses tujuan tidak ada, tidak switch sebagai bypass. |
| KDP → Completion Candidate → Draft Asset | RT-024 → RT-016 → RT-017 → RT-024/source sah; belum eligible/authority unknown tetap KDP/kandidat dengan alasan. |
| Super Admin → Canonical owning context | RT-002 → RT domain pemilik → RT-002/entry asal sah. Kemampuan lintas workspace dari OD-10 tidak menghasilkan salinan record, signer bisnis atau bypass domain rule. |

Setiap crossing membawa jenis/ID konteks, origin return yang sah dan hubungan sumber/hasil; tidak menetapkan transfer ownership resmi. Dua pengguna dengan akses berbeda dapat melihat outcome bersama yang konsisten tanpa melihat rincian terlindungi yang sama. Locale switch juga menjaga parent/workspace/filter/input context sejauh aman; bahasa dokumen resmi tidak diubah otomatis. P7/P8 kelak memverifikasi continuity/accessibility/locale dan nol dead interaction melalui journey kritis, setelah perencanaan serta execution diotorisasi; belum ada browser/runtime verification pada P3 ini.
