<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Role;
use App\Utils\Validator;

class RoleController {
    public function __construct() {
        // Ensure the user has the required permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('manage_roles'); // Default permission for all actions
    }

    /**
     * Display a list of roles (paginated).
     */
    public function index() {
        // Fetch all roles from the database (paginated)
        $roles = (new Role())->getAllPaginated(10); // Paginate results (e.g., 10 per page)
        
        include __DIR__ . '/../views/roles/index.php';
    }

    /**
     * View details of a specific role.
     */
    public function view($id) {
        // Fetch a single role by ID
        $role = (new Role())->find($id);
        if (!$role) {
            $_SESSION['error'] = 'Role not found.';
            header('Location: /roles');
            exit;
        }

        // Fetch permissions associated with the role
        $permissions = (new \App\Models\Permission())->getByRoleId($id);

        // Render the view
        include __DIR__ . '/../views/roles/view.php';
    }

    /**
     * Show the form to create a new role.
     */
    public function createForm() {
        // Fetch all permissions for the form
        $permissions = (new \App\Models\Permission())->getAll();
        include __DIR__ . '/../views/roles/create.php';
    }

    /**
     * Create a new role.
     */
    public function create($data) {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header('Location: /create_role');
            exit;
        }

        // Validate input data
        $validator = new Validator($data, [
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header('Location: /create_role');
            exit;
        }

        // Create the role
        $role = new Role();
        $role->name = htmlspecialchars($data['name']);
        $role->description = htmlspecialchars($data['description'] ?? null);
        $role->save();

        // Assign permissions to the role
        if (isset($data['permissions'])) {
            foreach ($data['permissions'] as $permissionId) {
                $role->assignPermission($permissionId);
            }
        }

        $_SESSION['success'] = 'Role created successfully.';
        header('Location: /roles');
        exit;
    }

    /**
     * Show the form to edit an existing role.
     */
    public function editForm($id) {
        // Fetch the role
        $role = (new Role())->find($id);
        if (!$role) {
            $_SESSION['error'] = 'Role not found.';
            header('Location: /roles');
            exit;
        }

        // Fetch all permissions and the role's current permissions
        $permissions = (new \App\Models\Permission())->getAll();
        $rolePermissions = $role->getPermissions();

        // Render the edit form
        include __DIR__ . '/../views/roles/edit.php';
    }

    /**
     * Update an existing role.
     */
    public function update($data, $id) {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header("Location: /edit_role?id=$id");
            exit;
        }

        // Validate input data
        $validator = new Validator($data, [
            'name' => 'required|string|max:100|unique:roles,name,' . $id,
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header("Location: /edit_role?id=$id");
            exit;
        }

        // Update the role
        $role = (new Role())->find($id);
        if (!$role) {
            $_SESSION['error'] = 'Role not found.';
            header('Location: /roles');
            exit;
        }

        $role->name = htmlspecialchars($data['name']);
        $role->description = htmlspecialchars($data['description'] ?? null);
        $role->save();

        // Update role permissions
        $role->syncPermissions($data['permissions'] ?? []);

        $_SESSION['success'] = 'Role updated successfully.';
        header('Location: /roles');
        exit;
    }

    /**
     * Delete a role (soft delete).
     */
    public function delete($id) {
        // Soft delete the role
        $role = (new Role())->find($id);
        if (!$role) {
            $_SESSION['error'] = 'Role not found.';
            header('Location: /roles');
            exit;
        }

        // Mark as deleted instead of permanently removing
        $role->is_deleted = true;
        $role->save();

        $_SESSION['success'] = 'Role deleted successfully.';
        header('Location: /roles');
        exit;
    }
}