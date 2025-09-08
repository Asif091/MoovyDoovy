<?php

session_start();
require_once 'db_connect.php';


if (isset($_POST['register'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? null;
    $password = $_POST['password']; 

    

    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }

    $conn->query("INSERT INTO users (first_name, last_name, email, phone, password) 
    VALUES ('$first_name', '$last_name', '$email', '$phone', '$password')");

    $user_id = $conn->insert_id;

    $customer_type = $_POST['customer_type'] ?? 'general';
    $conn->query("INSERT INTO customer (user_id, customer_type)
    VALUES ($user_id, '$customer_type')");
    header("Location: index.php");
    exit();
    

}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email' ");
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) {
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            $user_id = $user['user_id'];
            $checkAdmin = $conn->query("SELECT user_id FROM admin WHERE user_id = $user_id");
            if ($checkAdmin->num_rows > 0) {
                header("Location: admin_page.php");
            } else {
                header("Location: user_page.php");
            }
            exit();
        }
    }
    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}


?> 
