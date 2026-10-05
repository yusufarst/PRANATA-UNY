# Domain responsibilities — PRANATA UNY

Status: APPROVED | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P2 | Preparation: [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006), completed | Approval: [APPR-003](../00-governance/APPROVAL_RECORDS.md#appr-003) | Checkpoint: [AUTH-007](../00-governance/APPROVAL_RECORDS.md#auth-007), automatically completed after verified publication

Dokumen ini memiliki makna tanggung jawab dan batas pemetaannya. Istilah/definisi konsep DC dimiliki [DOMAIN_GLOSSARY](DOMAIN_GLOSSARY.md); hubungan, klasifikasi kebenaran dan identitas konseptual dimiliki [DOMAIN_MODEL](DOMAIN_MODEL.md). Seluruh rumusan aturan BR/INV dimiliki [BUSINESS_RULES](BUSINESS_RULES.md). P2 disetujui Owner under APPR-003 dengan semua kualifikasi dan kebutuhan validasi tanggung jawab tetap berlaku. Semua GAP yang disebut tetap OPEN dalam [GAP_REGISTER](../00-governance/GAP_REGISTER.md).

## Enam dimensi yang berbeda

Enam dimensi berikut membantu menjelaskan pekerjaan yang sama dari sudut berbeda. Pengelompokan ini adalah semantik domain; kombinasi dimensi belum menghasilkan izin tindakan. Dasar: [OD-09](../00-governance/DECISION_LOG.md#od-09), [OD-19](../00-governance/DECISION_LOG.md#od-19), [OD-20](../00-governance/DECISION_LOG.md#od-20); [BR-002](BUSINESS_RULES.md#br-002), [INV-001](BUSINESS_RULES.md#inv-001), [INV-002](BUSINESS_RULES.md#inv-002).

| Dimensi | Konsep | Pertanyaan yang dijawab | Batas validasi |
|---|---|---|---|
| Keluarga peran aplikasi | DC-006 | Kelompok kemampuan umum apa yang relevan bagi akun? | Internal User, Unit Admin, Central Operator, Reviewer / Approver, Vendor, Super Admin masih keluarga kandidat OD-09; kemampuan Owner Super Admin adalah OD-10. Detail keluarga dan kontrol tetap GAP-014/P5. |
| Cakupan organisasi | DC-001, DC-007 | Unit atau lingkup mana yang terkait dengan tanggung jawab ini? | Unit asal dan unit penanggung jawab dapat berbeda. Hierarki generik tidak memilih cakupan izin atau kewenangan lintas unit; GAP-001/014. |
| Konteks ruang kerja | DC-003 | Dari konteks Asset, Procurement, Vendor atau Super Admin pekerjaan dilihat? | Konteks pengalaman yang memakai data terhubung; rincian akses dan perpindahan P5/P7. |
| Penugasan proses/paket | DC-005 | Siapa yang ditugaskan pada proses, paket, kasus atau pekerjaan tertentu? | Penugasan mempunyai konteks, tanggung jawab dan relevansi waktunya; pemberian, delegasi, penghentian dan pengganti resmi GAP-003/014. |
| Tanggung jawab formal | DC-008 | Jabatan/fungsi bisnis apa yang menjadi dasar penugasan? | PPK, KPA, Pokja, Pejabat Pengadaan, Tim Teknis, Pemeriksa/Penerima dan Reviewer adalah contoh fungsi. Kewenangan aktual perlu sumber berlaku; GAP-003/004/013/014. |
| Tanggung jawab tindakan tertentu | DC-004, DC-009 | Siapa bertanggung jawab atas pekerjaan atau bukti tertentu dalam konteks tersebut? | Pemilik pekerjaan saat ini, pihak yang ditunggu dan pekerjaan berikut memerlukan makna proses yang tervalidasi; GAP-001–004/010, P3. |

Ringkasan konseptual **Role × Workspace × Organization Scope × Assignment** menjelaskan dimensi konteks; tanggung jawab formal dan tindakan spesifik menjelaskan dasar serta isi pekerjaan. P5 kelak menetapkan backend policy, konflik penugasan dan pemisahan tugas yang berlaku. Tidak ada pemetaan allow/deny, daftar aksi per role, atau pengecualian otorisasi dalam P2.

## Makna tanggung jawab pada pekerjaan

Nama dalam tabel adalah tipe tanggung jawab kontekstual, bukan role global baru atau jabatan yang wajib memiliki akun sendiri. Satu aktor dapat menjalankan lebih dari satu tipe apabila kebijakan yang nanti divalidasi membolehkannya; kebutuhan pemisahan orang/fungsi belum diputuskan (GAP-003/004/014).

| Tipe tanggung jawab | Makna dalam konteks | Referensi dan batas |
|---|---|---|
| Pihak asal/pengaju | Menjelaskan dari siapa/unit mana kebutuhan atau kiriman berasal; tetap relevan walau pihak yang mengerjakan sekarang berbeda. | DC-001/021/022; BR-001/005; AC-03/06; GAP-001. |
| Penanggung jawab pekerjaan | Pihak yang bertanggung jawab menjelaskan kemajuan atau hasil pekerjaan tertentu. Ini dapat berupa fungsi/unit sebelum aktor individual tervalidasi. | DC-005/009; BR-003; AC-05; GAP-001–003/010/014. |
| Pihak yang ditunggu | Pihak yang kontribusi, jawaban atau buktinya belum tersedia dalam pekerjaan tersebut. Berbeda dari pengaju dan pemilik pekerjaan. | DC-009; BR-003; AC-05/10/20; GAP-001–003/010. |
| Operator yang ditugaskan | Aktor pelaksana pencatatan/pelengkapan dalam lingkup penugasan tertentu; bukan otomatis pejabat yang menetapkan hasil. | DC-004/005; BR-002/013; AC-13/19; GAP-014/018. |
| Penelaah/reviewer | Menilai isi/bukti sesuai tujuan penelaahan dan mencatat hasil atau kebutuhan perbaikan. | DC-008/036/044; BR-004/011/023; GAP-003/004/010. |
| Pemberi persetujuan/approver | Menanggung keputusan persetujuan yang memang berlaku dalam proses; tidak ditentukan oleh label aplikasi saja. | DC-008/009; BR-002/004; GAP-001/003/014. |
| Penyusun/preparer/drafter | Menyiapkan bahan atau isi dokumen dari data terkait. | DC-011/012/040; BR-009/011; GAP-004/017/019. |
| Pemeriksa/checker | Memeriksa kelengkapan/konsistensi bahan atau dokumen; pemeriksaan ini belum berarti penerbitan atau tanda tangan. | DC-010/040; BR-011; GAP-004/017. |
| Penerbit/issuer | Menanggung tindakan penerbitan resmi dokumen ketika memang berlaku. | DC-014/040; BR-010/011; GAP-004/017. |
| Penanda tangan/signer | Menanggung pengesahan/tanda tangan sesuai dasar kewenangan dan varian yang berlaku. | DC-014/015/040; BR-010/011; GAP-003/004/017. |
| Pemeriksa/penerima hasil | Tanggung jawab memeriksa hasil dan/atau menerima serah terima; keduanya dapat berbeda berdasarkan prosedur. | DC-044/045/046; BR-012/013; GAP-003/018. |
| Penanggung jawab aset/kustodian | Tanggung jawab terkait aset/penempatan tertentu pada konteks waktu yang relevan; bukan otomatis pemilik secara hukum. | DC-051/054/055; BR-015; AC-14; GAP-014/018. |
| Penanggung jawab rekonsiliasi | Tanggung jawab menindaklanjuti perbandingan atau selisih dalam suatu kasus. | DC-071/072; BR-023; AC-20; GAP-010. |
| Pihak penerima/signoff rekonsiliasi | Tanggung jawab menerima hasil/penjelasan rekonsiliasi bila diperlukan kebijakan. Berbeda dari pihak yang mengerjakan selisih. | DC-071/072; BR-023; GAP-010. |
| Aktor penyedia | Bertindak untuk perusahaan/PIC/partisipasi yang terkait; hubungan representasi akun, PIC dan perusahaan belum final. | DC-002/028–035; BR-007/008; AC-09/10; GAP-014/015. |
| Pengelola sumber/kebijakan | Menjelaskan provenance, versi, keberlakuan dan makna sumber dalam lingkup keahliannya; pengakuan sebagai custodian belum otomatis membuktikan otoritas penerbit. | DC-018/020/067/073; BR-025; GAP-005/007/009/013. |

Konteks pekerjaan (DC-009) menghubungkan objek terkait, unit asal, status semantik, penugasan/pemilik pekerjaan, pihak yang ditunggu, pekerjaan berikut yang relevan, bukti, serta konteks waktu/tenggat bila ada. Ini mendukung [AC-05](../01-product/ACCEPTANCE_CRITERIA.md), AC-10 dan AC-20. Ketika pemetaan pihak atau pekerjaan berikut belum tervalidasi, konteks mencatat ketidakpastian dan GAP terkait. P3 nanti menentukan urutan serta keadaan yang membuat tanggung jawab berlaku; P7 menentukan penyajiannya. Tidak ada SLA/deadline atau actor assignment resmi baru di sini.

## Pemetaan tanggung jawab yang belum dapat ditetapkan

| Area | Yang tersedia dan dapat dibedakan | Yang tetap belum ditetapkan | Rujukan |
|---|---|---|---|
| Usulan unit–pusat dan RUP | Pihak asal, penerima pekerjaan, pemeriksa, pengembali untuk perbaikan dan pihak publikasi adalah fungsi yang berbeda. | Rantai persetujuan resmi seluruh jenis unit, pemilik RUP/publikasi dan delegasinya. | GAP-001/002/013; PROC-04/06/08/13 dalam [procurement review](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f03-rup-and-organizational-request-handoffs-are-candidate-evidence). |
| Metode dan aktivitas paket | Penugasan sesuai konteks metode, paket dan kegiatan. | Matriks jabatan/fungsi per metode; workshop, review, opening atau negosiasi sebagai gate wajib. | GAP-003/013/017; PROC-14–27; BR-004/006. |
| SPPBJ | Penyusun, pemeriksa, penerbit dan penanda tangan dibedakan secara semantik. | **Keempat pemetaan aktor tetap belum ditetapkan**; PPK/Pokja tidak dipilih universal. | GAP-004; PROC-02 table 6 step 14, PROC-10 table 6 step 11, PROC-12 table 1 row 3 item l dan PROC-22; [PR-F02](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f02-sppbj-responsibility-needs-a-precise-activity-distinction); BR-011. |
| Serah terima dan downstream | Pemeriksa/penerima, pembuat keputusan klasifikasi, pelengkap draft, penanggung jawab record hilir dibedakan. | Pemicu resmi, pihak yang menerima tanggung jawab, finalitas Asset/Persediaan/KDP dan koreksi lintas domain. | GAP-008/009/018; BR-012/013/021; AC-12/13/18. |
| Asset/Persediaan/Finance | Pelaksana pencatatan, kustodian, pihak penanganan selisih dan penerima hasil adalah tanggung jawab berbeda. | Ownership, waiting party, signoff dan penutupan periode resmi. | GAP-010/014; ASSET-04–09; BR-015/023. |
| Penyedia dan akun | Identitas perusahaan, kontak/PIC, akun dan partisipasi paket terpisah secara konseptual. | Bukti representasi, onboarding, kelayakan, pemberian akses dan visibilitas tepat. | GAP-003/014/015/017; BR-007/008; INV-003/004. |

Semua sumber PROC/ASSET adalah referensi pengamatan dengan batas pada [REFERENCE_COVERAGE](../01-product/REFERENCE_COVERAGE.md) dan [EVIDENCE_POLICY](../00-governance/EVIDENCE_POLICY.md). P1 approval tidak mengesahkan jabatan atau prosedur historis. [DOMAIN_DECISION_REQUESTS](DOMAIN_DECISION_REQUESTS.md) mengelompokkan validasi yang diperlukan; [DOMAIN_TRACEABILITY](DOMAIN_TRACEABILITY.md) memetakan seluruh kewajiban P1/GAP. P3/P5 dan semua implementasi tetap NOT AUTHORIZED.
