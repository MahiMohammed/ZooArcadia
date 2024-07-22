<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous - Zoo Arcadia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include "header.php"; ?>

    <main class="container my-4">
        <h1 class="mb-4">Contactez-nous</h1>
        <p class="mb-4">Nous serions ravis d'avoir de vos nouvelles ! Que vous ayez des questions sur nos animaux, nos efforts de conservation ou votre visite au zoo, n'hésitez pas à nous contacter.</p>
        
        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo "<div class='alert alert-success'>Votre message a été envoyé avec succès !</div>";
            } elseif ($_GET['status'] == 'error') {
                echo "<div class='alert alert-danger'>Une erreur s'est produite lors de l'envoi de votre message. Veuillez réessayer.</div>";
            }
        }
        ?>

        <form action="send_email.php" method="post" class="contact-form">
            <div class="mb-3">
                <label for="name" class="form-label">Votre nom :</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Votre email :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Sujet :</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Votre message :</label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer le message</button>
        </form>
    </main>

    <?php include "footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>