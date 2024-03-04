FROM php:8.1-cli

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

RUN apt-get update && \
    apt-get install -y \
        unzip \
        wget \
        git \
        zlib1g-dev \
        libzip-dev \
        libpq-dev \
        mariadb-client-10.5

RUN install-php-extensions \
    pdo_mysql-stable \
    pdo_pgsql-stable \
    pdo_dblib-stable \
    pdo_sqlsrv-5.11.0 \
    pgsql-stable \
    zip-stable \
    xdebug-3.1.5

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENTRYPOINT ["tail"]
CMD ["-f", "/dev/null"]
