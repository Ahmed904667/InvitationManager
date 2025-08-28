// ************* EXPORT FUNCTIONALITY ********************

// Export Modal Logic

// Show export modal
window.showExportModal = function() {
    // Update export statistics
    updateExportStats();
    
    showModal('exportModal');
}

// Hide export modal
window.hideExportModal = function() {
    hideModal('exportModal');
}

// Update export statistics based on current filters
function updateExportStats() {
    const search = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const group = document.getElementById('groupFilter')?.value || '';
    
    fetch(`/organizer/guest-lists/${window.guestListId}/export/stats?search=${search}&status=${status}&group=${group}`)
        .then(response => response.json())
        .then(data => {
            const statsContainer = document.getElementById('exportStats');
            if (statsContainer) {
                let statsHTML = '<div class="grid grid-cols-2 gap-4 text-sm">';
                
                // Always show total guests
                statsHTML += `
                    <div class="text-center">
                        <div class="font-bold text-lg">${data.total_guests}</div>
                        <div class="text-gray-600">Total Guests</div>
                    </div>
                `;
                
                // Show email count if available
                if (data.with_email !== undefined) {
                    statsHTML += `
                        <div class="text-center">
                            <div class="font-bold text-lg text-green-600">${data.with_email}</div>
                            <div class="text-gray-600">With Email</div>
                        </div>
                    `;
                }
                
                // Show phone count if available
                if (data.with_phone !== undefined) {
                    statsHTML += `
                        <div class="text-center">
                            <div class="font-bold text-lg text-blue-600">${data.with_phone}</div>
                            <div class="text-gray-600">With Phone</div>
                        </div>
                    `;
                }
                
                // Show groups count if available
                if (data.groups_count !== undefined) {
                    statsHTML += `
                        <div class="text-center">
                            <div class="font-bold text-lg text-purple-600">${data.groups_count}</div>
                            <div class="text-gray-600">Groups</div>
                        </div>
                    `;
                }
                
                statsHTML += '</div>';
                statsContainer.innerHTML = statsHTML;
            }
        })
        .catch(error => {
            console.error('Error fetching export stats:', error);
        });
}

// Export to CSV
window.exportToCsv = function() {
    const search = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const group = document.getElementById('groupFilter')?.value || '';
    
    const url = `/organizer/guest-lists/${window.guestListId}/export/csv?search=${search}&status=${status}&group=${group}`;
    
    // Show loading state
    const btn = document.getElementById('exportCsvBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Exporting...';
    
    // Trigger download
    const link = document.createElement('a');
    link.href = url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Reset button state
    setTimeout(() => {
        btn.disabled = false;
        btn.textContent = originalText;
        hideExportModal();
        window.GuestManager?.showNotification('CSV export completed!', 'success');
    }, 1000);
}

// Export to Excel
window.exportToExcel = function() {
    const search = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const group = document.getElementById('groupFilter')?.value || '';
    
    const url = `/organizer/guest-lists/${window.guestListId}/export/excel?search=${search}&status=${status}&group=${group}`;
    
    // Show loading state
    const btn = document.getElementById('exportExcelBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Exporting...';
    
    // Trigger download
    const link = document.createElement('a');
    link.href = url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Reset button state
    setTimeout(() => {
        btn.disabled = false;
        btn.textContent = originalText;
        hideExportModal();
        window.GuestManager?.showNotification('Excel export completed!', 'success');
    }, 1000);
}

// Export to PDF
window.exportToPdf = function() {
    const search = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const group = document.getElementById('groupFilter')?.value || '';
    
    const url = `/organizer/guest-lists/${window.guestListId}/export/pdf?search=${search}&status=${status}&group=${group}`;
    
    // Show loading state
    const btn = document.getElementById('exportPdfBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Exporting...';
    
    // Open in new window for PDF viewing/printing
    const newWindow = window.open(url, '_blank');
    
    // Reset button state after a delay
    setTimeout(() => {
        btn.disabled = false;
        btn.textContent = originalText;
        hideExportModal();
        window.GuestManager?.showNotification('PDF export opened in new window!', 'success');
    }, 1000);
}

// Quick export functions (for toolbar buttons)
window.exportToCSV = function() {
    const search = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const group = document.getElementById('groupFilter')?.value || '';
    
    const url = `/organizer/guest-lists/${window.guestListId}/export/csv?search=${search}&status=${status}&group=${group}`;
    
    const link = document.createElement('a');
    link.href = url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    window.GuestManager?.showNotification('CSV export completed!', 'success');
}

window.exportToExcel = function() {
    const search = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const group = document.getElementById('groupFilter')?.value || '';
    
    const url = `/organizer/guest-lists/${window.guestListId}/export/excel?search=${search}&status=${status}&group=${group}`;
    
    const link = document.createElement('a');
    link.href = url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    window.GuestManager?.showNotification('Excel export completed!', 'success');
}

window.exportToPDF = function() {
    const search = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const group = document.getElementById('groupFilter')?.value || '';
    
    const url = `/organizer/guest-lists/${window.guestListId}/export/pdf?search=${search}&status=${status}&group=${group}`;
    
    window.open(url, '_blank');
    window.GuestManager?.showNotification('PDF export opened in new window!', 'success');
}

// Export menu toggle function
window.toggleExportMenu = function(event) {
    event.stopPropagation();
    const menu = document.getElementById('exportMenu');
    const group = menu.closest('.group');
    
    if (group.classList.contains('show')) {
        group.classList.remove('show');
    } else {
        // Close other dropdowns first
        document.querySelectorAll('.group.show').forEach(g => g.classList.remove('show'));
        group.classList.add('show');
    }
}

// ************* END EXPORT FUNCTIONALITY ******************** 