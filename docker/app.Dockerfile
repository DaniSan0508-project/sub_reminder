# Use uma imagem base oficial do PHP 8.3-FPM
FROM php:8.3-fpm-alpine

# Instale as dependências do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    mariadb-client \
    libpng-dev \
    libzip-dev \
    jpeg-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    autoconf \
    build-base

# Instale as extensões do PHP necessárias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Instale o PECL Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Instale o Composer (globalmente)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Defina o diretório de trabalho
WORKDIR /var/www

# Copie o código da aplicação (que está no diretório pai)
COPY . /var/www

# Ajuste as permissões
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Exponha a porta do FPM
EXPOSE 9000
CMD ["php-fpm"]