<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Server-side validation
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: index.php");
        exit();
    }

    // Validate username format
    if (!preg_match('/^[a-zA-Z0-9@.]+$/', $username)) {
        $_SESSION['error'] = "Invalid username format.";
        header("Location: index.php");
        exit();
    }

    // Fetch user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $lockTime = 5 * 60; // 5 minutes lock time
    
    if ($user) {
        if ($user['login_attempts'] === null) {
            $user['login_attempts'] = 0;
        }

        if ($user['login_attempts'] >= 3) {
            $lastFailed = strtotime($user['last_failed_login']);
            if (time() - $lastFailed < $lockTime) {
                $_SESSION['lockout'] = $lastFailed + $lockTime; // Store lockout end time
                $_SESSION['error'] = "Account locked. Try again later.";
                header("Location: index.php");
                exit();
            } else {
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0 WHERE id = ?");
                $stmt->execute([$user['id']]);
            }
        }

        if (password_verify($password, $user['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0 WHERE id = ?");
            $stmt->execute([$user['id']]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: dashboard.php");
            exit();
        } else {
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_failed_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            $stmt = $pdo->prepare("SELECT login_attempts FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $updatedUser = $stmt->fetch();

            if ($updatedUser['login_attempts'] >= 3) {
                $_SESSION['lockout'] = time() + $lockTime;
                $_SESSION['error'] = "Account locked. Try again later.";
            } else {
                $_SESSION['error'] = "Invalid username or password. Attempts remaining: " . (3 - $updatedUser['login_attempts']);
            }

            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['unknown_login_attempts'] = ($_SESSION['unknown_login_attempts'] ?? 0) + 1;
        
        if ($_SESSION['unknown_login_attempts'] >= 3) {
            $_SESSION['lockout'] = time() + $lockTime;
            $_SESSION['error'] = "Too many failed attempts. Try again later.";
        } else {
            $_SESSION['error'] = "Invalid username or password. Attempts remaining: " . (3 - $_SESSION['unknown_login_attempts']);
        }

        header("Location: index.php");
        exit();
    }
}
?>
