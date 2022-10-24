FROM sancraftdev/openresty-nginx-quic:latest

ARG TARGETPLATFORM \
    BUILD_VERSION \
    BUILD_COMMIT \
    BUILD_DATE

ENV TARGETPLATFORM=${TARGETPLATFORM} \
    BUILD_VERSION=${BUILD_VERSION} \
    BUILD_COMMIT=${BUILD_COMMIT} \
    BUILD_DATE=${BUILD_DATE} \
    
    NPM_BUILD_VERSION=${BUILD_VERSION} \
    NPM_BUILD_COMMIT=${BUILD_COMMIT} \
    NPM_BUILD_DATE=${BUILD_DATE} \

    SUPPRESS_NO_CONFIG_WARNING=1 \
    S6_FIX_ATTRS_HIDDEN=1 \
    S6_BEHAVIOUR_IF_STAGE2_FAILS=1 \
    NODE_ENV=production \
    DB_SQLITE_FILE=/data/database.sqlite

COPY backend       /app
COPY frontend/dist /app/frontend
COPY docker/rootfs /
     
# s6 overlay
RUN curl -L https://github.com/just-containers/s6-overlay/releases/download/v1.22.1.0/s6-overlay-amd64.tar.gz | tar xz -C / && \

# Change permission of logrotate config file
    chmod 644 /etc/logrotate.d/nginx-proxy-manager && \
    
# Clean
    rm -rf /tmp

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
