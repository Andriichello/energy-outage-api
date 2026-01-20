# Create /var/log/supervisor if it doesn't exist
if [ ! -d "/var/log/supervisor" ]; then
    mkdir -p /var/log/supervisor
fi
if [ ! -f "/var/log/supervisor/supervisord.log" ]; then
    > /var/log/supervisor/supervisord.log
fi

# Create /var/log/nginx if it doesn't exist
if [ ! -d "/var/log/nginx" ]; then
    mkdir -p /var/log/nginx
fi
if [ ! -f "/var/log/nginx/access.log" ]; then
    > /var/log/nginx/access.log
fi
if [ ! -f "/var/log/nginx/error.log" ]; then
    > /var/log/nginx/error.log
fi

# Create /var/log/php-fpm if it doesn't exist
if [ ! -d "/var/log/php-fpm" ]; then
    mkdir -p /var/log/php-fpm
fi
if [ ! -f "/var/log/php-fpm/stdout.log" ]; then
    > /var/log/php-fpm/stdout.log
fi
if [ ! -f "/var/log/php-fpm/stderr.log" ]; then
    > /var/log/php-fpm/stderr.log
fi

# Create /var/log/queue-worker if it doesn't exist
if [ ! -d "/var/log/queue-worker" ]; then
    mkdir -p /var/log/queue-worker
fi
if [ ! -f "/var/log/queue-worker/stdout.log" ]; then
    > /var/log/queue-worker/stdout.log
fi
if [ ! -f "/var/log/queue-worker/stderr.log" ]; then
    > /var/log/queue-worker/stderr.log
fi

chmod -R 770 /var/log
