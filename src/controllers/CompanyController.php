<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Company;
use App\Models\Project;
use App\Utils\Validator;

class CompanyController
{
    private $authMiddleware;
    private $csrfMiddleware;

    public function __construct()
    {
        // Ensure the user has the required permission
        $this->authMiddleware = new AuthMiddleware();
        $this->csrfMiddleware = new CsrfMiddleware();
        $this->authMiddleware->hasPermission('manage_companies'); // Default permission for all actions
    }

    /**
     * Display a list of companies (paginated).
     */
    public function index($requestMethod, $data)
    {
        // Fetch all companies from the database (paginated)
        $limit = 10; // Number of companies per page
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $companies = (new Company())->getAllPaginated($limit, $page);

        // Prepare pagination data
        $totalCompanies = (new Company())->countAll();
        $totalPages = ceil($totalCompanies / $limit);
        $prevPage = $page > 1 ? $page - 1 : null;
        $nextPage = $page < $totalPages ? $page + 1 : null;

        $pagination = [
            'prev_page' => $prevPage,
            'next_page' => $nextPage,
        ];

        include __DIR__ . '/../Views/Companies/index.php';
    }

    /**
     * View details of a specific company.
     */
    public function view($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid company ID.';
            header('Location: /companies');
            exit;
        }

        // Fetch a single company by ID
        $company = (new Company())->find($id);
        if (!$company) {
            $_SESSION['error'] = 'Company not found.';
            header('Location: /companies');
            exit;
        }

        // Fetch related projects for the company
        $projects = (new Project())->getByCompanyId($id);

        // Render the view
        include __DIR__ . '/../Views/Companies/view.php';
    }

    /**
     * Show the form to create a new company.
     */
    public function createForm($requestMethod, $data)
    {
        $this->authMiddleware->hasPermission('create_companies');

        // Render the create form
        include __DIR__ . '/../Views/Companies/create.php';
    }

    /**
     * Create a new company.
     */
    public function create($requestMethod, $data)
    {
        $this->authMiddleware->hasPermission('create_companies');

        if ($requestMethod === 'POST') {
            // Validate input data
            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|unique:companies,email',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /companies/create');
                exit;
            }

            // Create the company
            $company = new Company();
            $company->name = htmlspecialchars($data['name']);
            $company->address = htmlspecialchars($data['address'] ?? '');
            $company->phone = htmlspecialchars($data['phone'] ?? '');
            $company->email = htmlspecialchars($data['email'] ?? '');
            $company->save();

            $_SESSION['success'] = "Company '$company->name' was created successfully.";
            header('Location: /companies');
            exit;
        }

        // Render the create form
        $this->createForm($requestMethod, $data);
    }

    /**
     * Show the form to edit an existing company.
     */
    public function editForm($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid company ID.';
            header('Location: /companies');
            exit;
        }

        $this->authMiddleware->hasPermission('edit_companies');

        // Fetch the company
        $company = (new Company())->find($id);
        if (!$company) {
            $_SESSION['error'] = 'Company not found.';
            header('Location: /companies');
            exit;
        }

        // Render the edit form
        include __DIR__ . '/../Views/Companies/edit.php';
    }

    /**
     * Update an existing company.
     */
    public function update($requestMethod, $data)
    {
        $id = intval($data['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = 'Invalid company ID.';
            header('Location: /companies');
            exit;
        }

        $this->authMiddleware->hasPermission('edit_companies');

        if ($requestMethod === 'POST') {
            // Validate input data
            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|unique:companies,email,' . $id,
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header("Location: /companies/edit/$id");
                exit;
            }

            // Update the company
            $company = (new Company())->find($id);
            if (!$company) {
                $_SESSION['error'] = 'Company not found.';
                header('Location: /companies');
                exit;
            }

            $company->name = htmlspecialchars($data['name']);
            $company->address = htmlspecialchars($data['address'] ?? null);
            $company->phone = htmlspecialchars($data['phone'] ?? null);
            $company->email = htmlspecialchars($data['email'] ?? null);
            $company->save();

            $_SESSION['success'] = 'Company updated successfully.';
            header('Location: /companies');
            exit;
        }

        // Fetch the company for the edit form
        $this->editForm($requestMethod, $data);
    }

    /**
     * Delete a company (soft delete).
     */
    public function delete($requestMethod, $data)
    {
        $id = intval($data['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = 'Invalid company ID.';
            header('Location: /companies');
            exit;
        }

        $this->authMiddleware->hasPermission('delete_companies');

        if ($requestMethod === 'POST') {
            // Soft delete the company
            $company = (new Company())->find($id);
            if (!$company) {
                $_SESSION['error'] = 'Company not found.';
                header('Location: /companies');
                exit;
            }

            $company->is_deleted = true;
            $company->save();

            $_SESSION['success'] = 'Company deleted successfully.';
            header('Location: /companies');
            exit;
        }

        // Fetch the company for the delete confirmation form
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid company ID.';
            header('Location: /companies');
            exit;
        }

        $company = (new Company())->find($id);
        if (!$company) {
            $_SESSION['error'] = 'Company not found.';
            header('Location: /companies');
            exit;
        }

        // Render the delete confirmation form
        include __DIR__ . '/../Views/Companies/delete.php';
    }
}