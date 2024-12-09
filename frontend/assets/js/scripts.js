
document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    fetch('backend/login.php', {
        method: 'POST',
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Login failed');
            }
        })
        .catch(error => console.error('Error during login:', error));
});



document.addEventListener('DOMContentLoaded', () => {
    // Initialize modal functionality
    const initializeModal = (triggerId, modalId, closeId) => {
        const trigger = document.getElementById(triggerId);
        const modal = document.getElementById(modalId);
        const close = document.getElementById(closeId);
        const overlay = modal ? modal.querySelector('.modal-overlay') : null;

        if (trigger && modal && close) {
            // Show modal on button click
            trigger.addEventListener('click', () => {
                modal.classList.remove('hidden');
                modal.style.display = 'flex'; // Ensure proper modal display
            });

            // Hide modal on close button click
            close.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.style.display = 'none'; // Ensure modal is properly hidden
            });

            // Hide modal on overlay click
            if (overlay) {
                overlay.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                });
            }
        }
    };

    // Initialize all modals
    initializeModal('addTripButton', 'addTripModal', 'closeAddTripModal');
    initializeModal('addFlightButton', 'addFlightModal', 'closeAddFlightModal');
    initializeModal('addExpenseButton', 'addExpenseModal', 'closeAddExpenseModal');
    initializeModal('manageBudgetButton', 'manageBudgetModal', 'closeManageBudgetModal');
});
