<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Session;

/**
 * Trait for handling session operations
 */
trait HandlesSessionOperations
{
    /**
     * Get a session value.
     *
     * @param string $key The session key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The session value
     */
    protected function getSession(string $key, $default = null)
    {
        return Session::get($key, $default);
    }
    
    /**
     * Set a session value.
     *
     * @param string|array $key The session key or an array of key/value pairs
     * @param mixed $value The session value (if key is a string)
     * @return void
     */
    protected function setSession($key, $value = null): void
    {
        Session::put($key, $value);
    }
    
    /**
     * Determine if a session value exists.
     *
     * @param string $key The session key
     * @return bool True if the session value exists, false otherwise
     */
    protected function hasSession(string $key): bool
    {
        return Session::has($key);
    }
    
    /**
     * Remove a session value.
     *
     * @param string|array $key The session key or an array of keys
     * @return void
     */
    protected function removeSession($key): void
    {
        Session::forget($key);
    }
    
    /**
     * Remove all session values.
     *
     * @return void
     */
    protected function clearSession(): void
    {
        Session::flush();
    }
    
    /**
     * Flash a session value for the next request.
     *
     * @param string|array $key The session key or an array of key/value pairs
     * @param mixed $value The session value (if key is a string)
     * @return void
     */
    protected function flashSession($key, $value = null): void
    {
        Session::flash($key, $value);
    }
    
    /**
     * Reflash all the session flash data.
     *
     * @return void
     */
    protected function reflashSession(): void
    {
        Session::reflash();
    }
    
    /**
     * Keep the session flash data for an additional request.
     *
     * @param string|array $key The session key or an array of keys
     * @return void
     */
    protected function keepSession($key): void
    {
        Session::keep($key);
    }
    
    /**
     * Get all session values.
     *
     * @return array The session values
     */
    protected function getAllSession(): array
    {
        return Session::all();
    }
    
    /**
     * Get the session ID.
     *
     * @return string The session ID
     */
    protected function getSessionId(): string
    {
        return Session::getId();
    }
    
    /**
     * Regenerate the session ID.
     *
     * @param bool $deleteOldSession Whether to delete the old session
     * @return void
     */
    protected function regenerateSessionId(bool $deleteOldSession = false): void
    {
        Session::regenerate($deleteOldSession);
    }
    
    /**
     * Get a typed session value.
     *
     * @param string $key The session key
     * @param string $type The expected type (string, int, bool, array)
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The session value cast to the specified type
     */
    protected function getTypedSession(string $key, string $type, $default = null)
    {
        $value = $this->getSession($key, $default);
        
        switch ($type) {
            case 'string':
                return (string) $value;
            case 'int':
            case 'integer':
                return (int) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'array':
                return (array) $value;
            default:
                return $value;
        }
    }
    
    /**
     * Get a boolean session value.
     *
     * @param string $key The session key
     * @param bool $default The default value if the key doesn't exist
     * @return bool The session value as a boolean
     */
    protected function getBoolSession(string $key, bool $default = false): bool
    {
        return $this->getTypedSession($key, 'bool', $default);
    }
    
    /**
     * Get an integer session value.
     *
     * @param string $key The session key
     * @param int $default The default value if the key doesn't exist
     * @return int The session value as an integer
     */
    protected function getIntSession(string $key, int $default = 0): int
    {
        return $this->getTypedSession($key, 'int', $default);
    }
    
    /**
     * Get a string session value.
     *
     * @param string $key The session key
     * @param string $default The default value if the key doesn't exist
     * @return string The session value as a string
     */
    protected function getStringSession(string $key, string $default = ''): string
    {
        return $this->getTypedSession($key, 'string', $default);
    }
    
    /**
     * Get an array session value.
     *
     * @param string $key The session key
     * @param array $default The default value if the key doesn't exist
     * @return array The session value as an array
     */
    protected function getArraySession(string $key, array $default = []): array
    {
        return $this->getTypedSession($key, 'array', $default);
    }
    
    /**
     * Get a float session value.
     *
     * @param string $key The session key
     * @param float $default The default value if the key doesn't exist
     * @return float The session value as a float
     */
    protected function getFloatSession(string $key, float $default = 0.0): float
    {
        return $this->getTypedSession($key, 'float', $default);
    }
    
    /**
     * Increment a session value.
     *
     * @param string $key The session key
     * @param int $amount The amount to increment
     * @return int|float The new value
     */
    protected function incrementSession(string $key, int $amount = 1)
    {
        return Session::increment($key, $amount);
    }
    
    /**
     * Decrement a session value.
     *
     * @param string $key The session key
     * @param int $amount The amount to decrement
     * @return int|float The new value
     */
    protected function decrementSession(string $key, int $amount = 1)
    {
        return Session::decrement($key, $amount);
    }
    
