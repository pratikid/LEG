<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trait for handling database operations
 */
trait HandlesDatabaseOperations
{
    /**
     * Begin a database transaction.
     *
     * @param string|null $connection The database connection
     * @return void
     */
    protected function beginTransaction(?string $connection = null): void
    {
        DB::connection($connection)->beginTransaction();
    }
    
    /**
     * Commit a database transaction.
     *
     * @param string|null $connection The database connection
     * @return void
     */
    protected function commitTransaction(?string $connection = null): void
    {
        DB::connection($connection)->commit();
    }
    
    /**
     * Rollback a database transaction.
     *
     * @param string|null $connection The database connection
     * @return void
     */
    protected function rollbackTransaction(?string $connection = null): void
    {
        DB::connection($connection)->rollBack();
    }
    
    /**
     * Execute a callback within a transaction.
     *
     * @param callable $callback The callback
     * @param string|null $connection The database connection
     * @return mixed The callback result
     * @throws \Throwable If an error occurs
     */
    protected function transaction(callable $callback, ?string $connection = null)
    {
        return DB::connection($connection)->transaction($callback);
    }
    
    /**
     * Get the transaction level.
     *
     * @param string|null $connection The database connection
     * @return int The transaction level
     */
    protected function getTransactionLevel(?string $connection = null): int
    {
        return DB::connection($connection)->transactionLevel();
    }
    
    /**
     * Execute a raw SQL query.
     *
     * @param string $query The SQL query
     * @param array $bindings The query bindings
     * @param string|null $connection The database connection
     * @return array The query result
     */
    protected function rawQuery(string $query, array $bindings = [], ?string $connection = null): array
    {
        return DB::connection($connection)->select($query, $bindings);
    }
    
    /**
     * Execute a raw SQL statement.
     *
     * @param string $query The SQL statement
     * @param array $bindings The statement bindings
     * @param string|null $connection The database connection
     * @return bool True if the statement was successful, false otherwise
     */
    protected function rawStatement(string $query, array $bindings = [], ?string $connection = null): bool
    {
        return DB::connection($connection)->statement($query, $bindings);
    }
    
    /**
     * Execute a raw SQL insert statement.
     *
     * @param string $query The SQL insert statement
     * @param array $bindings The statement bindings
     * @param string|null $connection The database connection
     * @return bool True if the statement was successful, false otherwise
     */
    protected function rawInsert(string $query, array $bindings = [], ?string $connection = null): bool
    {
        return DB::connection($connection)->insert($query, $bindings);
    }
    
    /**
     * Execute a raw SQL update statement.
     *
     * @param string $query The SQL update statement
     * @param array $bindings The statement bindings
     * @param string|null $connection The database connection
     * @return int The number of affected rows
     */
    protected function rawUpdate(string $query, array $bindings = [], ?string $connection = null): int
    {
        return DB::connection($connection)->update($query, $bindings);
    }
    
    /**
     * Execute a raw SQL delete statement.
     *
     * @param string $query The SQL delete statement
     * @param array $bindings The statement bindings
     * @param string|null $connection The database connection
     * @return int The number of affected rows
     */
    protected function rawDelete(string $query, array $bindings = [], ?string $connection = null): int
    {
        return DB::connection($connection)->delete($query, $bindings);
    }
    
    /**
     * Get a new query builder instance.
     *
     * @param string|null $connection The database connection
     * @return \Illuminate\Database\Query\Builder The query builder
     */
    protected function getQueryBuilder(?string $connection = null): QueryBuilder
    {
        return DB::connection($connection)->table('');
    }
    
    /**
     * Get a query builder for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The database connection
     * @return \Illuminate\Database\Query\Builder The query builder
     */
    protected function table(string $table, ?string $connection = null): QueryBuilder
    {
        return DB::connection($connection)->table($table);
    }
    
    /**
     * Get the database connection.
     *
     * @param string|null $name The connection name
     * @return \Illuminate\Database\Connection The database connection
     */
    protected function getConnection(?string $name = null)
    {
        return DB::connection($name);
    }
    
    /**
     * Get the name of the default database connection.
     *
     * @return string The default connection name
     */
    protected function getDefaultConnectionName(): string
    {
        return DB::getDefaultConnection();
    }
    
    /**
     * Set the default database connection.
     *
     * @param string $name The connection name
     * @return void
     */
    protected function setDefaultConnection(string $name): void
    {
        DB::setDefaultConnection($name);
    }
    
    /**
     * Get all of the available database connections.
     *
     * @return array The available connections
     */
    protected function getConnections(): array
    {
        return config('database.connections');
    }
    
    /**
     * Determine if a database connection exists.
     *
     * @param string $name The connection name
     * @return bool True if the connection exists, false otherwise
     */
    protected function hasConnection(string $name): bool
    {
        return array_key_exists($name, $this->getConnections());
    }
    
    /**
     * Get the database driver for a connection.
     *
     * @param string|null $connection The connection name
     * @return string The database driver
     */
    protected function getDatabaseDriver(?string $connection = null): string
    {
        return DB::connection($connection)->getDriverName();
    }
    
    /**
     * Get the database name for a connection.
     *
     * @param string|null $connection The connection name
     * @return string The database name
     */
    protected function getDatabaseName(?string $connection = null): string
    {
        return DB::connection($connection)->getDatabaseName();
    }
    
    /**
     * Get the table prefix for a connection.
     *
     * @param string|null $connection The connection name
     * @return string The table prefix
     */
    protected function getTablePrefix(?string $connection = null): string
    {
        return DB::connection($connection)->getTablePrefix();
    }
    
    /**
     * Get all of the tables for a connection.
     *
     * @param string|null $connection The connection name
     * @return array The tables
     */
    protected function getTables(?string $connection = null): array
    {
        return DB::connection($connection)->getDoctrineSchemaManager()->listTableNames();
    }
    
    /**
     * Determine if a table exists.
     *
     * @param string $table The table name
     * @param string|null $connection The database connection
     * @return bool True if the table exists, false otherwise
     */
    protected function tableExists(string $table, ?string $connection = null): bool
    {
        return Schema::connection($connection)->hasTable($table);
    }
    
    /**
     * Get the columns for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The database connection
     * @return array The columns
     */
    protected function getTableColumns(string $table, ?string $connection = null): array
    {
        return Schema::connection($connection)->getColumnListing($table);
    }
    
    /**
     * Determine if a column exists in a table.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The database connection
     * @return bool True if the column exists, false otherwise
     */
    protected function columnExists(string $table, string $column, ?string $connection = null): bool
    {
        return Schema::connection($connection)->hasColumn($table, $column);
    }
    
    /**
     * Get the column type for a column in a table.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The database connection
     * @return string The column type
     */
    protected function getColumnType(string $table, string $column, ?string $connection = null): string
    {
        return Schema::connection($connection)->getColumnType($table, $column);
    }
    
    /**
     * Determine if a table has a specific index.
     *
     * @param string $table The table name
     * @param string $index The index name
     * @param string|null $connection The database connection
     * @return bool True if the table has the index, false otherwise
     */
    protected function hasIndex(string $table, string $index, ?string $connection = null): bool
    {
        return Schema::connection($connection)->hasIndex($table, $index);
    }
    
    /**
     * Get the indexes for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The database connection
     * @return array The indexes
     */
    protected function getIndexes(string $table, ?string $connection = null): array
    {
        return DB::connection($connection)->getDoctrineSchemaManager()->listTableIndexes($table);
    }
    
    /**
     * Get the foreign keys for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The database connection
     * @return array The foreign keys
     */
    protected function getForeignKeys(string $table, ?string $connection = null): array
    {
        return DB::connection($connection)->getDoctrineSchemaManager()->listTableForeignKeys($table);
    }
    
    /**
     * Get the primary key for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The database connection
     * @return string|array The primary key
     */
    protected function getPrimaryKey(string $table, ?string $connection = null)
    {
        $indexes = $this->getIndexes($table, $connection);
        
        foreach ($indexes as $index) {
            if ($index->isPrimary()) {
                return $index->getColumns();
            }
        }
        
        return [];
    }
    
    /**
     * Get the database configuration.
     *
     * @return array The database configuration
     */
    protected function getDatabaseConfig(): array
    {
        return config('database');
    }
    
    /**
     * Get the configuration for a specific database connection.
     *
     * @param string $connection The connection name
     * @return array The connection configuration
     */
    protected function getConnectionConfig(string $connection): array
    {
        return config('database.connections.' . $connection, []);
    }
    
    /**
     * Get the query log for a connection.
     *
     * @param string|null $connection The connection name
     * @return array The query log
     */
    protected function getQueryLog(?string $connection = null): array
    {
        return DB::connection($connection)->getQueryLog();
    }
    
    /**
     * Enable the query log for a connection.
     *
     * @param string|null $connection The connection name
     * @return void
     */
    protected function enableQueryLog(?string $connection = null): void
    {
        DB::connection($connection)->enableQueryLog();
    }
    
    /**
     * Disable the query log for a connection.
     *
     * @param string|null $connection The connection name
     * @return void
     */
    protected function disableQueryLog(?string $connection = null): void
    {
        DB::connection($connection)->disableQueryLog();
    }
    
    /**
     * Determine if the query log is enabled for a connection.
     *
     * @param string|null $connection The connection name
     * @return bool True if the query log is enabled, false otherwise
     */
    protected function queryLogEnabled(?string $connection = null): bool
    {
        return DB::connection($connection)->logging();
    }
    
    /**
     * Clear the query log for a connection.
     *
     * @param string|null $connection The connection name
     * @return void
     */
    protected function clearQueryLog(?string $connection = null): void
    {
        DB::connection($connection)->flushQueryLog();
    }
    
    /**
     * Get the last executed query for a connection.
     *
     * @param string|null $connection The connection name
     * @return string|null The last executed query
     */
    protected function getLastQuery(?string $connection = null): ?string
    {
        $log = $this->getQueryLog($connection);
        
        if (empty($log)) {
            return null;
        }
        
        $lastQuery = end($log);
        
        return $lastQuery['query'] ?? null;
    }
    
    /**
     * Get the number of executed queries for a connection.
     *
     * @param string|null $connection The connection name
     * @return int The number of executed queries
     */
    protected function getQueryCount(?string $connection = null): int
    {
        return count($this->getQueryLog($connection));
    }
    
    /**
     * Disconnect from a database connection.
     *
     * @param string|null $connection The connection name
     * @return void
     */
    protected function disconnect(?string $connection = null): void
    {
        DB::connection($connection)->disconnect();
    }
    
    /**
     * Reconnect to a database connection.
     *
     * @param string|null $connection The connection name
     * @return void
     */
    protected function reconnect(?string $connection = null): void
    {
        DB::connection($connection)->reconnect();
    }
    
    /**
     * Get the PDO instance for a connection.
     *
     * @param string|null $connection The connection name
     * @return \PDO The PDO instance
     */
    protected function getPdo(?string $connection = null): \PDO
    {
        return DB::connection($connection)->getPdo();
    }
    
    /**
     * Execute a callback in a specific database transaction isolation level.
     *
     * @param callable $callback The callback
     * @param string $isolationLevel The isolation level
     * @param string|null $connection The database connection
     * @return mixed The callback result
     * @throws \Throwable If an error occurs
     */
    protected function transactionWithIsolationLevel(callable $callback, string $isolationLevel, ?string $connection = null)
    {
        return DB::connection($connection)->transaction($callback, 0, $isolationLevel);
    }
    
    /**
     * Get the elapsed time for a query.
     *
     * @param string|null $connection The connection name
     * @return float The elapsed time in milliseconds
     */
    protected function getQueryTime(?string $connection = null): float
    {
        $log = $this->getQueryLog($connection);
        
        if (empty($log)) {
            return 0.0;
        }
        
        $lastQuery = end($log);
        
        return $lastQuery['time'] ?? 0.0;
    }
    
    /**
     * Get the total elapsed time for all queries.
     *
     * @param string|null $connection The connection name
     * @return float The total elapsed time in milliseconds
     */
    protected function getTotalQueryTime(?string $connection = null): float
    {
        $log = $this->getQueryLog($connection);
        $totalTime = 0.0;
        
        foreach ($log as $query) {
            $totalTime += $query['time'] ?? 0.0;
        }
        
        return $totalTime;
    }
    
    /**
     * Get the average elapsed time for all queries.
     *
     * @param string|null $connection The connection name
     * @return float The average elapsed time in milliseconds
     */
    protected function getAverageQueryTime(?string $connection = null): float
    {
        $log = $this->getQueryLog($connection);
        $count = count($log);
        
        if ($count === 0) {
            return 0.0;
        }
        
        return $this->getTotalQueryTime($connection) / $count;
    }
    
    /**
     * Get the slowest query.
     *
     * @param string|null $connection The connection name
     * @return array|null The slowest query
     */
    protected function getSlowestQuery(?string $connection = null): ?array
    {
        $log = $this->getQueryLog($connection);
        
        if (empty($log)) {
            return null;
        }
        
        $slowestQuery = null;
        $maxTime = 0.0;
        
        foreach ($log as $query) {
            $time = $query['time'] ?? 0.0;
            
            if ($time > $maxTime) {
                $maxTime = $time;
                $slowestQuery = $query;
            }
        }
        
        return $slowestQuery;
    }
    
    /**
     * Get the fastest query.
     *
     * @param string|null $connection The connection name
     * @return array|null The fastest query
     */
    protected function getFastestQuery(?string $connection = null): ?array
    {
        $log = $this->getQueryLog($connection);
        
        if (empty($log)) {
            return null;
        }
        
        $fastestQuery = null;
        $minTime = PHP_FLOAT_MAX;
        
        foreach ($log as $query) {
            $time = $query['time'] ?? 0.0;
            
            if ($time < $minTime) {
                $minTime = $time;
                $fastestQuery = $query;
            }
        }
        
        return $fastestQuery;
    }
    
    /**
     * Get the queries that took longer than a specific time.
     *
     * @param float $time The time threshold in milliseconds
     * @param string|null $connection The connection name
     * @return array The slow queries
     */
    protected function getSlowQueries(float $time, ?string $connection = null): array
    {
        $log = $this->getQueryLog($connection);
        $slowQueries = [];
        
        foreach ($log as $query) {
            $queryTime = $query['time'] ?? 0.0;
            
            if ($queryTime > $time) {
                $slowQueries[] = $query;
            }
        }
        
        return $slowQueries;
    }
    
