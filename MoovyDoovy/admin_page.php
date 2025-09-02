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
    <title>Admin Page</title>
    <link rel="stylesheet" href="main.css">
</head>

<body style="background-color: #f0f0f0;">
    <div class="box">
        <h1>Welcome, <span></span></h1>
        <p>This is an <span>admin</span> page</p>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>

</html>