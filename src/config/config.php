<?php
namespace SajedZarinpour\DB;

/**
 * this file loads the configurations needed to connect to the database by the package.
 */
use SajedZarinpour\DB\envLoader;


if (!function_exists('config')) {
    function config(string $key): string
    {
        $envl = new envLoader;
        $envl(__DIR__ . '/../../');

        $configs = [
            'host' => getenv('DB_HOST') ?? '127.0.0.1',
            'port' => getenv('DB_PORT') ?? '3306',
            'database' => getenv('DB_DATABASE') ?? 'laravel',
            'user' => getenv('DB_USER') ?? 'dev',
            'password' => getenv('DB_PASSWORD') ?? 'password',
        ];

        if (empty($configs[$key]))
            throw new \Exception('requested config key not found');
        return $configs[$key];
    }
}
