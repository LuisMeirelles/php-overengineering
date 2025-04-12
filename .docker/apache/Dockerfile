FROM php:8.4-apache

ARG UID
ARG GID
ARG SERVER_NAME

WORKDIR /var/www/html

RUN apt-get update && \
    apt-get install -y \
    git \
    zip \
    unzip \
    libcurl4-openssl-dev \
    libevent-dev \
    libssl-dev \
    libicu-dev \
    libidn2-dev \
    zlib1g-dev && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql && \
    pecl install raphf apcu && \
    docker-php-ext-enable raphf apcu && \
    pecl install pecl_http && \
    docker-php-ext-enable http && \
    a2enmod rewrite

RUN usermod -u $UID www-data && groupmod -g $GID www-data && \
        chown -R $UID:$GID /var/www/html

COPY --from=composer:latest --chown=www-data:www-data /usr/bin/composer /usr/bin/composer

USER www-data
