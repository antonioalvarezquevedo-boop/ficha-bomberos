FROM php:8.4-cli

# Install MySQL PDO extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Expose the port that Railway will inject via PORT env var
EXPOSE 3000

# Start PHP built-in server on 0.0.0.0 with PORT env var
CMD ["php", "-S", "0.0.0.0:${PORT:-3000}"]
FROM php:8.4-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install MySQL PDO extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expose port 80 (used by Railway)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
