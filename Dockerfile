# Student Management System - PHP 8.3 + Apache
FROM php:8.3-apache

# PDO PostgreSQL driver used by includes/bootstrap.php
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Production PHP settings, then the app's own overrides
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
# zz- prefix: must load after Debian's security.conf, which would otherwise reset ServerTokens
COPY docker/apache.conf /etc/apache2/conf-available/zz-app.conf
RUN a2enmod headers && a2enconf zz-app

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# The admin login page queries the database, so this also covers the DB link
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS http://localhost/admin/ > /dev/null || exit 1

EXPOSE 80
