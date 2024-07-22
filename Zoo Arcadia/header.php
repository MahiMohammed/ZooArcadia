<header class="bg-success py-3">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Zoo Arcadia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Acceuil</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="habitats.php">Habitats</a></li>
                    <li class="nav-item"><a class="nav-link" href="reviews.php">Avis</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <span class="navbar-text me-3">
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                        </span>
                        <a href="logout.php" class="btn btn-outline-light">Deconnexion</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light me-2">Connexion</a>
                        <a href="#" class="btn btn-warning">Acheter des Tickets</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>