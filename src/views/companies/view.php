<?php
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: /login');
    exit;
}
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
    <main class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6"><?= htmlspecialchars($company->name) ?></h1>

        <!-- Company Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Company Details</h2>
            <ul class="space-y-2">
                <li><strong>Address:</strong> <?= htmlspecialchars($company->address ?? 'No address') ?></li>
                <li><strong>Phone:</strong> <?= htmlspecialchars($company->phone ?? 'No phone number') ?></li>
                <li><strong>Website:</strong> <?php 
                    echo !empty($company->website) 
                        ? '<a href="' . htmlspecialchars($company->website, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">' 
                            . htmlspecialchars($company->website, ENT_QUOTES, 'UTF-8') 
                            . '</a>' 
                        : 'No website'; 
                    ?></li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-between gap-4">
            <a href="/companies" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Back to Companies</a>
            <a href="/edit_company?id=<?= htmlspecialchars($company->id) ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Edit</a>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>