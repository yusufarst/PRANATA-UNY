<?php

return [
    'idempotency_missing' => 'This request carries no valid submission code. Reload the page, then send it again.',
    'idempotency_reused' => 'This submission code was already used for different input. Reload the page, then send it again.',
    'in_progress' => 'The same request is still being processed. Wait a moment, then check the result.',
    'busy' => 'Someone else is working on this record. Try again in a moment.',
    'conflict' => 'Someone else changed this record after you opened it. Review the latest data, then repeat your change.',
    'retry_exhausted' => 'The system is busy, so your request was not processed. Nothing was saved. Please try again.',
];
