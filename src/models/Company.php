<?php
// file: Models/Company.php
declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * Company Model
 * 
 * Handles all company-related database operations
 */
class Company extends BaseModel
{
    protected string $table = 'companies';
    
    /**
     * Company properties
     */
    public ?int $id = null;
    public string $name;
    public ?int $user_id = null;
    public ?string $address = null;
    public ?string $phone = null;
    public string $email;
    public ?string $website = null;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    
    /**
     * Define fillable fields
     */
    protected array $fillable = [
        'name', 'user_id', 'address', 'phone', 'email', 'website'
    ];
    
    /**
     * Define searchable fields
     */
    protected array $searchable = [
        'name', 'email', 'address'
    ];
    
    /**
     * Define validation rules
     */
    protected array $validationRules = [
        'name' => ['required', 'string'],
        'email' => ['required', 'email', 'unique'],
        'website' => ['url']
    ];

    /**
     * Get company users
     * 
     * @param int $companyId
     * @return array
     * @throws RuntimeException
     */
    public function getUsers(int $companyId): array
    {
        try {
            // First check direct user assignments
            $sql = "SELECT u.* FROM users u 
                    WHERE u.company_id = :company_id 
                    AND u.is_deleted = 0";
            
            $stmt = $this->db->executeQuery($sql, [':company_id' => $companyId]);
            $directUsers = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Then check indirect assignments via user_companies junction table
            $sql = "SELECT u.* FROM users u
                    JOIN user_companies uc ON u.id = uc.user_id
                    WHERE uc.company_id = :company_id
                    AND u.is_deleted = 0";
                    
            $stmt = $this->db->executeQuery($sql, [':company_id' => $companyId]);
            $indirectUsers = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Combine and remove duplicates
            $allUsers = array_merge($directUsers, $indirectUsers);
            $uniqueUsers = [];
            $userIds = [];
            
            foreach ($allUsers as $user) {
                if (!in_array($user->id, $userIds)) {
                    $userIds[] = $user->id;
                    $uniqueUsers[] = $user;
                }
            }
            
            return $uniqueUsers;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get company users: " . $e->getMessage());
        }
    }

    /**
     * Get company projects
     * 
     * @return array
     * @throws RuntimeException
     */
    public function getProjects(): array
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Company ID is not set");
            }

            // First get direct project associations
            $sql = "SELECT p.*, ps.name as status_name 
                    FROM projects p
                    JOIN statuses_project ps ON p.status_id = ps.id
                    WHERE p.company_id = :company_id 
                    AND p.is_deleted = 0";
                    
            $stmt = $this->db->executeQuery($sql, [':company_id' => $this->id]);
            $directProjects = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Then get indirect associations via company_projects junction table
            $sql = "SELECT p.*, ps.name as status_name 
                    FROM projects p
                    JOIN company_projects cp ON p.id = cp.project_id
                    JOIN statuses_project ps ON p.status_id = ps.id
                    WHERE cp.company_id = :company_id
                    AND p.is_deleted = 0";
                    
            $stmt = $this->db->executeQuery($sql, [':company_id' => $this->id]);
            $indirectProjects = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Combine and remove duplicates
            $allProjects = array_merge($directProjects, $indirectProjects);
            $uniqueProjects = [];
            $projectIds = [];
            
            foreach ($allProjects as $project) {
                if (!in_array($project->id, $projectIds)) {
                    $projectIds[] = $project->id;
                    $uniqueProjects[] = $project;
                }
            }
            
            return $uniqueProjects;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get company projects: " . $e->getMessage());
        }
    }

    /**
     * Get all companies without pagination
     * 
     * @return array
     */
    public function getAllCompanies(): array
    {
        try {
            $sql = "SELECT c.*, u.first_name as owner_first_name, u.last_name as owner_last_name
                    FROM companies c
                    LEFT JOIN users u ON c.user_id = u.id
                    WHERE c.is_deleted = 0
                    ORDER BY c.name ASC";
                    
            $stmt = $this->db->executeQuery($sql);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get all companies: " . $e->getMessage());
        }
    }

    /**
     * Get recent projects for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getRecentProjectsByUser(int $userId): array
    {
        try {
            $sql = "SELECT DISTINCT p.*, c.name as company_name, ps.name as status_name
                    FROM projects p 
                    JOIN companies c ON p.company_id = c.id 
                    JOIN statuses_project ps ON p.status_id = ps.id
                    WHERE (
                        c.user_id = :user_id 
                        OR p.owner_id = :user_id
                        OR EXISTS (
                            SELECT 1 FROM user_projects up 
                            WHERE up.project_id = p.id AND up.user_id = :user_id
                        )
                    )
                    AND p.is_deleted = 0 
                    AND c.is_deleted = 0
                    ORDER BY p.updated_at DESC 
                    LIMIT 5";

            $stmt = $this->db->executeQuery($sql, [':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get recent projects: " . $e->getMessage());
        }
    }

    /**
     * Associate a user with this company
     * 
     * @param int $userId
     * @return bool
     */
    public function addUser(int $userId): bool
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Company ID is not set");
            }
            
            $sql = "INSERT INTO user_companies (user_id, company_id) 
                    VALUES (:user_id, :company_id)
                    ON DUPLICATE KEY UPDATE user_id = :user_id";
                    
            return $this->db->executeInsertUpdate($sql, [
                ':user_id' => $userId,
                ':company_id' => $this->id
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to add user to company: " . $e->getMessage());
        }
    }
    
    /**
     * Remove a user association from this company
     * 
     * @param int $userId
     * @return bool
     */
    public function removeUser(int $userId): bool
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Company ID is not set");
            }
            
            $sql = "DELETE FROM user_companies 
                    WHERE user_id = :user_id AND company_id = :company_id";
                    
            return $this->db->executeInsertUpdate($sql, [
                ':user_id' => $userId,
                ':company_id' => $this->id
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to remove user from company: " . $e->getMessage());
        }
    }
    
    /**
     * Associate a project with this company
     * 
     * @param int $projectId
     * @return bool
     */
    public function addProject(int $projectId): bool
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Company ID is not set");
            }
            
            $sql = "INSERT INTO company_projects (company_id, project_id) 
                    VALUES (:company_id, :project_id)
                    ON DUPLICATE KEY UPDATE company_id = :company_id";
                    
            return $this->db->executeInsertUpdate($sql, [
                ':company_id' => $this->id,
                ':project_id' => $projectId
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to add project to company: " . $e->getMessage());
        }
    }
    
    /**
     * Remove a project association from this company
     * 
     * @param int $projectId
     * @return bool
     */
    public function removeProject(int $projectId): bool
    {
        try {
            if (!$this->id) {
                throw new RuntimeException("Company ID is not set");
            }
            
            $sql = "DELETE FROM company_projects 
                    WHERE company_id = :company_id AND project_id = :project_id";
                    
            return $this->db->executeInsertUpdate($sql, [
                ':company_id' => $this->id,
                ':project_id' => $projectId
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to remove project from company: " . $e->getMessage());
        }
    }
}