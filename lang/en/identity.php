<?php

return [
    'account_saved' => 'The user details have been saved.',
    'account_deactivated' => 'This account has been deactivated and can no longer be changed.',
    'account_deactivated_done' => 'The account has been deactivated. All of its sessions have ended.',
    'cannot_deactivate_self' => 'You cannot deactivate your own account.',
    'cannot_reset_self' => 'You cannot reset the password of your own account. Ask another administrator to do it.',
    'reset_already_pending' => 'A reset request is already waiting for a second administrator.',
    'reset_not_pending' => 'This reset request is no longer waiting for approval. Reload the page to see its current state.',
    'reset_needs_second_admin' => 'Another administrator must approve it, not the one who asked for the reset or the account owner.',
    'reset_awaits_approval' => 'The reset request is recorded. A second administrator must approve it before a temporary password is made.',
    'reset_revoked' => 'The reset request was cancelled. The password of the account did not change.',
    'session_not_found' => 'This session has already ended or is the one you are using. Reload the page to see the current list.',
    'session_revoked' => 'The session has ended. That device must sign in again.',
    'other_sessions_revoked' => 'All other sessions have ended. The session on this device keeps running.',
    'sessions_revoked' => '{0} This account has no active sessions left.|{1} 1 session has ended. The user must sign in again.|[2,*] :count sessions have ended. The user must sign in again.',
    'cannot_revoke_own_sessions_here' => 'End the sessions of your own account from the Active sessions page in Settings.',
    'username_taken' => 'This username is already used by another account.',
    'email_taken' => 'This email is already used by another account.',
];
