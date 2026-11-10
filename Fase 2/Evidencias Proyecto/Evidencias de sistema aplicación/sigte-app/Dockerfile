FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
	git unzip libpq-dev libzip-dev \
	libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install pdo pdo_pgsql zip gd \
	&& a2enmod rewrite \
	&& rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

COPY . .

RUN mkdir -p \
      storage/framework/sessions \
      storage/framework/views \
      storage/framework/cache/data \
      storage/logs \
      bootstrap/cache \
    && if [ -f composer.json ]; then composer install --no-interaction --prefer-dist --optimize-autoloader; fi \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/performance.ini /usr/local/etc/php/conf.d/zz-performance.ini

EXPOSE 80
