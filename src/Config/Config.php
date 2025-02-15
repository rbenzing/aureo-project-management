<?php
namespace App\Config;

class Config {
    public static $db = [
        'host' => 'localhost',
        'dbname' => 'your_database_name',
        'username' => 'your_username',
        'password' => 'your_password',
    ];

    public static $app = [
        'debug' => true,
        'timezone' => 'UTC',
    ];
}