<?php
//file: Views/Layouts/notifications.php
declare(strict_types=1);

if (isset($_SESSION['error'])): ?>
    <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif;

if (isset($_SESSION['success'])): ?>
    <div class="bg-green-50 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>