<?php
declare(strict_types=1);

// PIN hash — can be overridden at runtime by data/pin_hash.txt (written by change_pin.php)
// To set manually: php -r "echo password_hash('YOUR_PIN', PASSWORD_BCRYPT);"
$_pinHashFile = __DIR__ . '/../data/pin_hash.txt';
$_runtimeHash = file_exists($_pinHashFile) ? trim(file_get_contents($_pinHashFile)) : null;
define('ACCESS_PIN_HASH', $_runtimeHash ?: '$2y$12$vtMwLTALHZgq9cM59wyGWuBkQWdgJrPuAs8LZyja3SxSC0wbdqrK6');
define('PIN_HASH_FILE',   $_pinHashFile);
unset($_pinHashFile, $_runtimeHash);

// Session token — rotate this to invalidate all sessions
define('TOKEN_SECRET', 'kxSjO6n5ID96w6dqscxEbpea6QZeYaza40ybGkvfEk8lv925NTWdgx4zIk8DEN0fGi5YEgm7psC3qlZDxp+kJw==');

// Data directory (absolute path)
define('DATA_DIR',     __DIR__ . '/../data/');
define('READINGS_DIR', __DIR__ . '/../data/readings/');

// How long a session token is valid (seconds)
define('TOKEN_TTL', 86400 * 7); // 7 days
