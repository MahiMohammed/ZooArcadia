<?php
session_start();

require "php/constants.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $hostname, $hostpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password,password_hash($user['password'], PASSWORD_BCRYPT))) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['nom'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch ($user['role']) {
                case '2':
                    header("Location: employee.php");
                    break;
                case '1':
                    header("Location: admin.php");
                    break;
                case '3':
                    header("Location: veterinarian.php");
                    break;
                default:
                    header("Location: index.php");
                    break;
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zoo Arcadia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include "header.php"; ?>
    
    <main class="flex-shrink-0">
        <h1 class="mb-4">Connectez vous au Zoo Arcadia</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="post" class="login-form">
            <div class="mb-3">
                <label for="username" class="form-label">Nom D'utilisateur:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Connexion</button>
        </form>
    </main>

    <?php include "footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>