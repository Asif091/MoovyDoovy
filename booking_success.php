<?php
session_start();
require_once 'db_connect.php';


if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}


if (!isset($_GET['booking_id'])) {
    header("Location: user_page.php");
    exit();
}

$booking_id = $_GET['booking_id'];


$email = $_SESSION['email'];
$user_result = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['user_id'];


$verify_booking = $conn->query("SELECT booking_id FROM booking WHERE booking_id = $booking_id AND user_id = $user_id");
if (!$verify_booking || $verify_booking->num_rows == 0) {
    header("Location: user_page.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking Successful - MoovyDoovy</title>
    <link rel="stylesheet" href="main.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .success-container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
            margin: 20px;
        }
        
        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
            animation: bounceIn 1s ease-in-out;
        }
        
        .success-title {
            font-size: 36px;
            color: #333;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .success-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .booking-reference {
            background: #f8f9fa;
            border: 2px dashed #7494ec;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .booking-reference h4 {
            color: #7494ec;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .booking-id {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            font-family: monospace;
        }
        
        .action-buttons {
            margin-top: 40px;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 200px;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        
        .btn-secondary {
            background: #7494ec;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #6884d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(116, 148, 236, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            color: #7494ec;
            border: 2px solid #7494ec;
        }
        
        .btn-outline:hover {
            background: #7494ec;
            color: white;
        }
        
        .instructions {
            background: #e8f4f8;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .instructions h5 {
            color: #17a2b8;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .instructions p {
            color: #495057;
            margin: 0;
            font-size: 14px;
        }
        
        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-container > * {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .success-container > *:nth-child(2) { animation-delay: 0.1s; }
        .success-container > *:nth-child(3) { animation-delay: 0.2s; }
        .success-container > *:nth-child(4) { animation-delay: 0.3s; }
        .success-container > *:nth-child(5) { animation-delay: 0.4s; }
        .success-container > *:nth-child(6) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <div class="success-container">
        
        <h1 class="success-title">Ticket Booked Successfully!</h1>
        
        <p class="success-message">
            Congratulations! Your movie tickets have been booked successfully. 
            You can print your tickets from the My Bookings page.
        </p>
        
        <div class="booking-reference">
            <h4>📋 Your Booking Reference</h4>
            <div class="booking-id">#<?= $booking_id ?></div>
        </div>
        
        <div class="instructions">
            <h5>📝 Next Steps:</h5>
            <p>Visit the "My Bookings" page to view your ticket details and print your tickets. Make sure to arrive at the cinema 15 minutes before the show time.</p>
        </div>
        
        <div class="action-buttons">
            <a href="my_bookings.php" class="btn btn-primary">
                📋 Go to My Bookings
            </a>
            <a href="movies.php" class="btn btn-secondary">
                🎬 Book Another Movie
            </a>
            <a href="user_page.php" class="btn btn-outline">
                🏠 Back to Dashboard
            </a>
        </div>
    </div>
</body>

</html>
