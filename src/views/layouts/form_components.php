<?php
//file: Views/Layouts/form_components.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

/**
 * Standardized form components for consistent UI across all forms
 */

/**
 * Render a text input field
 */
function renderTextInput(array $options): string
{
    $defaults = [
        'type' => 'text',
        'required' => false,
        'disabled' => false,
        'placeholder' => '',
        'value' => '',
        'class' => '',
        'help_text' => '',
        'error' => ''
    ];
    
    $options = array_merge($defaults, $options);
    
    $baseClasses = 'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100';
    $errorClasses = $options['error'] ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600';
    $disabledClasses = $options['disabled'] ? 'bg-gray-100 dark:bg-gray-600 cursor-not-allowed' : '';
    
    $classes = trim($baseClasses . ' ' . $errorClasses . ' ' . $disabledClasses . ' ' . $options['class']);
    
    $html = '<div class="mb-4">';
    
    // Label
    if (!empty($options['label'])) {
        $html .= '<label for="' . htmlspecialchars($options['name']) . '" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">';
        $html .= htmlspecialchars($options['label']);
        if ($options['required']) {
            $html .= ' <span class="text-red-500">*</span>';
        }
        $html .= '</label>';
    }
    
    // Input field
    $html .= '<input';
    $html .= ' type="' . htmlspecialchars($options['type']) . '"';
    $html .= ' name="' . htmlspecialchars($options['name']) . '"';
    $html .= ' id="' . htmlspecialchars($options['name']) . '"';
    $html .= ' value="' . htmlspecialchars($options['value']) . '"';
    $html .= ' class="' . htmlspecialchars($classes) . '"';
    
    if ($options['placeholder']) {
        $html .= ' placeholder="' . htmlspecialchars($options['placeholder']) . '"';
    }
    
    if ($options['required']) {
        $html .= ' required';
    }
    
    if ($options['disabled']) {
        $html .= ' disabled';
    }
    
    $html .= '>';
    
    // Help text
    if ($options['help_text']) {
        $html .= '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">' . htmlspecialchars($options['help_text']) . '</p>';
    }
    
    // Error message
    if ($options['error']) {
        $html .= '<p class="mt-1 text-sm text-red-600 dark:text-red-400">' . htmlspecialchars($options['error']) . '</p>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render a textarea field
 */
function renderTextarea(array $options): string
{
    $defaults = [
        'required' => false,
        'disabled' => false,
        'placeholder' => '',
        'value' => '',
        'rows' => 4,
        'class' => '',
        'help_text' => '',
        'error' => ''
    ];
    
    $options = array_merge($defaults, $options);
    
    $baseClasses = 'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100';
    $errorClasses = $options['error'] ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600';
    $disabledClasses = $options['disabled'] ? 'bg-gray-100 dark:bg-gray-600 cursor-not-allowed' : '';
    
    $classes = trim($baseClasses . ' ' . $errorClasses . ' ' . $disabledClasses . ' ' . $options['class']);
    
    $html = '<div class="mb-4">';
    
    // Label
    if (!empty($options['label'])) {
        $html .= '<label for="' . htmlspecialchars($options['name']) . '" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">';
        $html .= htmlspecialchars($options['label']);
        if ($options['required']) {
            $html .= ' <span class="text-red-500">*</span>';
        }
        $html .= '</label>';
    }
    
    // Textarea field
    $html .= '<textarea';
    $html .= ' name="' . htmlspecialchars($options['name']) . '"';
    $html .= ' id="' . htmlspecialchars($options['name']) . '"';
    $html .= ' rows="' . (int)$options['rows'] . '"';
    $html .= ' class="' . htmlspecialchars($classes) . '"';
    
    if ($options['placeholder']) {
        $html .= ' placeholder="' . htmlspecialchars($options['placeholder']) . '"';
    }
    
    if ($options['required']) {
        $html .= ' required';
    }
    
    if ($options['disabled']) {
        $html .= ' disabled';
    }
    
    $html .= '>';
    $html .= htmlspecialchars($options['value']);
    $html .= '</textarea>';
    
    // Help text
    if ($options['help_text']) {
        $html .= '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">' . htmlspecialchars($options['help_text']) . '</p>';
    }
    
    // Error message
    if ($options['error']) {
        $html .= '<p class="mt-1 text-sm text-red-600 dark:text-red-400">' . htmlspecialchars($options['error']) . '</p>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render a select dropdown field
 */
function renderSelect(array $options): string
{
    $defaults = [
        'required' => false,
        'disabled' => false,
        'value' => '',
        'options' => [],
        'class' => '',
        'help_text' => '',
        'error' => '',
        'empty_option' => ''
    ];
    
    $options = array_merge($defaults, $options);
    
    $baseClasses = 'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100';
    $errorClasses = $options['error'] ? 'border-red-300 dark:border-red-600' : 'border-gray-300 dark:border-gray-600';
    $disabledClasses = $options['disabled'] ? 'bg-gray-100 dark:bg-gray-600 cursor-not-allowed' : '';
    
    $classes = trim($baseClasses . ' ' . $errorClasses . ' ' . $disabledClasses . ' ' . $options['class']);
    
    $html = '<div class="mb-4">';
    
    // Label
    if (!empty($options['label'])) {
        $html .= '<label for="' . htmlspecialchars($options['name']) . '" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">';
        $html .= htmlspecialchars($options['label']);
        if ($options['required']) {
            $html .= ' <span class="text-red-500">*</span>';
        }
        $html .= '</label>';
    }
    
    // Select field
    $html .= '<select';
    $html .= ' name="' . htmlspecialchars($options['name']) . '"';
    $html .= ' id="' . htmlspecialchars($options['name']) . '"';
    $html .= ' class="' . htmlspecialchars($classes) . '"';
    
    if ($options['required']) {
        $html .= ' required';
    }
    
    if ($options['disabled']) {
        $html .= ' disabled';
    }
    
    $html .= '>';
    
    // Empty option
    if ($options['empty_option']) {
        $html .= '<option value="">' . htmlspecialchars($options['empty_option']) . '</option>';
    }
    
    // Options
    foreach ($options['options'] as $value => $label) {
        $selected = (string)$value === (string)$options['value'] ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($value) . '"' . $selected . '>';
        $html .= htmlspecialchars($label);
        $html .= '</option>';
    }
    
    $html .= '</select>';
    
    // Help text
    if ($options['help_text']) {
        $html .= '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">' . htmlspecialchars($options['help_text']) . '</p>';
    }
    
    // Error message
    if ($options['error']) {
        $html .= '<p class="mt-1 text-sm text-red-600 dark:text-red-400">' . htmlspecialchars($options['error']) . '</p>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render a checkbox field
 */
function renderCheckbox(array $options): string
{
    $defaults = [
        'checked' => false,
        'disabled' => false,
        'value' => '1',
        'class' => '',
        'help_text' => '',
        'error' => ''
    ];
    
    $options = array_merge($defaults, $options);
    
    $html = '<div class="mb-4">';
    $html .= '<div class="flex items-center">';
    
    // Checkbox input
    $html .= '<input';
    $html .= ' type="checkbox"';
    $html .= ' name="' . htmlspecialchars($options['name']) . '"';
    $html .= ' id="' . htmlspecialchars($options['name']) . '"';
    $html .= ' value="' . htmlspecialchars($options['value']) . '"';
    $html .= ' class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded ' . htmlspecialchars($options['class']) . '"';
    
    if ($options['checked']) {
        $html .= ' checked';
    }
    
    if ($options['disabled']) {
        $html .= ' disabled';
    }
    
    $html .= '>';
    
    // Label
    if (!empty($options['label'])) {
        $html .= '<label for="' . htmlspecialchars($options['name']) . '" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">';
        $html .= htmlspecialchars($options['label']);
        $html .= '</label>';
    }
    
    $html .= '</div>';
    
    // Help text
    if ($options['help_text']) {
        $html .= '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">' . htmlspecialchars($options['help_text']) . '</p>';
    }
    
    // Error message
    if ($options['error']) {
        $html .= '<p class="mt-1 text-sm text-red-600 dark:text-red-400">' . htmlspecialchars($options['error']) . '</p>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render form buttons
 */
function renderFormButtons(array $options = []): string
{
    $defaults = [
        'submit_text' => 'Save',
        'cancel_url' => '',
        'show_cancel' => true,
        'additional_buttons' => []
    ];
    
    $options = array_merge($defaults, $options);
    
    $html = '<div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">';
    
    // Cancel button
    if ($options['show_cancel'] && $options['cancel_url']) {
        $html .= '<a href="' . htmlspecialchars($options['cancel_url']) . '" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">';
        $html .= 'Cancel';
        $html .= '</a>';
    }
    
    // Additional buttons
    foreach ($options['additional_buttons'] as $button) {
        $html .= '<button type="' . htmlspecialchars($button['type'] ?? 'button') . '" class="' . htmlspecialchars($button['class'] ?? 'bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md') . '">';
        $html .= htmlspecialchars($button['text']);
        $html .= '</button>';
    }
    
    // Submit button
    $html .= '<button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">';
    $html .= htmlspecialchars($options['submit_text']);
    $html .= '</button>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render CSRF token field
 */
function renderCSRFToken(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'] ?? '') . '">';
}
