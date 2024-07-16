<?php
namespace SajedZarinpour\DB;

if (!function_exists('config')) {
    function config(string $key):string {
        // new SajedZarinpour\DB\envLoader;
        // envLoader(__DIR__.'/../../');
        $configs = [
            'host' => '127.0.0.1', // getenv('APP_ENV')??'127.0.0.1',
            'port' => '3306',
            'user' => 'dev',
            'password'=>'password',
            'database'=>'laravel'
        ];

        if (empty($configs[$key]))
            throw new \Exception('requested config key not found');
        return $configs[$key];
    }
}
