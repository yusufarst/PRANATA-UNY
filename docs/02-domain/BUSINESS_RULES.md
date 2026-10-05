# Business rules — PRANATA UNY

Status: APPROVED | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent | Phase: P2 | Preparation: [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006), completed | Approval: [APPR-003](../00-governance/APPROVAL_RECORDS.md#appr-003) | Checkpoint: [AUTH-007](../00-governance/APPROVAL_RECORDS.md#auth-007), automatically completed after verified publication

Dokumen ini memiliki **kata-kata kanonik 29 aturan BR-001–BR-029 dan 18 invariant INV-001–INV-018**. [DOMAIN_GLOSSARY](DOMAIN_GLOSSARY.md) memiliki istilah, [DOMAIN_MODEL](DOMAIN_MODEL.md) konsep/relasi, [DOMAIN_RESPONSIBILITIES](DOMAIN_RESPONSIBILITIES.md) tanggung jawab, [DOMAIN_LIFECYCLES](DOMAIN_LIFECYCLES.md) makna lifecycle, dan [DOMAIN_TRACEABILITY](DOMAIN_TRACEABILITY.md) pemetaan balik. Dokumen lain merujuk ID ini; tidak membuat salinan aturan yang dapat bersaing.

P0/P1 tetap DONE. Persetujuan P1 [APPR-002](../00-governance/APPROVAL_RECORDS.md#appr-002) mempertahankan semua kualifikasi hipotesis/usulan dan kewajiban validasinya. P2 ini APPROVED under APPR-003 dengan kualifikasi yang tetap berlaku; persetujuan katalog konseptual tidak mengesahkan kebijakan institusi yang belum tervalidasi. P3–P11 tetap TODO / NOT AUTHORIZED; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY; Execution NOT AUTHORIZED; application implementation NONE. Penyerahan ke fase kelak berarti kewajiban ketika fase itu memperoleh otorisasi terpisah.

## Cara membaca otoritas dan status

| Status aturan | Makna |
|---|---|
| APPROVED_OWNER_DIRECTION | Arah/batas produk langsung dari Owner. Kata-kata candidate, proposed, future, dan unresolved pada sumber tetap berlaku. Bukan otomatis SOP atau aturan akuntansi resmi. |
| EVIDENCE_SUPPORTED | Konsep atau pola didukung pengamatan sumber pada batas inspeksi yang tercatat. Penggunaan/otoritas terkininya belum terbukti. |
| PROPOSED | Semantik domain atau hipotesis produk kandidat untuk review P2; tidak memperoleh otoritas resmi dari model yang diusulkan. |
| BLOCKED_BY_GAP | Isi resmi yang dibutuhkan belum dapat ditentukan. Pernyataan mengidentifikasi batas aman dan validasi yang wajib dipenuhi; tidak mengisi jawaban GAP. |
| FUTURE | Di luar dependency V1; memerlukan keputusan lingkup dan otorisasi baru. |
| OUT_OF_SCOPE | Dikecualikan dari V1 yang disetujui dengan kualifikasinya; keberadaan sumber tidak menambah scope. |

**Status utama berlaku pada pernyataan dalam entri itu saja.** Dukungan Owner, pengamatan, proposal dan isi yang masih blocked dibedakan pada otoritas/kualifikasi setiap entri. Invariant adalah hal yang harus tetap benar untuk domain PRANATA; tidak otomatis merupakan equality akuntansi. Tidak ada sumber lokal pengadaan/akuntansi yang baru diautentikasi sebagai kebijakan resmi pada P2 ini. Hierarki dan konflik mengikuti [SOURCE_OF_TRUTH](../00-governance/SOURCE_OF_TRUTH.md#authority-hierarchy); original/locator mengikuti [SOURCE_INVENTORY](../00-governance/SOURCE_INVENTORY.md). Nomor § pada AUTH-006 menunjuk bagian instruction Owner P2 [U-003](../00-governance/SOURCE_INVENTORY.md#u-003-p2-owner-request); provenance/fingerprint dan scope durabel berada pada record pemilik tersebut.

Semua dependency di bawah mengacu pada [GAP_REGISTER](../00-governance/GAP_REGISTER.md): **GAP-001–GAP-020 tetap OPEN**. Tidak adanya GAP pada batas Owner tertentu tidak menjadikan desain lengkap. Sumber/formula/heading historis bukan otoritas untuk nomor, signature, klasifikasi, threshold, debit/kredit, cutoff atau prosedur. Rule ID stabil; koreksi bermakna memerlukan provenance/review melalui [CHANGE_CONTROL](../00-governance/CHANGE_CONTROL.md), bukan penggantian diam-diam.

## Katalog aturan

### BR-001

**Judul:** Organisasi generik dan asal historis.

- **Domain:** Organization Unit; hierarki, asal dan tanggung jawab organisasi.
- **Pernyataan:** Organization Unit adalah konsep organisasi umum yang dapat memiliki hubungan induk/anak; asal kebutuhan/usulan dan unit penanggung jawab harus dapat dibedakan bila berbeda. Perubahan struktur organisasi tidak boleh menghilangkan jejak asal record historis.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION untuk struktur generik dan asal; semantik validitas/perubahan struktur merupakan proposal P2. [OD-04/05](../00-governance/DECISION_LOG.md#od-04); [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §8.
- **Cakupan:** [CAP-02/04](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-03/06](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-001/014 OPEN; P3 validasi routing, P4 representasi organisasi, P5 scope/enforcement. **Kualifikasi:** Fakultas bukan root wajib atau satu-satunya asal; belum ada aturan reorganisasi, tanggal validitas atau izin detail yang disahkan.

### BR-002

**Judul:** Dimensi tanggung jawab dan role ringkas.

- **Domain:** Application Role Family, Organization Scope, Workspace Context, Assignment dan Business Responsibility.
- **Pernyataan:** Role aplikasi, cakupan organisasi, konteks workspace, penugasan proses/paket, tanggung jawab formal dan tanggung jawab suatu tindakan mempunyai makna berbeda; aplikasi memakai keluarga role yang ringkas sambil mempertahankan tanggung jawab bisnis yang relevan.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION; [OD-09](../00-governance/DECISION_LOG.md#od-09), [OD-10/20](../00-governance/DECISION_LOG.md#od-20), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §9. Daftar role tetap candidate.
- **Cakupan:** [CAP-02/03/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-02/04/05/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-003/004/014 OPEN; P3 pemetaan tanggung jawab per proses, P5 model izin. **Kualifikasi:** Super Admin Owner tetap arah produk; tidak menetapkan pengecualian invariant, signatory, delegasi atau permission matrix.

### BR-003

**Judul:** Makna pemilik tindakan, pihak tunggu dan tindakan berikut.

- **Domain:** Work/Action Context dan Status.
- **Pernyataan:** Makna pekerjaan harus menghubungkan asal/konteks, status saat ini, pemilik tindakan, pihak yang ditunggu, tindakan berikut yang berlaku dan bukti. Jika tidak ada pihak tunggu/tindakan atau tanggung jawab belum diketahui, keadaan tersebut harus dapat dijelaskan.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION; [OD-19](../00-governance/DECISION_LOG.md#od-19), [PRODUCT_EXPERIENCE_DIRECTION](../01-product/PRODUCT_EXPERIENCE_DIRECTION.md#login-konteks-workspace-dan-tindakan-berikutnya), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §45/49.
- **Cakupan:** [CAP-03/04/07/17](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-05/06/10/20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-001–004/010/014 OPEN; P3 status/prosedur resmi, P5 akses, P7 penyajian. **Kualifikasi:** Tidak menciptakan deadline/SLA, state machine, urutan approval, prioritas atau layar.

### BR-004

**Judul:** Keberlakuan aturan menurut metode.

- **Domain:** Procurement Method dan applicability.
- **Pernyataan:** Keberlakuan aktivitas, tanggung jawab, dokumen dan aturan pengadaan harus dinilai terhadap metode/konteks yang relevan; isi satu metode atau template tidak dapat dijadikan prosedur universal.
- **Otoritas/status:** BLOCKED_BY_GAP untuk daftar/applicability resmi. Batas kehati-hatian adalah arah Owner [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §10/11; [PR-F05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f05-historical-procurement-outputs-need-method-specific-validation), PROC-21 hal. 1, menunjukkan judul tender dengan body pengadaan langsung.
- **Cakupan:** [CAP-05/08](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-07/11](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-003/013/017 OPEN; P3 memvalidasi prosedur/metode, P5 tanggung jawab. **Kualifikasi:** Pengadaan Langsung/Tender tetap cakupan awal usulan P1; filename tidak menentukan metode atau mengesahkan pertukaran prosedur.

### BR-005

**Judul:** Hubungan kebutuhan, usulan, RUP dan paket.

- **Domain:** Need, Procurement Request, Planning/RUP Reference dan Procurement Package.
- **Pernyataan:** Kebutuhan, pengajuan, referensi RUP/perencanaan dan paket merupakan konsep berbeda yang hubungan asalnya harus dapat ditelusuri; referensi RUP tidak menggantikan bukti usulan atau paket.
- **Otoritas/status:** PROPOSED untuk relasi konseptual; kebutuhan traceability dari [OD-05/06](../00-governance/DECISION_LOG.md#od-05) dan [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §12. [PR-F03](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f03-rup-and-organizational-request-handoffs-are-candidate-evidence), PROC-06 tabel 6 baris 3–8 dan PROC-08 tabel 1 baris 3, hanya lane/handoff kandidat.
- **Cakupan:** [CAP-04/05](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-06/07](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-001/002/003/013 OPEN; P3 handoff/publikasi/revisi resmi. **Kualifikasi:** Tidak menetapkan cardinality wajib, publication owner, tanggal publikasi atau integrasi SiRUP.

### BR-006

**Judul:** Aktivitas dan bukti hasil terkait paket.

- **Domain:** Package Activity/Event.
- **Pernyataan:** Aktivitas terkait paket dapat mempunyai kategori/tujuan, waktu terjadwal, peserta internal/eksternal, bahan/undangan dan bukti hasil yang dapat ditelusuri ke konteks paket.
- **Otoritas/status:** EVIDENCE_SUPPORTED untuk pola aktivitas; kebutuhan produk dari [OD-23](../00-governance/DECISION_LOG.md#od-23). [PROC-23–27](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#file-level-inventory) hal. 1–2 menunjukkan pembukaan, workshop HPS, review desain/penawaran dan negosiasi; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §13.
- **Cakupan:** [CAP-06](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-08](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-003/013/017 OPEN; P3 applicability/hasil, P5 visibilitas, P7 pengalaman kelak. **Kualifikasi:** Contoh kegiatan bukan gate wajib semua metode; peserta undangan bukan role global atau bukti kehadiran.

### BR-007

**Judul:** Penggunaan ulang profil perusahaan dan versinya.

- **Domain:** Provider/Company, PIC, Provider Profile dan Qualification Evidence.
- **Pernyataan:** Informasi perusahaan/PIC dan bukti kualifikasi yang masih berlaku dapat digunakan ulang untuk partisipasi yang relevan; kebutuhan pembaruan harus mempunyai alasan. Riwayat versi harus memungkinkan membedakan informasi perusahaan yang berlaku sekarang dari informasi yang digunakan pada konteks historis.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION untuk reuse; versioning konseptual mengikuti [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §14/33. [OD-23](../00-governance/DECISION_LOG.md#od-23); [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families), PROC-30 `Input!AU2:BX2`, mendukung keberadaan kelompok company/PIC.
- **Cakupan:** [CAP-07/08](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-09/11](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-003/014/015/017 OPEN; P3 validity/onboarding, P5 account/PIC/company relation. **Kualifikasi:** Tidak menetapkan kelayakan hukum, open self-registration, wajib dokumen, masa berlaku atau data legal cukup sekali seumur hidup; lihat INV-003.

### BR-008

**Judul:** Partisipasi, pengiriman dan revisi penyedia.

- **Domain:** Provider Participation, Submission, Revision dan Provider-visible History.
- **Pernyataan:** Partisipasi menghubungkan perusahaan dengan konteks paket; pengiriman, revisi, penerimaan dan hasil yang sama harus mempunyai bukti/riwayat yang dapat ditelusuri serta status yang konsisten bagi penyedia dan operator pusat relevan yang berwenang.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION pada tingkat hasil produk P1; [AC-09/10](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional) yang disetujui [APPR-002](../00-governance/APPROVAL_RECORDS.md#appr-002), [OD-19/23](../00-governance/DECISION_LOG.md#od-23), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §14/48.
- **Cakupan:** [CAP-03/07/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-09/10/22.
- **Dependency/penyerahan:** GAP-003/014/015/017 OPEN; P3 tahap/revisi/hasil yang berlaku, P5 visibility/enforcement. **Kualifikasi:** Consistency tidak berarti kedua pihak melihat seluruh detail yang sama; partisipasi bukan profil perusahaan baru, penawaran pihak lain atau hak melihat evaluasi terlindungi.

### BR-009

**Judul:** Data terstruktur, dokumen dan bukti.

- **Domain:** Structured Data, Generated Document, External Evidence dan Signed/Final Evidence.
- **Pernyataan:** Data domain/proses terstruktur, keluaran yang dihasilkan, bukti eksternal yang diunggah, bukti final/bertanda tangan dan revisi historis mempunyai fungsi berbeda. Dokumen turunan harus dapat ditelusuri ke data/konteks/versi asalnya; konflik terhadap record domain memerlukan penjelasan dan penyelesaian yang berwenang.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION; [OD-08/23](../00-governance/DECISION_LOG.md#od-08), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §15/32. [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families), PROC-30 `Input`/`Mail Merge`, mengamati derivasi tanpa audit nilai.
- **Cakupan:** [CAP-08/10/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-11/13/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-013/017/019 OPEN; P3 authority/variant/revision handling, P5 akses. **Kualifikasi:** Bukti eksternal/final dapat mempunyai nilai otoritatif sesuai validasi; model tidak menganggap data terstruktur selalu mengalahkan dokumen sah. Lihat INV-005/006.

### BR-010

**Judul:** Otoritas varian dan isi dokumen.

- **Domain:** Document Variant, numbering/signature dan field/formula trust.
- **Pernyataan:** Varian, field kanonik, nomor, klausul dan tanggung jawab signature dokumen hanya dapat difinalkan dari sumber yang valid dan applicability yang dikonfirmasi; formula/cache/template historis tidak menentukan isi resmi.
- **Otoritas/status:** BLOCKED_BY_GAP; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §15/33/53. [PR-F01/04/05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families): metadata template kosong, PROC-21 hal. 1 campuran metode, PROC-30 `Isian Nomor!A2:AN2`/`SPJ PL!C1267` dengan dependency eksternal yang belum diaudit.
- **Cakupan:** [CAP-05/08/09/16](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-07/11/12/19](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-003/013/017/019 OPEN; P3 output/nomor/variant, P5 signature capability, P6/P10 trust. **Kualifikasi:** Tidak menyalin rumus pajak/pembayaran, nomor aktual, klausul atau target dependency eksternal; digital signature tetap FUTURE.

### BR-011

**Judul:** Pemisahan tanggung jawab SPPBJ.

- **Domain:** SPPBJ responsibility.
- **Pernyataan:** Persiapan/drafting, pemeriksaan, penerbitan/penetapan dan penandatanganan SPPBJ adalah tanggung jawab yang harus dapat dibedakan sebelum dipetakan ke aktor/metode; satu aktor universal belum ditentukan.
- **Otoritas/status:** BLOCKED_BY_GAP; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §16. [PR-F02](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f02-sppbj-responsibility-needs-a-precise-activity-distinction): PROC-02 tabel 6 baris 16 kolom 6/step 14 PPK; PROC-10 tabel 6 baris 13 kolom 3/step 11 Pokja; PROC-12 tabel 1 baris 3 item l caveat.
- **Cakupan:** [CAP-05/08/09](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-07/11/12](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-003/004/013/017 OPEN; P3 memisahkan aktivitas resmi, P5 tanggung jawab/segregation. **Kualifikasi:** Perbedaan dapat berarti drafting versus issuance, belum terbukti kontradiksi legal; ejaan historis SPBJ/SPPBJ tetap memerlukan validasi, tidak memilih Pokja atau PPK diam-diam.

### BR-012

**Judul:** Hubungan pelaksanaan dan pelacakan pembayaran.

- **Domain:** Contract/SPK, Execution/Progress, Inspection, Handover/BAST, SPJ dan Payment Tracking.
- **Pernyataan:** Bukti kontrak, pelaksanaan, pemeriksaan, serah terima, kelengkapan SPJ dan pelacakan pembayaran harus dapat dihubungkan dengan konteks paket yang relevan. Payment Tracking adalah informasi status/bukti/tanggung jawab, tidak menyatakan PRANATA melakukan transfer dana atau menggantikan sistem keuangan.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION pada batas tracking; [OD-06/23](../00-governance/DECISION_LOG.md#od-06), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §17. [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families), PROC-30 `CX2:DS2`, `DT2:HN2`, `KO2:KV2`, mengamati kelompok bukti.
- **Cakupan:** [CAP-09/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-12/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-003/004/010/013/017/018/019 OPEN; P3 hubungan/tanggung jawab resmi. **Kualifikasi:** Tidak menetapkan urutan pembayaran, authorization treasury, perlakuan pajak atau seluruh konsep wajib setiap metode.

### BR-013

**Judul:** Klasifikasi dan draft downstream.

- **Domain:** Downstream Classification Decision dan Downstream Draft.
- **Pernyataan:** Data hasil pengadaan/serah terima yang memenuhi aturan kelak dapat digunakan ulang dalam draft yang dibedakan sebagai Asset, Persediaan atau KDP; operator penanggung jawab melengkapi dan memvalidasi konteks yang masih diperlukan. Definitif memerlukan terpenuhinya aturan pengakuan/klasifikasi/penerimaan yang berlaku.
- **Otoritas/status:** PROPOSED, hipotesis produk yang kualifikasinya tetap berlaku; [OD-03/23](../00-governance/DECISION_LOG.md#od-23), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §18. [PR-U05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#candidate-unresolved-questions-for-the-central-gap-register), PROC-02 tabel 6 steps 16–20, PROC-04 tabel 1 item k–l, PROC-30 `KO2:KV2`.
- **Cakupan:** [CAP-10/11/14/15](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-11/13/18](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-008/009/018 OPEN; P3 trigger/handoff resmi, P5 assignment, P6 consistency kelak. **Kualifikasi:** Relasi konseptual bukan transition chain; operator final belum dipilih, klasifikasi bukan dari filename/account label. Lihat INV-012.

### BR-014

**Judul:** Aset sekarang dan sejarah transaksi.

- **Domain:** Asset, Asset Transaction dan Asset History.
- **Pernyataan:** Representasi aset saat ini dan riwayat transaksi/peristiwa aset merupakan konsep berbeda yang harus tetap terkait; perubahan lifecycle bukan hanya penimpaan master statis.
- **Otoritas/status:** EVIDENCE_SUPPORTED untuk pola lifecycle/ledger; kewajiban traceability dari [AC-14/15](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional) dan [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §20. [Corrections/development/KDP findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#corrections-development-reclassification-and-kdp), ASSET-11 hal. 1–2, mengamati opening/perolehan/transfer/hibah/perubahan/penghapusan.
- **Cakupan:** [CAP-11/12/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-14/15/22.
- **Dependency/penyerahan:** GAP-008/011/013/018 OPEN; P3 transaksi yang berlaku, P4 representasi kelak, P6/P10 history/import. **Kualifikasi:** Daftar operasi historis bukan taxonomy transaksi resmi; tidak mengasumsikan semua atribut saat ini dapat dihitung hanya dari sumber lama.

### BR-015

**Judul:** Penempatan, kustodian dan observasi kondisi.

- **Domain:** Asset Placement/Location, Custodian, Verification, Condition Observation dan Maintenance.
- **Pernyataan:** Lokasi/penempatan dan kustodian aset harus dapat ditelusuri terhadap perubahan yang diterima; observasi kondisi/verifikasi dan pemeliharaan mempunyai bukti serta konteks sendiri, terpisah dari nilai pelaporan.
- **Otoritas/status:** PROPOSED untuk cara mengaitkan representasi terkini dengan penempatan diterima; kebutuhan produk [AC-14/15](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §19/21. [Source inventory](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#source-level-inventory), ASSET-08 representative header dan ASSET-11 hal. 1–2, hanya mendukung vocabulary, bukan aturan kondisi.
- **Cakupan:** [CAP-11/12](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-14/15.
- **Dependency/penyerahan:** GAP-008/011/014 OPEN; P3 penerimaan mutasi/verifikasi, P5 responsibility, P7 presentation. **Kualifikasi:** Tidak mengarang skala kondisi, kewajiban jadwal pemeliharaan, atau orang/unit kustodian default; INV-007 mengendalikan pemisahan kondisi.

### BR-016

**Judul:** Makna perubahan dan penghapusan aset.

- **Domain:** Correction, Development, Reclassification dan Disposal/Removal.
- **Pernyataan:** Koreksi memperbaiki informasi yang dipersoalkan, pengembangan merepresentasikan perubahan/pengembangan yang relevan, reklasifikasi mengubah klasifikasi yang berlaku, dan penghapusan/removal berkaitan dengan penghentian/keluarnya record dari posisi yang relevan; setiap konsep harus dibedakan dan perubahan material menjaga alasan, provenance, sebelum/sesudah, waktu/periode, actor/responsibility serta bukti.
- **Otoritas/status:** BLOCKED_BY_GAP untuk perlakuan resmi; semantik awal merupakan proposal. [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §24; [asset findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#corrections-development-reclassification-and-kdp), ASSET-11 hal. 1–2, ASSET-05 kategori akun koreksi, HTML 1395–1422.
- **Cakupan:** [CAP-12/15/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-15/18/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-008/009/010/013 OPEN; P3 tindakan/sumber resmi, P5 tanggung jawab, P6/P10 treatment. **Kualifikasi:** Tidak menetapkan debit/kredit, kapitalisasi, efek penyusutan, historical-period reopening atau rekomendasi disposal.

### BR-017

**Judul:** Batas policy penyusutan dan amortisasi.

- **Domain:** Depreciation/Amortization Policy dan Reporting Value.
- **Pernyataan:** Nilai penyusutan/amortisasi memerlukan policy yang berwenang dan berlaku, termasuk basis commencement, life/rate, rounding, koreksi/reversal dan cutoff yang relevan; belum ada formula atau daily proration yang dapat diperlakukan sebagai aturan resmi dari sumber yang tersedia.
- **Otoritas/status:** BLOCKED_BY_GAP; [OD-14/15](../00-governance/DECISION_LOG.md#od-14), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §22. [Legacy report/depreciation findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#legacy-report-period-and-depreciation-behavior), ASSET-12 1216–1229/1335–1386/2470–2529, adalah behavior kode, bukan policy.
- **Cakupan:** [CAP-13/17](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-16/20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-007/008/013 OPEN; validasi Asset/Finance pada P2 berotorisasi, P3 temporal semantics, P6 calculations, P8/P10 contoh sahih. **Kualifikasi:** Tidak mengadopsi monthly arithmetic, default tanggal kosong atau formula HTML; INV-007/013 tetap berlaku.

### BR-018

**Judul:** Batas policy kapitalisasi.

- **Domain:** Capitalization Policy dan Asset Classification.
- **Pernyataan:** Kapitalisasi memerlukan policy berotoritas dengan version/effective date, category scope, comparator, amount/currency basis dan applicability historis yang diketahui; threshold dan perlakuan intra/ekstra belum dapat difinalkan dari workbook atau flag legacy.
- **Otoritas/status:** BLOCKED_BY_GAP; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §23. [Capitalization findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#capitalization-reference-and-intraextra-classification), ASSET-10 `Akun!C4:D5`/`Kapitalisasi!B1:C11`, ASSET-12 1250–1254/1294–1295, hanya referensi historis/behavior.
- **Cakupan:** [CAP-10/13/15/16](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-13/16/18/19](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-008/009/011/013/018 OPEN; validasi Asset/Finance, P3 classification semantics, P5 responsibility, P10 historical handling. **Kualifikasi:** Tidak menyalin minimum nominal, equality/comparator, pajak/grouping basis, atau menganggap flag tak dikenal sebagai extra.

### BR-019

**Judul:** Ledger persediaan dan posisi stok.

- **Domain:** Inventory/Persediaan Item, Transaction Ledger dan Stock Position.
- **Pernyataan:** Persediaan memiliki item/lokasi dan ledger untuk saldo awal, penerimaan, pemakaian/pengeluaran serta penyesuaian fisik yang berlaku; posisi stok merupakan hasil semantik ledger yang diterima dan dapat direkonsiliasi.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION pada pemisahan ledger dan derivasi konseptual; [OD-23](../00-governance/DECISION_LOG.md#od-23), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §25. [Inventory findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#inventory-transaction-code-discrepancy), ASSET-01/02 transaction column, ASSET-04 `Neraca Psd`, ASSET-05 journal labels, menunjukkan konteks terpisah.
- **Cakupan:** [CAP-14/17](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-17/20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-005/006/010/011 OPEN; P3 transaksi diterima, P6 ledger/report semantics, P10 reconciliation. **Kualifikasi:** Tidak menetapkan tanda, valuation, unit konversi atau rumus saldo sebelum dictionary sahih; INV-009 mengendalikan pemisahan dari aset tetap.

### BR-020

**Judul:** Dictionary dan mapping kode yang belum disahkan.

- **Domain:** Transaction Code Dictionary Version dan Canonical Mapping.
- **Pernyataan:** Makna kode transaksi, sign dan mapping kanonik memerlukan dictionary/version/effective applicability yang berwenang; mapping P01/P02 belum ditentukan dan, bila kelak disahkan, harus mempertahankan hubungan reversibel terhadap nilai sumber.
- **Otoritas/status:** BLOCKED_BY_GAP; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §26/33. [Inventory discrepancy](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#inventory-transaction-code-discrepancy): ASSET-01 `Sheet2!K`/ASSET-02 kolom 11 memuat P01; ASSET-04 `Neraca Psd!E6/C44` dan ASSET-05 `Sheet1!F4` memakai P02.
- **Cakupan:** [CAP-14/16/17](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-17/19/20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-005/006/011/013 OPEN; validasi Inventory/Finance, P6/P10 mapping/reconciliation. **Kualifikasi:** Tidak memilih alias, typo atau transaksi berbeda; istilah opname teramati tidak membuktikan definisi resmi. INV-010 berlaku.

### BR-021

**Judul:** KDP dan hasil penyelesaian.

- **Domain:** KDP, Contract/Progress dan Completion Outcome.
- **Pernyataan:** KDP adalah capability berbeda untuk konteks pekerjaan/perolehan yang belum selesai ketika berlaku; bukti kontrak/progress, posisi KDP dan penyelesaian yang layak menjadi aset harus tetap dapat ditelusuri.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION pada pemisahan dan traceability; [OD-23](../00-governance/DECISION_LOG.md#od-23), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §27. [KDP findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#corrections-development-reclassification-and-kdp), ASSET-04 `KDP`, ASSET-06 `GD. KDP!A1:E1`, ASSET-11 hal. 1, mendukung vocabulary.
- **Cakupan:** [CAP-10/15](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-13/18](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-008/009/010/018 OPEN; P3 completion/classification responsibility, P6/P10 accounting/reconciliation. **Kualifikasi:** Tidak menetapkan progress-to-value, capitalization timing, jurnal atau universal contract-to-KDP sequence; INV-011 mengendalikan pekerjaan belum selesai.

### BR-022

**Judul:** Makna waktu dan keluarga laporan.

- **Domain:** Reporting Time, Report dan Report Family.
- **Pernyataan:** Laporan posisi/as-of menyatakan keadaan pada cutoff; laporan transaksi/mutasi menyatakan aktivitas dalam rentang; laporan periodik memakai bulan/triwulan/semester/tahun yang relevan; laporan rekonsiliasi menyatakan perbandingan/selisih/pengecualian. Konteks unit, jenis, sumber, policy version, periode/cutoff dan pengecualian harus dapat dijelaskan sesuai semantik laporan.
- **Otoritas/status:** PROPOSED untuk klasifikasi temporal domain, berdasarkan [OD-15](../00-governance/DECISION_LOG.md#od-15), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §28/29; [ASSET-03/04/12](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#source-level-inventory) header laporan/selector 516–521 hanya mengamati keluarga/report behavior.
- **Cakupan:** [CAP-13/17](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-16/20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-007/008/009/010/013 OPEN; P3 semantics per report, P6 generation, P8/P10 acceptance. **Kualifikasi:** Tidak semua laporan menerima semua mode tanggal. BMU berasal dari kebutuhan Owner/P1; format legacy berjudul BMU belum terbukti. Tidak menetapkan formal accounting equality atau rumus pengurangan/koreksi.

### BR-023

**Judul:** Kasus rekonsiliasi dan bukti penerimaan.

- **Domain:** Reconciliation Case, Scope, Difference/Exception dan Acceptance Evidence.
- **Pernyataan:** Kasus rekonsiliasi mengidentifikasi lingkup/konteks waktu, sumber yang dibandingkan, hubungan yang diharapkan menurut aturan sahih, selisih/pengecualian, tanggung jawab/pihak tunggu, bukti dan penjelasan hasil; penerimaan/signoff harus tetap dibedakan dari sekadar tidak ditemukannya selisih.
- **Otoritas/status:** PROPOSED untuk model kasus; kebutuhan produk [AC-20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §30. [Historical reconciliation findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#historical-import-and-reconciliation-constraints), ASSET-06/07/08/09, mengamati bahan tetapi bukan ownership/signoff.
- **Cakupan:** [CAP-17/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-20/22.
- **Dependency/penyerahan:** GAP-005–010/012/013/018 OPEN sesuai sumber; P3 official responsibility/period finality, P5 akses, P6 comparison, P10 acceptance. **Kualifikasi:** Belum ada Finance signoff owner, cadence, tolerance atau equality resmi; INV-014 membatasi klaim resolved.

### BR-024

**Judul:** Penerimaan historis dan keputusan validasi.

- **Domain:** Import Source, Import Candidate, Validation Issue dan Cleansing Decision.
- **Pernyataan:** Intake historis harus membedakan sumber asal, candidate, masalah validasi/ambiguity/duplicate, keputusan koreksi/penolakan dan record yang kelak diterima; transformasi serta hasil harus tetap dapat ditelusuri dan direkonsiliasi sebelum dianggap final.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION pada ekspektasi keselamatan intake; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §31/36. [Import findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#historical-import-and-reconciliation-constraints), ASSET-01/02 duplicate location headers, ASSET-04 Intra/Intra New, ASSET-08 variant sheets; [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families), PROC-30/31 header correspondence tidak membuktikan kesetaraan nilai.
- **Cakupan:** [CAP-16/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-19/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-011/012/013/019 OPEN; P4/P6 validation contract, P8/P10 accepted examples/migration, P9/P10 cutover. **Kualifikasi:** Konsep bukan ordered import workflow/tabel/script; source precedence dan write ownership belum disahkan. INV-015/016 berlaku.

### BR-025

**Judul:** Versi policy dan applicability efektif.

- **Domain:** Versioned Policy dan Effective Applicability.
- **Pernyataan:** Policy yang berubah menurut waktu harus mempunyai makna versi, otoritas dan applicability pada konteks yang relevan; record historis harus dapat dijelaskan terhadap policy/version yang berlaku ketika diperlukan, termasuk perubahan/reversal yang disahkan.
- **Otoritas/status:** APPROVED_OWNER_DIRECTION pada prinsip konseptual; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §33. [OD-14/15](../00-governance/DECISION_LOG.md#od-14); [source findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#capitalization-reference-and-intraextra-classification) menunjukkan absence effective authority, bukan tanggal pengganti.
- **Cakupan:** [CAP-05/08/13/14/15/16/17/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-07/11/16–20/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-003/005–009/011/013/017/019 OPEN; P3 domain applicability, P6/P10 historical calculation/mapping. **Kualifikasi:** Kandidat mencakup dictionary, accounting/depreciation, kapitalisasi, variant, metode dan klasifikasi; tidak mengarang effective date, retroactivity atau prioritas policy yang bertentangan. INV-018 berlaku.

### BR-026

**Judul:** Disposition dan bukti sejarah material.

- **Domain:** Revision/Correction History, Audit Evidence dan Lifecycle Disposition.
- **Pernyataan:** Koreksi, pembatalan, supersession, pengarsipan, deletion, anonymization dan restore mempunyai makna berbeda; tindakan material memerlukan jejak actor/responsibility, waktu, organization/context, asal, alasan, bukti dan sebelum/sesudah bila berlaku. Riwayat keuangan/operasional harus tetap dapat dijelaskan saat representasi record berubah.
- **Otoritas/status:** PROPOSED untuk disposition/kelengkapan semantik; kebutuhan history/audit dari [CAP-19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §37–39 dan [AICWDF §13](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#13-p2--domain-model--business-rules). [ASSET-07](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#source-level-inventory) header audit-support bukan audit ruling.
- **Cakupan:** CAP-19; [AC-04/15/19/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-008/010/011/012/014 OPEN; [DOMAIN_LIFECYCLES](DOMAIN_LIFECYCLES.md) memuat disposition candidate per konsep; P5/P9/P10 retention/security, P4 storage kelak. **Kualifikasi:** Tidak memberikan hak hard delete/anonymize/restore, durasi retensi, soft-delete implementation atau mekanisme audit log. INV-017 membatasi hilangnya jejak.

### BR-027

**Judul:** Referensi eksternal dan integrasi masa depan.

- **Domain:** External Reference dan Future Integration.
- **Pernyataan:** Referensi/bukti dari kanal institusi dapat mempunyai konteks domain yang relevan tanpa membuat integrasi langsung atau akses institusi sebagai dependency V1; SSO/LDAP/identity UNY, SiRUP/SPSE/LPSE, Finance/HR, email institusi, digital signature dan storage eksternal tetap peluang masa depan.
- **Otoritas/status:** FUTURE untuk integrasi; batas kemandirian V1 merupakan [OD-11–13](../00-governance/DECISION_LOG.md#od-12) dan [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §12/15. [V1_SCOPE FUT-01–03](../01-product/V1_SCOPE.md#future--memerlukan-keputusanotorisasi-baru) memiliki lingkupnya.
- **Cakupan:** [CAP-01–20](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-26/27](../01-product/ACCEPTANCE_CRITERIA.md#pengalaman-dan-batas-produk).
- **Dependency/penyerahan:** GAP-002/013/015/017 OPEN pada kewajiban terkait; perubahan scope melalui Owner, P3/P5/P9 kelak bila diotorisasi. **Kualifikasi:** Prosedur wajib pada kanal resmi tetap harus dipenuhi; tidak membuat adapter/API/infrastruktur atau menganggap PRANATA berwenang menerbitkan aksi eksternal.

### BR-028

**Judul:** Batas V1 peminjaman fasilitas.

- **Domain:** Facility Borrowing scope boundary.
- **Pernyataan:** Peminjaman fasilitas/booking/pengembalian tidak ditambahkan ke V1 oleh P2; sumber borrowing dipertahankan sebagai referensi untuk keputusan lingkup masa depan.
- **Otoritas/status:** OUT_OF_SCOPE untuk V1; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §40, [V1_SCOPE FUT-04](../01-product/V1_SCOPE.md#future--memerlukan-keputusanotorisasi-baru) dengan persetujuan P1 yang mempertahankan kualifikasi. [PR-F06](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f06-borrowing-sources-have-different-procedural-coverage): PROC-28 hal. 1–3/PROC-29 hal. 3–4 berbeda cakupan rantai disposisi.
- **Cakupan:** [CAP-02/20](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas) hanya guardrail konteks; [AC-26](../01-product/ACCEPTANCE_CRITERIA.md#pengalaman-dan-batas-produk). Tidak ada CAP borrowing.
- **Dependency/penyerahan:** GAP-020 tetap OPEN; Owner memutuskan scope terpisah sebelum P3/P5 terkait. **Kualifikasi:** Pengecualian V1 bukan resolusi gap prosedur, precedence dua sumber atau pemberian izin borrowing.

### BR-029

**Judul:** Identitas konseptual dan ketidakpastian uniqueness.

- **Domain:** Conceptual Identity dan Uniqueness.
- **Pernyataan:** Identitas unit organisasi, perusahaan, paket, kontrak, aset, item persediaan, KDP dan source/import harus cukup jelas untuk menghubungkan record dan membahas duplikasi; kemiripan label/nama atau nomor pada template saja tidak membuktikan identitas sama atau berbeda.
- **Otoritas/status:** PROPOSED untuk kebutuhan identity lintas konsep; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §36, [AC-19](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional). [Historical import findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#historical-import-and-reconciliation-constraints), ASSET-01/02/04/08, menunjukkan uncertainty precedence/field, bukan duplicate verdict.
- **Cakupan:** [CAP-02/05/07/11/14/15/16/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-03/07/09/14/17/18/19/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan:** GAP-011/013/014/017 OPEN sesuai konsep; P3 business identity/number validation, P4/P6 representation/duplicate contracts kelak. **Kualifikasi:** Tidak menetapkan primary/foreign key, unique constraint, panjang kode, format/sequence nomor atau penggabungan perusahaan berdasarkan nama.

## Katalog invariant

### INV-001

**Judul:** Workspace tidak memberikan izin.

- **Domain/pernyataan:** Workspace Context — pemilihan workspace tidak memberikan izin terhadap tindakan atau data.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [OD-20](../00-governance/DECISION_LOG.md#od-20), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §9; terkait [BR-002](#br-002).
- **Cakupan:** [CAP-02/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-02/04/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan/kualifikasi:** GAP-014 OPEN; P5 enforcement. Tidak mendefinisikan role atau permission matrix; shared data tidak sama dengan akses bebas.

### INV-002

**Judul:** Jabatan tidak otomatis menjadi role global.

- **Domain/pernyataan:** Business Responsibility — jabatan formal, undangan kegiatan atau penugasan proses tidak otomatis menjadi role global atau pemberian kewenangan aplikasi/signature.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [OD-09](../00-governance/DECISION_LOG.md#od-09), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §9/13; [PR-F05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f05-historical-procurement-outputs-need-method-specific-validation), PROC-23–27 hal. 1–2; [BR-002/006](#br-002).
- **Cakupan:** [CAP-02/05/06](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-02/07/08](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan/kualifikasi:** GAP-003/004/014 OPEN; P3 validated assignment, P5 permission. Daftar compact role tetap candidate; domain responsibility tetap dapat mempunyai makna resmi setelah validasi.

### INV-003

**Judul:** Reuse perusahaan dan snapshot paket tetap berbeda.

- **Domain/pernyataan:** Provider Reuse — informasi company yang dapat dipakai ulang tidak diduplikasi per paket tanpa kebutuhan versi/snapshot atau nilai khusus paket yang dapat dijelaskan; snapshot historis tidak menggantikan profil company terkini secara diam-diam.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §14, [OD-23](../00-governance/DECISION_LOG.md#od-23), [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families) PROC-30 `AU2:BX2`; [BR-007/008](#br-007).
- **Cakupan:** [CAP-07/08](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-09/11](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan/kualifikasi:** GAP-014/017 OPEN; P3 validity/snapshot need, P5 account/company scope. Tidak melarang pembaruan yang memang wajib atau menentukan storage.

### INV-004

**Judul:** Informasi penyedia lain tetap terlindungi.

- **Domain/pernyataan:** Provider Information — berada dalam paket yang sama tidak memberikan akses ke informasi terlindungi perusahaan, penawaran atau evaluasi penyedia lain.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [AC-10](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [OD-20](../00-governance/DECISION_LOG.md#od-20), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §14; [BR-008](#br-008).
- **Cakupan:** [CAP-07/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-10/22.
- **Dependency/penyerahan/kualifikasi:** GAP-003/014/017 OPEN; P5 tepatnya data/tahap/pembaca yang diizinkan. Tidak mengarang kebijakan keterbukaan atau melarang informasi yang sah untuk diumumkan setelah validasi.

### INV-005

**Judul:** Dokumen tidak membuat kebenaran yang berkonflik diam-diam.

- **Domain/pernyataan:** Document/Evidence — dokumen generated atau uploaded tidak boleh diam-diam menciptakan domain truth yang bertentangan; perbedaan terhadap sumber/record memerlukan jejak dan keputusan yang berwenang.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [OD-08](../00-governance/DECISION_LOG.md#od-08), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §15; [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families) `Input`/`Mail Merge`; [BR-009](#br-009).
- **Cakupan:** [CAP-08/10/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-11/13/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan/kualifikasi:** GAP-013/017/019 OPEN; P3 conflict handling, P5 authority. Dokumen final/signed tidak selalu subordinate pada input; konflik tidak diselesaikan dengan memilih salinan secara sepihak.

### INV-006

**Judul:** Asal dan versi reuse tetap dapat ditelusuri.

- **Domain/pernyataan:** Reuse/Revision — nilai bersama yang dipakai ulang harus mempunyai asal, konteks dan versi/revisi yang dapat ditelusuri; perubahan sumber tidak boleh meninggalkan keluaran historis dengan konteks yang ambigu.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [OD-03/08/23](../00-governance/DECISION_LOG.md#od-23), [AC-11](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §15/33; [BR-009/025](#br-009).
- **Cakupan:** [CAP-07/08/10/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-09/11/13/22.
- **Dependency/penyerahan/kualifikasi:** GAP-017/018/019 OPEN; P3 reuse applicability/revisions, P4/P6 consistency kelak. Tidak mengharuskan semua perubahan langsung mengubah dokumen final atau memilih teknis snapshot.

### INV-007

**Judul:** Kondisi fisik independen dari nilai akuntansi.

- **Domain/pernyataan:** Asset Condition — usia penyusutan, pemakaian masa manfaat, nilai buku atau nilai buku nol bukan bukti kondisi fisik. Nilai buku nol tidak berarti rusak, umur akuntansi habis tidak berarti tidak dapat dipakai, perhitungan penyusutan bukan rekomendasi penghapusan, dan rekomendasi terinferensi legacy bukan kondisi resmi. Kondisi harus berasal dari observasi/pemeriksaan atau record kondisi yang sesuai.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §21, [AC-14/15](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [PRODUCT_OVERVIEW prinsip 4](../01-product/PRODUCT_OVERVIEW.md#prinsip-produk-yang-harus-dijaga); [BR-015/017](#br-015).
- **Cakupan:** [CAP-11/12/13](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-14/15/16.
- **Dependency/penyerahan/kualifikasi:** GAP-007/008/009/014 OPEN untuk policy, disposal dan responsibility; P3 condition/disposal semantics, P5 authority. Kondisi tidak diketahui dinyatakan tidak diketahui; tidak mengarang condition scale atau disposal rule.

### INV-008

**Judul:** Representasi aset tidak menghilangkan sejarah.

- **Domain/pernyataan:** Asset History — representasi aset sekarang tidak boleh menghilangkan jejak transaksi/peristiwa, sumber perolehan, mutasi dan perubahan yang mendasarinya; perubahan bukan pengganti seluruh riwayat.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION pada traceability; [AC-14/15](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §20/24, [asset findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#corrections-development-reclassification-and-kdp) ASSET-11 hal. 1–2; [BR-014/016](#br-014).
- **Cakupan:** [CAP-11/12/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-14/15/22.
- **Dependency/penyerahan/kualifikasi:** GAP-008/011/018 OPEN; P3 accepted changes, P4/P6 representation, P10 historical trace. Tidak membuktikan completeness sumber lama atau menetapkan taxonomy/accounting.

### INV-009

**Judul:** Persediaan terpisah dan stok mengikuti ledger diterima.

- **Domain/pernyataan:** Persediaan — ledger persediaan berbeda dari register aset tetap; posisi stok tidak boleh menjadi kebenaran manual terpisah yang mengabaikan ledger yang diterima, koreksi/penyesuaian dan rekonsiliasinya.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [OD-23](../00-governance/DECISION_LOG.md#od-23), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §25/32, [inventory findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#inventory-transaction-code-discrepancy) ASSET-01/02/04/05; [BR-019](#br-019).
- **Cakupan:** [CAP-14/17](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-17/20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan/kualifikasi:** GAP-005/006/010/011 OPEN; P3 acceptance, P6 ledger semantics, P10 reconciliation. Tidak menetapkan rumus balance atau melarang accepted opening balance/adjustment yang berwenang.

### INV-010

**Judul:** Kode raw P01/P02 dipertahankan.

- **Domain/pernyataan:** Inventory Raw Code — kode historis P01/P02 harus tetap dapat ditelusuri; tidak ada normalisasi diam-diam P01 menjadi P02 atau P02 menjadi P01. Canonical mapping actual tetap blocked sampai dictionary dan discrepancy divalidasi.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION untuk preservasi; actual mapping BLOCKED_BY_GAP. [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §26, [inventory discrepancy](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#inventory-transaction-code-discrepancy): `Sheet2!K`/CSV 11 versus `Neraca Psd!E6/C44`/`Sheet1!F4`; [BR-020](#br-020).
- **Cakupan:** [CAP-14/16/17](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-17/19/20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan/kualifikasi:** GAP-005/006 OPEN; P6/P10 setelah Inventory/Finance mengesahkan mapping reversibel. Tidak memilih kode mana yang benar atau memaknai keduanya sebagai pembelian.

### INV-011

**Judul:** KDP belum selesai tidak menjadi aset prematur.

- **Domain/pernyataan:** KDP — pekerjaan belum selesai yang semestinya tetap KDP tidak boleh diklasifikasikan prematur sebagai aset definitif; hubungan KDP dengan penyelesaian/perolehan tetap dapat ditelusuri.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §27, [AC-18](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [KDP findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#corrections-development-reclassification-and-kdp) ASSET-04/06/11; [BR-021](#br-021).
- **Cakupan:** [CAP-10/15](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-13/18.
- **Dependency/penyerahan/kualifikasi:** GAP-008/009/018 OPEN; P3 applicable completion/recognition, P6/P10 accounting. Tidak menyatakan setiap unfinished package adalah KDP atau menetapkan capitalization trigger.

### INV-012

**Judul:** BAST sendiri tidak membuat record definitif.

- **Domain/pernyataan:** Downstream Definitiveness — keberadaan BAST sendiri tidak otomatis menciptakan Asset/Persediaan/KDP definitif; reuse awal tetap draft sampai klasifikasi, pelengkapan dan validasi/penerimaan yang berlaku benar-benar terpenuhi.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION untuk batas keselamatan hipotesis; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §18, [OD-23](../00-governance/DECISION_LOG.md#od-23), [AC-13](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [PR-U05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#candidate-unresolved-questions-for-the-central-gap-register) PROC-02 steps 16–20/PROC-30 `KO2:KV2`; [BR-013](#br-013).
- **Cakupan:** [CAP-10/11/14/15](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-13/18.
- **Dependency/penyerahan/kualifikasi:** GAP-008/009/018 OPEN; P3 trigger/owner, P5 authority, P6 consistency. Tidak menetapkan transition guards atau urutan tindakan; account label/filename tidak menggantikan classification decision.

### INV-013

**Judul:** Ketidakpastian accounting tidak menjadi angka resmi.

- **Domain/pernyataan:** Reporting/Accounting Uncertainty — cutoff/policy yang belum tervalidasi tidak boleh menghasilkan angka perkiraan yang tampak resmi; arbitrary as-of date tidak mengesahkan penyusutan harian, dan mode tanggal tidak berlaku universal untuk semua laporan.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [OD-14/15](../00-governance/DECISION_LOG.md#od-15), [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §22/28/29, [AC-16/20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional); [legacy behavior](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#legacy-report-period-and-depreciation-behavior) HTML 1256–1278/1335–1386; [BR-017/022](#br-017).
- **Cakupan:** [CAP-13/17](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-16/20.
- **Dependency/penyerahan/kualifikasi:** GAP-007/008/009/010/013 OPEN; P3 meanings, P6 formulas setelah authority, P8/P10 validation. Konsep today/custom range bukan formula akuntansi.

### INV-014

**Judul:** Resolved rekonsiliasi memerlukan bukti.

- **Domain/pernyataan:** Reconciliation Integrity — selisih/pengecualian tidak boleh dinyatakan resolved tanpa penjelasan dan bukti yang dapat ditelusuri; status penyelesaian tidak menyatakan signoff resmi yang belum diperoleh.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §30, [AC-20](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [historical findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#historical-import-and-reconciliation-constraints) ASSET-06–09 tidak menetapkan finality; [BR-023](#br-023).
- **Cakupan:** [CAP-17/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-20/22.
- **Dependency/penyerahan/kualifikasi:** GAP-010/012 OPEN dan GAP sumber terkait; P3 signoff/owner/period closure, P10 acceptance. Tidak mengarang tolerance, acceptance equality atau Finance authority.

### INV-015

**Judul:** Ambiguity historis tidak diterima diam-diam.

- **Domain/pernyataan:** Historical Intake Integrity — data historis ambigu/tidak valid tidak diam-diam diterima sebagai kebenaran; sumber tidak ditimpa diam-diam, dan nilai yang dikoreksi/ditolak tetap mempunyai jejak keputusan yang sesuai. Duplicate verdict memerlukan identity rule yang eksplisit.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §31/36, [AC-19](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [import findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#historical-import-and-reconciliation-constraints) ASSET-01/02/04/08; [BR-024/029](#br-024).
- **Cakupan:** [CAP-16/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-19/22.
- **Dependency/penyerahan/kualifikasi:** GAP-011/012/013/019 OPEN; P4/P6 validation, P8/P10 migration/cutover. Tidak memilih source winner atau menganggap header matching sebagai nilai equivalent.

### INV-016

**Judul:** Kredensial plaintext tidak diimpor.

- **Domain/pernyataan:** Credential-sensitive Historical Source — kredensial plaintext historis tidak boleh diimpor sebagai kredensial akun PRANATA atau direproduksi sebagai bukti domain.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §6/31, [OD-11](../00-governance/DECISION_LOG.md#od-11), [EVIDENCE_POLICY](../00-governance/EVIDENCE_POLICY.md), [ASSET-10](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#source-level-inventory) hanya menyatakan presence boolean pada workbook; [BR-024](#br-024).
- **Cakupan:** [CAP-01/16/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-01/19/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan/kualifikasi:** GAP-011/013/015 OPEN untuk kontrak yang tersisa; P5/P9 secure account/recovery, P10 intake. Larangan ini tidak menunggu gap; tidak mengungkap nilai, identitas atau memperlakukan workbook credential sebagai eligibility akun.

### INV-017

**Judul:** Record material tidak hilang tanpa jejak.

- **Domain/pernyataan:** Material History — record operasional/keuangan yang membawa sejarah tidak boleh hilang tanpa jejak ketika dikoreksi, dibatalkan, disupersede atau diarsipkan; kewajiban retensi/privasi yang sah harus dipertimbangkan sebelum disposal/anonymization.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION pada ekspektasi traceability; detail disposition PROPOSED/BLOCKED. [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §38/39, [AC-15/19/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional), [AICWDF §13](../00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md#13-p2--domain-model--business-rules); [BR-026](#br-026).
- **Cakupan:** [CAP-12/16/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); AC-15/19/22.
- **Dependency/penyerahan/kualifikasi:** GAP-008/010/011/014 OPEN; P5/P9/P10 retention/security/privacy. Bukan kewajiban menyimpan semua data selamanya atau izin hard delete; disposition per konsep tetap candidate.

### INV-018

**Judul:** Policy historis tetap dapat dijelaskan.

- **Domain/pernyataan:** Historical Policy Explainability — pemakaian policy/version baru tidak boleh diam-diam menghapus penjelasan policy/applicability yang diperlukan bagi record atau keluaran historis; recalculation/restatement yang berlaku memerlukan keputusan, alasan dan jejak versi.
- **Otoritas/status/sumber:** APPROVED_OWNER_DIRECTION pada prinsip effective policy; [AUTH-006](../00-governance/APPROVAL_RECORDS.md#auth-006) §33, [OD-14/15](../00-governance/DECISION_LOG.md#od-14), [capitalization findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#capitalization-reference-and-intraextra-classification) absence issuer/effective date; [BR-025](#br-025).
- **Cakupan:** [CAP-08/13/14/15/16/17/19](../01-product/V1_SCOPE.md#must-ship--hasil-minimum-kapabilitas); [AC-11/16–20/22](../01-product/ACCEPTANCE_CRITERIA.md#kriteria-fungsional).
- **Dependency/penyerahan/kualifikasi:** GAP-005–009/011/013/017/019 OPEN; P3 applicable version, P6/P10 historical effect. Tidak mengarang actual effective date, policy precedence atau kewajiban retrospective recalculation.

## Konflik, batas validasi dan keputusan yang dibutuhkan

| Konflik/ketidakpastian | Sumber dan klasifikasi | Aturan terkait / dependency OPEN | Aman dalam P2; yang tetap blocked |
|---|---|---|---|
| P01 versus P02 terkait opname | ASSET-01 `Sheet2!K`/ASSET-02 kolom 11 versus ASSET-04 `Neraca Psd!E6/C44`/ASSET-05 `Sheet1!F4`; OBSERVED_CURRENT_PROCESS, [discrepancy](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#inventory-transaction-code-discrepancy) | BR-019/020; INV-009/010; GAP-005/006 | Raw provenance dan versioned mapping concept; actual meaning/sign/mapping belum dipilih. |
| Persiapan versus penerbitan/signature SPPBJ | PROC-02 tabel 6 step 14 PPK versus PROC-10 step 11 Pokja, PROC-12 item l caveat; OBSERVED_CURRENT_PROCESS, [PR-F02](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f02-sppbj-responsibility-needs-a-precise-activity-distinction) | BR-002/011; INV-002; GAP-003/004/013/017 | Tanggung jawab dapat dibedakan; final actor/delegation per metode belum ditentukan. |
| Heading tender versus isi pengadaan langsung | PROC-21 hal. 1; OBSERVED_CURRENT_PROCESS, [PR-F05](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f05-historical-procurement-outputs-need-method-specific-validation) | BR-004/010; GAP-003/013/017 | Applicability concept; tidak menyatakan metode interchangeable atau memilih template resmi. |
| Behavior HTML versus policy yang dibutuhkan | ASSET-12 arithmetic/cutoff/default date/Y/T, LEGACY_IMPLEMENTATION_BEHAVIOR; authority policy belum tersedia, [legacy findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#legacy-report-period-and-depreciation-behavior) | BR-017/018/022; INV-007/013/018; GAP-007/008/009/013 | Report provenance/temporal concepts; formula, default dates dan flag handling tetap belum resmi. Ini kekosongan authority, bukan klaim formal policy tertentu berkontradiksi. |
| Referensi minimum kapitalisasi tanpa instrument/effective date | ASSET-10 `Akun!C4:D5`/`Kapitalisasi!B1:C11`; OBSERVED_CURRENT_PROCESS, [capitalization findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#capitalization-reference-and-intraextra-classification) | BR-018/025; INV-018; GAP-009/013 | Policy/version/applicability concept; minimum nominal/comparator dan historical treatment tetap blocked. |
| Versi historis dan formula/cache bukan source winner | ASSET-04 Intra/Intra New, ASSET-08 variant sheets; PROC-30/31 headers serta `SPJ PL!C1267`; OBSERVED_CURRENT_PROCESS, [import findings](../00-governance/evidence/ASSET_SOURCE_REVIEW.md#historical-import-and-reconciliation-constraints), [PR-F04](../00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md#pr-f04-shared-workbook-inputs-feed-many-document-families) | BR-009/010/024/029; INV-005/015; GAP-011/012/013/019 | Candidate/provenance/validation semantics; trust, precedence, canonical field/value/formula dan write ownership belum dipilih. |

Tidak ada konflik diselesaikan dengan memilih sumber secara diam-diam. Pertanyaan terkelompok, evidence yang tersedia, dependent behavior yang blocked dan safe default ada pada [DOMAIN_DECISION_REQUESTS](DOMAIN_DECISION_REQUESTS.md). Safe default adalah menahan behavior dependent yang resmi/irreversible sampai authority sahih tersedia; bukan formula, actor, threshold atau mapping pengganti. Tidak ada GAP baru/resolusi yang diciptakan oleh katalog ini.

## Handoff validasi

P2 review memeriksa konsistensi istilah/relasi, provenance, status rule dan invariant di atas terhadap semua OD/CAP/AC/GAP. Katalog ini tidak mengubah kata-kata OD-01–OD-23, CAP-01–CAP-20 atau AC-01–AC-27. Rule formal blocked memerlukan source custodian/domain specialist dan keputusan yang sesuai sebelum dipakai dalam fase berikutnya; hasil review P2 tidak mengubahnya menjadi official rule.

P3 kelak menentukan urutan/prosedur/status transitions dan tanggung jawab yang sudah tervalidasi; P4 representasi aplikasi/data; P5 izin/segregation/retention/security; P6 calculation/consistency/import/performance contracts; P8–P10 contoh sahih, operating/migration/cutover/acceptance. Tidak ada schema, workflow machine, permission matrix, rumus/threshold resmi, implementation, Task atau planning freeze pada dokumen ini. P2 DONE — APPROVED under APPR-003, seluruh status/authority/dependency aturan tetap berlaku. Setelah checkpoint terverifikasi, Owner separately authorizes P3 — Workflows, Routes & Interactions; P3 tetap NOT AUTHORIZED.
