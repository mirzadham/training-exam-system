/**
 * Training Exam System — Custom JavaScript
 * 
 * General JS utilities.
 * Exam-specific JS (timer, navigation) will be added in Phase 6.
 */

// Auto-dismiss flash alerts after 3 seconds
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 3000);
    });
});
