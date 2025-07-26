<?php
// file: Controllers/DashboardController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Sprint;
use App\Core\Database;
use App\Core\Config;
use App\Services\SecurityService;
use RuntimeException;
use InvalidArgumentException;

class DashboardController
{
    private AuthMiddleware $authMiddleware;
    private User $userModel;
    private Project $projectModel;
    private Task $taskModel;
    private Milestone $milestoneModel;
    private Sprint $sprintModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->userModel = new User();
        $this->projectModel = new Project();
        $this->taskModel = new Task();
        $this->milestoneModel = new Milestone();
        $this->sprintModel = new Sprint();
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
            // Basic authentication check - no specific permission required for dashboard access
            if (!$this->authMiddleware->isAuthenticated()) {
                throw new RuntimeException('Authentication required.');
            }

            $userId = $_SESSION['user']['profile']['id'] ?? null;
            if (!$userId) {
                throw new RuntimeException('Session expired. Please log in again.');
            }

            // Fetch user data with roles and permissions
            $user = $this->userModel->findWithDetails($userId);
            if (!$user || $user->is_deleted) {
                throw new RuntimeException('User not found. Please try again.');
            }

            // Get user permissions for conditional content display
            $userPermissions = $_SESSION['user']['permissions'] ?? [];

            // Fetch dashboard data only if user has relevant permissions
            try {
                $dashboardData = $this->getDashboardData($userId, $userPermissions);
            } catch (\Exception $e) {
                // Log the error but don't redirect to login - show error on dashboard
                $_SESSION['error'] = Config::getErrorMessage(
                    $e,
                    'DashboardController::index (data fetch)',
                    'Some dashboard data could not be loaded. Please refresh the page.'
                );

                // Provide empty dashboard data structure so the page can still render
                $dashboardData = [
                    'recent_projects' => [],
                    'recent_tasks' => [],
                    'upcoming_milestones' => [],
                    'task_summary' => [
                        'total' => 0,
                        'completed' => 0,
                        'in_progress' => 0,
                        'overdue' => 0,
                        'sprint_ready' => 0,
                        'backlog_items' => 0,
                        'bugs' => 0
                    ],
                    'time_tracking_summary' => [
                        'total_hours' => 0,
                        'billable_hours' => 0,
                        'this_week' => 0,
                        'this_month' => 0
                    ],
                    'project_summary' => [
                        'total' => 0,
                        'in_progress' => 0,
                        'completed' => 0,
                        'delayed' => 0,
                        'on_hold' => 0
                    ],
                    'active_timer' => null,
                    'active_sprints' => [],
                    'story_points_summary' => [
                        'this_week' => 0,
                        'total' => 0,
                        'completed' => 0,
                        'remaining' => 0
                    ],
                    'task_type_distribution' => [
                        'story' => 0,
                        'bug' => 0,
                        'task' => 0,
                        'epic' => 0
                    ],
                    'priority_tasks' => []
                ];
            }

