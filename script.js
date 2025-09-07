// Form switching functionality for login/register
function showForm(formId) {
    // Hide all forms
    const forms = document.querySelectorAll('.form-box');
    forms.forEach(form => form.classList.remove('active'));
    
    // Show selected form
    const targetForm = document.getElementById(formId);
    if (targetForm) {
        targetForm.classList.add('active');
    }
}

// Seat selection functionality
function toggleSeat(seatElement, seatNumber) {
    if (seatElement.classList.contains('occupied')) {
        return; // Can't select occupied seats
    }
    
    if (seatElement.classList.contains('selected')) {
        seatElement.classList.remove('selected');
        removeSeatFromSelection(seatNumber);
    } else {
        seatElement.classList.add('selected');
        addSeatToSelection(seatNumber);
    }
    
    updateSelectedSeatsDisplay();
    updateTotalPrice();
}

// Manage selected seats array
let selectedSeats = [];

function addSeatToSelection(seatNumber) {
    if (!selectedSeats.includes(seatNumber)) {
        selectedSeats.push(seatNumber);
    }
}

function removeSeatFromSelection(seatNumber) {
    selectedSeats = selectedSeats.filter(seat => seat !== seatNumber);
}

function updateSelectedSeatsDisplay() {
    const display = document.getElementById('selected-seats');
    if (display) {
        display.textContent = selectedSeats.length > 0 
            ? 'Selected Seats: ' + selectedSeats.join(', ')
            : 'No seats selected';
    }
}

function updateTotalPrice() {
    const pricePerSeat = parseFloat(document.getElementById('ticket-price')?.value || 0);
    const totalPrice = selectedSeats.length * pricePerSeat;
    const totalDisplay = document.getElementById('total-price');
    if (totalDisplay) {
        totalDisplay.textContent = `Total: $${totalPrice.toFixed(2)}`;
    }
}

// Form validation
function validateBookingForm() {
    if (selectedSeats.length === 0) {
        alert('Please select at least one seat');
        return false;
    }
    return true;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Set initial active form for login page
    const loginForm = document.getElementById('login-form');
    if (loginForm && !document.querySelector('.form-box.active')) {
        loginForm.classList.add('active');
    }
    
    // Update displays if on seat selection page
    updateSelectedSeatsDisplay();
    updateTotalPrice();
});