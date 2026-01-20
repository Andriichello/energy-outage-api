FROM alpine:edge

ARG ENV
ARG DOMAIN
ARG AWS_BUCKET
ARG AWS_ACCESS_KEY_ID
ARG AWS_SECRET_ACCESS_KEY

# Install necessary packages
RUN apk add --no-cache \
    nginx supervisor openssl \
    bash wget git unzip curl nano dos2unix \
    php83 php83-fpm php83-cli \
    php83-intl php83-xml php83-zip php83-curl \
    php83-gmp php83-bcmath php83-mbstring \
    php83-pgsql php83-pdo_pgsql php83-openssl php83-phar \
    php83-tokenizer php83-session php83-fileinfo \
    php83-simplexml php83-dom  php83-xmlwriter  \
    php83-pdo_mysql php83-gd \
    openssh-client python3 py3-pip \
    && pip3 install awscli --break-system-packages

# Next, we copy docker and scripts folders.
COPY resources/docker /

# Copy project
COPY . /var/www/power-outage-api

# Set up workspace and permissions
WORKDIR /var/www/power-outage-api

RUN (addgroup -S www-data || true)  \
    && (adduser -S -G www-data www-data || true) \
    && chown -R www-data:www-data ./storage

# AWS Credentials setup
RUN aws configure set aws_access_key_id ${AWS_ACCESS_KEY_ID} \
    && aws configure set aws_secret_access_key ${AWS_SECRET_ACCESS_KEY} \
    && aws s3 --endpoint-url https://storage.googleapis.com cp s3://${AWS_BUCKET}/google-cloud.json google-cloud.json \
    && aws s3 --endpoint-url https://storage.googleapis.com cp s3://${AWS_BUCKET}/.env.${ENV} .env

# Environment variables setup
RUN echo "ENV=\"$ENV\"" >> /etc/environment \
    && echo "DOMAIN=\"$DOMAIN\"" >> /etc/environment \
    && echo "AWS_BUCKET=\"$AWS_BUCKET\"" >> /etc/environment \
    && echo "AWS_ACCESS_KEY_ID=\"$AWS_ACCESS_KEY_ID\"" >> /etc/environment \
    && echo "AWS_SECRET_ACCESS_KEY=\"$AWS_SECRET_ACCESS_KEY\"" >> /etc/environment

# Install and setup Nginx, Composer
RUN chmod -R u+x ./resources/scripts \
    && sh ./resources/scripts/setup-dirs.sh \
    && sh ./resources/scripts/setup-logs.sh \
    && sh ./resources/scripts/nginx/setup.sh

# Install Composer and dependencies
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install -o -n

# Expose ports
EXPOSE 80 443

# The default script for the image will mainly start PHP and Apache.
CMD ["/opt/start.sh"]
