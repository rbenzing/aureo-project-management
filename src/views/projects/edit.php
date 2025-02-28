<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <div class="flex-col">
            <small><a href="/view_project?id=<?php echo $project->id; ?>" class="text-gray-200 py-4">&laquo; Back to Project</a></small>
            <h1 class="text-2xl font-bold mb-6">Edit Project</h1>

            <!-- Edit Project Form -->
            <form method="POST" action="/projects/update" class="space-y-4 max-w-md">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <input type="hidden" name="id" value="<?php echo $project->id; ?>">

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($project->name); ?>" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium">Description</label>
                    <textarea id="description" name="description"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($project->description ?? ''); ?></textarea>
                </div>

                <!-- Company -->
                <div>
                    <label for="company_id" class="block text-sm font-medium">Company</label>
                    <select id="company_id" name="company_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select a company</option>
                        <?php
                        foreach ($companies as $company): ?>
                            <option value="<?php echo $company['id']; ?>"<?php echo $company['id'] === $project->company_id ? ' selected' : '' ?>><?php echo htmlspecialchars($company['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status_id" class="block text-sm font-medium">Status</label>
                    <select id="status_id" name="status_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select a status</option>
                        <?php
                        foreach ($statuses as $status): ?>
                            <option value="<?php echo $status['id']; ?>"<?php echo $status['id'] === $project->status_id ? ' selected' : '' ?>><?php echo htmlspecialchars($status['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Project
                </button>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>