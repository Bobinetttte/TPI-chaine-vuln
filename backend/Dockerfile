FROM php:8.2-apache

RUN apt update && apt upgrade -y
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Exposer le port 80 pour Apache
EXPOSE 80
