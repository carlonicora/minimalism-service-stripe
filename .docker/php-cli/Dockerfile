FROM php:8.1.0-cli-alpine3.14

RUN apk update \
    && apk add --no-cache curl g++ make git autoconf bash

RUN docker-php-ext-install mysqli bcmath \
    && pecl install xdebug redis \
    && docker-php-ext-enable xdebug redis \
    && docker-php-source delete \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && export COMPOSER_PROCESS_TIMEOUT=600

COPY ./shell/ /tmp/scripts/
COPY ./php-cli/configurations/* /usr/local/etc/php/conf.d/
RUN chmod -R 777 /tmp/scripts/ \
    && /tmp/scripts/distributeConfig.sh /usr/local/etc/php/conf.d/zz-xdebug.ini "{XDEBUG_PORT}" "${XDEBUG_PORT}" \
    && /tmp/scripts/distributeConfig.sh /usr/local/etc/php/conf.d/zz-xdebug.ini "{XDEBUG_HOST}" "${XDEBUG_HOST}" \
    && rm -rf /tmp/*