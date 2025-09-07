<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Get user info
$email = $_SESSION['email'];
$user_result = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['user_id'];

// Get user's bookings
$bookings_query = "
    SELECT 
        b.booking_id,
        b.ticket_price,
        s.date,
        s.start,
        s.end,
        s.movie_name,
        h.hall_name,
        GROUP_CONCAT(bsn.booking_seat_no ORDER BY bsn.booking_seat_no) as seats,
        p.payment_status,
        COUNT(bsn.booking_seat_no) as seat_count
    FROM booking b
    INNER JOIN booking_seat_no bsn ON b.booking_id = bsn.booking_id
    INNER JOIN showtime s ON b.show_id = s.show_id
    INNER JOIN hall h ON s.hall_id = h.hall_id
    LEFT JOIN payment p ON b.booking_id = p.booking_id
    WHERE b.user_id = $user_id
    GROUP BY b.booking_id
    ORDER BY s.date DESC, s.start DESC
";
$bookings_result = $conn->query($bookings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Bookings - MoovyDoovy</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .bookings-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .user-info {
            text-align: right;
            padding: 10px;
            background: #f8f9fa;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .booking-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 5px solid #7494ec;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        
        .booking-id {
            font-size: 18px;
            font-weight: bold;
            color: #7494ec;
        }
        
        .booking-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .detail-section h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        
        .detail-value {
            color: #333;
        }
        
        .seats-display {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            margin: 10px 0;
        }
        
        .seats-display strong {
            color: #7494ec;
            font-size: 16px;
        }
        
        .booking-actions {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .view-ticket-btn {
            background: #7494ec;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        
        .view-ticket-btn:hover {
            background: #6884d3;
        }
        
        .no-bookings {
            text-align: center;
            background: white;
            border-radius: 10px;
            padding: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .no-bookings h3 {
            color: #666;
            margin-bottom: 20px;
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
            margin: 10px;
        }
        
        .back-btn:hover {
            background: #6884d3;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .booking-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body style="background: linear-gradient(to right, #e2e2e2, #c9d6ff); min-height: 100vh;">
    <div class="bookings-container">
        <div class="user-info">
            Welcome, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?> | 
            <a href="user_page.php">Dashboard</a> | 
            <a href="movies.php">Book Tickets</a> | 
            <a href="logout.php">Logout</a>
        </div>

        <div class="header">
            <h1>My Bookings</h1>
            <p>View and manage your movie ticket bookings</p>
        </div>

        <a href="user_page.php" class="back-btn">← Back to Dashboard</a>

        <?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
            <?php while($booking = $bookings_result->fetch_assoc()): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-id">Booking #<?= $booking['booking_id'] ?></div>
                        <div class="booking-status status-<?= strtolower($booking['payment_status'] ?? 'pending') ?>">
                            <?= ucfirst($booking['payment_status'] ?? 'Pending') ?>
                        </div>
                    </div>

                    <div class="booking-details">
                        <div class="detail-section">
                            <h4>🎬 Movie Information</h4>
                            <div class="detail-row">
                                <span class="detail-label">Movie:</span>
                                <span class="detail-value"><?= htmlspecialchars($booking['movie_name']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date:</span>
                                <span class="detail-value"><?= date('l, F j, Y', strtotime($booking['date'])) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Time:</span>
                                <span class="detail-value"><?= date('g:i A', strtotime($booking['start'])) ?> - <?= date('g:i A', strtotime($booking['end'])) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Hall:</span>
                                <span class="detail-value"><?= htmlspecialchars($booking['hall_name']) ?></span>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h4>🎫 Booking Information</h4>
                            <div class="detail-row">
                                <span class="detail-label">Number of Seats:</span>
                                <span class="detail-value"><?= $booking['seat_count'] ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value">৳<?= number_format($booking['ticket_price'], 2) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Status:</span>
                                <span class="detail-value"><?= ucfirst($booking['payment_status'] ?? 'Pending') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="seats-display">
                        <strong>Seat Numbers: <?= htmlspecialchars($booking['seats']) ?></strong>
                    </div>

                    <?php if (($booking['payment_status'] ?? '') === 'completed'): ?>
                        <div class="booking-actions">
                            <a href="ticket_generator.php?booking_id=<?= $booking['booking_id'] ?>" class="view-ticket-btn">
                                🎟️ Download Ticket
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-bookings">
                <h3>🎬 No Bookings Yet</h3>
                <p>You haven't made any movie bookings yet.</p>
                <p>Start by browsing our available movies and showtimes!</p>
                <a href="movies.php" class="view-ticket-btn">🎬 Book Your First Movie</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>