<?php
require_once __DIR__ . "/../functions/database.php";
$db = db();
if ($db->querySingle("SELECT COUNT(*) FROM auth") === 0) {
    session_destroy();
    header("Location: /auth/setup", true, 307);
    exit();
}

require_once __DIR__ . "/../functions/auth.php";
if (!isAuthenticated()) {
    session_destroy();
    header("Location: /auth/login", true, 307);
    exit();
}
