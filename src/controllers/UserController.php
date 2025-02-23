<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\User;
use App\Models\Company;
use App\Utils\Email;
use App\Utils\Validator;

class UserController
{
    private $authMiddleware;
    private $csrfMiddleware;

    public function __construct()
    {
        // Ensure the user has the required permission
        $this->authMiddleware = new AuthMiddleware();
        $this->csrfMiddleware = new CsrfMiddleware();
        $this->authMiddleware->hasPermission('manage_users'); // Default permission for all actions
    }

    /**
     * Display a list of users (paginated).
     */
    public function index($requestMethod, $data)
    {
        // Fetch all active users from the database (paginated)
        $limit = 10; // Number of users per page
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $users = (new User())->getAllPaginated($limit, $page);

        include __DIR__ . '/../Views/Users/index.php';
    }

    /**
     * View details of a specific user.
     */
    public function view($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid user ID.';
            header('Location: /users');
            exit;
        }

        // Fetch a single user by ID (excluding soft-deleted users)
        $user = (new User())->find($id);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /users');
            exit;
        }

        $this->authMiddleware->hasPermission('view_users'); // Default permission for all actions

        // Render the view
        include __DIR__ . '/../Views/Users/view.php';
    }

    /**
     * Show the form to create a new user.
     */
    public function createForm($requestMethod, $data)
    {
        $this->authMiddleware->hasPermission('create_users');

        // Fetch all companies for the form
        $companies = (new Company())->getAll();

        include __DIR__ . '/../Views/Users/create.php';
    }

    /**
     * Create a new user.
     */
    public function create($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $this->authMiddleware->hasPermission('create_users');

            // Validate input data
            $validator = new Validator($data, [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email',
                'role_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /users/create');
                exit;
            }

            // Create the user
            $user = new User();
            $user->first_name = htmlspecialchars($data['first_name']);
            $user->last_name = htmlspecialchars($data['last_name']);
            $user->email = htmlspecialchars($data['email']);
            $user->password_hash = password_hash('default_password', PASSWORD_ARGON2ID); // Set a default password
            $user->role_id = intval($data['role_id']);
            $user->company_id = isset($data['company_id']) ? intval($data['company_id']) : null;
            $user->generateActivationToken();
            $user->save();

            // Send activation email
            Email::sendActivationEmail($user);
            $_SESSION['success'] = 'User was created successfully. An email was sent to the user to activate the account.';
            header('Location: /users');
            exit;
        }

        // Render the create form
        $this->createForm($requestMethod, $data);
    }

    /**
     * Show the form to edit an existing user.
     */
    public function editForm($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid user ID.';
            header('Location: /users');
            exit;
        }

        $this->authMiddleware->hasPermission('edit_users');

        // Fetch the user (excluding soft-deleted users)
        $user = (new User())->find($id);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /users');
            exit;
        }

        // Fetch all companies for the form
        $companies = (new Company())->getAll();

        // Render the edit form
        include __DIR__ . '/../Views/Users/edit.php';
    }

    /**
     * Update an existing user.
     */
    public function update($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid user ID.';
                header('Location: /users');
                exit;
            }

            $this->authMiddleware->hasPermission('edit_users');

            // Validate input data
            $validator = new Validator($data, [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email,' . $id,
                'role_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header("Location: /users/edit/$id");
                exit;
            }

            // Update the user
            $user = (new User())->find($id);
            if (!$user) {
                $_SESSION['error'] = 'User not found.';
                header('Location: /users');
                exit;
            }

            $user->first_name = htmlspecialchars($data['first_name']);
            $user->last_name = htmlspecialchars($data['last_name']);
            $user->email = htmlspecialchars($data['email']);
            $user->role_id = intval($data['role_id']);
            $user->company_id = isset($data['company_id']) ? intval($data['company_id']) : null;
            $user->save();

            $_SESSION['success'] = 'User updated successfully.';
            header('Location: /users');
            exit;
        }

        // Fetch the user for the edit form
        $this->editForm($requestMethod, $data);
    }

    /**
     * Delete a user (soft delete).
     */
    public function delete($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid user ID.';
                header('Location: /users');
                exit;
            }

            $this->authMiddleware->hasPermission('delete_users');

            // Soft delete the user
            $user = (new User())->find($id);
            if (!$user) {
                $_SESSION['error'] = 'User not found.';
                header('Location: /users');
                exit;
            }

            $user->is_deleted = true;
            $user->save();

            $_SESSION['success'] = 'User deleted successfully.';
            header('Location: /users');
            exit;
        }

        // Fetch the user for the delete confirmation form
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid user ID.';
            header('Location: /users');
            exit;
        }

        $user = (new User())->find($id);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /users');
            exit;
        }

        // Render the delete confirmation form
        include __DIR__ . '/../Views/Users/delete.php';
    }
}