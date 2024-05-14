<?php
return $config = [
    "nginx_conf" => getenv("NGINX_CONF") ?: "/usr/local/nginx/conf",
    "data_path" => getenv("DATA_PATH") ?: "/data",
];
