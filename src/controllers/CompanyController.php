<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Utils\Validator;
use RuntimeException;
use InvalidArgumentException;

class CompanyController
{
    private AuthMiddleware $authMiddleware;
    private Company $companyModel;
    private Project $projectModel;
    private User $userModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->companyModel = new Company();
        $this->projectModel = new Project();
        $this->userModel = new User();
    }

    /**
     * Display paginated list of companies
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_companies');
            
            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $limit = 10;
            
            $companies = $this->companyModel->getAllWithDetails($limit, $page);
            $totalCompanies = $this->companyModel->count(['is_deleted' => 0]);
            $totalPages = ceil($totalCompanies / $limit);
            
            include __DIR__ . '/../Views/Companies/index.php';
        } catch (\Exception $e) {
            error_log("Error in CompanyController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching companies.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * View company details
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function view(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_companies');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid company ID');
            }

            $company = $this->companyModel->find($id);
            if (!$company || $company->is_deleted) {
                throw new InvalidArgumentException('Company not found');
            }

            // Get company related data
            $projects = $this->companyModel->getProjects($id);
            $users = $this->companyModel->getUsers($id);
            
            include __DIR__ . '/../Views/Companies/view.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /companies');
            exit;
        } catch (\Exception $e) {
            error_log("Error in CompanyController::view: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching company details.';
            header('Location: /companies');
            exit;
        }
    }

    /**
     * Display company creation form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function createForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_companies');
            include __DIR__ . '/../Views/Companies/create.php';
        } catch (\Exception $e) {
            error_log("Error in CompanyController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the creation form.';
            header('Location: /companies');
            exit;
        }
    }

    /**
     * Create new company
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
            $this->authMiddleware->hasPermission('create_companies');

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:25|regex:/^[+]?[0-9()-\s]{10,}$/',
                'email' => 'required|email|unique:companies,email',
                'website' => 'nullable|url|max:255'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $companyData = [
                'name' => htmlspecialchars($data['name']),
                'address' => isset($data['address']) ? 
                    htmlspecialchars($data['address']) : null,
                'phone' => isset($data['phone']) ? 
                    htmlspecialchars($data['phone']) : null,
                'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
                'website' => isset($data['website']) ? 
                    filter_var($data['website'], FILTER_SANITIZE_URL) : null,
                'user_id' => $_SESSION['user']['id']
            ];

            $companyId = $this->companyModel->create($companyData);

            $_SESSION['success'] = 'Company created successfully.';
            header('Location: /companies/view/' . $companyId);
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /companies/create');
            exit;
        } catch (\Exception $e) {
            error_log("Error in CompanyController::create: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while creating the company.';
            header('Location: /companies/create');
            exit;
        }
    }

    /**
     * Display company edit form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function editForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('edit_companies');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid company ID');
            }

            $company = $this->companyModel->find($id);
            if (!$company || $company->is_deleted) {
                throw new InvalidArgumentException('Company not found');
            }

            include __DIR__ . '/../Views/Companies/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /companies');
            exit;
        } catch (\Exception $e) {
            error_log("Error in CompanyController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /companies');
            exit;
        }
    }

    /**
     * Update existing company
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
            $this->authMiddleware->hasPermission('edit_companies');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid company ID');
            }

            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:25|regex:/^[+]?[0-9()-\s]{10,}$/',
                'email' => "required|email|unique:companies,email,{$id}",
                'website' => 'nullable|url|max:255'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $companyData = [
                'id' => $id,
                'name' => htmlspecialchars($data['name']),
                'address' => isset($data['address']) ? 
                    htmlspecialchars($data['address']) : null,
                'phone' => isset($data['phone']) ? 
                    htmlspecialchars($data['phone']) : null,
                'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
                'website' => isset($data['website']) ? 
                    filter_var($data['website'], FILTER_SANITIZE_URL) : null
            ];

            $this->companyModel->update($companyData);

            $_SESSION['success'] = 'Company updated successfully.';
            header('Location: /companies/view/' . $id);
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header("Location: /companies/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            error_log("Error in CompanyController::update: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while updating the company.';
            header("Location: /companies/edit/{$id}");
            exit;
        }
    }

    /**
     * Delete company (soft delete)
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function delete(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /companies');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('delete_companies');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid company ID');
            }

            $company = $this->companyModel->find($id);
            if (!$company || $company->is_deleted) {
                throw new InvalidArgumentException('Company not found');
            }

            // Check if company has active projects or users
            if ($this->projectModel->count(['company_id' => $id, 'is_deleted' => 0]) > 0) {
                throw new InvalidArgumentException('Cannot delete company with active projects');
            }

            if ($this->userModel->count(['company_id' => $id, 'is_deleted' => 0]) > 0) {
                throw new InvalidArgumentException('Cannot delete company with active users');
            }

            $this->companyModel->update([
                'id' => $id,
                'is_deleted' => true
            ]);

            $_SESSION['success'] = 'Company deleted successfully.';
            header('Location: /companies');
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /companies');
            exit;
        } catch (\Exception $e) {
            error_log("Error in CompanyController::delete: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while deleting the company.';
            header('Location: /companies');
            exit;
        }
    }
}