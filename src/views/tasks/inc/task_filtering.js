document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('task-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const filter = this.value;
            const rows = document.querySelectorAll('tbody tr');
            const today = new Date().toISOString().split('T')[0];
            const oneWeekLater = new Date();
            oneWeekLater.setDate(oneWeekLater.getDate() + 7);
            const weekEnd = oneWeekLater.toISOString().split('T')[0];
            
            rows.forEach(row => {
                // Default to showing the row
                let shouldShow = true;
                
                if (filter === 'all') {
                    // Show all rows
                    shouldShow = true;
                } else if (filter === 'overdue') {
                    // Check if task is overdue
                    const dueDateEl = row.querySelector('td:nth-child(5)'); // Adjust index based on columns
                    const statusEl = row.querySelector('td:nth-child(4)'); // Adjust index based on columns
                    
                    if (dueDateEl && statusEl) {
                        const hasOverdueBadge = dueDateEl.textContent.includes('Overdue');
                        const completedStatus = statusEl.textContent.includes('Completed') || 
                                                statusEl.textContent.includes('Closed');
                        shouldShow = hasOverdueBadge && !completedStatus;
                    }
                } else if (filter === 'today') {
                    // Check if task is due today
                    const dueDateEl = row.querySelector('td:nth-child(5)'); // Adjust index based on columns
                    if (dueDateEl) {
                        shouldShow = dueDateEl.textContent.includes('Today');
                    }
                } else if (filter === 'week') {
                    // Check if task is due this week (harder to do client-side)
                    // This would need server-side filtering for accuracy
                    // For now, we'll just show tasks marked as "Today" or with near dates
                    const dueDateEl = row.querySelector('td:nth-child(5)'); // Adjust index based on columns
                    if (dueDateEl) {
                        const dueDateText = dueDateEl.textContent;
                        // This is a simplified approach - server-side would be better
                        shouldShow = (dueDateText.includes('Today') || 
                                    !dueDateText.includes('Overdue'));
                    }
                } else if (filter === 'in-progress') {
                    // Check if task is in progress
                    const statusEl = row.querySelector('td:nth-child(4)'); // Adjust index based on columns
                    if (statusEl) {
                        shouldShow = statusEl.textContent.includes('In Progress');
                    }
                } else if (filter === 'completed') {
                    // Check if task is completed
                    const statusEl = row.querySelector('td:nth-child(4)'); // Adjust index based on columns
                    if (statusEl) {
                        shouldShow = statusEl.textContent.includes('Completed');
                    }
                }
                
                row.style.display = shouldShow ? '' : 'none';
            });
        });
    }
});