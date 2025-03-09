<?php
// file: Models/ProjectTemplate.php
declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;
use RuntimeException;

/**
 * ProjectTemplate Model
 * 
 * Handles all project template-related database operations
 */
class ProjectTemplate extends BaseModel
{
    protected string $table = 'project_templates';
    
    /**
     * ProjectTemplate properties
     */
    public ?int $id = null;
    public string $name;
    public string $description;
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
     * Define validation rules
     */
    protected array $validationRules = [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['required', 'string']
    ];
    
    /**
     * Get all available templates for a company
     * 
     * @param int|null $companyId Company ID or null for global templates
     * @return array Array of template objects
     */
    public function getAvailableTemplates(?int $companyId = null): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE is_deleted = 0
                    AND (company_id IS NULL OR company_id = :company_id)
                    ORDER BY is_default DESC, name ASC";
                    
            $stmt = $this->db->executeQuery($sql, [':company_id' => $companyId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get available templates: " . $e->getMessage());
        }
    }
    
    /**
     * Get default template
     * 
     * @param int|null $companyId Company ID or null for global default
     * @return object|null Default template object or null if not found
     */
    public function getDefaultTemplate(?int $companyId = null): ?object
    {
        try {
            // First try company-specific default
            if ($companyId) {
                $sql = "SELECT * FROM {$this->table} 
                        WHERE is_deleted = 0 
                        AND is_default = 1 
                        AND company_id = :company_id 
                        LIMIT 1";
                        
                $stmt = $this->db->executeQuery($sql, [':company_id' => $companyId]);
                $template = $stmt->fetch(PDO::FETCH_OBJ);
                
                if ($template) {
                    return $template;
                }
            }
            
            // Fall back to global default
            $sql = "SELECT * FROM {$this->table} 
                    WHERE is_deleted = 0 
                    AND is_default = 1 
                    AND company_id IS NULL 
                    LIMIT 1";
                    
            $stmt = $this->db->executeQuery($sql);
            return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to get default template: " . $e->getMessage());
        }
    }
    
    /**
     * Set a template as default for a company
     * 
     * @param int $templateId Template ID to set as default
     * @param int|null $companyId Company ID or null for global default
     * @return bool Success status
     */
    public function setDefaultTemplate(int $templateId, ?int $companyId = null): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Clear existing defaults for this company
            $sql = "UPDATE {$this->table} 
                    SET is_default = 0 
                    WHERE " . ($companyId ? "company_id = :company_id" : "company_id IS NULL");
                    
            $params = $companyId ? [':company_id' => $companyId] : [];
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