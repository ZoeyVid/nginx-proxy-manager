FROM golang:alpine as build

ENV GO111MODULE=on
ENV CGO_ENABLED=0
ENV TARGET_GOOS=${TARGET_GOOS}
ENV TARGET_GOARCH=${TARGET_GOARCH}

RUN apk add git build-base
RUN git clone https://github.com/cloudflare/cloudflared --branch 2022.5.0 /build/cloudflared
RUN cd /build/cloudflared && make -j2 cloudflared

FROM alpine
COPY --from=build /build/cloudflared/cloudflared /usr/local/bin/cloudflared

ENTRYPOINT cloudflared --no-autoupdate tunnel run --token ${token}
