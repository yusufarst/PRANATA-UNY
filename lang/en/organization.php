<?php

return [
    'request_saved' => 'The change request has been saved as a draft.',
    'request_submitted' => 'The request has been checked. Review the result of the checks before passing it on.',
    'request_returned' => 'The request has been returned to draft for correction.',
    'request_escalated' => 'The request has been passed on and now awaits the decision of the authorized official.',
    'request_decided' => 'The decision has been recorded.',
    'request_applied' => 'The change is now in force and recorded in the unit history.',
    'request_withdrawn' => 'The request has been withdrawn. The organization has not changed.',
    'transition_not_allowed' => 'This step is not possible in the current status of the request. Reload the page to see the latest state.',
    'not_yet_effective' => 'The effective date of the decision has not been reached yet. The change can be put into force from that date.',
    'blocking_issues' => 'Some issues still block the request. Correct the request or withdraw it.',
    'policy_unavailable' => 'An official decision cannot be recorded yet because the organization maintenance policy is not officially in force (:gap).',
    'effective_date_in_past' => 'The effective date cannot be earlier than the decision date.',
    'appointment_ends_before_start' => 'The end date of the appointment must be after its start date.',
];
