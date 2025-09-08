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
    
    header("Location: user_page.php");
    exit();
}

$user_data = $user_result->fetch_assoc();
$admin_user_id = $user_data['user_id'];


if (isset($_POST['delete_movie']) && !empty($_POST['movie_name'])) {
    $movie_to_delete = $_POST['movie_name'];
    
    
    $showtime_check = $conn->query("SELECT COUNT(*) as count FROM showtime WHERE movie_name = '" . $conn->real_escape_string($movie_to_delete) . "'");
    $showtime_count = $showtime_check->fetch_assoc()['count'];
    
    
    $booking_check = $conn->query("SELECT COUNT(*) as count FROM booking b INNER JOIN showtime s ON b.show_id = s.show_id WHERE s.movie_name = '" . $conn->real_escape_string($movie_to_delete) . "'");
    $booking_count = $booking_check->fetch_assoc()['count'];
    
    if ($booking_count > 0) {
        $error_message = "Cannot delete '$movie_to_delete'. This movie has $booking_count existing bookings. Cannot delete movies with bookings.";
    } elseif ($showtime_count > 0) {
        $error_message = "Cannot delete '$movie_to_delete'. This movie has $showtime_count scheduled showtimes. Please remove all showtimes first.";
    } else {
        
        $delete_stmt = $conn->prepare("DELETE FROM movie WHERE movie_name = ?");
        $delete_stmt->bind_param("s", $movie_to_delete);
        if ($delete_stmt->execute()) {
            $success_message = "Movie '$movie_to_delete' deleted successfully!";
        } else {
            $error_message = "Error deleting movie: " . $conn->error;
        }
    }
}


$result = $conn->query("SELECT m.movie_name, m.genre, m.duration, m.release_date, m.details,
                               COUNT(s.show_id) as showtime_count,
                               COUNT(b.booking_id) as booking_count
                        FROM movie m 
                        LEFT JOIN showtime s ON m.movie_name = s.movie_name 
                        LEFT JOIN booking b ON s.show_id = b.show_id
                        GROUP BY m.movie_name, m.genre, m.duration, m.release_date, m.details
                        ORDER BY m.release_date DESC");
$movies = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Delete Movies - Admin Panel</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
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

        .content {
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: #dc3545;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .warning-text {
            text-align: center;
            color: #6c757d;
            margin-bottom: 30px;
            font-style: italic;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .movie-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .movie-card:hover {
            border-color: #dc3545;
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
        }

        .movie-card.has-dependencies {
            border-color: #ffc107;
            background: #fff3cd;
        }

        .movie-card.can-delete {
            border-color: #28a745;
            background: #d4edda;
        }

        .movie-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .movie-details {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .dependency-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .badge-safe {
            background: #28a745;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #856404;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        .delete-form {
            margin-top: 15px;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }

        .delete-btn:hover:not(:disabled) {
            background: #c82333;
            transform: translateY(-2px);
        }

        .delete-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            Welcome, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?> (Admin) | 
            <a href="admin_page.php">Dashboard</a> | 
            <a href="Movies.php">Add Movies</a> | 
            <a href="showtimes.php">Showtimes</a> | 
            <a href="logout.php">Logout</a>
        </div>

        <div class="content">
            <h1>🗑️ Delete Movies</h1>
            <p class="warning-text">⚠️ Warning: Deleting a movie is permanent and cannot be undone!</p>

            <a href="Movies.php" class="back-btn">← Back to Movie Management</a>

            <?php if (isset($success_message)): ?>
                <div class="success">✅ <?= $success_message ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error">❌ <?= $error_message ?></div>
            <?php endif; ?>

            <?php if (empty($movies)): ?>
                <div class="movie-card">
                    <div class="movie-title">No Movies Found</div>
                    <p>There are no movies in the database to delete.</p>
                    <a href="Movies.php" class="back-btn">Add Some Movies</a>
                </div>
            <?php else: ?>
                <div class="movie-grid">
                    <?php foreach($movies as $movie): ?>
                        <?php 
                        $can_delete = ($movie['showtime_count'] == 0 && $movie['booking_count'] == 0);
                        $has_bookings = $movie['booking_count'] > 0;
                        $has_showtimes = $movie['showtime_count'] > 0;
                        
                        $card_class = 'movie-card';
                        $status_text = '';
                        $status_class = '';
                        
                        if ($has_bookings) {
                            $card_class .= ' has-dependencies';
                            $status_text = 'Has Bookings';
                            $status_class = 'badge-danger';
                        } elseif ($has_showtimes) {
                            $card_class .= ' has-dependencies';
                            $status_text = 'Has Showtimes';
                            $status_class = 'badge-warning';
                        } else {
                            $card_class .= ' can-delete';
                            $status_text = 'Can Delete';
                            $status_class = 'badge-safe';
                        }
                        ?>
                        
                        <div class="<?= $card_class ?>">
                            <div class="status-badge <?= $status_class ?>"><?= $status_text ?></div>
                            
                            <div class="movie-title"><?= htmlspecialchars($movie['movie_name']) ?></div>
                            
                            <div class="movie-details">
                                <strong>Genre:</strong> <?= htmlspecialchars($movie['genre']) ?><br>
                                <strong>Duration:</strong> <?= $movie['duration'] ?> minutes<br>
                                <strong>Release:</strong> <?= $movie['release_date'] ?><br>
                                <?php if (!empty($movie['details'])): ?>
                                    <strong>Details:</strong> <?= htmlspecialchars(substr($movie['details'], 0, 100)) ?><?= strlen($movie['details']) > 100 ? '...' : '' ?>
                                <?php endif; ?>
                            </div>

                            <div class="dependency-info">
                                📊 <strong>Current Usage:</strong><br>
                                • Showtimes: <?= $movie['showtime_count'] ?><br>
                                • Bookings: <?= $movie['booking_count'] ?>
                            </div>

                            <?php if ($can_delete): ?>
                                <form method="post" class="delete-form" onsubmit="return confirmDelete('<?= htmlspecialchars($movie['movie_name']) ?>')">
                                    <input type="hidden" name="movie_name" value="<?= htmlspecialchars($movie['movie_name']) ?>">
                                    <button type="submit" name="delete_movie" class="delete-btn">
                                        🗑️ Delete This Movie
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="delete-btn" disabled>
                                    🚫 Cannot Delete 
                                    <?php if ($has_bookings): ?>
                                        (Has Bookings)
                                    <?php elseif ($has_showtimes): ?>
                                        (Remove Showtimes First)
                                    <?php endif; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete(movieName) {
            return confirm(
                'Are you absolutely sure you want to delete "' + movieName + '"?\n\n' +
                '⚠️ This action cannot be undone!\n\n' +
                'The movie will be permanently removed from the database.'
            );
        }
    </script>
</body>

</html>
