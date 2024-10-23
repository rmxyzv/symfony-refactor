FROM composer:2.7.9 AS composer

FROM php:8.3-fpm-alpine3.20 AS dev

RUN apk --no-cache add \
    bash \
    curl \
    git \
    libpq-dev \
    libzip-dev \
    zip \
    mysql-client \
    icu-dev

COPY --from=composer /usr/bin/composer /usr/bin/composer

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions intl opcache pdo pdo_mysql zip redis soap sockets pcntl

# Add xdebug
RUN if [${ENABLE_XDEBUG} = "true"]; then install-php-extensions xdebug; fi

RUN mkdir -p /tmp && chmod -R 777 /tmp

COPY /./docker/php-fpm/conf.d /usr/local/etc/php/conf.d/

COPY . .

COPY bin/ /app/bin/
RUN chmod -R +x /app/bin

RUN mkdir -p /var/log/app
RUN chown -R www-data:www-data /var/log/app

WORKDIR /app

CMD ["php-fpm"]

EXPOSE 9000
