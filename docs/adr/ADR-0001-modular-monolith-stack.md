# ADR-0001 — Modular monolith Laravel dengan Inertia, React, TypeScript dan PostgreSQL

Status: ACCEPTED (APPR-006) | Date: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent | Approval: APPR-006, 2026-11-01

## Konteks dan bukti

OD-01–OD-03 menetapkan satu aplikasi terintegrasi dengan ekosistem data bersama; TOOLCHAIN mencatat baseline Laravel, Inertia, React, TypeScript, shadcn/ui, Tailwind CSS, PostgreSQL, Nginx dan modular monolith. Skala target sekitar 200 pengguna dan 1.000.000 aset (OD-16/22/32). Tim eksekusi adalah satu agen eksekusi dengan review berbasis risiko (GOV-12), sehingga kesederhanaan operasional dan konsistensi pola sangat penting.

## Alternatif

| Alternatif | Penilaian |
|---|---|
| Modular monolith Laravel + Inertia/React (dipilih) | Satu deployable, transaksi lintas modul mudah, pola Laravel standar, sesuai baseline Owner |
| Microservices | Kompleksitas operasi/jaringan/konsistensi tidak sebanding dengan skala; menambah biaya |
| SPA terpisah + REST API | Duplikasi otorisasi/validasi, perlu API publik dan manajemen token tanpa kebutuhan nyata |
| Livewire/Blade | Menyimpang dari baseline React/shadcn yang dipilih Owner |

## Keputusan dan alasan

Satu aplikasi Laravel modular monolith dengan modul domain eksplisit ([ARCHITECTURE](../04-architecture/ARCHITECTURE.md)), antarmuka Inertia.js 2 + React + TypeScript + Tailwind CSS 4 + shadcn/ui, basis data PostgreSQL (major yang didukung, minimal 16), Nginx + PHP-FPM. Versi tepat ditentukan saat bootstrap dari rilis stabil terkini yang didukung dan dicatat di TOOLCHAIN. Basis kode dimulai dari Laravel React starter kit resmi dengan registrasi publik dimatikan. Batas modul ditegakkan dengan arch tests.

## Risiko

- Starter kit/versi berubah sebelum eksekusi → TASK-002 memverifikasi dan menghentikan bila ada konflik.
- Monolith tumbuh tanpa disiplin batas → aturan dependensi + arch tests di CI.

## Biaya

Nol biaya lisensi; seluruh komponen open-source/first-party.

## Dampak kompatibilitas dan data

Basis kode baru; tidak ada data lama yang diubah.

## Migrasi dan rollback

Sebelum produksi, pembalikan keputusan berarti menulis ulang fondasi; setelah produksi, modul dapat diekstrak bila kelak perlu karena batasnya eksplisit.

## Yang terdampak

Semua Task P11; ARCHITECTURE, DATA_MODEL, P5–P9.

## Persetujuan

ADR ini PROPOSED dan tidak memberi otorisasi implementasi. Berlaku setelah Owner menyetujui kandidat P4 yang memuatnya; rujukan persetujuan aktual dicatat di [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md).
