FROM ubuntu:22.10 as nginxbuilder
RUN apt update -y && apt upgrade -y --allow-downgrades && apt dist-upgrade -y --allow-downgrades && apt autoclean && apt clean && apt autoremove -y && apt -o DPkg::Options::="--force-confnew" -y install python3-pip jq logrotate gcc g++ nodejs certbot uuid-dev make cargo rustc build-essential curl wget libpcre3 libpcre3-dev zlib1g-dev git brotli patch git unzip cmake libssl-dev perl -y
RUN curl "https://openresty.org/download/openresty-1.21.4.1rc3.tar.gz" | tar zx
RUN cp -r openresty-1.21.4.1rc3/. .
RUN wget "https://github.com/apache/incubator-pagespeed-ngx/archive/refs/heads/master.zip" && unzip master.zip
RUN cd incubator-pagespeed-ngx-master && curl https://dist.apache.org/repos/dist/release/incubator/pagespeed/1.14.36.1/x64/psol-1.14.36.1-apache-incubating-x64.tar.gz | tar zx
RUN git clone --recursive https://github.com/google/ngx_brotli
RUN git clone --recursive https://github.com/cloudflare/quiche && cd quiche && git checkout tags/0.12.0
RUN patch -p01 < quiche/nginx/nginx-1.16.patch; exit 0
RUN ./configure \
  --prefix=$PWD \
  --build="quiche-$(git --git-dir=./quiche/.git rev-parse --short HEAD)" \
  --sbin-path=/usr/sbin/nginx \
  --modules-path=/usr/lib/nginx/modules \
  --conf-path=/etc/nginx/nginx.conf \
  --error-log-path=/var/log/nginx/error.log \
  --http-log-path=/var/log/nginx/access.log \
  --pid-path=/var/run/nginx.pid \
  --lock-path=/var/run/nginx.lock \
  --http-client-body-temp-path=/var/cache/nginx/client_temp \
  --http-proxy-temp-path=/var/cache/nginx/proxy_temp \
  --http-fastcgi-temp-path=/var/cache/nginx/fastcgi_temp \
  --http-uwsgi-temp-path=/var/cache/nginx/uwsgi_temp \
  --http-scgi-temp-path=/var/cache/nginx/scgi_temp \
  --user=nginx \
  --group=nginx \
  --with-compat \
  --with-threads \
  --with-http_addition_module \
  --with-http_auth_request_module \
  --with-http_dav_module \
  --with-http_flv_module \
  --with-http_gunzip_module \
  --with-http_gzip_static_module \
  --with-http_mp4_module \
  --with-http_random_index_module \
  --with-http_realip_module \
  --with-http_secure_link_module \
  --with-http_slice_module \
  --with-http_ssl_module \
  --with-http_stub_status_module \
  --with-http_sub_module \
  --with-http_v2_module \
  --with-http_v3_module \
  --with-mail \
  --with-mail_ssl_module \
  --with-stream \
  --with-stream_realip_module \
  --with-stream_ssl_module \
  --with-stream_ssl_preread_module \
  --with-pcre-jit \
  --with-ipv6 \
  --with-http_redis2_module \
  --with-http_iconv_module \
  --with-http_postgres_module \
  --add-module=./incubator-pagespeed-ngx-master \
  --add-module=./ngx_brotli \
  --with-openssl=./quiche/quiche/deps/boringssl \
  --with-quiche=./quiche && \
  make -j2

FROM ubuntu:22.10
COPY --from=nginxbuilder ./ ./build
RUN apt update -y && apt upgrade -y --allow-downgrades && apt dist-upgrade -y --allow-downgrades && apt autoclean && apt clean && apt autoremove -y && apt -o DPkg::Options::="--force-confnew" -y install python3-pip jq logrotate gcc g++ nodejs certbot uuid-dev make cargo rustc build-essential curl wget libpcre3 libpcre3-dev zlib1g-dev git brotli patch git unzip cmake libssl-dev perl -y
RUN cd build && make install
RUN rm -rf build

ARG TARGETPLATFORM
ARG BUILD_VERSION
ARG BUILD_COMMIT
ARG BUILD_DATE

ENV SUPPRESS_NO_CONFIG_WARNING=1 \
	S6_FIX_ATTRS_HIDDEN=1 \
	S6_BEHAVIOUR_IF_STAGE2_FAILS=1 \
	NODE_ENV=production \
	NPM_BUILD_VERSION="${BUILD_VERSION}" \
	NPM_BUILD_COMMIT="${BUILD_COMMIT}" \
	NPM_BUILD_DATE="${BUILD_DATE}"

RUN echo "fs.file-max = 65535" > /etc/sysctl.conf \
	&& apt-get update \
	&& apt-get install -y --no-install-recommends jq logrotate \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

COPY scripts/install-s6 /tmp/install-s6
RUN /tmp/install-s6 "${TARGETPLATFORM}" && rm -f /tmp/install-s6

EXPOSE 80 81 443

COPY backend       /app
COPY frontend/dist /app/frontend
COPY global        /app/global

WORKDIR /app
RUN yarn install

COPY docker/rootfs /

RUN rm -rf /etc/services.d/frontend /etc/nginx/conf.d/dev.conf

RUN chmod 644 /etc/logrotate.d/nginx-proxy-manager

VOLUME [ "/data", "/etc/letsencrypt" ]
ENTRYPOINT [ "/init" ]

LABEL org.label-schema.schema-version="1.0" \
	org.label-schema.license="MIT" \
	org.label-schema.name="nginx-proxy-manager-quici" \
	org.label-schema.description="Docker container for managing Nginx proxy hosts with a simple, powerful interface " \
	org.label-schema.url="https://github.com/Sanoj2020/nginx-proxy-manager-quici" \
	org.label-schema.vcs-url="https://github.com/Sanoj2020/nginx-proxy-manager-quici.git" \
	org.label-schema.cmd="docker run --rm -ti sanoj2020/nginx-proxy-manager-quici:latest"
