<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Utils\Validator;

class UserController {
    public function __construct() {
        // Ensure the user has the required permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('manage_users'); // Default permission for all actions
    }

    /**
     * Display a list of users (paginated).
     */
    public function index() {
        // Fetch all active users from the database (paginated)
        $users = (new User())->getAllPaginated(10); // Paginate results (e.g., 10 per page)
        
        include __DIR__ . '/../views/users/index.php';
    }

    /**
     * View details of a specific user.
     */
    public function view($id) {
        // Fetch a single user by ID (excluding soft-deleted users)
        $user = (new User())->find($id);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /users/index.php');
            exit;
        }

        // Render the view
        include __DIR__ . '/../views/users/view.php';
    }

    /**
     * Show the form to create a new user.
     */
    public function createForm() {
        // Render the create form
        include __DIR__ . '/../views/users/create.php';
    }

    /**
     * Create a new user.
     */
    public function create($data) {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || !$this->validateCsrfToken($data['csrf_token'])) {
            $_SESSION['error'] = 'Invalid or expired CSRF token.';
            header('Location: /users/create.php');
            exit;
        }

        // Validate input data
        $validator = new Validator($data, [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header('Location: /users/create.php');
            exit;
        }

        // Create the user
        $user = new User();
        $user->first_name = htmlspecialchars($data['first_name']);
        $user->last_name = htmlspecialchars($data['last_name']);
        $user->email = htmlspecialchars($data['email']);
        $user->password_hash = password_hash('default_password', PASSWORD_ARGON2ID); // Set a default password
        $user->role_id = $data['role_id'];
        $user->activation_token = bin2hex(random_bytes(16));
        $user->activation_token_expires_at = date('Y-m-d H:i:s', strtotime('+1 day')); // Token expires in 1 day
        $user->is_active = false; // Require activation via email
        $user->save();

        // Send activation email
        \App\Utils\Email::sendActivationEmail($user);

        $_SESSION['success'] = 'User created successfully.';
        header('Location: /users/index.php');
        exit;
    }

    /**
     * Show the form to edit an existing user.
     */
    public function editForm($id) {
        // Fetch the user (excluding soft-deleted users)
        $user = (new User())->find($id);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /users/index.php');
            exit;
        }

        // Render the edit form
        include __DIR__ . '/../views/users/edit.php';
    }

    /**
     * Update an existing user.
     */
    public function update($data, $id) {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || !$this->validateCsrfToken($data['csrf_token'])) {
            $_SESSION['error'] = 'Invalid or expired CSRF token.';
            header("Location: /users/edit.php?id=$id");
            exit;
        }

        // Validate input data
        $validator = new Validator($data, [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header("Location: /users/edit.php?id=$id");
            exit;
        }

        // Update the user
        $user = (new User())->find($id);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /users/index.php');
            exit;
        }

        $user->first_name = htmlspecialchars($data['first_name']);
        $user->last_name = htmlspecialchars($data['last_name']);
        $user->email = htmlspecialchars($data['email']);
        $user->role_id = $data['role_id'];
        $user->save();

        $_SESSION['success'] = 'User updated successfully.';
        header('Location: /users/index.php');
        exit;
    }

    /**
     * Delete a user (soft delete).
     */
    public function delete($id) {
        // Soft delete the user
        $user = (new User())->find($id);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /users/index.php');
            exit;
        }

        // Mark as deleted instead of permanently removing
        $user->is_deleted = true;
        $user->save();

        $_SESSION['success'] = 'User deleted successfully.';
        header('Location: /users/index.php');
        exit;
    }

    /**
     * Activate a user account using the activation token.
     */
    public function activateAccount($token) {
        // Find the user with a valid activation token
        $user = (new User())->findByActivationToken($token);
        if (!$user || strtotime($user->activation_token_expires_at) < time()) {
            $_SESSION['error'] = 'Invalid or expired activation token.';
            header('Location: /auth/login.php');
            exit;
        }

        // Activate the account
        $user->activation_token = null;
        $user->activation_token_expires_at = null;
        $user->is_active = true;
        $user->save();

        $_SESSION['success'] = 'Account activated successfully. You can now log in.';
        header('Location: /auth/login.php');
        exit;
    }

    /**
     * Validate a CSRF token against the database.
     */
    private function validateCsrfToken($token) {
        $storedToken = (new \App\Models\CsrfToken())->findByToken($token);
        return $storedToken && strtotime($storedToken->expires_at) > time();
    }
}