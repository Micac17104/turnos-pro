FROM php:8.2-cli

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . /app

CMD ["php", "-S", "0.0.0.0:80", "-t", "."]
