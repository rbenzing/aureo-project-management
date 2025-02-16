<?php
namespace App\Config;

class Config {
    private const PAGINATION_LIMIT = 10;
    private const DEFAULT_TIMEZONE = 'UTC';
    private const APP_DOMAIN = 'slimbooks'; // domain.com
    private const APP_SCHEME = 'http';

    public static array $app = [
        'debug' => true,
        'max_pages' => self::PAGINATION_LIMIT,
        'timezone' => self::DEFAULT_TIMEZONE,
        'domain' => self::APP_DOMAIN,
        'scheme' => self::APP_SCHEME
    ];
}
