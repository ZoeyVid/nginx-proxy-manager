<?php
require_once __DIR__ . "/../functions/database.php";
$db = db();
if ($db->querySingle("SELECT COUNT(*) FROM auth") === 0) {
    session_unset();
    session_destroy();
    header('Location: /auth/setup', true, 307);
    exit;
}
if (!array_key_exists("AUTH", $_SESSION) || $_SESSION["AUTH"] !== true || !array_key_exists("LOGIN_TIME", $_SESSION) || (time() - $_SESSION["LOGIN_TIME"] > 3600)) {
    session_unset();
    session_destroy();
    header('Location: /auth/login', true, 307);
    exit;
}
