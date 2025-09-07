<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Check if booking data is provided
if (!isset($_POST['show_id']) || !isset($_POST['selected_seats']) || !isset($_POST['ticket_price'])) {
    header("Location: movies.php");
    exit();
}

$show_id = $_POST['show_id'];
$selected_seats = explode(',', $_POST['selected_seats']);
$ticket_price = floatval($_POST['ticket_price']);

// Get user info
$email = $_SESSION['email'];
$user_result = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['user_id'];

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
        m.duration,
        m.genre
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

// Check if seats are still available
$seat_check_query = "
    SELECT booking_seat_no 
    FROM booking_seat_no bsn
    INNER JOIN booking b ON bsn.booking_id = b.booking_id
    WHERE b.show_id = $show_id AND booking_seat_no IN (" . implode(',', $selected_seats) . ")
";
$seat_check_result = $conn->query($seat_check_query);

if ($seat_check_result && $seat_check_result->num_rows > 0) {
    $_SESSION['booking_error'] = 'Some selected seats are no longer available. Please select different seats.';
    header("Location: seat_selection.php?show_id=$show_id");
    exit();
}

// Process booking if form is submitted
if (isset($_POST['confirm_payment'])) {
    try {
        // Start transaction
        $conn->autocommit(FALSE);
        
        // Calculate total price
        $total_price = count($selected_seats) * $ticket_price;
        
        // Insert booking
        $booking_query = "INSERT INTO booking (ticket_price, show_id, user_id) VALUES ($total_price, $show_id, $user_id)";
        $conn->query($booking_query);
        $booking_id = $conn->insert_id;
        
        // Insert seat bookings
        foreach ($selected_seats as $seat_no) {
            $seat_query = "INSERT INTO booking_seat_no (booking_id, booking_seat_no) VALUES ($booking_id, $seat_no)";
            $conn->query($seat_query);
        }
        
        // Insert payment record
        $payment_query = "INSERT INTO payment (booking_id, payment_status) VALUES ($booking_id, 'completed')";
        $conn->query($payment_query);
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(TRUE);
        
        // Redirect to success page
        header("Location: booking_success.php?booking_id=$booking_id");
        exit();
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $conn->autocommit(TRUE);
        $error_message = "Booking failed. Please try again.";
    }
}

// If success parameter is present, show ticket
$booking_success = isset($_GET['success']) && $_GET['success'] == 1;
$booking_id = $_GET['booking_id'] ?? null;

if ($booking_success && $booking_id) {
    // Get booking details
    $booking_details_query = "
        SELECT 
            b.booking_id,
            b.ticket_price,
            GROUP_CONCAT(bsn.booking_seat_no ORDER BY bsn.booking_seat_no) as seats,
            s.date,
            s.start,
            s.end,
            s.movie_name,
            h.hall_name,
            u.first_name,
            u.last_name,
            u.email
        FROM booking b
        INNER JOIN booking_seat_no bsn ON b.booking_id = bsn.booking_id
        INNER JOIN showtime s ON b.show_id = s.show_id
        INNER JOIN hall h ON s.hall_id = h.hall_id
        INNER JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_id = $booking_id AND b.user_id = $user_id
        GROUP BY b.booking_id
    ";
    $booking_details_result = $conn->query($booking_details_query);
    
    if ($booking_details_result && $booking_details_result->num_rows > 0) {
        $booking_details = $booking_details_result->fetch_assoc();
    }
}

