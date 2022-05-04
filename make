
# Copyright (C) Cloudflare, Inc.

QUICHE_COMMON_FLAGS="--package quiche --verbose --no-default-features --features ffi"

# Default is release build
QUICHE_BUILD_FLAGS="$QUICHE_COMMON_FLAGS --release"
QUICHE_BUILD_TARGET="release"

if [ $NGX_DEBUG = YES ]; then
    QUICHE_BUILD_FLAGS="$QUICHE_COMMON_FLAGS"
    QUICHE_BUILD_TARGET="debug"
fi


cat << END                                                    >> $NGX_MAKEFILE

$QUICHE/target/$QUICHE_BUILD_TARGET/libquiche.a: \\
		$OPENSSL/.openssl/include/openssl/ssl.h \\
		$NGX_MAKEFILE
	cd $QUICHE && cargo build $QUICHE_BUILD_FLAGS $QUICHE_OPT

END
