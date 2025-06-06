<?php
//file: Views/Layouts/footer.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use \App\Core\Config;
?>

<footer class="bg-gray-800 text-white py-4 mt-auto">
    <div class="container mx-auto px-4 text-center text-sm">
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(Config::get('company_name')); ?>, All rights reserved.
    </div>
</footer>

<script type="text/javascript" src="/assets/js/scripts.js"></script>

<!-- Active Timer JavaScript -->
<?php if (!empty($_SESSION['active_timer'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerDisplay = document.getElementById('active-timer');
    if (timerDisplay) {
        const startTime = <?= $_SESSION['active_timer']['start_time'] ?? time() ?>;

        function updateTimer() {
            const now = Math.floor(Date.now() / 1000);
            const elapsed = now - startTime;

            const hours = Math.floor(elapsed / 3600);
            const minutes = Math.floor((elapsed % 3600) / 60);
            const seconds = elapsed % 60;

            const timeString =
                (hours < 10 ? '0' + hours : hours) + ':' +
                (minutes < 10 ? '0' + minutes : minutes) + ':' +
                (seconds < 10 ? '0' + seconds : seconds);

            timerDisplay.textContent = timeString;
        }

        // Update immediately and then every second
        updateTimer();
        setInterval(updateTimer, 1000);
    }
});
</script>
<?php endif; ?>

<!-- Common JavaScript Functions -->
<script>
// Delete entity function
function deleteEntity(entityType, id) {
    if (confirm('Are you sure you want to delete this ' + entityType + '?')) {
        fetch('/' + entityType + '/delete/' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting ' + entityType + ': ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting ' + entityType);
        });
    }
}

// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 768 &&
                !sidebar.contains(event.target) &&
                !sidebarToggle.contains(event.target) &&
                !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    }
});
</script>