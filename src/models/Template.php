<?php
//file: Models/Template.php
declare(strict_types=1);

namespace App\Models;


use PDO;
use RuntimeException;

/**
 * Template Model
 * 
 * Handles all template-related database operations for projects, tasks, milestones, and sprints
 */
class Template extends BaseModel
{
    protected string $table = 'templates';
    
    /**
     * Template properties
     */
    public ?int $id = null;
    public string $name;
    public string $description;
    public string $template_type = 'project';
    public ?int $company_id = null;
    public bool $is_default = false;
    public bool $is_deleted = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    
    /**
     * Define fillable fields
     */
    protected array $fillable = [
        'name', 
        'description',
        'template_type',
        'company_id', 
        'is_default'
    ];
    
    /**
     * Define searchable fields
     */
    protected array $searchable = [
        'name', 
        'description'
    ];
    
    /**
     * Valid template types
     */
    public const TEMPLATE_TYPES = [
        'project' => 'Project',
        'task' => 'Task',
        'milestone' => 'Milestone',
        'sprint' => 'Sprint'
    ];
    
    /**
     * Get all available templates for a company and type
     * 
     * @param string $templateType Template type (project, task, milestone, sprint)
     * @param int|null $companyId Company ID or null for global templates
     * @return array Array of template objects
     */
    public function getAvailableTemplates(string $templateType = 'project', ?int $companyId = null): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE is_deleted = 0
                    AND template_type = :template_type
                    AND (company_id IS NULL OR company_id = :company_id)
                    ORDER BY is_default DESC, name ASC";
                    
            $stmt = $this->db->executeQuery($sql, [
                ':template_type' => $templateType,
                ':company_id' => $companyId
            ]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get available templates: " . $e->getMessage());
        }
    }
    
    /**
     * Get all templates with optional filtering
     * 
     * @param array $filters Optional filters (template_type, company_id, etc.)
     * @param int $limit Items per page
     * @param int $page Current page number
     * @return array Array of template objects
     */
    public function getAllTemplates(array $filters = [], int $limit = 10, int $page = 1): array
    {
        try {
            $offset = ($page - 1) * $limit;
            $whereConditions = ['is_deleted = 0'];
            $params = [];
            
            if (!empty($filters['template_type'])) {
                $whereConditions[] = 'template_type = :template_type';
                $params[':template_type'] = $filters['template_type'];
            }
            
            if (isset($filters['company_id'])) {
                if ($filters['company_id'] === null) {
                    $whereConditions[] = 'company_id IS NULL';
                } else {
                    $whereConditions[] = 'company_id = :company_id';
                    $params[':company_id'] = $filters['company_id'];
                }
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            $sql = "SELECT * FROM {$this->table} 
                    WHERE {$whereClause}
                    ORDER BY template_type ASC, is_default DESC, name ASC
                    LIMIT :limit OFFSET :offset";
                    
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            $stmt = $this->db->executeQuery($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get templates: " . $e->getMessage());
        }
    }
    
    /**
     * Get default template for a specific type
     * 
     * @param string $templateType Template type (project, task, milestone, sprint)
     * @param int|null $companyId Company ID or null for global default
     * @return object|null Default template object or null if not found
     */
    public function getDefaultTemplate(string $templateType = 'project', ?int $companyId = null): ?object
    {
        try {
            // First try company-specific default
            if ($companyId) {
                $sql = "SELECT * FROM {$this->table} 
                        WHERE is_deleted = 0 
                        AND is_default = 1 
                        AND template_type = :template_type
                        AND company_id = :company_id 
                        LIMIT 1";
                        
                $stmt = $this->db->executeQuery($sql, [
                    ':template_type' => $templateType,
                    ':company_id' => $companyId
                ]);
                $template = $stmt->fetch(PDO::FETCH_OBJ);
                
                if ($template) {
                    return $template;
                }
            }
            
            // Fall back to global default
            $sql = "SELECT * FROM {$this->table} 
                    WHERE is_deleted = 0 
                    AND is_default = 1 
                    AND template_type = :template_type
                    AND company_id IS NULL 
                    LIMIT 1";
                    
            $stmt = $this->db->executeQuery($sql, [':template_type' => $templateType]);
            return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get default template: " . $e->getMessage());
        }
    }
    
    /**
     * Set a template as default for a company and type
     * 
     * @param int $templateId Template ID to set as default
     * @param string $templateType Template type (project, task, milestone, sprint)
     * @param int|null $companyId Company ID or null for global default
     * @return bool Success status
     */
    public function setDefaultTemplate(int $templateId, string $templateType = 'project', ?int $companyId = null): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Clear existing defaults for this company and type
            $sql = "UPDATE {$this->table} 
                    SET is_default = 0 
                    WHERE template_type = :template_type 
                    AND " . ($companyId ? "company_id = :company_id" : "company_id IS NULL");
                    
            $params = [':template_type' => $templateType];
            if ($companyId) {
                $params[':company_id'] = $companyId;
            }
            $this->db->executeInsertUpdate($sql, $params);
            
            // Set new default
            $sql = "UPDATE {$this->table} 
                    SET is_default = 1 
                    WHERE id = :template_id";
                    
            $this->db->executeInsertUpdate($sql, [':template_id' => $templateId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to set default template: " . $e->getMessage());
        }
    }
}
