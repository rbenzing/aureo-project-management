<?php
namespace App\Core;

class Config {
    private const DEFAULT_PAGE_LIMIT = 10;
    private const DEFAULT_TIMEZONE = 'UTC'; // e.g. UTC or America/New_York
    private const DEFAULT_COMPANY = 'Russell Benzing Inc. d/b/a Slimbooks'; // e.g. Company Name, Inc.
    private const DEFAULT_DOMAIN = 'slimbooks'; // e.g. domain.com
    private const DEFAULT_SCHEME = 'http'; // e.g. http or https
    private const DEFAULT_LOCALE = 'en_US'; // e.g. en_US or de_DE
    private const DEFAULT_CURRENCY_FORMAT = '%i'; // e.g. %i = USD 1,234.56

    public static array $app = [
        'debug' => true,
        'max_pages' => self::DEFAULT_PAGE_LIMIT,
        'timezone' => self::DEFAULT_TIMEZONE,
        'domain' => self::DEFAULT_DOMAIN,
        'company_name' => self::DEFAULT_COMPANY,
        'scheme' => self::DEFAULT_SCHEME,
        'locale' => self::DEFAULT_LOCALE,
        'currency_format' => self::DEFAULT_CURRENCY_FORMAT
    ];
}
