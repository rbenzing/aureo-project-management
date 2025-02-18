<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Company;
use App\Utils\Validator;

class CompanyController {
    public function __construct() {
        // Ensure the user has the required permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('manage_companies'); // Default permission for all actions
    }

    /**
     * Display a list of companies (paginated).
     */
    public function index() {
        // Fetch all companies from the database (paginated)
        $companies = (new Company())->getAllPaginated(10); // Paginate results (e.g., 10 per page)
        
        include __DIR__ . '/../views/companies/index.php';
    }

    /**
     * View details of a specific company.
     */
    public function view() {
        $id = $_GET['id'] ?? null;

        // Fetch a single company by ID
        $company = (new Company())->find($id);
        if (!$company) {
            $_SESSION['error'] = 'Company not found.';
            header('Location: /companies');
            exit;
        }

        // Fetch related projects for the company
        $projects = (new \App\Models\Project())->getByCompanyId($id);

        // Render the view
        include __DIR__ . '/../views/companies/view.php';
    }

    /**
     * Create a new company.
     */
    public function create($data = null) {
        if (isset($data)) {
            // Validate CSRF token
            if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = 'Invalid CSRF token.';
                header('Location: /create_company');
                exit;
            }

            // Validate input data
            $validator = new Validator($data, [
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|unique:companies,email',
            ]);

            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /create_company');
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
        include __DIR__ . '/../views/companies/create.php';
    }

    /**
     * Show the form to edit an existing company.
     */
    public function edit($id) {
        // Fetch the company
        $company = (new Company())->find($id);
        if (!$company) {
            $_SESSION['error'] = 'Company not found.';
            header('Location: /companies');
            exit;
        }

        // Render the edit form
        include __DIR__ . '/../views/companies/edit.php';
    }

    /**
     * Update an existing company.
     */
    public function update($data) {
        $id = intval($data['id']);

        // Validate CSRF token
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header("Location: /edit_company?id=$id");
            exit;
        }

        // Validate input data
        $validator = new Validator($data, [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:companies,email,' . $id,
        ]);

        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header("Location: /edit_company?id=$id");
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

    /**
     * Delete a company (soft delete).
     */
    public function delete($id) {
        // Soft delete the company
        $company = (new Company())->find($id);
        if (!$company) {
            $_SESSION['error'] = 'Company not found.';
            header('Location: /companies');
            exit;
        }

        // Mark as deleted instead of permanently removing
        $company->is_deleted = true;
        $company->save();

        $_SESSION['success'] = 'Company deleted successfully.';
        header('Location: /companies');
        exit;
    }
}