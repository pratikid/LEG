# GEDCOM Date Cleaning and Standardization

## Overview

The LEG application automatically cleans and standardizes GEDCOM date formats during import processing. This ensures consistent date handling across different genealogy software and improves data quality.

## Date Format Handling

### 1. Year-Only Dates (yyyy format)

**Problem**: Many GEDCOM files contain dates with only the year (e.g., "1980", "1850").

**Solution**: The system validates and standardizes year-only dates:

```gedcom
# Original
2 DATE 1980

# Cleaned (if valid year)
2 DATE 1980

# Original (with approximate prefix)
2 DATE ABT 1850

# Cleaned
2 DATE ABT 1850
```

**Validation Rules**:
- Year must be between 1000 and current year + 10
- Invalid years are logged and left unchanged
- Approximate prefixes (ABT, EST, etc.) are preserved

### 2. Year Ranges (yyyy-yyyy format)

**Handles**: Date ranges like "1980-1990" or "ABT 1850-1860"

```gedcom
# Original
2 DATE 1980-1990

# Cleaned
2 DATE 1980-1990

# Original with approximate
2 DATE ABT 1850-1860

# Cleaned
2 DATE ABT 1850-1860
```

### 3. Between Dates (BET format)

**Handles**: "BET 1980 AND 1990" format

```gedcom
# Original
2 DATE BET 1980 AND 1990

# Cleaned
2 DATE BET 1980 AND 1990
```

### 4. Full Dates (DD MMM YYYY format)

**Standardizes**: Month abbreviations and full month names

```gedcom
# Original
2 DATE 15 Jan 1980
2 DATE 20 February 1850

# Cleaned
2 DATE 15 JAN 1980
2 DATE 20 FEB 1850
```

**Supported Month Formats**:
- Abbreviations: Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, Oct, Nov, Dec
- Full names: January, February, March, April, May, June, July, August, September, October, November, December

### 5. Before/After Dates (BEF/AFT format)

**Handles**: "BEF 1980" or "AFT 1850" with recursive cleaning

```gedcom
# Original
2 DATE BEF 1980
2 DATE AFT ABT 1850

# Cleaned
2 DATE BEF 1980
2 DATE AFT ABT 1850
```

### 6. Approximate Dates

**Supported Prefixes**:
- ABT (About)
- EST (Estimated)
- CAL (Calculated)
- CIR/CIRC (Circa)
- ABOUT
- APPROX/APPROXIMATELY

```gedcom
# Original
2 DATE ABOUT 1850
2 DATE APPROXIMATELY 1980

# Cleaned
2 DATE ABT 1850
2 DATE APPROX 1980
```

### 7. Unknown Dates

**Standardizes**: Various unknown date formats

```gedcom
# Original
2 DATE UNKNOWN
2 DATE UNK
2 DATE ?

# Cleaned
2 DATE UNKNOWN
```

## Implementation Details

### Date Cleaning Process

1. **Parse GEDCOM Line**: Extract level, tag, and value
2. **Identify DATE Tags**: Only process DATE tag lines
3. **Apply Cleaning Rules**: Use regex patterns to match date formats
4. **Validate Dates**: Check year ranges and logical consistency
5. **Standardize Format**: Apply consistent formatting
6. **Log Changes**: Record all modifications for audit trail

### Validation Rules

```php
// Year validation
if ($year >= 1000 && $year <= date('Y') + 10) {
    // Valid year
} else {
    // Invalid year - log warning and keep original
}

// Day validation for full dates
if ($day >= 1 && $day <= 31) {
    // Valid day
}

// Range validation
if ($startYear <= $endYear) {
    // Valid range
}
```

### Logging and Monitoring

The system logs all date cleaning activities:

```php
Log::info('Standardized year-only date', [
    'original' => '1980',
    'cleaned' => '1980',
    'type' => 'year_only'
]);

Log::warning('Invalid year in date', [
    'original' => '9999',
    'year' => 9999,
    'type' => 'invalid_year'
]);
```

## Benefits

### 1. **Data Consistency**
- Standardized date formats across all imports
- Consistent month abbreviations (JAN, FEB, etc.)
- Proper handling of approximate dates

### 2. **Data Quality**
- Validation of year ranges (1000-2030)
- Detection of invalid dates
- Preservation of meaningful date qualifiers

### 3. **Compatibility**
- Handles dates from various genealogy software
- Supports both modern and historical date formats
- Maintains GEDCOM 7.0 compliance

### 4. **Audit Trail**
- Complete logging of all date changes
- Original and cleaned values preserved
- Debugging information for troubleshooting

## Examples

### Complex Date Scenarios

```gedcom
# Original GEDCOM
0 @I1@ INDI
1 NAME John /Doe/
1 BIRT
2 DATE 15 Jan 1980
1 DEAT
2 DATE ABT 2020
1 MARR
2 DATE BET 2000 AND 2005

# Cleaned GEDCOM
0 @I1@ INDI
1 NAME John /Doe/
1 BIRT
2 DATE 15 JAN 1980
1 DEAT
2 DATE ABT 2020
1 MARR
2 DATE BET 2000 AND 2005
```

### Year-Only Date Handling

```gedcom
# Original
2 DATE 1850
2 DATE ABT 1900
2 DATE EST 1800

# Cleaned (all valid)
2 DATE 1850
2 DATE ABT 1900
2 DATE EST 1800

# Invalid year (logged as warning)
2 DATE 9999  # Kept as original
```

## Configuration

### Customization Options

You can modify date cleaning behavior by editing the service classes:

- `GedcomService::cleanGedcomDate()`
- `GedcomMultiDatabaseService::cleanGedcomDate()`

### Year Range Validation

```php
// Modify year range in service classes
if ($year >= 1000 && $year <= date('Y') + 10) {
    // Current range: 1000 to current year + 10
}
```

### Month Standardization

```php
// Add custom month mappings
$monthMap = [
    'Jan' => 'JAN',
    'Feb' => 'FEB',
    // ... add more mappings
];
```

## Troubleshooting

### Common Issues

1. **Invalid Years**: Check logs for "invalid_year" warnings
2. **Unrecognized Formats**: Unmatched dates are preserved as original
3. **Performance**: Large files may take longer due to date processing

### Debugging

Enable detailed logging to see all date cleaning activities:

```php
// In config/logging.php
'level' => 'debug',
```

### Manual Testing

Test date cleaning with sample data:

```php
$service = new GedcomService();
$cleaned = $service->cleanGedcomDate('1980');
echo $cleaned; // Should output: 1980
```

## Best Practices

1. **Review Logs**: Regularly check date cleaning logs for patterns
2. **Validate Results**: Compare original and cleaned files
3. **Monitor Performance**: Large imports may take longer with date cleaning
4. **Backup Data**: Always backup original files before processing

## Future Enhancements

Potential improvements for date cleaning:

1. **Calendar System Support**: Handle different calendar systems (Julian, Gregorian)
2. **Localization**: Support for non-English month names
3. **Advanced Validation**: More sophisticated date logic validation
4. **Custom Rules**: User-configurable date cleaning rules 