# GEDCOM Cleaned Files Storage

## Overview

The LEG application automatically removes user-defined tags from GEDCOM files before processing and stores the cleaned content for debugging and audit purposes.

## File Storage Structure

Cleaned GEDCOM files are stored in the following directory structure:

```
storage/app/gedcom/cleaned/
├── tree_1/
│   ├── tree_1_cleaned_gedcom_2024-01-15_14-30-25.ged
│   └── tree_1_cleaned_gedcom_2024-01-16_09-15-42.ged
├── tree_2/
│   └── tree_2_cleaned_gedcom_2024-01-17_16-45-33.ged
└── cleaned_gedcom_2024-01-18_11-20-15.ged
```

## File Naming Convention

- **Tree-specific files**: `tree_{tree_id}_cleaned_gedcom_{timestamp}.ged`
- **General files**: `cleaned_gedcom_{timestamp}.ged`
- **Timestamp format**: `YYYY-MM-DD_HH-mm-ss`

## User-Defined Tag Removal

### What Gets Removed

The system automatically removes:
- Tags starting with underscore (`_`) - e.g., `_CUSTOM`, `_USER_TAG`
- All sub-tags of user-defined tags
- Vendor-specific extensions that don't follow GEDCOM standards

### Example

**Original GEDCOM:**
```gedcom
0 @I1@ INDI
1 NAME John /Doe/
1 _CUSTOM_TAG Some custom data
2 _CUSTOM_SUBTAG More custom data
1 BIRT
2 DATE 1980-01-15
```

**Cleaned GEDCOM:**
```gedcom
0 @I1@ INDI
1 NAME John /Doe/
1 BIRT
2 DATE 1980-01-15
```

## Benefits

1. **Debugging**: Compare original and cleaned files to understand what was removed
2. **Audit Trail**: Keep a record of processed files
3. **Compatibility**: Ensure only standard GEDCOM tags are processed
4. **Performance**: Reduce processing time by removing non-standard data

## Management Commands

### Cleanup Old Files

Remove cleaned files older than a specified number of days:

```bash
# Remove files older than 30 days (default)
php artisan gedcom:cleanup

# Remove files older than 7 days
php artisan gedcom:cleanup --days=7

# Dry run to see what would be deleted
php artisan gedcom:cleanup --dry-run
```

### Manual Cleanup

You can also manually manage files:

```bash
# List all cleaned files
find storage/app/gedcom/cleaned -name "*.ged" -type f

# Remove files for a specific tree
rm -rf storage/app/gedcom/cleaned/tree_1/

# Remove all cleaned files
rm -rf storage/app/gedcom/cleaned/
```

## Configuration

### Storage Location

The cleaned files are stored in `storage/app/gedcom/cleaned/` by default. You can modify this in the service classes:

- `GedcomService::storeCleanedGedcomContent()`
- `GedcomMultiDatabaseService::storeCleanedGedcomContent()`

### Retention Policy

By default, cleaned files are kept for 30 days. You can adjust this by:

1. Running the cleanup command with different `--days` parameter
2. Setting up a scheduled task in `app/Console/Kernel.php`:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Clean up old GEDCOM files daily
    $schedule->command('gedcom:cleanup --days=30')->daily();
}
```

## Monitoring

### Log Entries

The system logs information about cleaned files:

```php
Log::info('Stored cleaned GEDCOM content', [
    'cleaned_file_path' => '/path/to/cleaned/file.ged',
    'original_size' => 1024,
    'cleaned_size' => 896
]);
```

### Notification Data

The `GedcomImportCompleted` notification includes the cleaned file path:

```php
[
    'cleaned_file_path' => '/path/to/cleaned/file.ged',
    'user_defined_tags_removed' => true
]
```

## Troubleshooting

### File Permission Issues

If you encounter permission issues:

```bash
# Ensure proper permissions
chmod -R 755 storage/app/gedcom/
chown -R www-data:www-data storage/app/gedcom/
```

### Disk Space

Monitor disk space usage:

```bash
# Check disk usage
du -sh storage/app/gedcom/cleaned/

# Find largest files
find storage/app/gedcom/cleaned -name "*.ged" -exec ls -lh {} \; | sort -k5 -hr
```

### Performance Impact

Large numbers of cleaned files can impact performance. Consider:

1. More aggressive cleanup schedules
2. Compressing old files
3. Moving to external storage for long-term retention

## Security Considerations

- Cleaned files may contain sensitive genealogical data
- Ensure proper file permissions
- Consider encryption for long-term storage
- Implement access controls if needed 