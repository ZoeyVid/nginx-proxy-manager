FROM zoeyvid/nginx-quic:63
COPY rootfs          /
COPY backend         /app
COPY global          /app/global
COPY frontend/dist   /app/frontend

WORKDIR /app
RUN apk upgrade --no-cache && \
    apk add --no-cache ca-certificates wget tzdata \
    python3 nodejs-current \
    npm build-base python3-dev libffi-dev \
    grep coreutils jq openssl apache2-utils && \
# Change permission
    chmod +x /bin/start.sh && \
    chmod +x /bin/check-health.sh && \
# Install cross-env
    npm install --global cross-env && \
# Build Backend
    sed -i "s|\"0.0.0\"|\""$(cat global/.version)"\"|g" package.json && \
    npm install --force && \
# Install pip
    for file in /usr/lib/python*/EXTERNALLY-MANAGED; do rm -rf "$file"; done && \
    wget https://bootstrap.pypa.io/get-pip.py -O - | python3 && \
# Install Certbot
    pip install --no-cache-dir certbot && \
# Clean
    apk del --no-cache npm build-base python3-dev libffi-dev

ENV NODE_ENV=production \
    DB_SQLITE_FILE=/data/database.sqlite
    
ENTRYPOINT ["start.sh"]
HEALTHCHECK CMD check-health.sh