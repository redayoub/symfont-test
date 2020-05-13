FROM alpine:3.10

RUN apk update && apk upgrade
RUN apk add nginx
RUN apk add php7 \
    php7-fpm \
    php7-opcache \
    php7-pdo_mysql \
    php7-pdo \
    php7-zlib \
    php7-curl \
    php7-iconv \
    php7-json \
    php7-session \
    php7-openssl \
    php7-tokenizer \
    php7-phar \
    php7-mbstring \
    php7-dom \
    php7-xml \
    php7-fileinfo \
    php7-xmlwriter \
    php7-ctype
RUN apk add openrc curl

COPY ./docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod 777 /docker-entrypoint.sh
RUN mkdir -p /run/nginx
RUN mkdir -p /run/php-fpm && touch /run/php-fpm/php-fpm.sock && chmod 777 /run/php-fpm/php-fpm.sock

RUN ln -sf /dev/stdout /var/log/nginx/access.log && ln -sf /dev/stderr /var/log/nginx/error.log
RUN ln -sf /dev/stdout /var/log/php7/error.log

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/app

COPY ./docker/php-fpm-nginx/php-fpm.conf /etc/php7/php-fpm.conf
COPY ./docker/php-fpm-nginx/www.conf /etc/php7/php-fpm.d/www.conf
COPY ./docker/php-fpm-nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/php-fpm-nginx/default.conf /etc/nginx/conf.d/default.conf

COPY . .
RUN composer install --no-interaction --no-dev

EXPOSE 80

ENTRYPOINT [ "/docker-entrypoint.sh" ]
