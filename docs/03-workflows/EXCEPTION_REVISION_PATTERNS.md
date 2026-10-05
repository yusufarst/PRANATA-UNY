# Exception and revision patterns — PRANATA UNY

Status: APPROVED (APPR-004) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P3 | Preparation: [AUTH-008](../00-governance/APPROVAL_RECORDS.md#auth-008) (HISTORICAL / COMPLETED) | Approval/checkpoint: [APPR-004](../00-governance/APPROVAL_RECORDS.md#appr-004) / [AUTH-009](../00-governance/APPROVAL_RECORDS.md#auth-009)

Dokumen ini memiliki **10 pola reusable EP-001–EP-010** untuk revision/exception/recovery pada tingkat produk. Pola hanya dipakai bila WF/transition terkait memperbolehkannya; tidak membuat state machine universal atau actor/domain authority baru. [STATE_TRANSITIONS](STATE_TRANSITIONS.md) tetap pemilik wording state/transition; [WORKFLOW_CONTRACTS](WORKFLOW_CONTRACTS.md) pemilik proses/gate; [INTERACTION_CONTRACTS](INTERACTION_CONTRACTS.md) pemilik IX success/failure/history; [ROUTE_CONTRACTS](ROUTE_CONTRACTS.md) pemilik contextual return. BR/INV hanya dirujuk ke sole canonical [BUSINESS_RULES](../02-domain/BUSINESS_RULES.md).

## Kontrak recovery bersama

Setiap EP harus membuat pengguna memahami **apa yang terjadi; apakah bahan tersimpan, tindakan diterima atau hasil belum dapat dipastikan; siapa yang bertanggung jawab dan siapa/apa yang ditunggu; next action yang aman; evidence/history; dan return ke parent**. Pihak belum ditugaskan atau pemetaan official belum disahkan tidak disamakan dengan tidak ada pihak tunggu. Tampilkan fungsi/unit contextual yang sudah diketahui, atau status pending/unresolved dan GAP; jangan menambah aktor palsu agar flow tampak lengkap.

Generic interaction pattern di bawah berklasifikasi **PROPOSED_WORKFLOW** dengan tujuan produk **APPROVED_PRODUCT_FLOW** dari OD-19/CAP-03/18/19/20 dan AC-05/21/22/24. Batas domain spesifik mengikuti **APPROVED_DOMAIN_INVARIANT** yang dirujuk dengan qualifications P2. Official meaning/authority yang belum tervalidasi tetap **BLOCKED_BY_GAP**. Tidak ada EP yang menyelesaikan GAP; **GAP-001–GAP-020 tetap OPEN** menurut [GAP_REGISTER](../00-governance/GAP_REGISTER.md).

Recovery bukan persetujuan untuk melakukan mutasi ulang tanpa mengetahui hasil sebelumnya. Invalid input yang diketahui belum accepted dapat diperbaiki; unknown result memerlukan receipt/history/status dari konteks yang sama lebih dulu; policy/actor/identity gate memerlukan evidence/decision/assignment. P6 kelak menetapkan mekanisme teknis concurrency/retry/idempotency/operasi panjang. Tidak ada algorithm, deadline atau queue contract di P3.

## EP-001

**Nama:** Revision requested / perbaikan yang diminta.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Generic interaction pattern**, hanya pada WF yang mempunyai revision loop; submitted/reviewed bahan mempunyai kebutuhan perbaikan yang boleh diminta. Kebutuhan perbaikan bukan rejection/cancellation otomatis. |
| Owner / waiting / next | Penelaah/pihak pengembali mencatat reason/scope; pihak pengirim/pelengkap menjadi waiting party bila diketahui; current owner sesuai titik WF. Bila individual belum assigned tampilkan pending assignment yang dibolehkan; jika official mapping unknown, named GAP tetap gate. Next safe action: baca alasan/source, lengkapi hal relevan lalu IX-005 resubmit bila permitted. |
| Saved / outcome / evidence | Revision request yang tercatat menjelaskan apa yang perlu diperbaiki dan versi asal. Corrected submission menghasilkan receipt/version/history baru dan re-review, bukan accepted content otomatis. Prior source/submission/revision reason/evidence tetap dapat ditelusuri; pengguna diberi tahu apakah corrected draft tersimpan atau revision sudah diterima. |
| Failure / retry / return | Incomplete correction tetap diperlihatkan dalam parent yang sama; target revised/stale → review current source/compare lebih dulu. Receipt unknown → IX-012 sebelum ulang. RT parent menyimpan request/reason; evidence/history/profil child kembali same request/participation/parent. |
| Applicability / batas domain | WF-003/007/008 dan review WF yang applicable; IX-004/005/006/011/012. Provider menggunakan company/PIC/qualification yang masih valid tanpa unrelated re-entry; central operator sah dan provider melihat outcome consistent untuk revision yang sama, dengan protected scope tetap terjaga. BR-007/008/026; INV-003/004/006/017; GAP-001/003/014/017 sesuai WF. |

## EP-002

**Nama:** Required evidence missing atau evidence tidak memenuhi dasar yang applicable.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Generic interaction pattern**; required evidence yang sudah tervalidasi belum tersedia/tidak relevan/versinya tidak sesuai untuk claim/transition. Checklist official yang belum sahih tidak boleh diada-adakan; gunakan EP-003/004 bila requirement sendiri unknown. |
| Owner / waiting / next | Owner claim/work tetap dikenal atau pending/unresolved dijelaskan; waiting party ialah pemberi bukti yang memang diketahui dari assignment, bukan presumed signer/Finance. Next: buka requirement/claim dan attach/replace linked evidence via IX-011, atau minta validasi source/applicability bila unknown. |
| Saved / outcome / evidence | Draft yang memang tersimpan tetap draft; submit/accept/generate official claim dependent tidak dinyatakan sukses. Catat evidence yang kurang dan context keputusan; keberadaan attachment baru belum mengesahkan authenticity/content/claim. |
| Failure / retry / return | Failed attachment menjelaskan association/file tersimpan atau unknown; cek evidence/history jika mungkin diterima sebelum ulang. View unavailable tetap mempertahankan parent dan safe read retry; RT-031 → exact parent/activity/participation/case/difference. |
| Applicability / batas domain | Semua WF evidence; IX-003/006/007/010/011/015. SPPBJ/signature/BAST/SPJ/kualifikasi/rekonsiliasi mempunyai evidence meaning berbeda; tidak menyamakan kelengkapan berkas dengan payment/downstream acceptance. BR-009/010/012/023; INV-005/012/014; GAP-013/017/018/019 dan GAP claim. |

## EP-003

**Nama:** Policy/procedure/actor authority unresolved.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Generic authority-gate pattern** dengan domain-specific dependency: decision, order, actor, cutoff, policy atau official source yang dibutuhkan transition belum tervalidasi. Ini blocked gate, bukan user validation error yang dapat dilewati. |
| Owner / waiting / next | Work owner yang sudah diketahui dapat menjaga bahan/context; waiting contribution ialah decision/evidence oleh resolver yang tercatat dalam GAP_REGISTER. Jika responsibility official unknown, tampilkan belum ditetapkan dan named GAP. Next: kumpulkan safe provenance/decision request/review evidence; jangan memilih fungsi/approval chain sendiri. |
| Saved / outcome / evidence | Safe draft/reference/penjelasan yang diizinkan WF boleh tetap tersimpan pending/unassigned; official dependent transition tetap belum diterima/blocked. Catat gate, affected claim/context dan source limits, tanpa menganggap proposal sebagai official answer. |
| Failure / retry / return | Repeated retry tidak menyelesaikan authority; jangan default to historical policy/template/date/actor atau future integration. Setelah actual validated decision tersedia, impact review dan gate reassessment sesuai change control. Parent/source/decision request context tetap dapat dibuka bila sah. |
| Applicability / batas domain | WF-001–028 pada dependent claims, mencakup seluruh WF pemanggil eksplisit pada WORKFLOW_CONTRACTS/STATE_TRANSITIONS. Contoh: unit routing GAP-001; RUP GAP-002; method actor GAP-003; SPPBJ GAP-004; cutoff GAP-007; effects GAP-008/009; signoff GAP-010; cutover GAP-012; source authority GAP-013; scope/account GAP-014/015; document GAP-017/019; downstream GAP-018. IX-006/007/014/015/018/021. BR-004/010/011/017/018/025; INV-013/018. |

## EP-004

**Nama:** Method applicability unknown/conditional.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Domain-specific procurement gate** reusable pada action/activity/document terkait: metode, applicable step/requiredness/order/variant atau evidence/source belum tervalidasi. Unknown berbeda dari optional/not applicable yang mempunyai basis. |
| Owner / waiting / next | Owner paket/activity/document yang diketahui menjaga context; contribution waiting dari procurement/source custodian menurut GAP, actor final masih unresolved bila demikian. Next: telaah method context/source; validasi required/optional/not-applicable serta scope/order hanya dengan authority. |
| Saved / outcome / evidence | Bahan aman dapat disimpan sebagai context/candidate dengan applicability unknown/conditional dan GAP. Dependent official action tidak active hanya karena filename tender/direct atau activity label tersedia; outcome pending/blocked dinyatakan. |
| Failure / retry / return | Tidak fallback ke satu universal procurement sequence; mixed tender-heading/direct-body tetap ambiguity. Retry hanya pembacaan/source setelah tersedia; actual applicability decision dicatat sebelum reassess. Return exact package/assessment/activity/document parent. |
| Applicability / batas domain | WF-004–006/008–015/028; IX-003/006/007/010/020/021; RT-005–015/028. BR-004/006/010/011; GAP-003/013/017, GAP-004/019 sesuai action. Pengadaan Langsung/Tender keluarga awal P1, bukan pengesahan semua metode/urutan/signature. |

## EP-005

**Nama:** Conflicting historical source / konflik sumber atau versi keluaran.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Generic source-conflict pattern** dengan domain-specific rule: source/candidate/generated/uploaded/final evidence berbeda meaning/value/version atau source authority belum jelas. Perbedaan dapat menjadi issue yang perlu keputusan, bukan bukti siapa otomatis salah. |
| Owner / waiting / next | Penanggung jawab bahan/kasus menjaga konteks dan source; waiting evidence/decision dari custodian/resolver sesuai GAP. Next: IX-013 compare source/version/scope, catat limitation serta IX-015 disposition candidate; official acceptance menunggu authority applicable. |
| Saved / outcome / evidence | Raw/original/final historical source tetap berprovenance; interpreted/corrected/rejected decisions terhubung terpisah. Nyatakan penjelasan tersimpan versus record accepted. Tidak memilih Intra/Intra New atau generated output sebagai source winner tanpa basis. |
| Failure / retry / return | Source unavailable/stale → compare belum selesai; jangan invent value/date atau silently overwrite. Re-open same source/compare/history setelah tersedia; candidate/result unknown check history dulu. RT source → parent case/intake/document/correction yang sama dan scope/time terjaga. |
| Applicability / batas domain | WF-002–005/007–013/015–020/022–028 pada konflik sumber yang applicable; IX-002/006/010–013/015/018. BR-009/010/024/025/029; INV-005/006/015/018; GAP-005–013/017/019 sesuai source. Header equivalence/formula cache/scan signature bukan trusted truth; credential values tidak direproduksi. |

## EP-006

**Nama:** Duplicate ambiguity / identitas belum pasti.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Generic identity-review pattern** dengan rule domain: potential duplicate/same-label/same-number tidak cukup untuk memutus identitas candidate/company/package/asset/item/KDP/source sama atau berbeda. |
| Owner / waiting / next | Reviewer/intake owner yang diketahui menjaga kandidat; waiting evidence/identity rule oleh relevant resolver sesuai GAP. Next: buka allowed source/version/claim, compare serta record proposed disposition; official merge/reject/accept menunggu identity rule dan authority. |
| Saved / outcome / evidence | Issue/candidate tersimpan unresolved dengan origin; duplicate verdict hanya bila explicit validated identity rule tersedia. Tidak menggabung company berdasarkan nama, menghapus row mirip atau menganggap nomor template unique. |
| Failure / retry / return | Evidence/rule absent → dependent acceptance/merge blocked, perbaikan input unrelated tidak menyelesaikan identity. Safe read/compare ulang bila evidence baru; uncertain outcome history dahulu. Return source/candidate/company/asset/item/KDP same parent, bukan record mirip sebagai fallback. |
| Applicability / batas domain | WF-007/008/017/023/024/026 serta identity-sensitive WF; IX-001/006/007/013/015. BR-024/029; INV-015/017; GAP-011/013/014/017 sesuai object. Tidak membuat identifier format/uniqueness/database enforcement. |

## EP-007

**Nama:** Cancelled/superseded work dengan dampak yang explainable.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Generic lifecycle pattern**; cancellation/supersession diperbolehkan pada WF dan authority applicable. Keluar form tanpa save adalah navigation cancellation tersendiri, bukan business cancellation. |
| Owner / waiting / next | Pihak berwenang melakukan decision serta reason; parties/outcomes terdampak yang diketahui terlihat, belum ditetapkan dinyatakan. Next: telaah scope/dampak/replacement/evidence, confirm material action IX-009 hanya bila permitted. |
| Saved / outcome / evidence | Decision/reason/history/source/replacement retained; final/generated output lama mempunyai version/provenance serta kedudukan yang explainable. Nyatakan scope cancelled/superseded, bukan semua proses/kontrak/payment/period otomatis dibalik. |
| Failure / retry / return | Authority/effect unknown → action blocked; berubahnya related source memerlukan impact review. Hasil unknown cek history; cancellation tidak delete evidence atau restore prior accepted truth. Return historical parent atau replacement dengan source-return jelas, bukan stranded detail. |
| Applicability / batas domain | WF yang mengizinkan action; IX-009/012/013/021; RT domain/030. BR-026; INV-008/017/018; GAP-001–004/008/010/012/013/017/018 sesuai work. Archive/restore/delete/anonymization tidak diotorisasi dari pola ini. |

## EP-008

**Nama:** Downstream classification/eligibility/handoff blocked.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Domain-specific downstream gate**; source perolehan/pemeriksaan/handover atau KDP completion kandidat ada, tetapi trigger/eligibility/classification/penerima/acceptance rule belum terpenuhi atau unknown. |
| Owner / waiting / next | Source work owner tetap diketahui atau pending/unresolved terlihat; classification decision/recipient assignment waiting menurut GAP, bukan otomatis petugas aset tertentu. Next: telaah source/evidence/policy; validasi event/jenis tujuan/authority melalui IX-014/021 jika tersedia. |
| Saved / outcome / evidence | Source/handoff candidate tetap **needs classification/blocked** sesuai WF. Bila rule validated menghasilkan draft tujuan, draft masih membutuhkan missing info/review/acceptance; jelaskan actual saved/accepted claim. Source/decision/version/outcome evidence retained. |
| Failure / retry / return | Jangan guess dari filename/account label/book value/BAST alone atau progress/KDP unfinished. Unknown policy tidak selesai dengan retry; ambiguous outcome cek handoff/history. RT-016 mempertahankan source ↔ target safe return; destination forbidden/unavailable tetap source sah atau fallback yang dijelaskan. |
| Applicability / batas domain | WF-014/016/017/023/024; IX-014/007/021; RT-016/017/023/024. BR-013/018/021; INV-006/009/011/012; GAP-008/009/014/018. BAST evidence, serah terima, payment, classification dan definitive record tidak dipersamakan. |

## EP-009

**Nama:** Reconciliation difference memerlukan explanation/evidence dan acceptance yang terpisah.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Domain-specific reconciliation pattern**; expected relationship yang applicable terhadap source/scope/time menghasilkan selisih/exception, atau expected relationship sendiri belum dapat divalidasi. Tidak mengarang equality/tolerance. |
| Owner / waiting / next | Pihak tindak lanjut yang assigned dan kontribusi source/explanation waiting terlihat; official owner/waiting/signoff/cadence yang belum sahih tetap GAP-010. Next: buka difference/source, attach evidence/penjelasan, compare atau ajukan accepted correction sesuai WF; bila relationship unknown, EP-003 sebelum claim numeric comparison. |
| Saved / outcome / evidence | Selisih/scope/source/time/rule/context serta explanation/disposition retained. Explanation tersimpan belum resolved; no difference belum official signoff. Resolved claim hanya jika traceable evidence/prerequisites satisfied dan tidak mengklaim signoff yang belum diperoleh. |
| Failure / retry / return | Missing evidence/rule/signoff → tetap exception/pending/blocked dalam scope yang tepat; jangan ubah source supaya cocok atau menyembunyikan difference. Uncertain decision cek case history dulu. Case → Difference → Evidence/Source → same Difference/Case; source forbidden menjaga allowed case tanpa bocoran. |
| Applicability / batas domain | WF-025/026/027 dan reconciliation-related WF-015/017/019/020/023/024; IX-006/007/011–013/015/018; RT-025/031. BR-023/024; INV-014/015/018; GAP-010/012 serta GAP source/policy. Numeric consistency tidak membuktikan institutional acceptance. |

## EP-010

**Nama:** Long operation accepted tetapi belum complete / result belum dapat dipastikan.

| Aspek | Kontrak |
|---|---|
| Jenis / trigger | **Generic operation-feedback pattern**; import/report/reconciliation/document operation membutuhkan waktu atau outcome action belum dapat dipastikan. Operation accepted, working, completed within scope dan failed adalah feedback meanings konseptual; wording state spesifik tetap STATE_TRANSITIONS/WF, bukan shared database statuses. |
| Owner / waiting / next | Requester/work owner diketahui atau pending sesuai WF; waiting dapat result/proses yang belum selesai dengan reason, tidak harus invented human actor. Next: open identity/status/history operasi yang sama, lanjutkan pekerjaan lain, lalu return result/failure; required human follow-up/evidence dinyatakan bila diketahui. |
| Saved / outcome / evidence | Pesan accepted menjelaskan scope/identity/context dan **belum selesai**. Status dapat ditemukan kembali; expose progress atau reason tidak dapat dihitung, elapsed context serta result/exception bila tersedia. Completed menyatakan actual output/scope/provenance; failed menjelaskan accepted/saved portions bila known. No-data tidak dipakai sebagai label failure/blocked/pending. |
| Failure / retry / return | Failure menyebut apa yang gagal, actual acceptance yang known atau unknown, next responsible contribution, safe next action. Bila outcome uncertain, jangan request ulang sebelum status/receipt/history diperiksa. Cancel/abandon view tidak menyatakan operation cancelled; operation cancellation hanya jika supported WF/authority. P6 memutuskan technical retry/concurrency/cancellation/limits kelak. |
| Applicability / batas domain | WF-022/025–028 dan conflict-sensitive actions terkait; IX-003/005/007/010/015/018; RT domain/027/028/030. APPROVED_PRODUCT_FLOW CAP-18/AC-21/NFR-P01–04 sebagai proposal P1 yang tetap qualified; PROPOSED_WORKFLOW feedback behavior. GAP-016 workload/capacity dan GAP official output/source tetap OPEN. No queue/API/idempotency/performance algorithm. |

## Penerapan khusus yang tidak boleh dinormalisasi oleh pola generic

| Area | Batas pemakaian pola |
|---|---|
| Provider submission/revision | EP-001/002/010 menjaga same participation/submission/revision receipt/history/outcome yang konsisten bagi provider/central operator sah; tidak menuntut unrelated company re-entry atau membuka other-provider protected data. |
| SPPBJ | EP-003/004 menjaga preparer, checker, issuer dan signer sebagai tanggung jawab berbeda; tidak mengisi universal PPK/Pokja. Decision request menjaga GAP-004 OPEN. |
| Persediaan / P01/P02 | EP-003/005/006/009 tidak mengubah raw code atau memilih alias/version/typo/operation meaning. Kandidat interpretation tetap berbeda dari accepted ledger; unknown mapping menahan dependent accepted stock/report claim. GAP-005/006 OPEN. |
| Physical asset condition/disposal | EP-002/003 tidak mengganti observasi dengan depreciation age/life/book value/zero. Proposal removal, accepted lifecycle decision dan delete history berbeda; GAP applicable tetap gate. |
| KDP | EP-003/008 menjaga unfinished work tetap context KDP bila applicable; progress/completion candidate tidak otomatis capitalization atau definitive Asset. |
| Reporting/accounting | EP-003/005/010 menjaga position/as-of, transaction range, periodic dan reconciliation meaning masing-masing. Unknown cutoff/policy/formula/format/BMU tidak menghasilkan official-looking estimate atau semua filter accepted. |
| Historical intake/cutover | EP-005/006/009/010 mempertahankan source/candidate/cleansing/rejection/accepted record distinctions dan provenance. Credential-like plaintext tidak masuk account password; cutover/write ownership unknown GAP-012 tidak diselesaikan oleh import accepted. |
| Future integration absent | Generic unavailable/failure bukan alasan mewajibkan SSO/API/email/signature/storage future. Local reference/manual evidence dapat dicatat bila rules validated; kewajiban resmi di kanal eksternal tetap berlaku dan bukan aksi eksternal yang dilakukan PRANATA. |

Pola ini memberikan safe recovery/return yang konkret tanpa menjawab authority yang belum ada. P8/P10 kelak memverifikasi skenario applicable, evidence, privacy dan output correctness; browser E2E critical journeys dilakukan saat application/execution authorized. Review dokumentasi P3 bukan klaim runtime, official procurement/accounting validity, successful import atau completed report.