$total_amount = count($selected_seats) * $ticket_price;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $booking_success ? 'Booking Confirmed' : 'Confirm Booking' ?> - MoovyDoovy</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .booking-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .booking-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .booking-header {
            text-align: center;
            margin-bottom: 30px;
            color: #7494ec;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-section h4 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #7494ec;
            padding-bottom: 5px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        
        .detail-value {
            color: #333;
        }
        
        .seat-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 15px 0;
        }
        
        .total-section {
            background: #f0f4ff;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #7494ec;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 18px;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 24px;
            color: #7494ec;
            border-top: 2px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .payment-section {
            text-align: center;
            margin-top: 30px;
        }
        
        .confirm-btn {
            background: #4CAF50;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            margin: 10px;
        }
        
        .cancel-btn {
            background: #f44336;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .ticket {
            background: white;
            border: 3px dashed #7494ec;
            border-radius: 10px;
            padding: 30px;
            margin: 20px 0;
            position: relative;
        }
        
        .ticket::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -15px;
            width: 30px;
            height: 30px;
            background: #e2e2e2;
            border-radius: 50%;
            transform: translateY(-50%);
        }
        
        .ticket::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -15px;
            width: 30px;
            height: 30px;
            background: #e2e2e2;
            border-radius: 50%;
            transform: translateY(-50%);
        }
        
        .ticket-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .ticket-title {
            font-size: 28px;
            color: #7494ec;
            margin-bottom: 5px;
        }
        
        .ticket-subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .ticket-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .download-btn {
            background: #7494ec;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        @media print {
            .no-print { display: none; }
            .ticket { border: 2px solid #333; }
        }
    </style>
</head>
<body style="background: linear-gradient(to right, #e2e2e2, #c9d6ff); min-height: 100vh;">
    <div class="booking-container">
        
        <?php if ($booking_success && isset($booking_details)): ?>
            <!-- Booking Success - Show Ticket -->
            <div class="success-message">
                <h3>🎉 Booking Confirmed Successfully!</h3>
                <p>Your booking ID is: <strong>#<?= $booking_details['booking_id'] ?></strong></p>
            </div>
            
            <div class="ticket">
                <div class="ticket-header">
                    <div class="ticket-title">MoovyDoovy Cinema</div>
                    <div class="ticket-subtitle">E-Ticket</div>
                </div>
                
                <div class="ticket-details">
                    <div class="detail-section">
                        <h4>Movie Information</h4>
                        <div class="detail-row">
                            <span class="detail-label">Movie:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking_details['movie_name']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value"><?= date('l, F j, Y', strtotime($booking_details['date'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Time:</span>
                            <span class="detail-value"><?= date('g:i A', strtotime($booking_details['start'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Hall:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking_details['hall_name']) ?></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Booking Information</h4>
                        <div class="detail-row">
                            <span class="detail-label">Customer:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking_details['first_name'] . ' ' . $booking_details['last_name']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking_details['email']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Booking ID:</span>
                            <span class="detail-value">#<?= $booking_details['booking_id'] ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Paid:</span>
                            <span class="detail-value">৳<?= number_format($booking_details['ticket_price'], 2) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="seat-list">
                    <h4>Seat Numbers</h4>
                    <div style="font-size: 24px; color: #7494ec; font-weight: bold;">
                        <?= htmlspecialchars($booking_details['seats']) ?>
                    </div>
                </div>
                
                <div class="qr-code">
                    <div style="font-size: 14px; color: #666;">Booking Reference</div>
                    <div style="font-family: monospace; font-size: 16px; margin-top: 5px;">
                        <?= strtoupper(md5($booking_details['booking_id'] . $booking_details['movie_name'])) ?>
                    </div>
                </div>
            </div>
            
            <div class="payment-section no-print">
                <button onclick="window.print()" class="download-btn">🖨️ Print Ticket</button>
                <a href="ticket_generator.php?booking_id=<?= $booking_details['booking_id'] ?>" class="download-btn">📄 Download PDF/Image</a>
                <a href="user_page.php" class="download-btn">📱 Back to Dashboard</a>
                <a href="movies.php" class="download-btn">🎬 Book Another Movie</a>
            </div>
            
        <?php else: ?>
            <!-- Booking Confirmation Form -->
            <div class="booking-card">
                <div class="booking-header">
                    <h2>Confirm Your Booking</h2>
                    <p>Please review your booking details before confirming</p>
                </div>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?= $error_message ?></div>
                <?php endif; ?>
                
                <div class="booking-details">
                    <div class="detail-section">
                        <h4>Movie Details</h4>
                        <div class="detail-row">
                            <span class="detail-label">Movie:</span>
                            <span class="detail-value"><?= htmlspecialchars($showtime['movie_name']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Genre:</span>
                            <span class="detail-value"><?= htmlspecialchars($showtime['genre']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value"><?= $showtime['duration'] ?> minutes</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value"><?= date('l, F j, Y', strtotime($showtime['date'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Time:</span>
                            <span class="detail-value"><?= date('g:i A', strtotime($showtime['start'])) ?> - <?= date('g:i A', strtotime($showtime['end'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Hall:</span>
                            <span class="detail-value"><?= htmlspecialchars($showtime['hall_name']) ?></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Customer Details</h4>
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?= $_SESSION['email'] ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="seat-list">
                    <h4>Selected Seats</h4>
                    <div style="font-size: 20px; color: #7494ec; font-weight: bold;">
                        <?= implode(', ', $selected_seats) ?>
                    </div>
                </div>
                
                <div class="total-section">
                    <div class="total-row">
                        <span>Ticket Price:</span>
                        <span>৳<?= number_format($ticket_price, 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Number of Seats:</span>
                        <span><?= count($selected_seats) ?></span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total Amount:</span>
                        <span>৳<?= number_format($total_amount, 2) ?></span>
                    </div>
                </div>
                
                <div class="payment-section">
                    <p style="margin-bottom: 20px; color: #666;">
                        By confirming this booking, you agree to our terms and conditions.
                    </p>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="show_id" value="<?= $show_id ?>">
                        <input type="hidden" name="selected_seats" value="<?= implode(',', $selected_seats) ?>">
                        <input type="hidden" name="ticket_price" value="<?= $ticket_price ?>">
                        <button type="submit" name="confirm_payment" class="confirm-btn">
                            💳 Confirm & Pay ৳<?= number_format($total_amount, 2) ?>
                        </button>
                    </form>
                    
                    <a href="seat_selection.php?show_id=<?= $show_id ?>" class="cancel-btn">
                        ← Back to Seat Selection
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>