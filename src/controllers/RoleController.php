<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Role;
use App\Models\Permission;
use App\Utils\Validator;

class RoleController
{
    private $authMiddleware;
    private $csrfMiddleware;

    public function __construct()
    {
        // Ensure the user has the required permission
        $this->authMiddleware = new AuthMiddleware();
        $this->csrfMiddleware = new CsrfMiddleware();
        $this->authMiddleware->hasPermission('manage_roles'); // Default permission for all actions
    }

    /**
     * Display a list of roles (paginated).
     */
    public function index($requestMethod, $data)
    {
        // Fetch all roles from the database (paginated)
        $limit = 10; // Number of roles per page
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $roles = (new Role())->getAllPaginated($limit, $page);

        // Prepare pagination data
        $totalRoles = (new Role())->countAll();
        $totalPages = ceil($totalRoles / $limit);
        $prevPage = $page > 1 ? $page - 1 : null;
        $nextPage = $page < $totalPages ? $page + 1 : null;

        $pagination = [
            'prev_page' => $prevPage,
            'next_page' => $nextPage,
        ];

        include __DIR__ . '/../Views/Roles/index.php';
    }

    /**
     * View details of a specific role.
     */
    public function view($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid role ID.';
            header('Location: /roles');
            exit;
        }

        // Fetch a single role by ID
        $role = (new Role())->find($id);
        if (!$role) {
            $_SESSION['error'] = 'Role not found.';
            header('Location: /roles');
            exit;
        }

        // Fetch permissions associated with the role
        $permissions = (new Permission())->getByRoleId($id);
        $role->permissions = $permissions;

        // Render the view
        include __DIR__ . '/../Views/Roles/view.php';
    }

    /**
     * Show the form to create a new role.
     */
    public function createForm($requestMethod, $data)
    {
        $this->authMiddleware->hasPermission('create_roles');

        // Fetch all permissions for the form
        $permissions = (new Permission())->getAll();
        include __DIR__ . '/../Views/Roles/create.php';
    }

    /**
     * Create a new role.
     */
    public function create($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $this->authMiddleware->hasPermission('create_roles');

            // Validate CSRF token
            $this->csrfMiddleware->handleToken();

            // Validate input data
            $validator = new Validator($data, [
                'name' => 'required|string|max:100|unique:roles,name',
                'description' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header('Location: /roles/create');
                exit;
            }

            // Create the role
            $role = new Role();
            $role->name = htmlspecialchars($data['name']);
            $role->description = htmlspecialchars($data['description'] ?? null);
            $role->save();

            // Assign permissions to the role
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                foreach ($data['permissions'] as $permissionId) {
                    $role->assignPermission((int)$permissionId);
                }
            }

            $_SESSION['success'] = 'Role created successfully.';
            header('Location: /roles');
            exit;
        }

        // Render the create form
        $this->createForm($requestMethod, $data);
    }

    /**
     * Show the form to edit an existing role.
     */
    public function editForm($requestMethod, $data)
    {
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid role ID.';
            header('Location: /roles');
            exit;
        }

        $this->authMiddleware->hasPermission('edit_roles');

        // Fetch the role
        $role = (new Role())->find($id);
        if (!$role) {
            $_SESSION['error'] = 'Role not found.';
            header('Location: /roles');
            exit;
        }

        // Fetch all permissions and the role's current permissions
        $allPermissions = (new Permission())->getAll();
        $rolePermissions = $role->getPermissions();
        $role->permissions = $rolePermissions;

        // Render the edit form
        include __DIR__ . '/../Views/Roles/edit.php';
    }

    /**
     * Update an existing role.
     */
    public function update($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid role ID.';
                header('Location: /roles');
                exit;
            }

            $this->authMiddleware->hasPermission('edit_roles');

            // Validate CSRF token
            $this->csrfMiddleware->handleToken();

            // Validate input data
            $validator = new Validator($data, [
                'name' => 'required|string|max:100|unique:roles,name,' . $id,
                'description' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
                header("Location: /roles/edit/$id");
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
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->syncPermissions(array_map('intval', $data['permissions']));
            } else {
                $role->syncPermissions([]);
            }

            $_SESSION['success'] = 'Role updated successfully.';
            header('Location: /roles');
            exit;
        }

        // Fetch the role for the edit form
        $this->editForm($requestMethod, $data);
    }

    /**
     * Delete a role (soft delete).
     */
    public function delete($requestMethod, $data)
    {
        if ($requestMethod === 'POST') {
            $id = $data['id'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Invalid role ID.';
                header('Location: /roles');
                exit;
            }

            $this->authMiddleware->hasPermission('delete_roles');

            // Validate CSRF token
            $this->csrfMiddleware->handleToken();

            // Soft delete the role
            $role = (new Role())->find($id);
            if (!$role) {
                $_SESSION['error'] = 'Role not found.';
                header('Location: /roles');
                exit;
            }

            $role->is_deleted = true;
            $role->save();

            $_SESSION['success'] = 'Role deleted successfully.';
            header('Location: /roles');
            exit;
        }

        // Fetch the role for the delete confirmation form
        $id = $data['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid role ID.';
            header('Location: /roles');
            exit;
        }

        $role = (new Role())->find($id);
        if (!$role) {
            $_SESSION['error'] = 'Role not found.';
            header('Location: /roles');
            exit;
        }

        // Render the delete confirmation form
        include __DIR__ . '/../Views/Roles/delete.php';
    }
}