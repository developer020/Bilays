# Use an official PHP image with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy project files to the web root
COPY . /var/www/html/

# Set file permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for HTTP traffic
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
