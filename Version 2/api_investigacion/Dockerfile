FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

# Configurar Apache para que escuche en el puerto 8026 de la API
RUN sed -i 's/80/8026/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf