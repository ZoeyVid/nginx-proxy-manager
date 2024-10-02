<?php
function totp($secret): string
{
    function base32decode($base32string): string
    {
        $base32string = preg_replace("/[^A-Z2-7]/", "", strtoupper($base32string));

        $decoded = "";
        $bitBuffer = 0;
        $bitBufferLength = 0;

        foreach (str_split($base32string) as $char) {
            $pentet = strpos("ABCDEFGHIJKLMNOPQRSTUVWXYZ234567", $char);
            $bitBuffer = ($bitBuffer << 5) + $pentet;
            $bitBufferLength += 5;

            if ($bitBufferLength >= 8) {
                $decoded .= chr(($bitBuffer >> $bitBufferLength - 8) & 0xff);
                $bitBufferLength -= 8;
            }
        }

        return $decoded;
    }

    function truncate($hmac_result): string
    {
        $offset = ord($hmac_result[strlen($hmac_result) - 1]) & 0xf;
        return substr(((ord($hmac_result[$offset]) & 0x7f) << 24) | ((ord($hmac_result[$offset + 1]) & 0xff) << 16) | ((ord($hmac_result[$offset + 2]) & 0xff) << 8) | (ord($hmac_result[$offset + 3]) & 0xff), -6);
    }

    $hash = hash_hmac("sha1", pack("J", floor(time() / 30)), base32decode($secret), true);
    return truncate($hash);
}