    /**
     * Push a value onto a session array.
     *
     * @param string $key The session key
     * @param mixed $value The value to push
     * @return void
     */
    protected function pushSession(string $key, $value): void
    {
        Session::push($key, $value);
    }
    
    /**
     * Get and remove a session value.
     *
     * @param string $key The session key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The session value
     */
    protected function pullSession(string $key, $default = null)
    {
        return Session::pull($key, $default);
    }
    
    /**
     * Get a session value with dot notation support.
     *
     * @param string $key The session key with dot notation
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The session value
     */
    protected function getDotSession(string $key, $default = null)
    {
        return data_get($this->getAllSession(), $key, $default);
    }
    
    /**
     * Set a session value with dot notation support.
     *
     * @param string $key The session key with dot notation
     * @param mixed $value The session value
     * @return void
     */
    protected function setDotSession(string $key, $value): void
    {
        $keys = explode('.', $key);
        $sessionKey = array_shift($keys);
        $session = $this->getSession($sessionKey, []);
        
        data_set($session, implode('.', $keys), $value);
        $this->setSession($sessionKey, $session);
    }
    
    /**
     * Get a session value with a prefix.
     *
     * @param string $prefix The session prefix
     * @param string $key The session key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The session value
     */
    protected function getPrefixedSession(string $prefix, string $key, $default = null)
    {
        return $this->getSession($prefix . '.' . $key, $default);
    }
    
    /**
     * Get all session values with a prefix.
     *
     * @param string $prefix The session prefix
     * @return array The session values
     */
    protected function getAllPrefixedSession(string $prefix): array
    {
        return $this->getSession($prefix, []);
    }
    
    /**
     * Set a session value with a prefix.
     *
     * @param string $prefix The session prefix
     * @param string $key The session key
     * @param mixed $value The session value
     * @return void
     */
    protected function setPrefixedSession(string $prefix, string $key, $value): void
    {
        $this->setSession($prefix . '.' . $key, $value);
    }
    
    /**
     * Remove a session value with a prefix.
     *
     * @param string $prefix The session prefix
     * @param string $key The session key
     * @return void
     */
    protected function removePrefixedSession(string $prefix, string $key): void
    {
        $this->removeSession($prefix . '.' . $key);
    }
    
    /**
     * Remove all session values with a prefix.
     *
     * @param string $prefix The session prefix
     * @return void
     */
    protected function removeAllPrefixedSession(string $prefix): void
    {
        $this->removeSession($prefix);
    }
    
    /**
     * Store a value in the session temporarily (for a specified number of requests).
     *
     * @param string $key The session key
     * @param mixed $value The session value
     * @param int $requests The number of requests to keep the value
     * @return void
     */
    protected function setTemporarySession(string $key, $value, int $requests = 1): void
    {
        $this->setSession($key, $value);
        $this->setSession('_temporary.' . $key, $requests);
    }
    
    /**
     * Get a temporary session value and decrement its counter.
     *
     * @param string $key The session key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The session value
     */
    protected function getTemporarySession(string $key, $default = null)
    {
        $counterKey = '_temporary.' . $key;
        $counter = $this->getIntSession($counterKey, 0);
        
        if ($counter <= 0) {
            $this->removeSession([$key, $counterKey]);
            return $default;
        }
        
        $this->setSession($counterKey, $counter - 1);
        return $this->getSession($key, $default);
    }
    
    /**
     * Store user preferences in the session.
     *
     * @param string $key The preference key
     * @param mixed $value The preference value
     * @return void
     */
    protected function setUserPreference(string $key, $value): void
    {
        $this->setPrefixedSession('preferences', $key, $value);
    }
    
    /**
     * Get a user preference from the session.
     *
     * @param string $key The preference key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The preference value
     */
    protected function getUserPreference(string $key, $default = null)
    {
        return $this->getPrefixedSession('preferences', $key, $default);
    }
    
    /**
     * Get all user preferences from the session.
     *
     * @return array The user preferences
     */
    protected function getAllUserPreferences(): array
    {
        return $this->getAllPrefixedSession('preferences');
    }
    
    /**
     * Remove a user preference from the session.
     *
     * @param string $key The preference key
     * @return void
     */
    protected function removeUserPreference(string $key): void
    {
        $this->removePrefixedSession('preferences', $key);
    }
    
    /**
     * Store a value in the session for the current request only.
     *
     * @param string $key The session key
     * @param mixed $value The session value
     * @return void
     */
    protected function setRequestSession(string $key, $value): void
    {
        request()->session()->flash($key, $value);
    }
    
    /**
     * Get a value from the session for the current request only.
     *
     * @param string $key The session key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The session value
     */
    protected function getRequestSession(string $key, $default = null)
    {
        return request()->session()->get($key, $default);
    }
}