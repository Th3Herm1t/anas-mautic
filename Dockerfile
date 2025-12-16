FROM mautic/mautic:5-apache

# 1. Copy Custom Plugin (Burns code into image)
# NOTE: Mautic 5 Docker image uses docroot/ subdirectory structure
COPY plugins/MauticAnasBusinessBundle /var/www/html/docroot/plugins/MauticAnasBusinessBundle

# 2. Copy Custom Theme
COPY themes/anas_arabic /var/www/html/docroot/themes/anas_arabic

# 3. Fix Permissions (Crucial for "Permission denied" errors)
# Mautic runs as www-data (ID 33)
RUN chown -R www-data:www-data /var/www/html/docroot/plugins/MauticAnasBusinessBundle \
    && chown -R www-data:www-data /var/www/html/docroot/themes/anas_arabic \
    && chown -R www-data:www-data /var/www/html/var

