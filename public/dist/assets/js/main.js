/**
 * Main JavaScript file for the Grocery Store application
 */
document.addEventListener('DOMContentLoaded', function() {
    // Enable Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Enable Bootstrap dropdowns
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Add active class to current navigation link
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('header .nav-link');
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (currentLocation.endsWith(linkPath) || 
            (linkPath !== '/' && currentLocation.includes(linkPath) && linkPath !== 'home')) {
            link.classList.add('active');
        }
    });
}); 