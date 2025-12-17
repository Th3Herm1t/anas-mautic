FROM mautic/mautic:5-apache

# =============================================================================
# AnasArabic Custom Mautic Image
# 
# Strategy: Store custom code in /opt/mautic-custom/ (staging directory)
# The entrypoint script syncs this to the volume on every container start,
# ensuring code updates are applied without re-installing Mautic.
# =============================================================================

# Install rsync for file syncing
RUN apt-get update && apt-get install -y rsync && rm -rf /var/lib/apt/lists/*

# Create staging directory for custom code
RUN mkdir -p /opt/mautic-custom/plugins /opt/mautic-custom/themes

# Copy custom plugin to staging directory
COPY plugins/MauticAnasBusinessBundle /opt/mautic-custom/plugins/MauticAnasBusinessBundle

# Copy custom theme to staging directory
COPY themes/anas_arabic /opt/mautic-custom/themes/anas_arabic

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/mautic-entrypoint.sh
RUN chmod +x /usr/local/bin/mautic-entrypoint.sh

# Set the custom entrypoint (chains to original)
ENTRYPOINT ["/usr/local/bin/mautic-entrypoint.sh"]

# Default command (Apache)
CMD ["apache2-foreground"]
