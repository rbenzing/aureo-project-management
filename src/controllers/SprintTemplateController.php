<?php
// file: Controllers/SprintTemplateController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Template;
use App\Models\Project;
use App\Models\Company;
use App\Utils\Validator;
use InvalidArgumentException;
use RuntimeException;

/**
 * Sprint Template Controller
 * 
 * Handles sprint template management with configuration options
 */
class SprintTemplateController
{
    private AuthMiddleware $authMiddleware;
    private Template $templateModel;
    private Project $projectModel;
    private Company $companyModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->templateModel = new Template();
        $this->projectModel = new Project();
        $this->companyModel = new Company();
    }

    /**
     * Display sprint templates index
     * @param string $requestMethod
     * @param array $data
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_templates');

            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $settingsService = \App\Services\SettingsService::getInstance();
            $limit = $settingsService->getResultsPerPage();
            
            // Get filter parameters
            $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;
            $companyId = $_SESSION['user']['company_id'] ?? null;

            // Get sprint templates with configurations
            $templates = $this->templateModel->getSprintTemplates($companyId, $projectId);
            
            // Get projects for filter dropdown
            $projects = $this->projectModel->getAllWithDetails();

            include __DIR__ . '/../Views/SprintTemplates/index.php';
        } catch (\Exception $e) {
            error_log("Exception in SprintTemplateController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading sprint templates.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Display sprint template creation form
     * @param string $requestMethod
     * @param array $data
     */
    public function createForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_templates');
            
            $companies = $this->companyModel->getAllCompanies();
            $projects = $this->projectModel->getAllWithDetails();
            
            include __DIR__ . '/../Views/SprintTemplates/create.php';
        } catch (\Exception $e) {
            error_log("Exception in SprintTemplateController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the creation form.';
            header('Location: /sprint-templates');
            exit;
        }
    }

    /**
     * Create new sprint template
     * @param string $requestMethod
     * @param array $data
     */
    public function create(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $this->createForm($requestMethod, $data);
            return;
        }

        try {
            $this->authMiddleware->hasPermission('create_templates');

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'company_id' => 'nullable|integer|exists:companies,id',
                'is_default' => 'boolean',
                'sprint_length' => 'required|integer|min:1|max:8',
                'estimation_method' => 'required|in:hours,story_points,both',
                'default_capacity' => 'required|integer|min:1|max:200',
                'include_weekends' => 'boolean',
                'auto_assign_subtasks' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            // Prepare template data
            $templateData = [
                'name' => htmlspecialchars($data['name']),
                'description' => $data['description'],
                'template_type' => 'sprint',
                'company_id' => !empty($data['company_id']) ? 
                    filter_var($data['company_id'], FILTER_VALIDATE_INT) : null,
                'is_default' => isset($data['is_default']) ? true : false
            ];

            // Prepare configuration data
            $configData = [
                'project_id' => !empty($data['project_id']) ? 
                    filter_var($data['project_id'], FILTER_VALIDATE_INT) : null,
                'sprint_length' => intval($data['sprint_length']),
                'estimation_method' => $data['estimation_method'],
                'default_capacity' => intval($data['default_capacity']),
                'include_weekends' => isset($data['include_weekends']) ? true : false,
                'auto_assign_subtasks' => isset($data['auto_assign_subtasks']) ? true : false,
                'ceremony_settings' => $this->processCeremonySettings($data)
            ];

            // Create sprint template with configuration
            $templateId = $this->templateModel->createSprintTemplate($templateData, $configData);

            $_SESSION['success'] = 'Sprint template created successfully.';
            header('Location: /sprint-templates');
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /sprint-templates/create');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in SprintTemplateController::create: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while creating the sprint template.';
            header('Location: /sprint-templates/create');
            exit;
        }
    }

    /**
     * Display sprint template edit form
     * @param string $requestMethod
     * @param array $data
     */
    public function editForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('edit_templates');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $template = $this->templateModel->find($id);
            if (!$template || $template->is_deleted || $template->template_type !== 'sprint') {
                throw new InvalidArgumentException('Sprint template not found');
            }

            // Get template configuration
            $config = $this->templateModel->getSprintTemplateConfiguration($id);
            
            $companies = $this->companyModel->getAllCompanies();
            $projects = $this->projectModel->getAllWithDetails();

            include __DIR__ . '/../Views/SprintTemplates/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /sprint-templates');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in SprintTemplateController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /sprint-templates');
            exit;
        }
    }

    /**
     * Process ceremony settings from form data
     * @param array $data Form data
     * @return array Processed ceremony settings
     */
    private function processCeremonySettings(array $data): array
    {
        $ceremonies = ['planning', 'daily_standup', 'review', 'retrospective'];
        $settings = [];

        foreach ($ceremonies as $ceremony) {
            $settings[$ceremony] = [
                'enabled' => isset($data["ceremony_{$ceremony}_enabled"]) ? true : false,
                'duration_hours' => floatval($data["ceremony_{$ceremony}_duration"] ?? 1),
                'participants' => explode(',', $data["ceremony_{$ceremony}_participants"] ?? 'team')
            ];

            if ($ceremony === 'daily_standup') {
                $settings[$ceremony]['duration_minutes'] = intval($data["ceremony_{$ceremony}_duration"] ?? 15);
                $settings[$ceremony]['time'] = $data["ceremony_{$ceremony}_time"] ?? '09:00';
                unset($settings[$ceremony]['duration_hours']);
            }
        }

        return $settings;
    }

    /**
     * Get sprint template for AJAX requests
     * @param string $requestMethod
     * @param array $data
     */
    public function getTemplate(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_templates');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $template = $this->templateModel->find($id);
            if (!$template || $template->is_deleted || $template->template_type !== 'sprint') {
                throw new InvalidArgumentException('Sprint template not found');
            }

            $config = $this->templateModel->getSprintTemplateConfiguration($id);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'template' => [
                    'name' => $template->name,
                    'description' => $template->description,
                    'config' => $config
                ]
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Apply sprint template to create new sprint
     * @param string $requestMethod
     * @param array $data
     */
    public function applyTemplate(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_sprints');

            $templateId = filter_var($data['template_id'] ?? null, FILTER_VALIDATE_INT);
            $projectId = filter_var($data['project_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$templateId || !$projectId) {
                throw new InvalidArgumentException('Template ID and Project ID are required');
            }

            $template = $this->templateModel->find($templateId);
            $config = $this->templateModel->getSprintTemplateConfiguration($templateId);

            if (!$template || !$config) {
                throw new InvalidArgumentException('Template or configuration not found');
            }

            // Redirect to sprint creation with template data
            $queryParams = http_build_query([
                'template_id' => $templateId,
                'project_id' => $projectId,
                'name' => $template->name,
                'description' => $template->description,
                'sprint_length' => $config->sprint_length ?? 2,
                'estimation_method' => $config->estimation_method ?? 'hours',
                'capacity' => $config->default_capacity ?? 40
            ]);

            header("Location: /sprints/create/{$projectId}?{$queryParams}");
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /sprint-templates');
            exit;
        }
    }

    /**
     * Get sprint templates for API requests
     * @param string $requestMethod
     * @param array $data
     */
    public function getTemplatesApi(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_templates');

            $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;
            $companyId = $_SESSION['user']['company_id'] ?? null;

            // Get sprint templates with configurations
            $templates = $this->templateModel->getSprintTemplates($companyId, $projectId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
