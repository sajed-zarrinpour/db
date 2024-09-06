<?php
namespace SajedZarinpour\DB\config;

/**
 * this file contains the class used to load the configurations from env file if any presents.
 */
class envLoader
{

    public function __invoke($path): void
    {
        $lines = file($path . '/.env');
        foreach ($lines as $line) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            putenv(sprintf('%s=%s', $key, $value));
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}