/**
 * booking_form.js
 * Auto-calculate return date based on destination
 */

document.addEventListener('DOMContentLoaded', function() {
    const destinationSelect = document.getElementById('destination_id');
    const travelDateInput = document.getElementById('travel_date');
    const returnDateInput = document.getElementById('return_date');
    const durationInfo = document.getElementById('duration_info');

    // When user selects a destination
    if (destinationSelect) {
        destinationSelect.addEventListener('change', updateReturnDate);
    }

    // When user changes travel date
    if (travelDateInput) {
        travelDateInput.addEventListener('change', updateReturnDate);
    }

    /**
     * Fetch destination duration and auto-calculate return date
     */
    function updateReturnDate() {
        const destinationId = destinationSelect.value;
        const travelDate = travelDateInput.value;

        if (!destinationId || !travelDate) return;

        // Fetch suggested duration from server
        fetch('api/get_destination_duration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                destination_id: destinationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const durationDays = data.suggested_duration_days;
                const calculatedReturnDate = calculateReturnDate(travelDate, durationDays);

                // Auto-fill return date
                returnDateInput.value = calculatedReturnDate;
                returnDateInput.min = travelDate;
                returnDateInput.removeAttribute('readonly');

                // Display info to user
                if (durationInfo) {
                    durationInfo.style.display = 'block';
                    durationInfo.innerHTML = `
                        <div class="alert alert-info">
                            <strong>📅 Suggested Duration:</strong> ${durationDays} days
                            <br><small>You can modify the return date if needed.</small>
                        </div>
                    `;
                }
            } else {
                console.error('Error fetching duration:', data.message);
            }
        })
        .catch(error => console.error('Fetch error:', error));
    }

    /**
     * Calculate return date from travel date + duration days
     */
    function calculateReturnDate(travelDateString, durationDays) {
        const travelDate = new Date(travelDateString);
        const returnDate = new Date(travelDate);
        returnDate.setDate(returnDate.getDate() + parseInt(durationDays));

        // Format as YYYY-MM-DD
        const year = returnDate.getFullYear();
        const month = String(returnDate.getMonth() + 1).padStart(2, '0');
        const day = String(returnDate.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    // Allow manual override
    if (returnDateInput) {
        returnDateInput.addEventListener('change', function() {
            const travelDate = new Date(travelDateInput.value);
            const returnDate = new Date(this.value);

            if (returnDate <= travelDate) {
                alert('Return date must be after travel date!');
                this.value = '';
                return;
            }

            const actualDuration = Math.ceil(
                (returnDate - travelDate) / (1000 * 60 * 60 * 24)
            );

            if (durationInfo) {
                durationInfo.style.display = 'block';
                durationInfo.innerHTML = `
                    <div class="alert alert-warning">
                        <strong>✏️ Custom Duration:</strong> ${actualDuration} days (Modified)
                    </div>
                `;
            }
        });
    }
});
