<?php
declare(strict_types=1);

// Change this PIN before deploying!
// Generate hash: echo password_hash('1234', PASSWORD_BCRYPT);
define('ACCESS_PIN_HASH', '$2y$12$vtMwLTALHZgq9cM59wyGWuBkQWdgJrPuAs8LZyja3SxSC0wbdqrK6');

// Session token — rotate this to invalidate all sessions
define('TOKEN_SECRET', 'kxSjO6n5ID96w6dqscxEbpea6QZeYaza40ybGkvfEk8lv925NTWdgx4zIk8DEN0fGi5YEgm7psC3qlZDxp+kJw==');

// Data directory (absolute path)
define('DATA_DIR',     __DIR__ . '/../data/');
define('READINGS_DIR', __DIR__ . '/../data/readings/');

// How long a session token is valid (seconds)
define('TOKEN_TTL', 86400 * 7); // 7 days
