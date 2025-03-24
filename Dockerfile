# Use an official PHP image with Apache
FROM php:8.2-apache

# Copy project files to the web root
COPY . /var/www/html/

# Expose port 80 (needed for Vercel)
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
