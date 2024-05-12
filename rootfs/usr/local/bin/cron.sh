#!/bin/sh

while true; do
    if [ "$LOGROTATE" = "true" ]; then
        logrotate --state /data/etc/logrotate.status /etc/logrotate;
    fi

    sleep 1h
done
