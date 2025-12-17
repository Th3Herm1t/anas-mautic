# MauticAnasBusinessBundle

Custom Mautic 5 plugin for AnasArabic.com marketing automation.

## Overview

This plugin programmatically creates and syncs marketing automation assets:
- **Custom Fields**: Student Age, Institution Name, Level Result, Trial dates
- **Segments**: B2C Parents, B2B Institutions, Trial Active, Trial Expired, Signup Incomplete
- **Emails**: 8 lifecycle templates (Emails A-H)
- **Campaigns**: Signup Flow, Trial Nurture, Recovery logic

## Architecture

```
MauticAnasBusinessBundle/
├── Command/
│   └── SyncBusinessLogicCommand.php    # mautic:business:sync command
├── Config/
│   ├── config.php                      # Plugin metadata
│   └── services.php                    # DI service configuration
├── DataFixtures/ORM/
│   ├── LoadCustomFieldData.php         # Order 1: Custom lead fields
│   ├── LoadSegmentData.php             # Order 2: Lead segments
│   ├── LoadEmailData.php               # Order 3: Email templates
│   └── LoadCampaignData.php            # Order 4: Campaign workflows
├── DependencyInjection/
│   └── MauticAnasBusinessExtension.php # Symfony DI extension
├── MauticAnasBusinessBundle.php        # Bundle class
└── composer.json
```

## Key Technical Details

### Mautic 5 Compatibility

This plugin uses Mautic 5's modern Symfony-based architecture:

| Component | Implementation |
|-----------|----------------|
| Bundle Base | `PluginBundleBase` (not `AbstractPluginBundle`) |
| Extension | Extends `Symfony\Component\DependencyInjection\Extension\Extension` |
| Services | PHP configurator (`services.php`), not YAML |
| Commands | `#[AsCommand]` attribute (Symfony 5.3+) |
| Fixtures | `FixtureGroupInterface` with group `anas_business` |

### Docker Deployment (Coolify)

**Critical**: Mautic 5 Docker image uses a `docroot/` subdirectory structure.

```dockerfile
# CORRECT paths:
COPY plugins/MauticAnasBusinessBundle /var/www/html/docroot/plugins/MauticAnasBusinessBundle
COPY themes/anas_arabic /var/www/html/docroot/themes/anas_arabic

# WRONG (will not work):
# COPY plugins/... /var/www/html/plugins/...
```

### Entity Field Notes

- **Email**: No `alias` field. Use `name` for lookups.
- **LeadList (Segment)**: Has `alias` field. Safe to use.
- **Campaign**: Has `alias` field. Safe to use.

## Usage

### Sync Command

```bash
# Deploy and sync all business logic
php bin/console mautic:business:sync

# With data purge (caution: destructive)
php bin/console mautic:business:sync --purge
```

### Deployment Sequence

```bash
# 1. Fix permissions
chown -R www-data:www-data /var/www/html

# 2. Regenerate autoloader
composer dump-autoload

# 3. Clear cache
rm -rf /var/www/html/var/cache/*

# 4. Register plugin
php bin/console mautic:plugins:reload

# 5. Sync business logic
php bin/console mautic:business:sync
```

## Troubleshooting

### "Command not found"
- Verify plugin is in `/docroot/plugins/` not `/plugins/`
- Check Extension class naming matches bundle name
- Ensure `services.php` uses PHP configurator format

### "Class not found"
- Run `composer dump-autoload`
- Verify namespace matches directory structure

### Fixture errors
- Check entity field names match Mautic's actual schema
- Email entity has no `alias` - use `name` instead

## Dependencies

- Mautic 5.x
- PHP 8.2+
- Doctrine Fixtures Bundle

## Maintenance

When updating fixtures:
1. Check Mautic entity schemas for field changes
2. Test locally before deploying
3. Use `--append` flag (default) to avoid data loss
