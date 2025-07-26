<?php
// file: Utils/Sort.php
declare(strict_types=1);

namespace App\Utils;

/**
 * Sort Utility Class
 * 
 * Provides common sorting functionality for views
 */
class Sort
{
    /**
     * Generate sort URL for a field
     * 
     * @param string $field Field to sort by
     * @param string $currentField Currently sorted field
     * @param string $currentDir Current sort direction
     * @param string $fieldParam URL parameter name for the field
     * @param string $dirParam URL parameter name for the direction
     * @return string Generated URL with sort parameters
     */
    public static function getUrl(
        string $field, 
        string $currentField, 
        string $currentDir, 
        string $fieldParam = 'sort', 
        string $dirParam = 'dir'
    ): string {
        $newDir = ($field === $currentField && $currentDir === 'desc') ? 'asc' : 'desc';
        
        // Start with current GET parameters
        $params = $_GET;
        
        // Update sort parameters
        $params[$fieldParam] = $field;
        $params[$dirParam] = $newDir;
        
        // Build query string
        return '?' . http_build_query($params);
    }

    /**
     * Generate HTML for sort indicator
     * 
     * @param string $field Field to check
     * @param string $currentField Currently sorted field
     * @param string $currentDir Current sort direction
     * @return string HTML for sort indicator
     */
    public static function getIndicator(string $field, string $currentField, string $currentDir): string
    {
        if ($field !== $currentField) {
            return '<svg class="w-3 h-3 ml-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>';
        }
        
        return $currentDir === 'asc' 
            ? '<svg class="w-3 h-3 ml-1 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
               </svg>'
            : '<svg class="w-3 h-3 ml-1 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
               </svg>';
    }

    /**
     * Sort an array of objects by a given field
     * 
     * @param array $items Array of objects to sort
     * @param string $field Field to sort by
     * @param string $direction Sort direction (asc or desc)
     * @return array Sorted array
     */
    public static function sortObjects(array $items, string $field, string $direction): array
    {
        usort($items, function($a, $b) use ($field, $direction) {
            // Handle special cases for different field types
            switch ($field) {
                case 'priority':
                    // Convert priority to numeric value for sorting
                    $priorityValues = [
                        'high' => 3,
                        'medium' => 2,
                        'low' => 1,
                        'none' => 0
                    ];
                    
                    $aValue = $priorityValues[$a->priority ?? 'none'] ?? 0;
                    $bValue = $priorityValues[$b->priority ?? 'none'] ?? 0;
                    break;
                    
                case 'due_date':
                    // Handle null dates
                    $aValue = ($a->due_date ?? null) ? strtotime($a->due_date) : PHP_INT_MAX;
                    $bValue = ($b->due_date ?? null) ? strtotime($b->due_date) : PHP_INT_MAX;
                    break;
                    
                case 'assigned_to':
                    // For assigned_to, if we have the user's name use that, otherwise use the ID
                    $aValue = isset($a->first_name) ? 
                        strtolower($a->first_name . ' ' . $a->last_name) : 
                        ($a->assigned_to ?? '');
                    
                    $bValue = isset($b->first_name) ? 
                        strtolower($b->first_name . ' ' . $b->last_name) : 
                        ($b->assigned_to ?? '');
                    break;
                    
                default:
                    // Default case for other fields
                    $aValue = $a->$field ?? '';
                    $bValue = $b->$field ?? '';
                    
                    // Convert to lowercase for string comparison
                    if (is_string($aValue)) {
                        $aValue = strtolower($aValue);
                    }
                    if (is_string($bValue)) {
                        $bValue = strtolower($bValue);
                    }
            }
            
            // Compare values based on direction
            if ($aValue == $bValue) {
                return 0;
            }
            
            if ($direction === 'asc') {
                return $aValue <=> $bValue;
            } else {
                return $bValue <=> $aValue;
            }
        });
        
        return $items;
    }
}