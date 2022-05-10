# This is a Dockerfile intended to be built using `docker buildx`
# for multi-arch support. Building with `docker build` may have unexpected results.

# This file assumes that the frontend has been built using ./scripts/frontend-build

FROM debian:bullseye

EXPOSE 80 81 443

ARG GEOIP2_ACCOUNT_ID=123456
ARG GEOIP2_LICENSE_KEY=AbcD1EfGHI2JKLmN

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
    OPTION=1 \
    PAGESPEED=y \
    HEADERMOD=y \
    FANCYINDEX=y \
    CACHEPURGE=y \
    SUBFILTER=y \
    LUA=y \
    WEBDAV=y \
    VTS=y \
    RTMP=y \
    TESTCOOKIE=y \
    HTTP3=y \
    MODSEC=y \
    REDIS2=y \
    HTTPREDIS=y \
    SRCACHE=y \
    SETMISC=y \
    NGXECHO=y \
    HPACK=y \
    SSL=1 \
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

RUN rm /etc/apt/sources.list && \
    echo "deb http://deb.debian.org/debian bullseye main" >> /etc/apt/sources.list && \
    echo "deb http://deb.debian.org/debian/ bullseye-updates main" >> /etc/apt/sources.list && \
    echo "deb http://security.debian.org/debian-security bullseye-security main" >> /etc/apt/sources.list && \
    apt update -y && \
    apt upgrade -y --allow-downgrades && \
    apt dist-upgrade -y --allow-downgrades && \
    apt autoremove -y && \
    apt -o DPkg::Options::="--force-confnew" -y install curl coreutils lsb-release gnupg software-properties-common && \
    curl -s https://deb.nodesource.com/gpgkey/nodesource.gpg.key | gpg --dearmor | tee /usr/share/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/usr/share/keyrings/nodesource.gpg] https://deb.nodesource.com/node_16.x bullseye main" >> /etc/apt/sources.list && \
    apt update -y && \
    apt upgrade -y --allow-downgrades && \
    apt dist-upgrade -y --allow-downgrades && \
    apt autoremove -y && \
    apt -o DPkg::Options::="--force-confnew" -y install -y python3 python3-pip certbot curl gcc make g++ git nodejs build-essential ca-certificates git sqlite3 \
    tar unzip moreutils jq logrotate wget python-is-python3 knot-dnsutils redis-tools redis-server && \
    npm i -g yarn && \
    wget https://raw.githubusercontent.com/SanCraftDev/nginx-autoinstall/master/nginx-autoinstall.sh && \
    chmod +x nginx-autoinstall.sh && \
    ./nginx-autoinstall.sh && \
    
    /tmp/install-s6 "${TARGETPLATFORM}" && \
    rm -f /tmp/install-s6 && \

    wget https://raw.githubusercontent.com/mitchellkrogza/nginx-ultimate-bad-bot-blocker/master/install-ngxblocker -O /usr/local/sbin/install-ngxblocker && \
    chmod +x /usr/local/sbin/install-ngxblocker && \
    cd /usr/local/sbin && \
    ./install-ngxblocker && \
    ./install-ngxblocker -x && \
    chmod +x /usr/local/sbin/setup-ngxblocker && \
    chmod +x /usr/local/sbin/update-ngxblocker && \
    ./setup-ngxblocker -e conf && \
    ./setup-ngxblocker -x -e conf && \

    cd /app && \
    yarn install && \

# Remove frontend service not required for prod, dev nginx config as well
    rm -rf /etc/services.d/frontend /etc/nginx/conf.d/dev.conf && \

# Change permission of logrotate config file
    chmod 644 /etc/logrotate.d/nginx-proxy-manager && \

# fix for pip installs
# https://github.com/NginxProxyManager/nginx-proxy-manager/issues/1769
    pip uninstall --yes setuptools \
	&& pip install "setuptools==58.0.0"

COPY docker/rootfs /

VOLUME [ "/data", "/etc/letsencrypt" ]
ENTRYPOINT [ "/init" ]

LABEL org.label-schema.schema-version="1.0" \
	org.label-schema.license="MIT" \
	org.label-schema.name="nginx-proxy-manager" \
	org.label-schema.description="Docker container for managing Nginx proxy hosts with a simple, powerful interface " \
	org.label-schema.url="https://github.com/jc21/nginx-proxy-manager" \
	org.label-schema.vcs-url="https://github.com/jc21/nginx-proxy-manager.git" \
	org.label-schema.cmd="docker run --rm -ti jc21/nginx-proxy-manager:latest"
