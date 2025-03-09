<?php
// file: Controllers/ProjectTemplateController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Middleware\AuthMiddleware;
use App\Models\ProjectTemplate;
use App\Models\Company;
use App\Utils\Validator;
use RuntimeException;
use InvalidArgumentException;

class ProjectTemplateController
{
    private AuthMiddleware $authMiddleware;
    private ProjectTemplate $templateModel;
    private Company $companyModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->templateModel = new ProjectTemplate();
        $this->companyModel = new Company();
    }

    /**
     * Display paginated list of templates
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_project_templates');
            
            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $limit = Config::get('max_pages', 10);
            
            $results = $this->templateModel->getAll(['is_deleted' => 0], $page, $limit);
            $templates = $results['records'];
            $totalTemplates = $results['total'];
            $totalPages = ceil($totalTemplates / $limit);
            
            include __DIR__ . '/../Views/ProjectTemplates/index.php';
        } catch (\Exception $e) {
            error_log("Exception in ProjectTemplateController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching templates.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * View template details
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function view(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_project_templates');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $template = $this->templateModel->find($id);
            if (!$template || $template->is_deleted) {
                throw new InvalidArgumentException('Template not found');
            }

            include __DIR__ . '/../Views/ProjectTemplates/view.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /project-templates');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectTemplateController::view: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching template details.';
            header('Location: /project-templates');
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
            $this->authMiddleware->hasPermission('create_project_templates');
            
            $companies = $this->companyModel->getAllCompanies();
            
            include __DIR__ . '/../Views/ProjectTemplates/create.php';
        } catch (\Exception $e) {
            error_log("Exception in ProjectTemplateController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the creation form.';
            header('Location: /project-templates');
            exit;
        }
    }

    /**
     * Create new template
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function create(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $this->createForm($requestMethod, $data);
            return;
        }

        try {
            $this->authMiddleware->hasPermission('create_project_templates');

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'company_id' => 'nullable|integer|exists:companies,id',
                'is_default' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $templateData = [
                'name' => htmlspecialchars($data['name']),
                'description' => $data['description'],
                'company_id' => !empty($data['company_id']) ? 
                    filter_var($data['company_id'], FILTER_VALIDATE_INT) : null,
                'is_default' => isset($data['is_default']) ? true : false
            ];

            // Begin transaction for setting default template
            $this->templateModel->beginTransaction();

            try {
                $templateId = $this->templateModel->create($templateData);
                
                // If this is set as default, update other templates
                if ($templateData['is_default']) {
                    $this->templateModel->setDefaultTemplate($templateId, $templateData['company_id']);
                }
                
                $this->templateModel->commit();
                
                $_SESSION['success'] = 'Template created successfully.';
                header('Location: /project-templates');
                exit;
            } catch (\Exception $e) {
                $this->templateModel->rollBack();
                throw $e;
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /project-templates/create');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectTemplateController::create: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while creating the template.';
            header('Location: /project-templates/create');
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
            $this->authMiddleware->hasPermission('edit_project_templates');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $template = $this->templateModel->find($id);
            if (!$template || $template->is_deleted) {
                throw new InvalidArgumentException('Template not found');
            }

            $companies = $this->companyModel->getAllCompanies();

            include __DIR__ . '/../Views/ProjectTemplates/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /project-templates');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectTemplateController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /project-templates');
            exit;
        }
    }

    /**
     * Update existing template
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function update(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $this->editForm($requestMethod, $data);
            return;
        }

        try {
            $this->authMiddleware->hasPermission('edit_project_templates');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'company_id' => 'nullable|integer|exists:companies,id',
                'is_default' => 'boolean'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $templateData = [
                'name' => htmlspecialchars($data['name']),
                'description' => $data['description'],
                'company_id' => !empty($data['company_id']) ? 
                    filter_var($data['company_id'], FILTER_VALIDATE_INT) : null,
                'is_default' => isset($data['is_default']) ? true : false
            ];

            // Begin transaction for setting default template
            $this->templateModel->beginTransaction();

            try {
                $this->templateModel->update($id, $templateData);
                
                // If this is set as default, update other templates
                if ($templateData['is_default']) {
                    $this->templateModel->setDefaultTemplate($id, $templateData['company_id']);
                }
                
                $this->templateModel->commit();
                
                $_SESSION['success'] = 'Template updated successfully.';
                header('Location: /project-templates');
                exit;
            } catch (\Exception $e) {
                $this->templateModel->rollBack();
                throw $e;
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header("Location: /project-templates/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectTemplateController::update: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while updating the template.';
            header("Location: /project-templates/edit/{$id}");
            exit;
        }
    }

    /**
     * Delete template (soft delete)
     * 
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function delete(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /project-templates');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('delete_project_templates');

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
            header('Location: /project-templates');
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /project-templates');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in ProjectTemplateController::delete: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while deleting the template.';
            header('Location: /project-templates');
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
            $this->authMiddleware->hasPermission('view_projects');

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
                    'description' => $template->description
                ]
            ]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}