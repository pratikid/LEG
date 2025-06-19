# PostgreSQL Enums in Laravel

This document explains how to properly work with PostgreSQL ENUM types in Laravel applications.

## Overview

PostgreSQL has native support for ENUM types, which are more efficient and type-safe than check constraints. Unlike MySQL, Laravel's `enum()` method in PostgreSQL creates a VARCHAR with a check constraint rather than a true ENUM type.

## Methods for PostgreSQL Enums

### Method 1: Native PostgreSQL ENUM (Recommended)

This is the most efficient approach as it uses PostgreSQL's native ENUM type.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create the PostgreSQL ENUM type first
        DB::statement("CREATE TYPE sex_enum AS ENUM ('M', 'F', 'U')");
        
        Schema::create('individuals', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('sex')->nullable(); // We'll convert this to enum
            $table->timestamps();
        });
        
        // Convert the string column to use the ENUM type
        DB::statement('ALTER TABLE individuals ALTER COLUMN sex TYPE sex_enum USING sex::sex_enum');
    }

    public function down(): void
    {
        Schema::dropIfExists('individuals');
        DB::statement('DROP TYPE IF EXISTS sex_enum');
    }
};
```

### Method 2: Check Constraints (Laravel's enum() method)

Laravel's `enum()` method creates a VARCHAR with a check constraint in PostgreSQL:

```php
$table->enum('status', ['active', 'inactive', 'pending'])->nullable();
```

This creates:
```sql
ALTER TABLE table_name ADD CONSTRAINT check_status CHECK (status IN ('active', 'inactive', 'pending'))
```

### Method 3: Manual Check Constraints

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('category')->nullable();
    $table->timestamps();
});

// Add check constraint
DB::statement("ALTER TABLE posts ADD CONSTRAINT check_category CHECK (category IN ('news', 'article', 'tutorial'))");
```

## Using the HasPostgresEnums Trait

We've created a trait that provides helper methods for working with PostgreSQL enums:

```php
use App\Traits\HasPostgresEnums;

class Individual extends Model
{
    use HasPostgresEnums;
    
    // Sex constants for better code readability
    public const SEX_MALE = 'M';
    public const SEX_FEMALE = 'F';
    public const SEX_UNKNOWN = 'U';
    
    // Get all possible enum values
    public static function getSexValues(): array
    {
        return static::getEnumValues('sex');
    }
    
    // Scope to filter by enum value
    public function scopeWhereSex($query, string $sex)
    {
        return $query->whereEnum('sex', $sex);
    }
    
    // Check if value is valid
    public static function isValidSex(string $sex): bool
    {
        return static::isValidEnumValue('sex', $sex);
    }
}
```

## Available Trait Methods

### `getEnumValues(string $column): array`
Returns all possible values for a PostgreSQL enum column.

### `scopeWhereEnum(Builder $query, string $column, string $value): Builder`
Scope to filter by a specific enum value.

### `scopeWhereEnumIn(Builder $query, string $column, array $values): Builder`
Scope to filter by multiple enum values.

### `isValidEnumValue(string $column, string $value): bool`
Check if a value is valid for the enum column.

### `getRandomEnumValue(string $column): string`
Get a random enum value for a column.

## Individual Model Helper Methods

The Individual model includes additional helper methods for working with sex values:

### Constants
- `Individual::SEX_MALE` - 'M'
- `Individual::SEX_FEMALE` - 'F'
- `Individual::SEX_UNKNOWN` - 'U'

### Scopes
- `whereMale()` - Filter males only
- `whereFemale()` - Filter females only
- `whereUnknownSex()` - Filter unknown sex only
- `whereKnownSex()` - Filter known sex (male or female, excluding unknown)

### Instance Methods
- `isMale()` - Check if individual is male
- `isFemale()` - Check if individual is female
- `isUnknownSex()` - Check if individual's sex is unknown
- `hasKnownSex()` - Check if individual has known sex (male or female)
- `getSexLabel()` - Get human-readable sex label

## Usage Examples

```php
// Get all possible sex values
$sexValues = Individual::getSexValues(); // ['M', 'F', 'U']

// Filter individuals by sex
$males = Individual::whereMale()->get();
$females = Individual::whereFemale()->get();
$unknown = Individual::whereUnknownSex()->get();
$known = Individual::whereKnownSex()->get();

// Filter by multiple sex values
$adults = Individual::whereSexIn(['M', 'F'])->get();

// Validate sex value
if (Individual::isValidSex('M')) {
    // Valid value
}

// Get random sex value
$randomSex = Individual::getRandomEnumValue('sex');

// Instance methods
$individual = Individual::find(1);
if ($individual->isMale()) {
    echo "This is a male individual";
}

if ($individual->hasKnownSex()) {
    echo "Sex is known: " . $individual->getSexLabel();
}

// Using constants
$individual->sex = Individual::SEX_UNKNOWN;
$individual->save();
```

## Adding New Enum Values

To add new values to an existing enum:

```php
// Add new value to existing enum
DB::statement("ALTER TYPE sex_enum ADD VALUE 'X' AFTER 'U'");
```

## Migration Best Practices

1. **Always create ENUM types before tables** to avoid dependency issues
2. **Use descriptive enum type names** (e.g., `sex_enum`, `status_enum`)
3. **Handle rollbacks properly** by dropping ENUM types in the `down()` method
4. **Use the HasPostgresEnums trait** for consistent enum handling across models
5. **Validate enum values** in your application logic
6. **Consider GEDCOM standards** when designing enums (e.g., sex values: M, F, U)
7. **Use constants** for better code readability and maintainability

## Performance Considerations

- **Native ENUMs are more efficient** than check constraints
- **ENUMs use less storage** than VARCHAR with check constraints
- **Indexes on ENUM columns** are more efficient
- **Query performance** is better with native ENUMs

## Validation

Always validate enum values in your application:

```php
// In form requests
public function rules(): array
{
    return [
        'sex' => ['nullable', 'string', 'in:' . implode(',', Individual::getSexValues())],
    ];
}

// In controllers
if (!Individual::isValidSex($request->sex)) {
    return back()->withErrors(['sex' => 'Invalid sex value']);
}

// Using constants in validation
'sex' => ['nullable', 'string', 'in:' . implode(',', [
    Individual::SEX_MALE,
    Individual::SEX_FEMALE,
    Individual::SEX_UNKNOWN,
])],
```

## Testing

```php
public function test_enum_values_are_correct()
{
    $expectedValues = ['M', 'F', 'U'];
    $actualValues = Individual::getSexValues();
    
    $this->assertEquals($expectedValues, $actualValues);
}

public function test_invalid_enum_value_is_rejected()
{
    $this->assertFalse(Individual::isValidSex('INVALID'));
    $this->assertTrue(Individual::isValidSex('M'));
    $this->assertTrue(Individual::isValidSex('U'));
}

public function test_sex_constants_are_defined()
{
    $this->assertEquals('M', Individual::SEX_MALE);
    $this->assertEquals('F', Individual::SEX_FEMALE);
    $this->assertEquals('U', Individual::SEX_UNKNOWN);
}
``` 