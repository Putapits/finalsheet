<?php
/**
 * Secure Configuration Loader
 * Loads environment variables from .env file
 */

class Config
{
    private static $config = [];
    private static $loaded = false;

    /**
     * Load configuration from .env file
     */
    public static function load()
    {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/.env';

        // If .env doesn't exist, try to use environment variables or defaults
        if (!file_exists($envFile)) {
            self::loadFromEnvironment();
            self::$loaded = true;
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                self::$config[$key] = $value;

                // Also set as environment variable
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Load from system environment variables
     */
    private static function loadFromEnvironment()
    {
        // Default XAMPP settings
        self::$config['DB_HOST'] = 'localhost';
        self::$config['DB_USERNAME'] = 'root';
        self::$config['DB_PASSWORD'] = '';
        self::$config['DB_NAME'] = 'gsm_health_system';

        $keys = [
            'SMTP_HOST',
            'SMTP_PORT',
            'SMTP_USERNAME',
            'SMTP_PASSWORD',
            'SMTP_FROM_EMAIL',
            'SMTP_FROM_NAME',
            'APP_ENV',
            'APP_DEBUG'
        ];

        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false) {
                self::$config[$key] = $value;
            }
        }
    }

    /**
     * Get configuration value
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config[$key] ?? $default;
    }

    /**
     * Check if configuration key exists
     */
    public static function has($key)
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset(self::$config[$key]);
    }

    /**
     * Get all configuration
     */
    public static function all()
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config;
    }
}

// Auto-load configuration
Config::load();
