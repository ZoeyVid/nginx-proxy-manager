<?php
function db() : SQLite3
{
    $config = require_once __DIR__ . "/../config.php";
    $db = new SQLite3($config["data_path"] . "/npmplus.sqlite");
    $db->exec("CREATE TABLE IF NOT EXISTS config (key CHAR(255) UNIQUE NOT NULL, value CHAR(255) NOT NULL)");
    $db->exec("INSERT OR IGNORE INTO config (key, value) VALUES ('mail_host', 'mx.zvcdn.de'), ('mail_address', 'reservierung@a24dmng.de'), ('mail_pswd', '-,25,xEcEpirETionfrat')");
    $db->exec("CREATE TABLE IF NOT EXISTS auth (email CHAR(255) UNIQUE NOT NULL, pswd CHAR(255) UNIQUE NOT NULL, totp CHAR(255) UNIQUE)");
    //$db->exec("INSERT OR IGNORE INTO auth (email, pswd, totp) VALUES ('', '', '')");
    $db->exec("VACUUM");
    return $db;
}
