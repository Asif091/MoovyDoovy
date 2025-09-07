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

// Get current date and calculate next 2 days
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$day_after_tomorrow = date('Y-m-d', strtotime('+2 days'));

// Get all movies with their showtimes for next 2 days only
$movies_query = "
    SELECT DISTINCT 
        m.movie_name, 
        m.genre, 
        m.duration, 
        m.release_date, 
        m.details 
    FROM movie m 
    INNER JOIN showtime s ON m.movie_name = s.movie_name 
    WHERE s.date IN ('$tomorrow', '$day_after_tomorrow')
    ORDER BY m.movie_name
";
$movies_result = $conn->query($movies_query);

// Handle showtime selection
if (isset($_GET['movie'])) {
    $selected_movie = $_GET['movie'];
    $showtimes_query = "
        SELECT 
            s.show_id,
            s.date,
            s.start,
            s.end,
            s.movie_name,
            h.hall_name,
            h.hall_id
        FROM showtime s
        INNER JOIN hall h ON s.hall_id = h.hall_id
        WHERE s.movie_name = '$selected_movie'
        AND s.date IN ('$tomorrow', '$day_after_tomorrow')
        ORDER BY s.date, s.start
    ";
    $showtimes_result = $conn->query($showtimes_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Movie Selection - MoovyDoovy</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .movies-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .movie-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .movie-card h3 {
            color: #7494ec;
            margin-bottom: 10px;
        }
        .movie-info {
            margin: 10px 0;
            color: #666;
        }
        .showtime-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .showtime-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .showtime-card:hover {
            border-color: #7494ec;
            background: #f0f4ff;
        }
        .showtime-date {
            font-weight: bold;
            color: #333;
        }
        .showtime-time {
            color: #7494ec;
            font-size: 18px;
            margin: 5px 0;
        }
        .showtime-hall {
            color: #666;
            font-size: 14px;
        }
        .back-btn, .select-btn {
            background: #7494ec;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .back-btn:hover, .select-btn:hover {
            background: #6884d3;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .user-info {
            text-align: right;
            padding: 10px;
            background: #f8f9fa;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .price-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 2px solid #28a745;
        }
        .price-text {
            font-size: 1.2em;
            font-weight: bold;
            color: #155724;
        }
        .no-movies {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
        .no-movies h3 {
            color: #6c757d;
            margin-bottom: 15px;
        }
        .no-movies p {
            color: #6c757d;
            margin-bottom: 10px;
        }
    </style>
</head>
<body style="background: linear-gradient(to right, #e2e2e2, #c9d6ff); min-height: 100vh;">
    <div class="movies-container">
        <div class="user-info">
            Welcome, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?> | 
            <a href="user_page.php">Dashboard</a> | 
            <a href="my_bookings.php">My Bookings</a> | 
            <a href="logout.php">Logout</a>
        </div>

        <div class="price-info">
            <div class="price-text">🎬 Movie Tickets: ৳500 per seat</div>
            <small>All movies • All showtimes • Premium cinema experience</small>
        </div>

        <?php if (!isset($_GET['movie'])): ?>
            <!-- Movie Selection -->
            <div class="header">
                <h1>Available Movies</h1>
                <p>Select a movie to view showtimes for the next 2 days</p>
                <p style="color: #7494ec; font-weight: bold;">Booking available for: <?= date('M d, Y', strtotime($tomorrow)) ?> and <?= date('M d, Y', strtotime($day_after_tomorrow)) ?></p>
            </div>

            <?php if ($movies_result && $movies_result->num_rows > 0): ?>
                <?php while($movie = $movies_result->fetch_assoc()): ?>
                    <div class="movie-card">
                        <h3><?= htmlspecialchars($movie['movie_name']) ?></h3>
                        <div class="movie-info">
                            <strong>Genre:</strong> <?= htmlspecialchars($movie['genre']) ?><br>
                            <strong>Duration:</strong> <?= $movie['duration'] ?> minutes<br>
                            <strong>Release Date:</strong> <?= $movie['release_date'] ?>
                        </div>
                        <?php if (!empty($movie['details'])): ?>
                            <div class="movie-info">
                                <strong>Details:</strong> <?= htmlspecialchars($movie['details']) ?>
                            </div>
                        <?php endif; ?>
                        <a href="?movie=<?= urlencode($movie['movie_name']) ?>" class="select-btn">
                            View Showtimes
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-movies">
                    <h3>🎬 No Movies Available for Booking</h3>
                    <p>There are currently no movies with scheduled showtimes for the next 2 days.</p>
                    <p><strong>Booking Period:</strong> <?= date('M d, Y', strtotime($tomorrow)) ?> to <?= date('M d, Y', strtotime($day_after_tomorrow)) ?></p>
                    <p style="margin-top: 20px;">
                        <strong>What you can do:</strong><br>
                        • Check back later for new showtimes<br>
                        • Contact cinema management for schedule updates<br>
                        • View your existing bookings
                    </p>
                    <div style="margin-top: 20px;">
                        <a href="user_page.php" class="back-btn">Back to Dashboard</a>
                        <a href="my_bookings.php" class="back-btn" style="background: #28a745;">My Bookings</a>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Showtime Selection -->
            <div class="header">
                <h1>Showtimes for: <?= htmlspecialchars($selected_movie) ?></h1>
                <a href="movies.php" class="back-btn">← Back to Movies</a>
            </div>

            <?php if (isset($showtimes_result) && $showtimes_result->num_rows > 0): ?>
                <div class="showtime-grid">
                    <?php while($showtime = $showtimes_result->fetch_assoc()): ?>
                        <div class="showtime-card" onclick="selectShowtime(<?= $showtime['show_id'] ?>)">
                            <div class="showtime-date"><?= date('M d, Y', strtotime($showtime['date'])) ?></div>
                            <div class="showtime-time"><?= date('g:i A', strtotime($showtime['start'])) ?> - <?= date('g:i A', strtotime($showtime['end'])) ?></div>
                            <div class="showtime-hall"><?= htmlspecialchars($showtime['hall_name']) ?></div>
                            <form method="POST" action="seat_selection.php" style="margin-top: 10px;">
                                <input type="hidden" name="show_id" value="<?= $showtime['show_id'] ?>">
                                <button type="submit" class="select-btn">Select Seats - ৳500</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="movie-card">
                    <h3>No Showtimes Available</h3>
                    <p>There are currently no scheduled showtimes for this movie in the next 2 days.</p>
                    <p>Booking is available for: <?= date('M d, Y', strtotime($tomorrow)) ?> and <?= date('M d, Y', strtotime($day_after_tomorrow)) ?></p>
                    <a href="movies.php" class="back-btn">← Back to Movies</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        function selectShowtime(showId) {
            // This function can be used for additional interactions
            console.log('Selected showtime ID:', showId);
        }
    </script>
</body>
</html>