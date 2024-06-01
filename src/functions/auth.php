<?php
function isAuthenticated(): bool
{
    if (!array_key_exists("AUTH_EMAIL", $_SESSION) || empty($_SESSION["AUTH_EMAIL"]) || !array_key_exists("AUTH_PW_HASH", $_SESSION) || empty($_SESSION["AUTH_PW_HASH"]) || !array_key_exists("LOGIN_TIME", $_SESSION) || empty($_SESSION["LOGIN_TIME"]) || time() - $_SESSION["LOGIN_TIME"] > 3600) {
        return false;
    } else {
        require_once __DIR__ . "/database.php";
        $db = db();
        $query = $db->prepare("SELECT * FROM auth WHERE email=:email");
        $query->bindValue(":email", $_SESSION["AUTH_EMAIL"]);
        $queryresult = $query->execute()->fetchArray();
        if (!is_array($queryresult) || $_SESSION["AUTH_PW_HASH"] !== hash("sha256", $queryresult["pswd"])) {
            return false;
        } else {
            if (!empty($queryresult["totp"])) {
                if (!array_key_exists("AUTH_TOTP_HASH", $_SESSION) || $_SESSION["AUTH_TOTP_HASH"] !== hash("sha256", $queryresult["totp"])) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }
}
