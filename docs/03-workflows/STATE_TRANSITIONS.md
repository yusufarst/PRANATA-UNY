# State and transition contracts — PRANATA UNY

Status: APPROVED (APPR-004) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P3 | Preparation: [AUTH-008](../00-governance/APPROVAL_RECORDS.md#auth-008) (HISTORICAL / COMPLETED) | Approval/checkpoint: [APPR-004](../00-governance/APPROVAL_RECORDS.md#appr-004) / [AUTH-009](../00-governance/APPROVAL_RECORDS.md#auth-009)

Dokumen ini memiliki **rumusan state dan transition/action TR P3**. Tujuan, scope, trigger, DC/BR/INV/CAP/AC/GAP dan handoff fase tiap workflow dimiliki [WORKFLOW_CONTRACTS](WORKFLOW_CONTRACTS.md). [WORKFLOW_CATALOG](WORKFLOW_CATALOG.md) hanya indeks; RT/IX/EP memiliki kontrak navigasi/interaksi/pola kegagalan. State IDs adalah referensi lokal per workflow, bukan enum atau database fields. **WF-001 context-only; WF-002–WF-028 mempunyai state workflow/konteks, total 27 stateful workflows.**

## Semantik dan authority

| Lapisan | Makna / batas |
|---|---|
| Kondisi semantik domain | Klaim bisnis P2, misalnya bukti perolehan, pekerjaan belum selesai, record diterima, policy applicable. State workflow tidak menciptakan klaim itu hanya dari label. |
| State workflow | Kedudukan pekerjaan lokal di bawah, dengan entry, action/exit, owner/waiting dan bukti. State menyatakan scope pekerjaan, bukan bahwa pengadaan/accounting resmi telah lengkap. |
| Display status | Ringkasan tekstual keadaan yang dimiliki P7. Dapat memperlihatkan beberapa kontribusi/pending action sekaligus; tidak menggantikan source state/domain truth. Warna/motion bukan satu-satunya penjelasan. |

Authority tiap TR ditulis eksplisit: `APPROVED_PRODUCT_FLOW` untuk bagian hasil/interaksi P1 yang sudah disetujui; `APPROVED_DOMAIN_INVARIANT` untuk batas P2 yang dirujuk; `EVIDENCE_SUPPORTED` untuk keberadaan konsep teramati; `PROPOSED_WORKFLOW` untuk rangkaian kandidat; `BLOCKED_BY_GAP` untuk keputusan/aksi resmi yang belum dapat ditentukan. **Seluruh TR dengan BLOCKED_BY_GAP tidak tersedia sebagai aksi resmi sekarang.** Tujuan state bersyarat pada baris itu menunjukkan outcome yang perlu divalidasi, bukan official path/actor yang sudah diketahui. Guardian gap serta evidence yang diminta disebut per baris dan di kontrak induk. `PROPOSED_WORKFLOW` tidak menjadi SOP UNY karena dokumen ini kelak disetujui.

Kolom **owner; tunggu; next** merupakan hasil setelah tindakan. Responsible sebelum tindakan ada di kolom sendiri. `— (selesai scope)` berarti tidak ada waiting/next dalam scope itu; pekerjaan terkait lain tetap dapat tertunda. `fungsi belum dipetakan` adalah ketidakpastian yang harus ditampilkan, bukan assignment ke jabatan tertentu. Tidak ada deadline resmi diciptakan. Tindakan hanya untuk aktor yang benar-benar authorized menurut kontrak P5 kelak; workspace/role/position label tidak memenuhi guard authority.

Entry state selalu memerlukan konteks asal/identitas dan referensi evidence yang disebut; exit terjadi hanya lewat TR yang tercatat dan guard-nya terpenuhi. Allowed conceptual actions ada di kolom TR/exit. History dan provenance setiap tindakan material mengikuti DC-010/016/017/018, BR-026/INV-017. Failure **tidak** menandai target sebagai berhasil: pertahankan last confirmed state, nyatakan data disimpan/diterima atau belum/unknown, alasan, owner dan next yang aman. Missing evidence/authority dapat memindahkan pekerjaan ke state blocked yang ditentukan per WF. EP-001–EP-010 memiliki rincian recovery; technical retry/consistency P6. Tidak ada generic transition tak tercatat yang memberi jalan melewati guard.

Local draft withdrawal hanya menghentikan bahan kerja. Recording keputusan external cancel/supersede memerlukan provenance/authority yang tervalidasi; cancellation resmi yang belum sahih tetap blocked. Cancel/supersede tidak menghapus historical evidence atau otomatis membalik akibat hilir. Closed/accepted business records memakai controlled-change workflow; tidak kembali menjadi draft bebas. Report/request accepted berbeda dari completed; accepted-operation cancellation belum dispesifikasikan P6 dan tidak dianggap available.

<a id="wf-001"></a>

## WF-001 — Konteks entry, tanpa state machine autentikasi

P3 merujuk hasil kontrak akun P5 untuk sesi sahih/gagal/logout/recovery; tidak mendefinisikan state keamanan baru. Konteks entry menyimpan tujuan aman yang diketahui, pengguna memegang next action, dan bantuan yang belum memiliki kanal sahih tetap menunggu GAP-015. Tidak ada data credential menjadi evidence.

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-001 | APPROVED_PRODUCT_FLOW | Entry → ajukan masuk lokal → hasil entry kontrak P5 | Pemilik akun | Kontrak akun/sesi sahih; tujuan aman, tanpa credential history | Bila sahih, navigasi WF-002/tujuan berakses | Pengguna; hasil entry; pilih/lanjut konteks | Gagal/unknown tidak mengaku signed in; EP-003, tetap entry |
| TR-002 | APPROVED_PRODUCT_FLOW | Konteks akun → keluar → entry | Pemilik akun | Hasil penghentian sesi sesuai P5 | Akhiri konteks penggunaan sesi | Pengguna; tidak ada; entry bila ingin kembali | Hasil unknown dijelaskan, P5 menentukan recovery |
| TR-003 | BLOCKED_BY_GAP | Entry/profil akun → minta recovery/perubahan akun → konteks bantuan P5 | Pengguna/pengelola akun tervalidasi | GAP-015 eligibility, provisioning, channel; evidence hasil tanpa rahasia | P3 hanya merujuk scope bantuan, tidak membuat akun/policy | Pengguna; pengelola akun/kanal belum diputus; minta kontrak sahih | Tidak memaksa UNY/email berbayar; EP-003 |

<a id="wf-002"></a>

## WF-002 — Konteks navigasi ruang kerja

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF002-S1 / Pilih konteks | Entry sahih; belum memilih tujuan operasional | TR-011/014; exit bila tujuan sahih | Pengguna; pilihan sendiri; available context menurut P5 |
| WF002-S2 / Konteks aktif | Workspace/record tujuan dapat diakses | TR-012/013/014; exit lewat navigasi yang sahih | Pengguna; tidak ada pihak bisnis baru; tujuan/asal jelas |
| WF002-S3 / Tujuan tidak tersedia | Akses/konteks tujuan gagal atau tidak diketahui | TR-014, atau TR-011/012 setelah validasi | Pengguna; penyelesaian akses/konteks; alasan tanpa protected details |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-011 | APPROVED_PRODUCT_FLOW | S1/S3 → pilih tujuan → S2 | Pengguna | Tujuan berakses tervalidasi P5 | Context navigation saja | Pengguna; tidak ada; buka pekerjaan applicable | Tujuan gagal → S3; IX-016/RT-002 |
| TR-012 | APPROVED_PRODUCT_FLOW | S2/S3 → pindah workspace → S2 | Pengguna | Akses tujuan, origin dan unsaved-work context | Record shared sama, tanpa grant | Pengguna; tidak ada; lanjut tujuan | Gagal → S3/origin sahih; jangan hilangkan draft tanpa penjelasan |
| TR-013 | APPROVED_PRODUCT_FLOW | S2 → buka owning context lintas workspace → S2 | Pengguna berakses | Record dan source context sahih, termasuk Super Admin | Buka canonical target dengan backlink | Pengguna; tidak ada; kerja/lihat record | Forbidden/not-found tidak bocorkan scope; RT-002/IX-021 |
| TR-014 | APPROVED_DOMAIN_INVARIANT | S1/S2/S3 → cancel/return tujuan → origin sahih S1/S2 | Pengguna | Origin diketahui, INV-001 | Pilihan tidak memberi izin | Pengguna; tidak ada; pilih/lanjut origin | Bila origin tidak tersedia, entry/context aman disertai alasan |

<a id="wf-003"></a>

## WF-003 — Usulan unit dan kelengkapan intake

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF003-S1 / Bahan usulan | Kebutuhan/origin unit tersedia, belum dikirim | TR-021/022/026; exit lewat pengiriman/withdrawal | Pengaju; kelengkapan sendiri; bahan kebutuhan/source |
| WF003-S2 / Kiriman intake tercatat | Submission atau revisi mempunyai receipt history | TR-023/024/025/026; exit hasil kelengkapan | Penerima intake; penelaahan; bukti kiriman/receipt |
| WF003-S3 / Perlu perbaikan usulan | Kekurangan/pertanyaan dijelaskan untuk pengaju | TR-021/022/026; exit revisi terkirim | Pengaju; bukti/perbaikan; reason dan versi awal |
| WF003-S4 / Hasil intake tercatat | Scope kelengkapan/receipt sudah dijelaskan, bukan approval resmi | TR-025/026, hubungan WF-004/005 conditional | Penanggung jawab intake; official routing GAP-001 bila dependent; hasil/evidence |
| WF003-S5 / Routing resmi belum tervalidasi | Approval/routing resmi diperlukan tetapi rule/actor unknown | TR-021/025/026; official advancement unavailable | Fungsi intake belum dipetakan; custodian GAP-001; named gap/reason |
| WF003-S6 / Bahan dihentikan/digantikan | Withdrawal bahan lokal atau validated decision direkam | History/view; tidak reopen otomatis | Pemilik bahan/keputusan; — (selesai scope); reason/impact/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-021 | PROPOSED_WORKFLOW | Source/S1/S3/S5 → create/lengkapi/edit bahan → S1/S3/S5 | Pengaju/operator ditugaskan | Origin tetap, alasan/perubahan/evidence | Bahan lengkap tanpa membuat origin baru | Pengaju; missing evidence; kirim bila siap | EP-002/005; official blocked tidak hilang karena edit |
| TR-022 | APPROVED_PRODUCT_FLOW | S1/S3 → kirim/resubmit → S2 | Pengaju | Kelengkapan yang applicable, revision link dan receipt | Kiriman unit sama tertelusur | Penerima intake; telaah; periksa scope | Receipt unknown/gagal tidak accepted; EP-001/002 |
| TR-023 | APPROVED_PRODUCT_FLOW | S2 → minta perbaikan → S3 | Penelaah kelengkapan ditugaskan | Kekurangan/next yang jelas, bukti review | Pekerjaan kembali pengaju | Pengaju; perbaikan/bukti; resubmit yang sama | Reason gagal disimpan: S2, tidak klaim permintaan dikirim |
| TR-024 | PROPOSED_WORKFLOW | S2 → catat hasil kelengkapan/receipt → S4 | Operator/penelaah intake | Review basis dan hasil tersimpan | Selesai scope intake, belum official approval | Penanggung jawab intake; gate berikut bila applicable; asosiasi planning/paket conditional | Missing evidence tetap S2/3; EP-002 |
| TR-025 | BLOCKED_BY_GAP | S2/S4/S5 → official routing/approval → S5 selama unknown | Fungsi resmi belum dipetakan | GAP-001/002/003/013 current path, actors, delegation, exceptions | Tidak membuat approval/paket dari tebakan | Fungsi intake; custodian routing; validasi keputusan | EP-003; hanya scope data/evidence unaffected dapat dilanjutkan |
| TR-026 | PROPOSED_WORKFLOW | S1–S5 → withdraw bahan/catat validated supersession → S6 | Pemilik bahan/pihak keputusan sahih | Reason, source authority bila official, impact hilir | Hentikan scope lokal/rujuk keputusan, history retained | Pemilik keputusan; affected follow-up bila ada; lihat impact | Official cancellation unknown blocked GAP-001/003; EP-007 |

<a id="wf-004"></a>

## WF-004 — Referensi RUP

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF004-S1 / Hubungan perlu dilengkapi | Usulan/paket diketahui, referensi missing/unassessed | TR-031/033/034 | Operator planning context; source reference; origin/evidence |
| WF004-S2 / Referensi tertaut dengan batas | Reference/version terhubung dengan assessment lokal | TR-032/033/034 | Operator context; review applicability bila perlu; provenance/version |
| WF004-S3 / Makna/otoritas referensi belum sahih | Validity/handoff/publication rule diperlukan tapi unavailable | TR-031/033/034 | Fungsi planning belum dipetakan; custodian GAP-002/013; named gate |
| WF004-S4 / Asosiasi digantikan | Hubungan lama dicabut/digantikan dalam scope lokal | History; referensi konteks baru: TR-031 dari S1/S3 asosiasi baru, bukan exit langsung S4 | Operator context; — (selesai asosiasi lama); reason dan relation |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-031 | PROPOSED_WORKFLOW | S1/S3 → tautkan/lengkapi reference → S2 atau S3 | Operator ditugaskan | Origin, locator/version, assessment validity | Local association, bukan publication | Operator; review reference; telaah applicability | Missing source S1/3; EP-002/005 |
| TR-032 | PROPOSED_WORKFLOW | S2 → catat penilaian association → S2 | Penelaah context | Evidence/source current applicability | Jelaskan rujukan/uncertainty bagi paket | Penanggung jawab paket; relevant gate bila required; lanjut yang sahih | Tidak mengklaim RUP publication; EP-003 |
| TR-033 | BLOCKED_BY_GAP | S1/S2/S3 → prepare/publish/revise/cancel RUP resmi → S3 selama unknown | Creator/approver/operator/publisher belum dipetakan | GAP-002/013 current procedure/change responsibility | Local tracking tidak melakukan SiRUP action | Fungsi planning; custodian; validasi official handoff | Missing reference hanya menghalangi package bila validated rule mensyaratkan |
| TR-034 | PROPOSED_WORKFLOW | S1/S2/S3 → koreksi/supersede asosiasi → S4 | Operator authorized untuk hubungan lokal | Reason/version/source/impact | Rujukan lama tertelusur, tanpa cancel RUP eksternal | Operator; affected package review; tautkan pengganti | EP-007; official cancellation tetap GAP-002 |

<a id="wf-005"></a>

## WF-005 — Koordinasi paket dan guards metode

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF005-S1 / Konteks paket disiapkan | Origin dan bahan paket tersedia, applicability belum dinilai semua | TR-041/042/045 | Operator paket; sumber/context; origin/versi |
| WF005-S2 / Pekerjaan applicable dipantau | Ada extension yang tervalidasi/dapat direkam dalam scope-nya | TR-041/042/043/044/045 | Pemilik current work per extension; kontribusi applicable; progress/evidence |
| WF005-S3 / Gate metode belum tervalidasi | Extension/urutan/actor needed masih unknown | TR-041/042/044/045 | Fungsi paket belum dipetakan; custodian metode; explicit GAP-003/017 |
| WF005-S4 / Lingkup paket dinyatakan selesai bersyarat | Completion method-specific benar-benar tervalidasi dan evidence diterima | TR-045/controlled revision conditional | Penanggung jawab sesuai rule; — (selesai scope); completion evidence |
| WF005-S5 / Lingkup dihentikan/digantikan | Local draft withdrawal/validated official decision diketahui | History/impact follow-up | Pihak keputusan; affected owner bila perlu; reason/source/impact |

Setiap extension action/activity/document yang ditautkan harus mempunyai **applicable methods (bila validated), conditional/unknown applicability, required/optional/not applicable/unknown, source/version dan unresolved GAP**. Untuk Pengadaan Langsung dan Tender sebagai keluarga awal P1, current official procedure/routing/actors/outputs belum tervalidasi: seluruh official requirements/order masih unknown. Common context/traceability berlaku sebagai hasil produk, bukan rule bahwa semua extension wajib. Unknown berbeda dari not applicable; not applicable harus mempunyai dasar tervalidasi. Tender-labelled PROC-21 dengan direct-procurement body tetap conflict, tidak menentukan metode. Negosiasi, SPPBJ, aktivitas atau dokumen tidak otomatis gate bagi metode lain. Guard ini dirujuk setiap WF procurement; tidak menetapkan scoring/deadline/nomor.

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-041 | PROPOSED_WORKFLOW | Source/S1/S2/S3 → create/lengkapi source/context → state scope sama | Operator paket | Origin, method context, KAK/spec/HPS jika applicable, versi | Evidence context diperbaiki, final lama tidak berubah otomatis | Pemilik pekerjaan context; kekurangan; lengkapi/telaah | EP-001/002/005, impact extension visible |
| TR-042 | PROPOSED_WORKFLOW | S1/S2/S3 → buka/tautkan extension applicable → S2 atau S3 | Penanggung jawab paket/extension | Method guard di atas + state/authority WF target | Pekerjaan terhubung tanpa universal sequence | Owner extension; kontribusi/authority; next WF target | Unknown official applicability S3; EP-004 |
| TR-043 | PROPOSED_WORKFLOW | S2 → catat receipt/outcome extension → S2 | Operator/owner extension | Related TR target sukses, evidence/version | Shared status package merujuk hasil yang sama | Owner next applicable work; pihak relevant; tindak lanjut | Tidak auto-advance extension lain; EP-005 |
| TR-044 | BLOCKED_BY_GAP | S2/S3 → official advancement/closure → S3 selama unknown; S4 hanya setelah validation | Actor/order belum dipetakan | GAP-003/013/017 completion/order/required evidence dan assigned authority | Completion conditional, tidak available sekarang | Fungsi official work; custodian metode; sahihkan gate | EP-003/004; incomplete extension tetap outstanding |
| TR-045 | PROPOSED_WORKFLOW | S1–S4 → withdraw draft/record validated cancel-supersede → S5 | Pemilik bahan/pihak keputusan sahih | Reason, official authority jika relevant, downstream impact | History/context cancellation tertelusur | Decision owner; impacted owners; follow-up | Official cancel unknown blocked GAP-003/017; EP-007 |

<a id="wf-006"></a>

## WF-006 — Aktivitas paket

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF006-S1 / Kegiatan direncanakan | Paket/tujuan/activity context tersedia | TR-051/052/055 | Pengelola kegiatan; peserta/bahan; source/tujuan |
| WF006-S2 / Jadwal dan peserta tertaut | Jadwal/participant set dan invitation applicability diketahui | TR-051/053/055 | Pengelola kegiatan; kejadian/kontribusi peserta; schedule/undangan |
| WF006-S3 / Hasil perlu dicatat/dilengkapi | Kegiatan terjadi atau hasil perlu evidence/revision | TR-053/054/055 | Pencatat hasil; evidence/penjelasan; occurrence/context |
| WF006-S4 / Hasil kegiatan tertelusur | Bukti hasil dan next action dalam scope tersedia | TR-054/055; linked next applicable WF | Owner follow-up; kontribusi berikut jika ada; outcome/evidence |
| WF006-S5 / Jadwal/lingkup dihentikan | Local cancellation/validated outcome non-occurrence recorded | History, new revision context bila applicable | Pengelola kegiatan; affected participants/follow-up; reason/version |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-051 | PROPOSED_WORKFLOW | Package context/S1/S2 → create/edit/reschedule → S1/S2 | Pengelola kegiatan ditugaskan | Package link, category/tujuan, schedule/participants, reason | Activity context retained | Pengelola; bahan/participants; lengkapi | Mandatory official activity unknown remains GAP-003/017 |
| TR-052 | PROPOSED_WORKFLOW | S1 → record jadwal/undangan applicable → S2 | Pengelola/penyusun | Guard WF-005, variant WF-028 bila generated | Schedule/invitation evidence link, bukan attendance | Pengelola; kejadian/respon yang diminta; pantau | EP-002/004; no email dependency |
| TR-053 | PROPOSED_WORKFLOW | S2/S3 → record occurrence/outcome evidence → S3/S4 | Pencatat hasil | Actual occurrence, outcome, participants bila known, evidence | Kegiatan punya hasil/context | Pencatat/follow-up owner; missing evidence; lengkapi/lanjut | Undangan bukan occurrence; missing evidence S3 |
| TR-054 | PROPOSED_WORKFLOW | S3/S4 → complete/correct hasil scope → S4 | Penanggung jawab kegiatan | Before/after/reason, outcome dan next action | Completion activity tidak meluluskan paket | Follow-up owner; contribution jika ada; applicable WF | Official acceptance/gate unknown ditahan EP-003/004 |
| TR-055 | PROPOSED_WORKFLOW | S1–S4 → cancel/supersede local activity scope → S5 | Pengelola/pihak keputusan sahih | Reason, consequences/participants, authority bila official | History dan package link retained | Pengelola; affected follow-up; jelaskan hasil | Official cancellation unknown blocked; EP-007 |

<a id="wf-007"></a>

## WF-007 — Profil penyedia dan pembaruan

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF007-S1 / Bahan profil/pembaruan | New provider candidate atau existing profile perlu update | TR-061/062/066 | Aktor penyedia; bahan valid; company/PIC/source version |
| WF007-S2 / Bahan profil diterima untuk telaah | Submission completeness tersedia, belum eligibility | TR-063/064/065/066 | Penelaah profil; assessment; receipt/version |
| WF007-S3 / Perlu pembaruan dengan alasan | Missing/invalidity known atau representation/eligibility unknown | TR-061/062/064/066 | Penyedia atau fungsi belum dipetakan; bukti/custodian; reason/GAP |
| WF007-S4 / Konteks reuse dijelaskan | Validity/applicability yang sahih diketahui dalam scope tertentu | TR-061/065/066; participation conditional | Pemilik profile context; update bila required; validity/version evidence |
| WF007-S5 / Bahan/versi digantikan | Local proposal withdrawn/superseded, prior versions available | History; referensi konteks baru: TR-061 dari Company/PIC context/S1/S3/S4 proposal baru, bukan exit langsung S5 | Pemilik bahan; — (scope lama); reason/relation/impact |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-061 | APPROVED_PRODUCT_FLOW | Company/PIC context/S1/S3/S4 → create/lengkapi hanya bahan relevant → S1/S3 | Aktor penyedia sesuai representation | Company/PIC/account terpisah, existing valid values, update reason | Reuse tidak unrelated re-entry; historical participation retained | Penyedia; bukti yang perlu; ajukan update | EP-002/006; name similarity bukan merge |
| TR-062 | PROPOSED_WORKFLOW | S1/S3 → kirim bahan profil → S2 | Aktor penyedia | Source/version dan receipt, allowed representation kelak | New/update proposal tertelusur | Penelaah; review; telaah completeness | Receipt failure tidak eligibility; EP-002/005 |
| TR-063 | APPROVED_PRODUCT_FLOW | S2 → minta update/perbaikan → S3 | Penelaah profil ditugaskan | Reason/required correction dan evidence | Pending action penyedia jelas | Penyedia; perbaikan; resubmit relevant | Tidak minta semua company values ulang |
| TR-064 | BLOCKED_BY_GAP | S2/S3 → nyatakan eligibility/validity/representation → S3 selama unknown; S4 conditional | Actor resmi belum dipetakan | GAP-014/015/017 rules/document validity/reuse scope | Tidak membuat legal eligibility dari file | Fungsi profile review; custodian; validasi scope | EP-003; open self-registration tidak disimpulkan |
| TR-065 | APPROVED_PRODUCT_FLOW | S2/S4 → tautkan informasi relevant pada partisipasi → S4 scope reuse known | Operator/aktor penyedia | Validity/applicability sahih atau uncertainty clear; WF-008 guards | Reused version retained, package-specific terpisah | Owner participation; package action; WF-008 | Unknown official requirement tetap blocked |
| TR-066 | PROPOSED_WORKFLOW | S1–S4 → withdraw/supersede bahan profil → S5 | Pemilik bahan/pihak decision sahih | Reason/source/version dan participation impact | History preserved, bukan delete company | Pemilik profile; impacted work; telaah links | Accepted validity reversal unknown blocked GAP-014/017 |

<a id="wf-008"></a>

## WF-008 — Kiriman/revisi partisipasi penyedia

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF008-S1 / Tindakan kiriman perlu disiapkan | Participation dan applicable action diketahui | TR-071/072/077 | Penyedia; bahan khusus paket; valid reused profile reference |
| WF008-S2 / Kiriman diterima dalam scope receipt | Submission/revision mempunyai receipt history yang sama bagi pihak terkait | TR-073/074/076/077 | Pusat relevan/penelaah; review; receipt/source/version |
| WF008-S3 / Revisi diminta | Reason/required correction merujuk kiriman sebelumnya | TR-071/075/077 | Penyedia; correction evidence; revision request |
| WF008-S4 / Hasil tindakan/kiriman tercatat | Outcome scope tersimpan; bukan otomatis evaluation pass/award | TR-076/077; applicable WF-009 | Owner next work; kontribusi applicable; outcome/evidence |
| WF008-S5 / Keputusan metode/otoritas belum sahih | Official action/acceptance applicability unknown | TR-071/076/077; official progression unavailable | Fungsi belum dipetakan; GAP-003/014/017 custodian; reason |
| WF008-S6 / Lingkup bahan/keputusan dihentikan | Draft withdrawn atau validated supersession/cancellation recorded | History/affected follow-up | Pihak decision; impacted work; reason/evidence |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-071 | APPROVED_PRODUCT_FLOW | Participation context/S1/S3/S5 → create/lengkapi bahan relevant → state scope sama | Aktor penyedia | Participation/company/package identity, reuse version, package-specific values | Hanya missing/revised inputs dilengkapi | Penyedia; bukti specific; kirim/resubmit applicable | Missing authority S5 tetap blocked; no unrelated re-entry |
| TR-072 | APPROVED_PRODUCT_FLOW | S1 → submit applicable → S2 | Aktor penyedia | Applicability/required evidence dan receipt tersimpan | Submission/history outcome sama referensinya | Pusat relevan; review; telaah | Failure/unknown receipt tetap unconfirmed; EP-002/005 |
| TR-073 | APPROVED_PRODUCT_FLOW | S2 → review dan minta revision → S3 | Penelaah pusat ditugaskan | Reason/action visible, revision allowed scope, original submission reference | Provider action dan pusat history consistent | Penyedia; correction; perbaiki/resubmit | Protected detail penyedia lain excluded; EP-001 |
| TR-074 | APPROVED_PRODUCT_FLOW | S2 → record review/hasil scope → S4 | Pusat relevan/penelaah | Basis scope hasil dan evidence, shareable outcome | Provider/pusat melihat consistent outcome/history menurut akses | Owner next work; applicable contribution; lihat hasil/lanjut | Official approval tidak disimpulkan; EP-003/004 |
| TR-075 | APPROVED_PRODUCT_FLOW | S3 → resubmit revision → S2 | Aktor penyedia | Revision link/reason/changed evidence, reused valid company data, receipt | Old/new submissions and receipt retained | Pusat relevan; re-review; telaah revision sama | Receipt unknown tidak resolved; original retained |
| TR-076 | BLOCKED_BY_GAP | S2/S4/S5 → official accept/reject/progression → S5 selama unknown | Fungsi metode/decision belum dipetakan | GAP-003/014/017 stages/deadline/authority/applicability | Tidak memperlakukan receipt sebagai legal result | Fungsi review; custodian metode; validasi guard | EP-003/004, unaffected receipt/history tetap terlihat |
| TR-077 | PROPOSED_WORKFLOW | S1–S5 → withdraw draft/record validated cancel-supersede → S6 | Pemilik bahan/pihak decision sahih | Reason, source authority dan impact on package outcome | History consistent kedua konteks, no delete | Owner decision; related work; tindak lanjut | Official withdraw/retract unknown blocked; EP-007 |

<a id="wf-009"></a>

## WF-009 — Review, klarifikasi dan negosiasi

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF009-S1 / Bahan penelaahan tertaut | Submission/context tersedia | TR-081/082/084/085 | Penelaah; basis review; source/version |
| WF009-S2 / Kontribusi penjelasan diperlukan | Clarification/revision/negotiation contribution applicable requested | TR-083/084/085 | Pihak ditugaskan menjawab; jawaban; pertanyaan/alasan |
| WF009-S3 / Hasil penelaahan tertelusur | Review/answer/negotiation evidence recorded dalam scope | TR-081/083/084/085 | Reviewer/next work owner; decision bila applicable; hasil/source |
| WF009-S4 / Dasar keputusan/metode belum sahih | Criteria/applicability/actors/order belum tersedia | TR-081/084/085 | Fungsi official review belum dipetakan; GAP-003/013/017; uncertainty |
| WF009-S5 / Scope penelaahan digantikan | Valid withdrawal/supersession of work recorded | History/follow-up | Decision owner; affected work; reason/version/impact |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-081 | PROPOSED_WORKFLOW | S1/S3/S4 → record review context/evidence → S1/S3/S4 | Penelaah ditugaskan | Submission/version, criterion basis bila known | Evidence assessment tertelusur, no score formula | Penelaah; missing basis/evidence; lanjut scope sahih | Unknown official criteria S4; EP-003/004 |
| TR-082 | PROPOSED_WORKFLOW | S1 → request clarification/allowed revision/negotiation contribution → S2 | Penelaah/owner action sahih | Method guard, scope question/change allowed dan recipient | Requested contribution clear; WF-008 jika revision | Pihak jawaban; response/evidence; jawab/revise | Tidak mandatory negotiation; unknown guard S4 |
| TR-083 | PROPOSED_WORKFLOW | S2/S3 → record response/review result → S3 | Responder/penelaah | Question-response link, evidence, revised-source relation | Klarifikasi/negosiasi punya outcome separate | Penelaah/next owner; decision applicable; telaah/taut WF-010 | Answer tidak otomatis revised offer; EP-005 |
| TR-084 | BLOCKED_BY_GAP | S1–S4 → official evaluation/negotiation outcome → S4 selama unknown | Aktor/decision authority belum dipetakan | GAP-003/013/017 criterion/method/current procedure | Official result tidak available tanpa rule | Fungsi evaluation; custodian; sahihkan dasar | No score/bobot/deadline invented; EP-003/004 |
| TR-085 | PROPOSED_WORKFLOW | S1–S4 → withdraw local scope/record validated supersession → S5 | Work owner/decision sahih | Reason/source/impact evidence | Preserve review/response histories | Decision owner; impacted work; tindak lanjut | Official annulment unknown blocked; EP-007 |

<a id="wf-010"></a>

## WF-010 — Bahan dan keputusan hasil pengadaan

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF010-S1 / Bahan hasil disiapkan | Review/source outcome known, award belum diklaim | TR-091/092/094 | Penyusun; bukti/dasar; source/versions |
| WF010-S2 / Dasar hasil resmi perlu validasi | Actor/applicability/decision authority belum sahih | TR-091/092/094 | Fungsi hasil belum dipetakan; GAP-003/013/017; reason |
| WF010-S3 / Hasil diterima dalam scope sahih | Keputusan dengan method/authority/evidence tervalidasi | TR-093/094; applicable extensions | Owner next applicable work; kontribusi berikut; decision provenance |
| WF010-S4 / Hasil/bahan digantikan | Withdrawal lokal atau validated change result recorded | History/impact follow-up | Pihak decision; affected contract etc bila ada; reason/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-091 | PROPOSED_WORKFLOW | Package/result context/S1/S2 → create/prepare/revise bahan hasil → S1/S2 | Penyusun hasil | Package/provider/review context/source/version | Candidate result tertelusur | Penyusun; basis missing; telaah | Mixed-method output remains unresolved EP-004/005 |
| TR-092 | BLOCKED_BY_GAP | S1/S2 → accept official result → S2 selama unknown; S3 conditional | Official actor belum dipetakan | GAP-003/013/017 authority/current method/decision evidence | Award conditional, unavailable sekarang | Fungsi official result; custodian; validasi | Tidak menganggap rekomendasi award; EP-003 |
| TR-093 | PROPOSED_WORKFLOW | S3 → tautkan applicable document/contract work → S3 | Penanggung jawab hasil/paket | WF-005 guard, WF-011/012/028 own prerequisites | Hubungan hasil/next context, no automatic issue/sign | Owner extension; bahan/authority; buka extension | Unknown applicability tampil blocked tanpa auto-generation |
| TR-094 | PROPOSED_WORKFLOW | S1–S3 → withdraw bahan/record validated change result → S4 | Pemilik bahan/pihak decision sahih | Reason, authority bila official, impacted downstream scope | Old decision/context retained | Decision owner; affected work; telaah impact | Official revocation unknown blocked; EP-007 |

<a id="wf-011"></a>

## WF-011 — Kontribusi SPPBJ, tanpa urutan resmi universal

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF011-S1 / Bahan draft SPPBJ | Result/context/data available, draft bukan penerbitan | TR-101–104/106 sesuai guards | Penyusun; data/varian; draft/source/version |
| WF011-S2 / Tanggung jawab/applicability belum tervalidasi | Keempat mapping/sequence/method authority diperlukan tapi unknown | TR-101–104/106, official acts blocked | Fungsi pekerjaan spesifik belum dipetakan; GAP-004/017 custodian; reason |
| WF011-S3 / Kontribusi resmi tertaut bersyarat | Satu/lebih check/issue/sign contributions individually validated | TR-102–106; tidak menyatakan urutan antar kontribusi | Owner contribution berikut menurut rule; evidence pending; traceability tiap kontribusi |
| WF011-S4 / Lingkup SPPBJ tervalidasi | Semua contributions yang diwajibkan rule sahih telah terpenuhi | TR-106/controlled change; extension applicable | Penanggung jawab scope menurut rule; — (scope selesai); completion basis |
| WF011-S5 / Draft/versi digantikan | Local draft withdraw atau validated supersession recorded | History/impact follow-up | Pihak decision; related work; reason/version |

**TR-102, TR-103 dan TR-104 tidak saling menjadi prasyarat universal.** Review/check, issuance dan signature mempunyai guards/applicability terpisah yang baru dapat dipetakan setelah GAP-004. Tidak ditetapkan bahwa issue selalu sebelum sign atau sign selalu sebelum issue; mapping preparer/checker/issuer/signer semuanya unresolved. SPPBJ tidak menjadi kontrak hanya karena suatu contribution tercatat.

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-101 | PROPOSED_WORKFLOW | Result context/S1/S2 → create/prepare/revise labelled draft → S1/S2 | Penyusun, tanpa universal jabatan | Result/source context/version, variant unknown clearly labelled | Bahan belum resmi, reuse tertelusur | Penyusun; data/variant validation; perbaiki/telaah | Missing actor/method tetap S2; WF-028/EP-003/004 |
| TR-102 | BLOCKED_BY_GAP | S1/S2/S3 → check contribution → S2 selama unknown; S3 conditional | **Pemeriksa/checker**, mapping unknown | GAP-004/017 basis check, method guard, assigned authority, evidence | Check outcome terpisah dari issue/sign | Owner next required contribution belum dipetakan; custodian; validasi | Tidak mengaku check implies issue; missing basis S2 |
| TR-103 | BLOCKED_BY_GAP | S1/S2/S3 → issuance contribution → S2 selama unknown; S3 conditional | **Penerbit/issuer**, mapping unknown | GAP-004/017 issue authority/applicability/order/source evidence | Issuance claim terpisah | Owner applicable next menurut rule; evidence/custodian; sahihkan contribution | Tidak memilih PPK/Pokja universal; S2 |
| TR-104 | BLOCKED_BY_GAP | S1/S2/S3 → signature contribution → S2 selama unknown; S3 conditional | **Penanda tangan/signer**, mapping unknown | GAP-004/017 signer authority/applicability/order/sign evidence | Signature claim terpisah, no digital-sign dependency | Owner applicable next menurut rule; evidence/custodian; sahihkan contribution | Scan/label saja tidak authority; S2/EP-005 |
| TR-105 | BLOCKED_BY_GAP | S3 → nyatakan completion scope → S2 selama unknown; S4 conditional | Scope responsibility belum dipetakan | GAP-004/017 all applicable contributions/order/finality sahih | Completion hanya scope SPPBJ, no auto contract | Next extension owner bila applicable; required work; buka guarded extension | Partial contributions bukan completion |
| TR-106 | PROPOSED_WORKFLOW | S1–S4 → withdraw draft/record validated supersession → S5 | Pemilik bahan/pihak decision sahih | Reason/source authority bila final, affected versions/contracts | Preserve draft/final contribution histories | Decision owner; impacted work; telaah impact | Official cancel/revoke unknown blocked GAP-004/017; EP-007 |

<a id="wf-012"></a>

## WF-012 — Bahan kontrak, konteks berlaku dan perubahan

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF012-S1 / Bahan kontrak/SPK | Applicable result/context tersedia untuk preparation | TR-111/112/115 | Penyusun; required values/variant; source draft |
| WF012-S2 / Review/otoritas kontrak diperlukan | Review/approval/sign/variant rules unknown atau bukti kurang | TR-111/112/115 | Fungsi review/sign belum dipetakan; GAP-003/017/019; reason |
| WF012-S3 / Konteks kontrak berlaku bersyarat | Contract authority/evidence benar-benar validated | TR-113/114/115 | Penanggung jawab contract scope; execution contribution; accepted evidence |
| WF012-S4 / Perubahan kontrak perlu validasi | Addendum/change proposal terkait prior berlaku | TR-111/114/115 | Penyusun change; review/authority; original/change version |
| WF012-S5 / Bahan/lingkup digantikan | Withdrawal draft/validated official change/cancel recorded | History/impacted follow-up | Pihak decision; affected work; reason/authority/version |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-111 | PROPOSED_WORKFLOW | Result/contract context/S1/S2/S4 → create/prepare/revise draft → S1/S2/S4 | Penyusun | Reused package/provider/result, source/version/variant context | Draft/history tertelusur | Penyusun; required values/review; complete source | Tidak memilih clauses/numbering dari workbook; EP-002/003 |
| TR-112 | BLOCKED_BY_GAP | S1/S2 → official review/approval/sign acceptance → S2 selama unknown; S3 conditional | Reviewer/approver/signer belum dipetakan | GAP-003/017/019 applicable variant/authority/order/evidence | Contract active only conditional sahih | Contract owner belum dipetakan; custodian; validasi | Generate draft bukan active; EP-003/004 |
| TR-113 | PROPOSED_WORKFLOW | S3 → buka/tautkan execution tracking → S3 | Operator/penanggung jawab | Applicable WF-013/014/015/024 guard + contract evidence | Tracking links, tanpa payment/recognition inference | Owner next work; evidence contribution; target workflow | Missing context remains explicit, return contract |
| TR-114 | BLOCKED_BY_GAP | S3/S4 → propose/accept official contract change → S4 selama unknown; S3 only after rule | Change preparer dan official authority belum dipetakan | GAP-017/019 change/approval/sign variant, before–after/effect | Versioned change conditional, prior final retained | Change owner; review/custodian; sahihkan change | No silent overwrite/addendum policy; EP-001/005 |
| TR-115 | PROPOSED_WORKFLOW | S1–S4 → withdraw draft/record validated supersession/cancel → S5 | Pemilik bahan/pihak decision sahih | Reason/source authority dan execution/output impact | History/context keputusan tertelusur | Decision owner; affected progress/SPJ etc; tindak lanjut | Official cancel unknown blocked; EP-007 |

<a id="wf-013"></a>

## WF-013 — Tracking pelaksanaan/progress

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF013-S1 / Bukti pelaksanaan ditunggu | Applicable contract/execution context tersedia | TR-121/125 | Penanggung jawab execution; kontribusi pelaksana; contract/source |
| WF013-S2 / Progress tercatat | Update/evidence execution tersedia | TR-121/122/123/125 | Work owner; progress/bukti berikut; update/version |
| WF013-S3 / Isu/perbaikan terbuka | Discrepancy/incomplete evidence perlu tindak lanjut | TR-121/122/123/125 | Pihak correction; perbaikan; reason/issues |
| WF013-S4 / Calon hasil selesai | Completion claim/evidence submitted, belum inspection acceptance | TR-123/124/125 | Reviewer/hasil owner; inspection/authority; candidate evidence |
| WF013-S5 / Scope tracking dihentikan/digantikan | Local draft withdrawal/validated source decision diketahui | History/follow-up | Source decision owner; affected work; reason/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-121 | PROPOSED_WORKFLOW | Execution context/S1/S2/S3 → create/record/correct progress → S2/S3 | Operator/pelaksana sesuai assignment | Contract/package link, evidence/context/time, reason revision | Tracking proof, tanpa infer paid/value | Execution owner; next progress/missing evidence; lengkapi | Issue unresolved tetap S3; EP-001/002 |
| TR-122 | PROPOSED_WORKFLOW | S2/S3 → record issue/request correction → S3 | Work owner/reviewer | Reason/source/required follow-up | Pending correction visible | Pihak correction; contribution; perbaiki | Failure tak menutup issue, EP-005 |
| TR-123 | PROPOSED_WORKFLOW | S2/S3/S4 → submit/correct completion candidate → S4 atau S3 | Work owner | Candidate evidence, unresolved issues disertakan | Candidate hasil, bukan accepted pekerjaan | Hasil reviewer; pemeriksaan; WF-014 applicable | Missing evidence S3; no completion percentage rule |
| TR-124 | BLOCKED_BY_GAP | S4 → official completion acceptance → S4 selama unknown | Acceptance authority belum dipetakan | GAP-003/017/018/019 criteria/method/actor evidence | Official completion bukan efek progress | Fungsi hasil; custodian/inspection evidence; WF-014 validation | No auto paid/BAST/asset, EP-003 |
| TR-125 | PROPOSED_WORKFLOW | S1–S4 → withdraw local evidence proposal/record validated source change → S5 | Pemilik bahan/source decision sahih | Reason/history/affected handover/SPJ/KDP | Scope recording stopped, bukan cancel kontrak otomatis | Source owner; impacted work; telaah effect | Official contract cancellation unknown stays blocked |

<a id="wf-014"></a>

## WF-014 — Kontribusi pemeriksaan/penerimaan/serah terima/BAST

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF014-S1 / Konteks hasil untuk pemeriksaan | Candidate execution/source outcome tersedia | TR-131–134/136 sesuai guard | Pihak examination context; evidence/criteria; source |
| WF014-S2 / Perbaikan hasil/bukti diperlukan | Examination issue atau kontribusi evidence belum lengkap | TR-131–134/136; WF-013 correction | Work/correction owner; perbaikan/authority; reason/evidence |
| WF014-S3 / Kontribusi hasil tertaut | Examination/receipt/handover/BAST contribution dicatat dalam scope distinct | TR-131–136 sesuai guards | Owner contribution berikut; missing evidence/authority; provenance tiap klaim |
| WF014-S4 / Batas penerimaan/trigger belum sahih | Required contributions/actors/order/eligibility unknown | TR-131–136; official outcome unavailable | Fungsi hasil belum dipetakan; GAP-018 custodian; blocked basis |
| WF014-S5 / Scope hasil tervalidasi bersyarat | Semua applicable contributions dan acceptance rule sahih | TR-135/136; handoff candidate conditional | Owner applicable next; classification/SPJ contribution; scope completion evidence |
| WF014-S6 / Bahan/hasil digantikan | Local draft withdrawal/validated source decision diketahui | History/impact follow-up | Decision owner; affected downstream; reason/version/source |

Tidak ditetapkan urutan universal examination, acceptance/receipt, handover dan BAST generation. Kontribusi dicatat sesuai urutan sahih yang kelak divalidasi; BAST evidence tidak membuktikan kontribusi lain otomatis terpenuhi. TR-135 menghubungkan klaim hasil dengan calon handoff, bukan menetapkan pengakuan downstream.

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-131 | PROPOSED_WORKFLOW | Result context/S1/S2/S3/S4 → create/record examination evidence/issue → S2/S3/S4 | Pemeriksa/pencatat menurut scope tervalidasi | Objek, observation/result/source, basis known/unknown clear | Examination claim distinct, issue WF-013 | Examination/correction owner; missing evidence; tindak lanjut | Official criteria unknown S4; EP-001/003 |
| TR-132 | BLOCKED_BY_GAP | S1–S4 → record official acceptance/receipt → S4 selama unknown; S3 conditional | Penerima hasil, mapping unknown | GAP-003/018 authority/method/criteria and accepted evidence | Reception claim distinct from examination/BAST | Next contribution owner belum dipetakan; custodian/bukti; validate | File presence bukan accepted hasil |
| TR-133 | BLOCKED_BY_GAP | S1–S4 → record official handover → S4 selama unknown; S3 conditional | Pihak serah-terima, mapping unknown | GAP-018 objects/parties/event/authority/bukti | Handover event, bukan accounting entry | Next contribution owner; source/evidence; complete scope | Unknown event remains blocked EP-003 |
| TR-134 | PROPOSED_WORKFLOW | S1–S4 → generate/attach BAST evidence → S3/S4 | Penyusun/pencatat; final actor unknown | WF-028 variant/source/version guard; final authority GAP-017/018 | Draft/external evidence tertaut, no definitive downstream | Evidence owner; validation/finality; telaah applicability | Generated/scan label tidak issue/sign acceptance |
| TR-135 | BLOCKED_BY_GAP | S3/S4/S5 → validate scope/eligible downstream trigger → S4 selama unknown; S5 conditional | Result/trigger responsibility belum dipetakan | GAP-008/009/018 applicable contributions/trigger/authority | Source candidate WF-016, belum asset/persediaan/KDP | Source/receiving function; classification decision; WF-016 | INV-012 guard; no auto from BAST/payment |
| TR-136 | PROPOSED_WORKFLOW | S1–S5 → withdraw bahan/record validated correction-cancel-supersede → S6 | Pemilik bahan/decision sahih | Reason, source authority, impacted downstream and outputs | Historical final evidence preserved | Decision owner; affected work; review impact | Official reversal unknown blocked GAP-018; EP-007/008 |

<a id="wf-015"></a>

## WF-015 — SPJ dan payment tracking

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF015-S1 / Bahan/status perlu dilengkapi | Konteks paket/kontrak serta bahan SPJ/status reference tersedia | TR-141/142/145 | Operator SPJ/tracking; bukti/kekurangan; source context |
| WF015-S2 / Bahan/status ditelaah | Receipt/review scope diketahui, belum official Finance acceptance | TR-141/142/143/144/145 | Penelaah ditugaskan; kontribusi sumber/validasi; receipt/evidence |
| WF015-S3 / Perbaikan/otoritas diperlukan | Missing/conflicting evidence atau responsibility/rules unknown | TR-141/142/143/144/145 | Pihak pelengkapan/fungsi belum dipetakan; source/custodian GAP-010; alasan |
| WF015-S4 / Status tracking didukung bukti | Supported scope status tercatat; payment/SPJ official finality belum otomatis | TR-143/144/145 | Owner next work; evidence baru/rekonsiliasi bila relevant; status/source/version |
| WF015-S5 / Bahan/status digantikan | Withdrawal proposal/validated corrected source decision recorded | History/impact follow-up | Source decision owner; affected work; reason/before–after |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-141 | PROPOSED_WORKFLOW | Package/contract context/S1/S2/S3 → create/lengkapi/revise SPJ bahan/status reference → S1/S2/S3 | Operator pencatat | Package/contract/source/version, required values bila rules known | Evidence context diperbaiki, no transfer | Operator; missing evidence; telaah | Missing policy tetap S3; EP-002/003 |
| TR-142 | PROPOSED_WORKFLOW | S1/S2/S3 → review/request correction → S2/S3 | Penelaah ditugaskan | Completeness basis/source/needed correction reason | Pending action jelas | Pihak pelengkapan; contribution; lengkapi | Unknown checklist bukan complete; EP-001/005 |
| TR-143 | APPROVED_PRODUCT_FLOW | S2/S3/S4 → record supported payment/SPJ tracking outcome → S4 atau S3 | Operator/owner evidence | Source/version/time/scope klaim, conflicting evidence explained | Status/evidence tertelusur, bukan funds authorization | Owner next work; contribution baru/rekonsiliasi; lihat/follow-up | Conflict S3; progress/BAST bukan paid proof |
| TR-144 | BLOCKED_BY_GAP | S2/S3/S4 → official Finance acceptance/paid finality → S3 selama unknown | Finance responsibility belum dipetakan | GAP-010/017/018/019 authority/rules/accepted evidence | Official conclusion unavailable; no treasury action | Fungsi Finance belum dipetakan; custodian; sahihkan scope | Tidak menetapkan tax/payment rules; EP-003 |
| TR-145 | PROPOSED_WORKFLOW | S1–S4 → withdraw proposal/record validated source supersession → S5 | Pemilik bahan/pihak decision sahih | Reason/history/source authority dan impact | Old status/evidence preserved | Source owner; impacted reconciliation; telaah | Official reversal unknown blocked; EP-007 |

<a id="wf-016"></a>

## WF-016 — Klasifikasi dan draft hilir

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF016-S1 / Kandidat handoff perlu klasifikasi | Outcome/source tertaut, eligibility/type belum diterima | TR-151/152/156 | Source owner; classification basis; provenance/evidence |
| WF016-S2 / Klasifikasi/trigger terblokir | Policy/eligibility/owner decision belum sahih | TR-151/152/156 | Fungsi classification belum dipetakan; GAP-008/009/018; reason |
| WF016-S3 / Tujuan klasifikasi tervalidasi bersyarat | Decision/eligibility applicable telah diperiksa sahih | TR-153/156 | Source/receiving operator sesuai validated assignment; missing data; decision evidence |
| WF016-S4 / Draft tujuan perlu dilengkapi | Selected reusable source menjadi draft, bukan definitif | TR-154/155/156 | Operator hilir; required completion/review; draft/source/version |
| WF016-S5 / Handoff draft diterima dalam scope | Receiving context, owner dan outstanding work tercatat | TR-154/156; own domain WF-017/023/024 | Owner domain receiving; applicable acceptance work; receipt/links |
| WF016-S6 / Handoff/draft digantikan | Local withdrawal/validated change decision diketahui | History/impact follow-up | Decision owner; affected downstream; reason/version/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-151 | PROPOSED_WORKFLOW | Source/S1/S2 → record candidate outcome/provenance → S1/S2 | Source operator/owner | Source package/contract/result/evidence/version, eligibility unknown explicit | Candidate handoff saja | Source owner; basis classification; telaah | BAST/file/account/book value bukan classification; EP-008 |
| TR-152 | BLOCKED_BY_GAP | S1/S2 → decide eligible type/assignment → S2 selama unknown; S3 conditional | Classification responsibility belum dipetakan | GAP-008/009/018 recognition/type/trigger/authority/evidence | Type decision conditional, tidak ditebak | Fungsi classification; custodian; sahihkan keputusan | Unsupported choice tidak membuat Asset/Persediaan/KDP |
| TR-153 | PROPOSED_WORKFLOW | S3 → reuse relevant source menjadi draft → S4 | Operator sumber/hilir sesuai validated assignment | Validated classification dan eligible source, selected source versions/missing context | Draft typed, no definitive record | Operator hilir; completion; lengkapi draft | Conflicting/duplicate source EP-005/006; draft tetap nonfinal |
| TR-154 | PROPOSED_WORKFLOW | S4/S5 → lengkapi/revise draft context → S4/S5 | Operator hilir | Missing values, source version, reason/affected scope | Receiving bahan lengkap/history retained | Operator/reviewer domain; applicable review; WF-017/023/024 | Source revision tidak overwrite accepted target; EP-001/005 |
| TR-155 | PROPOSED_WORKFLOW | S4 → record receiving handoff receipt → S5 | Receiving operator/owner | Target/source links, owner, outstanding actions/evidence | Scope handoff complete, final acceptance separate | Receiving domain owner; domain validation; follow own workflow | Missing assignment/evidence tetap S4; GAP-014/018 |
| TR-156 | PROPOSED_WORKFLOW | S1–S5 → withdraw draft/record validated changed classification/handoff → S6 | Decision owner/source/receiving parties sahih | Reason/authority if official, impact targets/outputs | Traceable change, no delete/reversal otomatis | Decision/impacted owners; review; controlled follow-up | Definite target reversal blocked GAP-008/018; EP-007/008 |

<a id="wf-017"></a>

## WF-017 — Candidate register dan penerimaan aset

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF017-S1 / Kandidat aset dilengkapi | Downstream/historical/approved other acquisition source tertaut | TR-161/162/166 | Operator; completion/source; acquisition/provenance |
| WF017-S2 / Kandidat diajukan untuk telaah | Completeness submission/evidence available | TR-163/164/165/166 | Penelaah; review/authority; candidate receipt |
| WF017-S3 / Perbaikan/identitas perlu keputusan | Missing data, duplicate ambiguity atau acceptance policy unknown | TR-161/162/164/165/166 | Operator/issue resolver; source/rule/custodian; reason/ambiguity |
| WF017-S4 / Aset diterima bersyarat | Recognition/identity/acceptance applicable sahih dan terpenuhi | Controlled-change WF-018/020/021; history | Asset responsible/custodian sesuai assignment; next domain work; accepted evidence |
| WF017-S5 / Kandidat ditolak/dihentikan | Validated rejection atau draft withdrawal recorded | History; new/revised candidate relation bila permitted | Decision/pemilik bahan; — (candidate scope); reason/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-161 | PROPOSED_WORKFLOW | Source/S1/S3 → create/complete/correct candidate → S1/S3 | Operator ditugaskan | Provenance, identity, acquisition, placement/custodian context, source/version | Missing context completed tanpa format nomor baru | Operator; missing evidence/identity rule; submit bila sahih | EP-002/006; similarity bukan duplicate verdict |
| TR-162 | PROPOSED_WORKFLOW | S1/S3 → submit candidate scope → S2 | Operator | Applicable completion/evidence, submission history | Candidate available for review | Penelaah; review; telaah | Missing policy/identity remains S3; EP-003 |
| TR-163 | PROPOSED_WORKFLOW | S2 → request correction/identity resolution → S3 | Penelaah | Explicit reason/missing values/bukti/identity question | Revision pending jelas | Operator/resolver; correction/evidence; resubmit | No unrelated source re-entry; original retained |
| TR-164 | BLOCKED_BY_GAP | S2/S3 → accept definitive Asset → S3 selama unknown; S4 conditional | Acceptance responsibility belum dipetakan | GAP-008/009/011/014/018 classification/recognition/identity/authority/evidence | Definitiveness conditional, no automatic from BAST | Function acceptance; custodian/review; validate | Candidate stays nonfinal until all required rules satisfied |
| TR-165 | BLOCKED_BY_GAP | S2/S3 → official reject candidate → S3 selama unknown; S5 conditional | Decision responsibility belum dipetakan | GAP-008/011/018 authority/criteria/reason/source | Rejection traceable jika sahih | Decision owner; — (rejected scope); history/next candidate if permitted | Agent/label tidak official rejection authority |
| TR-166 | PROPOSED_WORKFLOW | S1/S2/S3 → withdraw/supersede local candidate → S5 | Pemilik bahan/pihak decision sahih | Reason, impacted source/links | Candidate history retained | Pemilik bahan; affected follow-up bila ada; lihat reason | Accepted Asset tidak lewat withdrawal; use WF-020/021 |

<a id="wf-018"></a>

## WF-018 — Proposal perpindahan/mutasi

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF018-S1 / Bahan perubahan posisi | Identified asset/current context dan target proposal available | TR-171/172/175 | Pengaju/operator; target evidence; before/after candidate |
| WF018-S2 / Proposal dalam telaah | Submission source/reason tersedia | TR-173/174/175 | Reviewer; assignment/authority/evidence; proposal receipt |
| WF018-S3 / Perbaikan/dasar perlu validasi | Missing/conflicting placement atau official chain/types unknown | TR-171/172/174/175 | Pengaju/resolver; source/custodian GAP-008/014; reason |
| WF018-S4 / Perubahan diterima bersyarat | Movement/mutation applicable sahih diterima | History/controlled correction WF-020 | Responsible asset/placement/custodian relevant; next work; acceptance evidence |
| WF018-S5 / Bahan perubahan dihentikan | Local proposal withdrawn/validated supersession recorded | History/follow-up | Decision/pemilik bahan; impacted work; reason/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-171 | PROPOSED_WORKFLOW | Asset context/S1/S3 → create/prepare/correct change proposal → S1/S3 | Pengaju/operator | Asset identity/current/target context, source/time/reason/evidence | Candidate change tertelusur | Pengaju; missing facts; submit | Historical conflict stays exception, EP-005 |
| TR-172 | PROPOSED_WORKFLOW | S1/S3 → submit proposal → S2 | Pengaju | Completion/source/version dan actual receiving scope | Review work created dalam scope | Reviewer; review/source contribution; telaah | Unknown official routing explicit GAP-008/014 |
| TR-173 | PROPOSED_WORKFLOW | S2 → request correction/clarification → S3 | Reviewer ditugaskan | Reason/current-versus-target discrepancy | Pending responsibility clear | Pengaju/resolver; correction; resubmit | No silent location overwrite; EP-001 |
| TR-174 | BLOCKED_BY_GAP | S2/S3 → accept movement/mutation → S3 selama unknown; S4 conditional | Authority/chain belum dipetakan | GAP-008/011/014/018 accepted types/placement/custodian/evidence/time | Update relevant representation + preserve history if validated | Asset/placement owner; next relevant work; view resulting history | No last-write-wins/accounting inference; EP-003/005 |
| TR-175 | PROPOSED_WORKFLOW | S1/S2/S3 → withdraw/supersede local proposal → S5 | Pemilik bahan/pihak decision sahih | Reason/version/source/target impact | Proposal stopped, prior actual representation retained | Pemilik bahan; affected scope; follow-up | Accepted movement reversal requires WF-020/rule, EP-007 |

<a id="wf-019"></a>

## WF-019 — Observasi fisik dan maintenance evidence

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF019-S1 / Lingkup pemeriksaan/perawatan | Identified asset dan tujuan pekerjaan available | TR-181/185 | Pemeriksa/work owner; access/observation evidence; scope/source |
| WF019-S2 / Observasi/bukti tercatat | Observation/maintenance occurrence recorded, bukan inferred condition | TR-182/183/184/185 | Pencatat/reviewer; assessment/follow-up; actual evidence/time |
| WF019-S3 / Ketidaksesuaian/dasar terbuka | Observed discrepancy atau condition criterion/authority belum sahih | TR-182/183/184/185 | Issue owner; correction/reconciliation/custodian; reason/evidence |
| WF019-S4 / Hasil lingkup pemeriksaan dijelaskan | Observed result verified sesuai scope/rule; open differences still linked | TR-182/183/185; future follow-up | Next work owner bila ada; relevant follow-up; scope/result evidence |
| WF019-S5 / Bahan lingkup digantikan | Local scope withdrawn/validated supersession recorded | History/follow-up | Decision/work owner; affected work; reason/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-181 | PROPOSED_WORKFLOW | Asset context/S1 → create/record actual observation/maintenance → S2 | Pemeriksa/pencatat ditugaskan | Asset/scope/time/observing actor/source evidence | Physical/maintenance claim distinct from accounting | Pencatat/reviewer; result check; telaah | No scale invented; unknown remains stated |
| TR-182 | PROPOSED_WORKFLOW | S2/S3/S4 → correct/add observation evidence → S2/S3 | Observer/operator | Original/revised context/reason/evidence | History sebelum/sesudah preserved | Reviewer/issue owner; evidence; review | Book value/age/life/zero cannot supply condition, INV-007 |
| TR-183 | PROPOSED_WORKFLOW | S2/S3/S4 → record discrepancy/follow-up link → S3 | Reviewer/work owner | Observed difference and needed correction scope | WF-020/025 relation, no automatic adjustment/disposal | Follow-up owner; correction/explanation; proceed appropriate WF | EP-003/009; maintenance no automatic capitalization |
| TR-184 | PROPOSED_WORKFLOW | S2/S3 → record reviewed result scope → S4 or S3 | Reviewer/penanggung jawab | Verified observation basis, issue links, allowed completion scope | Observation scope explained, open issue not falsely resolved | Next issue owner bila ada; linked evidence; follow-up | Official condition criterion unknown stays S3 GAP-008/013 |
| TR-185 | PROPOSED_WORKFLOW | S1–S4 → withdraw bahan/record validated supersession → S5 | Pemilik bahan/pihak decision sahih | Reason/source/version/impact | Observation history retained | Work/decision owner; affected follow-up; telaah | Tidak delete accepted condition evidence; EP-007 |

<a id="wf-020"></a>

## WF-020 — Controlled-change aset

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF020-S1 / Usulan perubahan terjelaskan | Correction/development/reclassification purpose distinguished | TR-191/192/195 | Pengaju/operator; source/reason; before/after proposal |
| WF020-S2 / Usulan dalam telaah | Submission/version/evidence tersedia | TR-193/194/195 | Reviewer; accounting/authority basis; proposal receipt |
| WF020-S3 / Perbaikan/policy diperlukan | Missing/conflicting basis atau accounting/period effect unknown | TR-191/192/194/195 | Pengaju/fungsi decision belum dipetakan; GAP-008/009 custodian; reason |
| WF020-S4 / Keputusan perubahan diterima bersyarat | Applicable rule/authority/effect/evidence benar-benar validated | Controlled further change/history | Asset/transaction responsible; impacted reports/periods as rule; decision/history |
| WF020-S5 / Proposal ditolak/dihentikan | Validated rejection atau local withdrawal recorded | History; linked new proposal bila allowed | Decision/pemilik bahan; — (proposal scope); reason/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-191 | PROPOSED_WORKFLOW | Asset context/S1/S3 → create/prepare/revise change proposal → S1/S3 | Pengaju/operator | Kind purpose, source/target/time/reason/evidence/version/impact | Candidate controlled change, original retained | Pengaju; missing basis; complete | Correction/development/reclassification not conflated |
| TR-192 | PROPOSED_WORKFLOW | S1/S3 → submit proposal → S2 | Pengaju | Proposal evidence/current record context | Review pending in relevant scope | Reviewer; rule/source clarification; telaah | Official chain unknown explicit, EP-003 |
| TR-193 | PROPOSED_WORKFLOW | S2 → request correction/clarification → S3 | Reviewer | Reason/missing evidence/period question | Pending work attributed | Pengaju/custodian; contribution; revise/resubmit | EP-001/005, no overwrite closed period |
| TR-194 | BLOCKED_BY_GAP | S2/S3 → decide accept/reject and applicable effect → S3 selama unknown; S4/S5 conditional | Official decision responsibility belum dipetakan | GAP-007/008/009/010/013/014 rule/effects/authority/history evidence | Resulting representation conditional, no journal/formula invented | Responsible change/decision; impacted work; controlled follow-up | No depreciation/historical treatment inference; INV-018 |
| TR-195 | PROPOSED_WORKFLOW | S1/S2/S3 → withdraw/supersede proposal → S5 | Pemilik bahan/pihak decision sahih | Reason/version/affected scope | Proposal historical, accepted record unchanged | Pemilik bahan; impact follow-up if applicable; view history | Accepted change reversal requires authority, EP-007 |

<a id="wf-021"></a>

## WF-021 — Proposal removal dan bukti tindakannya

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF021-S1 / Bahan usulan removal | Identified object/action scope dan business/observed basis available | TR-201/202/205 | Pengaju/kustodian; basis/evidence; proposal/source |
| WF021-S2 / Dasar/otoritas removal perlu validasi | Review required; criteria/authority/effects belum sahih | TR-201/203/205 | Fungsi reviewer/decision belum dipetakan; GAP-008/013/014; reason |
| WF021-S3 / Keputusan applicable tertaut bersyarat | Validated decision acceptance/rejection diketahui | TR-204/205; actual action only if authorized | Decision/action responsible; actual action/evidence jika approved; authority |
| WF021-S4 / Hasil removal tercatat bersyarat | Tindakan applicable dan evidence validated, history retained | History/controlled further action | Asset/lifecycle responsible; impacted reports/recon as rule; actual outcome proof |
| WF021-S5 / Proposal/versi dihentikan | Local withdrawal/validated supersession of proposal recorded | History/follow-up | Pemilik bahan/decision owner; affected work; reason/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-201 | PROPOSED_WORKFLOW | Asset context/S1/S2 → create/prepare/correct proposal → S1/S2 | Pengaju/operator | Object, intended administrative/physical scope, observed/business basis/reason | Candidate removal saja | Pengaju; evidence; submit/telaah | Book value zero/age bukan eligibility; INV-007 |
| TR-202 | PROPOSED_WORKFLOW | S1 → submit/telaah bahan → S2 | Pengaju/reviewer | Source/version and needed validation/evidence | Review work pending | Reviewer/fungsi belum dipetakan; authority; validate | Missing rules remain blocked EP-003 |
| TR-203 | BLOCKED_BY_GAP | S2 → official decision removal/rejection → S2 selama unknown; S3 conditional | Decision authority belum dipetakan | GAP-008/013/014 criteria/effects/delegation/decision evidence | Decision distinct dari actual removal | Action/decision owner; required next if approved; record evidence | No disposal recommendation from depreciation |
| TR-204 | BLOCKED_BY_GAP | S3 → record accepted applicable removal outcome → S2 jika basis missing; S4 conditional | Actual action/record acceptance responsibility belum dipetakan | GAP-008/014 action/effect/evidence authority, administrative vs physical scope | Lifecycle outcome/history conditional, no record deletion | Lifecycle owner; impacted reconciliation/report; follow rule | Decision alone bukan completed physical disposal |
| TR-205 | PROPOSED_WORKFLOW | S1/S2/S3 → withdraw bahan/record validated supersession → S5 | Pemilik bahan/pihak decision sahih | Reason/source authority if material/impact | History retained, no auto restore/reversal | Decision/pemilik bahan; affected work; review impact | Completed removal reversal blocked until rule, EP-007 |

<a id="wf-022"></a>

## WF-022 — Output penyusutan/amortisasi dengan policy sahih

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF022-S1 / Konteks permintaan dipilih | Report type/scope/temporal input candidate available | TR-211/212; abandon sebelum acceptance | Requester; policy/cutoff validity; selected context |
| WF022-S2 / Policy/cutoff belum sahih | Unsupported input atau required accounting authority unavailable | TR-211/212 | Requester/fungsi policy; custodian GAP-007/008/013; reason |
| WF022-S3 / Permintaan diterima, hasil belum selesai | Validated scope/policy accepted for future operation | TR-213/214 | Penanggung jawab operasi; completion/evidence; receipt/context |
| WF022-S4 / Hasil tersedia dalam scope | Output berhasil dengan source/policy/cutoff/provenance; official finality separate | TR-214; view/compare/new request | Requester/reviewer applicable; signoff bila required; result/evidence |
| WF022-S5 / Operasi gagal/hasil belum terkonfirmasi | Failure/uncertain operation outcome explicitly recorded | TR-214; return request/result scope | Penanggung jawab operasi; failure resolution; result/error status |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-211 | PROPOSED_WORKFLOW | S1/S2 → choose/correct context → S1/S2 | Requester | Supported report scope/time, policy/cutoff reference | Request context, no formula | Requester; validity missing; validate input | Today/custom arbitrary tidak universally valid; S2 |
| TR-212 | BLOCKED_BY_GAP | S1/S2 → request official applicable output → S2 selama unknown; S3 conditional | Requester/policy responsibility | GAP-007/008/013 approved policy/version/cutoff/examples; related GAP-009/010 if required | Output request accepted only with sahih basis | Operation owner; validated operation; wait/track | No official estimate/daily proration; INV-013 |
| TR-213 | PROPOSED_WORKFLOW | S3 → record completion/failure result → S4/S5 | Penanggung jawab operasi | Approved applicable policy + operation result/provenance/status | Result distinct dari acceptance, no physical-condition inference | Requester; review/signoff jika required; view result/error | EP-010; accepted is not complete; technical contracts P6 |
| TR-214 | PROPOSED_WORKFLOW | S3/S4/S5 → inspect history/result/recovery context → same confirmed scope | Requester/operation owner | Receipt/result/error evidence, source/policy versions | Find output again; new request tidak overwrite historical final | User/operation owner; uncertainty resolution; view/check then decide safe next | Retry unknown acceptance not automatic; P6 menentukan mechanics |

<a id="wf-023"></a>

## WF-023 — Ledger dan observations Persediaan

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF023-S1 / Bahan transaksi/observasi | Item/scope/source/family intent known | TR-221/222/226 | Operator/observer; completion/dictionary; source/raw code |
| WF023-S2 / Makna kode/dasar belum sahih | Mapping/sign/identity/authority unknown atau conflicting | TR-221/222/223/224/226 | Fungsi ledger/resolver; GAP-005/006 custodian; raw preserved/reason |
| WF023-S3 / Bahan ditelaah | Submitted transaction/observation evidence available | TR-223/224/226 | Reviewer; actual applicable rule/decision; receipt/evidence |
| WF023-S4 / Transaksi diterima bersyarat | Meaning/sign/mapping/evidence/authority tervalidasi, ledger acceptance sahih | TR-225; controlled correction new scope | Ledger responsible; comparison/correction bila relevant; accepted version |
| WF023-S5 / Perbandingan/selisih tertaut | Physical observation versus accepted ledger perlu ditelaah | TR-221/223/225/226; WF-025 | Difference owner; explanation/adjustment authority; count/ledger/source |
| WF023-S6 / Bahan ditolak/dihentikan | Validated rejection/local withdrawal recorded | History/follow-up | Pemilik bahan/decision owner; affected scope; reason/raw provenance |

| Keluarga | Guard dan bukti yang membedakan workflow |
|---|---|
| Opening/baseline | Basis sumber pembuka, lingkup item/lokasi/waktu dan penerimaan saldo awal sahih; bukan input posisi stok mandiri tanpa ledger context. |
| Receipt/addition | Bukti perolehan/penerimaan applicable dan quantity/unit meaning sesuai dictionary; source procurement bila ada, bukan BAST otomatis accepted ledger. |
| Usage/release | Bukti penggunaan/pengeluaran dan responsible context; meaning/sign/effect tidak ditebak dari label. |
| Physical count/adjustment | Observation DC-069 dapat direkam; comparison ke ledger accepted terpisah. Adjustment menjadi transaksi hanya dengan decision/rule sahih, bukan auto posting hasil count. |
| Correction | Reason/source/version/before–after dan applicable correction authority; nilai posisi tidak diedit sebagai truth kedua. |
| Reconciliation | Expected relationship/source/time/owner/evidence WF-025; closure/signoff bukan efek balance matching. |

Tidak ada family dipetakan otomatis ke kode raw P01/P02. Meaning, sign, effective historical mapping dan approved examples seluruhnya GAP-005/006; raw dan canonical interpretation tetap dibedakan.

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-221 | PROPOSED_WORKFLOW | Item/source context/S1/S2/S5 → create/prepare/correct bahan/observation → S1/S2/S5 | Operator/observer | Item/source/location/time/family intent, raw code/version, evidence | Candidate/observation preserved, not ledger official | Operator/resolver; missing basis; complete | Ambiguity remains; EP-005/006 |
| TR-222 | PROPOSED_WORKFLOW | S1/S2 → submit review scope → S3 atau S2 | Operator | Evidence/receipt, unresolved meaning explicitly retained | Review candidate tanpa canonical guess | Reviewer/custodian; dictionary/identity; assess | Unsupported mapping S2; INV-010 |
| TR-223 | PROPOSED_WORKFLOW | S3/S2/S5 → request meaning/correction evidence → S2/S5 | Reviewer/issue owner | Reason/source/basis required | Pending resolver visible | Operator/custodian; clarification; correct/resubmit | Tidak normalize raw; EP-001/003 |
| TR-224 | BLOCKED_BY_GAP | S3/S2 → accept/reject ledger transaction → S2 selama unknown; S4/S6 conditional | Ledger acceptance responsibility belum dipetakan | GAP-005/006/011/014 meaning/sign/effective dictionary/identity/authority/evidence | Accepted ledger only conditional; rejection traceable | Ledger/decision owner; next comparison; view history | Position not from unsupported rule/manual overwrite |
| TR-225 | PROPOSED_WORKFLOW | S4/S5 → compare position/count/link reconciliation → S5 atau confirmed S4 | Operator/difference owner | Accepted ledger semantics/time plus actual count evidence, WF-025 basis | Position derived from accepted ledger; difference linked | Case/difference owner; explanation/adjustment rule; WF-025 | No auto adjustment/signoff; GAP-005/006/010, EP-009 |
| TR-226 | PROPOSED_WORKFLOW | S1/S2/S3/S5 → withdraw/supersede bahan → S6 | Pemilik bahan/pihak decision sahih | Reason/raw/source/version/impact | Candidate historical, no deletion or accepted ledger reversal | Decision/pemilik bahan; affected work; review impact | Accepted correction/reversal requires rules; EP-007 |

<a id="wf-024"></a>

## WF-024 — KDP dan penyelesaian bersyarat

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF024-S1 / Kandidat konteks KDP | Source construction/acquisition/execution applicable perlu ditinjau | TR-231/232/236 | Operator KDP; eligibility/source rule; source/contract/context |
| WF024-S2 / KDP/progress ditelusuri bersyarat | Validated applicable KDP context dan progress evidence tracked | TR-233/234/236 | KDP/work responsible; progress/completion evidence; accepted context |
| WF024-S3 / Recognition/hasil perlu validasi | KDP eligibility/value/closure/type/authority unknown atau evidence kurang | TR-231/232/233/234/235/236 | Fungsi KDP/hasil belum dipetakan; GAP-008/009/018; reason |
| WF024-S4 / Calon penyelesaian tertaut | Completion candidate available, belum definitive Asset | TR-234/235/236 | Completion/classification responsibility; validation; candidate proof |
| WF024-S5 / Hasil penyelesaian/draft eligible bersyarat | Completion/type decision sahih; eligible asset draft linked separately | TR-236; WF-016/017 acceptance, history | Receiving asset draft owner; missing/domain acceptance; decision/source links |
| WF024-S6 / Bahan/lingkup digantikan | Local proposal withdrawal/validated change source recorded | History/impact follow-up | Decision owner; affected domain work; reason/version/source |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-231 | PROPOSED_WORKFLOW | Source/S1/S3 → record candidate/source KDP context → S1/S3 | Operator | Contract/execution/acquisition/historical provenance, classification unknown clear | Candidate KDP tanpa automatic recognition | Operator; rule/source; assess eligibility | Construction contract bukan automatic KDP |
| TR-232 | BLOCKED_BY_GAP | S1/S3 → accept applicable KDP context → S3 selama unknown; S2 conditional | KDP authority belum dipetakan | GAP-008/009/011/018 recognition/identity/evidence/assignment | Accepted context conditional, value rules not invented | KDP responsible; progress/evidence; track applicable work | No progress-to-value/capitalization timing |
| TR-233 | PROPOSED_WORKFLOW | S2/S3 → record/correct progress/issue evidence → S2/S3 | Work/KDP operator | Source/version/time/evidence, reason correction | Tracking/history, unfinished kept distinct | Work owner; contribution; next progress/resolve issue | INV-011; unfinished not Asset |
| TR-234 | PROPOSED_WORKFLOW | S2/S3/S4 → submit/correct completion candidate → S4 atau S3 | Work owner | Completion claim/source, unresolved issues/evidence | Candidate outcome, no definitive conversion | Completion reviewer; evidence/authority; assess | Missing basis S3, EP-001/003 |
| TR-235 | BLOCKED_BY_GAP | S4/S3 → accept completion/type and eligible draft handoff → S3 selama unknown; S5 conditional | Completion/classification responsibility belum dipetakan | GAP-008/009/018 closure/classification/capitalization authority/evidence | Asset candidate via WF-016/017 only if applicable; KDP history retained | Draft receiving owner; completion/acceptance; follow target WF | No automatic capitalization/unfinished conversion |
| TR-236 | PROPOSED_WORKFLOW | S1–S5 → withdraw bahan/record validated source supersession → S6 | Pemilik bahan/pihak decision sahih | Reason/authority/impacted KDP–Asset–report links | Historical context preserved | Decision/affected owner; review; controlled change | Accepted KDP/Asset reversal requires rule; EP-007/008 |

<a id="wf-025"></a>

## WF-025 — Kasus rekonsiliasi dan penerimaan hasil

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF025-S1 / Lingkup kasus disiapkan | Sources/time/scope selected, expected relationship perlu dinilai | TR-241/242/246 | Case operator; source/rule; source versions |
| WF025-S2 / Perbandingan tercatat | Sahih comparison basis applied, result/operation receipt known | TR-242/243/245/246 | Case/difference owner; assessment/contribution; results/evidence |
| WF025-S3 / Selisih/penjelasan terbuka | Differences/missing evidence/action assignment recorded | TR-243/244/245/246 | Difference owner; source contribution/evidence; discrepancy/reason |
| WF025-S4 / Penjelasan siap ditelaah | Traceable explanation/evidence tersedia; belum final resolved/signoff | TR-243/244/245/246 | Reviewer/acceptance function; review/authority; explanation evidence |
| WF025-S5 / Hasil diterima dalam scope bersyarat | Resolution/acceptance rule terpenuhi; formal signoff hanya jika sahih | TR-242/246; new case/revision relation | Case responsible; — (accepted scope), future work if applicable; acceptance basis |
| WF025-S6 / Relasi/otoritas belum sahih | Comparison rule, institutional ownership/waiting/signoff/cadence/output unavailable | TR-241/242/243/245/246 | Fungsi case/signoff belum dipetakan; GAP-010/source custodian; reason |
| WF025-S7 / Lingkup kasus digantikan | Local scope stopped/validated supersession recorded | History/follow-up | Case/decision owner; affected reports/source; reason/version |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-241 | PROPOSED_WORKFLOW | Source/S1/S6 → initiate/edit case scope → S1/S6 | Case operator | Source/version/scope/time, expected relation evidence, provisional responsibility clear | Comparison candidate/context | Case owner/fungsi belum dipetakan; rule/source; validate scope | No invented tolerance/equality/cadence |
| TR-242 | PROPOSED_WORKFLOW | S1/S2/S5/S6 → compare/recompare supported relation → S2 atau S6 | Case operator | Validated expected relation + source scope/time, operation result | Difference/result evidence; prior run context retained | Case/difference owner; assessment; record issues | Unknown basis S6; long-op EP-010, not complete until result |
| TR-243 | PROPOSED_WORKFLOW | S2/S3/S4/S6 → record difference/assignment/request contribution → S3/S6 | Case/work owner | Difference/source/reason, responsible/waiting known or unresolved | Action ownership visible, no official actor invention | Difference owner/fungsi belum dipetakan; evidence; investigate | Unknown institutional mapping remains GAP-010 |
| TR-244 | APPROVED_PRODUCT_FLOW | S3/S4 → supply explanation/evidence → S4 atau S3 | Difference owner/contributing party | Source, reason, corrective decision links and evidence | Explained difference ready for review, no automatic resolved | Reviewer/acceptance function; assessment; telaah | Text alone not sufficient; EP-009/INV-014 |
| TR-245 | BLOCKED_BY_GAP | S2/S3/S4/S6 → accept resolved/signoff/period output → S6 selama unknown; S5 conditional | Acceptance/signoff responsibility belum dipetakan | GAP-010/012 + relevant source policies; evidence/reason/disposition/rule fulfilled | Resolved scope and official signoff distinguished, no false finality | Case/period function; custodian/approval evidence; validate | Equal numbers/explanation typed not official signoff |
| TR-246 | PROPOSED_WORKFLOW | S1–S6 → stop local scope/record validated supersession → S7 | Case/decision owner | Reason/run versions/impacted sources/reports | History retained; differences not erased/resolved by cancellation | Owner affected scope; follow-up; review links | Accepted period reopening/reversal requires authority; EP-007 |

<a id="wf-026"></a>

## WF-026 — Historical candidate, validation dan cleansing decision

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF026-S1 / Source dan kandidat intake | Source/version/locator/target proposal known, belum accepted | TR-252/257 | Source/intake operator; format/rule; provenance |
| WF026-S2 / Hasil validasi candidate tersedia | Structural/semantic checks with supported basis produced result | TR-253/254/255/257 | Intake reviewer; issue decisions; findings/basis |
| WF026-S3 / Ambiguitas/duplikasi/policy belum selesai | Missing meaning/trust/identity rule/cutover authority atau conflicting source | TR-252/253/254/255/257 | Issue resolver/custodian; applicable rule/evidence; original-versus-candidate |
| WF026-S4 / Keputusan candidate terdokumentasi | Cleansing/reject/conditional accept decision traceable; target belum diterima | TR-254/255/257 | Intake/domain reviewer; applicable acceptance/reconciliation; decision/history |
| WF026-S5 / Target diterima bersyarat, rekonsiliasi diperlukan | Future domain acceptance/cutover authority sahih, outcome/source related | TR-256; controlled correction/history | Target/case owner; reconciliation acceptance; target/source/decision links |
| WF026-S6 / Scope penerimaan direkonsiliasi bersyarat | Target telah diterima melalui S5; applicable reconciliation/trust acceptance sahih | History/approved domain follow-up | Target/source accountable function; — (accepted scope); completion evidence |
| WF026-S7 / Candidate ditolak/dihentikan | Valid rejection atau local withdrawal recorded | History; corrected candidate relation bila allowed | Decision/pemilik bahan; — (scope lama); reason/provenance |

Tidak ada transition di sini mengizinkan P3 menjalankan importer/migrasi. Future intake operation semantics yang bisa panjang mengikuti EP-010; accepted-operation outcome unknown memerlukan pengecekan status dahulu. Raw source tidak diedit; credential-like plaintext values bukan candidate password dan tidak masuk bukti domain. Duplicate verdict memerlukan explicit identity rule yang validated, bukan similarity label.

Keputusan di S4 bukan penerimaan target. Finality record domain yang diterima hanya melalui S5 → TR-256 → S6 dengan seluruh guard penerimaan dan rekonsiliasi terpenuhi. Penolakan/withdrawal mengikuti TR-255/TR-257 ke S7 sesuai guard masing-masing; penutupan scope candidate itu tidak menyatakan accepted-record finality.

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-251 | PROPOSED_WORKFLOW | Source → record proposed intake/source context → S1 | Source/intake operator | Safe source identity/version/locator/target, allowed future scope | Source berbeda dari candidate/accepted data | Intake owner; validation basis; select supported checks | Raw source unchanged; sensitive data handling required |
| TR-252 | PROPOSED_WORKFLOW | S1/S3 → record supported structural/semantic validation → S2/S3 | Intake operator/reviewer | Approved format/rule evidence where known, result/error scope | Candidate issues/success distinguished | Reviewer/resolver; findings/rule; assess | Unknown meaning stays S3, credential exclusion INV-016 |
| TR-253 | PROPOSED_WORKFLOW | S2/S3 → detect/record ambiguity/duplicate concern → S3 | Reviewer | Source/candidate comparison, identity uncertainty/rule stated | Concern bukan automatic duplicate verdict | Identity/source resolver; rule/evidence; decide safely | EP-005/006; no merge/overwrite raw |
| TR-254 | PROPOSED_WORKFLOW | S2/S3/S4 → propose/document cleansing/rejection decision → S4 atau S3 | Reviewer/source custodian within validated scope | Original/proposed values meaning, reason/version/evidence, authority scope | Decision traceable; official accept/reject subject own guard | Decision/domain owner; applicable approval; review | Unvalidated source precedence/authority stays S3 |
| TR-255 | BLOCKED_BY_GAP | S2/S3/S4 → official accept/reject target record → S3 selama unknown; S5/S7 conditional | Acceptance/cutover responsibility belum dipetakan | GAP-011/012/013/019 format/identity/trust/write ownership/authority/domain rules | Future accepted domain outcome conditional, not production action now | Target/case function; reconciliation; WF-025 | Invalid/ambiguous records not silently final; INV-015 |
| TR-256 | BLOCKED_BY_GAP | S5 → accepted reconciliation/intake finality → S3 selama unknown; S6 conditional | Reconciliation/acceptance responsibility belum dipetakan | Target diterima melalui TR-255 dengan domain/trust/cutover authority sahih dan target/source/decision links; GAP-010/011/012 official comparison/disposition/signoff evidence | Final accepted scope only with traceable basis | Target/case owner; — (scope completed); retain provenance | Summary counts not proof final production migration |
| TR-257 | PROPOSED_WORKFLOW | S1–S4 → withdraw local candidate/record validated supersession → S7 | Pemilik bahan/pihak decision sahih | Reason/source/version/impact | Rejected/withdrawn/corrected history retained | Pemilik bahan; relevant follow-up; view decision | Accepted target reversal requires controlled rule, EP-007 |

<a id="wf-027"></a>

## WF-027 — Report request, operasi dan output

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF027-S1 / Konteks laporan dipilih | Type/scope/temporal inputs candidate available | TR-261/262; abandon pre-acceptance | Requester; validation; context/type |
| WF027-S2 / Semantik/policy laporan belum sahih | Unsupported inputs/cutoff/policy/form/source authority unavailable | TR-261/262/265 | Requester/policy function; custodian relevant GAP; reason |
| WF027-S3 / Permintaan diterima, hasil diproses | Supported context accepted; operation belum complete | TR-263/264/265 | Penanggung jawab operasi; result/error; receipt/scope/progress |
| WF027-S4 / Hasil tersedia | Result atau no-data yang sahih tersedia dengan context/provenance | TR-264/265; history/compare/new request | Requester/reviewer; signoff bila applicable; result/source/policy |
| WF027-S5 / Operasi gagal/belum terkonfirmasi | Error/uncertain acceptance/completion recorded clearly | TR-264/265 | Operation owner; recovery/status clarity; evidence failure |

| Family temporal | Input sahih yang harus dinyatakan | Policy/cutoff/provenance dan block | Empty/no-data |
|---|---|---|---|
| POSITION / AS-OF | Satu tanggal posisi/cutoff bermakna dan lingkup record | Source history applicable pada cutoff; policy version; unsupported arbitrary date/cutoff GAP-007/008/009/010/013 menahan official result. Today bukan universal valid input. | Jelaskan tidak ada record pada scope/cutoff yang sahih, jangan substitute nol resmi untuk policy missing. |
| TRANSACTION RANGE | Rentang aktivitas/transaksi dengan batas waktu bermakna menurut rule report | Source transaction/history dan basis inclusion; tidak dipakai sebagai posisi atau daily depreciation formula. Unknown boundaries/treatment GAP-005–008/010/013 blocked. | Jelaskan tidak ada transaksi pada valid scope/range; posisi awal/akhir tidak diasumsikan dari empty range. |
| PERIODIC | Bulan/triwulan/semester/tahun atau custom period hanya bila report/policy membolehkan | Applicable period/cutoff/version, sources dan closure qualifications; GAP-007/008/009/010/013. Tidak memaksa semua modes pada setiap judul. BMU definisi/form/temporal semantics masih perlu authority. | Report scope/period dan absence dijelaskan, official finality/signoff tetap terpisah. |
| RECONCILIATION | Scope comparison dan kompatibilitas waktu/source yang rule-nya tervalidasi WF-025 | Expected relationship, source versions/cutoffs, differences/exceptions, applicable disposition; GAP-010/012 dan source gaps. Matching result bukan official signoff. | Bedakan no sources, no comparable records dan no differences; tidak menyatakan periode resmi selesai dari kosong. |

Keempat families adalah klasifikasi semantik P2/P3 yang proposed, bukan mapping resmi setiap report title. Setiap report harus mendeklarasikan family applicable, temporal inputs dan policy/authority-nya sebelum input offered sebagai supported. Neraca position candidate tidak membuktikan seluruh format resmi; BMU tetap nama kebutuhan dengan definisi/form belum terverifikasi. Tidak ada formula/cutoff/rate/journal baru.

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-261 | PROPOSED_WORKFLOW | S1/S2 → choose/correct family/scope/time → S1/S2 | Requester | Supported family/temporal contract per type, scope/source | Report context candidate | Requester; valid inputs/policy; assess | Unsupported mode S2; error bukan empty data |
| TR-262 | BLOCKED_BY_GAP | S1/S2 → request supported official output → S2 selama authority missing; S3 conditional | Requester/policy function | Relevant GAP-005–010/012/013 policy/format/cutoff/source basis and receipt | Request accepted separate from generated output | Operation owner; operation result; track | No unsupported estimate official; INV-013 |
| TR-263 | PROPOSED_WORKFLOW | S3 → record progress/result/failure → S3/S4/S5 | Operation responsible | Scope/time/source/policy versions, progress or reason unmeasurable, result/error | Honest accepted/running/completed distinction | Requester/operation owner; result/recovery; find outcome | EP-010; cannot claim complete without outcome |
| TR-264 | APPROVED_PRODUCT_FLOW | S3/S4/S5 → open result/history/source context → same confirmed scope | Requester berakses | Receipt/result/error links, origin filters, provenance/exceptions | Result findable again, canonical source inspectable | Requester/reviewer; relevant signoff; view/return | Access/notfound behavior RT-027, no full dataset load |
| TR-265 | PROPOSED_WORKFLOW | S2/S3/S4/S5 → review exception/new request/compare → same scope; new request S1 | Requester/reviewer/operation owner | Reason/input change, original result/source/version, known acceptance state | Historical final not overwritten; uncertainty follow-up | Exception/policy/operation owner; evidence; validate/check status | Retry unknown acceptance not automatic; official signoff GAP-010 |

<a id="wf-028"></a>

## WF-028 — Dokumen draft, review dan final evidence

| State ID / nama | Makna / entry | Actions / exit | Owner; waiting / evidence |
|---|---|---|---|
| WF028-S1 / Konteks sumber/varian dipilih | Parent workflow/source/version dan variant proposal available | TR-271/272/276 | Penyusun; values/applicability; parent/source |
| WF028-S2 / Varian/data/otoritas belum sahih | Required values/variant/number/clause/signature/applicability unknown | TR-271/272/273/274/276 | Penyusun/fungsi belum dipetakan; GAP-017/019 custodian; reason |
| WF028-S3 / Draft keluaran tersedia | Generation result sukses dari source/variant versions | TR-273/274/275/276 | Reviewer/penyusun; review/correction; draft/source provenance |
| WF028-S4 / Final/issue evidence tertaut bersyarat | Finality/issue/sign claims masing-masing diterima sesuai authority | TR-275/276; controlled new version | Relevant document/decision responsibility; applicable next parent work; validated evidence |
| WF028-S5 / Versi/bahan digantikan | Local draft withdrawn/validated supersession recorded | History/new-version relation | Decision/pemilik bahan; parent impact review; reason/source/version |
| WF028-S6 / Generasi gagal/belum terkonfirmasi | Failed/uncertain generation outcome recorded | TR-272/275/276 | Operation/penyusun; failure/status resolution; evidence error |

| TR | Authority | Dari / action / ke | Responsible | Prasyarat / bukti | Efek bisnis | Owner; tunggu; next | Failure / revision |
|---|---|---|---|---|---|---|---|
| TR-271 | PROPOSED_WORKFLOW | S1/S2 → validate source/variant/required values → S1/S2 | Penyusun/penelaah | Parent method guard WF-005, source/version, applicable variant/required values validated | Ready context atau named block, bukan official document | Penyusun/custodian; missing values/rule; complete | Filename/formula bukan authority; EP-002/003/004 |
| TR-272 | PROPOSED_WORKFLOW | S1/S2/S6 → generate labelled draft where basis valid → S3/S6 atau S2 | Penyusun/operation responsible | Supported generation scope, source/variant versions and result/receipt | Draft derived, source not overwritten | Reviewer/operation owner; review/result; inspect | Unknown template/value blocks S2, failure/unknown S6; EP-010 |
| TR-273 | PROPOSED_WORKFLOW | S3/S2 → review/correct source or draft according authority → S3/S2 | Reviewer/penyusun | Before–after/reason/source relation/allowed correction scope | Revised draft/source context tertelusur | Reviewer/penyusun; re-review/variant validation; assess | External final conflict EP-005, no silent source winner |
| TR-274 | BLOCKED_BY_GAP | S3/S2 → issue/finalize/accept signature evidence → S2 selama unknown; S4 conditional | Issuer/signer/reviewer responsibility belum dipetakan | GAP-003/004/013/017/019 variant/number/clauses/method/order/authority per claim | Final/issue/sign distinguished; no legal claim from draft | Parent/document responsibility; remaining applicable work; return parent | Digital signature FUTURE; unknown authority not finalized |
| TR-275 | APPROVED_DOMAIN_INVARIANT | S3/S4/S6 → inspect history/compare source change impact → same scope | Authorized reviewer/penyusun | Source/output/variant version links, reason, affected parent/outcome | Old final retains provenance; change may need new linked draft | Parent/change owner; impact assessment; validate new version if permitted | INV-005/006/018; no overwrite/conflicting truth |
| TR-276 | PROPOSED_WORKFLOW | S1–S4/S6 → withdraw draft/record validated supersession → S5 | Pemilik bahan/pihak decision sahih | Reason/authority if final/version and parent/downstream impact | Historical outputs/evidence retained | Decision/parent owner; impacted work; controlled follow-up | Official revocation unknown blocked GAP-017/019; EP-007 |

## Cakupan kandidat dan verification boundary

**28 WF / 27 stateful workflows / 148 TR**. TR blocks intentionally leave unused identifiers reserved; hanya baris `TR-xxx` di atas adalah transition/action definitions. Tidak ada extra implicit universal approve/reject/cancel transition. Interaksi yang available harus menunjuk TR applicable/guard-nya; blocked official actions mengikuti IX/EP/RT yang menjelaskan alasan dan next action validasi. Context-only entry bukan kontrak auth implementation.

State/TR baru tidak meredefinisi 77 DC, 29 BR, 18 INV atau responsibility model P2. [WORKFLOW_TRACEABILITY](WORKFLOW_TRACEABILITY.md) memetakan coverage, [WORKFLOW_DECISION_REQUESTS](WORKFLOW_DECISION_REQUESTS.md) mengelompokkan evidence yang diperlukan; GAP_REGISTER tetap pemilik status OPEN. Tidak ada schema/permission matrix/API/idempotency/design specification/Task. Kandidat P3 berakhir VERIFYING, menunggu independent review exact manifest dan Owner approval/corrections. P4–P11 dan implementasi tetap NOT AUTHORIZED.
