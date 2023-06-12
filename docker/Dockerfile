FROM php:8.2-fpm-alpine3.17

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN echo "Updating Alpine Linux..." \
    && apk update 1>/dev/null \
    && echo "-> Done"

RUN echo "Installing PHP extensions..." \
    && install-php-extensions pdo_pgsql \
    && echo "-> Done"

RUN echo "Installing PECL extensions..." \
    && apk --no-cache --update add build-base autoconf \
    && apk del build-base autoconf \
    && echo "-> Done"

RUN echo "Installing Dev tools..." \
    && install-php-extensions sockets xdebug \
    && echo "-> Done"

RUN echo "Installing Composer..." \
    && apk --no-cache --update add git 1>/dev/null \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && echo "-> Done"

RUN echo "Cleaning docker..." \
    && rm -Rf /var/cache/apk/* 1>/dev/null \
    && rm -Rf /tmp/* 1>/dev/null \
    && rm -f /usr/local/bin/install-php-extensions 1>/dev/null \
    && echo "-> Done"

RUN echo "Creating nginx user..." \
    && addgroup -g 101 -S nginx \
    && adduser -S -D -H -u 101 -h /var/cache/nginx -s /sbin/nologin -G nginx -g nginx nginx \
    && echo "-> Done"

WORKDIR /var/www