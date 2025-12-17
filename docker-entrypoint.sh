#!/bin/bash
set -e

# =============================================================================
# Mautic Custom Entrypoint
# Purpose: Sync custom plugins/themes and initialize Mautic on container start
# =============================================================================

echo "=================================================="
echo "AnasArabic Mautic - Custom Entrypoint"
echo "=================================================="

# Paths
MAUTIC_ROOT="/var/www/html"
DOCROOT="${MAUTIC_ROOT}/docroot"
CUSTOM_SOURCE="/opt/mautic-custom"
INIT_MARKER="${MAUTIC_ROOT}/.mautic-initialized"

# -----------------------------------------------------------------------------
# 1. Sync Custom Plugins from Image to Volume
# -----------------------------------------------------------------------------
echo "[1/6] Syncing custom plugins..."

if [ -d "${CUSTOM_SOURCE}/plugins" ]; then
    for plugin_dir in ${CUSTOM_SOURCE}/plugins/*/; do
        plugin_name=$(basename "$plugin_dir")
        echo "  -> Syncing plugin: ${plugin_name}"
        rsync -a --delete "${plugin_dir}" "${DOCROOT}/plugins/${plugin_name}/"
    done
fi

# -----------------------------------------------------------------------------
# 2. Sync Custom Themes from Image to Volume
# -----------------------------------------------------------------------------
echo "[2/6] Syncing custom themes..."

if [ -d "${CUSTOM_SOURCE}/themes" ]; then
    for theme_dir in ${CUSTOM_SOURCE}/themes/*/; do
        theme_name=$(basename "$theme_dir")
        echo "  -> Syncing theme: ${theme_name}"
        rsync -a --delete "${theme_dir}" "${DOCROOT}/themes/${theme_name}/"
    done
fi

# -----------------------------------------------------------------------------
# 3. Fix Permissions
# -----------------------------------------------------------------------------
echo "[3/6] Fixing permissions..."

chown -R www-data:www-data "${DOCROOT}/plugins" 2>/dev/null || true
chown -R www-data:www-data "${DOCROOT}/themes" 2>/dev/null || true
chown -R www-data:www-data "${MAUTIC_ROOT}/var" 2>/dev/null || true

# -----------------------------------------------------------------------------
# 4. Composer Autoload (if vendor exists)
# -----------------------------------------------------------------------------
echo "[4/6] Regenerating autoloader..."

if [ -f "${MAUTIC_ROOT}/composer.json" ]; then
    cd "${MAUTIC_ROOT}"
    composer dump-autoload --no-interaction --quiet 2>/dev/null || echo "  -> Skipped (composer not ready)"
fi

# -----------------------------------------------------------------------------
# 5. Clear Cache
# -----------------------------------------------------------------------------
echo "[5/6] Clearing cache..."

rm -rf "${MAUTIC_ROOT}/var/cache/"* 2>/dev/null || true

# -----------------------------------------------------------------------------
# 6. Post-Deploy Commands (only after Mautic is installed)
# -----------------------------------------------------------------------------
echo "[6/6] Running post-deploy commands..."

# Check if Mautic is installed (local.php exists with DB config)
if [ -f "${DOCROOT}/config/local.php" ]; then
    echo "  -> Mautic installation detected"
    
    cd "${MAUTIC_ROOT}"
    
    # Reload plugins
    echo "  -> Reloading plugins..."
    php bin/console mautic:plugins:reload --no-interaction 2>/dev/null || echo "  -> Plugin reload skipped"
    
    # Warm up cache
    echo "  -> Warming cache..."
    php bin/console cache:warmup --no-interaction 2>/dev/null || true
    
    # Run business sync ONCE (after first installation)
    if [ ! -f "${INIT_MARKER}" ]; then
        echo "  -> First boot detected - running business fixtures..."
        php bin/console mautic:business:sync --no-interaction 2>/dev/null || echo "  -> Business sync skipped (run manually if needed)"
    fi
    
    # Mark as initialized
    touch "${INIT_MARKER}"
else
    echo "  -> Mautic not yet installed (waiting for GUI installation)"
    echo "  -> After installation, restart container to complete setup"
fi

echo "=================================================="
echo "Entrypoint complete. Starting Apache..."
echo "=================================================="

# Execute the original entrypoint or start Apache
exec "$@"
