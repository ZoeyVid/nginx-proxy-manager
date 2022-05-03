FROM golang:alpine as build

RUN apk add git build-base

RUN git clone https://github.com/cloudflare/cloudflared --branch 2022.5.0 .
RUN make cloudflared

FROM golang:alpine
COPY --from=build cloudflared /usr/local/bin/cloudflared

ENTRYPOINT cloudflared --no-autoupdate tunnel run --token
CMD ["version"]
