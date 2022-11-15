FROM alpine:20221110 as build

RUN apk upgrade --no-cache && \ 
    apk add --no-cache ca-certificates wget git && \ 
    git clone --recursive https://github.com/SanCraftDev/Nginx-Fancyindex-Theme /nft && \
    wget https://ssl-config.mozilla.org/ffdhe2048.txt -O /etc/ssl/dhparam

FROM sancraftdev/openresty-nginx-quic:latest

ARG S6_VERSION=v1.22.1.0

ARG TARGETPLATFORM \
    BUILD_VERSION \
    BUILD_COMMIT \
    BUILD_DATE

COPY rootfs        /
COPY backend       /app
COPY frontend/dist /app/frontend

COPY --from=build /etc/ssl/dhparam /etc/ssl/dhparam
COPY --from=build /nft/Nginx-Fancyindex-Theme-dark /nft

RUN apk upgrade --no-cache && \
    apk add --no-cache ca-certificates wget \
    nodejs-current npm \
    python3 py3-pip \
    bash logrotate apache2-utils openssl \
    gcc g++ libffi-dev python3-dev && \
    
# s6 overlay
    if [ "$TARGETPLATFORM" = "linux/amd64" ]; then export ARCH=amd64; fi && \
    if [ "$TARGETPLATFORM" = "linux/arm64" ]; then export ARCH=aarch64; fi && \
    wget https://github.com/just-containers/s6-overlay/releases/download/"$S6_VERSION"/s6-overlay-"$ARCH".tar.gz -O - | tar xz -C / && \

# Change permission
    chmod 644 /etc/logrotate.d/nginx-proxy-manager && \
    chmod +x /bin/check-health && \
    chmod +x /bin/handle-ipv6-setting && \
    
# Clean
    rm -rf /tmp && \

# Build Backend
    cd /app && \
    npm install --force && \
    pip install --no-cache-dir certbot && \
    apk del --no-cache gcc g++ libffi-dev python3-dev npm

ENV BUILD_VERSION=${BUILD_VERSION} \
    BUILD_COMMIT=${BUILD_COMMIT} \
    BUILD_DATE=${BUILD_DATE} \
    
    NPM_BUILD_VERSION=${BUILD_VERSION} \
    NPM_BUILD_COMMIT=${BUILD_COMMIT} \
    NPM_BUILD_DATE=${BUILD_DATE} \

    SUPPRESS_NO_CONFIG_WARNING=1 \
    S6_FIX_ATTRS_HIDDEN=1 \
    S6_BEHAVIOUR_IF_STAGE2_FAILS=1 \
    NODE_OPTIONS=--openssl-legacy-provider \
    DB_SQLITE_FILE=/data/database.sqlite \
    NODE_ENV=production

EXPOSE 80 81 443 81/udp 443/udp
VOLUME [ "/data", "/etc/letsencrypt" ]
ENTRYPOINT [ "/init" ]

HEALTHCHECK CMD /bin/check-health

LABEL org.label-schema.schema-version="1.0" \
      org.label-schema.license="MIT" \
      org.label-schema.name="nginx-proxy-manager" \
      org.label-schema.description="Docker container for managing Nginx proxy hosts with a simple, powerful interface " \
      org.label-schema.url="https://github.com/SanCraftDev/nginx-proxy-manager" \
      org.label-schema.vcs-url="https://github.com/SanCraftDev/nginx-proxy-manager.git" \
      org.label-schema.cmd="docker run --rm -it sancraftdev/nginx-proxy-manager:latest" \ 
      org.opencontainers.image.source="https://github.com/SanCraftDev/nginx-proxy-manager"
