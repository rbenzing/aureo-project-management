<?php
// file: Controllers/MilestoneController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Middleware\AuthMiddleware;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Template;
use App\Utils\Validator;
use RuntimeException;
use InvalidArgumentException;

class MilestoneController
{
    private AuthMiddleware $authMiddleware;
    private Milestone $milestoneModel;
    private Project $projectModel;
    private Template $templateModel;

    public function __construct()
    {
        $this->authMiddleware = new AuthMiddleware();
        $this->milestoneModel = new Milestone();
        $this->projectModel = new Project();
        $this->templateModel = new Template();
    }

    /**
     * Display paginated list of milestones
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function index(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_milestones');

            $project_id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $limit = Config::get('max_pages', 10);

            if (!empty($project_id)) {
                $project = $this->projectModel->findWithDetails($project_id);
                if (!$project || $project->is_deleted) {
                    throw new InvalidArgumentException('Project not found');
                }
                // Get milestones only for this specific project
                $milestones = $this->milestoneModel->getByProjectId($project_id);
            } else {
                $projects = $this->projectModel->getAllWithDetails($limit, $page);
                // Get all milestones when no specific project is selected
                $milestones = $this->milestoneModel->getAllWithProgress($limit, $page);
            }

            $totalMilestones = $this->milestoneModel->count(['is_deleted' => 0]);
            $totalPages = ceil($totalMilestones / $limit);

            include __DIR__ . '/../Views/Milestones/index.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /milestones');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in MilestoneController::index: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching milestones.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * View milestone details
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function view(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('view_milestones');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid milestone ID');
            }

            $milestone = $this->milestoneModel->findWithDetails($id);
            if (!$milestone || $milestone->is_deleted) {
                throw new InvalidArgumentException('Milestone not found');
            }

            $project = $this->projectModel->findWithDetails($milestone->project_id);
            if (!$project) {
                throw new RuntimeException('Associated project not found');
            }

            // Initialize optional variables
            $epic = null;
            $relatedMilestones = [];

            // If this is a milestone under an epic, fetch the epic
            if ($milestone->epic_id) {
                $epic = $this->milestoneModel->find($milestone->epic_id);
            }

            // If this is an epic, fetch its milestones
            if ($milestone->milestone_type === 'epic') {
                $relatedMilestones = $this->milestoneModel->getEpicMilestones($id);
            }

            include __DIR__ . '/../Views/Milestones/view.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /milestones');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in MilestoneController::view: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while fetching milestone details.';
            header('Location: /milestones');
            exit;
        }
    }

    /**
     * Display milestone creation form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function createForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('create_milestones');

            $projects = $this->projectModel->getAll(['is_deleted' => 0]);
            $statuses = $this->milestoneModel->getMilestoneStatuses();
            $epics = $this->milestoneModel->getProjectEpics($data['project_id'] ?? 0);

            // Get company ID from user session if available
            $companyId = $_SESSION['user']['profile']['company_id'] ?? null;
            // Load templates available for this company or global templates
            $templates = $this->templateModel->getAvailableTemplates('milestone', $companyId);

            include __DIR__ . '/../Views/Milestones/create.php';
        } catch (\Exception $e) {
            error_log("Exception in MilestoneController::createForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the creation form.';
            header('Location: /milestones');
            exit;
        }
    }

    /**
     * Create new milestone
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
            $this->authMiddleware->hasPermission('create_milestones');

            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'milestone_type' => 'required|in:epic,milestone',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date|after:start_date',
                'status_id' => 'required|integer|exists:statuses_milestone,id',
                'project_id' => 'required|integer|exists:projects,id',
                'epic_id' => 'nullable|integer|exists:milestones,id'
            ]);

            // If this is an epic itself, check for circular references
            if (isset($data['milestone_type']) && $data['milestone_type'] === 'epic' && isset($id)) {
                $this->milestoneModel->checkCircularEpicReference($id, $data['epic_id']);
            }

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $milestoneData = [
                'title' => htmlspecialchars($data['title']),
                'description' => isset($data['description']) ?
                    htmlspecialchars($data['description']) : null,
                'milestone_type' => $data['milestone_type'],
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT),
                'project_id' => filter_var($data['project_id'], FILTER_VALIDATE_INT),
                'epic_id' => isset($data['epic_id']) ?
                    filter_var($data['epic_id'], FILTER_VALIDATE_INT) : null
            ];

            $milestoneId = $this->milestoneModel->create($milestoneData);

            $_SESSION['success'] = 'Milestone created successfully.';
            header('Location: /milestones/view/' . $milestoneId);
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header('Location: /milestones/create');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in MilestoneController::create: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while creating the milestone.';
            header('Location: /milestones/create');
            exit;
        }
    }

    /**
     * Display milestone edit form
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function editForm(string $requestMethod, array $data): void
    {
        try {
            $this->authMiddleware->hasPermission('edit_milestones');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid milestone ID');
            }

            $milestone = $this->milestoneModel->find($id);
            if (!$milestone || $milestone->is_deleted) {
                throw new InvalidArgumentException('Milestone not found');
            }

            $projects = $this->projectModel->getAll(['is_deleted' => 0]);
            $statuses = $this->milestoneModel->getMilestoneStatuses();
            $epics = $this->milestoneModel->getProjectEpics($milestone->project_id);

            // Get company ID from milestone's project
            $companyId = null;
            if ($milestone->project_id) {
                $project = $this->projectModel->find($milestone->project_id);
                $companyId = $project->company_id ?? null;
            }
            // Load templates available for this company or global templates
            $templates = $this->templateModel->getAvailableTemplates('milestone', $companyId);

            include __DIR__ . '/../Views/Milestones/edit.php';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /milestones');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in MilestoneController::editForm: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading the edit form.';
            header('Location: /milestones');
            exit;
        }
    }

    /**
     * Update existing milestone
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
            $this->authMiddleware->hasPermission('edit_milestones');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid milestone ID');
            }

            $validator = new Validator($data, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'milestone_type' => 'required|in:epic,milestone',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date|after:start_date',
                'status_id' => 'required|integer|exists:statuses_milestone,id',
                'project_id' => 'required|integer|exists:projects,id',
                'epic_id' => 'nullable|integer|exists:milestones,id'
            ]);

            // If this is an epic itself, check for circular references
            if (isset($data['milestone_type']) && $data['milestone_type'] === 'epic' && isset($id)) {
                $this->milestoneModel->checkCircularEpicReference($id, $data['epic_id']);
            }

            if ($validator->fails()) {
                throw new InvalidArgumentException(implode(', ', $validator->errors()));
            }

            $milestoneData = [
                'id' => $id,
                'title' => htmlspecialchars($data['title']),
                'description' => isset($data['description']) ?
                    htmlspecialchars($data['description']) : null,
                'milestone_type' => $data['milestone_type'],
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'status_id' => filter_var($data['status_id'], FILTER_VALIDATE_INT),
                'project_id' => filter_var($data['project_id'], FILTER_VALIDATE_INT),
                'epic_id' => isset($data['epic_id']) ?
                    filter_var($data['epic_id'], FILTER_VALIDATE_INT) : null
            ];

            // Update completion date if status is completed
            if ($data['status_id'] == 3) { // Assuming 3 is 'completed' status
                $milestoneData['complete_date'] = date('Y-m-d');
            }

            $this->milestoneModel->update($id, $milestoneData);

            $_SESSION['success'] = 'Milestone updated successfully.';
            header('Location: /milestones/view/' . $id);
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_data'] = $data;
            header("Location: /milestones/edit/{$id}");
            exit;
        } catch (\Exception $e) {
            error_log("Exception in MilestoneController::update: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while updating the milestone.';
            header("Location: /milestones/edit/{$id}");
            exit;
        }
    }

    /**
     * Delete milestone (soft delete)
     * @param string $requestMethod
     * @param array $data
     * @throws RuntimeException
     */
    public function delete(string $requestMethod, array $data): void
    {
        if ($requestMethod !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: /milestones');
            exit;
        }

        try {
            $this->authMiddleware->hasPermission('delete_milestones');

            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('Invalid milestone ID');
            }

            $milestone = $this->milestoneModel->find($id);
            if (!$milestone || $milestone->is_deleted) {
                throw new InvalidArgumentException('Milestone not found');
            }

            // Check if milestone is an epic with active milestones
            if ($milestone->milestone_type === 'epic') {
                $activeMilestones = $this->milestoneModel->getEpicMilestones($id);
                if (!empty($activeMilestones)) {
                    throw new InvalidArgumentException('Cannot delete epic with active milestones');
                }
            }

            $this->milestoneModel->update($id, ['is_deleted' => true]);

            $_SESSION['success'] = 'Milestone deleted successfully.';
            header('Location: /milestones');
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /milestones');
            exit;
        } catch (\Exception $e) {
            error_log("Exception in MilestoneController::delete: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while deleting the milestone.';
            header('Location: /milestones');
            exit;
        }
    }
}
