FROM php:8.2-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip libpq-dev \
    && apt install -y python3 python3-venv python3-pip build-essential libssl-dev libffi-dev python3-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install intl opcache pdo pdo_pgsql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN apt install -y     \
          chromium         \
          chromium-driver  \
          chromium-sandbox \
          xvfb             \
          x11vnc \
          fluxbox \
          xterm \
          libffi-dev \
          git \
          dbus \
          dbus-x11 \
          ca-certificates

RUN mkdir ~/.vnc
RUN x11vnc -storepasswd 1234 ~/.vnc/passwd

WORKDIR /var/www/project
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

