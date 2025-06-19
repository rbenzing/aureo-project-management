// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('sidebar-toggle');
    const closeButton = document.getElementById('sidebar-close');

    // Check if all required elements exist
    if (!sidebar || !toggleButton) {
        console.warn('Sidebar elements not found');
        return;
    }

    // Open/close sidebar when clicking the toggle button
    toggleButton.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        sidebar.classList.toggle('-translate-x-full');
    });

    // Close sidebar when clicking the close button (if it exists)
    if (closeButton) {
        closeButton.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            sidebar.classList.add('-translate-x-full');
        });
    }

    // Close sidebar when clicking outside (on all screen sizes)
    document.addEventListener('click', function(event) {
        if (!sidebar.contains(event.target) &&
            !toggleButton.contains(event.target) &&
            !sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.add('-translate-x-full');
        }
    });

    // Prevent sidebar from closing when clicking inside it
    sidebar.addEventListener('click', function(event) {
        event.stopPropagation();
    });
});

// Copy to clipboard functionality
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        // Use the modern Clipboard API
        navigator.clipboard.writeText(text).then(function() {
            showCopyNotification('URL copied to clipboard!');
        }).catch(function(err) {
            console.error('Failed to copy text: ', err);
            fallbackCopyToClipboard(text);
        });
    } else {
        // Fallback for older browsers or non-secure contexts
        fallbackCopyToClipboard(text);
    }
}

// Fallback copy method for older browsers
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopyNotification('URL copied to clipboard!');
        } else {
            showCopyNotification('Failed to copy URL', 'error');
        }
    } catch (err) {
        console.error('Fallback copy failed: ', err);
        showCopyNotification('Failed to copy URL', 'error');
    }

    document.body.removeChild(textArea);
}

// Show copy notification
function showCopyNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 left-4 px-4 py-2 rounded-md shadow-lg z-50 transition-all duration-300 transform translate-y-0 opacity-100 ${
        type === 'error'
            ? 'bg-red-500 text-white'
            : 'bg-green-500 text-white'
    }`;
    notification.textContent = message;

    // Add to page
    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateY(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}