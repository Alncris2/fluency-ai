# PHP base image
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# ------------------------------------------------------------
# Dependências de sistema
# ------------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git curl gnupg lsb-release \
    libonig-dev libzip-dev libpng-dev libxml2-dev libwebp-dev libjpeg-dev \
    libxrender1 libfontconfig1 libx11-dev libxtst6 \
    wget zip unzip supervisor procps \
    libmagickwand-dev imagemagick \
    && wget https://github.com/h4cc/wkhtmltopdf-amd64/blob/master/bin/wkhtmltopdf-amd64?raw=true -O /usr/local/bin/wkhtmltopdf \
    && chmod +x /usr/local/bin/wkhtmltopdf

# ------------------------------------------------------------
# Clear cache
# ------------------------------------------------------------
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# ------------------------------------------------------------
# PHP extensions
# ------------------------------------------------------------
RUN docker-php-ext-configure gd --with-webp --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl soap xml zip sockets \
    && docker-php-ext-configure intl

# Redis only (removed Swoole/Octane for development)
RUN pecl install redis \
    && docker-php-ext-enable redis

# Xdebug (desabilitado por padrão)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && mv /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/disabled-xdebug.ini

# ------------------------------------------------------------
# Composer
# ------------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ------------------------------------------------------------
# Node.js (para Vite build/dev)
# ------------------------------------------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs

# ------------------------------------------------------------
# Usuário não-root para rodar a app
# ------------------------------------------------------------
RUN useradd -ms /bin/bash laravel \
    && mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R laravel:laravel /var/www \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache

RUN chown -R laravel:laravel /tmp /home/laravel \
    && chmod -R 777 /tmp /home/laravel

RUN mkdir -p /home/laravel/.npm && chown -R laravel:laravel /home/laravel/.npm
ENV NPM_CONFIG_CACHE=/home/laravel/.npm

# Add alias for bash,
RUN echo "\
    alias pa='php artisan'\n\
    alias migrate='php artisan migrate'\n\
    alias test='php artisan test'\n\
    alias seed='php artisan db:seed'\n\
    alias tinker='php artisan tinker'\n\
    alias reverb='php artisan reverb:start'\n\
    alias horizon='php artisan horizon'\n\
    alias dev='npm run dev'\n\
    alias ziggy='php artisan ziggy:generate'\n\
    " >> /home/laravel/.bashrc

# Install Starship
RUN curl -sS https://starship.rs/install.sh | sh -s -- --yes

# Add Starship init script to .bashrc
RUN echo 'eval "$(starship init bash)"' >> /home/laravel/.bashrc
RUN mkdir -p /home/laravel/.config && touch /home/laravel/.config/starship.tomla
RUN starship preset no-runtime-versions -o /home/laravel/.config/starship.toml

USER laravel

# ------------------------------------------------------------
# Copiar configs de PHP
# ------------------------------------------------------------
COPY docker/backend/php/php.ini /usr/local/etc/php/php.ini

# ------------------------------------------------------------
# Instalar dependências do Laravel (com dev dependencies)
# ------------------------------------------------------------
COPY --chown=laravel:laravel backend/composer.json backend/composer.lock ./
RUN composer install --optimize-autoloader --no-interaction --no-scripts


# ------------------------------------------------------------
# Instalar dependências do Node (com dev dependencies)
# ------------------------------------------------------------
COPY --chown=laravel:laravel backend/package*.json ./
RUN mkdir -p /var/www/node_modules && chown -R laravel:laravel /var/www/node_modules
RUN npm install --legacy-peer-deps --silent --no-audit

# ------------------------------------------------------------
# Copiar resto da aplicação
# ------------------------------------------------------------
COPY --chown=laravel:laravel backend/. .

# ------------------------------------------------------------
# Garantir permissões finais
# ------------------------------------------------------------
RUN chown -R laravel:laravel /var/www
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache /var/www/public

# ------------------------------------------------------------
# Supervisord para gerenciar PHP-FPM, Horizon e Reverb
# ------------------------------------------------------------
USER root
COPY docker/backend/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/backend/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/backend/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
