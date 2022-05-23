# This is a Dockerfile intended to be built using `docker buildx`
# for multi-arch support. Building with `docker build` may have unexpected results.

# This file assumes that the frontend has been built using ./scripts/frontend-build

FROM sancraftdev/openresty-nginx-quic:latest

ARG TARGETPLATFORM \
    BUILD_VERSION \
    BUILD_COMMIT \
    BUILD_DATE

ENV SUPPRESS_NO_CONFIG_WARNING=1 \
	S6_FIX_ATTRS_HIDDEN=1 \
	S6_BEHAVIOUR_IF_STAGE2_FAILS=1 \
	NODE_ENV=production

COPY backend       /app
COPY frontend/dist /app/frontend
COPY global        /app/global
COPY docker/rootfs /
     
# s6 overlay
RUN curl -L https://github.com/just-containers/s6-overlay/releases/download/v1.22.1.0/s6-overlay-amd64.tar.gz | tar xz -C / && \

# Change permission of logrotate config file
    chmod 644 /etc/logrotate.d/nginx-proxy-manager && \

# fix for pip installs
# https://github.com/NginxProxyManager/nginx-proxy-manager/issues/1769
    pip install "setuptools==58.0.0" && \

# Downloas dhparam
    curl -L https://ssl-config.mozilla.org/ffdhe2048.txt -o /dhparam \
    
# Clean
    rm -rf /tmp

EXPOSE 80 81 443 81/udp 443/udp
VOLUME [ "/data", "/etc/letsencrypt" ]
CMD [ "/init" ]

LABEL org.label-schema.schema-version="1.0" \
	org.label-schema.license="MIT" \
	org.label-schema.name="nginx-proxy-manager" \
	org.label-schema.description="Docker container for managing Nginx proxy hosts with a simple, powerful interface " \
	org.label-schema.url="https://github.com/SanCraftDev/nginx-proxy-manager" \
	org.label-schema.vcs-url="https://github.com/SanCraftDev/nginx-proxy-manager.git" \
	org.label-schema.cmd="docker run --rm -ti sancraftdev/nginx-proxy-manager:latest"
