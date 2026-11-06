<?php

return [
    'uploaded' => 'The evidence is uploaded and stored.',
    'released' => 'The evidence link is released. The file and its history stay.',
    'owner_unknown' => 'The object this evidence belongs to was not found.',
    'purpose_not_allowed' => 'This upload purpose is not used for that object.',
    'link_not_active' => 'This evidence link is already released. Reload the page to see the latest state.',
    'link_sealed' => 'This evidence link is sealed and cannot be released or changed.',
    'integrity_failed' => 'This file cannot be sent: its content in storage is missing or does not match the record. The incident is recorded. Contact an administrator and quote the request code.',
    'validation' => [
        'invalid_upload' => 'The file did not upload completely. Try again.',
        'name_too_long' => 'The file name is too long (255 characters at most).',
        'extension' => 'This kind of file is not allowed. Accepted kinds: :allowed.',
        'double_extension' => 'The file name holds more than one extension. Rename the file so that only one extension ends it.',
        'empty' => 'An empty file cannot be uploaded.',
        'size' => 'The file is larger than the limit of :max MB.',
        'mime' => 'The content of the file does not match the extension .:extension.',
    ],
    'fields' => [
        'purpose' => 'purpose',
        'file' => 'file',
        'label' => 'label',
    ],
    'owners' => [
        'appointment' => ':position · :person',
        'assignment' => ':role · :person',
        'decree' => 'Decree :ref',
    ],
    'parents' => [
        'appointment' => 'Organization · :unit',
        'assignment' => 'Assignments',
    ],
];
