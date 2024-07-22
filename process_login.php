<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // This is a very basic example. In a real application, you would:
    // 1. Sanitize and validate input
    // 2. Check credentials against a database
    // 3. Use proper password hashing
    if ($username === "admin" && $password === "password") {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>