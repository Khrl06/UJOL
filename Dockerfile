FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80

CMD sh -c 'PORT=${PORT:-80}; sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf; sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf; apache2-foreground'
