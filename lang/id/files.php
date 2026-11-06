<?php

return [
    'uploaded' => 'Bukti sudah diunggah dan tersimpan.',
    'released' => 'Tautan bukti sudah dilepas. Berkas dan riwayatnya tetap tersimpan.',
    'owner_unknown' => 'Objek pemilik bukti ini tidak ditemukan.',
    'purpose_not_allowed' => 'Tujuan unggahan ini tidak dipakai untuk objek tersebut.',
    'link_not_active' => 'Tautan bukti ini sudah dilepas. Muat ulang halaman untuk melihat keadaan terbaru.',
    'link_sealed' => 'Tautan bukti ini bersegel dan tidak dapat dilepas atau diubah.',
    'integrity_failed' => 'Berkas ini tidak dapat dikirim: isinya di penyimpanan hilang atau tidak cocok dengan catatan. Kejadian ini sudah dicatat. Hubungi administrator dengan menyebutkan kode permintaan.',
    'validation' => [
        'invalid_upload' => 'Berkas tidak terunggah dengan utuh. Coba unggah lagi.',
        'name_too_long' => 'Nama berkas terlalu panjang (maksimum 255 karakter).',
        'extension' => 'Jenis berkas tidak diizinkan. Jenis yang diterima: :allowed.',
        'double_extension' => 'Nama berkas memuat lebih dari satu ekstensi. Ganti nama berkas sehingga hanya ada satu ekstensi di akhir.',
        'empty' => 'Berkas kosong tidak dapat diunggah.',
        'size' => 'Ukuran berkas melebihi batas :max MB.',
        'mime' => 'Isi berkas tidak sesuai dengan ekstensi .:extension.',
    ],
    'fields' => [
        'purpose' => 'tujuan',
        'file' => 'berkas',
        'label' => 'keterangan',
    ],
    'owners' => [
        'appointment' => ':position · :person',
        'assignment' => ':role · :person',
        'decree' => 'SK :ref',
    ],
    'parents' => [
        'appointment' => 'Organisasi · :unit',
        'assignment' => 'Penugasan',
    ],
];
