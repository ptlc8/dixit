ARG PHP_VERSION=8
FROM php:${PHP_VERSION}-fpm-alpine

# Use the default production configuration
ARG PHP_ENV=production # can be "development"
RUN mv "$PHP_INI_DIR/php.ini-"${PHP_ENV} "$PHP_INI_DIR/php.ini"

# Install the mysqli extension
RUN docker-php-ext-install mysqli

# Copy the source files
COPY src /var/www/html
WORKDIR /var/www/html

# Start the server
CMD ["php-fpm"]
