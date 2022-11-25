FROM php:7.4-apache

RUN apt-get update && \
    apt-get install -y \
        unzip \
        wget \
        git \
        zlib1g-dev \
        libzip-dev \
		mariadb-client-10.5

RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install zip

RUN pecl install xdebug-3.1.5 && \
    echo zend_extension=xdebug.so > $PHP_INI_DIR/conf.d/xdebug.ini

# Install composer
RUN EXPECTED_CHECKSUM="$(wget -q -O - https://composer.github.io/installer.sig)" && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --quiet && \
    RESULT=$? && \
    rm composer-setup.php && \
    mv composer.phar /usr/local/bin/composer && \
    exit $RESULT

WORKDIR /var/www/html

COPY composer.json .
COPY composer.lock .

RUN composer install --no-autoloader

COPY . .

RUN composer dump-autoload -o
