<?php
// Copy to config.local.php ON THE SERVER ONLY (never commit it) and fill in.
// Make a password hash on any computer with PHP:  php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
return [
    'base_url'            => 'https://dive.wheretospot.com',
    'data_dir'            => '/home/viorowzb/dive-data',        // outside public_html: the database lives here
    'admin_password_hash' => '',                                // paste the hash here
    'admin_email'         => '',                                // gets a copy of every inquiry and listing request
    'mail_from'           => 'no-reply@wheretospot.com',
];
