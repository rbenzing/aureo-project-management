// Sidebar toggle functionality
const sidebar = document.getElementById('sidebar');
const toggleButton = document.getElementById('sidebar-toggle');
const closeButton = document.getElementById('sidebar-close');

// Open sidebar when clicking the toggle button
toggleButton.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (event) => {
    if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
        sidebar.classList.add('-translate-x-full');
    }
});

// Close sidebar when clicking the close button
closeButton.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
});

// Prevent sidebar from closing when clicking inside it
sidebar.addEventListener('click', (event) => {
    event.stopPropagation();
});