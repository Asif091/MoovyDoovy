<?php
session_start();
require_once 'db_connect.php';


if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}


$email = $_SESSION['email'];
$user_result = $conn->query("SELECT u.user_id, u.first_name, u.last_name FROM users u INNER JOIN admin a ON u.user_id = a.user_id WHERE u.email = '$email'");

if (!$user_result || $user_result->num_rows === 0) {
    // Not an admin, redirect to user page
    header("Location: user_page.php");
    exit();
}

$user_data = $user_result->fetch_assoc();
$admin_user_id = $user_data['user_id'];


$movie_count = $conn->query("SELECT COUNT(*) as count FROM movie")->fetch_assoc()['count'];
$showtime_count = $conn->query("SELECT COUNT(*) as count FROM showtime WHERE date >= CURDATE()")->fetch_assoc()['count'];
$booking_count = $conn->query("SELECT COUNT(*) as count FROM booking")->fetch_assoc()['count'];
$hall_count = $conn->query("SELECT COUNT(*) as count FROM hall")->fetch_assoc()['count'];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="main.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 0;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .nav-bar {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #dee2e6;
            text-align: right;
        }

        .nav-bar a {
            margin: 0 10px;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .nav-bar a:hover {
            text-decoration: underline;
        }

        h1 {
            text-align: center;
            margin: 30px 0;
            color: #333;
            font-size: 2.5em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 2.5em;
            margin: 0;
            font-weight: bold;
        }

        .stat-card p {
            margin: 10px 0 0 0;
            font-size: 1.1em;
            opacity: 0.9;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 30px;
        }

        .menu-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .menu-card:hover {
            border-color: #007bff;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.15);
        }

        .menu-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .menu-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin: 15px 0;
        }

        a.button {
            display: inline-block;
            text-decoration: none;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            padding: 12px 25px;
            border-radius: 25px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        a.button:hover {
            background: linear-gradient(135deg, #0056b3, #003d82);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="nav-bar">
            Welcome, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?> (Admin) | 
            <a href="user_page.php">User View</a> | 
            <a href="logout.php">Logout</a>
        </div>
        
        <h1>🎬 Admin Dashboard - MoovyDoovy Cinema</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $movie_count ?></h3>
                <p>Total Movies</p>
            </div>
            <div class="stat-card">
                <h3><?= $showtime_count ?></h3>
                <p>Active Showtimes</p>
            </div>
            <div class="stat-card">
                <h3><?= $booking_count ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?= $hall_count ?></h3>
                <p>Cinema Halls</p>
            </div>
        </div>
        <div class="menu-grid">
            <div class="menu-card">
                <h3>🎬 Movies</h3>
                <p>Add new movies, edit details, and manage the movie catalog</p>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <a class="button" href="Movies.php" style="flex: 1;">Add Movies</a>
                    <a class="button" href="delete_movie.php" style="flex: 1; background: linear-gradient(135deg, #dc3545, #c82333);">Delete Movies</a>
                </div>
            </div>
            
            <div class="menu-card">
                <h3>🕐 Showtimes</h3>
                <p>Schedule showtimes, set dates and times for all movies</p>
                <a class="button" href="showtimes.php">Manage Showtimes</a>
            </div>
            
            <div class="menu-card">
                <h3>🏛️ Halls</h3>
                <p>Configure cinema halls and seating arrangements</p>
                <a class="button" href="#" onclick="alert('Hall management coming soon!')">Manage Halls</a>
            </div>
            
            <div class="menu-card">
                <h3>📊 Reports</h3>
                <p>View booking statistics and revenue reports</p>
                <a class="button" href="#" onclick="alert('Reports coming soon!')">View Reports</a>
            </div>
        </div>
    </div>
</body>

</html>

<!-- 

// session_start();

// $errors = [
//   'show_listing' => $_SESSION['show_listing_error'] ?? '',
// ];

// function showError($error)
// {
//   return !empty($error) ? "<p class='error-message'>$error</p>" : '';
// }
// ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Page</title>
    <link rel="stylesheet" href="main.css">
</head>

<body style="background-color: #f0f0f0;">    
    <div class="container">
        <div class="bucket" >
            <h2>Welcome this is an <span>admin page</span></h2>
            <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
        
        <div class="bucket" id="show-update">
            <form action="show_handler.php" method="post">
            <h1>Show Listing</h1>
            <?= showError($errors['show_listing']); ?>
            <label for="date">Show Date</label>
            <input type="date" name="date" required />
            <label for="start">Start Time</label>
            <input type="time" name="start"required />
            <label for="end">End Time</label>
            <input type="time" name="end"required />
            <label for="movie_name">Movie Name</label>
            <input type="text" name="movie_name" required />
            <label for="hall_id">Hall ID</label>
            <input type="number" name="hall_id" placeholder="eg.(1,2,3...)" required />
            <label for="user_id">Your ID</label>
            <input type="number" name="user_id" placeholder="eg.(1,2,3...)" required />
            <button type="submit" name="show_listing">Update</button>
            </form>
        </div>
    </div>
</body>


</html> -->
