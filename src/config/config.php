<?php
namespace SajedZarinpour\DB;

/**
 * this file loads the configurations needed to connect to the database by the package.
 */
use SajedZarinpour\DB\config\envLoader;


if (!function_exists('config')) {
    function config(string $key): string
    {
        if(isset($_ENV['TEST']) && $_ENV['TEST'] == true)
        {
            $configs = [
                'host' => '127.0.0.1',
                'port' => '3306',
                'database' => 'test_db',
                'user' => 'root',
                'password' => 'root',
            ];
    
            if (!isset($configs[$key]))
                throw new \Exception('requested config key not found');
            return $configs[$key];
        }
        else
        {

            $envl = new envLoader;
            $envl(__DIR__ . '/../../../../..');
            
            $configs = [
                'host' => getenv('DB_HOST') ?? '127.0.0.1',
                'port' => getenv('DB_PORT') ?? '3306',
                'database' => getenv('DB_DATABASE') ?? 'laravel',
                'user' => getenv('DB_USER') ?? 'dev',
                'password' => getenv('DB_PASSWORD') ?? 'password',
            ];
    
            if (!isset($configs[$key]))
                throw new \Exception('requested config key not found');
            return $configs[$key];
        }
    }
}
