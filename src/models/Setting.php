<?php
//file: Models/Setting.php
declare(strict_types=1);

namespace App\Models;

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Database;
use PDO;

class Setting
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all settings grouped by category
     */
    public function getAllGrouped(): array
    {
        $sql = "SELECT category, setting_key, setting_value FROM settings ORDER BY category, setting_key";
        $stmt = $this->db->executeQuery($sql);

        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['category']][$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Get a specific setting value
     */
    public function getSetting(string $category, string $key, string $default = ''): string
    {
        $sql = "SELECT setting_value FROM settings WHERE category = :category AND setting_key = :key";
        $stmt = $this->db->executeQuery($sql, [':category' => $category, ':key' => $key]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Update or create a setting
     */
    public function updateSetting(string $category, string $key, string $value): bool
    {
        // Check if setting exists
        $sql = "SELECT id FROM settings WHERE category = :category AND setting_key = :key";
        $stmt = $this->db->executeQuery($sql, [':category' => $category, ':key' => $key]);

        if ($stmt->fetch()) {
            // Update existing setting
            $sql = "UPDATE settings SET setting_value = :value, updated_at = NOW() WHERE category = :category AND setting_key = :key";
            return $this->db->executeInsertUpdate($sql, [':value' => $value, ':category' => $category, ':key' => $key]);
        } else {
            // Create new setting
            $sql = "INSERT INTO settings (category, setting_key, setting_value, created_at, updated_at) VALUES (:category, :key, :value, NOW(), NOW())";
            return $this->db->executeInsertUpdate($sql, [':category' => $category, ':key' => $key, ':value' => $value]);
        }
    }

    /**
     * Get settings for a specific category
     */
    public function getCategorySettings(string $category): array
    {
        $sql = "SELECT setting_key, setting_value FROM settings WHERE category = :category ORDER BY setting_key";
        $stmt = $this->db->executeQuery($sql, [':category' => $category]);

        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Delete a setting
     */
    public function deleteSetting(string $category, string $key): bool
    {
        $sql = "DELETE FROM settings WHERE category = :category AND setting_key = :key";
        return $this->db->executeInsertUpdate($sql, [':category' => $category, ':key' => $key]);
    }

    /**
     * Get time unit setting with default
     */
    public function getTimeUnit(): string
    {
        return $this->getSetting('time_intervals', 'time_unit', 'minutes');
    }

    /**
     * Get time precision setting with default
     */
    public function getTimePrecision(): int
    {
        return (int)$this->getSetting('time_intervals', 'time_precision', '15');
    }

    /**
     * Get all time interval settings
     */
    public function getTimeIntervalSettings(): array
    {
        return [
            'time_unit' => $this->getTimeUnit(),
            'time_precision' => $this->getTimePrecision()
        ];
    }

    /**
     * Helper method to get setting with type conversion
     */
    public function getSettingBool(string $category, string $key, bool $default = false): bool
    {
        $value = $this->getSetting($category, $key, $default ? '1' : '0');
        return $value === '1' || $value === 'true';
    }

    /**
     * Helper method to get setting as integer
     */
    public function getSettingInt(string $category, string $key, int $default = 0): int
    {
        $value = $this->getSetting($category, $key, (string)$default);
        return (int)$value;
    }
}
