document.addEventListener('DOMContentLoaded', () => {
    // Add event listener to login form, if it exists
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
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
    }

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
                if (triggerId === 'addExpenseButton') {
                    populateCurrencyDropdown(); // Populate dropdown for expense modal
                }
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

    // Initialize Add Trip modal
    initializeModal('addTripButton', 'addTripModal', 'closeAddTripModal');

    // Add event listener to Add Trip form
    const addTripForm = document.getElementById('addTripForm');
    if (addTripForm) {
        addTripForm.addEventListener('submit', function(event) {
            event.preventDefault();
    
            const formData = new FormData(addTripForm);
            for (const pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`); // Debug: Log form data
            }
    
            fetch('../backend/add_trip.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Trip added successfully!');
                        location.reload(); // Reload the page to fetch updated trips
                    } else {
                        alert(data.message || 'Failed to add trip');
                    }
                })
                .catch(error => console.error('Error adding trip:', error));
        });
    }

    // Fetch currencies and populate dropdown
    const populateCurrencyDropdown = () => {
        const currencyDropdown = document.getElementById('currency');
        fetch('../backend/fetch_currencies.php')
            .then(response => response.json())
            .then(currencies => {
                // Clear existing options
                currencyDropdown.innerHTML = '<option value="" disabled selected>Select Currency</option>';
                // Populate with fetched currencies
                currencies.forEach(currency => {
                    const option = document.createElement('option');
                    option.value = currency;
                    option.textContent = currency;
                    currencyDropdown.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching currencies:', error));
    };

    // Initialize Add Expense modal
    initializeModal('addExpenseButton', 'addExpenseModal', 'closeAddExpenseModal');

    // Add event listener to Add Expense form
    const addExpenseForm = document.getElementById('addExpenseForm');
    if (addExpenseForm) {
        addExpenseForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(addExpenseForm);
            for (const pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`); // Debug: Log form data
            }

            fetch('../backend/add_expense.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Expense added successfully!');
                        location.reload(); // Reload the page to fetch updated expenses
                    } else {
                        alert(data.message || 'Failed to add expense');
                    }
                })
                .catch(error => console.error('Error adding expense:', error));
        });
    }
});