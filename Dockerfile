# Required PHP version: 8.5 (matches composer.json platform requirement)
ARG PHP_VERSION=8.5

###########################################
# Composer dependencies stage
###########################################
FROM php:${PHP_VERSION}-cli-alpine AS composer-deps

WORKDIR /app

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl sockets zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --no-ansi \
    --no-scripts \
    --prefer-dist

###########################################
# Main application stage
###########################################
FROM php:${PHP_VERSION}-cli-alpine

LABEL maintainer="Liberu Software <hello@liberu.co.uk>"
LABEL org.opencontainers.image.title="Liberu Billing"
LABEL org.opencontainers.image.description="Production-ready Dockerfile for Liberu Billing (Laravel Octane / RoadRunner)"
LABEL org.opencontainers.image.source=https://github.com/liberu-billing/billing-laravel
LABEL org.opencontainers.image.licenses=MIT

ARG WWWUSER=1000
ARG WWWGROUP=1000
ARG TZ=UTC

ENV TERM=xterm-color \
    WITH_HORIZON=false \
    WITH_SCHEDULER=false \
    OCTANE_SERVER=roadrunner \
    CONTAINER_MODE=app \
    USER=octane \
    ROOT=/var/www/html \
    COMPOSER_FUND=0 \
    COMPOSER_MAX_PARALLEL_HTTP=24

WORKDIR ${ROOT}

SHELL ["/bin/sh", "-eou", "pipefail", "-c"]

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apk update && \
    apk upgrade && \
    apk add --no-cache \
        curl \
        wget \
        nano \
        ncdu \
        procps \
        ca-certificates \
        supervisor \
        libsodium-dev && \
    install-php-extensions \
        bz2 \
        pcntl \
        mbstring \
        bcmath \
        sockets \
        pgsql \
        pdo_pgsql \
        opcache \
        exif \
        pdo_mysql \
        zip \
        intl \
        gd \
        redis \
        igbinary && \
    docker-php-source delete && \
    rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

RUN arch="$(apk --print-arch)" \
    && case "$arch" in \
        armhf)   _cronic_fname='supercronic-linux-arm' ;; \
        aarch64) _cronic_fname='supercronic-linux-arm64' ;; \
        x86_64)  _cronic_fname='supercronic-linux-amd64' ;; \
        x86)     _cronic_fname='supercronic-linux-386' ;; \
        *) echo >&2 "error: unsupported architecture: $arch"; exit 1 ;; \
    esac \
    && wget -q "https://github.com/aptible/supercronic/releases/download/v0.2.29/${_cronic_fname}" \
        -O /usr/bin/supercronic \
    && chmod +x /usr/bin/supercronic \
    && mkdir -p /etc/supercronic \
    && echo "*/1 * * * * php ${ROOT}/artisan schedule:run --no-interaction" > /etc/supercronic/laravel

RUN addgroup -g ${WWWGROUP} ${USER} \
    && adduser -D -h ${ROOT} -G ${USER} -u ${WWWUSER} -s /bin/sh ${USER}

RUN mkdir -p /var/log/supervisor /var/run/supervisor /etc/supervisor/conf.d \
    && chown -R ${USER}:${USER} ${ROOT} /var/log /var/run \
    && chmod -R a+rw ${ROOT} /var/log /var/run

RUN cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini

USER ${USER}

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --chown=${USER}:${USER} --from=composer-deps /app/vendor ./vendor

COPY --chown=${USER}:${USER} composer.json composer.lock ./

COPY --chown=${USER}:${USER} . .

RUN composer dump-autoload --classmap-authoritative --no-dev && \
    composer clear-cache

RUN mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache && \
    chmod -R a+rw storage

# Copy PHP configuration
COPY --chown=${USER}:${USER} .docker/octane/php.ini ${PHP_INI_DIR}/conf.d/99-octane.ini

# Copy RoadRunner config
COPY --chown=${USER}:${USER} .docker/octane/RoadRunner/.rr.prod.yaml ${ROOT}/.rr.yaml

# Copy supervisor main config
COPY --chown=${USER}:${USER} .docker/octane/supervisord.app.conf /etc/supervisor/conf.d/supervisord.app.conf
COPY --chown=${USER}:${USER} .docker/octane/supervisord.app.roadrunner.conf /etc/supervisor/conf.d/supervisord.app.roadrunner.conf
COPY --chown=${USER}:${USER} .docker/octane/supervisord.horizon.conf /etc/supervisor/conf.d/supervisord.horizon.conf
COPY --chown=${USER}:${USER} .docker/octane/FrankenPHP/supervisord.frankenphp.conf /etc/supervisor/conf.d/supervisord.frankenphp.conf

# Copy entrypoint
COPY --chown=${USER}:${USER} .docker/octane/entrypoint.sh /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

# Copy environment file
COPY --chown=${USER}:${USER} .env.example ./.env

RUN cat .docker/octane/utilities.sh >> ~/.bashrc

EXPOSE 8000

ENTRYPOINT ["start-container"]

HEALTHCHECK --start-period=5s --interval=2s --timeout=5s --retries=8 \
    CMD php artisan octane:status || exit 1
