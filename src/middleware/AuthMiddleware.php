<?php
namespace App\Middleware;

class AuthMiddleware {
    /**
     * Ensure the user is authenticated (logged in).
     */
    public function isAuthenticated() {
        // Check if the user is logged in by verifying the session
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'You must be logged in to access this page.';
            header('Location: /login');
            exit;
        }

        // Optionally, validate the session against the database
        $userModel = new \App\Models\User();
        $user = $userModel->find($_SESSION['user_id']);
        if (!$user || !$user->is_active) {
            unset($_SESSION['user_id']);
            $_SESSION['error'] = 'Your account is no longer active. Please contact support.';
            header('Location: /login');
            exit;
        }
    }

    /**
     * Ensure the user has a specific permission.
     */
    public function hasPermission($requiredPermission) {
        // Ensure the user is authenticated first
        $this->isAuthenticated();

        // Check if the user has the required permission
        $userPermissions = $_SESSION['user']['permissions'] ?? [];
        if (!in_array($requiredPermission, $userPermissions)) {
            $_SESSION['error'] = 'You do not have permission to access this resource.';
            header('Location: /dashboard'); // Redirect to a safe page
            exit;
        }
    }

    /**
     * Ensure the user has one of multiple required permissions.
     */
    public function hasAnyPermission(array $requiredPermissions) {
        // Ensure the user is authenticated first
        $this->isAuthenticated();

        // Check if the user has at least one of the required permissions
        $userPermissions = $_SESSION['user']['permissions'] ?? [];
        foreach ($requiredPermissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                return true;
            }
        }

        $_SESSION['error'] = 'You do not have permission to access this resource.';
        header('Location: /dashboard'); // Redirect to a safe page
        exit;
    }

    /**
     * Ensure the user has all of multiple required permissions.
     */
    public function hasAllPermissions(array $requiredPermissions) {
        // Ensure the user is authenticated first
        $this->isAuthenticated();

        // Check if the user has all of the required permissions
        $userPermissions = $_SESSION['user']['permissions'] ?? [];
        foreach ($requiredPermissions as $permission) {
            if (!in_array($permission, $userPermissions)) {
                $_SESSION['error'] = 'You do not have sufficient permissions to access this resource.';
                header('Location: /dashboard'); // Redirect to a safe page
                exit;
            }
        }
    }
}