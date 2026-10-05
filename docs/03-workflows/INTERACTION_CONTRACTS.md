# Interaction contracts — PRANATA UNY

Status: APPROVED (APPR-004) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P3 | Preparation: [AUTH-008](../00-governance/APPROVAL_RECORDS.md#auth-008) (HISTORICAL / COMPLETED) | Approval/checkpoint: [APPR-004](../00-governance/APPROVAL_RECORDS.md#appr-004) / [AUTH-009](../00-governance/APPROVAL_RECORDS.md#auth-009)

Dokumen ini memiliki **21 kontrak interaksi reusable IX-001–IX-021** pada tingkat intent/hasil produk. Kontrak tidak menentukan komponen, form fields final, layout, API, penegakan izin, mekanisme retry atau state database. [STATE_TRANSITIONS](STATE_TRANSITIONS.md) memiliki wording state/transition WF; interaksi di bawah hanya dapat memakai transition yang applicable pada WF terkait. Tidak semua IX berlaku pada semua WF, dan generic review/accept tidak menambah official approval chain.

[ROUTE_CONTRACTS](ROUTE_CONTRACTS.md) memiliki entry/parent/return RT; [EXCEPTION_REVISION_PATTERNS](EXCEPTION_REVISION_PATTERNS.md) memiliki pola EP; [WORKFLOW_CONTRACTS](WORKFLOW_CONTRACTS.md) memiliki proses serta gate; [WORKFLOW_TRACEABILITY](WORKFLOW_TRACEABILITY.md) memiliki cakupan balik P1/P2. [BUSINESS_RULES](../02-domain/BUSINESS_RULES.md) tetap sole owner wording BR/INV; [DOMAIN_RESPONSIBILITIES](../02-domain/DOMAIN_RESPONSIBILITIES.md) memiliki makna tipe tanggung jawab. Kata berwenang berarti sesuai authority/assignment yang kelak divalidasi P5, bukan izin dari workspace atau label jabatan.

## Kontrak bersama semua IX

Setiap IX di bawah **wajib mewarisi** semua persyaratan ini, lalu menambahkan perilaku khusus pada entrynya. Aksi hanya menjadi available bila konteks, otorisasi dan prerequisite applicable terpenuhi. Aksi dependent yang belum tersedia menyatakan alasan/gate/next safe action atau tidak ditawarkan bila menampilkan keberadaannya sendiri membocorkan informasi; keputusan visibility detail P5. Tidak boleh ada visible/available action tanpa outcome dan return kontrak.

| Aspek | Kontrak reusable |
|---|---|
| Intent dan konteks | Sebelum bertindak, pengguna dapat mengenali objek/versi, parent, workspace/unit/scope dan akibat yang diminta. Draft, submitted, receipt, accepted content, evaluated, issued/signed, downstream definitive dan final report mempunyai claim berbeda sesuai WF, tidak dipersamakan. |
| Tanggung jawab | Nyatakan penanggung jawab tindakan saat ini, pihak yang ditunggu dan next action. Pihak dapat fungsi/unit contextual sesuai P2; individual belum ditugaskan berbeda dari official actor mapping belum disahkan. Bahan aman dapat tetap pending/unassigned bila WF mengizinkan; tampilkan alasan dan kebutuhan penugasan/validasi. Tidak mengarang aktor hanya agar label penuh. |
| Konfirmasi | Memerlukan konfirmasi yang menyebut target, scope dan akibat material untuk submit/resubmit, keputusan accept/reject, cancellation/supersession, issuance/finalization, classification atau accepted controlled change. Penyimpanan draft/read-only navigation tidak memerlukan persetujuan ulang hanya karena interaksi dipakai. Mengakhiri input yang belum tersimpan perlu menjelaskan pilihan simpan jika tersedia atau kehilangan input; aturan domain tetap berlaku. |
| Validasi | Jelaskan input/context/evidence yang belum memenuhi requirement tervalidasi, lokasi masalah yang dapat dicapai dan next safe action. Jangan meminta field/evidence/actor wajib dari template/filename atau guessed method. Unknown policy/applicability/identity ditangani sebagai gate EP-003/004/005/006, bukan error yang user dapat mengakali dengan nilai palsu. |
| Success | Nyatakan **apa yang tersimpan/diterima dan dalam lingkup apa**, identitas/konteks outcome, siapa perlu bertindak berikut, bukti/history dan route hasil. Draft tersimpan tidak sama dengan submitted; kiriman tercatat diterima pusat tidak sama dengan isi disetujui; operation accepted tidak sama dengan hasil selesai. |
| Failure | Jawab apa yang gagal, apakah data/tindakan tersimpan atau diterima, owner/pihak tunggu yang diketahui, apa yang dapat dilakukan berikut dan apakah retry aman secara konseptual. Jika hasil belum dapat dipastikan, nyatakan ketidakpastian lalu temukan receipt/history/status sebelum tindakan baru; tidak menyamarkan failure sebagai success. |
| Retry/recovery | Pembacaan dapat dicoba lagi bila akses/data tersedia. Validasi yang gagal tanpa accepted action dapat diperbaiki lalu dicoba lagi. Bila tindakan mungkin diterima, cek outcome yang sama dulu. Policy/actor/application gate memerlukan evidence/decision/assignment, bukan repeated retry. Detail concurrency/idempotency/retry algorithm P6; tidak didefinisikan di P3. |
| Preserved context / return | Pertahankan input kerja yang aman, parent/objek/versi, requirement/revision reason, locale, scope/filter/time dan origin return yang sah. Secret tidak disalin ke history/context return. Success/cancel/failure mempunyai jalur ke konteks yang sama atau RT fallback sah; child/panel/modal tidak boleh menghapus orientasi parent. |
| Evidence/history | Material accepted action mempunyai pelaku/tanggung jawab, waktu, objek/parent, alasan bila berlaku, sumber/versi/evidence dan before/after bila relevan menurut BR-026. Draft yang belum diterima tidak disebut final evidence. Retention/privacy/detail event list P5/P9; existing original/final source tidak ditimpa. |
| Accessibility/localization | Aksi/outcome/error/alasan blocked dapat dimengerti tekstual dalam bahasa Indonesia sederhana dengan padanan English kelak. Mekanisme action/cancel/return dapat diakses tanpa hover/color/motion; perubahan bahasa menjaga konteks dan tidak mengubah makna kode/dokumen resmi. P7/P8 menetapkan detail serta pengujian. |
| Official authority | Generic IX tidak dapat mengesahkan issuance/signature/award/capitalization/payment/signoff/cutover. Sumber pengesahan dan method/policy applicability tetap prerequisite WF; semua GAP tetap OPEN. Future institution services tetap optional/future dan tidak menjadi jalan wajib untuk action V1. |

Wording alur reusable adalah **PROPOSED_WORKFLOW**. Hasil reuse/action/return/accessibility yang langsung mengikuti OD/CAP/AC adalah **APPROVED_PRODUCT_FLOW**; batas BR/INV yang dirujuk mempertahankan authority P2 (**APPROVED_DOMAIN_INVARIANT** bila applicable). Official actions yang belum mempunyai required authority adalah **BLOCKED_BY_GAP**. Dokumen VERIFYING ini bukan izin menjalankan tindakan atau implementasi.

## IX-001

**Nama / intent:** Create bahan kerja/kandidat dalam konteks asal.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Jenis kandidat, parent/asal dan unit/scope relevan; operator/pengaju/aktor penyedia yang sah untuk konteks itu. Jika WF membolehkan bahan pending/unassigned, owner belum ditetapkan tetap terlihat; create tidak mensyaratkan actor approval yang diada-adakan. |
| Confirmation / validation | Draft create biasa tanpa konfirmasi material tambahan; create yang mempunyai business effect mengikuti gate WF. Validasi data/context/provenance yang diperlukan, duplicate ambiguity serta evidence applicable; jangan memilih identity rule/nomor dari kemiripan label. |
| Success / history | Kandidat tersimpan dan dapat dibuka dengan parent, provenance serta sisa pelengkapan/next action. State/klaim diterima/definitif tidak lahir dari create; hubungan source candidate tetap dapat ditelusuri. |
| Failure / retry | Input tidak valid → jelaskan belum tersimpan/diterima dalam lingkup yang diketahui, pertahankan input aman, perbaiki; outcome uncertain → cek kandidat/status sebelumnya sebelum create ulang. EP-002/005/006/010. |
| Preserved context / return | Success → candidate detail RT domain; cancel → parent/list asal dengan filter yang sama; failure tetap context create dengan return sah dan status simpan jelas. |
| Applicability / authority | WF-003–028 pada candidate yang relevan; RT domain terkait. PROPOSED_WORKFLOW, product CAP-04–17/AC-06–20; BR-024/029, INV-015. Official create/acceptance gate mengikuti GAP pada WF. |

## IX-002

**Nama / intent:** Edit atau melengkapi bahan kerja dengan asal/versi jelas.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Objek/versi/kedudukan draft atau ruang correction yang memang berlaku, parent dan editor yang sah; sumber data reuse dikenali. Existing company data berbeda dari package-specific fields; original/final evidence bukan draft bebas edit. |
| Confirmation / validation | Draft save biasa tanpa confirmation material; accepted change/affected output/source correction mengikuti IX-007/IX-013 dan WF. Keluar dengan input belum tersimpan menjelaskan simpan bila tersedia atau kehilangan input. Stale/revised source harus ditelaah kembali, bukan menimpa versi accepted secara diam-diam. |
| Success / history | Nyatakan bahan tersimpan dan apa yang belum submitted/accepted. Perubahan material mempertahankan versi/reason/source relation; output historis final tetap menjelaskan sumber lama dan kebutuhan generated draft baru bila applicable. |
| Failure / retry | Validasi menunjukkan hal yang perlu diperbaiki dan apakah ada bagian sudah tersimpan; data berubah/stale → buka versi current sah dan compare sebelum melanjutkan. Hasil simpan uncertain → temukan history/status dulu. EP-001/005/010. |
| Preserved context / return | Pertahankan input aman dan origin return; save → objek/versi yang sama; cancel → parent tepat dengan status input jelas; dari dokumen/partisipasi kembali kebutuhan dokumen/partisipasi asal. |
| Applicability / authority | WF-003–028 sesuai ruang edit yang applicable; IX-013/RT-030 untuk compare. PROPOSED_WORKFLOW; BR-007/009/025/026; INV-003/005/006/017/018; GAP-008/013/017/019 dan GAP WF tetap mengendalikan. |

## IX-003

**Nama / intent:** Submit bahan/kiriman yang siap ditinjau sesuai kontrak workflow.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Kandidat/versi, parent, requirement applicable, pihak pengirim dan penerima contextual yang diketahui atau status pending assignment yang dibolehkan WF. Submission profil penyedia mengikat perusahaan/PIC/versi profil tepat; kiriman partisipasi penyedia mengikat perusahaan–partisipasi–paket tepat. |
| Confirmation / validation | Konfirmasi target/isi/versi dan akibat pengiriman. Validasi evidence/requirement yang sudah sahih, applicability serta authority submit; kebutuhan official recipient/order yang unknown tidak diganti aktor universal. Tidak menciptakan deadline metode. |
| Success / history | Nyatakan kiriman tercatat/diterima untuk ditangani, receipt/versi dan next action/reviewer pending; jangan menyatakan accepted content/eligible/award. Provider dan central operator sah melihat outcome/receipt yang konsisten untuk kiriman sama. Bukti/history setiap submission retained. |
| Failure / retry | Missing evidence/validation → tidak mengklaim kiriman diterima; pertahankan bahan. Receipt uncertain → cek status/history kiriman yang sama sebelum submit lagi. Gate actor/method/policy → EP-003/004. |
| Preserved context / return | Success → parent submission/work context; penyedia tetap profil sama RT-007 atau partisipasi sama RT-008 sesuai WF pemanggil, dengan return ke partisipasi asal bila memicu update profil. Cancel → bahan yang sama; failure menyimpan orientation/requirement dan status simpan; child kembali RT parent. |
| Applicability / authority | WF-003/007/008/013/014/017–021/023/024/026 sesuai WF; RT terkait. PROPOSED_WORKFLOW; approved AC-06/09/10/19; BR-008, INV-004/015; GAP-001/003/014/017 serta gate WF. |

## IX-004

**Nama / intent:** Request revision dengan alasan, ruang perbaikan dan pihak tunggu.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Submitted/reviewed object/version, parent, penelaah/pihak pengembali yang sah; pihak diminta memperbaiki dibedakan dari current owner. Pemetaan belum diketahui ditulis unresolved, tidak invent reviewer/approver. |
| Confirmation / validation | Konfirmasi target/version serta akibat pekerjaan dikembalikan; alasan dan kebutuhan perbaikan jelas, evidence/requirement applicable. Jangan meminta unrelated company data valid, mandatory activity atau deadline tanpa dasar. |
| Success / history | Revision request tercatat, reason/scope/source version terbuka bagi pihak terkait sah, waiting party dan next correction action terlihat. Provider dan operator pusat melihat consistent revision outcome; request bukan cancellation/rejection final. |
| Failure / retry | Reason/context tidak valid → request belum diterima bila diketahui, perbaiki; changed target → re-review current version; outcome uncertain → history/receipt sebelum ulang. Gate unresolved tidak diubah menjadi perintah revisi agar user menebak policy. |
| Preserved context / return | Kembali object/review/partisipasi yang sama; reason dan relevant source accessible untuk reviser tanpa membuka protected data lain. Cancel menjaga review context dan tidak mengubah target. |
| Applicability / authority | WF-003/007/008 dan WF review lain hanya bila revision applicable; EP-001. PROPOSED_WORKFLOW; approved AC-06/09/10; BR-003/007/008/026; INV-003/004/017; GAP WF. |

## IX-005

**Nama / intent:** Resubmit perbaikan yang menjawab revision request.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Revision request/reason, prior submission/version, parent dan pengirim sah; reuse company data valid dibedakan dari perbaikan package-specific. |
| Confirmation / validation | Konfirmasi corrected version serta ruang perbaikan yang dikirim; validasi applicable requirement dan hubungan prior/new version. Belum memenuhi reason tetap ditunjukkan, bukan new submission tanpa asal. |
| Success / history | Revisi tercatat diterima untuk penelaahan ulang, prior version/reason/evidence/history terjaga. Provider dan central operator sah melihat receipt/status yang konsisten untuk revision sama; re-review berikut terlihat, bukan otomatis diterima/award. |
| Failure / retry | Revision invalid/incomplete → jelaskan belum diterima atau bagian simpan yang diketahui, input aman tetap ada. Receipt unknown → buka history/outcome versi sama sebelum pengiriman ulang. EP-001/002/010. |
| Preserved context / return | Success/cancel/failure tetap usulan/profil/partisipasi atau parent relevant yang sama; reviser dapat kembali ke reason dan relevant source. |
| Applicability / authority | WF-003/007/008 serta review WF yang mengizinkan revision; RT domain. PROPOSED_WORKFLOW; approved AC-06/09/10; BR-007/008/026; INV-003/004/006/017; GAP WF. |

## IX-006

**Nama / intent:** Review/check bahan, klaim dan bukti tanpa menyamakan review dengan approval.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Target/versi/dasar/lingkup review, evidence/provenance dan penelaah/checker contextual yang sah. SPPBJ checker berbeda dari preparer/issuer/signer; reconciliation reviewer berbeda dari penerima/signoff. |
| Confirmation / validation | Membaca bahan tidak membutuhkan confirmation; hasil review material dikonfirmasi target/scope sebelum dicatat. Validasi dasar applicable, completeness dan versi current; missing official criterion/actor menjadi named blocked gate. |
| Success / history | Hasil penelaahan serta evidence/reason tersimpan dan next action jelas: completeness recorded, request revision, atau decision candidate bila permitted. Review result tidak otomatis award/issued/signed/downstream definitive/reconciliation signoff. |
| Failure / retry | Bahan tidak tersedia/changed/criterion unknown → jelaskan review belum selesai/hasil belum accepted, siapa perlu menyediakan evidence/authority yang diketahui dan next safe action. Retry pembacaan setelah tersedia; hasil uncertain cek history. |
| Preserved context / return | Review → target/parent/version sama; evidence/history child → review asal; cancel tidak mengubah reviewed claim. |
| Applicability / authority | WF-003–028 pada review yang applicable; IX-004/007/008/011/013. PROPOSED_WORKFLOW; BR-004/011/023, INV-005/014; official scope/order/actor GAP-001–004/010/013/014/017–019 menurut WF. |

## IX-007

**Nama / intent:** Accept/approve claim tertentu hanya bila dasar dan authority applicable tersedia.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Target/versi, scope claim yang hendak diterima, dasar/authority/evidence dan aktor yang benar-benar berwenang dalam konteks tersebut. Penerimaan receipt, isi, record definitive, issuance/signature dan signoff tetap kegiatan berbeda; actor mapping official unresolved tidak diisi. |
| Confirmation / validation | Konfirmasi claim/target/version/effect dan affected downstream/output yang material. Semua gate applicable wajib terpenuhi; accepted-change/reconciliation/classification memerlukan reason/evidence sesuai WF. Unknown policy/actor bukan prerequisite lulus. |
| Success / history | Hanya claim/lingkup yang tervalidasi memperoleh accepted outcome/evidence/history. Nyatakan resulting record/status, next owner/waiting/action; approval tidak menyelesaikan claim lain atau mencairkan pembayaran. |
| Failure / retry | Gate/evidence/authority belum valid → acceptance tidak dijalankan, bahan tetap kandidat dengan named block. Changed target → review/compare ulang. Result uncertain → cek decision/history sama; tidak mengulang keputusan buta. |
| Preserved context / return | Success → keputusan/target dan parent yang sama; blocked/failure → target dengan prerequisite jelas; cancel → review asal tanpa accepted result. |
| Applicability / authority | WF yang mempunyai accepted claim applicable, terutama WF-010–012/014/016–021/023–028; RT domain. PROPOSED_WORKFLOW; safety INV-005/011–015/017/018; official acceptance **BLOCKED_BY_GAP** sesuai GAP WF sampai validated decision, bukan universal approver. |

## IX-008

**Nama / intent:** Reject claim/kandidat bila rejection memang diperbolehkan.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Target/versi, scope claim, dasar dan actor penolak yang sah; bedakan rejection dari revision request, cancellation dan import issue unresolved. |
| Confirmation / validation | Konfirmasi target/scope serta akibat material; reason dan authority/applicability rejection diperlukan. Tidak menjadikan inability to classify atau unknown identity sebagai official rejection tanpa rule. |
| Success / history | Hasil penolakan/reason/evidence/version retained dan pihak terkait sah melihat outcome; next allowed action/revision baru hanya bila WF mengizinkan. Parent/history tidak hilang. |
| Failure / retry | Authority/context/reason invalid → tidak claim rejected; status simpan diketahui dijelaskan. Changed target/rejection uncertain → review/history sebelum retry. Policy unknown tetap EP-003, bukan forced reject. |
| Preserved context / return | Keputusan → target/parent sama; cancel → review; rejected evidence/history tetap dapat dibuka sesuai P5 dan RT fallback. |
| Applicability / authority | WF review yang mengizinkan rejection, termasuk WF-008/010/017–021/023/026; RT domain. PROPOSED_WORKFLOW; BR-024/026, INV-015/017; official rejection gate GAP WF tetap OPEN. |

## IX-009

**Nama / intent:** Cancel pekerjaan atau supersede versi dalam lingkup yang diperbolehkan.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Target/versi, alasan, replacement bila supersession, affected linked context/output dan actor berwenang. Cancel input/navigation berbeda dari cancellation bisnis. |
| Confirmation / validation | Cancellation/supersession material mengonfirmasi scope/alasan/akibat/hubungan pengganti; authority legal/official tidak diada-adakan. Cancel editor dengan unsaved input menjelaskan data yang hilang atau pilihan save bila available. |
| Success / history | Cancellation/supersession dicatat dengan alasan dan jejak source/replacement/effect; existing output/evidence/history tetap explainable. Tidak delete history, otomatis reverse accounting, reopen period atau reactivate older work. |
| Failure / retry | Authority/effect belum sahih → cancellation ditahan, target tetap kedudukan sebelumnya; data/output changed → telaah dampak; outcome unknown → history dahulu. EP-007 dan related policy gate. |
| Preserved context / return | Business result → parent/target historis sama atau replacement yang jelas dengan return ke versi asal; cancel input → parent asal dengan status simpan. Deep-link historical target tidak silently redirect tanpa explanation. |
| Applicability / authority | WF yang memiliki cancellation/supersession applicable; RT domain/RT-030. PROPOSED_WORKFLOW; BR-026, INV-008/017/018; official effects GAP-001–004/008/010/013/017/018 menurut WF. Archive/restore/delete/anonymize tidak mendapat permission dari IX ini. |

## IX-010

**Nama / intent:** Generate draft dokumen, review sumber/varian dan menelusuri final evidence yang berlaku.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Parent domain, structured source/version, applicable variant, purpose dan preparer; checker/issuer/signer terpisah sesuai WF. Nomor/clause/signatory tidak diambil dari historical file tanpa authority. |
| Confirmation / validation | Draft generation ordinary tanpa confirmation issuance; finalization/issuance/signature material mengonfirmasi claim/context/authority tersendiri, IX-007 dan WF. Validasi applicable variant/source/evidence required values; unknown variant/field/formula trust → blocked GAP. |
| Success / history | Generated **draft** dapat ditelusuri ke source/version/variant dan review next action. Final evidence bila sah mempunyai claim terpisah; source change memperlihatkan draft/versi terdampak dan kebutuhan regenerate/review tanpa menimpa historical final. |
| Failure / retry | Missing source/variant/authority → nyatakan generation/issuance belum selesai dan data yang tetap tersimpan; failed/accepted operation mengikuti EP-010. Conflicting generated/uploaded/source → EP-005, bukan competing domain truth. |
| Preserved context / return | Draft/evidence → RT-028 lalu parent tepat; source correction → source context lalu kebutuhan dokumen semula; cancel → parent/versi asal. |
| Applicability / authority | WF-006/010–015/028 dan related outputs; RT-028/031. PROPOSED_WORKFLOW; approved CAP-08/AC-11, BR-009/010/011, INV-005/006/018; official variant/number/clause/signature **BLOCKED_BY_GAP** GAP-003/004/013/017/019. Digital signature remains FUTURE. |

## IX-011

**Nama / intent:** Attach atau open evidence untuk claim dan parent yang jelas.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Parent/claim, evidence/source/versi/keterbatasan dan actor yang sah untuk memberi/membaca bukti. Evidence internal, external, generated dan final/signed dibedakan; raw/source original tidak diedit. |
| Confirmation / validation | Read tanpa confirmation material; attachment biasa menyatakan target/type/provenance; replacement/supersession atau acceptance evidence yang material mengikuti IX-009/007. Validasi relevansi/applicability/required evidence yang sahih, tidak invent document checklist. |
| Success / history | Evidence terhubung ke claim/parent dan dapat ditemukan kembali; nyatakan tersimpan, review/penerimaan bila belum ada tetap belum. Attach tidak membuktikan authenticity/validity/claim completion. Context/provenance/versi/history dapat ditelusuri. |
| Failure / retry | Failed attach/open → jelaskan apakah bukti/association tersimpan atau belum diketahui; parent context tetap ada. Jika accepted state uncertain, cek evidence/history sebelum reattach. Missing/invalid evidence → EP-002; conflicting authority → EP-005. |
| Preserved context / return | RT-031 kembali exact parent, activity/participation/submission/case/difference yang sama; file failure tidak menutup semua context. Cancel attach kembali parent tanpa ambiguous accepted claim. |
| Applicability / authority | Seluruh WF evidence; RT-031/028/030. PROPOSED_WORKFLOW; BR-009/025/026, INV-005/006/017; GAP-013/017/019 sesuai claim. P5 privacy/evidence readers/retention; tidak publish raw source/secrets atau import credential. |

## IX-012

**Nama / intent:** View history/source/receipt/outcome yang boleh ditelaah.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Parent/object dan action/version/history scope; pembaca sah menurut P5, aktor/tanggung jawab tercatat berbeda dari viewer. |
| Confirmation / validation | Pembacaan tanpa confirmation; validasi parent/version/access. Tidak menawarkan restore/edit hanya karena history tersedia. |
| Success / history | Riwayat yang diizinkan menerangkan action, waktu, reason/source/evidence, prior/current/replacement dan accepted versus pending result. Receipt/provider revision yang sama mempertahankan outcome konsisten; view tidak mengubah status claim. |
| Failure / retry | Empty/partial/unavailable/forbidden dijelaskan sesuai RT contract tanpa false claim history complete/no change; safe read retry bila tersedia, tidak menampilkan detail protected sebagai error. |
| Preserved context / return | RT-030 → parent/versi/partisipasi/case semula; dari search → query semula bila praktis; unavailable parent memakai RT fallback sah. |
| Applicability / authority | Semua WF material; RT-030. PROPOSED_WORKFLOW; approved CAP-19/AC-10/22; BR-026, INV-004/006/008/017/018; P5/P9 menentukan visibility/retention, bukan full audit access sekarang. |

## IX-013

**Nama / intent:** Compare before/after, prior/new submission atau source/candidate/output yang relevan.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Dua konteks/versi yang dapat dibedakan, parent, waktu/scope dan tujuan compare; viewer/reviewer sah. Comparison kandidat bukan accepted change. |
| Confirmation / validation | Read-only compare tanpa confirmation; keputusan corrective/accept setelah compare memakai IX-007/008/015. Validasi kedua sumber tersedia dan meaning/scope comparability; unavailable source tidak diisi dengan angka/value guessed. |
| Success / history | Perbedaan yang dapat dijelaskan ditemukan dengan provenance/version/time, raw versus interpretation dan limitation; jika keputusan material dicatat, evidence/reason before/after retained. Tidak memilih source winner atau accounting effect dari diff saja. |
| Failure / retry | Stale/version/context unavailable → jelaskan compare incomplete dan next source/re-review action; safe read retry. Authority/identity ambiguous → EP-005/006; status sebelumnya tidak ditimpa. |
| Preserved context / return | Return ke revision/correction/review/candidate/document atau reconciliation case yang membuka compare; dua sumber dapat dibuka hanya dalam access scope dengan return ke compare yang sama. |
| Applicability / authority | WF-008/017–021/023–028 dan source revision WF lain; RT-030/031/domain. PROPOSED_WORKFLOW; BR-024–026/029, INV-005/006/015/017/018; GAP-008/010–013/017/019 sesuai comparison. |

## IX-014

**Nama / intent:** Classify downstream dengan rule/evidence yang sudah tervalidasi.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Source procurement/acceptance/handover atau KDP result, candidate, applicability policy/version/evidence serta contextual pihak keputusan dan pelengkap draft. Jika actor belum ditetapkan tampilkan pending/unresolved, jangan assign official classifier baru. |
| Confirmation / validation | Konfirmasi candidate, klasifikasi/tujuan, basis dan akibat reuse draft; validasi trigger/eligibility/classification rule yang berlaku. Tidak classify dari filename/account label/book value/BAST presence; unknown rule tetap needs classification/blocked. |
| Success / history | Keputusan klasifikasi yang sahih dan source evidence/version tercatat; bila eligible, draft jenis tujuan applicable dapat dilengkapi dengan provenance serta missing information. Hanya draft/claim yang benar-benar diterima disampaikan; Asset/Persediaan/KDP definitive tetap separate gate IX-007/WF. |
| Failure / retry | GAP authority/trigger belum terpenuhi → kandidat tetap belum terklasifikasi/blocked dengan next decision request; repeated retry tidak membantu. Result uncertain → cek handoff/decision history; konflik source → EP-005/008. |
| Preserved context / return | RT-016 → RT-017/023/024 yang sah → same source/handoff; target belum ada/forbidden tetap source context sah sesuai RT-016 fallback. |
| Applicability / authority | WF-014/016/024 pada classification/handoff yang applicable; RT-016. PROPOSED_WORKFLOW hipotesis OD-23/CAP-10/AC-13; BR-013/018/021, INV-006/011/012; definitive trigger/classification **BLOCKED_BY_GAP** GAP-008/009/014/018. |

## IX-015

**Nama / intent:** Resolve exception dalam lingkup yang dapat dipertanggungjawabkan.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Exception/difference/issue dan parent, cause/known uncertainty, applicable rule/source/evidence serta pihak tindak lanjut. Penerima/signoff dibedakan; unassigned/official owner unknown tampak explicit. |
| Confirmation / validation | Konfirmasi proposed disposition, target/scope serta efek accepted change; validation reason/evidence/identity/rule dan authority sesuai WF. Merekam penjelasan belum menyelesaikan selisih atau official signoff. |
| Success / history | Explanation/disposition terhubung, history before/after/decision evidence retained. Klaim resolved hanya bila WF prerequisite satisfied, status signoff terpisah; jika belum sahih, tersimpan sebagai penjelasan/pending review/authority. |
| Failure / retry | Missing evidence/authority/expected relationship → tetap exception/block, jelaskan next party/decision yang diketahui. Correction failure tidak mengubah source agar angka terlihat sama. Result uncertain → case history dahulu. |
| Preserved context / return | Exception → parent/case/difference/intake/source yang sama; evidence/compare anak kembali exception semula; cancel menjaga exception. |
| Applicability / authority | WF-005/013–028 pada exception applicable; EP-002–009. PROPOSED_WORKFLOW; BR-023/024/029, INV-014/015/017; GAP-005–013/018/019 sesuai source. No invented tolerance/signoff/cutover. |

## IX-016

**Nama / intent:** Switch workspace/scope dengan orientasi dan input kerja terjaga.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Akun/sesi local, workspace asal/tujuan yang tersedia, allowed unit/scope, object/intended destination opsional; navigation bukan formal responsibility change. |
| Confirmation / validation | Switch biasa tanpa confirmation bisnis; unsaved input menjelaskan save bila available/discard/remaining context sesuai IX-002. Nilai akses/scope destination dinilai ulang, bukan granted by switch. |
| Success / history | Context workspace berubah dan source/target orientation jelas, shared outcome tetap konsisten; tidak create copy record, reassign owner atau bypass permission/domain gate. Material associated action history hanya bila ada action tersendiri. |
| Failure / retry | Destination forbidden/unavailable → tetap source sah atau fallback RT-002; jelaskan input/action status yang diketahui. Tidak membocorkan target protected; safe navigation retry bila akses/availability valid. |
| Preserved context / return | Workspace asal/object/filter/input aman dapat ditemukan kembali; source unavailable → owning list/context sah dengan explanation. Handoff destination mengikuti IX-021. |
| Applicability / authority | WF-002/all crossing; RT-002. PROPOSED_WORKFLOW, approved AC-02/04; BR-002, INV-001/002; GAP-014/P5, P7 workspace/scope presentation. |

## IX-017

**Nama / intent:** Search/filter/order/navigate bagian daftar/open canonical detail.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Query, tipe, workspace/unit/scope, filter/order/portion dan caller context; hanya allowed result serta metadata/count yang boleh dibaca. |
| Confirmation / validation | Search/open read-only tanpa confirmation material; filter/time/type harus supported untuk context; clear/reset menyatakan lingkup yang berubah. Tidak menyatakan cross-scope record exists dari forbidden result. |
| Success / history | Result menjelaskan type/context dan scope, open canonical RT object; empty scope/filter jelas dengan next search/refine action. Read/search tidak mengubah business status dan tidak memerlukan full dataset di browser. |
| Failure / retry | Invalid filter/query/context → jelaskan input supported; data unavailable → query/context preserved dan safe read retry. Selected result forbidden/notfound → return prior search atau allowed owning list, bukan mirror detail. |
| Preserved context / return | RT-029 → detail → prior query/filter/order/portion where practical; cancel quick find → caller yang sama, keyboard/focus completion/return ditentukan P7. |
| Applicability / authority | Semua WF discovery; RT-029/domain. PROPOSED_WORKFLOW, approved CAP-18/20/AC-21/24; INV-001/004; GAP-014/016. P6 performance/search implementation, P7 find/navigation presentation tetap deferred. |

## IX-018

**Nama / intent:** Request/view laporan menurut temporal meaning yang applicable.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Report family + scope/sumber + temporal input valid + required policy/version/cutoff dan tujuan output; requester sah, policy/source custodian atau required receiver pending/unresolved dibedakan. |
| Confirmation / validation | Request ordinary tanpa confirmation official acceptance; final official claim/signoff material sesuai IX-007/WF. Validasi family/context: position/as-of satu cutoff; transaction range batas aktivitas; periodic period applicable; reconciliation scope/sumber/comparison time yang compatible. Tidak semua mode berlaku untuk semua laporan; unknown policy bukan tanggal default. |
| Success / history | Nyatakan request **diterima belum selesai** atau hasil **selesai dalam lingkupnya** sesuai actual outcome; identitas/status dapat ditemukan kembali. Output menyertakan scope/type/time/source/version/policy/exception serta empty meaning; tidak mengesahkan formula/signoff. Report provenance retained. |
| Failure / retry | Unresolved rule/cutoff/format → no false official numeric output; jelaskan block dan next evidence decision. No-data berbeda dari failed/blocked/pending. Long operation failure/status uncertain → EP-010 dengan result/status dahulu sebelum request ulang. |
| Preserved context / return | RT-022/027 → request/result → report choices serta source/case asal yang sama, time input masih valid; pengguna dapat melanjutkan kerja dan menemukan operasi/hasil kembali. |
| Applicability / authority | WF-022/025/027, terkait WF-023/024/026 outputs; RT-022/025/027. PROPOSED_WORKFLOW; BR-017/018/022/023/025, INV-013/014/018; GAP-005–010/013/016 sesuai report. P6 operation/performance dan P7 feedback kelak. |

## IX-019

**Nama / intent:** Local account entry/session/account-context/recovery reference.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Intent masuk/keluar/account update/recovery, local session dan intended work context; account eligibility/provisioning/recovery/security hanya references P5/GAP-015, bukan account-flow specification final. |
| Confirmation / validation | Local credential/session/recovery validation serta error disclosure mengikuti P5; tidak reusing plaintext legacy password, institutional identity atau paid channel mandatory. Account update/session termination dengan material effect menyampaikan akibat sesuai contract P5 kelak. |
| Success / history | Nyatakan hasil local entry/session/account/recovery **dalam lingkup yang benar-benar terpenuhi**, destination sah RT-001/002 dan next action. Recovery request accepted belum sama dengan password updated; error tidak memberi false success. Audit/secret handling ditetapkan P5/P9. |
| Failure / retry | Entry/recovery failure menyatakan langkah aman dan keterbatasan menurut P5 tanpa membuka account existence/secret secara spekulatif. P5 menentukan throttling/session/security; P3 tidak menetapkan interval/attempt/formula. Unknown recovery channel tetap GAP-015 dengan next operational decision. |
| Preserved context / return | Intended destination hanya setelah auth/access valid; cancel/failed entry tetap entry sah; secrets tidak dipreservasi sebagai return/history. Local auth source of product scope, detail P5. |
| Applicability / authority | WF-001; RT-001/002. APPROVED_PRODUCT_FLOW untuk local entry direction saja; exact behavior PROPOSED_WORKFLOW/P5 handoff; CAP-01/AC-01, BR-027, INV-016; GAP-014/015. |

## IX-020

**Nama / intent:** Schedule/record occurrence dan outcome kegiatan paket.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Paket, jenis/tujuan aktivitas, schedule/participants/materials, pihak kegiatan sesuai assignment; occurrence/outcome/evidence terpisah dari invitation dan jadwal. |
| Confirmation / validation | Draft schedule/edit biasa mengikuti IX-001/002; material schedule change/cancellation/outcome decision menyebut affected context/participants dan akibat. Validation applicability/participant/evidence yang memang berlaku; tidak wajibkan meeting/negotiation semua metode atau official SLA. |
| Success / history | Schedule atau occurrence/outcome tersimpan sesuai claim; pihak tunggu/tindak lanjut dan linked resulting evidence terlihat. Invitation tidak membuktikan attendance, activity completion tidak otomatis package approval/award. Perubahan/outcome material dapat ditelusuri. |
| Failure / retry | Invalid context/evidence/unknown method responsibility → jelaskan yang belum tersimpan atau belum accepted; safe correction/review, uncertain result cek activity/history. Future calendar/email absence tidak memblokir local schedule/evidence. |
| Preserved context / return | RT-006 → child invitation/evidence → activity/package sama; dari schedule list kembali rentang/context asal, tidak hilang package relation. |
| Applicability / authority | WF-006; konteks aktivitas anak WF-005/009 bila applicable; RT-006/005. PROPOSED_WORKFLOW, EVIDENCE_SUPPORTED activity examples; BR-006, INV-002; GAP-003/013/017. P7 calendar presentation remains deferred. |

## IX-021

**Nama / intent:** Open/record contextual handoff tanpa kehilangan sumber atau membuat permission grant.

| Aspek | Kontrak |
|---|---|
| Context / responsibility | Source object/version/claim/evidence, target type/context yang diketahui atau belum ada, proposed/validated responsibility serta source/target readers sah. Navigation crossing berbeda dari acceptance of business responsibility. |
| Confirmation / validation | Open target read-only tanpa confirmation; material handoff/classification/acceptance mengonfirmasi source/target/scope serta effect mengikuti IX-014/007 dan WF. Validate applicable trigger/classification/assignment; unresolved recipient tampil pending/unassigned bila WF membolehkan, official responsibility transfer tetap blocked. |
| Success / history | Target yang sah dibuka atau handoff/draft outcome tercatat menurut gate; source/target relation dan owner/waiting/next action dapat ditelusuri. Record candidate tidak auto definitive, opening target tidak mengubah ownership/permission. |
| Failure / retry | Target belum ada/blocked → source handoff context memberi prerequisite dan next safe decision; forbidden destination tidak bocorkan contents; target unavailable/data outcome uncertain → cek source/target status sebelum action baru. EP-003/008/010. |
| Preserved context / return | Canonical source ↔ RT-016 ↔ target RT-017/023/024 atau cross-process source/case; exact return dua arah bila allowed, fallback owning list/RT-002 bila source access berubah. Super Admin menggunakan canonical owning RT dan kembali origin sah. |
| Applicability / authority | WF-002/005/014/016/017/023–026 dan related crossings; RT-002/016/domain. PROPOSED_WORKFLOW; approved CAP-02/10/19, BR-013/027, INV-001/006/011/012; GAP-008/009/010/012/014/018 according handoff. |

## Pending-action dan feedback yang tidak memerlukan kanal eksternal

| Kondisi pekerjaan | Informasi yang harus dapat ditemukan dalam konteks sah |
|---|---|
| Pending action | Tindakan/target/requirement yang applicable, current owner atau pending assignment yang dijelaskan, bukti dan jalur RT/IX next action. Bukan task execution ID atau daftar tombol tanpa kontrak. |
| Waiting on | Pihak/kontribusi yang belum tersedia, sebab menunggu dan next action bagi pengguna saat ini atau alasan belum dapat bertindak. No waiting party berbeda dari waiting party unknown. |
| Revision required | Reason, scope/versi asal, pihak perbaikan dan next action IX-005; provider/company data valid lain tetap reusable. |
| Deadline approaching | Hanya deadline/context yang memang tervalidasi dan berlaku; bila deadline tidak diketahui/tidak applicable dijelaskan. Tidak ada angka SLA/deadline atau inferred legal limit baru. |
| Exception/blocked | Nama gate/GAP, what saved/accepted, siapa/keputusan yang dibutuhkan, bukti dan langkah aman. Tidak memakai warning semata sebagai izin melewati rule. |
| Completed within scope | Claim yang selesai, completion evidence, history/source dan next action/none reason; issuance, signoff, accepted content dan downstream final tetap terpisah. |

Ini adalah visibilitas pekerjaan in-app **APPROVED_PRODUCT_FLOW** OD-19/CAP-03/AC-05/10/20 dengan bentuk penyajian **PROPOSED_WORKFLOW**. Tidak menentukan penerima email/push, kanal notifikasi, delivery, prioritas SLA atau desain dashboard; P7 menentukan presentation, P5 reader scope, P9 channel operations bila kelak diotorisasi. [BR-027](../02-domain/BUSINESS_RULES.md#br-027) dan AC-26 menjaga operabilitas V1 tanpa future institution integrations.

P8 kelak mengikat verification yang bermakna pada setiap IX applicable: intent/context, valid success, invalid prerequisite/evidence, saved-versus-accepted failure, ambiguous outcome recovery, authority gate, history/privacy serta exact parent return. Critical UI journey memerlukan browser E2E saat application/execution tersedia; pada P3 ini hanya spesifikasi diperiksa dan belum ada runtime test atau klaim acceptance aplikasi PASS.
