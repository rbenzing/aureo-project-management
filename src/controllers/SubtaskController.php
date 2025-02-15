<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Subtask;
use App\Utils\Validator;

class SubtaskController {
    public function __construct() {
        // Ensure the user has the required permission
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('view_subtasks'); // Default permission for all actions
    }

    /**
     * Create a new subtask.
     */
    public function create($taskId) {
        // Permission check
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('create_subtasks');

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header('Location: /tasks/view.php?id=' . $taskId);
            exit;
        }

        // Validate input data
        $validator = new Validator($_POST, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:todo,in_progress,done',
            'estimated_time' => 'nullable|integer|min:0',
        ]);
        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header('Location: /tasks/view.php?id=' . $taskId);
            exit;
        }

        // Save the subtask
        $subtask = new Subtask();
        $subtask->task_id = $taskId;
        $subtask->title = htmlspecialchars($_POST['title']);
        $subtask->description = htmlspecialchars($_POST['description'] ?? null);
        $subtask->status = $_POST['status'] ?? 'todo';
        $subtask->estimated_time = $_POST['estimated_time'] ?? null;
        $subtask->save();

        // Success message
        $_SESSION['success'] = 'Subtask created successfully.';
        header('Location: /tasks/view.php?id=' . $taskId);
        exit;
    }

    /**
     * Update an existing subtask.
     */
    public function update($subtaskId) {
        // Permission check
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('edit_subtasks');

        // Fetch the subtask to get its task ID
        $subtask = (new Subtask())->find($subtaskId);
        if (!$subtask) {
            $_SESSION['error'] = 'Subtask not found.';
            header('Location: /tasks');
            exit;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header('Location: /tasks/view.php?id=' . $subtask->task_id);
            exit;
        }

        // Validate input data
        $validator = new Validator($_POST, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:todo,in_progress,done',
            'estimated_time' => 'nullable|integer|min:0',
        ]);
        if ($validator->fails()) {
            $_SESSION['error'] = 'Validation failed: ' . implode(', ', $validator->errors());
            header('Location: /tasks/view.php?id=' . $subtask->task_id);
            exit;
        }

        // Update the subtask
        $subtask->title = htmlspecialchars($_POST['title']);
        $subtask->description = htmlspecialchars($_POST['description'] ?? null);
        $subtask->status = $_POST['status'] ?? 'todo';
        $subtask->estimated_time = $_POST['estimated_time'] ?? null;
        $subtask->save();

        // Success message
        $_SESSION['success'] = 'Subtask updated successfully.';
        header('Location: /tasks/view.php?id=' . $subtask->task_id);
        exit;
    }

    /**
     * Delete a subtask (soft delete).
     */
    public function delete($subtaskId) {
        // Permission check
        $middleware = new AuthMiddleware();
        $middleware->hasPermission('delete_subtasks');

        // Fetch the subtask to get its task ID
        $subtask = (new Subtask())->find($subtaskId);
        if (!$subtask) {
            $_SESSION['error'] = 'Subtask not found.';
            header('Location: /tasks');
            exit;
        }

        // Soft delete the subtask
        $subtask->is_deleted = true;
        $subtask->save();

        // Success message
        $_SESSION['success'] = 'Subtask deleted successfully.';
        header('Location: /tasks/view.php?id=' . $subtask->task_id);
        exit;
    }
}