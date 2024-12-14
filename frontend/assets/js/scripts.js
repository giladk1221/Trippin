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
                            <p><strong>Origin:</strong> ${flight.origin_airport} (Terminal ${flight.origin_terminal || 'N/A'})</p>
                            <p><strong>Destination:</strong> ${flight.destination_airport} (Terminal ${flight.destination_terminal || 'N/A'})</p>
                            <p><strong>Departure:</strong> ${flight.scheduled_departure_time}</p>
                            <p><strong>Arrival:</strong> ${flight.scheduled_arrival}</p>
                        </div>
                    `;
                    flightContainer.innerHTML += flightCard;
                });
            } else {
                flightContainer.innerHTML = '<p>No flights recorded for this trip.</p>';
            }
        } catch (error) {
            console.error('Error loading flights:', error);
            flightContainer.innerHTML = '<p>Error loading flights. Please try again later.</p>';
        }
    };

    // Load flights when the page is ready
    loadFlights();

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