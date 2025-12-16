# Use official PHP image with required extensions
FROM php:8.4-cli

# Set working directory
WORKDIR /var/www

# Install system dependencies for Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    && docker-php-ext-install zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the Laravel app (optional if you mount via volume)
# COPY . .

# Expose port for php artisan serve
EXPOSE 8000

# Default command
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
