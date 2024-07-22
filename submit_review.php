<?php
// Database connection using PDO

require "php/constants.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $hostname, $hostpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $author = $_POST['author'];
    $message = $_POST['message'];

    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (author, message) VALUES (?, ?)");
        $stmt->execute([$author, $message]);
        header("Location: reviews.php?status=success");
        exit();
    } catch(PDOException $e) {
        header("Location: reviews.php?status=error");
        exit();
    }
} else {
    header("Location: reviews.php");
    exit();
}
?>