<?php
session_start();

// Check if the user is logged in as an employee
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != '2') {
    header("Location: login.php");
    exit();
}

require "php/constants.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $hostname, $hostpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle review visibility toggle
if (isset($_POST['toggle_visibility'])) {
    $review_id = $_POST['review_id'];
    $visible = $_POST['visible'] ? 0 : 1;
    try {
        $stmt = $pdo->prepare("UPDATE reviews SET visible = ? WHERE id = ?");
        $stmt->execute([$visible, $review_id]);
    } catch(PDOException $e) {
        echo "Error updating review visibility: " . $e->getMessage();
    }
}

// Handle review deletion
if (isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
    } catch(PDOException $e) {
        echo "Error deleting review: " . $e->getMessage();
    }
}

// Handle service modification
if (isset($_POST['modify_service'])) {
    $service_id = $_POST['service_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];
    try {
        $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $description, $image_url, $service_id]);
    } catch(PDOException $e) {
        echo "Error updating service: " . $e->getMessage();
    }
}

// Handle service deletion
if (isset($_POST['delete_service'])) {
    $service_id = $_POST['service_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
    } catch(PDOException $e) {
        echo "Error deleting service: " . $e->getMessage();
    }
}

// Handle new service addition
if (isset($_POST['add_service'])) {
    $name = $_POST['new_name'];
    $description = $_POST['new_description'];
    $image_url = $_POST['new_image_url'];
    try {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, image_url) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $image_url]);
    } catch(PDOException $e) {
        echo "Error adding new service: " . $e->getMessage();
    }
}

// Fetch reviews
try {
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error fetching reviews: " . $e->getMessage();
}

// Fetch services
try {
    $stmt = $pdo->query("SELECT * FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error fetching services: " . $e->getMessage();
}

// Fetch animals for the dropdown menu
try {
    $stmt = $pdo->query("SELECT id, name FROM animals ORDER BY id");
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error fetching animals: " . $e->getMessage();
}

// Handle nutrition report submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_nutrition_report'])) {
    $animal_id = $_POST['animal_id'];
    $date_time = $_POST['date_time'];
    $food_type = $_POST['food_type'];
    $food_quantity = $_POST['food_quantity'];
    $author = $_SESSION['username']; // Get the username from the session

    try {
        $stmt = $pdo->prepare("INSERT INTO rapports_nutrition (animal_id, date_time, food_type, food_quantity, author) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$animal_id, $date_time, $food_type, $food_quantity, $author]);
        $success_message = "Nutrition report submitted successfully!";
    } catch(PDOException $e) {
        $error_message = "Error submitting nutrition report: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Arcadia Zoo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include "header.php"; ?>

    <main class="container my-4">
        <h1 class="mb-4">Employee Dashboard</h1>

        <h2 class="mt-4">Manage Reviews</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Message</th>
                    <th>Visible</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($review['author']); ?></td>
                        <td><?php echo htmlspecialchars($review['message']); ?></td>
                        <td><?php echo $review['visible'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <input type="hidden" name="visible" value="<?php echo $review['visible']; ?>">
                                <button type="submit" name="toggle_visibility" class="btn btn-sm btn-primary">Toggle Visibility</button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <button type="submit" name="delete_review" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2 class="mt-4">Manage Services</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <form method="post">
                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" class="form-control"></td>
                            <td><textarea name="description" class="form-control"><?php echo htmlspecialchars($service['description']); ?></textarea></td>
                            <td><input type="text" name="image_url" value="<?php echo htmlspecialchars($service['image_url']); ?>" class="form-control"></td>
                            <td>
                                <button type="submit" name="modify_service" class="btn btn-sm btn-primary">Modify</button>
                                <button type="submit" name="delete_service" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this service?')">Delete</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="mt-4">Add New Service</h3>
        <form method="post">
            <div class="mb-3">
                <label for="new_name" class="form-label">Name</label>
                <input type="text" name="new_name" id="new_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="new_description" class="form-label">Description</label>
                <textarea name="new_description" id="new_description" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label for="new_image_url" class="form-label">Image URL</label>
                <input type="text" name="new_image_url" id="new_image_url" class="form-control" required>
            </div>
            <button type="submit" name="add_service" class="btn btn-success">Add Service</button>
        </form>

        <h2 class="mt-4">Submit Nutrition Report</h2>
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
                    <option value="">Select an animal</option>
                    <?php foreach ($animals as $animal): ?>
                        <option value="<?php echo $animal['id']; ?>"><?php echo htmlspecialchars($animal['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="date_time" class="form-label">Date and Time</label>
                <input type="datetime-local" name="date_time" id="date_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="food_type" class="form-label">Food Type</label>
                <input type="text" name="food_type" id="food_type" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="food_quantity" class="form-label">Food Quantity (in grams)</label>
                <input type="number" name="food_quantity" id="food_quantity" class="form-control" required>
            </div>
            <button type="submit" name="submit_nutrition_report" class="btn btn-primary">Submit Report</button>
        </form>
    </main>

    <?php include "footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>