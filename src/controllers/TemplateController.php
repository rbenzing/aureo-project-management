<?php
// file: Controllers/TemplateController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Middleware\AuthMiddleware;
use App\Models\Template;
use App\Models\Company;
use App\Utils\Validator;
use RuntimeException;
use InvalidArgumentException;
use App\Services\SecurityService;

class TemplateController
{
    private AuthMiddleware $authMiddleware;
    private Template $templateModel;
    private Company $companyModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->templateModel = new Template();
        $this->companyModel = new Company();
    }

    /**
     * Display paginated list of templates
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_templates');

            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $settingsService = \App\Services\SettingsService::getInstance();
            $limit = $settingsService->getResultsPerPage();
            
            // Get filter parameters
            $templateType = isset($_GET['type']) ? $_GET['type'] : '';
            $filters = [];
            if (!empty($templateType) && array_key_exists($templateType, Template::TEMPLATE_TYPES)) {
                $filters['template_type'] = $templateType;
            }

            // Debug: Test each step individually
            error_log("TemplateController: Starting to fetch templates");

            try {
                $templates = $this->templateModel->getAllTemplates($filters, $limit, $page);
                error_log("TemplateController: Successfully got templates, count: " . count($templates));
            } catch (\Exception $e) {
                error_log("TemplateController: Error getting templates: " . $e->getMessage());
                throw $e;
            }

            try {
                $countFilters = $filters;
                $countFilters['is_deleted'] = 0;
                $totalTemplates = $this->templateModel->count($countFilters);
                error_log("TemplateController: Successfully got count: " . $totalTemplates);
            } catch (\Exception $e) {
                error_log("TemplateController: Error getting count: " . $e->getMessage());
                throw $e;
            }

            $totalPages = ceil($totalTemplates / $limit);

            include __DIR__ . '/../Views/Templates/index.php';
        } catch (\Exception $e) {
            $securityService = SecurityService::getInstance();
            $_SESSION['error'] = $securityService->handleError($e, 'TemplateController::index', 'An error occurred while fetching templates.');
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Display template details
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function view(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_templates');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $template = $this->templateModel->find($id);
            if (!$template || $template->is_deleted) {
                throw new InvalidArgumentException('Template not found');
            }

            include __DIR__ . '/../Views/Templates/view.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /templates');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TemplateController::view: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching template details.';
            header('Location: /templates');
            exit;
        }
    }

    /**
     * Display template creation form
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function createForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_templates');
            
            $companies = $this->companyModel->getAllCompanies();
            
            include __DIR__ . '/../Views/Templates/create.php';
        } catch (\Exception $e) {
            error_log("Exception in TemplateController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the creation form.';
            header('Location: /templates');
            exit;
        }
    }

    /**
     * Handle template creation
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function create(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            header('Location: /templates/create');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('create_templates');

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'template_type' => 'required|in:project,task,milestone,sprint',
                'company_id' => 'nullable|integer|exists:companies,id',
                'is_default' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $templateData = [
                'name' => htmlspecialchars($data['name']),
                'description' => $data['description'],
                'template_type' => $data['template_type'],
                'company_id' => !empty($data['company_id']) ? 
                    filter_var($data['company_id'], FILTER_VALIDATE_INT) : null,
                'is_default' => isset($data['is_default']) ? true : false
            ];

            // Begin transaction for setting default template
            $this->templateModel->beginTransaction();

            try {
                $templateId = $this->templateModel->create($templateData);
                
                // If this is set as default, update other templates of the same type
                if ($templateData['is_default']) {
                    $this->templateModel->setDefaultTemplate($templateId, $templateData['template_type'], $templateData['company_id']);
                }
                
                $this->templateModel->commit();
                
                $_SESSION['success'] = 'Template created successfully.';
                header('Location: /templates');
                exit;
            } catch (\Exception $e) {
                $this->templateModel->rollBack();
                throw $e;
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /templates/create');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TemplateController::create: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while creating the template.';
            $_SESSION['form_data'] = $data;
            header('Location: /templates/create');
            exit;
        }
    }

    /**
     * Display template edit form
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
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
            if (!$template || $template->is_deleted) {
                throw new InvalidArgumentException('Template not found');
            }

            $companies = $this->companyModel->getAllCompanies();

            include __DIR__ . '/../Views/Templates/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /templates');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TemplateController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /templates');
            exit;
        }
    }

    /**
     * Handle template update
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function update(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            header('Location: /templates');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('edit_templates');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'template_type' => 'required|in:project,task,milestone,sprint',
                'company_id' => 'nullable|integer|exists:companies,id',
                'is_default' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $templateData = [
                'name' => htmlspecialchars($data['name']),
                'description' => $data['description'],
                'template_type' => $data['template_type'],
                'company_id' => !empty($data['company_id']) ? 
                    filter_var($data['company_id'], FILTER_VALIDATE_INT) : null,
                'is_default' => isset($data['is_default']) ? true : false
            ];

            // Begin transaction for setting default template
            $this->templateModel->beginTransaction();

            try {
                $this->templateModel->update($id, $templateData);
                
                // If this is set as default, update other templates of the same type
                if ($templateData['is_default']) {
                    $this->templateModel->setDefaultTemplate($id, $templateData['template_type'], $templateData['company_id']);
                }
                
                $this->templateModel->commit();
                
                $_SESSION['success'] = 'Template updated successfully.';
                header('Location: /templates');
                exit;
            } catch (\Exception $e) {
                $this->templateModel->rollBack();
                throw $e;
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: /templates/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TemplateController::update: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while updating the template.';
            header("Location: /templates/edit/{$id}");
            exit;
        }
    }

    /**
     * Handle template deletion
     *
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function delete(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            header('Location: /templates');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('delete_templates');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $template = $this->templateModel->find($id);
            if (!$template || $template->is_deleted) {
                throw new InvalidArgumentException('Template not found');
            }

            $this->templateModel->update($id, ['is_deleted' => true]);

            $_SESSION['success'] = 'Template deleted successfully.';
            header('Location: /templates');
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /templates');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TemplateController::delete: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while deleting the template.';
            header('Location: /templates');
            exit;
        }
    }

    /**
     * Return template JSON for AJAX requests
     *
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
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
            if (!$template || $template->is_deleted) {
                throw new InvalidArgumentException('Template not found');
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'template' => [
                    'name' => $template->name,
                    'description' => $template->description,
                    'template_type' => $template->template_type
                ]
            ]);
            exit;
        } catch (InvalidArgumentException $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        } catch (\Exception $e) {
            error_log("Exception in TemplateController::getTemplate: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while fetching the template.'
            ]);
            exit;
        }
    }
}
