<?php

session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
  ];

$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError ($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}
function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active': '';
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>MoovyDoovy</title>
    <link rel="icon" href="MoovyDoovy_icon.png" />
    <link rel="stylesheet" href="main.css" />
  </head>

  <body>
    <div class="container">
      <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
        <form action="login-register.php" method="post">
          <h2>Login</h2>
          <?= showError($errors ['login']); ?>
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <button type="submit" name="login">Login</button>
          <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
        </form>
      </div>

      <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
        <form action="login-register.php" method="post">
          <h2>Register</h2>
          <?= showError($errors ['register']); ?>
          <input type="text" name="first_name" placeholder="First Name" required />
          <input type="text" name="last_name" placeholder="Last Name" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="tel" name="phone" placeholder="Phone (01XXXXXXXXX)" required pattern="01[0-9]{9}"/>
          <input type="password" name="password" placeholder="Password" required />
          <select name="role" required>
              <option value="">--Select Role--</option>
              <option value="customer">Customer</option>
              <option value="admin">Admin</option>
          </select>
          <button type="submit" name="Register">Register</button>
          <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
        </form>
      </div>
    </div>

    <script src="script.js"></script>

  </body>
</html>
