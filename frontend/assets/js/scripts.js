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

    // Add Trip functionality
    initializeModal('addTripButton', 'addTripModal', 'closeAddTripModal');

    const addTripForm = document.getElementById('addTripForm');
    if (addTripForm) {
        addTripForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(addTripForm);

            fetch('../backend/add_trip.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Trip added successfully!');
                        location.reload(); // Reload to show the new trip
                    } else {
                        alert(data.message || 'Failed to add trip');
                    }
                })
                .catch(error => console.error('Error adding trip:', error));
        });
    }

    // Fetch currencies for dropdown
    const populateCurrencyDropdown = () => {
        const currencyDropdown = document.getElementById('currency');
        fetch('../backend/fetch_currencies.php')
            .then(response => response.json())
            .then(currencies => {
                currencyDropdown.innerHTML = '<option value="" disabled selected>Select Currency</option>';
                currencies.forEach(currency => {
                    const option = document.createElement('option');
                    option.value = currency;
                    option.textContent = currency;
                    currencyDropdown.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching currencies:', error));
    };

    // Add Expense functionality
    initializeModal('addExpenseButton', 'addExpenseModal', 'closeAddExpenseModal');

    const addExpenseForm = document.getElementById('addExpenseForm');
    if (addExpenseForm) {
        addExpenseForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const tripData = JSON.parse(sessionStorage.getItem('selectedTrip'));
            if (!tripData || !tripData.id) {
                console.error("Trip data or trip ID is missing.");
                return;
            }

            const formData = new FormData(addExpenseForm);
            formData.append('trip_id', tripData.id);

            fetch('../backend/add_expense.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Expense added successfully!');
                        document.getElementById('closeAddExpenseModal').click(); // Close modal
                        loadTripExpenses(); // Refresh expenses
                    } else {
                        alert(data.message || 'Failed to add expense');
                    }
                })
                .catch(error => console.error('Error adding expense:', error));
        });
    }

    // Fetch and display trip expenses
    const loadTripExpenses = () => {
        const tripData = JSON.parse(sessionStorage.getItem('selectedTrip'));
        if (!tripData || !tripData.id) {
            console.error("Trip data or trip ID is missing.");
            return;
        }

        fetch(`../backend/fetch_expenses.php?trip_id=${tripData.id}`)
            .then(response => response.json())
            .then(expenses => {
                const expenseContainer = document.getElementById('expenseContainer');
                expenseContainer.innerHTML = '';
                if (expenses.length > 0) {
                    expenses.forEach(expense => {
                        const expenseCard = `
                            <div class="expense-card">
                                <p>Reason: ${expense.reason}</p>
                                <p>Amount: ${expense.amount} ${expense.currency}</p>
                                <p>Date: ${expense.date}</p>
                            </div>
                        `;
                        expenseContainer.innerHTML += expenseCard;
                    });
                } else {
                    expenseContainer.innerHTML = '<p>No expenses recorded for this trip.</p>';
                }
            })
            .catch(error => console.error('Error loading expenses:', error));
    };

    // Load expenses when the page is ready
    loadTripExpenses();
});