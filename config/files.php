<?php

/*
 * Private files (SECURITY_MODEL section 6, TASK-016). The disk is private: nothing under it is served as a static URL.
 * Every extension names the MIME types its bytes may carry as the server detects them; a file whose bytes say otherwise is
 * refused. A purpose may narrow the extensions and the size.
 */
return [
    'disk' => env('FILES_DISK', 'local'),

    // 20 MB by default (SECURITY_MODEL section 6).
    'max_bytes' => 20 * 1024 * 1024,

    'extensions' => [
        'pdf' => ['application/pdf'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        'csv' => ['text/csv', 'text/plain', 'application/csv'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ],

    // Purpose => the extensions and size it allows; a purpose that is not listed allows every extension up to max_bytes.
    'purposes' => [
        'DECREE' => ['extensions' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_bytes' => 10 * 1024 * 1024],
    ],
];
