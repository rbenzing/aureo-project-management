<?php
namespace App\Config;

class Config {
    public const PAGINATION_LIMIT = 10;
    public const DEFAULT_TIMEZONE = 'UTC';

    public static $app = [
        'debug' => true,
        'timezone' => DEFAULT_TIMEZONE,
    ];
}