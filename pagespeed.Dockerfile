FROM jc21/nginx-proxy-manager:2.9.18
ENV DEBIAN_FRONTEND=noninteractive
#ENV NGINX_VERSION=nginx-1.21.6
ENV OPENRESTY_VERSION=openresty-1.21.4.1rc3
#ENV QUICHE_NGINX_PATCH_1=1.16
#ENV QUICHE_NGINX_PATCH_2=1.19.7
#ENV QUICHE_VERSION=0.12.0
ENV PAGESPEED_INCUBATOR_VERSION=1.14.36.1
RUN apt update -y && apt upgrade -y --allow-downgrades && apt dist-upgrade -y --allow-downgrades && apt autoclean && apt clean && apt autoremove -y && apt -o DPkg::Options::="--force-confnew" -y install certbot uuid-dev make cargo rustc build-essential curl wget libpcre3 libpcre3-dev zlib1g-dev git brotli patch git unzip cmake libssl-dev perl software-properties-common -y
RUN apt-add-repository 'deb http://deb.debian.org/debian bullseye main' && apt-add-repository 'deb http://deb.debian.org/debian bullseye-updates main' && apt update -y && apt upgrade -y --allow-downgrades && apt dist-upgrade -y --allow-downgrades && apt autoclean && apt clean && apt autoremove -y && apt -o DPkg::Options::="--force-confnew" -y install libc-dev-bin libc-devtools libc6-dev-amd64-cross libc6-amd64-cross libcrypt1
#RUN curl "https://nginx.org/download/${NGINX_VERSION}.tar.gz" | tar zx
#RUN mv ${NGINX_VERSION} build
RUN curl "https://openresty.org/download/${OPENRESTY_VERSION}.tar.gz" | tar zx
RUN mv ${OPENRESTY_VERSION} build
RUN cd build && wget "https://github.com/apache/incubator-pagespeed-ngx/archive/refs/heads/master.zip" && unzip master.zip
RUN cd build/incubator-pagespeed-ngx-master && curl https://dist.apache.org/repos/dist/release/incubator/pagespeed/${PAGESPEED_INCUBATOR_VERSION}/x64/psol-${PAGESPEED_INCUBATOR_VERSION}-apache-incubating-x64.tar.gz | tar zx
RUN cd build && git clone --recursive https://github.com/google/ngx_brotli
#RUN cd build && git clone --recursive https://github.com/cloudflare/quiche && cd quiche && git checkout tags/${QUICHE_VERSION}
#RUN cd build && curl -L https://raw.githubusercontent.com/angristan/nginx-autoinstall/master/patches/nginx-http3-${QUICHE_NGINX_PATCH_2}.patch -o ./quiche/nginx/nginx-http3-1.19.7.patch
#RUN cd build && patch -p01 < quiche/nginx/nginx-${QUICHE_NGINX_PATCH_1}.patch; exit 0
#RUN cd build && patch -p01 < quiche/nginx/nginx-http3-${QUICHE_NGINX_PATCH_2}.patch; exit 0
RUN cd build && ./configure \
    --prefix=$PWD \
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
#    --with-http_v3_module \
    --with-mail \
    --with-mail_ssl_module \
    --with-stream \
    --with-stream_realip_module \
    --with-stream_ssl_module \
    --with-stream_ssl_preread_module \
    --add-module=./incubator-pagespeed-ngx-master \
    --add-module=./ngx_brotli \
#    --with-openssl=./quiche/quiche/deps/boringssl \
#    --with-quiche=./quiche \
    && make -j2 && make install && rm -rf /build
