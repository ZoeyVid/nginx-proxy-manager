# This is a Dockerfile intended to be built using `docker buildx`
# for multi-arch support. Building with `docker build` may have unexpected results.

# This file assumes that the frontend has been built using ./scripts/frontend-build

FROM sancraftdev/openresty-nginx-quic:develop

EXPOSE 80 81 443

ENV DEBIAN_FRONTEND=noninteractive \
    SUPPRESS_NO_CONFIG_WARNING=1 \
    S6_FIX_ATTRS_HIDDEN=1 \
    S6_BEHAVIOUR_IF_STAGE2_FAILS=1 \
    NODE_ENV=production \
    NPM_BUILD_VERSION="${BUILD_VERSION}" \
    NPM_BUILD_COMMIT="${BUILD_COMMIT}" \
    NPM_BUILD_DATE="${BUILD_DATE}" \
    HEADLESS=y \
    NGINX_VER=MAINLINE \
    BROTLI=y \
#    OPTION=1 \
    PAGESPEED=y \
#    HEADERMOD=y \
    FANCYINDEX=y \
#    CACHEPURGE=y \
#    SUBFILTER=y \
    LUA=y \
#    WEBDAV=y \
#    VTS=y \
#    RTMP=y \
#    TESTCOOKIE=y \
    HTTP3=y \
#    MODSEC=y \
#    REDIS2=y \
#    HTTPREDIS=y \
#    SRCACHE=y \
#    NGXECHO=y \
#    HPACK=y \
#    TLSDYN=y \
#    SSL=1 \
    RM_CONF=y \
    RM_LOGS=y \
    GEOIP=y
    
ARG TARGETPLATFORM
ARG BUILD_VERSION
ARG BUILD_COMMIT
ARG BUILD_DATE

COPY backend            /app
COPY global             /app/global
COPY frontend/dist      /app/frontend
COPY scripts/install-s6 /tmp/install-s6

WORKDIR /app

RUN /tmp/install-s6 "${TARGETPLATFORM}" && \
    rm -f /tmp/install-s6 && \

    cd /app && \
    yarn install && \

# Remove frontend service not required for prod, dev nginx config as well
    rm -rf /etc/services.d/frontend /etc/nginx/conf.d/dev.conf

COPY docker/rootfs /

# Change permission of logrotate config file
RUN chmod 644 /etc/logrotate.d/nginx-proxy-manager && \

# fix for pip installs
# https://github.com/NginxProxyManager/nginx-proxy-manager/issues/1769
    pip uninstall --yes setuptools \
	&& pip install "setuptools==58.0.0"

VOLUME [ "/data", "/etc/letsencrypt" ]
ENTRYPOINT [ "/init" ]

LABEL org.label-schema.schema-version="1.0" \
	org.label-schema.license="MIT" \
	org.label-schema.name="nginx-proxy-manager" \
	org.label-schema.description="Docker container for managing Nginx proxy hosts with a simple, powerful interface " \
	org.label-schema.url="https://github.com/SanCraftDev/nginx-proxy-manager" \
	org.label-schema.vcs-url="https://github.com/SanCraftDev/nginx-proxy-manager.git" \
	org.label-schema.cmd="docker run --rm -ti sancraftdev/nginx-proxy-manager:latest"
