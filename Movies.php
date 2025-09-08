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


if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $movie_to_delete = $_GET['delete'];
    
    
    $showtime_check = $conn->query("SELECT COUNT(*) as count FROM showtime WHERE movie_name = '" . $conn->real_escape_string($movie_to_delete) . "'");
    $showtime_count = $showtime_check->fetch_assoc()['count'];
    
    if ($showtime_count > 0) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['movie_name'] ?? '');
  $genre = trim($_POST['genre'] ?? '');
  $duration = (int)($_POST['duration'] ?? 0);
  $release = $_POST['release_date'] ?? '';
  $details = trim($_POST['details'] ?? '');

  if ($name && $genre && $duration > 0 && $release) {
    $stmt = $conn->prepare("INSERT INTO movie (movie_name, genre, duration, release_date, details, user_id) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssissi", $name, $genre, $duration, $release, $details, $admin_user_id);
    $stmt->execute();
    $success_message = "Movie added successfully!";
  }
}


$result = $conn->query("SELECT movie_name, genre, duration, release_date FROM movie ORDER BY release_date DESC");
$movies = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}

?>
<!doctype html><html><head>
<meta charset="utf-8"><title>Movies - Admin Panel</title>
<link rel="stylesheet" href="main.css">
<style>
.row{margin-bottom:10px} 
table{width:100%;border-collapse:collapse} 
td,th{border:1px solid #ddd;padding:8px}
.success{background:#d4edda;color:#155724;padding:10px;border-radius:5px;margin-bottom:20px}
.error{background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:20px}
.delete-btn{background:#dc3545;color:white;padding:5px 10px;border:none;border-radius:4px;cursor:pointer;text-decoration:none;font-size:12px}
.delete-btn:hover{background:#c82333;color:white}
.nav-bar{background:#f8f9fa;padding:10px;margin-bottom:20px;text-align:right}
.nav-bar a{margin:0 10px;color:#007bff;text-decoration:none}
.nav-bar a:hover{text-decoration:underline}
</style>
</head><body>
<div class="container">
  <div class="nav-bar">
    Welcome, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?> (Admin) | 
    <a href="admin_page.php">Dashboard</a> | 
    <a href="delete_movie.php">Delete Movies</a> | 
    <a href="showtimes.php">Showtimes</a> | 
    <a href="logout.php">Logout</a>
  </div>
  <div class="form-box active">
    <h2>Movie Management</h2>

    <?php if (isset($success_message)): ?>
      <div class="success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
      <div class="error"><?= $error_message ?></div>
    <?php endif; ?>

    <h3>Add New Movie</h3>

    <form method="post">
      <div class="row"><input name="movie_name" placeholder="Movie name" required></div>
      <div class="row"><input name="genre" placeholder="Genre" required></div>
      <div class="row"><input name="duration" type="number" min="1" placeholder="Duration (min)" required></div>
      <div class="row"><input name="release_date" type="date" required></div>
      <div class="row"><input name="details" placeholder="Details (optional)"></div>
      <button>Add Movie</button>
    </form>

    <h3>All Movies (Total: <?= count($movies) ?>)</h3>
    <table>
      <tr><th>Name</th><th>Genre</th><th>Duration</th><th>Release</th><th>Actions</th></tr>
      <?php foreach($movies as $m): ?>
        <tr>
          <td><a href="showtimes.php?movie=<?= urlencode($m['movie_name']) ?>"><?= htmlspecialchars($m['movie_name']) ?></a></td>
          <td><?= htmlspecialchars($m['genre']) ?></td>
          <td><?= (int)$m['duration'] ?> min</td>
          <td><?= htmlspecialchars($m['release_date']) ?></td>
          <td>
            <a href="showtimes.php?movie=<?= urlencode($m['movie_name']) ?>" class="pill" style="background:#28a745;color:white;padding:4px 8px;border-radius:4px;text-decoration:none;font-size:11px;margin-right:5px">Showtimes</a>
            <a href="?delete=<?= urlencode($m['movie_name']) ?>" 
               class="delete-btn" 
               onclick="return confirm('Are you sure you want to delete \'<?= htmlspecialchars($m['movie_name']) ?>\'?\n\nThis action cannot be undone!')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
</body></html>

