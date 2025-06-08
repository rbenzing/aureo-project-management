<?php
//file: Views/Layouts/footer.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use \App\Core\Config;
use \App\Services\SettingsService;

// Get current year using configured timezone
$settingsService = SettingsService::getInstance();
$timezone = $settingsService->getDefaultTimezone();
try {
    $currentYear = (new DateTime('now', new DateTimeZone($timezone)))->format('Y');
} catch (Exception $e) {
    $currentYear = date('Y'); // Fallback
}
?>

<footer class="bg-gray-800 text-white py-4 mt-auto">
    <div class="container mx-auto px-4 text-center text-sm">
        &copy; <?php echo $currentYear; ?> <?php echo htmlspecialchars(Config::get('company_name')); ?>, All rights reserved.
    </div>
</footer>

<!-- Include Floating Timer -->
<?php include BASE_PATH . '/../src/Views/Layouts/floating_timer.php'; ?>

<script type="text/javascript" src="/assets/js/scripts.js"></script>

<!-- Avatar Dropdown - Load last to ensure it works on all pages -->
<script>
// Ensure avatar dropdown works on all pages - runs after all other scripts
(function() {
    function initAvatarDropdown() {
        const avatarDropdown = document.querySelector('header .dropdown');
        if (!avatarDropdown) return;

        const button = avatarDropdown.querySelector('button');
        const menu = avatarDropdown.querySelector('.dropdown-menu');

        if (!button || !menu) return;

        // Clear any existing event listeners
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        // Add click handler
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu:not(.hidden)').forEach(otherMenu => {
                if (otherMenu !== menu) {
                    otherMenu.classList.add('hidden');
                }
            });

            menu.classList.toggle('hidden');
            newButton.setAttribute('aria-expanded', !menu.classList.contains('hidden'));
        });

        // Close on outside click
        document.addEventListener('click', function(event) {
            if (!avatarDropdown.contains(event.target)) {
                menu.classList.add('hidden');
                newButton.setAttribute('aria-expanded', 'false');
            }
        });

        // Close on escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
                newButton.setAttribute('aria-expanded', 'false');
                newButton.focus();
            }
        });
    }

    // Initialize immediately if DOM is ready, otherwise wait
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initAvatarDropdown, 200);
        });
    } else {
        setTimeout(initAvatarDropdown, 200);
    }
})();
</script>

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


</script>