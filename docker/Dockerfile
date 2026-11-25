FROM php:8.3-apache

# Установка расширений для работы с MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Включение модуля маршрутизации Apache
RUN a2enmod rewrite

# Настройка публичной директории приложения
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

# Разрешение использования .htaccess
RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/gamevote.conf \
    && a2enconf gamevote

# Рабочая директория приложения
WORKDIR /var/www/html