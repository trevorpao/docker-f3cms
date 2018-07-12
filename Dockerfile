FROM php:5.6-apache
MAINTAINER Wayne <me@weien.tw>

RUN requirements="nano cron mariadb-client-10.1 libpng-dev libmcrypt-dev libmcrypt4 libcurl3-dev libxml2-dev libfreetype6 libjpeg62-turbo libfreetype6-dev libjpeg62-turbo-dev libmagickwand-dev" \
    && apt-get update && apt-get install -y --no-install-recommends $requirements && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install soap \
    && docker-php-ext-install mysqli \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && requirementsToRemove="libmcrypt-dev libcurl3-dev libxml2-dev libpng12-dev libfreetype6-dev libjpeg62-turbo-dev" \
    && apt-get purge --auto-remove -y $requirementsToRemove

## build custom conf
RUN mkdir -p  /etc/apache2/custom-conf
RUN echo "" >> /etc/apache2/apache2.conf \
    && echo "# Include the configurations from the host machine" >> /etc/apache2/apache2.conf \
    && echo "IncludeOptional custom-conf/*.conf" >> /etc/apache2/apache2.conf

RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/ssl-cert-snakeoil.key -out /etc/ssl/certs/ssl-cert-snakeoil.pem -subj "/C=TW/ST=Taiwan/L=Taipei/O=Security/OU=Development/CN=local.host"

RUN a2enmod rewrite
RUN a2ensite default-ssl
RUN a2enmod ssl
RUN a2enmod headers
RUN a2enmod proxy
RUN a2enmod proxy_http
RUN a2enmod proxy_wstunnel

EXPOSE 80
EXPOSE 443
