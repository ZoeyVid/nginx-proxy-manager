<?php
require_once __DIR__ . "/../../../functions/database.php";
$db = db();
if ($db->querySingle("SELECT COUNT(*) FROM auth") !== 0) {
    header("Location: /", true, 307);
    exit();
} else {
     ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>NPMplus - Setup</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-name" content="NPMplus">
    <meta name="author" content="ZoeyVid">
    <meta name="description" content="Login Page for NPMplus">
    <meta name="keywords" content="NPMplus, Setup">
    <link rel="stylesheet" href="/tailwind.css">
    <link rel="icon" type="image/webp" href="/favicon.webp">
</head>
<?php
}
