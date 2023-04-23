#!/bin/sh

echo "
-------------------------------------
 _   _ ____  __  __
| \ | |  _ \|  \/  |
|  \| | |_) | |\/| |
| |\  |  __/| |  | |
|_| \_|_|   |_|  |_|
-------------------------------------
User:     $(whoami)
User ID:  $(id -u)
Group ID: $(id -g)
-------------------------------------
"

if ! nginx -t > /dev/null 2>&1; then
    nginx -T || sleep inf
    sleep inf || exit 1
fi

if [ "$PHP81" = "true" ]; then
    if ! PHP_INI_SCAN_DIR=/data/php/81/conf.d php-fpm81 -c /data/php/81 -y /data/php/81/php-fpm.conf -FORt > /dev/null 2>&1; then
        PHP_INI_SCAN_DIR=/data/php/81/conf.d php-fpm81 -c /data/php/81 -y /data/php/81/php-fpm.conf -FORt || sleep inf
        sleep inf || exit 1
    fi
fi

if [ "$PHP82" = "true" ]; then
    if ! PHP_INI_SCAN_DIR=/data/php/82/conf.d php-fpm82 -c /data/php/82 -y /data/php/82/php-fpm.conf -FORt > /dev/null 2>&1; then
        PHP_INI_SCAN_DIR=/data/php/82/conf.d php-fpm82 -c /data/php/82 -y /data/php/82/php-fpm.conf -FORt || sleep inf
        sleep inf || exit 1
    fi
fi

while (nginx -t > /dev/null 2>&1 && if [ "$PHP81" = true ]; then PHP_INI_SCAN_DIR=/data/php/81/conf.d php-fpm81 -c /data/php/81 -y /data/php/81/php-fpm.conf -FORt > /dev/null 2>&1; fi && if [ "$PHP82" = true ]; then PHP_INI_SCAN_DIR=/data/php/82/conf.d php-fpm82 -c /data/php/82 -y /data/php/82/php-fpm.conf -FORt > /dev/null 2>&1; fi); do
    nginx || exit 1 &
    if [ "$PHP81" = "true" ]; then PHP_INI_SCAN_DIR=/data/php/81/conf.d php-fpm81 -c /data/php/81 -y /data/php/81/php-fpm.conf -FOR || exit 1; fi &
    if [ "$PHP82" = "true" ]; then PHP_INI_SCAN_DIR=/data/php/82/conf.d php-fpm82 -c /data/php/82 -y /data/php/82/php-fpm.conf -FOR || exit 1; fi &
    index.js || exit 1 &
    wait
done

if ! nginx -t > /dev/null 2>&1; then
    nginx -T || sleep inf
fi

if [ "$PHP81" = "true" ]; then
    if ! PHP_INI_SCAN_DIR=/data/php/81/conf.d php-fpm81 -c /data/php/81 -y /data/php/81/php-fpm.conf -FORt > /dev/null 2>&1; then
        PHP_INI_SCAN_DIR=/data/php/81/conf.d php-fpm81 -c /data/php/81 -y /data/php/81/php-fpm.conf -FORt || sleep inf
    fi
fi

if [ "$PHP82" = "true" ]; then
    if ! PHP_INI_SCAN_DIR=/data/php/82/conf.d php-fpm82 -c /data/php/82 -y /data/php/82/php-fpm.conf -FORt > /dev/null 2>&1; then
        PHP_INI_SCAN_DIR=/data/php/82/conf.d php-fpm82 -c /data/php/82 -y /data/php/82/php-fpm.conf -FORt || sleep inf
    fi
fi