    /**
     * Get the queries that match a specific pattern.
     *
     * @param string $pattern The pattern to match
     * @param string|null $connection The connection name
     * @return array The matching queries
     */
    protected function getQueriesMatchingPattern(string $pattern, ?string $connection = null): array
    {
        $log = $this->getQueryLog($connection);
        $matchingQueries = [];
        
        foreach ($log as $query) {
            $sql = $query['query'] ?? '';
            
            if (preg_match($pattern, $sql)) {
                $matchingQueries[] = $query;
            }
        }
        
        return $matchingQueries;
    }
    
    /**
     * Get the queries for a specific table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The table queries
     */
    protected function getQueriesForTable(string $table, ?string $connection = null): array
    {
        $pattern = '/\b' . preg_quote($table, '/') . '\b/i';
        
        return $this->getQueriesMatchingPattern($pattern, $connection);
    }
    
    /**
     * Get the SELECT queries.
     *
     * @param string|null $connection The connection name
     * @return array The SELECT queries
     */
    protected function getSelectQueries(?string $connection = null): array
    {
        return $this->getQueriesMatchingPattern('/^\s*SELECT\b/i', $connection);
    }
    
    /**
     * Get the INSERT queries.
     *
     * @param string|null $connection The connection name
     * @return array The INSERT queries
     */
    protected function getInsertQueries(?string $connection = null): array
    {
        return $this->getQueriesMatchingPattern('/^\s*INSERT\b/i', $connection);
    }
    
    /**
     * Get the UPDATE queries.
     *
     * @param string|null $connection The connection name
     * @return array The UPDATE queries
     */
    protected function getUpdateQueries(?string $connection = null): array
    {
        return $this->getQueriesMatchingPattern('/^\s*UPDATE\b/i', $connection);
    }
    
    /**
     * Get the DELETE queries.
     *
     * @param string|null $connection The connection name
     * @return array The DELETE queries
     */
    protected function getDeleteQueries(?string $connection = null): array
    {
        return $this->getQueriesMatchingPattern('/^\s*DELETE\b/i', $connection);
    }
    
    /**
     * Get the database server version.
     *
     * @param string|null $connection The connection name
     * @return string The server version
     */
    protected function getDatabaseServerVersion(?string $connection = null): string
    {
        return DB::connection($connection)->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }
    
    /**
     * Get the database server info.
     *
     * @param string|null $connection The connection name
     * @return string The server info
     */
    protected function getDatabaseServerInfo(?string $connection = null): string
    {
        return DB::connection($connection)->getPdo()->getAttribute(\PDO::ATTR_SERVER_INFO) ?? '';
    }
    
    /**
     * Get the database client version.
     *
     * @param string|null $connection The connection name
     * @return string The client version
     */
    protected function getDatabaseClientVersion(?string $connection = null): string
    {
        return DB::connection($connection)->getPdo()->getAttribute(\PDO::ATTR_CLIENT_VERSION) ?? '';
    }
    
    /**
     * Get the database connection status.
     *
     * @param string|null $connection The connection name
     * @return string The connection status
     */
    protected function getDatabaseConnectionStatus(?string $connection = null): string
    {
        return DB::connection($connection)->getPdo()->getAttribute(\PDO::ATTR_CONNECTION_STATUS) ?? '';
    }
    
    /**
     * Check if a database connection is in read-only mode.
     *
     * @param string|null $connection The connection name
     * @return bool True if the connection is in read-only mode, false otherwise
     */
    protected function isDatabaseReadOnly(?string $connection = null): bool
    {
        $result = DB::connection($connection)->select('SELECT @@read_only as read_only');
        
        return (bool) ($result[0]->read_only ?? false);
    }
    
