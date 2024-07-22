<?php
session_start();

require "php/constants.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $hostname, $hostpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails de l'habitat
    $habitat_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM habitats WHERE id = ?");
    $stmt->execute([$habitat_id]);
    $habitat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$habitat) {
        throw new Exception("Habitat non trouvé");
    }

    // Récupérer les animaux pour cet habitat
    $stmt = $pdo->prepare("SELECT * FROM animals WHERE habitat_id = ?");
    $stmt->execute([$habitat_id]);
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les rapports vétérinaires pour chaque animal
    $animal_reports = [];
    foreach ($animals as $animal) {
        $stmt = $pdo->prepare("
            SELECT vr.*, u.username as author
            FROM veterinary_reports vr
            JOIN utilisateurs u ON vr.author = u.username
            WHERE vr.animal_id = ?
            ORDER BY vr.date_time DESC
            LIMIT 1
        ");
        $stmt->execute([$animal['id']]);
        $animal_reports[$animal['id']] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error_message = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($habitat['name']); ?> - Zoo Arcadia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "header.php"; ?>

    <main class="flex-shrink-0">
        <div class="container my-4">
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php else : ?>
                <h1 class="mb-4"><?php echo htmlspecialchars($habitat['name']); ?></h1>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <img src="<?php echo htmlspecialchars($habitat['image_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($habitat['name']); ?>">
                    </div>
                    <div class="col-md-6">
                        <p><?php echo htmlspecialchars($habitat['description']); ?></p>
                    </div>
                </div>

                <h2 class="mb-3">Animaux dans cet habitat</h2>

                <div id="animalsCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($animals as $index => $animal) : ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($animal['image_url']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($animal['name']); ?>">
                                <div class="carousel-caption d-none d-md-block">
                                    <h5><?php echo htmlspecialchars($animal['name']); ?></h5>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#animalModal<?php echo $animal['id']; ?>">En savoir plus</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#animalsCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Précédent</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#animalsCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Suivant</span>
                    </button>
                </div>

                <?php foreach ($animals as $animal) : ?>
                    <div class="modal fade" id="animalModal<?php echo $animal['id']; ?>" tabindex="-1" aria-labelledby="animalModalLabel<?php echo $animal['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="animalModalLabel<?php echo $animal['id']; ?>"><?php echo htmlspecialchars($animal['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <img src="<?php echo htmlspecialchars($animal['image_url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($animal['name']); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Race : <?php echo htmlspecialchars($animal['race']); ?></h6>
                                            <p><?php echo htmlspecialchars($animal['description']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6>Dernier rapport vétérinaire</h6>
                                            <?php if (isset($animal_reports[$animal['id']]) && $animal_reports[$animal['id']] !== false) : ?>
                                                <?php $report = $animal_reports[$animal['id']]; ?>
                                                <p><strong>Date :</strong>
                                                    <?php
                                                    if (!empty($report['date_time'])) {
                                                        echo date('d/m/Y H:i', strtotime($report['date_time']));
                                                    } else {
                                                        echo "Non disponible";
                                                    }
                                                    ?>
                                                </p>
                                                <p><strong>Vétérinaire :</strong> <?php echo htmlspecialchars($report['author']); ?></p>
                                                <p><strong>État :</strong> <?php echo htmlspecialchars($report['animal_condition']); ?></p>
                                                <p><strong>Nourriture recommandée :</strong> <?php echo htmlspecialchars($report['food_suggested']); ?></p>
                                                <p><strong>Quantité recommandée :</strong> <?php echo htmlspecialchars($report['food_quantity']); ?> grammes</p>
                                                <?php if (!empty($report['message'])) : ?>
                                                    <p><strong>Notes supplémentaires :</strong> <?php echo htmlspecialchars($report['message']); ?></p>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <p>Aucun rapport vétérinaire disponible pour cet animal.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include "footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>