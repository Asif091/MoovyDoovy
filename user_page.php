<?php

session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>User Page</title>
    <link rel="stylesheet" href="main.css">
</head>

<body style="background-color: #f0f0f0;">
    <div class="box">
        <h1>Welcome, <span><?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?></span></h1>
        <p>This is your <span>user dashboard</span></p>
        
        <div style="margin: 30px 0; text-align: center;">
            <h3>Quick Actions</h3>
            <button onclick="window.location.href='user_movies.php'" style="display: block; width: 300px; margin: 10px auto; padding: 15px 25px; font-size: 16px; background: #7494ec; color: white; border: none; border-radius: 6px; cursor: pointer;">🎬 Book Movie Tickets</button>
            <button onclick="window.location.href='my_bookings.php'" style="display: block; width: 300px; margin: 10px auto; padding: 15px 25px; font-size: 16px; background: #7494ec; color: white; border: none; border-radius: 6px; cursor: pointer;">📋 My Bookings</button>
        </div>
        
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>

</html>