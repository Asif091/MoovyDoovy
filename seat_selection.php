<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Check if show_id is provided
if (!isset($_POST['show_id']) && !isset($_GET['show_id'])) {
    header("Location: movies.php");
    exit();
}

$show_id = $_POST['show_id'] ?? $_GET['show_id'];

// Get showtime details
$showtime_query = "
    SELECT 
        s.show_id,
        s.date,
        s.start,
        s.end,
        s.movie_name,
        h.hall_name,
        h.hall_id,
        m.duration
    FROM showtime s
    INNER JOIN hall h ON s.hall_id = h.hall_id
    INNER JOIN movie m ON s.movie_name = m.movie_name
    WHERE s.show_id = $show_id
";
$showtime_result = $conn->query($showtime_query);

if (!$showtime_result || $showtime_result->num_rows == 0) {
    header("Location: movies.php");
    exit();
}

$showtime = $showtime_result->fetch_assoc();

// Get hall seats
$seats_query = "
    SELECT hall_seat_no 
    FROM hall_seat_no 
    WHERE hall_id = {$showtime['hall_id']} 
    ORDER BY hall_seat_no
";
$seats_result = $conn->query($seats_query);

// Get booked seats for this show
$booked_seats_query = "
    SELECT booking_seat_no 
    FROM booking_seat_no bsn
    INNER JOIN booking b ON bsn.booking_id = b.booking_id
    WHERE b.show_id = $show_id
";
$booked_seats_result = $conn->query($booked_seats_query);

$booked_seats = [];
if ($booked_seats_result) {
    while ($row = $booked_seats_result->fetch_assoc()) {
        $booked_seats[] = $row['booking_seat_no'];
    }
}

// Generate seat layout (if no seats in database, create a default layout)
$all_seats = [];
if ($seats_result && $seats_result->num_rows > 0) {
    while ($row = $seats_result->fetch_assoc()) {
        $all_seats[] = $row['hall_seat_no'];
    }
} else {
    // Default layout: 8 rows, 10 seats per row
    for ($i = 1; $i <= 80; $i++) {
        $all_seats[] = $i;
    }
}

