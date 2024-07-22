<?php
session_start();

require "php/constants.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $hostname, $hostpassword);
    // Définir le mode d'erreur PDO sur exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

// Récupérer les services de la base de données
try {
    $stmt = $pdo->query("SELECT id, name, description, image_url FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur lors de la récupération des services : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services du Zoo - Zoo Arcadia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include "header.php"; ?>

    <main class="flex-shrink-0">
        <h1 class="mb-4">Nos Services</h1>
        
        <div id="servicesCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($services as $index => $service): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($service['image_url']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($service['name']); ?>">
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?php echo htmlspecialchars($service['name']); ?></h5>
                            <p><?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?></p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal<?php echo $service['id']; ?>">En savoir plus</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#servicesCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Précédent</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#servicesCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Suivant</span>
            </button>
        </div>

        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($service['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($service['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?></p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal<?php echo $service['id']; ?>">En savoir plus</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php foreach ($services as $service): ?>
            <div class="modal fade" id="serviceModal<?php echo $service['id']; ?>" tabindex="-1" aria-labelledby="serviceModalLabel<?php echo $service['id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="serviceModalLabel<?php echo $service['id']; ?>"><?php echo htmlspecialchars($service['name']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="<?php echo htmlspecialchars($service['image_url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($service['name']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

    <?php include "footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>