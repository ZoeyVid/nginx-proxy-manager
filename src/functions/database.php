<?php
function db(): SQLite3
{
    require_once __DIR__ . "/../config.php";
    $db = new SQLite3(data_path() . "/npmplus.sqlite");
    $db->exec("CREATE TABLE IF NOT EXISTS config (key CHAR(255) UNIQUE NOT NULL, value CHAR(255) NOT NULL)");
    $db->exec("CREATE TABLE IF NOT EXISTS auth (email CHAR(255) UNIQUE NOT NULL, pswd CHAR(255) UNIQUE NOT NULL, totp CHAR(255) UNIQUE)");
    $db->exec("VACUUM");
    return $db;
}
