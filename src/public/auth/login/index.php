<?php
require_once __DIR__ . "/../../../functions/database.php";
$db = db();
if ($db->querySingle("SELECT COUNT(*) FROM auth") === 0) {
    session_unset();
    session_destroy();
    header('Location: /auth/setup', true, 307);
    exit;
}
if (array_key_exists("AUTH", $_SESSION) && $_SESSION["AUTH"] === true && array_key_exists("LOGIN_TIME", $_SESSION) && (time() - $_SESSION["LOGIN_TIME"] < 3600)) {
    header("Location: /", true, 307);
    exit;
} else { ?>

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
    <?php if (!array_key_exists("email", $_POST) || !array_key_exists("pswd", $_POST)) { ?>
    <h1>Login</h1>
    <form method="post">
        <label for="email">E-Mail: </label><input type="email" name="email" id="email" maxlength="255" required><br>
        <label for="pswd">Passwort: </label><input type="password" name="pswd" id="pswd" maxlength="255" required><br>
        <label for="totp">TOTP: </label><input type="text" name="totp" id="totp" maxlength="6"><br>
        <!--<div class="h-captcha" data-sitekey="<?php //echo $hcaptcha_key; ?>"></div>-->
        <input type="submit" value="Login" onClick="this.hidden=true;">
        <b></b>
    </form>
        <?php if (array_key_exists("msg", $_GET)) {
            $msg = match ($_GET["msg"]) {
                "adne" => "Account does not exist.",
                "wpw" => "Wrong password.",
                "mtotp" => "Missing TOTP.",
                "wtotp" => "Wrong TOTP.",
                default => "Please login.",
            };
        } else {
            $msg = "Please login.";
        }
        echo "<p><b>Note: " . $msg . "</b></p>";
    } else {
        require_once __DIR__ . "/../../../functions/email.php";
        $_SESSION["LOGIN_TIME"] = time();
        $pswd = $_POST["pswd"];
        $email = $_POST["email"];
        if (!empty($_POST["totp"])) {
            $totp = $_POST["totp"];
        }

        $query = $db->prepare("SELECT * FROM auth WHERE email=:email");
        $query->bindValue(":email", $email);
        $result = $query->execute()->fetchArray();

        if (is_array($result) && validateEmail($email)) {
            if (!password_verify($pswd, $result["pswd"])) {
                sendMail($email, "login", $_SERVER["REMOTE_ADDR"] . " failed to login into your account.");
                header("Location: /auth/login?msg=wpw", true, 307);
                exit;
            } else {
                if (empty($result["totp"])) {
                    sendMail($email, "login", $_SERVER["REMOTE_ADDR"] . " logged into your account");
                    $_SESSION["AUTH"] = true;
                    header("Location: /", true, 307);
                    exit;
                } else {
                    if (empty($totp)) {
                        sendMail($email, "login", $_SERVER["REMOTE_ADDR"] . " failed to login into your account.");
                        header("Location: /auth/login?msg=mtotp", true, 307);
                        exit;
                    } else {
                        require_once __DIR__ . "/../../../functions/totp.php";
                        if ($totp === totp($result["totp"])) {
                            sendMail($email, "login", $_SERVER["REMOTE_ADDR"] . " logged into your account");
                            $_SESSION["AUTH"] = true;
                            header("Location: /", true, 307);
                            exit;
                        } else {
                            sendMail($email, "login", $_SERVER["REMOTE_ADDR"] . " failed to login into your account.");
                            header("Location: /auth/login?msg=wtotp", true, 307);
                            exit;
                        }
                    }

                }
            }
        } else {
            header("Location: /auth/login?msg=adne", true, 307);
            exit;
        }

    } ?>
</div>
</body>
<?php } ?>