    /**
     * Get the database size.
     *
     * @param string|null $connection The connection name
     * @return int The database size in bytes
     */
    protected function getDatabaseSize(?string $connection = null): int
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = ?',
                [$database]
            );
            
            return (int) ($result[0]->size ?? 0);
        }
        
        if ($driver === 'pgsql') {
            $result = DB::connection($connection)->select(
                'SELECT pg_database_size(?) as size',
                [$database]
            );
            
            return (int) ($result[0]->size ?? 0);
        }
        
        if ($driver === 'sqlite') {
            $path = DB::connection($connection)->getPdo()->query('PRAGMA database_list')->fetchAll()[0]['file'] ?? null;
            
            if ($path && file_exists($path)) {
                return filesize($path);
            }
        }
        
        return 0;
    }
    
    /**
     * Get the size of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return int The table size in bytes
     */
    protected function getTableSize(string $table, ?string $connection = null): int
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT data_length + index_length as size FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );
            
            return (int) ($result[0]->size ?? 0);
        }
        
        if ($driver === 'pgsql') {
            $result = DB::connection($connection)->select(
                'SELECT pg_total_relation_size(?) as size',
                [$table]
            );
            
            return (int) ($result[0]->size ?? 0);
        }
        
        return 0;
    }
    
    /**
     * Get the row count of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return int The row count
     */
    protected function getTableRowCountSimple(string $table, ?string $connection = null): int
    {
        return DB::connection($connection)->table($table)->count();
    }
    
    /**
     * Get the estimated row count of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return int The estimated row count
     */
    protected function getEstimatedTableRowCount(string $table, ?string $connection = null): int
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT table_rows as count FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );
            
            return (int) ($result[0]->count ?? 0);
        }
        
        if ($driver === 'pgsql') {
            $result = DB::connection($connection)->select(
                'SELECT reltuples::bigint as count FROM pg_class WHERE relname = ?',
                [$table]
            );
            
            return (int) ($result[0]->count ?? 0);
        }
        
        return $this->getTableRowCount($table, $connection);
    }
    
    /**
     * Truncate a table.
     *
     * @param string $table The table name
     * @param string|null $connection The database connection
     * @return void
     */
    protected function truncateTableSimple(string $table, ?string $connection = null): void
    {
        DB::connection($connection)->table($table)->truncate();
    }
    
    /**
     * Get the creation date of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return string|null The creation date
     */
    protected function getTableCreationDate(string $table, ?string $connection = null): ?string
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT create_time FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );
            
            return $result[0]->create_time ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the update date of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return string|null The update date
     */
    protected function getTableUpdateDate(string $table, ?string $connection = null): ?string
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT update_time FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );
            
            return $result[0]->update_time ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the auto-increment value of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return int|null The auto-increment value
     */
    protected function getTableAutoIncrement(string $table, ?string $connection = null): ?int
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT auto_increment FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );
            
            return $result[0]->auto_increment ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the engine of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return string|null The engine
     */
    protected function getTableEngine(string $table, ?string $connection = null): ?string
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT engine FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );
            
            return $result[0]->engine ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the collation of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return string|null The collation
     */
    protected function getTableCollation(string $table, ?string $connection = null): ?string
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT table_collation FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );
            
            return $result[0]->table_collation ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the collation of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return string|null The collation
     */
    protected function getColumnCollation(string $table, string $column, ?string $connection = null): ?string
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT collation_name FROM information_schema.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?',
                [$database, $table, $column]
            );
            
            return $result[0]->collation_name ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the character set of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return string|null The character set
     */
    protected function getTableCharacterSet(string $table, ?string $connection = null): ?string
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT character_set_name FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );
            
            return $result[0]->character_set_name ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the character set of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return string|null The character set
     */
    protected function getColumnCharacterSet(string $table, string $column, ?string $connection = null): ?string
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT character_set_name FROM information_schema.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?',
                [$database, $table, $column]
            );
            
            return $result[0]->character_set_name ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the default value of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return mixed The default value
     */
    protected function getColumnDefaultValue(string $table, string $column, ?string $connection = null)
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT column_default FROM information_schema.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?',
                [$database, $table, $column]
            );
            
            return $result[0]->column_default ?? null;
        }
        
        return null;
    }
    
    /**
     * Check if a column is nullable.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return bool True if the column is nullable, false otherwise
     */
    protected function isColumnNullable(string $table, string $column, ?string $connection = null): bool
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT is_nullable FROM information_schema.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?',
                [$database, $table, $column]
            );
            
            return strtoupper($result[0]->is_nullable ?? 'NO') === 'YES';
        }
        
        return false;
    }
    
    /**
     * Check if a column is auto-increment.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return bool True if the column is auto-increment, false otherwise
     */
    protected function isColumnAutoIncrement(string $table, string $column, ?string $connection = null): bool
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT extra FROM information_schema.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?',
                [$database, $table, $column]
            );
            
            return strpos(strtoupper($result[0]->extra ?? ''), 'AUTO_INCREMENT') !== false;
        }
        
        return false;
    }
    
    /**
     * Check if a column is a primary key.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return bool True if the column is a primary key, false otherwise
     */
    protected function isColumnPrimaryKey(string $table, string $column, ?string $connection = null): bool
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT column_key FROM information_schema.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?',
                [$database, $table, $column]
            );
            
            return strtoupper($result[0]->column_key ?? '') === 'PRI';
        }
        
        return false;
    }
    
    /**
     * Check if a column is a unique key.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return bool True if the column is a unique key, false otherwise
     */
    protected function isColumnUnique(string $table, string $column, ?string $connection = null): bool
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT column_key FROM information_schema.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?',
                [$database, $table, $column]
            );
            
            return strtoupper($result[0]->column_key ?? '') === 'UNI';
        }
        
        return false;
    }
    
    /**
     * Check if a column is a foreign key.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return bool True if the column is a foreign key, false otherwise
     */
    protected function isColumnForeignKey(string $table, string $column, ?string $connection = null): bool
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT COUNT(*) as count FROM information_schema.KEY_COLUMN_USAGE WHERE table_schema = ? AND table_name = ? AND column_name = ? AND referenced_table_name IS NOT NULL',
                [$database, $table, $column]
            );
            
            return (int) ($result[0]->count ?? 0) > 0;
        }
        
        return false;
    }
    
    /**
     * Get the foreign key reference for a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array|null The foreign key reference
     */
    protected function getColumnForeignKeyReference(string $table, string $column, ?string $connection = null): ?array
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT referenced_table_name, referenced_column_name FROM information_schema.KEY_COLUMN_USAGE WHERE table_schema = ? AND table_name = ? AND column_name = ? AND referenced_table_name IS NOT NULL',
                [$database, $table, $column]
            );
            
            if (empty($result)) {
                return null;
            }
            
            return [
                'table' => $result[0]->referenced_table_name,
                'column' => $result[0]->referenced_column_name,
            ];
        }
        
        return null;
    }
    
    /**
     * Get the columns that reference a specific table and column.
     *
     * @param string $referencedTable The referenced table name
     * @param string $referencedColumn The referenced column name
     * @param string|null $connection The connection name
     * @return array The referencing columns
     */
    protected function getReferencingColumns(string $referencedTable, string $referencedColumn, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $database = $this->getDatabaseName($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                'SELECT table_name, column_name FROM information_schema.KEY_COLUMN_USAGE WHERE table_schema = ? AND referenced_table_name = ? AND referenced_column_name = ?',
                [$database, $referencedTable, $referencedColumn]
            );
            
            $references = [];
            
            foreach ($result as $row) {
                $references[] = [
                    'table' => $row->table_name,
                    'column' => $row->column_name,
                ];
            }
            
            return $references;
        }
        
        return [];
    }
    
    /**
     * Get the maximum value of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return mixed The maximum value
     */
    protected function getColumnMaxValue(string $table, string $column, ?string $connection = null)
    {
        $result = DB::connection($connection)->table($table)->max($column);
        
        return $result;
    }
    
    /**
     * Get the minimum value of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return mixed The minimum value
     */
    protected function getColumnMinValue(string $table, string $column, ?string $connection = null)
    {
        $result = DB::connection($connection)->table($table)->min($column);
        
        return $result;
    }
    
    /**
     * Get the average value of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return mixed The average value
     */
    protected function getColumnAvgValue(string $table, string $column, ?string $connection = null)
    {
        $result = DB::connection($connection)->table($table)->avg($column);
        
        return $result;
    }
    
    /**
     * Get the sum of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return mixed The sum
     */
    protected function getColumnSum(string $table, string $column, ?string $connection = null)
    {
        $result = DB::connection($connection)->table($table)->sum($column);
        
        return $result;
    }
    
    /**
     * Get the distinct values of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The distinct values
     */
    protected function getColumnDistinctValues(string $table, string $column, ?string $connection = null): array
    {
        $result = DB::connection($connection)->table($table)->distinct()->pluck($column)->toArray();
        
        return $result;
    }
    
    /**
     * Get the number of distinct values of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return int The number of distinct values
     */
    protected function getColumnDistinctCount(string $table, string $column, ?string $connection = null): int
    {
        $result = DB::connection($connection)->table($table)->distinct($column)->count($column);
        
        return $result;
    }
    
    /**
     * Get the value distribution of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The value distribution
     */
    protected function getColumnValueDistribution(string $table, string $column, ?string $connection = null): array
    {
        $result = DB::connection($connection)->table($table)
            ->select($column, DB::raw('COUNT(*) as count'))
            ->groupBy($column)
            ->get()
            ->mapWithKeys(function ($item) use ($column) {
                return [$item->{$column} => $item->count];
            })
            ->toArray();
        
        return $result;
    }
    
    /**
     * Get the null count of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return int The null count
     */
    protected function getColumnNullCount(string $table, string $column, ?string $connection = null): int
    {
        $result = DB::connection($connection)->table($table)->whereNull($column)->count();
        
        return $result;
    }
    
    /**
     * Get the not null count of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return int The not null count
     */
    protected function getColumnNotNullCount(string $table, string $column, ?string $connection = null): int
    {
        $result = DB::connection($connection)->table($table)->whereNotNull($column)->count();
        
        return $result;
    }
    
    /**
     * Get the empty string count of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return int The empty string count
     */
    protected function getColumnEmptyStringCount(string $table, string $column, ?string $connection = null): int
    {
        $result = DB::connection($connection)->table($table)->where($column, '')->count();
        
        return $result;
    }
    
    /**
     * Get the zero count of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return int The zero count
     */
    protected function getColumnZeroCount(string $table, string $column, ?string $connection = null): int
    {
        $result = DB::connection($connection)->table($table)->where($column, 0)->count();
        
        return $result;
    }
    
    /**
     * Get the duplicate count of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return int The duplicate count
     */
    protected function getColumnDuplicateCount(string $table, string $column, ?string $connection = null): int
    {
        $result = DB::connection($connection)->table($table)
            ->select($column, DB::raw('COUNT(*) as count'))
            ->groupBy($column)
            ->having('count', '>', 1)
            ->count();
        
        return $result;
    }
    
    /**
     * Get the duplicate values of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The duplicate values
     */
    protected function getColumnDuplicateValues(string $table, string $column, ?string $connection = null): array
    {
        $result = DB::connection($connection)->table($table)
            ->select($column, DB::raw('COUNT(*) as count'))
            ->groupBy($column)
            ->having('count', '>', 1)
            ->get()
            ->pluck('count', $column)
            ->toArray();
        
        return $result;
    }
    
    /**
     * Get the unique count of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return int The unique count
     */
    protected function getColumnUniqueCount(string $table, string $column, ?string $connection = null): int
    {
        $result = DB::connection($connection)->table($table)
            ->select($column, DB::raw('COUNT(*) as count'))
            ->groupBy($column)
            ->having('count', '=', 1)
            ->count();
        
        return $result;
    }
    
    /**
     * Get the unique values of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The unique values
     */
    protected function getColumnUniqueValues(string $table, string $column, ?string $connection = null): array
    {
        $result = DB::connection($connection)->table($table)
            ->select($column, DB::raw('COUNT(*) as count'))
            ->groupBy($column)
            ->having('count', '=', 1)
            ->get()
            ->pluck($column)
            ->toArray();
        
        return $result;
    }
    
    /**
     * Get the most common value of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return mixed The most common value
     */
    protected function getColumnMostCommonValue(string $table, string $column, ?string $connection = null)
    {
        $result = DB::connection($connection)->table($table)
            ->select($column, DB::raw('COUNT(*) as count'))
            ->groupBy($column)
            ->orderBy('count', 'desc')
            ->first();
        
        return $result ? $result->{$column} : null;
    }
    
    /**
     * Get the least common value of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return mixed The least common value
     */
    protected function getColumnLeastCommonValue(string $table, string $column, ?string $connection = null)
    {
        $result = DB::connection($connection)->table($table)
            ->select($column, DB::raw('COUNT(*) as count'))
            ->groupBy($column)
            ->orderBy('count', 'asc')
            ->first();
        
        return $result ? $result->{$column} : null;
    }
    
    /**
     * Get the frequency of a specific value in a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param mixed $value The value
     * @param string|null $connection The connection name
     * @return int The frequency
     */
    protected function getColumnValueFrequency(string $table, string $column, $value, ?string $connection = null): int
    {
        $result = DB::connection($connection)->table($table)->where($column, $value)->count();
        
        return $result;
    }
    
    /**
     * Get the percentage of a specific value in a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param mixed $value The value
     * @param string|null $connection The connection name
     * @return float The percentage
     */
    protected function getColumnValuePercentage(string $table, string $column, $value, ?string $connection = null): float
    {
        $total = $this->getTableRowCount($table, $connection);
        
        if ($total === 0) {
            return 0.0;
        }
        
        $count = $this->getColumnValueFrequency($table, $column, $value, $connection);
        
        return ($count / $total) * 100;
    }
    
    /**
     * Get the percentage of null values in a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float The percentage
     */
    protected function getColumnNullPercentage(string $table, string $column, ?string $connection = null): float
    {
        $total = $this->getTableRowCount($table, $connection);
        
        if ($total === 0) {
            return 0.0;
        }
        
        $count = $this->getColumnNullCount($table, $column, $connection);
        
        return ($count / $total) * 100;
    }
    
    /**
     * Get the percentage of not null values in a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float The percentage
     */
    protected function getColumnNotNullPercentage(string $table, string $column, ?string $connection = null): float
    {
        $total = $this->getTableRowCount($table, $connection);
        
        if ($total === 0) {
            return 0.0;
        }
        
        $count = $this->getColumnNotNullCount($table, $column, $connection);
        
        return ($count / $total) * 100;
    }
    
    /**
     * Get the percentage of empty string values in a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float The percentage
     */
    protected function getColumnEmptyStringPercentage(string $table, string $column, ?string $connection = null): float
    {
        $total = $this->getTableRowCount($table, $connection);
        
        if ($total === 0) {
            return 0.0;
        }
        
        $count = $this->getColumnEmptyStringCount($table, $column, $connection);
        
        return ($count / $total) * 100;
    }
    
    /**
     * Get the percentage of zero values in a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float The percentage
     */
    protected function getColumnZeroPercentage(string $table, string $column, ?string $connection = null): float
    {
        $total = $this->getTableRowCount($table, $connection);
        
        if ($total === 0) {
            return 0.0;
        }
        
        $count = $this->getColumnZeroCount($table, $column, $connection);
        
        return ($count / $total) * 100;
    }
    
    /**
     * Get the percentage of duplicate values in a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float The percentage
     */
    protected function getColumnDuplicatePercentage(string $table, string $column, ?string $connection = null): float
    {
        $total = $this->getTableRowCount($table, $connection);
        
        if ($total === 0) {
            return 0.0;
        }
        
        $count = $this->getColumnDuplicateCount($table, $column, $connection);
        
        return ($count / $total) * 100;
    }
    
    /**
     * Get the percentage of unique values in a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float The percentage
     */
    protected function getColumnUniquePercentage(string $table, string $column, ?string $connection = null): float
    {
        $total = $this->getTableRowCount($table, $connection);
        
        if ($total === 0) {
            return 0.0;
        }
        
        $count = $this->getColumnUniqueCount($table, $column, $connection);
        
        return ($count / $total) * 100;
    }
    
    /**
     * Get the data type distribution of a column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The data type distribution
     */
    protected function getColumnDataTypeDistribution(string $table, string $column, ?string $connection = null): array
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
        $distribution = [];
        
        foreach ($values as $value) {
            $type = gettype($value);
            $distribution[$type] = ($distribution[$type] ?? 0) + 1;
        }
        
        return $distribution;
    }
    
    /**
     * Get the length distribution of a string column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The length distribution
     */
    protected function getColumnLengthDistribution(string $table, string $column, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $distribution = [];
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->table($table)
                ->select(DB::raw("LENGTH($column) as length"), DB::raw('COUNT(*) as count'))
                ->whereNotNull($column)
                ->groupBy(DB::raw("LENGTH($column)"))
                ->get();
            
            foreach ($result as $row) {
                $distribution[$row->length] = $row->count;
            }
        } elseif ($driver === 'pgsql') {
            $result = DB::connection($connection)->table($table)
                ->select(DB::raw("LENGTH($column) as length"), DB::raw('COUNT(*) as count'))
                ->whereNotNull($column)
                ->groupBy(DB::raw("LENGTH($column)"))
                ->get();
            
            foreach ($result as $row) {
                $distribution[$row->length] = $row->count;
            }
        } elseif ($driver === 'sqlite') {
            $result = DB::connection($connection)->table($table)
                ->select(DB::raw("LENGTH($column) as length"), DB::raw('COUNT(*) as count'))
                ->whereNotNull($column)
                ->groupBy(DB::raw("LENGTH($column)"))
                ->get();
            
            foreach ($result as $row) {
                $distribution[$row->length] = $row->count;
            }
        }
        
        return $distribution;
    }
    
    /**
     * Get the pattern distribution of a string column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param array $patterns The patterns to match
     * @param string|null $connection The connection name
     * @return array The pattern distribution
     */
    protected function getColumnPatternDistribution(string $table, string $column, array $patterns, ?string $connection = null): array
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
        $distribution = array_fill_keys(array_keys($patterns), 0);
        $distribution['other'] = 0;
        
        foreach ($values as $value) {
            $matched = false;
            
            foreach ($patterns as $name => $pattern) {
                if (preg_match($pattern, $value)) {
                    $distribution[$name]++;
                    $matched = true;
                    break;
                }
            }
            
            if (!$matched) {
                $distribution['other']++;
            }
        }
        
        return $distribution;
    }
    
    /**
     * Get the character distribution of a string column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The character distribution
     */
    protected function getColumnCharacterDistribution(string $table, string $column, ?string $connection = null): array
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
        $distribution = [];
        
        foreach ($values as $value) {
            $chars = str_split($value);
            
            foreach ($chars as $char) {
                $distribution[$char] = ($distribution[$char] ?? 0) + 1;
            }
        }
        
        return $distribution;
    }
    
    /**
     * Get the word distribution of a string column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The word distribution
     */
    protected function getColumnWordDistribution(string $table, string $column, ?string $connection = null): array
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
        $distribution = [];
        
        foreach ($values as $value) {
            $words = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);
            
            foreach ($words as $word) {
                $distribution[$word] = ($distribution[$word] ?? 0) + 1;
            }
        }
        
        return $distribution;
    }
    
    /**
     * Get the date distribution of a date column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string $groupBy The grouping (day, month, year, weekday)
     * @param string|null $connection The connection name
     * @return array The date distribution
     */
    protected function getColumnDateDistribution(string $table, string $column, string $groupBy = 'day', ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $distribution = [];
        
        if ($driver === 'mysql') {
            $format = '';
            
            switch ($groupBy) {
                case 'day':
                    $format = '%Y-%m-%d';
                    break;
                case 'month':
                    $format = '%Y-%m';
                    break;
                case 'year':
                    $format = '%Y';
                    break;
                case 'weekday':
                    $format = '%W';
                    break;
                default:
                    $format = '%Y-%m-%d';
            }
            
            $result = DB::connection($connection)->table($table)
                ->select(DB::raw("DATE_FORMAT($column, '$format') as date_group"), DB::raw('COUNT(*) as count'))
                ->whereNotNull($column)
                ->groupBy(DB::raw("DATE_FORMAT($column, '$format')"))
                ->get();
            
            foreach ($result as $row) {
                $distribution[$row->date_group] = $row->count;
            }
        } elseif ($driver === 'pgsql') {
            $format = '';
            
            switch ($groupBy) {
                case 'day':
                    $format = 'YYYY-MM-DD';
                    break;
                case 'month':
                    $format = 'YYYY-MM';
                    break;
                case 'year':
                    $format = 'YYYY';
                    break;
                case 'weekday':
                    $format = 'Day';
                    break;
                default:
                    $format = 'YYYY-MM-DD';
            }
            
            $result = DB::connection($connection)->table($table)
                ->select(DB::raw("TO_CHAR($column, '$format') as date_group"), DB::raw('COUNT(*) as count'))
                ->whereNotNull($column)
                ->groupBy(DB::raw("TO_CHAR($column, '$format')"))
                ->get();
            
            foreach ($result as $row) {
                $distribution[$row->date_group] = $row->count;
            }
        } elseif ($driver === 'sqlite') {
            $format = '';
            
            switch ($groupBy) {
                case 'day':
                    $format = '%Y-%m-%d';
                    break;
                case 'month':
                    $format = '%Y-%m';
                    break;
                case 'year':
                    $format = '%Y';
                    break;
                case 'weekday':
                    $format = '%w';
                    break;
                default:
                    $format = '%Y-%m-%d';
            }
            
            $result = DB::connection($connection)->table($table)
                ->select(DB::raw("strftime('$format', $column) as date_group"), DB::raw('COUNT(*) as count'))
                ->whereNotNull($column)
                ->groupBy(DB::raw("strftime('$format', $column)"))
                ->get();
            
            foreach ($result as $row) {
                $distribution[$row->date_group] = $row->count;
            }
        }
        
        return $distribution;
    }
    
    /**
     * Get the numeric range distribution of a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param int $buckets The number of buckets
     * @param string|null $connection The connection name
     * @return array The range distribution
     */
    protected function getColumnNumericRangeDistribution(string $table, string $column, int $buckets = 10, ?string $connection = null): array
    {
        $min = $this->getColumnMinValue($table, $column, $connection);
        $max = $this->getColumnMaxValue($table, $column, $connection);
        
        if ($min === null || $max === null || $min === $max) {
            return [];
        }
        
        $range = $max - $min;
        $bucketSize = $range / $buckets;
        $distribution = [];
        
        for ($i = 0; $i < $buckets; $i++) {
            $lowerBound = $min + ($i * $bucketSize);
            $upperBound = $min + (($i + 1) * $bucketSize);
            
            if ($i === $buckets - 1) {
                $upperBound = $max;
            }
            
            $count = DB::connection($connection)->table($table)
                ->where($column, '>=', $lowerBound)
                ->where($column, $i === $buckets - 1 ? '<=' : '<', $upperBound)
                ->count();
            
            $distribution["$lowerBound - $upperBound"] = $count;
        }
        
        return $distribution;
    }
    
    /**
     * Get the correlation between two numeric columns.
     *
     * @param string $table The table name
     * @param string $column1 The first column name
     * @param string $column2 The second column name
     * @param string|null $connection The connection name
     * @return float|null The correlation coefficient
     */
    protected function getColumnCorrelation(string $table, string $column1, string $column2, ?string $connection = null): ?float
    {
        $driver = $this->getDatabaseDriver($connection);
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                "SELECT
                    (COUNT(*) * SUM($column1 * $column2) - SUM($column1) * SUM($column2)) /
                    (SQRT(COUNT(*) * SUM($column1 * $column1) - SUM($column1) * SUM($column1)) *
                     SQRT(COUNT(*) * SUM($column2 * $column2) - SUM($column2) * SUM($column2))) as correlation
                FROM $table
                WHERE $column1 IS NOT NULL AND $column2 IS NOT NULL"
            );
            
            return $result[0]->correlation;
        }
        
        return null;
    }
    
    /**
     * Get the outliers in a numeric column using the IQR method.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param float $iqrMultiplier The IQR multiplier (default: 1.5)
     * @param string|null $connection The connection name
     * @return array The outliers
     */
    protected function getColumnOutliers(string $table, string $column, float $iqrMultiplier = 1.5, ?string $connection = null): array
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
        
        if (empty($values)) {
            return [];
        }
        
        sort($values);
        $count = count($values);
        
        $q1Index = floor($count / 4);
        $q3Index = floor(3 * $count / 4);
        
        $q1 = $values[$q1Index];
        $q3 = $values[$q3Index];
        
        $iqr = $q3 - $q1;
        $lowerBound = $q1 - ($iqrMultiplier * $iqr);
        $upperBound = $q3 + ($iqrMultiplier * $iqr);
        
        $outliers = [];
        
        foreach ($values as $value) {
            if ($value < $lowerBound || $value > $upperBound) {
                $outliers[] = $value;
            }
        }
        
        return $outliers;
    }
    
    /**
     * Get the percentile value of a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param float $percentile The percentile (0-100)
     * @param string|null $connection The connection name
     * @return float|null The percentile value
     */
    protected function getColumnPercentile(string $table, string $column, float $percentile, ?string $connection = null): ?float
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
        
        if (empty($values)) {
            return null;
        }
        
        sort($values);
        $count = count($values);
        
        $index = ($percentile / 100) * ($count - 1);
        $intIndex = floor($index);
        $fraction = $index - $intIndex;
        
        if ($intIndex + 1 < $count) {
            return $values[$intIndex] + ($fraction * ($values[$intIndex + 1] - $values[$intIndex]));
        }
        
        return $values[$intIndex];
    }
    
    /**
     * Get the median value of a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float|null The median value
     */
    protected function getColumnMedian(string $table, string $column, ?string $connection = null): ?float
    {
        return $this->getColumnPercentile($table, $column, 50, $connection);
    }
    
    /**
     * Get the mode value(s) of a column (most frequent value).
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The mode value(s)
     */
    protected function getColumnMode(string $table, string $column, ?string $connection = null): array
    {
        $frequencies = DB::connection($connection)
            ->table($table)
            ->select($column, DB::raw('COUNT(*) as frequency'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderBy('frequency', 'desc')
            ->get()
            ->toArray();
        
        if (empty($frequencies)) {
            return [];
        }
        
        $maxFrequency = $frequencies[0]->frequency;
        $modes = [];
        
        foreach ($frequencies as $item) {
            if ($item->frequency == $maxFrequency) {
                $modes[] = $item->{$column};
            } else {
                break;
            }
        }
        
        return $modes;
    }
    
    /**
     * Get the standard deviation of a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param bool $population Whether to calculate population standard deviation (true) or sample standard deviation (false)
     * @param string|null $connection The connection name
     * @return float|null The standard deviation
     */
    protected function getColumnStandardDeviation(string $table, string $column, bool $population = true, ?string $connection = null): ?float
    {
        $driver = $this->getDatabaseDriver($connection);
        $stdDevFunc = $population ? 'STDDEV_POP' : 'STDDEV_SAMP';
        
        if ($driver === 'mysql' || $driver === 'pgsql') {
            $result = DB::connection($connection)->select(
                "SELECT $stdDevFunc($column) as std_dev FROM $table WHERE $column IS NOT NULL"
            );
            
            return $result[0]->std_dev;
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't have built-in standard deviation functions
            // Calculate manually
            $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
            
            if (empty($values)) {
                return null;
            }
            
            $count = count($values);
            $mean = array_sum($values) / $count;
            $variance = 0;
            
            foreach ($values as $value) {
                $variance += pow($value - $mean, 2);
            }
            
            $divisor = $population ? $count : $count - 1;
            
            if ($divisor === 0) {
                return null;
            }
            
            return sqrt($variance / $divisor);
        }
        
        return null;
    }
    
    /**
     * Get the variance of a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param bool $population Whether to calculate population variance (true) or sample variance (false)
     * @param string|null $connection The connection name
     * @return float|null The variance
     */
    protected function getColumnVariance(string $table, string $column, bool $population = true, ?string $connection = null): ?float
    {
        $driver = $this->getDatabaseDriver($connection);
        $varFunc = $population ? 'VAR_POP' : 'VAR_SAMP';
        
        if ($driver === 'mysql' || $driver === 'pgsql') {
            $result = DB::connection($connection)->select(
                "SELECT $varFunc($column) as variance FROM $table WHERE $column IS NOT NULL"
            );
            
            return $result[0]->variance;
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't have built-in variance functions
            // Calculate manually
            $stdDev = $this->getColumnStandardDeviation($table, $column, $population, $connection);
            
            if ($stdDev === null) {
                return null;
            }
            
            return $stdDev * $stdDev;
        }
        
        return null;
    }
    
    /**
     * Get the skewness of a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float|null The skewness
     */
    protected function getColumnSkewness(string $table, string $column, ?string $connection = null): ?float
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
        
        if (empty($values)) {
            return null;
        }
        
        $count = count($values);
        $mean = array_sum($values) / $count;
        $stdDev = $this->getColumnStandardDeviation($table, $column, true, $connection);
        
        if ($stdDev === null || $stdDev === 0) {
            return null;
        }
        
        $sum = 0;
        
        foreach ($values as $value) {
            $sum += pow(($value - $mean) / $stdDev, 3);
        }
        
        return $sum / $count;
    }
    
    /**
     * Get the kurtosis of a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float|null The kurtosis
     */
    protected function getColumnKurtosis(string $table, string $column, ?string $connection = null): ?float
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column)->toArray();
        
        if (empty($values)) {
            return null;
        }
        
        $count = count($values);
        $mean = array_sum($values) / $count;
        $stdDev = $this->getColumnStandardDeviation($table, $column, true, $connection);
        
        if ($stdDev === null || $stdDev === 0) {
            return null;
        }
        
        $sum = 0;
        
        foreach ($values as $value) {
            $sum += pow(($value - $mean) / $stdDev, 4);
        }
        
        return $sum / $count - 3; // Excess kurtosis (normal distribution has kurtosis of 3)
    }
    
    /**
     * Get the z-scores for values in a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return array The z-scores
     */
    protected function getColumnZScores(string $table, string $column, ?string $connection = null): array
    {
        $values = DB::connection($connection)->table($table)->whereNotNull($column)->pluck($column, 'id')->toArray();
        
        if (empty($values)) {
            return [];
        }
        
        $mean = array_sum($values) / count($values);
        $stdDev = $this->getColumnStandardDeviation($table, $column, true, $connection);
        
        if ($stdDev === null || $stdDev === 0) {
            return [];
        }
        
        $zScores = [];
        
        foreach ($values as $id => $value) {
            $zScores[$id] = ($value - $mean) / $stdDev;
        }
        
        return $zScores;
    }
    
    /**
     * Get the coefficient of variation of a numeric column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param string|null $connection The connection name
     * @return float|null The coefficient of variation
     */
    protected function getColumnCoefficientOfVariation(string $table, string $column, ?string $connection = null): ?float
    {
        $mean = $this->getColumnAverage($table, $column, $connection);
        $stdDev = $this->getColumnStandardDeviation($table, $column, true, $connection);
        
        if ($mean === null || $stdDev === null || $mean === 0) {
            return null;
        }
        
        return ($stdDev / $mean) * 100; // As percentage
    }
    
    /**
     * Get the cross-tabulation (contingency table) between two categorical columns.
     *
     * @param string $table The table name
     * @param string $column1 The first column name
     * @param string $column2 The second column name
     * @param string|null $connection The connection name
     * @return array The cross-tabulation array
     */
    protected function getColumnCrossTabulation(string $table, string $column1, string $column2, ?string $connection = null): array
    {
        $results = DB::connection($connection)
            ->table($table)
            ->select($column1, $column2, DB::raw('COUNT(*) as count'))
            ->whereNotNull($column1)
            ->whereNotNull($column2)
            ->groupBy($column1, $column2)
            ->get();
        
        $crossTab = [];
        $column1Values = [];
        $column2Values = [];
        
        // Collect unique values for both columns
        foreach ($results as $result) {
            $val1 = $result->{$column1};
            $val2 = $result->{$column2};
            
            if (!in_array($val1, $column1Values)) {
                $column1Values[] = $val1;
            }
            
            if (!in_array($val2, $column2Values)) {
                $column2Values[] = $val2;
            }
        }
        
        // Initialize the cross-tabulation array with zeros
        foreach ($column1Values as $val1) {
            $crossTab[$val1] = [];
            foreach ($column2Values as $val2) {
                $crossTab[$val1][$val2] = 0;
            }
        }
        
        // Fill in the counts
        foreach ($results as $result) {
            $crossTab[$result->{$column1}][$result->{$column2}] = $result->count;
        }
        
        return $crossTab;
    }
    
    /**
     * Calculate the chi-square statistic for independence between two categorical columns.
     *
     * @param string $table The table name
     * @param string $column1 The first column name
     * @param string $column2 The second column name
     * @param string|null $connection The connection name
     * @return array The chi-square statistic and p-value
     */
    protected function getColumnChiSquare(string $table, string $column1, string $column2, ?string $connection = null): array
    {
        $crossTab = $this->getColumnCrossTabulation($table, $column1, $column2, $connection);
        
        if (empty($crossTab)) {
            return ['chi_square' => null, 'p_value' => null, 'degrees_of_freedom' => null];
        }
        
        // Calculate row and column totals
        $rowTotals = [];
        $colTotals = [];
        $grandTotal = 0;
        
        foreach ($crossTab as $row => $cols) {
            $rowTotals[$row] = 0;
            foreach ($cols as $col => $count) {
                if (!isset($colTotals[$col])) {
                    $colTotals[$col] = 0;
                }
                $rowTotals[$row] += $count;
                $colTotals[$col] += $count;
                $grandTotal += $count;
            }
        }
        
        // Calculate chi-square statistic
        $chiSquare = 0;
        $degreesOfFreedom = (count($rowTotals) - 1) * (count($colTotals) - 1);
        
        foreach ($crossTab as $row => $cols) {
            foreach ($cols as $col => $observed) {
                $expected = ($rowTotals[$row] * $colTotals[$col]) / $grandTotal;
                if ($expected > 0) {
                    $chiSquare += pow($observed - $expected, 2) / $expected;
                }
            }
        }
        
        // P-value calculation would require a chi-square distribution function
        // For simplicity, we'll return null for p-value
        // In a real implementation, you might use a statistical library
        
        return [
            'chi_square' => $chiSquare,
            'degrees_of_freedom' => $degreesOfFreedom,
            'p_value' => null
        ];
    }
    
    /**
     * Get the mutual information between two columns.
     *
     * @param string $table The table name
     * @param string $column1 The first column name
     * @param string $column2 The second column name
     * @param string|null $connection The connection name
     * @return float|null The mutual information
     */
    protected function getColumnMutualInformation(string $table, string $column1, string $column2, ?string $connection = null): ?float
    {
        $crossTab = $this->getColumnCrossTabulation($table, $column1, $column2, $connection);
        
        if (empty($crossTab)) {
            return null;
        }
        
        // Calculate row and column totals
        $rowTotals = [];
        $colTotals = [];
        $grandTotal = 0;
        
        foreach ($crossTab as $row => $cols) {
            $rowTotals[$row] = 0;
            foreach ($cols as $col => $count) {
                if (!isset($colTotals[$col])) {
                    $colTotals[$col] = 0;
                }
                $rowTotals[$row] += $count;
                $colTotals[$col] += $count;
                $grandTotal += $count;
            }
        }
        
        // Calculate mutual information
        $mutualInfo = 0;
        
        foreach ($crossTab as $row => $cols) {
            foreach ($cols as $col => $count) {
                if ($count > 0) {
                    $pxy = $count / $grandTotal;
                    $px = $rowTotals[$row] / $grandTotal;
                    $py = $colTotals[$col] / $grandTotal;
                    
                    $mutualInfo += $pxy * log($pxy / ($px * $py), 2);
                }
            }
        }
        
        return $mutualInfo;
    }
    
    /**
     * Get the schema information for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The schema information
     */
    protected function getTableSchema(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $schema = [];
        
        if ($driver === 'mysql') {
            $columns = DB::connection($connection)->select(
                "SHOW FULL COLUMNS FROM $table"
            );
            
            foreach ($columns as $column) {
                $schema[] = [
                    'name' => $column->Field,
                    'type' => $column->Type,
                    'null' => $column->Null === 'YES',
                    'key' => $column->Key,
                    'default' => $column->Default,
                    'extra' => $column->Extra,
                    'comment' => $column->Comment,
                ];
            }
        } elseif ($driver === 'pgsql') {
            $columns = DB::connection($connection)->select(
                "SELECT column_name, data_type, is_nullable, column_default, character_maximum_length, numeric_precision, numeric_scale
                FROM information_schema.columns
                WHERE table_name = '$table'"
            );
            
            foreach ($columns as $column) {
                $schema[] = [
                    'name' => $column->column_name,
                    'type' => $this->formatPostgresType($column),
                    'null' => $column->is_nullable === 'YES',
                    'default' => $column->column_default,
                ];
            }
        } elseif ($driver === 'sqlite') {
            $columns = DB::connection($connection)->select(
                "PRAGMA table_info($table)"
            );
            
            foreach ($columns as $column) {
                $schema[] = [
                    'name' => $column->name,
                    'type' => $column->type,
                    'null' => !$column->notnull,
                    'default' => $column->dflt_value,
                    'primary' => $column->pk === 1,
                ];
            }
        }
        
        return $schema;
    }
    
    /**
     * Format PostgreSQL data type with precision and scale.
     *
     * @param object $column The column information
     * @return string The formatted type
     */
    private function formatPostgresType(object $column): string
    {
        $type = $column->data_type;
        
        if ($type === 'character varying' && $column->character_maximum_length) {
            return "varchar({$column->character_maximum_length})";
        } elseif ($type === 'numeric' && $column->numeric_precision && $column->numeric_scale) {
            return "numeric({$column->numeric_precision},{$column->numeric_scale})";
        }
        
        return $type;
    }
    
    /**
     * Get the indexes for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The indexes
     */
    protected function getTableIndexes(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $indexes = [];
        
        if ($driver === 'mysql') {
            $results = DB::connection($connection)->select(
                "SHOW INDEXES FROM $table"
            );
            
            foreach ($results as $result) {
                $keyName = $result->Key_name;
                
                if (!isset($indexes[$keyName])) {
                    $indexes[$keyName] = [
                        'name' => $keyName,
                        'columns' => [],
                        'unique' => !$result->Non_unique,
                        'type' => $result->Index_type,
                    ];
                }
                
                $indexes[$keyName]['columns'][] = $result->Column_name;
            }
            
            // Convert to indexed array
            $indexes = array_values($indexes);
        } elseif ($driver === 'pgsql') {
            $results = DB::connection($connection)->select(
                "SELECT
                    i.relname as index_name,
                    a.attname as column_name,
                    ix.indisunique as is_unique,
                    ix.indisprimary as is_primary
                FROM
                    pg_class t,
                    pg_class i,
                    pg_index ix,
                    pg_attribute a
                WHERE
                    t.oid = ix.indrelid
                    AND i.oid = ix.indexrelid
                    AND a.attrelid = t.oid
                    AND a.attnum = ANY(ix.indkey)
                    AND t.relkind = 'r'
                    AND t.relname = '$table'
                ORDER BY
                    i.relname, a.attnum"
            );
            
            foreach ($results as $result) {
                $keyName = $result->index_name;
                
                if (!isset($indexes[$keyName])) {
                    $indexes[$keyName] = [
                        'name' => $keyName,
                        'columns' => [],
                        'unique' => $result->is_unique,
                        'primary' => $result->is_primary,
                    ];
                }
                
                $indexes[$keyName]['columns'][] = $result->column_name;
            }
            
            // Convert to indexed array
            $indexes = array_values($indexes);
        } elseif ($driver === 'sqlite') {
            $results = DB::connection($connection)->select(
                "PRAGMA index_list($table)"
            );
            
            foreach ($results as $result) {
                $indexName = $result->name;
                $indexInfo = DB::connection($connection)->select(
                    "PRAGMA index_info($indexName)"
                );
                
                $columns = [];
                foreach ($indexInfo as $info) {
                    $columns[] = $info->name;
                }
                
                $indexes[] = [
                    'name' => $indexName,
                    'columns' => $columns,
                    'unique' => $result->unique,
                ];
            }
        }
        
        return $indexes;
    }
    
    /**
     * Get the foreign keys for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The foreign keys
     */
    protected function getTableForeignKeys(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $foreignKeys = [];
        
        if ($driver === 'mysql') {
            $results = DB::connection($connection)->select(
                "SELECT
                    COLUMN_NAME as column_name,
                    REFERENCED_TABLE_NAME as referenced_table,
                    REFERENCED_COLUMN_NAME as referenced_column,
                    CONSTRAINT_NAME as constraint_name
                FROM
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                    TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = '$table'
                    AND REFERENCED_TABLE_NAME IS NOT NULL"
            );
            
            foreach ($results as $result) {
                $foreignKeys[] = [
                    'column' => $result->column_name,
                    'referenced_table' => $result->referenced_table,
                    'referenced_column' => $result->referenced_column,
                    'constraint_name' => $result->constraint_name,
                ];
            }
        } elseif ($driver === 'pgsql') {
            $results = DB::connection($connection)->select(
                "SELECT
                    tc.constraint_name,
                    kcu.column_name,
                    ccu.table_name AS referenced_table,
                    ccu.column_name AS referenced_column
                FROM
                    information_schema.table_constraints AS tc
                    JOIN information_schema.key_column_usage AS kcu
                      ON tc.constraint_name = kcu.constraint_name
                    JOIN information_schema.constraint_column_usage AS ccu
                      ON ccu.constraint_name = tc.constraint_name
                WHERE
                    tc.constraint_type = 'FOREIGN KEY'
                    AND tc.table_name = '$table'"
            );
            
            foreach ($results as $result) {
                $foreignKeys[] = [
                    'column' => $result->column_name,
                    'referenced_table' => $result->referenced_table,
                    'referenced_column' => $result->referenced_column,
                    'constraint_name' => $result->constraint_name,
                ];
            }
        } elseif ($driver === 'sqlite') {
            $results = DB::connection($connection)->select(
                "PRAGMA foreign_key_list($table)"
            );
            
            foreach ($results as $result) {
                $foreignKeys[] = [
                    'column' => $result->from,
                    'referenced_table' => $result->table,
                    'referenced_column' => $result->to,
                    'constraint_name' => null, // SQLite doesn't provide constraint names
                ];
            }
        }
        
        return $foreignKeys;
    }
    
    /**
     * Get the table size information.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The table size information
     */
    protected function getTableSizeDetails(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $size = [
            'data_size' => null,
            'index_size' => null,
            'total_size' => null,
            'row_count' => null,
        ];
        
        // Get row count for all drivers
        $rowCount = DB::connection($connection)->table($table)->count();
        $size['row_count'] = $rowCount;
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                "SELECT
                    data_length as data_size,
                    index_length as index_size,
                    data_length + index_length as total_size,
                    table_rows as row_estimate
                FROM
                    information_schema.tables
                WHERE
                    table_schema = DATABASE()
                    AND table_name = '$table'"
            );
            
            if (!empty($result)) {
                $size['data_size'] = $result[0]->data_size;
                $size['index_size'] = $result[0]->index_size;
                $size['total_size'] = $result[0]->total_size;
                // Use the actual count we got above instead of the estimate
            }
        } elseif ($driver === 'pgsql') {
            $result = DB::connection($connection)->select(
                "SELECT
                    pg_total_relation_size('$table') as total_size,
                    pg_relation_size('$table') as data_size,
                    pg_total_relation_size('$table') - pg_relation_size('$table') as index_size"
            );
            
            if (!empty($result)) {
                $size['data_size'] = $result[0]->data_size;
                $size['index_size'] = $result[0]->index_size;
                $size['total_size'] = $result[0]->total_size;
            }
        }
        // SQLite doesn't provide easy access to table size information
        
        return $size;
    }
    
    /**
     * Get slow queries from the database.
     *
     * @param float $threshold The threshold in seconds
     * @param int $limit The maximum number of queries to return
     * @param string|null $connection The connection name
     * @return array The slow queries
     */
    protected function getSlowQueriesFromLog(float $threshold = 1.0, int $limit = 100, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $slowQueries = [];
        
        if ($driver === 'mysql') {
            // This requires the slow query log to be enabled
            $results = DB::connection($connection)->select(
                "SELECT
                    start_time,
                    query_time,
                    sql_text
                FROM
                    mysql.slow_log
                WHERE
                    query_time > $threshold
                ORDER BY
                    query_time DESC
                LIMIT $limit"
            );
            
            foreach ($results as $result) {
                $slowQueries[] = [
                    'start_time' => $result->start_time,
                    'duration' => $result->query_time,
                    'query' => $result->sql_text,
                ];
            }
        }
        // PostgreSQL and SQLite require external logging solutions
        
        return $slowQueries;
    }
    
    /**
     * Get database server information.
     *
     * @param string|null $connection The connection name
     * @return array The database server information
     */
    protected function getDatabaseServerDetails(?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $info = [
            'driver' => $driver,
            'version' => null,
            'charset' => null,
            'collation' => null,
            'timezone' => null,
            'server_info' => null,
        ];
        
        if ($driver === 'mysql') {
            $results = DB::connection($connection)->select('SHOW VARIABLES');
            $variables = [];
            
            foreach ($results as $result) {
                $variables[$result->Variable_name] = $result->Value;
            }
            
            $info['version'] = $variables['version'] ?? null;
            $info['charset'] = $variables['character_set_database'] ?? null;
            $info['collation'] = $variables['collation_database'] ?? null;
            $info['timezone'] = $variables['time_zone'] ?? null;
            $info['server_info'] = $variables['version_comment'] ?? null;
        } elseif ($driver === 'pgsql') {
            $version = DB::connection($connection)->select('SELECT version()');
            $info['version'] = $version[0]->version ?? null;
            
            $charset = DB::connection($connection)->select('SHOW server_encoding');
            $info['charset'] = $charset[0]->server_encoding ?? null;
            
            $timezone = DB::connection($connection)->select('SHOW timezone');
            $info['timezone'] = $timezone[0]->timezone ?? null;
        } elseif ($driver === 'sqlite') {
            $version = DB::connection($connection)->select('SELECT sqlite_version()');
            $info['version'] = $version[0]->{'sqlite_version()'} ?? null;
        }
        
        return $info;
    }
    
    /**
     * Get database connection status.
     *
     * @param string|null $connection The connection name
     * @return array The connection status
     */
    protected function getDatabaseConnectionDetails(?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $status = [
            'connected' => true,
            'max_connections' => null,
            'current_connections' => null,
            'uptime' => null,
        ];
        
        try {
            // Test connection
            DB::connection($connection)->getPdo();
            
            if ($driver === 'mysql') {
                $variables = DB::connection($connection)->select('SHOW GLOBAL STATUS');
                $statusVars = [];
                
                foreach ($variables as $variable) {
                    $statusVars[$variable->Variable_name] = $variable->Value;
                }
                
                $maxConnections = DB::connection($connection)->select('SHOW VARIABLES LIKE \'max_connections\'');
                
                $status['max_connections'] = $maxConnections[0]->Value ?? null;
                $status['current_connections'] = $statusVars['Threads_connected'] ?? null;
                $status['uptime'] = $statusVars['Uptime'] ?? null;
            } elseif ($driver === 'pgsql') {
                $maxConnections = DB::connection($connection)->select('SHOW max_connections');
                $status['max_connections'] = $maxConnections[0]->max_connections ?? null;
                
                $currentConnections = DB::connection($connection)->select('SELECT count(*) as count FROM pg_stat_activity');
                $status['current_connections'] = $currentConnections[0]->count ?? null;
            }
        } catch (\Exception $e) {
            $status['connected'] = false;
        }
        
        return $status;
    }
    
    /**
     * Get database cache statistics.
     *
     * @param string|null $connection The connection name
     * @return array The cache statistics
     */
    protected function getDatabaseCacheStats(?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $stats = [
            'cache_hit_ratio' => null,
            'cache_hits' => null,
            'cache_misses' => null,
        ];
        
        if ($driver === 'mysql') {
            $results = DB::connection($connection)->select('SHOW GLOBAL STATUS LIKE \'Innodb_buffer_pool%\'');
            $statusVars = [];
            
            foreach ($results as $result) {
                $statusVars[$result->Variable_name] = $result->Value;
            }
            
            $hits = $statusVars['Innodb_buffer_pool_read_requests'] ?? 0;
            $misses = $statusVars['Innodb_buffer_pool_reads'] ?? 0;
            
            $stats['cache_hits'] = $hits;
            $stats['cache_misses'] = $misses;
            
            if (($hits + $misses) > 0) {
                $stats['cache_hit_ratio'] = $hits / ($hits + $misses) * 100;
            }
        } elseif ($driver === 'pgsql') {
            $results = DB::connection($connection)->select(
                "SELECT
                    sum(heap_blks_read) as heap_read,
                    sum(heap_blks_hit) as heap_hit
                FROM
                    pg_statio_user_tables"
            );
            
            if (!empty($results)) {
                $hits = $results[0]->heap_hit;
                $misses = $results[0]->heap_read;
                
                $stats['cache_hits'] = $hits;
                $stats['cache_misses'] = $misses;
                
                if (($hits + $misses) > 0) {
                    $stats['cache_hit_ratio'] = $hits / ($hits + $misses) * 100;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Get database locks information.
     *
     * @param string|null $connection The connection name
     * @return array The locks information
     */
    protected function getDatabaseLocks(?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $locks = [];
        
        if ($driver === 'mysql') {
            $results = DB::connection($connection)->select(
                "SELECT
                    p.id as process_id,
                    p.user,
                    p.host,
                    p.db as database_name,
                    p.command,
                    p.time as seconds,
                    p.state,
                    p.info as query,
                    bl.lock_id,
                    bl.lock_mode,
                    bl.lock_type,
                    bl.lock_table,
                    bl.lock_index
                FROM
                    information_schema.processlist p
                    LEFT JOIN information_schema.innodb_locks bl ON p.id = bl.lock_trx_id
                WHERE
                    bl.lock_id IS NOT NULL"
            );
            
            foreach ($results as $result) {
                $locks[] = (array) $result;
            }
        } elseif ($driver === 'pgsql') {
            $results = DB::connection($connection)->select(
                "SELECT
                    blocked_locks.pid as blocked_pid,
                    blocked_activity.usename as blocked_user,
                    blocking_locks.pid as blocking_pid,
                    blocking_activity.usename as blocking_user,
                    blocked_activity.query as blocked_query,
                    blocking_activity.query as blocking_query
                FROM
                    pg_catalog.pg_locks blocked_locks
                    JOIN pg_catalog.pg_stat_activity blocked_activity ON blocked_activity.pid = blocked_locks.pid
                    JOIN pg_catalog.pg_locks blocking_locks ON blocking_locks.locktype = blocked_locks.locktype
                        AND blocking_locks.database IS NOT DISTINCT FROM blocked_locks.database
                        AND blocking_locks.relation IS NOT DISTINCT FROM blocked_locks.relation
                        AND blocking_locks.page IS NOT DISTINCT FROM blocked_locks.page
                        AND blocking_locks.tuple IS NOT DISTINCT FROM blocked_locks.tuple
                        AND blocking_locks.virtualxid IS NOT DISTINCT FROM blocked_locks.virtualxid
                        AND blocking_locks.transactionid IS NOT DISTINCT FROM blocked_locks.transactionid
                        AND blocking_locks.classid IS NOT DISTINCT FROM blocked_locks.classid
                        AND blocking_locks.objid IS NOT DISTINCT FROM blocked_locks.objid
                        AND blocking_locks.objsubid IS NOT DISTINCT FROM blocked_locks.objsubid
                        AND blocking_locks.pid != blocked_locks.pid
                    JOIN pg_catalog.pg_stat_activity blocking_activity ON blocking_activity.pid = blocking_locks.pid
                WHERE
                    NOT blocked_locks.granted"
            );
            
            foreach ($results as $result) {
                $locks[] = (array) $result;
            }
        }
        
        return $locks;
    }
    
    /**
     * Get database query execution plan.
     *
     * @param string $query The SQL query to analyze
     * @param array $bindings The query bindings
     * @param string|null $connection The connection name
     * @return array The execution plan
     */
    protected function getDatabaseQueryPlan(string $query, array $bindings = [], ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $plan = [];
        
        if ($driver === 'mysql') {
            // MySQL EXPLAIN
            $results = DB::connection($connection)->select('EXPLAIN ' . $query, $bindings);
            
            foreach ($results as $result) {
                $plan[] = (array) $result;
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL EXPLAIN
            $results = DB::connection($connection)->select('EXPLAIN (FORMAT JSON) ' . $query, $bindings);
            
            if (!empty($results)) {
                $jsonPlan = json_decode($results[0]->{'QUERY PLAN'}, true);
                $plan = $jsonPlan[0]['Plan'] ?? [];
            }
        } elseif ($driver === 'sqlite') {
            // SQLite EXPLAIN QUERY PLAN
            $results = DB::connection($connection)->select('EXPLAIN QUERY PLAN ' . $query, $bindings);
            
            foreach ($results as $result) {
                $plan[] = (array) $result;
            }
        }
        
        return $plan;
    }
    
    /**
     * Get database table partitioning information.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The partitioning information
     */
    protected function getTablePartitionInfo(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $partitionInfo = [
            'is_partitioned' => false,
            'partition_method' => null,
            'partition_count' => 0,
            'partitions' => [],
        ];
        
        if ($driver === 'mysql') {
            $results = DB::connection($connection)->select(
                "SELECT
                    p.PARTITION_METHOD,
                    COUNT(p.PARTITION_NAME) as partition_count
                FROM
                    information_schema.PARTITIONS p
                WHERE
                    p.TABLE_SCHEMA = DATABASE()
                    AND p.TABLE_NAME = ?
                GROUP BY
                    p.PARTITION_METHOD",
                [$table]
            );
            
            if (!empty($results)) {
                $partitionInfo['is_partitioned'] = true;
                $partitionInfo['partition_method'] = $results[0]->PARTITION_METHOD;
                $partitionInfo['partition_count'] = $results[0]->partition_count;
                
                $partitions = DB::connection($connection)->select(
                    "SELECT
                        PARTITION_NAME,
                        PARTITION_ORDINAL_POSITION,
                        PARTITION_DESCRIPTION,
                        TABLE_ROWS,
                        AVG_ROW_LENGTH,
                        DATA_LENGTH,
                        INDEX_LENGTH
                    FROM
                        information_schema.PARTITIONS
                    WHERE
                        TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = ?
                        AND PARTITION_NAME IS NOT NULL
                    ORDER BY
                        PARTITION_ORDINAL_POSITION",
                    [$table]
                );
                
                foreach ($partitions as $partition) {
                    $partitionInfo['partitions'][] = (array) $partition;
                }
            }
        } elseif ($driver === 'pgsql') {
            // Check if table is partitioned in PostgreSQL
            $result = DB::connection($connection)->select(
                "SELECT
                    c.relispartition,
                    c.relkind
                FROM
                    pg_class c
                    JOIN pg_namespace n ON n.oid = c.relnamespace
                WHERE
                    n.nspname = 'public'
                    AND c.relname = ?",
                [$table]
            );
            
            if (!empty($result) && isset($result[0]->relispartition) && $result[0]->relispartition) {
                $partitionInfo['is_partitioned'] = true;
                
                // Get partition information
                $partitions = DB::connection($connection)->select(
                    "SELECT
                        child.relname as partition_name,
                        pg_get_expr(child.relpartbound, child.oid) as partition_expression
                    FROM
                        pg_inherits
                        JOIN pg_class parent ON pg_inherits.inhparent = parent.oid
                        JOIN pg_class child ON pg_inherits.inhrelid = child.oid
                        JOIN pg_namespace nmsp_parent ON nmsp_parent.oid = parent.relnamespace
                        JOIN pg_namespace nmsp_child ON nmsp_child.oid = child.relnamespace
                    WHERE
                        parent.relname = ?
                        AND nmsp_parent.nspname = 'public'
                    ORDER BY
                        child.relname",
                    [$table]
                );
                
                $partitionInfo['partition_count'] = count($partitions);
                
                foreach ($partitions as $partition) {
                    $partitionInfo['partitions'][] = (array) $partition;
                }
            }
        }
        
        return $partitionInfo;
    }
    
    /**
     * Get database table storage information.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The storage information
     */
    protected function getTableStorageInfo(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $storageInfo = [
            'engine' => null,
            'row_format' => null,
            'avg_row_length' => null,
            'data_length' => null,
            'index_length' => null,
            'data_free' => null,
            'auto_increment' => null,
            'create_time' => null,
            'update_time' => null,
            'collation' => null,
        ];
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                "SHOW TABLE STATUS WHERE Name = ?",
                [$table]
            );
            
            if (!empty($result)) {
                $info = $result[0];
                $storageInfo['engine'] = $info->Engine;
                $storageInfo['row_format'] = $info->Row_format;
                $storageInfo['avg_row_length'] = $info->Avg_row_length;
                $storageInfo['data_length'] = $info->Data_length;
                $storageInfo['index_length'] = $info->Index_length;
                $storageInfo['data_free'] = $info->Data_free;
                $storageInfo['auto_increment'] = $info->Auto_increment;
                $storageInfo['create_time'] = $info->Create_time;
                $storageInfo['update_time'] = $info->Update_time;
                $storageInfo['collation'] = $info->Collation;
            }
        } elseif ($driver === 'pgsql') {
            // Get basic table information
            $result = DB::connection($connection)->select(
                "SELECT
                    pg_relation_size(?) as data_length,
                    pg_indexes_size(?) as index_length,
                    pg_total_relation_size(?) as total_length,
                    obj_description(?) as comment,
                    (SELECT reltuples FROM pg_class WHERE relname = ?) as row_estimate
                ",
                [$table, $table, $table, $table::class, $table]
            );
            
            if (!empty($result)) {
                $info = $result[0];
                $storageInfo['data_length'] = $info->data_length;
                $storageInfo['index_length'] = $info->index_length;
                $storageInfo['row_estimate'] = $info->row_estimate;
            }
            
            // Get table creation time
            $createTime = DB::connection($connection)->select(
                "SELECT
                    c.relname as table_name,
                    CASE WHEN c.relkind = 'r' THEN 'table' ELSE c.relkind::text END as type,
                    pg_catalog.pg_get_userbyid(c.relowner) as owner,
                    CASE WHEN c.relhasindex THEN 'true' ELSE 'false' END as has_indexes,
                    to_char(c.reltuples, '999G999G999G999') as row_estimate,
                    pg_size_pretty(pg_relation_size(c.oid)) as size,
                    to_char(GREATEST(c.relfrozenxid, 0), 'FM9,999,999,999,999,999') as frozen_xid,
                    to_char(GREATEST(c.relminmxid, 0), 'FM9,999,999,999,999,999') as min_mxid,
                    array_to_string(c.reloptions, ', ') as storage_params
                FROM
                    pg_catalog.pg_class c
                    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                WHERE
                    c.relkind = 'r'
                    AND n.nspname = 'public'
                    AND c.relname = ?
                ORDER BY
                    c.relpages DESC",
                [$table]
            );
            
            if (!empty($createTime)) {
                $storageInfo['storage_params'] = $createTime[0]->storage_params;
                $storageInfo['owner'] = $createTime[0]->owner;
                $storageInfo['has_indexes'] = $createTime[0]->has_indexes;
            }
        } elseif ($driver === 'sqlite') {
            // Get table info from SQLite
            $result = DB::connection($connection)->select("PRAGMA table_info(?)", [$table]);
            $storageInfo['column_count'] = count($result);
            
            // Get index information
            $indexes = DB::connection($connection)->select("PRAGMA index_list(?)", [$table]);
            $storageInfo['index_count'] = count($indexes);
        }
        
        return $storageInfo;
    }
    
    /**
     * Get database table fragmentation information.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The fragmentation information
     */
    protected function getTableFragmentationInfo(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $fragmentationInfo = [
            'fragmentation_percentage' => null,
            'fragmented_pages' => null,
            'total_pages' => null,
            'fragmented_space' => null,
            'total_space' => null,
        ];
        
        if ($driver === 'mysql') {
            // For InnoDB tables, we can check data_free
            $result = DB::connection($connection)->select(
                "SHOW TABLE STATUS WHERE Name = ?",
                [$table]
            );
            
            if (!empty($result) && $result[0]->Engine === 'InnoDB') {
                $dataLength = (float) $result[0]->Data_length;
                $dataFree = (float) $result[0]->Data_free;
                $totalSpace = $dataLength + $dataFree;
                
                if ($totalSpace > 0) {
                    $fragmentationInfo['fragmentation_percentage'] = ($dataFree / $totalSpace) * 100;
                    $fragmentationInfo['fragmented_space'] = $dataFree;
                    $fragmentationInfo['total_space'] = $totalSpace;
                }
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL fragmentation can be estimated with bloat queries
            $result = DB::connection($connection)->select(
                "SELECT
                    current_database() as db_name,
                    schemaname,
                    tablename,
                    reltuples::bigint as row_estimate,
                    pg_total_relation_size(schemaname || '.' || tablename) as total_bytes,
                    pg_relation_size(schemaname || '.' || tablename) as table_bytes,
                    (pg_total_relation_size(schemaname || '.' || tablename) - pg_relation_size(schemaname || '.' || tablename)) as index_bytes,
                    pg_total_relation_size(schemaname || '.' || tablename) / (reltuples + 1) as bytes_per_row
                FROM
                    pg_class c
                    JOIN pg_namespace n ON n.oid = c.relnamespace
                    JOIN pg_stat_user_tables psut ON c.relname = psut.relname
                WHERE
                    relkind = 'r'
                    AND n.nspname = 'public'
                    AND c.relname = ?
                ORDER BY
                    pg_total_relation_size(schemaname || '.' || tablename) DESC",
                [$table]
            );
            
            if (!empty($result)) {
                $info = $result[0];
                $fragmentationInfo['total_space'] = $info->total_bytes;
                $fragmentationInfo['table_space'] = $info->table_bytes;
                $fragmentationInfo['index_space'] = $info->index_bytes;
                $fragmentationInfo['row_estimate'] = $info->row_estimate;
                $fragmentationInfo['bytes_per_row'] = $info->bytes_per_row;
            }
        }
        
        return $fragmentationInfo;
    }
    
    /**
     * Get database table growth information.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @param int $days Number of days to analyze growth (MySQL only)
     * @return array The growth information
     */
    protected function getTableGrowthInfo(string $table, ?string $connection = null, int $days = 30): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $growthInfo = [
            'current_size' => null,
            'growth_rate' => null,
            'estimated_size_next_month' => null,
            'history' => [],
        ];
        
        // Get current size
        $currentSize = 0;
        
        if ($driver === 'mysql') {
            $result = DB::connection($connection)->select(
                "SHOW TABLE STATUS WHERE Name = ?",
                [$table]
            );
            
            if (!empty($result)) {
                $currentSize = $result[0]->Data_length + $result[0]->Index_length;
                $growthInfo['current_size'] = $currentSize;
                
                // For MySQL, we can check if there's a table_stats table that tracks growth
                // This is a hypothetical example as you would need to implement a table_stats tracking system
                try {
                    $history = DB::connection($connection)->select(
                        "SELECT
                            date,
                            table_size
                        FROM
                            table_stats
                        WHERE
                            table_name = ?
                            AND date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                        ORDER BY
                            date ASC",
                        [$table, $days]
                    );
                    
                    if (!empty($history)) {
                        foreach ($history as $record) {
                            $growthInfo['history'][] = [
                                'date' => $record->date,
                                'size' => $record->table_size,
                            ];
                        }
                        
                        // Calculate growth rate if we have at least 2 data points
                        if (count($history) >= 2) {
                            $firstSize = $history[0]->table_size;
                            $lastSize = $history[count($history) - 1]->table_size;
                            $daysDiff = (strtotime($history[count($history) - 1]->date) - strtotime($history[0]->date)) / (60 * 60 * 24);
                            
                            if ($daysDiff > 0) {
                                $growthRate = ($lastSize - $firstSize) / $daysDiff; // bytes per day
                                $growthInfo['growth_rate'] = $growthRate;
                                $growthInfo['estimated_size_next_month'] = $currentSize + ($growthRate * 30);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Table stats table doesn't exist
                }
            }
        } elseif ($driver === 'pgsql') {
            $result = DB::connection($connection)->select(
                "SELECT
                    pg_total_relation_size(?) as total_size",
                [$table]
            );
            
            if (!empty($result)) {
                $currentSize = $result[0]->total_size;
                $growthInfo['current_size'] = $currentSize;
            }
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't provide direct table size information
            // We could estimate based on row count and average row size
            $result = DB::connection($connection)->select("PRAGMA table_info(?)", [$table]);
            $rowCount = DB::connection($connection)->table($table)->count();
            
            // Rough estimate
            $growthInfo['row_count'] = $rowCount;
            $growthInfo['column_count'] = count($result);
        }
        
        return $growthInfo;
    }
    
    /**
     * Get database table usage statistics.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The usage statistics
     */
    protected function getTableUsageStats(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $usageStats = [
            'row_count' => null,
            'read_count' => null,
            'write_count' => null,
            'last_analyzed' => null,
            'last_vacuum' => null,
            'sequential_scans' => null,
            'index_scans' => null,
        ];
        
        if ($driver === 'mysql') {
            // Get row count
            $result = DB::connection($connection)->select(
                "SHOW TABLE STATUS WHERE Name = ?",
                [$table]
            );
            
            if (!empty($result)) {
                $usageStats['row_count'] = $result[0]->Rows;
            }
            
            // For MySQL, we can check the information_schema for usage statistics
            try {
                $stats = DB::connection($connection)->select(
                    "SELECT
                        table_rows,
                        data_length,
                        index_length,
                        update_time
                    FROM
                        information_schema.tables
                    WHERE
                        table_schema = DATABASE()
                        AND table_name = ?",
                    [$table]
                );
                
                if (!empty($stats)) {
                    $usageStats['row_count'] = $stats[0]->table_rows;
                    $usageStats['last_analyzed'] = $stats[0]->update_time;
                }
            } catch (\Exception $e) {
                // Table doesn't exist in information_schema
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL provides detailed statistics
            $result = DB::connection($connection)->select(
                "SELECT
                    reltuples::bigint as row_estimate,
                    n_tup_ins as inserts,
                    n_tup_upd as updates,
                    n_tup_del as deletes,
                    n_live_tup as live_rows,
                    n_dead_tup as dead_rows,
                    seq_scan as sequential_scans,
                    idx_scan as index_scans,
                    last_vacuum,
                    last_analyze
                FROM
                    pg_stat_user_tables
                WHERE
                    relname = ?",
                [$table]
            );
            
            if (!empty($result)) {
                $info = $result[0];
                $usageStats['row_count'] = $info->live_rows;
                $usageStats['write_count'] = $info->inserts + $info->updates + $info->deletes;
                $usageStats['sequential_scans'] = $info->sequential_scans;
                $usageStats['index_scans'] = $info->index_scans;
                $usageStats['last_vacuum'] = $info->last_vacuum;
                $usageStats['last_analyzed'] = $info->last_analyze;
                $usageStats['dead_rows'] = $info->dead_rows;
            }
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't provide usage statistics
            // We can only get the row count
            $usageStats['row_count'] = DB::connection($connection)->table($table)->count();
        }
        
        return $usageStats;
    }
    
    /**
     * Get database index usage statistics.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The index usage statistics
     */
    protected function getIndexUsageStats(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $indexStats = [];
        
        if ($driver === 'mysql') {
            // MySQL doesn't provide detailed index usage statistics
            // We can only get the index definitions
            $indexes = DB::connection($connection)->select(
                "SHOW INDEX FROM " . $table
            );
            
            $indexNames = [];
            foreach ($indexes as $index) {
                if (!in_array($index->Key_name, $indexNames)) {
                    $indexNames[] = $index->Key_name;
                    $indexStats[$index->Key_name] = [
                        'name' => $index->Key_name,
                        'unique' => $index->Non_unique == 0,
                        'type' => $index->Index_type,
                        'cardinality' => $index->Cardinality,
                    ];
                }
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL provides detailed index usage statistics
            $indexes = DB::connection($connection)->select(
                "SELECT
                    indexrelname as index_name,
                    idx_scan as index_scans,
                    idx_tup_read as tuples_read,
                    idx_tup_fetch as tuples_fetched,
                    pg_relation_size(indexrelid) as index_size
                FROM
                    pg_stat_user_indexes
                WHERE
                    relname = ?
                ORDER BY
                    idx_scan DESC",
                [$table]
            );
            
            foreach ($indexes as $index) {
                $indexStats[$index->index_name] = [
                    'name' => $index->index_name,
                    'scans' => $index->index_scans,
                    'tuples_read' => $index->tuples_read,
                    'tuples_fetched' => $index->tuples_fetched,
                    'size' => $index->index_size,
                ];
            }
            
            // Get index definitions
            $definitions = DB::connection($connection)->select(
                "SELECT
                    i.relname as index_name,
                    a.attname as column_name,
                    ix.indisunique as is_unique,
                    ix.indisprimary as is_primary,
                    am.amname as index_type
                FROM
                    pg_index ix
                    JOIN pg_class i ON i.oid = ix.indexrelid
                    JOIN pg_class t ON t.oid = ix.indrelid
                    JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(ix.indkey)
                    JOIN pg_am am ON am.oid = i.relam
                WHERE
                    t.relname = ?
                ORDER BY
                    i.relname, a.attnum",
                [$table]
            );
            
            foreach ($definitions as $def) {
                if (isset($indexStats[$def->index_name])) {
                    if (!isset($indexStats[$def->index_name]['columns'])) {
                        $indexStats[$def->index_name]['columns'] = [];
                        $indexStats[$def->index_name]['unique'] = $def->is_unique;
                        $indexStats[$def->index_name]['primary'] = $def->is_primary;
                        $indexStats[$def->index_name]['type'] = $def->index_type;
                    }
                    $indexStats[$def->index_name]['columns'][] = $def->column_name;
                }
            }
        } elseif ($driver === 'sqlite') {
            // SQLite provides basic index information
            $indexes = DB::connection($connection)->select("PRAGMA index_list(?)", [$table]);
            
            foreach ($indexes as $index) {
                $columns = DB::connection($connection)->select("PRAGMA index_info(?)", [$index->name]);
                $columnNames = [];
                
                foreach ($columns as $column) {
                    $columnNames[] = $column->name;
                }
                
                $indexStats[$index->name] = [
                    'name' => $index->name,
                    'unique' => $index->unique == 1,
                    'columns' => $columnNames,
                ];
            }
        }
        
        return $indexStats;
    }
    
    /**
     * Get unused indexes for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @param int $minTableSize Minimum table size in rows to consider (PostgreSQL only)
     * @return array The unused indexes
     */
    protected function getUnusedIndexes(string $table, ?string $connection = null, int $minTableSize = 1000): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $unusedIndexes = [];
        
        if ($driver === 'pgsql') {
            // PostgreSQL can identify unused indexes
            $indexes = DB::connection($connection)->select(
                "SELECT
                    s.relname as table_name,
                    si.indexrelname as index_name,
                    si.idx_scan as index_scans,
                    pg_relation_size(si.indexrelid) as index_size,
                    (SELECT n_live_tup FROM pg_stat_user_tables WHERE relname = s.relname) as table_rows
                FROM
                    pg_stat_user_indexes si
                    JOIN pg_stat_user_tables s ON s.relname = si.relname
                WHERE
                    s.relname = ?
                    AND si.idx_scan = 0
                    AND (SELECT n_live_tup FROM pg_stat_user_tables WHERE relname = s.relname) >= ?
                    AND NOT EXISTS (
                        SELECT 1 FROM pg_constraint c
                        WHERE c.conindid = si.indexrelid AND c.contype IN ('p', 'u')
                    )
                ORDER BY
                    si.indexrelname",
                [$table, $minTableSize]
            );
            
            foreach ($indexes as $index) {
                $unusedIndexes[] = [
                    'name' => $index->index_name,
                    'table' => $index->table_name,
                    'size' => $index->index_size,
                    'table_rows' => $index->table_rows,
                ];
            }
        }
        // MySQL and SQLite don't provide reliable unused index detection
        
        return $unusedIndexes;
    }
    
    /**
     * Get duplicate indexes for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The duplicate indexes
     */
    protected function getDuplicateIndexes(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $duplicateIndexes = [];
        
        if ($driver === 'pgsql') {
            // PostgreSQL can identify duplicate indexes
            $indexes = DB::connection($connection)->select(
                "WITH index_cols AS (
                    SELECT
                        i.relname as index_name,
                        array_agg(a.attname ORDER BY array_position(ix.indkey, a.attnum)) as columns,
                        ix.indisunique as is_unique,
                        ix.indisprimary as is_primary,
                        t.relname as table_name
                    FROM
                        pg_index ix
                        JOIN pg_class i ON i.oid = ix.indexrelid
                        JOIN pg_class t ON t.oid = ix.indrelid
                        JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(ix.indkey)
                    WHERE
                        t.relname = ?
                    GROUP BY
                        i.relname, ix.indisunique, ix.indisprimary, t.relname
                )
                SELECT
                    array_agg(index_name) as index_names,
                    columns,
                    count(*) as index_count,
                    table_name
                FROM
                    index_cols
                GROUP BY
                    columns, table_name, is_unique, is_primary
                HAVING
                    count(*) > 1
                ORDER BY
                    table_name, index_count DESC",
                [$table]
            );
            
            foreach ($indexes as $index) {
                $indexNames = explode(',', trim($index->index_names, '{}'));
                $columns = explode(',', trim($index->columns, '{}'));
                
                $duplicateIndexes[] = [
                    'table' => $index->table_name,
                    'index_names' => $indexNames,
                    'columns' => $columns,
                    'index_count' => $index->index_count,
                ];
            }
        }
        // MySQL and SQLite don't provide reliable duplicate index detection
        
        return $duplicateIndexes;
    }
    
    /**
     * Get missing indexes for a table based on query patterns.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The suggested missing indexes
     */
    protected function getMissingIndexes(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $missingIndexes = [];
        
        if ($driver === 'mysql') {
            // MySQL can suggest missing indexes through the performance_schema
            try {
                $suggestions = DB::connection($connection)->select(
                    "SELECT
                        table_schema,
                        table_name,
                        index_columns,
                        non_unique,
                        seq_in_index
                    FROM
                        performance_schema.schema_unused_indexes
                    WHERE
                        table_schema = DATABASE()
                        AND table_name = ?",
                    [$table]
                );
                
                foreach ($suggestions as $suggestion) {
                    $missingIndexes[] = [
                        'table' => $suggestion->table_name,
                        'columns' => $suggestion->index_columns,
                        'non_unique' => $suggestion->non_unique,
                    ];
                }
            } catch (\Exception $e) {
                // Performance schema might not be enabled
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL can suggest missing indexes through pg_stat_statements
            try {
                $suggestions = DB::connection($connection)->select(
                    "SELECT
                        s.relname as table_name,
                        a.attname as column_name,
                        s.seq_scan - s.idx_scan as too_much_seq,
                        CASE WHEN s.idx_scan = 0 THEN 0 ELSE s.seq_scan::float / s.idx_scan END as seq_to_idx_ratio,
                        s.seq_scan,
                        s.idx_scan,
                        s.n_live_tup as row_count
                    FROM
                        pg_stat_user_tables s
                        JOIN pg_attribute a ON a.attrelid = s.relid
                    WHERE
                        s.relname = ?
                        AND a.attnum > 0
                        AND NOT a.attisdropped
                        AND s.seq_scan > 50
                        AND s.seq_scan::float / (s.idx_scan + 1) > 3
                        AND s.n_live_tup > 10000
                        AND NOT EXISTS (
                            SELECT 1
                            FROM pg_index i
                            WHERE i.indrelid = s.relid AND a.attnum = ANY(i.indkey)
                        )
                    ORDER BY
                        too_much_seq DESC,
                        seq_to_idx_ratio DESC",
                    [$table]
                );
                
                foreach ($suggestions as $suggestion) {
                    $missingIndexes[] = [
                        'table' => $suggestion->table_name,
                        'column' => $suggestion->column_name,
                        'seq_scan' => $suggestion->seq_scan,
                        'idx_scan' => $suggestion->idx_scan,
                        'row_count' => $suggestion->row_count,
                    ];
                }
            } catch (\Exception $e) {
                // pg_stat_statements might not be enabled
            }
        }
        
        return $missingIndexes;
    }
    
    /**
     * Get query performance statistics for a specific table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @param int $limit Maximum number of queries to return
     * @return array The query performance statistics
     */
    protected function getTableQueryPerformance(string $table, ?string $connection = null, int $limit = 10): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $queryStats = [];
        
        if ($driver === 'mysql') {
            // MySQL can provide query performance statistics through performance_schema
            try {
                $stats = DB::connection($connection)->select(
                    "SELECT
                        DIGEST_TEXT as query_text,
                        COUNT_STAR as execution_count,
                        SUM_TIMER_WAIT/1000000000 as total_latency_ms,
                        AVG_TIMER_WAIT/1000000000 as avg_latency_ms,
                        MAX_TIMER_WAIT/1000000000 as max_latency_ms,
                        SUM_ROWS_EXAMINED as rows_examined,
                        SUM_ROWS_SENT as rows_sent
                    FROM
                        performance_schema.events_statements_summary_by_digest
                    WHERE
                        DIGEST_TEXT LIKE ?
                    ORDER BY
                        total_latency_ms DESC
                    LIMIT ?",
                    ['%' . $table . '%', $limit]
                );
                
                foreach ($stats as $stat) {
                    $queryStats[] = [
                        'query' => $stat->query_text,
                        'execution_count' => $stat->execution_count,
                        'total_latency_ms' => $stat->total_latency_ms,
                        'avg_latency_ms' => $stat->avg_latency_ms,
                        'max_latency_ms' => $stat->max_latency_ms,
                        'rows_examined' => $stat->rows_examined,
                        'rows_sent' => $stat->rows_sent,
                    ];
                }
            } catch (\Exception $e) {
                // Performance schema might not be enabled
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL can provide query performance statistics through pg_stat_statements
            try {
                $stats = DB::connection($connection)->select(
                    "SELECT
                        query,
                        calls as execution_count,
                        total_time as total_latency_ms,
                        mean_time as avg_latency_ms,
                        max_time as max_latency_ms,
                        rows as rows_affected
                    FROM
                        pg_stat_statements
                    WHERE
                        query ILIKE ?
                    ORDER BY
                        total_time DESC
                    LIMIT ?",
                    ['%' . $table . '%', $limit]
                );
                
                foreach ($stats as $stat) {
                    $queryStats[] = [
                        'query' => $stat->query,
                        'execution_count' => $stat->execution_count,
                        'total_latency_ms' => $stat->total_latency_ms,
                        'avg_latency_ms' => $stat->avg_latency_ms,
                        'max_latency_ms' => $stat->max_latency_ms,
                        'rows_affected' => $stat->rows_affected,
                    ];
                }
            } catch (\Exception $e) {
                // pg_stat_statements might not be enabled
            }
        }
        
        return $queryStats;
    }
    
    /**
     * Get table optimization recommendations.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return array The optimization recommendations
     */
    protected function getTableOptimizationRecommendations(string $table, ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $recommendations = [];
        
        // Check for unused indexes
        $unusedIndexes = $this->getUnusedIndexes($table, $connection);
        if (!empty($unusedIndexes)) {
            $recommendations[] = [
                'type' => 'unused_indexes',
                'description' => 'Consider removing unused indexes to improve write performance',
                'details' => $unusedIndexes,
            ];
        }
        
        // Check for duplicate indexes
        $duplicateIndexes = $this->getDuplicateIndexes($table, $connection);
        if (!empty($duplicateIndexes)) {
            $recommendations[] = [
                'type' => 'duplicate_indexes',
                'description' => 'Consider removing duplicate indexes to improve write performance',
                'details' => $duplicateIndexes,
            ];
        }
        
        // Check for missing indexes
        $missingIndexes = $this->getMissingIndexes($table, $connection);
        if (!empty($missingIndexes)) {
            $recommendations[] = [
                'type' => 'missing_indexes',
                'description' => 'Consider adding indexes to improve read performance',
                'details' => $missingIndexes,
            ];
        }
        
        // Check for table fragmentation (MySQL and PostgreSQL only)
        if ($driver === 'mysql' || $driver === 'pgsql') {
            $fragmentationInfo = $this->getTableFragmentationInfo($table, $connection);
            if (isset($fragmentationInfo['fragmentation_percentage']) && $fragmentationInfo['fragmentation_percentage'] > 30) {
                $recommendations[] = [
                    'type' => 'table_fragmentation',
                    'description' => 'Consider rebuilding or reorganizing the table to reduce fragmentation',
                    'details' => $fragmentationInfo,
                ];
            }
        }
        
        // Check for large tables without partitioning (MySQL and PostgreSQL only)
        if (($driver === 'mysql' || $driver === 'pgsql')) {
            $tableSize = $this->getTableSizeDetails($table, $connection);
            $partitionInfo = $this->getTablePartitionInfo($table, $connection);
            
            if (isset($tableSize['size_in_mb']) && $tableSize['size_in_mb'] > 1000 && empty($partitionInfo['partitions'])) {
                $recommendations[] = [
                    'type' => 'large_table_no_partitioning',
                    'description' => 'Consider partitioning large tables to improve query performance',
                    'details' => [
                        'table_size' => $tableSize,
                        'partition_info' => $partitionInfo,
                    ],
                ];
            }
        }
        
        // Check for columns with high null percentage
        $columns = $this->getTableColumns($table, $connection);
        foreach ($columns as $column) {
            $nullPercentage = $this->getColumnNullPercentage($table, $column, $connection);
            if ($nullPercentage > 80) {
                $recommendations[] = [
                    'type' => 'high_null_percentage',
                    'description' => "Column '{$column}' has a high percentage of NULL values ({$nullPercentage}%). Consider if this column is necessary or could be moved to a separate table.",
                    'details' => [
                        'column' => $column,
                        'null_percentage' => $nullPercentage,
                    ],
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Generate a database health report.
     *
     * @param string|null $connection The connection name
     * @return array The database health report
     */
    protected function generateDatabaseHealthReport(?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $report = [
            'database_info' => $this->getDatabaseServerDetails($connection),
            'connection_status' => $this->getDatabaseConnectionDetails($connection),
            'tables' => [],
            'slow_queries' => $this->getSlowQueriesFromLog($connection),
            'locks' => $this->getDatabaseLocks($connection),
            'recommendations' => [],
        ];
        
        // Get all tables
        $tables = $this->getDatabaseTables($connection);
        
        // Analyze each table
        foreach ($tables as $table) {
            $tableInfo = [
                'name' => $table,
                'size' => $this->getTableSizeDetails($table, $connection),
                'row_count' => $this->getTableRowCount($table, $connection),
                'columns' => count($this->getTableColumns($table, $connection)),
                'indexes' => count($this->getTableIndexes($table, $connection)),
                'foreign_keys' => count($this->getTableForeignKeys($table, $connection)),
                'usage_stats' => $this->getTableUsageStats($table, $connection),
            ];
            
            $report['tables'][] = $tableInfo;
            
            // Get optimization recommendations for each table
            $recommendations = $this->getTableOptimizationRecommendations($table, $connection);
            if (!empty($recommendations)) {
                $report['recommendations'][] = [
                    'table' => $table,
                    'recommendations' => $recommendations,
                ];
            }
        }
        
        // Sort tables by size
        usort($report['tables'], function ($a, $b) {
            return ($b['size']['size_in_bytes'] ?? 0) <=> ($a['size']['size_in_bytes'] ?? 0);
        });
        
        return $report;
    }
    
    /**
     * Get the row count of a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return int The row count
     */
    protected function getTableRowCount(string $table, ?string $connection = null): int
    {
        $driver = $this->getDatabaseDriver($connection);
        
        if ($driver === 'mysql') {
            // For MySQL, we can get an approximate row count from information_schema
            // which is much faster for large tables
            $result = DB::connection($connection)->select(
                "SELECT
                    table_rows
                FROM
                    information_schema.tables
                WHERE
                    table_schema = DATABASE()
                    AND table_name = ?",
                [$table]
            );
            
            if (!empty($result)) {
                return (int) $result[0]->table_rows;
            }
        } elseif ($driver === 'pgsql') {
            // For PostgreSQL, we can get an approximate row count from pg_stat_user_tables
            $result = DB::connection($connection)->select(
                "SELECT
                    n_live_tup as row_count
                FROM
                    pg_stat_user_tables
                WHERE
                    relname = ?",
                [$table]
            );
            
            if (!empty($result)) {
                return (int) $result[0]->row_count;
            }
        }
        
        // Fallback to COUNT(*) which can be slow for large tables
        return DB::connection($connection)->table($table)->count();
    }
    
    /**
     * Optimize a database table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return bool Whether the optimization was successful
     */
    protected function optimizeTable(string $table, ?string $connection = null): bool
    {
        $driver = $this->getDatabaseDriver($connection);
        
        try {
            if ($driver === 'mysql') {
                // MySQL supports OPTIMIZE TABLE directly
                DB::connection($connection)->statement("OPTIMIZE TABLE {$table}");
                return true;
            } elseif ($driver === 'pgsql') {
                // PostgreSQL uses VACUUM and ANALYZE
                DB::connection($connection)->statement("VACUUM ANALYZE {$table}");
                return true;
            } elseif ($driver === 'sqlite') {
                // SQLite uses VACUUM
                DB::connection($connection)->statement("VACUUM");
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return false;
    }
    
    /**
     * Rebuild indexes for a table.
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @return bool Whether the rebuild was successful
     */
    protected function rebuildTableIndexes(string $table, ?string $connection = null): bool
    {
        $driver = $this->getDatabaseDriver($connection);
        
        try {
            if ($driver === 'mysql') {
                // MySQL can rebuild indexes with ALTER TABLE ... ENGINE=InnoDB
                DB::connection($connection)->statement("ALTER TABLE {$table} ENGINE=InnoDB");
                return true;
            } elseif ($driver === 'pgsql') {
                // PostgreSQL can rebuild indexes with REINDEX
                DB::connection($connection)->statement("REINDEX TABLE {$table}");
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return false;
    }
    
    /**
     * Truncate a table (remove all rows).
     *
     * @param string $table The table name
     * @param string|null $connection The connection name
     * @param bool $cascade Whether to cascade the truncation to related tables (PostgreSQL only)
     * @return bool Whether the truncation was successful
     */
    protected function truncateTable(string $table, ?string $connection = null, bool $cascade = false): bool
    {
        $driver = $this->getDatabaseDriver($connection);
        
        try {
            if ($driver === 'pgsql' && $cascade) {
                DB::connection($connection)->statement("TRUNCATE TABLE {$table} CASCADE");
            } else {
                DB::connection($connection)->table($table)->truncate();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get the estimated query execution time.
     *
     * @param string $query The SQL query
     * @param array $bindings The query bindings
     * @param string|null $connection The connection name
     * @return float|null The estimated execution time in milliseconds, or null if not available
     */
    protected function getQueryExecutionTime(string $query, array $bindings = [], ?string $connection = null): ?float
    {
        $driver = $this->getDatabaseDriver($connection);
        $executionTime = null;
        
        try {
            if ($driver === 'mysql') {
                // Enable profiling for this session
                DB::connection($connection)->statement("SET profiling = 1");
                
                // Execute the query
                DB::connection($connection)->select($query, $bindings);
                
                // Get the profile information
                $profile = DB::connection($connection)->select("SHOW PROFILE");
                
                // Calculate total duration
                $executionTime = 0;
                foreach ($profile as $row) {
                    $executionTime += (float) $row->Duration;
                }
                
                // Convert to milliseconds
                $executionTime *= 1000;
                
                // Disable profiling
                DB::connection($connection)->statement("SET profiling = 0");
            } elseif ($driver === 'pgsql') {
                // PostgreSQL can use EXPLAIN ANALYZE
                $result = DB::connection($connection)->select("EXPLAIN (ANALYZE, FORMAT JSON) {$query}", $bindings);
                
                if (!empty($result)) {
                    $explainData = json_decode($result[0]->{'QUERY PLAN'});
                    $executionTime = $explainData[0]->{'Execution Time'};
                }
            }
        } catch (\Exception $e) {
            // Query execution failed
            return null;
        }
        
        return $executionTime;
    }
    
    /**
     * Get the query execution plan.
     *
     * @param string $query The SQL query
     * @param array $bindings The query bindings
     * @param string|null $connection The connection name
     * @return array The query execution plan
     */
    protected function getQueryExecutionPlan(string $query, array $bindings = [], ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $plan = [];
        
        try {
            if ($driver === 'mysql') {
                // MySQL uses EXPLAIN
                $result = DB::connection($connection)->select("EXPLAIN {$query}", $bindings);
                
                foreach ($result as $row) {
                    $plan[] = (array) $row;
                }
            } elseif ($driver === 'pgsql') {
                // PostgreSQL can use EXPLAIN with more detailed output
                $result = DB::connection($connection)->select("EXPLAIN (FORMAT JSON) {$query}", $bindings);
                
                if (!empty($result)) {
                    $plan = json_decode($result[0]->{'QUERY PLAN'}, true);
                }
            } elseif ($driver === 'sqlite') {
                // SQLite uses EXPLAIN QUERY PLAN
                $result = DB::connection($connection)->select("EXPLAIN QUERY PLAN {$query}", $bindings);
                
                foreach ($result as $row) {
                    $plan[] = (array) $row;
                }
            }
        } catch (\Exception $e) {
            // Query execution failed
            return ['error' => $e->getMessage()];
        }
        
        return $plan;
    }
    
    /**
     * Analyze a query for potential performance issues.
     *
     * @param string $query The SQL query
     * @param array $bindings The query bindings
     * @param string|null $connection The connection name
     * @return array The query analysis results
     */
    protected function analyzeQuery(string $query, array $bindings = [], ?string $connection = null): array
    {
        $driver = $this->getDatabaseDriver($connection);
        $analysis = [
            'query' => $query,
            'execution_plan' => $this->getQueryExecutionPlan($query, $bindings, $connection),
            'execution_time' => $this->getQueryExecutionTime($query, $bindings, $connection),
            'issues' => [],
        ];
        
        // Analyze the execution plan for common issues
        if ($driver === 'mysql') {
            foreach ($analysis['execution_plan'] as $step) {
                // Check for full table scans
                if (isset($step['type']) && $step['type'] === 'ALL') {
                    $analysis['issues'][] = [
                        'type' => 'full_table_scan',
                        'description' => "Full table scan detected on table '{$step['table']}'. Consider adding an index.",
                        'severity' => 'high',
                    ];
                }
                
                // Check for temporary tables
                if (isset($step['Extra']) && (strpos($step['Extra'], 'Using temporary') !== false)) {
                    $analysis['issues'][] = [
                        'type' => 'temporary_table',
                        'description' => 'Query uses temporary tables which can impact performance.',
                        'severity' => 'medium',
                    ];
                }
                
                // Check for filesorts
                if (isset($step['Extra']) && (strpos($step['Extra'], 'Using filesort') !== false)) {
                    $analysis['issues'][] = [
                        'type' => 'filesort',
                        'description' => 'Query uses filesort which can impact performance.',
                        'severity' => 'medium',
                    ];
                }
            }
        } elseif ($driver === 'pgsql' && !empty($analysis['execution_plan'])) {
            $this->analyzePgPlan($analysis['execution_plan'][0], $analysis['issues']);
        }
        
        // Check for long execution time
        if ($analysis['execution_time'] !== null) {
            if ($analysis['execution_time'] > 1000) { // More than 1 second
                $analysis['issues'][] = [
                    'type' => 'long_execution_time',
                    'description' => "Query execution time is {$analysis['execution_time']} ms which is very slow.",
                    'severity' => 'high',
                ];
            } elseif ($analysis['execution_time'] > 100) { // More than 100 ms
                $analysis['issues'][] = [
                    'type' => 'moderate_execution_time',
                    'description' => "Query execution time is {$analysis['execution_time']} ms which is moderately slow.",
                    'severity' => 'medium',
                ];
            }
        }
        
        return $analysis;
    }
    
    /**
     * Recursively analyze a PostgreSQL execution plan for issues.
     *
     * @param array $node The plan node to analyze
     * @param array &$issues The issues array to populate
     * @return void
     */
    protected function analyzePgPlan(array $node, array &$issues): void
    {
        // Check for sequential scans
        if (isset($node['Node Type']) && $node['Node Type'] === 'Seq Scan') {
            $issues[] = [
                'type' => 'sequential_scan',
                'description' => "Sequential scan detected on relation '{$node['Relation Name']}'. Consider adding an index.",
                'severity' => 'high',
                'details' => $node,
            ];
        }
        
        // Check for hash joins (might be inefficient for large tables)
        if (isset($node['Node Type']) && $node['Node Type'] === 'Hash Join' && isset($node['Plan Rows']) && $node['Plan Rows'] > 10000) {
            $issues[] = [
                'type' => 'large_hash_join',
                'description' => 'Large hash join detected which might consume significant memory.',
                'severity' => 'medium',
                'details' => $node,
            ];
        }
        
        // Check for high cost operations
        if (isset($node['Total Cost']) && $node['Total Cost'] > 1000) {
            $issues[] = [
                'type' => 'high_cost_operation',
                'description' => "High cost operation detected (cost: {$node['Total Cost']}).",
                'severity' => 'medium',
                'details' => $node,
            ];
        }
        
        // Recursively check child plans
        if (isset($node['Plans'])) {
            foreach ($node['Plans'] as $childNode) {
                $this->analyzePgPlan($childNode, $issues);
            }
        }
    }
}