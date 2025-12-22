<?php

// file: Utils/Time.php
declare(strict_types=1);

namespace App\Utils;

use App\Services\SettingsService;

/**
 * Time Utility Class
 *
 * Provides common time formatting and calculation methods with settings support
 */
class Time
{
    /**
     * Format time in seconds to human-readable format using settings
     *
     * @param int|null $seconds
     * @param bool $useSettings Whether to use settings for formatting
     * @return string Formatted time (e.g. "2h 30m" or "150m" based on settings)
     */
    public static function formatSeconds(?int $seconds, bool $useSettings = true): string
    {
        if ($seconds === null || $seconds == 0) {
            if ($useSettings) {
                $settingsService = SettingsService::getInstance();
                $unit = $settingsService->getTimeUnitLabel();

                return "0 {$unit}";
            }

            return '0h 0m';
        }

        if ($useSettings) {
            $settingsService = SettingsService::getInstance();
            $converted = $settingsService->convertTime($seconds);

            return $converted['formatted'];
        }

        // Default formatting (backward compatibility)
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return "{$hours}h {$minutes}m";
    }

    /**
     * Format duration between two timestamps
     *
     * @param int $startTime Unix timestamp
     * @param int $endTime Unix timestamp
     * @return array Formatted duration parts [hours, minutes, seconds]
     */
    public static function formatDuration(int $startTime, int $endTime): array
    {
        $duration = $endTime - $startTime;

        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'total_seconds' => $duration,
            'formatted' => self::formatSeconds($duration),
        ];
    }

    /**
     * Calculate time remaining until a given date
     *
     * @param string $dueDate Due date (YYYY-MM-DD)
     * @return int Days remaining (negative if overdue)
     */
    public static function daysRemaining(?string $dueDate): ?int
    {
        if (empty($dueDate)) {
            return null;
        }

        $now = new \DateTime();
        $due = new \DateTime($dueDate);

        return (int)$now->diff($due)->format('%r%a');
    }

    /**
     * Check if a task is overdue
     *
     * @param string $dueDate Due date (YYYY-MM-DD)
     * @param int $statusId Current task status ID
     * @param array $completedStatusIds Array of status IDs considered "complete" (5=closed, 6=completed)
     * @return bool True if task is overdue
     */
    public static function isOverdue(?string $dueDate, int $statusId, array $completedStatusIds = [5, 6]): bool
    {
        if (empty($dueDate)) {
            return false;
        }

        // Not overdue if task is completed
        if (in_array($statusId, $completedStatusIds)) {
            return false;
        }

        $now = new \DateTime();
        $due = new \DateTime($dueDate);

        return $due < $now;
    }

    /**
     * Convert time value from settings unit to seconds
     *
     * @param float $value Time value in the configured unit
     * @param string|null $unit Override unit (if null, uses settings)
     * @return int Seconds
     */
    public static function convertToSeconds(float $value, ?string $unit = null): int
    {
        if ($unit === null) {
            $settingsService = SettingsService::getInstance();
            $timeSettings = $settingsService->getTimeIntervalSettings();
            $unit = $timeSettings['time_unit'];
        }

        switch ($unit) {
            case 'seconds':
                return (int)$value;
            case 'hours':
                return (int)($value * 3600);
            case 'days':
                return (int)($value * 86400);
            case 'minutes':
            default:
                return (int)($value * 60);
        }
    }

    /**
     * Convert seconds to settings unit
     *
     * @param int $seconds
     * @param string|null $unit Override unit (if null, uses settings)
     * @return float Value in the specified unit
     */
    public static function convertFromSeconds(int $seconds, ?string $unit = null): float
    {
        if ($unit === null) {
            $settingsService = SettingsService::getInstance();
            $timeSettings = $settingsService->getTimeIntervalSettings();
            $unit = $timeSettings['time_unit'];
        }

        switch ($unit) {
            case 'seconds':
                return (float)$seconds;
            case 'hours':
                return round($seconds / 3600, 2);
            case 'days':
                return round($seconds / 86400, 2);
            case 'minutes':
            default:
                return round($seconds / 60, 2);
        }
    }

    /**
     * Parse seconds from various formatted time strings
     *
     * @param string $timeString Time string (e.g. "2h 30m", "2.5h", "150m", "02:30")
     * @return int Seconds
     */
    public static function parseTimeToSeconds(string $timeString): int
    {
        $seconds = 0;

        // Pattern for "Xh Ym" format
        if (preg_match('/^(\d+)h\s*(\d+)m$/', $timeString, $matches)) {
            $seconds = ((int)$matches[1] * 3600) + ((int)$matches[2] * 60);
        }
        // Pattern for "X.Yh" format
        elseif (preg_match('/^(\d+(\.\d+)?)h$/', $timeString, $matches)) {
            $seconds = (float)$matches[1] * 3600;
        }
        // Pattern for "Xm" format
        elseif (preg_match('/^(\d+)m$/', $timeString, $matches)) {
            $seconds = (int)$matches[1] * 60;
        }
        // Pattern for "mm:ss" format
        elseif (preg_match('/^(\d{1,2}):(\d{1,2})$/', $timeString, $matches)) {
            $seconds = ((int)$matches[1] * 60) + (int)$matches[2];
        }

        return (int)$seconds;
    }
}
