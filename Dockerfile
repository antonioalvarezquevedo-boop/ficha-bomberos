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
CMD sh -c 'php -S 0.0.0.0:${PORT:-3000}'
