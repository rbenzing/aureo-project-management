<?php
//file: Services/SettingsService.php
declare(strict_types=1);

namespace App\Services;

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Models\Setting;

/**
 * Settings Service
 * 
 * Centralized service for managing application settings with caching
 */
class SettingsService
{
    private Setting $settingModel;
    private static ?array $cache = null;
    private static ?SettingsService $instance = null;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): SettingsService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get all settings with caching
     */
    public function getAllSettings(): array
    {
        if (self::$cache === null) {
            self::$cache = $this->settingModel->getAllGrouped();
            
            // Apply defaults if settings don't exist
            $this->applyDefaults();
        }
        
        return self::$cache;
    }

    /**
     * Get a specific setting value
     */
    public function getSetting(string $category, string $key, string $default = ''): string
    {
        $settings = $this->getAllSettings();
        return $settings[$category][$key] ?? $default;
    }

    /**
     * Get setting as boolean
     */
    public function getSettingBool(string $category, string $key, bool $default = false): bool
    {
        $value = $this->getSetting($category, $key, $default ? '1' : '0');
        return $value === '1' || $value === 'true';
    }

    /**
     * Get setting as integer
     */
    public function getSettingInt(string $category, string $key, int $default = 0): int
    {
        $value = $this->getSetting($category, $key, (string)$default);
        return (int)$value;
    }

    /**
     * Get time interval settings
     */
    public function getTimeIntervalSettings(): array
    {
        return [
            'time_unit' => $this->getSetting('time_intervals', 'time_unit', 'minutes'),
            'time_precision' => $this->getSettingInt('time_intervals', 'time_precision', 15)
        ];
    }

    /**
     * Get project settings
     */
    public function getProjectSettings(): array
    {
        return [
            'default_task_type' => $this->getSetting('projects', 'default_task_type', 'task'),
            'auto_assign_creator' => $this->getSettingBool('projects', 'auto_assign_creator', true),
            'require_project_for_tasks' => $this->getSettingBool('projects', 'require_project_for_tasks', false)
        ];
    }

    /**
     * Get task settings
     */
    public function getTaskSettings(): array
    {
        return [
            'default_priority' => $this->getSetting('tasks', 'default_priority', 'medium'),
            'auto_estimate_enabled' => $this->getSettingBool('tasks', 'auto_estimate_enabled', false),
            'story_points_enabled' => $this->getSettingBool('tasks', 'story_points_enabled', true)
        ];
    }

    /**
     * Get milestone settings
     */
    public function getMilestoneSettings(): array
    {
        return [
            'auto_create_from_sprints' => $this->getSettingBool('milestones', 'auto_create_from_sprints', false),
            'milestone_notification_days' => $this->getSettingInt('milestones', 'milestone_notification_days', 7)
        ];
    }

    /**
     * Get sprint settings
     */
    public function getSprintSettings(): array
    {
        return [
            'default_sprint_length' => $this->getSettingInt('sprints', 'default_sprint_length', 14),
            'auto_start_next_sprint' => $this->getSettingBool('sprints', 'auto_start_next_sprint', false),
            'sprint_planning_enabled' => $this->getSettingBool('sprints', 'sprint_planning_enabled', true)
        ];
    }

    /**
     * Get template settings
     */
    public function getTemplateSettings(): array
    {
        return [
            'project' => [
                'show_quick_templates' => $this->getSettingBool('templates', 'project_show_quick_templates', true),
                'show_custom_templates' => $this->getSettingBool('templates', 'project_show_custom_templates', true)
            ],
            'task' => [
                'show_quick_templates' => $this->getSettingBool('templates', 'task_show_quick_templates', true),
                'show_custom_templates' => $this->getSettingBool('templates', 'task_show_custom_templates', true)
            ],
            'milestone' => [
                'show_quick_templates' => $this->getSettingBool('templates', 'milestone_show_quick_templates', true),
                'show_custom_templates' => $this->getSettingBool('templates', 'milestone_show_custom_templates', true)
            ],
            'sprint' => [
                'show_quick_templates' => $this->getSettingBool('templates', 'sprint_show_quick_templates', true),
                'show_custom_templates' => $this->getSettingBool('templates', 'sprint_show_custom_templates', true)
            ]
        ];
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): void
    {
        self::$cache = null;
    }

    /**
     * Apply default settings if they don't exist
     */
    private function applyDefaults(): void
    {
        $defaultSettings = [
            'time_intervals' => [
                'time_unit' => 'minutes',
                'time_precision' => '15'
            ],
            'projects' => [
                'default_task_type' => 'task',
                'auto_assign_creator' => '1',
                'require_project_for_tasks' => '0'
            ],
            'tasks' => [
                'default_priority' => 'medium',
                'auto_estimate_enabled' => '0',
                'story_points_enabled' => '1'
            ],
            'milestones' => [
                'auto_create_from_sprints' => '0',
                'milestone_notification_days' => '7'
            ],
            'sprints' => [
                'default_sprint_length' => '14',
                'auto_start_next_sprint' => '0',
                'sprint_planning_enabled' => '1'
            ],
            'templates' => [
                'project_show_quick_templates' => '1',
                'project_show_custom_templates' => '1',
                'task_show_quick_templates' => '1',
                'task_show_custom_templates' => '1',
                'milestone_show_quick_templates' => '1',
                'milestone_show_custom_templates' => '1',
                'sprint_show_quick_templates' => '1',
                'sprint_show_custom_templates' => '1'
            ]
        ];

        // Merge with existing settings
        foreach ($defaultSettings as $category => $categoryDefaults) {
            if (!isset(self::$cache[$category])) {
                self::$cache[$category] = [];
            }
            foreach ($categoryDefaults as $key => $defaultValue) {
                if (!isset(self::$cache[$category][$key])) {
                    self::$cache[$category][$key] = $defaultValue;
                }
            }
        }
    }

    /**
     * Convert time based on settings
     */
    public function convertTime(int $seconds, ?string $targetUnit = null): array
    {
        $timeSettings = $this->getTimeIntervalSettings();
        $unit = $targetUnit ?? $timeSettings['time_unit'];
        
        switch ($unit) {
            case 'seconds':
                return ['value' => $seconds, 'unit' => 'seconds', 'formatted' => $seconds . 's'];
            case 'hours':
                $hours = round($seconds / 3600, 2);
                return ['value' => $hours, 'unit' => 'hours', 'formatted' => $hours . 'h'];
            case 'days':
                $days = round($seconds / 86400, 2);
                return ['value' => $days, 'unit' => 'days', 'formatted' => $days . 'd'];
            case 'minutes':
            default:
                $minutes = round($seconds / 60, 2);
                return ['value' => $minutes, 'unit' => 'minutes', 'formatted' => $minutes . 'm'];
        }
    }

    /**
     * Get time step value based on precision setting
     */
    public function getTimeStep(): string
    {
        $timeSettings = $this->getTimeIntervalSettings();
        $unit = $timeSettings['time_unit'];
        $precision = $timeSettings['time_precision'];
        
        switch ($unit) {
            case 'seconds':
                return (string)$precision;
            case 'hours':
                return (string)($precision / 60);
            case 'days':
                return (string)($precision / 1440);
            case 'minutes':
            default:
                return (string)$precision;
        }
    }

    /**
     * Get time unit label
     */
    public function getTimeUnitLabel(): string
    {
        $timeSettings = $this->getTimeIntervalSettings();
        $unit = $timeSettings['time_unit'];
        
        switch ($unit) {
            case 'seconds':
                return 'seconds';
            case 'hours':
                return 'hours';
            case 'days':
                return 'days';
            case 'minutes':
            default:
                return 'minutes';
        }
    }
}