            include BASE_PATH . '/../Views/Dashboard/index.php';

        } catch (\Exception $e) {
            // Only redirect to login for actual authentication/authorization errors
            $_SESSION['error'] = Config::getErrorMessage(
                $e,
                'DashboardController::index (critical)',
                'A critical error occurred while loading the dashboard.'
            );
            header('Location: /login');
            exit;
        }
    }

    /**
     * Get dashboard data based on user permissions
     * @param int $userId
     * @param array $userPermissions
     * @return array
     * @throws RuntimeException
     */
    private function getDashboardData(int $userId, array $userPermissions): array
    {
        try {
            // Initialize empty data structure
            $dashboardData = [
                'recent_projects' => [],
                'recent_tasks' => [],
                'upcoming_milestones' => [],
                'task_summary' => [
                    'total' => 0,
                    'completed' => 0,
                    'in_progress' => 0,
                    'overdue' => 0,
                    'sprint_ready' => 0,
                    'backlog_items' => 0,
                    'bugs' => 0
                ],
                'time_tracking_summary' => [
                    'total_hours' => 0,
                    'billable_hours' => 0,
                    'this_week' => 0,
                    'this_month' => 0
                ],
                'project_summary' => [
                    'total' => 0,
                    'in_progress' => 0,
                    'completed' => 0,
                    'delayed' => 0,
                    'on_hold' => 0
                ],
                'active_timer' => null,
                'active_sprints' => [],
                'story_points_summary' => [
                    'this_week' => 0,
                    'total' => 0,
                    'completed' => 0,
                    'remaining' => 0
                ],
                'task_type_distribution' => [
                    'story' => 0,
                    'bug' => 0,
                    'task' => 0,
                    'epic' => 0
                ],
                'priority_tasks' => []
            ];

            // Only fetch data if user has relevant permissions
            if (in_array('view_projects', $userPermissions)) {
                try {
                    $dashboardData['recent_projects'] = $this->projectModel->getRecentByUser($userId, 5);
                    error_log("Dashboard: Fetched " . count($dashboardData['recent_projects']) . " recent projects for user $userId");
                } catch (\Exception $e) {
                    error_log("Error fetching recent projects: " . $e->getMessage());
                    $dashboardData['recent_projects'] = [];
                }
            }

            if (in_array('view_tasks', $userPermissions)) {
                try {
                    $dashboardData['recent_tasks'] = $this->taskModel->getByUserId($userId, 15);
                    error_log("Dashboard: Fetched " . count($dashboardData['recent_tasks']) . " recent tasks for user $userId");

                    $dashboardData['priority_tasks'] = $this->getPriorityTasks($userId);
                    error_log("Dashboard: Fetched " . count($dashboardData['priority_tasks']) . " priority tasks for user $userId");

                    $dashboardData['task_type_distribution'] = $this->getTaskTypeDistribution($userId);
                    error_log("Dashboard: Task type distribution - " . json_encode($dashboardData['task_type_distribution']));

                    $dashboardData['story_points_summary'] = $this->getStoryPointsSummary($userId);
                    error_log("Dashboard: Story points summary - " . json_encode($dashboardData['story_points_summary']));
                } catch (\Exception $e) {
                    error_log("Error fetching task data: " . $e->getMessage());
                    // Keep default empty values for task data
                }
            }

            if (in_array('view_milestones', $userPermissions)) {
                try {
                    $dashboardData['upcoming_milestones'] = $this->milestoneModel->getAllWithProgress(5, 1, [
                        'due_date' => ['>', date('Y-m-d')],
                        'is_deleted' => 0
                    ]);
                    error_log("Dashboard: Fetched " . count($dashboardData['upcoming_milestones']) . " upcoming milestones");
                } catch (\Exception $e) {
                    error_log("Error fetching milestones: " . $e->getMessage());
                    $dashboardData['upcoming_milestones'] = [];
                }
            }

            // Enhanced Task summary with new metrics - only if user has task permissions
            if (in_array('view_tasks', $userPermissions)) {
                try {
                    $dashboardData['task_summary'] = $this->getTaskSummary($userId);
                    error_log("Dashboard: Task summary - " . json_encode($dashboardData['task_summary']));
                } catch (\Exception $e) {
                    error_log("Error fetching task summary: " . $e->getMessage());
                    // Keep default empty values
                }
            }

            // Enhanced Time tracking summary - only if user has time tracking permissions
            if (in_array('view_time_tracking', $userPermissions)) {
                try {
                    $dashboardData['time_tracking_summary'] = [
                        'total_hours' => $this->taskModel->getTotalTimeSpent($userId),
                        'billable_hours' => $this->taskModel->getTotalBillableTime($userId),
                        'this_week' => $this->taskModel->getWeeklyTimeSpent($userId),
                        'this_month' => $this->taskModel->getMonthlyTimeSpent($userId)
                    ];
                    error_log("Dashboard: Time tracking summary - " . json_encode($dashboardData['time_tracking_summary']));
                } catch (\Exception $e) {
                    error_log("Error fetching time tracking data: " . $e->getMessage());
                    // Keep default empty values
                }
            }

            // Project status summary - only if user has project permissions
            if (in_array('view_projects', $userPermissions)) {
                try {
                    $dashboardData['project_summary'] = [
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
                            'status_id' => 6, // Delayed status
                            'is_deleted' => 0
                        ]),
                        'on_hold' => $this->projectModel->count([
                            'owner_id' => $userId,
                            'status_id' => 4, // On Hold status
                            'is_deleted' => 0
                        ])
                    ];
                    error_log("Dashboard: Project summary - " . json_encode($dashboardData['project_summary']));
                } catch (\Exception $e) {
                    error_log("Error fetching project summary: " . $e->getMessage());
                    // Keep default empty values
                }
            }

            // Active sprints with enhanced data - only if user has sprint permissions
            if (in_array('view_sprints', $userPermissions)) {
                try {
                    $dashboardData['active_sprints'] = $this->sprintModel->getProjectSprints($userId, 'active');
                    error_log("Dashboard: Fetched " . count($dashboardData['active_sprints']) . " active sprints for user $userId");
                } catch (\Exception $e) {
                    error_log("Error fetching active sprints: " . $e->getMessage());
                    $dashboardData['active_sprints'] = [];
                }
            }

            // Active timer check - only if user has time tracking permissions
            if (in_array('view_time_tracking', $userPermissions)) {
                try {
                    $activeTimer = $_SESSION['active_timer'] ?? null;
                    if ($activeTimer) {
                        $activeTask = $this->taskModel->find($activeTimer['task_id']);
                        if ($activeTask && !$activeTask->is_deleted) {
                            $activeTimer['task'] = $activeTask;
                            $activeTimer['duration'] = time() - $activeTimer['start_time'];
                            error_log("Dashboard: Active timer found for task " . $activeTimer['task_id'] . " (" . $activeTask->title . ")");
                        } else {
                            error_log("Dashboard: Active timer references deleted/missing task " . $activeTimer['task_id']);
                        }
                    } else {
                        error_log("Dashboard: No active timer for user $userId");
                    }
                    $dashboardData['active_timer'] = $activeTimer;
                } catch (\Exception $e) {
                    error_log("Error processing active timer: " . $e->getMessage());
                    $dashboardData['active_timer'] = null;
                }
            }

            return $dashboardData;

        } catch (\Exception $e) {
            throw new RuntimeException('Failed to fetch dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Get story points summary for the user
     * @param int $userId
     * @return array
     */
    private function getStoryPointsSummary(int $userId): array
    {
        try {
            $db = Database::getInstance();

            // Story points completed this week
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $weekEnd = date('Y-m-d', strtotime('sunday this week'));

            $sql = "SELECT COALESCE(SUM(story_points), 0) as points_this_week
                    FROM tasks
                    WHERE assigned_to = :user_id
                    AND status_id = 6
                    AND complete_date BETWEEN :week_start AND :week_end
                    AND is_deleted = 0";

            $stmt = $db->executeQuery($sql, [
                ':user_id' => $userId,
                ':week_start' => $weekStart,
                ':week_end' => $weekEnd
            ]);
            $weeklyPoints = (int) $stmt->fetchColumn();

            // Total story points assigned
            $sql = "SELECT COALESCE(SUM(story_points), 0) as total_points
                    FROM tasks
                    WHERE assigned_to = :user_id
                    AND story_points IS NOT NULL
                    AND is_deleted = 0";

            $stmt = $db->executeQuery($sql, [':user_id' => $userId]);
            $totalPoints = (int) $stmt->fetchColumn();

            // Story points completed
            $sql = "SELECT COALESCE(SUM(story_points), 0) as completed_points
                    FROM tasks
                    WHERE assigned_to = :user_id
                    AND status_id = 6
                    AND story_points IS NOT NULL
                    AND is_deleted = 0";

            $stmt = $db->executeQuery($sql, [':user_id' => $userId]);
            $completedPoints = (int) $stmt->fetchColumn();

            $result = [
                'this_week' => $weeklyPoints,
                'total' => $totalPoints,
                'completed' => $completedPoints,
                'remaining' => $totalPoints - $completedPoints
            ];

            error_log("Story points calculation for user $userId: " . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            throw new RuntimeException("Error calculating story points summary: " . $e->getMessage());
        }
    }

    /**
     * Get task type distribution for the user
     * @param int $userId
     * @return array
     */
    private function getTaskTypeDistribution(int $userId): array
    {
        try {
            $db = Database::getInstance();

            $sql = "SELECT
                        task_type,
                        COUNT(*) as count
                    FROM tasks
                    WHERE assigned_to = :user_id
                    AND is_deleted = 0
                    GROUP BY task_type";

            $stmt = $db->executeQuery($sql, [':user_id' => $userId]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $distribution = [
                'story' => 0,
                'bug' => 0,
                'task' => 0,
                'epic' => 0
            ];

            foreach ($results as $result) {
                $distribution[$result['task_type']] = (int) $result['count'];
            }

            error_log("Task type distribution for user $userId: " . json_encode($distribution));
            return $distribution;
        } catch (\Exception $e) {
            throw new RuntimeException("Error calculating task type distribution: " . $e->getMessage());
        }
    }

    /**
     * Get priority tasks for the user (high priority and due soon)
     * @param int $userId
     * @return array
     */
    private function getPriorityTasks(int $userId): array
    {
        try {
            $db = Database::getInstance();

            $sql = "SELECT t.*,
                        p.name as project_name,
                        ts.name as status_name
                    FROM tasks t
                    LEFT JOIN projects p ON t.project_id = p.id
                    LEFT JOIN statuses_task ts ON t.status_id = ts.id
                    WHERE t.assigned_to = :user_id
                    AND t.status_id NOT IN (5, 6) -- Not closed or completed
                    AND t.is_deleted = 0
                    AND (
                        t.priority = 'high'
                        OR t.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                    )
                    ORDER BY
                        CASE
                            WHEN t.due_date < CURDATE() THEN 0  -- Overdue
                            WHEN t.due_date = CURDATE() THEN 1  -- Due today
                            ELSE 2                             -- Due soon
                        END,
                        t.due_date ASC,
                        t.priority DESC
                    LIMIT 5";

            $stmt = $db->executeQuery($sql, [':user_id' => $userId]);
            $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

            error_log("Priority tasks for user $userId: " . count($results) . " tasks found");
            return $results;
        } catch (\Exception $e) {
            throw new RuntimeException("Error fetching priority tasks: " . $e->getMessage());
        }
    }

    /**
     * Get comprehensive task summary with accurate counts
     * @param int $userId
     * @return array
     */
    private function getTaskSummary(int $userId): array
    {
        try {
            $today = date('Y-m-d');

            // Get total tasks
            $total = $this->taskModel->count(['assigned_to' => $userId, 'is_deleted' => 0]);

            // Get completed tasks (status_id = 6)
            $completed = $this->taskModel->count([
                'assigned_to' => $userId,
                'status_id' => 6,
                'is_deleted' => 0
            ]);

            // Get overdue tasks (due_date < today AND status NOT IN [5,6])
            $overdue = $this->taskModel->count([
                'assigned_to' => $userId,
                'due_date' => ['<', $today],
                'status_id' => ['NOT IN', [5, 6]],
                'is_deleted' => 0
            ]);

            // Get in progress tasks (status_id = 2) excluding overdue ones
            $inProgressTotal = $this->taskModel->count([
                'assigned_to' => $userId,
                'status_id' => 2,
                'is_deleted' => 0
            ]);

            $inProgressOverdue = $this->taskModel->count([
                'assigned_to' => $userId,
                'status_id' => 2,
                'due_date' => ['<', $today],
                'is_deleted' => 0
            ]);

            $inProgress = $inProgressTotal - $inProgressOverdue;

            // Calculate open/other tasks (all remaining tasks)
            $openOther = $total - $completed - $inProgress - $overdue;

            return [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'overdue' => $overdue,
                'open_other' => max(0, $openOther), // Ensure non-negative
                'sprint_ready' => $this->taskModel->count([
                    'assigned_to' => $userId,
                    'is_ready_for_sprint' => 1,
                    'is_deleted' => 0
                ]),
                'backlog_items' => $this->getUserBacklogCount($userId),
                'bugs' => $this->taskModel->count([
                    'assigned_to' => $userId,
                    'task_type' => 'bug',
                    'status_id' => ['NOT IN', [5, 6]],
                    'is_deleted' => 0
                ])
            ];
        } catch (\Exception $e) {
            error_log("Error getting task summary: " . $e->getMessage());
            return [
                'total' => 0,
                'completed' => 0,
                'in_progress' => 0,
                'overdue' => 0,
                'open_other' => 0,
                'sprint_ready' => 0,
                'backlog_items' => 0,
                'bugs' => 0
            ];
        }
    }

    /**
     * Get user-specific backlog count
     * @param int $userId
     * @return int
     */
    private function getUserBacklogCount(int $userId): int
    {
        try {
            $db = Database::getInstance();

            $sql = "SELECT COUNT(*)
                    FROM tasks t
                    LEFT JOIN projects p ON t.project_id = p.id
                    WHERE t.assigned_to = :user_id
                    AND t.is_deleted = 0
                    AND p.is_deleted = 0
                    AND t.id NOT IN (
                        SELECT st.task_id FROM sprint_tasks st
                        JOIN sprints s ON st.sprint_id = s.id
                        WHERE s.status_id IN (1, 2) AND s.is_deleted = 0
                    )";

            $stmt = $db->executeQuery($sql, [':user_id' => $userId]);
            $count = (int) $stmt->fetchColumn();

            error_log("Backlog count for user $userId: $count items");
            return $count;
        } catch (\Exception $e) {
            throw new RuntimeException("Error counting user backlog items: " . $e->getMessage());
        }
    }
}