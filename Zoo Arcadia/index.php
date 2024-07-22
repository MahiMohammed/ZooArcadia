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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arcadia Zoo - Welcome to Nature's Paradise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "header.php"; ?>

    <main class="flex-shrink-0">
        <h1 class="mb-4">Welcome to Arcadia Zoo</h1>
        <p>Discover the wonders of nature at Arcadia Zoo, where conservation meets education. Our eco-friendly haven is home to a diverse array of species from around the globe, living in carefully crafted habitats that mimic their natural environments.</p>
        <p>At Arcadia, we're committed to preserving biodiversity and inspiring the next generation of environmental stewards. Through interactive exhibits, educational programs, and hands-on experiences, we invite you to connect with wildlife and learn about the importance of ecological balance.</p>

        <h2 class="mt-5 mb-3">Zoo Hours</h2>
        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php else : ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Opening Time</th>
                        <th>Closing Time</th>
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

        <h2 class="mt-5 mb-3">Submit a Review</h2>
        <form action="submit_review.php" method="post" class="mb-5">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Your Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="review" class="form-label">Your Review:</label>
                <textarea class="form-control" id="review" name="review" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    </main>

    <?php
    include "footer.html";
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>