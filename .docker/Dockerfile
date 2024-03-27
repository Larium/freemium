FROM php:8.2-cli
WORKDIR "/opt/php"

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && \
    apt-get install -y zip libzip-dev libxml2-dev && \
    pecl channel-update pecl.php.net && \
    pecl install xdebug && \
    docker-php-ext-install bcmath zip soap && \
    docker-php-source delete && \
    rm -r /tmp/* /var/cache/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
     && php composer-setup.php \
     && php -r "unlink('composer-setup.php');" \
     && chmod +x composer.phar \
     && mv composer.phar /usr/local/bin/composer

RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v3.phar -o php-cs-fixer \
    && chmod a+x php-cs-fixer \
    && mv php-cs-fixer /usr/bin/php-cs-fixer
