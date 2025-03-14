// file: Views/Tasks/inc/task_filtering.js

document.addEventListener('DOMContentLoaded', function() {
    // Task Filter
    const filterSelect = document.getElementById('task-filter');
    const searchInput = document.getElementById('search-tasks');
    const taskRows = document.querySelectorAll('tr.task-row');

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterTasks();
    });
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            filterTasks();
        });
    }

    function filterTasks() {
        const filter = filterSelect ? filterSelect.value : 'all';
        const searchTerm = searchInput.value.toLowerCase();
        
        taskRows.forEach(row => {
            // Default to showing the row
            let shouldShow = true;
            
            // First check search term
            if (searchTerm) {
                const taskTitle = row.querySelector('td:first-child').textContent.toLowerCase();
                if (!taskTitle.includes(searchTerm)) {
                    shouldShow = false;
                }
            }
            
            // Then apply filter
            if (shouldShow && filter !== 'all') {
                const status = row.getAttribute('data-status').toLowerCase();
                const dueDate = row.getAttribute('data-due-date');
                const priority = row.getAttribute('data-priority').toLowerCase();
                
                const today = new Date().toISOString().split('T')[0];
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const tomorrowStr = tomorrow.toISOString().split('T')[0];
                
                // Calculate one week from now
                const oneWeek = new Date();
                oneWeek.setDate(oneWeek.getDate() + 7);
                const oneWeekStr = oneWeek.toISOString().split('T')[0];
                
                if (filter === 'overdue') {
                    shouldShow = dueDate && dueDate < today && 
                                 status !== 'completed' && status !== 'closed';
                } else if (filter === 'today') {
                    shouldShow = dueDate && dueDate >= today && dueDate < tomorrowStr;
                } else if (filter === 'week') {
                    shouldShow = dueDate && dueDate >= today && dueDate <= oneWeekStr;
                } else if (filter === 'in-progress') {
                    shouldShow = status.includes('in progress');
                } else if (filter === 'completed') {
                    shouldShow = status.includes('completed');
                } else if (filter === 'high-priority') {
                    shouldShow = priority === 'high';
                } else if (filter === 'assigned') {
                    // This would need additional data attributes for assigned tasks
                    const assigned = row.hasAttribute('data-assigned') ? 
                                    row.getAttribute('data-assigned') : null;
                    shouldShow = assigned && assigned !== '';
                } else if (filter === 'unassigned') {
                    const assigned = row.hasAttribute('data-assigned') ? 
                                    row.getAttribute('data-assigned') : null;
                    shouldShow = !assigned || assigned === '';
                }
            }
            
            row.style.display = shouldShow ? '' : 'none';
        });
        
        // Update stats based on visible rows
        updateVisibleStats();
    }
    
    // Function to update stats based on visible rows
    function updateVisibleStats() {
        // Count visible rows
        let visibleTotal = 0;
        let visibleInProgress = 0;
        let visibleOverdue = 0;
        let visibleCompleted = 0;
        
        taskRows.forEach(row => {
            if (row.style.display !== 'none') {
                visibleTotal++;
                
                const status = row.getAttribute('data-status').toLowerCase();
                const dueDate = row.getAttribute('data-due-date');
                const today = new Date().toISOString().split('T')[0];
                
                if (status.includes('in progress')) {
                    visibleInProgress++;
                }
                
                if (dueDate && dueDate < today && 
                    status !== 'completed' && status !== 'closed') {
                    visibleOverdue++;
                }
                
                if (status.includes('completed')) {
                    visibleCompleted++;
                }
            }
        });
        
        // Update stat display if elements exist
        const statElements = document.querySelectorAll('.stats-counter');
        if (statElements.length >= 4) {
            statElements[0].textContent = visibleTotal;
            statElements[1].textContent = visibleInProgress;
            statElements[2].textContent = visibleOverdue;
            statElements[3].textContent = visibleCompleted;
        }
    }
    
    // Handle active timer updates
    const timerDisplay = document.getElementById('timer-display');
    if (timerDisplay) {
        let seconds = parseInt(timerDisplay.getAttribute('data-duration') || '0');
        
        setInterval(function() {
            seconds++;
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            
            timerDisplay.textContent = 
                (hours < 10 ? '0' + hours : hours) + ':' +
                (minutes < 10 ? '0' + minutes : minutes) + ':' +
                (secs < 10 ? '0' + secs : secs);
        }, 1000);
    }
});