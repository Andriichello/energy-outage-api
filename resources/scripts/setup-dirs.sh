# Create /var/log if it doesn't exist
if [ ! -d "/var/log" ]; then
    mkdir -p /var/log
fi

# Create /var/run if it doesn't exist
if [ ! -d "/var/run" ]; then
    mkdir -p /var/run
fi

# Create /var/run/php if it doesn't exist
if [ ! -d "/var/run/php" ]; then
    mkdir -p /var/run/php
fi

# Create /var/lib/nginx/tmp/fastcgi if it doesn't exist
if [ ! -d "/var/lib/nginx/tmp/fastcgi" ]; then
    mkdir -p /var/lib/nginx/tmp/fastcgi
fi

chown -R www-data:www-data /var/lib/nginx/tmp/fastcgi
chmod -R 700 /var/lib/nginx/tmp/fastcgi
