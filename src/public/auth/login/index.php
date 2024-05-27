<?php
require_once __DIR__ . "/../../../functions/database.php";
$db = db();
if ($db->querySingle("SELECT COUNT(*) FROM auth") === 0) {
    session_destroy();
    header('Location: /auth/setup', true, 307);
    exit;
}

require_once __DIR__ . "/../../../functions/auth.php";
if (isAuthenticated()) {
    header("Location: /", true, 307);
    exit;
} else {
    session_unset();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>NPMplus - Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-name" content="NPMplus">
    <meta name="author" content="ZoeyVid">
    <meta name="description" content="Login Page for NPMplus">
    <meta name="keywords" content="NPMplus, login">
    <link rel="icon" type="image/webp" href="/favicon.webp">
    <!--<script src="https://js.hcaptcha.com/1/api.js?hl=en&render=onload&recaptchacompat=off" async defer></script>-->
</head>

<body>
<div style="text-align: center;">
    <?php function login($msg): void
    { ?>
    <h1>Login</h1>
    <form method="post">
        <label for="email">E-Mail: </label><input type="email" name="email" id="email" maxlength="255" required><br>
        <label for="pswd">Passwort: </label><input type="password" name="pswd" id="pswd" maxlength="255" required><br>
        <label for="totp">TOTP: </label><input type="text" name="totp" id="totp" maxlength="6"><br>
        <!--<div class="h-captcha" data-sitekey="<?php //echo $hcaptcha_key; ?>"></div>-->
        <input type="submit" value="Login" onClick="this.hidden=true;">
        <b></b>
    </form>
        <?php
        $msg = match ($msg) {
            "adne" => "Account does not exist.",
            "wpw" => "Wrong password.",
            "mtotp" => "Missing TOTP.",
            "wtotp" => "Wrong TOTP.",
            default => "Please login.",
        };
        echo "<p><strong>Note: " . $msg . "</strong></p>";
    }
        if (!array_key_exists("email", $_POST) || !array_key_exists("pswd", $_POST)) {
            login("none");
        } else {
            require_once __DIR__ . "/../../../functions/email.php";
            $_SESSION["LOGIN_TIME"] = time();
            $query = $db->prepare("SELECT * FROM auth WHERE email=:email");
            $query->bindValue(":email", $_POST["email"]);
            $queryresult = $query->execute()->fetchArray();

            if (is_array($queryresult) && validateEmail($_POST["email"])) {
                if (!password_verify($_POST["pswd"], $queryresult["pswd"])) {
                    sendMail($_POST["email"], "Failed Login", $_SERVER["REMOTE_ADDR"] . " failed to login into your account.");
                    login("wpw");
                } else {
                    if (empty($queryresult["totp"])) {
                        sendMail($_POST["email"], "New Login", $_SERVER["REMOTE_ADDR"] . " logged into your account");
                        $_SESSION["AUTH_PW_HASH"] = hash("sha256", $queryresult["pswd"]);
                        header("Location: /", true, 307);
                        exit;
                    } else {
                        if (empty($_POST["totp"])) {
                            sendMail($_POST["email"], "Failed Login", $_SERVER["REMOTE_ADDR"] . " failed to login into your account.");
                            login("mtotp");
                        } else {
                            require_once __DIR__ . "/../../../functions/totp.php";
                            if ($_POST["totp"] === totp($queryresult["totp"])) {
                                sendMail($_POST["email"], "New Login", $_SERVER["REMOTE_ADDR"] . " logged into your account");
                                $_SESSION["AUTH_EMAIL"] = $_POST["email"];
                                $_SESSION["AUTH_PW_HASH"] = hash("sha256", $queryresult["pswd"]);
                                $_SESSION["AUTH_TOTP_HASH"] = hash("sha256", $queryresult["totp"]);
                                header("Location: /", true, 307);
                                exit;
                            } else {
                                sendMail($_POST["email"], "Failed Login", $_SERVER["REMOTE_ADDR"] . " failed to login into your account.");
                                login("wtotp");
                            }
                        }

                    }
                }
            } else {
                login("adne");
            }
        } ?>
</div>
</body>
<?php } ?>
