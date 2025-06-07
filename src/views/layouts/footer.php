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

<!-- Include Floating Timer -->
<?php include BASE_PATH . '/../src/Views/Layouts/floating_timer.php'; ?>

<script type="text/javascript" src="/assets/js/scripts.js"></script>



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