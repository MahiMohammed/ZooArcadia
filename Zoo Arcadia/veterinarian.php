<?php
session_start();

// Vérifier si l'utilisateur est connecté en tant que vétérinaire
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != '3') {
    header("Location: login.php");
    exit();
}

require "php/constants.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $hostname, $hostpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

// Récupérer les animaux pour le menu déroulant
try {
    $stmt = $pdo->query("SELECT id, name FROM animals ORDER BY name");
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Erreur lors de la récupération des animaux : " . $e->getMessage();
}

// Traiter la soumission du rapport vétérinaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_vet_report'])) {
    $animal_id = $_POST['animal_id'];
    $condition = $_POST['condition'];
    $food_suggested = $_POST['food_suggested'];
    $food_quantity = $_POST['food_quantity'];
    $date_time = $_POST['date_time'];
    $message = $_POST['message'];
    $author = $_SESSION['username']; // Obtenir le nom d'utilisateur de la session

    try {
        $stmt = $pdo->prepare("INSERT INTO veterinary_reports (animal_id, animal_condition, food_suggested, food_quantity, date_time, message, author) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$animal_id, $condition, $food_suggested, $food_quantity, $date_time, $message, $author]);
        $success_message = "Rapport vétérinaire soumis avec succès !";
    } catch(PDOException $e) {
        $error_message = "Erreur lors de la soumission du rapport vétérinaire : " . $e->getMessage();
    }
}

// Récupérer les utilisateurs (employés) pour le menu déroulant
try {
    $stmt = $pdo->query("SELECT username FROM utilisateurs WHERE role = '2' ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}

// Récupérer les rapports de nutrition pour l'utilisateur sélectionné
$selectedUserId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$nutritionReports = [];

if ($selectedUserId) {
    try {
        $stmt = $pdo->prepare("
            SELECT nr.*, animal_id AS animal_name, u.username
            FROM rapports_nutrition nr
            JOIN animals a ON nr.animal_id = a.id
            JOIN utilisateurs u ON nr.author = u.username
            WHERE nr.author = ?
            ORDER BY nr.date_time DESC
        ");
        $stmt->execute([$selectedUserId]);
        $nutritionReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Erreur lors de la récupération des rapports de nutrition : " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord du vétérinaire - Zoo Arcadia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include "header.php"; ?>

    <main class="container my-4">
        <h1 class="mb-4">Tableau de bord du vétérinaire</h1>

        <!-- Formulaire de rapport vétérinaire -->
        <h2 class="mt-4">Soumettre un rapport vétérinaire</h2>
        <?php
        if (isset($success_message)) {
            echo "<div class='alert alert-success'>$success_message</div>";
        }
        if (isset($error_message)) {
            echo "<div class='alert alert-danger'>$error_message</div>";
        }
        ?>
        <form method="post" class="mb-4">
            <div class="mb-3">
                <label for="animal_id" class="form-label">Animal</label>
                <select name="animal_id" id="animal_id" class="form-select" required>
                    <option value="">Sélectionner un animal</option>
                    <?php foreach ($animals as $animal): ?>
                        <option value="<?php echo $animal['id']; ?>"><?php echo htmlspecialchars($animal['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="condition" class="form-label">État de l'animal</label>
                <input type="text" name="condition" id="condition" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="food_suggested" class="form-label">Nourriture suggérée</label>
                <input type="text" name="food_suggested" id="food_suggested" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="food_quantity" class="form-label">Quantité de nourriture suggérée (en grammes)</label>
                <input type="number" name="food_quantity" id="food_quantity" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="date_time" class="form-label">Date et heure</label>
                <input type="datetime-local" name="date_time" id="date_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message optionnel</label>
                <textarea name="message" id="message" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" name="submit_vet_report" class="btn btn-primary">Soumettre le rapport</button>
        </form>

        <!-- Visualisation des rapports de nutrition -->
        <h2 class="mt-5">Voir les rapports de nutrition par utilisateur</h2>
        <form method="get" class="mb-4">
            <div class="mb-3">
                <label for="user_id" class="form-label">Sélectionner un utilisateur</label>
                <select name="user_id" id="user_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Sélectionner un utilisateur</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['username']; ?>" <?php echo $selectedUserId == $user['username'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if ($selectedUserId && !empty($nutritionReports)): ?>
            <h3>Rapports de nutrition pour <?php echo htmlspecialchars($nutritionReports[0]['username']); ?></h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date/Heure</th>
                        <th>Animal</th>
                        <th>Type de nourriture</th>
                        <th>Quantité de nourriture (g)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nutritionReports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['date_time']); ?></td>
                            <td><?php echo htmlspecialchars($report['animal_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['food_type']); ?></td>
                            <td><?php echo htmlspecialchars($report['food_quantity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($selectedUserId): ?>
            <p>Aucun rapport de nutrition trouvé pour cet utilisateur.</p>
        <?php endif; ?>

    </main>

    <?php include "footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>