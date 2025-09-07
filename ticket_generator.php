<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Check if booking_id is provided
if (!isset($_GET['booking_id'])) {
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_GET['booking_id'];

// Get user info
$email = $_SESSION['email'];
$user_result = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['user_id'];

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
        u.email,
        m.genre,
        m.duration
    FROM booking b
    INNER JOIN booking_seat_no bsn ON b.booking_id = bsn.booking_id
    INNER JOIN showtime s ON b.show_id = s.show_id
    INNER JOIN hall h ON s.hall_id = h.hall_id
    INNER JOIN users u ON b.user_id = u.user_id
    INNER JOIN movie m ON s.movie_name = m.movie_name
    WHERE b.booking_id = $booking_id AND b.user_id = $user_id
    GROUP BY b.booking_id
";
$booking_details_result = $conn->query($booking_details_query);

if (!$booking_details_result || $booking_details_result->num_rows == 0) {
    header("Location: my_bookings.php");
    exit();
}

$booking = $booking_details_result->fetch_assoc();

// Generate ticket reference
$ticket_reference = strtoupper(substr(md5($booking['booking_id'] . $booking['movie_name']), 0, 12));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ticket - MoovyDoovy</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .ticket-container { 
                width: 100%; 
                max-width: none; 
                box-shadow: none;
                border: 2px solid #000;
            }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            position: relative;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .ticket-header::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            background: linear-gradient(45deg, #667eea, #764ba2, #667eea);
            z-index: -1;
            border-radius: 20px;
        }
        
        .cinema-name {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .ticket-subtitle {
            font-size: 18px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .ticket-body {
            padding: 40px;
            background: white;
        }
        
        .movie-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        
        .ticket-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .detail-section h4 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
            padding-left: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px dotted #ddd;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        .detail-value {
            color: #333;
            font-weight: bold;
            text-align: right;
        }
        
        .seats-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .seats-section::before {
            content: '🎪';
            position: absolute;
            top: -10px;
            left: 20px;
            font-size: 30px;
        }
        
        .seats-title {
            font-size: 18px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .seats-numbers {
            font-size: 32px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .qr-section {
            text-align: center;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border: 2px dashed #667eea;
            margin: 30px 0;
        }
        
        .qr-code {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            letter-spacing: 2px;
            margin: 10px 0;
        }
        
        .booking-reference {
            color: #667eea;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .ticket-footer {
            background: #333;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .controls {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        /* Decorative elements */
        .ticket-container::before,
        .ticket-container::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
            background: #f5f5f5;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .ticket-container::before {
            left: -15px;
        }
        
        .ticket-container::after {
            right: -15px;
        }
    </style>
</head>
<body>
    <div class="controls no-print">
        <button onclick="window.print()" class="btn">🖨️ Print Ticket</button>
        <a href="my_bookings.php" class="btn btn-secondary">← Back to Bookings</a>
    </div>

    <div class="ticket-container" id="ticket">
        <div class="ticket-header">
            <div class="cinema-name">MoovyDoovy Cinema</div>
            <div class="ticket-subtitle">Electronic Ticket</div>
        </div>

        <div class="ticket-body">
            <div class="movie-title"><?= htmlspecialchars($booking['movie_name']) ?></div>

            <div class="ticket-details">
                <div class="detail-section">
                    <h4>🎬 Show Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Genre:</span>
                        <span class="detail-value"><?= htmlspecialchars($booking['genre']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Duration:</span>
                        <span class="detail-value"><?= $booking['duration'] ?> min</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value"><?= date('D, M j, Y', strtotime($booking['date'])) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Time:</span>
                        <span class="detail-value"><?= date('g:i A', strtotime($booking['start'])) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hall:</span>
                        <span class="detail-value"><?= htmlspecialchars($booking['hall_name']) ?></span>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>🎫 Booking Details</h4>
                    <div class="detail-row">
                        <span class="detail-label">Customer:</span>
                        <span class="detail-value"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?= htmlspecialchars($booking['email']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Booking ID:</span>
                        <span class="detail-value">#<?= $booking['booking_id'] ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Paid:</span>
                        <span class="detail-value">৳<?= number_format($booking['ticket_price'], 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="seats-section">
                <div class="seats-title">Seat Numbers</div>
                <div class="seats-numbers"><?= htmlspecialchars($booking['seats']) ?></div>
            </div>

            <div class="qr-section">
                <div class="booking-reference">Booking Reference</div>
                <div class="qr-code"><?= $ticket_reference ?></div>
                <div style="font-size: 12px; color: #666; margin-top: 10px;">
                    Present this ticket at the cinema entrance
                </div>
            </div>
        </div>

        <div class="ticket-footer">
            <div>🎬 Thank you for choosing MoovyDoovy Cinema! 🍿</div>
            <div style="margin-top: 5px; font-size: 12px; opacity: 0.8;">
                Generated on <?= date('F j, Y \a\t g:i A') ?>
            </div>
        </div>
    </div>
    <script>
        // Auto-hide controls after 5 seconds for clean view
        setTimeout(() => {
            const controls = document.querySelector('.controls');
            if (controls) {
                controls.style.opacity = '0.7';
                controls.addEventListener('mouseenter', () => controls.style.opacity = '1');
                controls.addEventListener('mouseleave', () => controls.style.opacity = '0.7');
            }
        }, 5000);
    </script>
</body>
</html>