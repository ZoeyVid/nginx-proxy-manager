FROM zoeyvid/nginx-quic:70
COPY rootfs          /
COPY backend         /app
COPY global          /app/global
COPY frontend/dist   /app/frontend

WORKDIR /app
RUN apk upgrade --no-cache && \
    apk add --no-cache ca-certificates wget tzdata \
    npm build-base libffi-dev \
    nodejs-current grep coreutils jq openssl apache2-utils && \
# Build Backend
    sed -i "s|\"0.0.0\"|\""$(cat global/.version)"\"|g" package.json && \
    npm install --force && \
# Install Certbot
    pip install --no-cache-dir certbot && \
# Clean
    apk del --no-cache npm build-base libffi-dev

ENV NODE_ENV=production \
    DB_SQLITE_FILE=/data/database.sqlite
    
ENTRYPOINT ["start.sh"]
HEALTHCHECK CMD check-health.sh
