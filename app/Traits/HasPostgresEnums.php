<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

trait HasPostgresEnums
{
    /**
     * Get all possible values for a PostgreSQL enum column
     */
    public static function getEnumValues(string $column): array
    {
        $table = (new static)->getTable();
        $columnType = DB::select('
            SELECT udt_name 
            FROM information_schema.columns 
            WHERE table_name = ? AND column_name = ?
        ', [$table, $column]);

        if (empty($columnType)) {
            throw new InvalidArgumentException("Column {$column} not found in table {$table}");
        }

        $enumType = $columnType[0]->udt_name;

        $values = DB::select('
            SELECT enumlabel 
            FROM pg_enum 
            WHERE enumtypid = (SELECT oid FROM pg_type WHERE typname = ?)
            ORDER BY enumsortorder
        ', [$enumType]);

        return array_column($values, 'enumlabel');
    }

    /**
     * Check if a value is valid for the enum column
     */
    public static function isValidEnumValue(string $column, string $value): bool
    {
        return in_array($value, static::getEnumValues($column), true);
    }

    /**
     * Get a random enum value for a column
     */
    public static function getRandomEnumValue(string $column): string
    {
        $values = static::getEnumValues($column);

        return $values[array_rand($values)];
    }

    /**
     * Scope to filter by enum value
     */
    public function scopeWhereEnum(Builder $query, string $column, string $value): Builder
    {
        return $query->where($column, $value);
    }

    /**
     * Scope to filter by multiple enum values
     */
    public function scopeWhereEnumIn(Builder $query, string $column, array $values): Builder
    {
        return $query->whereIn($column, $values);
    }
}
