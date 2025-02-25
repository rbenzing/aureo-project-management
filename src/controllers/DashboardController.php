<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Milestone;
use RuntimeException;
use InvalidArgumentException;

class DashboardController
{
    private AuthMiddleware $authMiddleware;
    private User $userModel;
    private Project $projectModel;
    private Task $taskModel;
    private Milestone $milestoneModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->userModel = new User();
        $this->projectModel = new Project();
        $this->taskModel = new Task();
        $this->milestoneModel = new Milestone();
    }

    /**
     * Display dashboard overview
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_dashboard');

            $userId = $_SESSION['user']['id'] ?? null;
            if (!$userId) {
                throw new RuntimeException('User session not found');
            }

            // Fetch user data with roles and permissions
            $user = $this->userModel->findWithDetails($userId);
            if (!$user || $user->is_deleted) {
                throw new RuntimeException('User not found');
            }

            // Fetch dashboard data
            $dashboardData = $this->getDashboardData($userId);

            include __DIR__ . '/../Views/Dashboard/index.php';

        } catch (RuntimeException $e) {
            $_SESSION['error'] = 'Session expired. Please log in again.';
            header('Location: /login');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in DashboardController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the dashboard.';
            header('Location: /login');
            exit;
        }
    }

    /**
     * Get all required dashboard data
     * @param int $userId
     * @return array
     * @throws RuntimeException
     */
    private function getDashboardData(int $userId): array
    {
        try {
            // Recent projects
            $recentProjects = $this->projectModel->getRecentByUser($userId, 5);

            // Recent tasks
            $recentTasks = $this->taskModel->getRecentByUser($userId, 5);

            // Upcoming milestones
            $upcomingMilestones = $this->milestoneModel->getAllWithProgress(5, 1, [
                'due_date' => ['>', date('Y-m-d')],
                'is_deleted' => 0
            ]);

            // Task summary
            $taskSummary = [
                'total' => $this->taskModel->count(['assigned_to' => $userId, 'is_deleted' => 0]),
                'completed' => $this->taskModel->count([
                    'assigned_to' => $userId,
                    'status_id' => 6, // Completed status
                    'is_deleted' => 0
                ]),
                'in_progress' => $this->taskModel->count([
                    'assigned_to' => $userId,
                    'status_id' => 2, // In Progress status
                    'is_deleted' => 0
                ]),
                'overdue' => $this->taskModel->count([
                    'assigned_to' => $userId,
                    'due_date' => ['<', date('Y-m-d')],
                    'status_id' => ['NOT IN', [6, 7]], // Not completed or cancelled
                    'is_deleted' => 0
                ])
            ];

            // Time tracking summary
            $timeTrackingSummary = [
                'total_hours' => $this->taskModel->getTotalTimeSpent($userId),
                'billable_hours' => $this->taskModel->getTotalBillableTime($userId),
                'this_week' => $this->taskModel->getWeeklyTimeSpent($userId),
                'this_month' => $this->taskModel->getMonthlyTimeSpent($userId)
            ];

            // Project status summary
            $projectSummary = [
                'total' => $this->projectModel->count(['owner_id' => $userId, 'is_deleted' => 0]),
                'in_progress' => $this->projectModel->count([
                    'owner_id' => $userId,
                    'status_id' => 2, // In Progress status
                    'is_deleted' => 0
                ]),
                'completed' => $this->projectModel->count([
                    'owner_id' => $userId,
                    'status_id' => 3, // Completed status
                    'is_deleted' => 0
                ]),
                'delayed' => $this->projectModel->count([
                    'owner_id' => $userId,
                    'status_id' => 5, // Delayed status
                    'is_deleted' => 0
                ])
            ];

            // Active timer check
            $activeTimer = $_SESSION['active_timer'] ?? null;
            if ($activeTimer) {
                $activeTask = $this->taskModel->find($activeTimer['task_id']);
                if ($activeTask && !$activeTask->is_deleted) {
                    $activeTimer['task'] = $activeTask;
                    $activeTimer['duration'] = time() - $activeTimer['start_time'];
                }
            }

            return [
                'recent_projects' => $recentProjects,
                'recent_tasks' => $recentTasks,
                'upcoming_milestones' => $upcomingMilestones,
                'task_summary' => $taskSummary,
                'time_tracking_summary' => $timeTrackingSummary,
                'project_summary' => $projectSummary,
                'active_timer' => $activeTimer
            ];

        } catch (\Exception $e) {
            throw new RuntimeException('Failed to fetch dashboard data: ' . $e->getMessage());
        }
    }
}