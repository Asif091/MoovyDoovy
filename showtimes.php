<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Get user info and check if admin
$email = $_SESSION['email'];
$user_result = $conn->query("SELECT u.user_id, u.first_name, u.last_name FROM users u INNER JOIN admin a ON u.user_id = a.user_id WHERE u.email = '$email'");

if (!$user_result || $user_result->num_rows === 0) {
    // Not an admin, redirect to user page
    header("Location: user_page.php");
    exit();
}

$user_data = $user_result->fetch_assoc();
$admin_user_id = $user_data['user_id'];

$movie = $_GET['movie'] ?? '';

$movieList = [];
$res = $conn->query("SELECT movie_name FROM movie ORDER BY movie_name");
while ($row = $res->fetch_assoc()) $movieList[] = $row['movie_name'];

$halls = [];
$res = $conn->query("SELECT hall_id, hall_name FROM hall ORDER BY hall_name");
while ($row = $res->fetch_assoc()) $halls[] = $row;

// CREATE showtime
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $m = $_POST['movie_name'] ?? '';
  $d = $_POST['date'] ?? '';
  $start = $_POST['start'] ?? '';
  $end = $_POST['end'] ?? '';
  $hall = (int)($_POST['hall_id'] ?? 0);

  if ($m && $d && $start && $end && $hall>0) {
    $conn->query("INSERT INTO showtime (`date`,`start`,`end`,movie_name,hall_id,user_id)
                  VALUES ('$d','$start','$end','$m',$hall,$admin_user_id)");
    $movie = $m; // keep selection
    $success_message = "Showtime added successfully!";
  }
}

// READ showtimes (filter by movie if provided, else show all upcoming)
// READ showtimes
if ($movie) {
  $result = $conn->query("
    SELECT s.show_id, s.date, TIME_FORMAT(s.start,'%H:%i') st, TIME_FORMAT(s.end,'%H:%i') en,
           s.movie_name, h.hall_name
    FROM showtime s JOIN hall h ON h.hall_id=s.hall_id
    WHERE s.movie_name='$movie' AND s.date >= CURDATE()
    ORDER BY s.date, s.start
  ");
} else {
  $result = $conn->query("
    SELECT s.show_id, s.date, TIME_FORMAT(s.start,'%H:%i') st, TIME_FORMAT(s.end,'%H:%i') en,
           s.movie_name, h.hall_name
    FROM showtime s JOIN hall h ON h.hall_id=s.hall_id
    WHERE s.date >= CURDATE()
    ORDER BY s.date, s.start
  ");
}

$shows = [];
if ($result) {
  while ($row = $result->fetch_assoc()) $shows[] = $row;
}
?>

<!doctype html><html><head>
<meta charset="utf-8"><title>Showtimes - Admin Panel</title>
<link rel="stylesheet" href="main.css">
<style>
.row{margin-bottom:10px} 
table{width:100%;border-collapse:collapse} 
td,th{border:1px solid #ddd;padding:8px} 
.pill{padding:6px 10px;border-radius:999px;background:#eee;color:#333;text-decoration:none}
.pill:hover{background:#ddd}
.success{background:#d4edda;color:#155724;padding:10px;border-radius:5px;margin-bottom:20px}
.nav-bar{background:#f8f9fa;padding:10px;margin-bottom:20px;text-align:right}
.nav-bar a{margin:0 10px;color:#007bff;text-decoration:none}
.nav-bar a:hover{text-decoration:underline}
</style>
</head><body>
<div class="container">
  <div class="nav-bar">
    Welcome, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?> (Admin) | 
    <a href="admin_page.php">Dashboard</a> | 
    <a href="Movies.php">Movies</a> | 
    <a href="logout.php">Logout</a>
  </div>
  <div class="form-box active">
    <h2>Showtime Management</h2>

    <?php if (isset($success_message)): ?>
      <div class="success"><?= $success_message ?></div>
    <?php endif; ?>

    <h3>Filter by Movie</h3>
    <form method="get" class="row">
      <select name="movie" onchange="this.form.submit()">
        <option value="">-- All Movies --</option>
        <?php foreach($movieList as $m): ?>
          <option value="<?= htmlspecialchars($m) ?>" <?= $movie===$m?'selected':'' ?>><?= htmlspecialchars($m) ?></option>
        <?php endforeach; ?>
      </select>
    </form>

    <h3>Add Showtime</h3>
    <form method="post">
      <div class="row">
        <select name="movie_name" required>
          <option value="">-- Movie --</option>
          <?php foreach($movieList as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>" <?= $movie===$m?'selected':'' ?>><?= htmlspecialchars($m) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row"><input type="date" name="date" required></div>
      <div class="row"><input type="time" name="start" required></div>
      <div class="row"><input type="time" name="end" required></div>
      <div class="row">
        <select name="hall_id" required>
          <option value="">-- Hall --</option>
          <?php foreach($halls as $h): ?>
            <option value="<?= (int)$h['hall_id'] ?>"><?= htmlspecialchars($h['hall_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button>Add Showtime</button>
    </form>

    <h3>Upcoming</h3>
    <table>
      <tr><th>Date</th><th>Start–End</th><th>Movie</th><th>Hall</th><th>Action</th></tr>
      <?php foreach($shows as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['date']) ?></td>
          <td><?= $s['st'] ?>–<?= $s['en'] ?></td>
          <td><?= htmlspecialchars($s['movie_name']) ?></td>
          <td><?= htmlspecialchars($s['hall_name']) ?></td>
          <td><a class="pill" href="seat_selection.php?show_id=<?= (int)$s['show_id'] ?>">View Seats</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
</body></html>
