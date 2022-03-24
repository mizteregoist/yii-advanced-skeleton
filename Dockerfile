FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    libicu-dev \
    libfreetype6-dev \
    libonig-dev \
    libmagickwand-dev \
    libjpeg62-turbo-dev\
    libwebp-dev \
    libpng-dev \
    libc-client-dev \
    libpq-dev \
    libxml2-dev \
    libxslt-dev \
    libssl-dev \
    libmcrypt-dev \
    libzip-dev \
    libbz2-dev \
    libkrb5-dev \
    libldb-dev \
    libldap2-dev \
        && pecl install mcrypt \
	&& docker-php-ext-enable mcrypt \
        && pecl install apcu \
	&& docker-php-ext-enable apcu \
        && pecl install redis \
	&& docker-php-ext-enable redis \
        && pecl install igbinary \
	&& docker-php-ext-enable igbinary \
        && pecl install imagick \
	&& docker-php-ext-enable imagick \
        && docker-php-ext-install -j$(nproc) pdo pgsql mysqli pdo_pgsql pdo_mysql \
    	&& docker-php-ext-install -j$(nproc) iconv mbstring gettext exif \
    	&& docker-php-ext-install -j$(nproc) xml xsl json zip bz2 \
    	&& docker-php-ext-install -j$(nproc) opcache calendar \
        && docker-php-ext-install -j$(nproc) bcmath intl shmop \
        && docker-php-ext-install -j$(nproc) soap sockets \
        && docker-php-ext-install -j$(nproc) sysvmsg sysvsem sysvshm \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
        && docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD /docker/config/php/php.ini /usr/local/etc/php/conf.d/40-custom.ini
ADD /docker/config/php/apcu.ini /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

WORKDIR /var/www/html

COPY . /var/www/html

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

RUN find /var/www -exec chown www-data:www-data {} \; \
    && find /var/www -type d -exec chmod 775 {} \; \
    && find /var/www -type f -exec chmod 664 {} \; \
    && find /var/www/html/backend -maxdepth 1 -type d -name "runtime" -exec chmod 777 {} \; \
    && find /var/www/html/console -maxdepth 1 -type d -name "runtime" -exec chmod 777 {} \; \
    && find /var/www/html/frontend -maxdepth 1 -type d -name "runtime" -exec  chmod 777 {} \; \
    && find /var/www/html/backend/web -maxdepth 1 -type d -name "assets" -exec  chmod 777 {} \; \
    && find /var/www/html/frontend/web -maxdepth 1 -type d -name "assets" -exec  chmod 777 {} \; \
    && find /var/www/html -maxdepth 1 -type f -name "yii" -exec chmod 775 {} \; \
    && find /var/www/html -maxdepth 1 -type f -name "yii_test" -exec chmod 775 {} \;

EXPOSE 9000
