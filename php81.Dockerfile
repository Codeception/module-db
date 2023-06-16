FROM php:8.1-cli

ARG USERNAME=codeception
ARG USER_UID=1000
ARG USER_GID=$USER_UID

RUN groupadd --gid $USER_GID $USERNAME &&  \
    useradd --uid $USER_UID --gid $USER_GID -m $USERNAME

RUN apt-get update && \
    apt-get install -y \
        unzip \
        wget \
        git \
        zlib1g-dev \
        libzip-dev \
		mariadb-client

RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install zip

RUN pecl install xdebug-3.1.5 && \
    echo zend_extension=xdebug.so > $PHP_INI_DIR/conf.d/xdebug.ini

USER $USERNAME

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json .
COPY composer.lock .

RUN composer install --no-interaction --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload -o

ENTRYPOINT ["tail"]
CMD ["-f", "/dev/null"]
