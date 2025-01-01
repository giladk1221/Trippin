document.addEventListener('DOMContentLoaded', () => {
    // Add event listener to login form, if it exists
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
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
                } else if (triggerId === 'addFlightButton') {
                    populateAirportsDropdown(); // Populate dropdown for flight modal
                }
                else if (triggerId === 'addTripButton') {
                    populateDestinationsDropdown(); // Populate dropdown for trip modal
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
        addTripForm.addEventListener('submit', function (event) {
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

    // Populate airports dropdown with only IATA codes as values
    const populateAirportsDropdown = async () => {
        const originDropdown = document.getElementById('origin');
        originDropdown.innerHTML = `<option value="" disabled selected>Loading airports...</option>`; // Loading state

        try {
            const response = await fetch('../backend/fetch_airports.php'); // Call PHP script
            const data = await response.json();

            if (data && data.data) {
                originDropdown.innerHTML = `<option value="" disabled selected>Select an airport</option>`;
                data.data.forEach(airport => {
                    if (airport.country_name && airport.iata_code) {
                        const option = document.createElement('option');
                        option.value = airport.iata_code; // Save only `iata_code` as value
                        option.textContent = `${airport.country_name} - ${airport.iata_code}`; // Display format
                        originDropdown.appendChild(option);
                    }
                });
            } else {
                originDropdown.innerHTML = `<option value="" disabled>No airports available</option>`;
            }
        } catch (error) {
            console.error('Error fetching airports:', error);
            originDropdown.innerHTML = `<option value="" disabled>Error loading airports</option>`;
        }
    };


    const populateDestinationsDropdown = async () => {
        const destinationDropdown = document.getElementById('destination');
        destinationDropdown.innerHTML = `<option value="" disabled selected>Loading destinations...</option>`; // Loading state

        try {
            const response = await fetch('../backend/fetch_destinations.php'); // Call PHP script
            const data = await response.json();

            if (data && data.length > 0) {
                destinationDropdown.innerHTML = `<option value="" disabled selected>Select a destination</option>`;
                data.forEach(destination => {
                    const option = document.createElement('option');
                    option.value = destination; // Save destination as value
                    option.textContent = destination; // Display format
                    destinationDropdown.appendChild(option);
                });
            } else {
                destinationDropdown.innerHTML = `<option value="" disabled>No destinations available</option>`;
            }
        } catch (error) {
            console.error('Error fetching destinations:', error);
            destinationDropdown.innerHTML = `<option value="" disabled>Error loading destinations</option>`;
        }
    };




    // Initialize modal functionality for flights
    initializeModal('addFlightButton', 'addFlightModal', 'closeAddFlightModal');

    // Add event listener to Add Flight form
    const addFlightForm = document.getElementById('addFlightForm');
    if (addFlightForm) {
        addFlightForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const tripData = JSON.parse(sessionStorage.getItem('selectedTrip'));
            if (!tripData || !tripData.id) {
                console.error("Trip data or trip ID is missing.");
                alert("Please select a trip before adding a flight.");
                return;
            }

            const formData = new FormData(addFlightForm);
            formData.append('trip_id', tripData.id);

            try {
                const response = await fetch('../backend/add_flight.php', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    alert(result.message);
                    loadFlights(); // Refresh flights dynamically
                    document.getElementById('closeAddFlightModal').click(); // Close modal
                } else {
                    console.error(result.error || "Error adding flight");
                    alert(result.error || "Failed to add flight. Please try again.");
                }
            } catch (error) {
                console.error('Error adding flight:', error);
                alert('An unexpected error occurred. Please try again.');
            }
        });
    }

    // Function to fetch and display flights for the selected trip
    const loadFlights = async () => {
        const tripData = JSON.parse(sessionStorage.getItem('selectedTrip'));
        if (!tripData || !tripData.id) {
            console.error("Trip data or trip ID is missing.");
            return;
        }

        const flightContainer = document.getElementById('flightContainer');
        flightContainer.innerHTML = '<p>Loading flights...</p>';

        try {
            const response = await fetch(`../backend/fetch_flights.php?trip_id=${tripData.id}`);
            const flights = await response.json();

            flightContainer.innerHTML = ''; // Clear container
            if (flights.length > 0) {
                flights.forEach(flight => {
                    const flightCard = `
                        <div class="flight-card">
                            <p><strong>Flight Number:</strong> ${flight.flight_number}</p>
                            <p><strong>Airline:</strong> ${flight.airline}</p>
                            <p><strong>Date:</strong> ${flight.flight_date || 'N/A'}</p>
                            <p><strong>Origin:</strong> ${flight.origin_airport} (Terminal ${flight.origin_terminal || 'N/A'})</p>
                            <p><strong>Destination:</strong> ${flight.destination_airport} (Terminal ${flight.destination_terminal || 'N/A'})</p>
                            <p><strong>Departure:</strong> ${flight.scheduled_departure_time || 'N/A'}</p>
                            <p><strong>Arrival:</strong> ${flight.scheduled_arrival || 'N/A'}</p>
                            <button class="google-calendar-button" data-flight='${JSON.stringify(flight)}'>
                                <img src="assets/images/google-calendar-icon.png" alt="Add to Google Calendar">
                                Add to Google Calendar
                            </button>
                            <button class="delete-flight-button" data-flight-id="${flight.id}">
                                Delete Flight
                            </button>
                        </div>
                    `;
                    flightContainer.innerHTML += flightCard;
                });

                // Add event listeners to Google Calendar buttons
                const googleCalendarButtons = document.querySelectorAll('.google-calendar-button');
                googleCalendarButtons.forEach(button => {
                    button.addEventListener('click', (event) => {
                        const flightData = JSON.parse(event.target.dataset.flight);
                        addFlightToGoogleCalendar(flightData);
                    });
                });

                // Add event listeners to Delete Flight buttons
                const deleteFlightButtons = document.querySelectorAll('.delete-flight-button');
                deleteFlightButtons.forEach(button => {
                    button.addEventListener('click', (event) => {
                        const flightId = event.target.dataset.flightId;
                        if (confirm(`Are you sure you want to delete this flight?`)) {
                            deleteFlight(flightId);
                        }
                    });
                });
            } else {
                flightContainer.innerHTML = '<p>No flights recorded for this trip.</p>';
            }
        } catch (error) {
            console.error('Error loading flights:', error);
            flightContainer.innerHTML = '<p>Error loading flights. Please try again later.</p>';
        }
    };

    const deleteFlight = (flightId) => {
        fetch('../backend/delete_flight.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: flightId }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Flight deleted successfully!');
                    loadFlights(); // Refresh flights
                } else {
                    alert(data.message || 'Failed to delete flight.');
                }
            })
            .catch(error => console.error('Error deleting flight:', error));
    };

    const addFlightToGoogleCalendar = (flightData) => {
        if (confirm(`Do you want to add this flight to your Google Calendar?\n\nFlight Number: ${flightData.flight_number}\nAirline: ${flightData.airline}`)) {
            fetch('../backend/add_to_google_calendar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(flightData),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Flight successfully added to Google Calendar!');
                    } else {
                        alert(data.error || 'Failed to add flight to Google Calendar.');
                    }
                })
                .catch(error => {
                    console.error('Error adding flight to Google Calendar:', error);
                    alert('An unexpected error occurred. Please try again.');
                });
        }
    };

    if (document.getElementById('flightContainer')) {
        loadFlights();
    }

    // Add Expense functionality
    initializeModal('addExpenseButton', 'addExpenseModal', 'closeAddExpenseModal');

    const addExpenseForm = document.getElementById('addExpenseForm');
    if (addExpenseForm) {
        addExpenseForm.addEventListener('submit', function (event) {
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
    const loadTripExpenses = async () => {
        const tripData = JSON.parse(sessionStorage.getItem('selectedTrip'));
        if (!tripData || !tripData.id) {
            console.error("Trip data or trip ID is missing.");
            return;
        }

        const expenseContainer = document.getElementById('expenseContainer');
        expenseContainer.innerHTML = '<p>Loading expenses...</p>';

        try {
            const response = await fetch(`../backend/fetch_expenses.php?trip_id=${tripData.id}`);
            const expenses = await response.json();

            expenseContainer.innerHTML = ''; // Clear container
            if (expenses.length > 0) {
                expenses.forEach(expense => {
                    const expenseCard = `
                        <div class="expense-card">
                            <p><strong>Reason:</strong> ${expense.reason}</p>
                            <p><strong>Amount:</strong> ${expense.amount} ${expense.currency}</p>
                            <p><strong>Date:</strong> ${expense.date}</p>
                            <button class="edit-expense-button" data-expense-id="${expense.id}">
                                Edit Expense
                            </button>
                            <button class="delete-expense-button" data-expense-id="${expense.id}">
                                Delete Expense
                            </button>
                        </div>
                    `;
                    expenseContainer.innerHTML += expenseCard;
                });

                // Add event listeners for Edit Expense buttons
                const editExpenseButtons = document.querySelectorAll('.edit-expense-button');
                editExpenseButtons.forEach(button => {
                    button.addEventListener('click', (event) => {
                        const expenseId = event.target.dataset.expenseId;
                        openEditExpenseModal(expenseId);
                    });
                });

                // Add event listeners for Delete Expense buttons
                const deleteExpenseButtons = document.querySelectorAll('.delete-expense-button');
                deleteExpenseButtons.forEach(button => {
                    button.addEventListener('click', (event) => {
                        const expenseId = event.target.dataset.expenseId;
                        if (confirm(`Are you sure you want to delete this expense?`)) {
                            deleteExpense(expenseId);
                        }
                    });
                });
            } else {
                expenseContainer.innerHTML = '<p>No expenses recorded for this trip.</p>';
            }
        } catch (error) {
            console.error('Error loading expenses:', error);
            expenseContainer.innerHTML = '<p>Error loading expenses. Please try again later.</p>';
        }
    };

    const deleteExpense = (expenseId) => {
        fetch('../backend/delete_expense.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: expenseId }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Expense deleted successfully!');
                    loadTripExpenses(); // Refresh expenses
                } else {
                    alert(data.message || 'Failed to delete expense.');
                }
            })
            .catch(error => console.error('Error deleting expense:', error));
    };

    const openEditExpenseModal = (expenseId) => {
        // Fetch expense data
        fetch(`../backend/fetch_expense_details.php?id=${expenseId}`)
            .then(response => response.json())
            .then(expense => {
                // Populate the edit form with the fetched expense data
                const editExpenseModal = document.getElementById('editExpenseModal');
                document.getElementById('editReason').value = expense.reason;
                document.getElementById('editAmount').value = expense.amount;
                document.getElementById('editDate').value = expense.date;
                document.getElementById('editCurrency').value = expense.currency;
                document.getElementById('editExpenseId').value = expense.id;

                // Show the modal
                editExpenseModal.classList.remove('hidden');
                editExpenseModal.style.display = 'flex';
            })
            .catch(error => console.error('Error fetching expense details:', error));
    };

    const editExpenseForm = document.getElementById('editExpenseForm');
    if (editExpenseForm) {
        editExpenseForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const formData = new FormData(editExpenseForm);

            fetch('../backend/edit_expense.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Expense updated successfully!');
                        document.getElementById('closeEditExpenseModal').click(); // Close modal
                        loadTripExpenses(); // Refresh expenses
                    } else {
                        alert(data.message || 'Failed to update expense.');
                    }
                })
                .catch(error => console.error('Error updating expense:', error));
        });
    }

    if (document.getElementById('expenseContainer')) {
        loadTripExpenses();
    }
    // Submit Trip Functionality
    document.addEventListener('DOMContentLoaded', () => {
        const submitTripButton = document.getElementById('submitTripButton');

        if (submitTripButton) {
            submitTripButton.addEventListener('click', async () => {
                const tripData = JSON.parse(sessionStorage.getItem('selectedTrip'));
                if (!tripData || !tripData.id) {
                    alert("No trip is selected to submit.");
                    return;
                }

                try {
                    const response = await fetch(`../backend/submit_trip.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ trip_id: tripData.id }),
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        alert('Trip submitted successfully!');
                    } else {
                        alert(result.error || 'Failed to submit trip. Please try again.');
                    }
                } catch (error) {
                    console.error('Error submitting trip:', error);
                    alert('An unexpected error occurred. Please try again.');
                }
            });
        }
    });
    
    // Load flights when the page is ready and contains the expense container
    if (document.getElementById('expenseContainer')) {
        loadTripExpenses();
    }



}); 