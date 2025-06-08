<?php
// file: Controllers/RoleController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Middleware\AuthMiddleware;
use App\Models\Role;
use App\Models\Permission;
use App\Utils\Validator;
use RuntimeException;
use InvalidArgumentException;

class RoleController
{
    private AuthMiddleware $authMiddleware;
    private Role $roleModel;
    private Permission $permissionModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }

    /**
     * Display paginated list of roles
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_roles');

            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $limit = Config::get('max_pages', 10);

            $results = $this->roleModel->getAllWithDetails($page, $limit);
            $roles = $results['records'];
            $totalRoles = $results['total'];
            $totalPages = ceil($totalRoles / $limit);

            include __DIR__ . '/../Views/Roles/index.php';
        } catch (\Exception $e) {
            error_log("Exception in RoleController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching roles.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * View role details
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function view(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_roles');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid role ID');
            }

            $role = $this->roleModel->findWithPermissions($id);
            if (!$role || $role->is_deleted) {
                throw new InvalidArgumentException('Role not found');
            }

            include __DIR__ . '/../Views/Roles/view.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /roles');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in RoleController::view: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching role details.';
            header('Location: /roles');
            exit;
        }
    }

    /**
     * Display role creation form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function createForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_roles');
            
            $permissions = $this->permissionModel->getOrganizedPermissions();
            
            include __DIR__ . '/../Views/Roles/create.php';
        } catch (\Exception $e) {
            error_log("Exception in RoleController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the creation form.';
            header('Location: /roles');
            exit;
        }
    }

    /**
     * Create new role
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
            $this->authMiddleware->hasPermission('create_roles');

            $validator = new Validator($data, [
                'name' => 'required|string|max:100|unique:roles,name',
                'description' => 'nullable|string|max:500',
                'permissions' => 'array'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            // Begin transaction
            $this->roleModel->beginTransaction();

            try {
                $roleData = [
                    'name' => htmlspecialchars($data['name']),
                    'description' => isset($data['description']) ? 
                        htmlspecialchars($data['description']) : null
                ];

                $roleId = $this->roleModel->create($roleData);

                // Assign permissions if any
                if (!empty($data['permissions'])) {
                    $permissions = array_map('intval', $data['permissions']);
                    $this->permissionModel->assignToRole($roleId, $permissions);
                }

                $this->roleModel->commit();

                $_SESSION['success'] = 'Role created successfully.';
                header('Location: /roles');
                exit;

            } catch (\Exception $e) {
                $this->roleModel->rollBack();
                throw $e;
            }

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /roles/create');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in RoleController::create: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while creating the role.';
            header('Location: /roles/create');
            exit;
        }
    }

    /**
     * Display role edit form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function editForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('edit_roles');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid role ID');
            }

            $role = $this->roleModel->findWithPermissions($id);
            if (!$role || $role->is_deleted) {
                throw new InvalidArgumentException('Role not found');
            }

            $permissions = $this->permissionModel->getOrganizedPermissions();

            include __DIR__ . '/../Views/Roles/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /roles');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in RoleController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /roles');
            exit;
        }
    }

    /**
     * Update existing role
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
            $this->authMiddleware->hasPermission('edit_roles');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid role ID');
            }

            $validator = new Validator($data, [
                'name' => 'required|string|max:100|unique:roles,name',
                'description' => 'nullable|string|max:500',
                'permissions' => 'array'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            // Begin transaction
            $this->roleModel->beginTransaction();

            try {
                $roleData = [
                    'name' => htmlspecialchars($data['name']),
                    'description' => isset($data['description']) ? 
                        htmlspecialchars($data['description']) : null
                ];

                $this->roleModel->update($id, $roleData);

                // Sync permissions
                $permissions = !empty($data['permissions']) ? 
                    array_map('intval', $data['permissions']) : [];
                $this->roleModel->syncPermissions($id, $permissions);

                $this->roleModel->commit();

                $_SESSION['success'] = 'Role updated successfully.';
                header('Location: /roles');
                exit;

            } catch (\Exception $e) {
                $this->roleModel->rollBack();
                throw $e;
            }

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header("Location: /roles/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            error_log("Exception in RoleController::update: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while updating the role.';
            header("Location: /roles/edit/{$id}");
            exit;
        }
    }

    /**
     * Delete role (soft delete)
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function delete(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /roles');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('delete_roles');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid role ID');
            }

            // Check if role exists and is not already deleted
            $role = $this->roleModel->find($id);
            if (!$role || $role->is_deleted) {
                throw new InvalidArgumentException('Role not found');
            }

            // Check if role is in use
            $users = $this->roleModel->getUsers($id);
            if (!empty($users)) {
                throw new InvalidArgumentException('Cannot delete role as it is currently assigned to one or more users.');
            }

            $this->roleModel->update($id, ['is_deleted' => true]);

            $_SESSION['success'] = 'Role deleted successfully.';
            header('Location: /roles');
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /roles');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in RoleController::delete: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while deleting the role.';
            header('Location: /roles');
            exit;
        }
    }
}