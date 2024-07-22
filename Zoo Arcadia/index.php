<?php
session_start();

require "php/constants.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $hostname, $hostpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch zoo hours
    $stmt = $pdo->prepare("SELECT * FROM zoo_hours ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
    $stmt->execute();
    $zoo_hours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arcadia Zoo - Bienvenue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "header.php"; ?>

    <main class="flex-shrink-0">
        <h1 class="mb-4">Bienvenue au Zoo Arcadia</h1>
        <p>Découvrez les merveilles de la nature au zoo d'Arcadia, où la conservation rencontre l'éducation. Notre refuge écologique abrite un large éventail d'espèces du monde entier, vivant dans des habitats soigneusement conçus qui imitent leur environnement naturel..</p>
        <p>Chez Arcadia, nous nous engageons à préserver la biodiversité et à inspirer la prochaine génération de protecteur de l'environnement. Grâce à des expositions interactives, des programmes éducatifs et des expériences pratiques, nous vous invitons à vous connecter avec la faune et à découvrir l'importance de l'équilibre écologique.</p>

        <h2 class="mt-5 mb-3">Heures d'ouverture</h2>
        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php else : ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Heure d'ouverture</th>
                        <th>Heure de Fermeture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($zoo_hours as $hours) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($hours['day']); ?></td>
                            <td><?php echo date('g:i A', strtotime($hours['open_time'])); ?></td>
                            <td><?php echo date('g:i A', strtotime($hours['close_time'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        ?>

        <h2 class="mt-5 mb-3">Donner un avis</h2>
        <form action="submit_review.php" method="post" class="mb-5">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Votre Nom:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="review" class="form-label">Votre Avis:</label>
                <textarea class="form-control" id="review" name="review" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </main>

    <?php include "footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>