// Set ticket price (default ৳500)
$ticket_price = 500.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Seat Selection - MoovyDoovy</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .seat-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .showtime-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .screen {
            background: #333;
            color: white;
            text-align: center;
            padding: 10px;
            margin: 20px auto 40px;
            border-radius: 10px;
            max-width: 300px;
        }
        
        .seat-layout {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 8px;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .seat {
            width: 40px;
            height: 40px;
            border: 2px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.3s;
            background: #f0f0f0;
        }
        
        .seat.available {
            background: #4CAF50;
            color: white;
            border-color: #45a049;
        }
        
        .seat.available:hover {
            background: #45a049;
            transform: scale(1.1);
        }
        
        .seat.selected {
            background: #2196F3;
            color: white;
            border-color: #1976D2;
            transform: scale(1.1);
        }
        
        .seat.occupied {
            background: #f44336;
            color: white;
            border-color: #d32f2f;
            cursor: not-allowed;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .legend-seat {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .booking-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: center;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 0 20px;
        }
        
        .confirm-btn {
            background: #4CAF50;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .confirm-btn:hover {
            background: #45a049;
        }
        
        .confirm-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .back-btn {
            background: #7494ec;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body style="background: linear-gradient(to right, #e2e2e2, #c9d6ff); min-height: 100vh;">
    <div class="seat-container">
        <a href="movies.php?movie=<?= urlencode($showtime['movie_name']) ?>" class="back-btn">← Back to Showtimes</a>
        
        <div class="showtime-info">
            <h2><?= htmlspecialchars($showtime['movie_name']) ?></h2>
            <p><strong>Date:</strong> <?= date('l, F j, Y', strtotime($showtime['date'])) ?></p>
            <p><strong>Time:</strong> <?= date('g:i A', strtotime($showtime['start'])) ?> - <?= date('g:i A', strtotime($showtime['end'])) ?></p>
            <p><strong>Hall:</strong> <?= htmlspecialchars($showtime['hall_name']) ?></p>
            <p><strong>Duration:</strong> <?= $showtime['duration'] ?> minutes</p>
        </div>

        <div class="screen">SCREEN</div>

        <div class="seat-layout">
            <?php foreach ($all_seats as $seat_num): ?>
                <?php 
                    $is_booked = in_array($seat_num, $booked_seats);
                    $seat_class = $is_booked ? 'occupied' : 'available';
                ?>
                <div class="seat <?= $seat_class ?>" 
                     data-seat="<?= $seat_num ?>"
                     onclick="<?= $is_booked ? '' : 'toggleSeat(this, ' . $seat_num . ')' ?>">
                    <?= $seat_num ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-seat" style="background: #4CAF50;"></div>
                <span>Available</span>
            </div>
            <div class="legend-item">
                <div class="legend-seat" style="background: #2196F3;"></div>
                <span>Selected</span>
            </div>
            <div class="legend-item">
                <div class="legend-seat" style="background: #f44336;"></div>
                <span>Occupied</span>
            </div>
        </div>

        <div class="booking-summary">
            <h3>Booking Summary</h3>
            <div id="selected-seats">No seats selected</div>
            <div class="summary-row">
                <span>Ticket Price:</span>
                <span>৳<?= number_format($ticket_price, 2) ?> per seat</span>
            </div>
            <div class="summary-row">
                <span>Number of Seats:</span>
                <span id="seat-count">0</span>
            </div>
            <div class="summary-row" style="font-weight: bold; border-top: 1px solid #ddd; padding-top: 10px;">
                <span>Total Amount:</span>
                <span id="total-price">৳0.00</span>
            </div>

            <form method="POST" action="booking.php" onsubmit="return validateBookingForm()">
                <input type="hidden" name="show_id" value="<?= $show_id ?>">
                <input type="hidden" name="selected_seats" id="selected-seats-input">
                <input type="hidden" name="ticket_price" value="<?= $ticket_price ?>">
                <button type="submit" class="confirm-btn" id="confirm-booking" disabled>
                    Confirm Booking
                </button>
            </form>
        </div>
    </div>

    <script>
        let selectedSeats = [];
        const ticketPrice = <?= $ticket_price ?>;

        function toggleSeat(seatElement, seatNumber) {
            if (seatElement.classList.contains('occupied')) {
                return;
            }
            
            if (seatElement.classList.contains('selected')) {
                seatElement.classList.remove('selected');
                selectedSeats = selectedSeats.filter(seat => seat !== seatNumber);
            } else {
                seatElement.classList.add('selected');
                selectedSeats.push(seatNumber);
            }
            
            updateBookingSummary();
        }

        function updateBookingSummary() {
            const selectedSeatsDisplay = document.getElementById('selected-seats');
            const seatCountDisplay = document.getElementById('seat-count');
            const totalPriceDisplay = document.getElementById('total-price');
            const confirmButton = document.getElementById('confirm-booking');
            const selectedSeatsInput = document.getElementById('selected-seats-input');

            if (selectedSeats.length > 0) {
                selectedSeatsDisplay.textContent = 'Selected Seats: ' + selectedSeats.sort((a, b) => a - b).join(', ');
                seatCountDisplay.textContent = selectedSeats.length;
                totalPriceDisplay.textContent = '৳' + (selectedSeats.length * ticketPrice).toFixed(2);
                confirmButton.disabled = false;
                selectedSeatsInput.value = selectedSeats.join(',');
            } else {
                selectedSeatsDisplay.textContent = 'No seats selected';
                seatCountDisplay.textContent = '0';
                totalPriceDisplay.textContent = '৳0.00';
                confirmButton.disabled = true;
                selectedSeatsInput.value = '';
            }
        }

        function validateBookingForm() {
            if (selectedSeats.length === 0) {
                alert('Please select at least one seat');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>