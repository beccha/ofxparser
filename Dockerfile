FROM php:8.1-fpm-alpine
RUN apk update && apk add --no-cache build-base
RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-3.1.6 \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install bcmath
WORKDIR /app
ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"
