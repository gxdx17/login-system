<?php
session_start();
require 'db.php';

$error = isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" crossorigin="">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>Login form</title>
    <script>
        function validateInput(event) {
            const regex = /^[a-zA-Z0-9@.]+$/;
            const inputs = document.querySelectorAll('.login__input');

            for (let input of inputs) {
                if (!regex.test(input.value)) {
                    alert("Please use only letters, numbers, '@', or '.' in the input fields.");
                    event.preventDefault();
                    return false;
                }
            }
        }
    </script>
    
</head>
<body>
    <div class="login">
        <img src="assets/img/sql_bg.png" alt="image" class="login__bg">

        <form class="login__form" method="POST" action="login.php" onsubmit="validateInput(event)" autocomplete="off">
            <h1 class="login__title">Login</h1>

            <?php if (!empty($error)): ?>
                <div class="login__error"><?php echo $error; ?></div>
            <?php endif; ?>
            

            <div class="login__inputs">
                <div class="login__box">
                    <input type="text" name="username" placeholder="Username" required class="login__input" autocomplete="off">
                    <i class="ri-user-fill"></i>
                </div>

                <div class="login__box">
                    <input type="password" name="password" placeholder="Password" required class="login__input" autocomplete="off">
                    <i class="ri-lock-2-fill"></i>
                </div>
            </div>

            <div class="login__check">
                <div class="login__check-box">
                    <input type="checkbox" class="login__check-input" id="user-check">
                    <label for="user-check" class="login__check-label">Remember me</label>
                </div>

                <a href="#" class="login__forgot">Forgot Password?</a>
            </div>

            <button type="submit" class="login__button">Login</button>

        </form>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const lockoutTime = <?php echo $_SESSION['lockout'] ?? 0; ?>;
            const currentTime = Math.floor(Date.now() / 1000);
            const inputs = document.querySelectorAll(".login__input");
            const button = document.querySelector(".login__button");

            if (lockoutTime > currentTime) {
                inputs.forEach(input => input.disabled = true);
                button.disabled = true;

                const remainingTime = lockoutTime - currentTime;
                alert(`Too many failed attempts. Try again in ${Math.ceil(remainingTime / 60)} minutes.`);
            }
        });
    </script>
    </div>
</body>
</html>