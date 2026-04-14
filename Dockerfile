FROM php:8.2-apache

# Install required PHP extensions for MySQL connect and PDO
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite for URL routing if needed
RUN a2enmod rewrite

# Copy local code to the web server's document root
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port (Render automatically assigns one, but 80 is standard for docker containers)
EXPOSE 80
