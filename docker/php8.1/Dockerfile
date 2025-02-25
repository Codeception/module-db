FROM php:8.1-fpm

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

RUN apt-get update && \
    apt-get install -y \
        unzip \
        wget \
        git \
        zlib1g-dev \
        libzip-dev \
        libpq-dev \
        default-mysql-client

RUN install-php-extensions \
    pdo_mysql-stable \
    pdo_pgsql-stable \
    pdo_dblib-stable \
    pdo_sqlsrv-5.11.0 \
    pgsql-stable \
    zip-stable \
    xdebug-3.3.2

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENTRYPOINT ["tail"]
CMD ["-f", "/dev/null"]
