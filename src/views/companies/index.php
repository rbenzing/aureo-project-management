<?php
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: /login');
    exit;
}

// Fetch the list of companies
require_once __DIR__ . '/../../Controllers/CompanyController.php';
$controller = new \App\Controllers\CompanyController();
$companies = (new \App\Models\Company())->getAllPaginated(10); // Fetch first page, 10 items per page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow md:ml-64 p-6">
        <h1 class="text-2xl font-bold mb-6">Companies</h1>

        <!-- List of Companies -->
        <?php if (!empty($companies)): ?>
            <ul class="space-y-2">
                <?php foreach ($companies as $company): ?>
                    <li>
                        <a href="/companies/view.php?id=<?php echo $company->id; ?>" class="text-indigo-600 hover:text-indigo-900">
                            <?php echo htmlspecialchars($company->name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">No companies found.</p>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="mt-4">
            <?php if (isset($pagination)): ?>
                <nav class="flex justify-between items-center">
                    <?php if ($pagination['prev_page']): ?>
                        <a href="?page=<?php echo $pagination['prev_page']; ?>" class="text-indigo-600 hover:text-indigo-900">&laquo; Previous</a>
                    <?php endif; ?>
                    <?php if ($pagination['next_page']): ?>
                        <a href="?page=<?php echo $pagination['next_page']; ?>" class="text-indigo-600 hover:text-indigo-900">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>

        <!-- Create Button -->
        <a href="/companies/create.php" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create New Company</a>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>