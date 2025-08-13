FROM dunglas/frankenphp:latest-php8.2-alpine

# Install only necessary extensions for development
RUN install-php-extensions \
    pdo_mysql \
    gd \
    bcmath \
    redis \
    zip \
    intl \
    pcntl \
    exif \
    imap

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# Create start script
COPY docker/start-dev-server.sh /usr/local/bin/start-dev-server
RUN chmod +x /usr/local/bin/start-dev-server

EXPOSE 8181
CMD ["/bin/sh", "/usr/local/bin/start-dev-server"]
