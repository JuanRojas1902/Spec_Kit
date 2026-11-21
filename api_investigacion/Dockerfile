FROM php:8.3-apache
# Instalamos la extensión pdo_mysql para que el repositorio pueda conectarse a MariaDB
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite