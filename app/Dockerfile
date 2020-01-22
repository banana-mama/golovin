FROM php:7.3-fpm

RUN apt-get update && apt-get install -y \
        nginx \
        curl \
        wget \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libxslt-dev \
        libicu-dev \
        libmcrypt-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo_mysql zip \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd
    
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-install xsl
RUN docker-php-ext-install soap

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# REDIS
RUN pecl install redis

#ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

#RUN mkdir ~/.ssh/
#RUN touch ~/.ssh/known_hosts
#RUN ssh-keyscan github.com >> ~/.ssh/known_hosts

#COPY ./certs/git/app /root/.ssh/id_rsa

#COPY git.sh /
#RUN chmod u+x /git.sh
#RUN . /git.sh

WORKDIR /var/www/app

COPY bootstrap.sh /
RUN chmod u+x /bootstrap.sh
CMD ["/bootstrap.sh"]

