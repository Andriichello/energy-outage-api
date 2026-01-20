#!/bin/bash
set -euo pipefail

sh /var/www/energy-outage-api/resources/scripts/setup-vars.sh

cd /var/www/energy-outage-api

aws configure set aws_access_key_id ${AWS_ACCESS_KEY_ID}
aws configure set aws_secret_access_key ${AWS_SECRET_ACCESS_KEY}

aws s3 --endpoint-url https://storage.googleapis.com cp s3://${AWS_BUCKET}/google-cloud.json google-cloud.json
aws s3 --endpoint-url https://storage.googleapis.com cp s3://${AWS_BUCKET}/.env.${ENV} .env

php artisan storage:link
php artisan cache:clear
php artisan route:cache

# Create /var/run/php if it doesn't exist
if [ ! -d "/var/run/php" ]; then
    mkdir -p /var/run/php
fi

sh ./resources/scripts/setup-dirs.sh
sh ./resources/scripts/setup-logs.sh

# Start supervisord, which manages Nginx and PHP-FPM for us.
exec supervisord -c /etc/supervisor/supervisord.conf
