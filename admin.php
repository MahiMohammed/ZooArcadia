<?php
session_start();

// Check if the user is logged in as an administrator
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != '1') {
    header("Location: login.php");
    exit();
}

require "php/constants.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $hostname, $hostpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle user creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $lastname = $_POST['lastname'];
    $role = $_POST['role'];
    $email = $_POST['email'];

    try {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password, lastname, role, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $lastname, $role, $email]);
        $success_message = "User created successfully!";

        // Send email to the new user
        $to = $email;
        $subject = "Your new account at Arcadia Zoo";
        $message = "Hello $username,\n\nYour account has been created at Arcadia Zoo.\nUsername: $username\nRole: $role\n\nPlease log in to change your password.";
        $headers = "From: admin@arcadiazoo.com";

        if (mail($to, $subject, $message, $headers)) {
            $success_message .= " An email has been sent to the user.";
        } else {
            $error_message = "Error sending email to the user.";
        }
    } catch (PDOException $e) {
        $error_message = "Error creating user: " . $e->getMessage();
    }
}

// Handle service management
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['manage_service'])) {
    $action = $_POST['action'];
    $service_id = isset($_POST['service_id']) ? $_POST['service_id'] : null;
    $nom = $_POST['nom'];
    $description = $_POST['description'];

    try {
        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO services (nom, description) VALUES (?, ?)");
            $stmt->execute([$nom, $description]);
        } elseif ($action === 'update' && $service_id) {
            $stmt = $pdo->prepare("UPDATE services SET nom = ?, description = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $service_id]);
        } elseif ($action === 'delete' && $service_id) {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$service_id]);
        }
        $success_message = "Service $action successful!";
    } catch (PDOException $e) {
        $error_message = "Error managing service: " . $e->getMessage();
    }
}

// Handle zoo hours management
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['manage_hours'])) {
    $day = $_POST['day'];
    $open_time = $_POST['open_time'];
    $close_time = $_POST['close_time'];

    try {
        $stmt = $pdo->prepare("INSERT INTO zoo_hours (day, open_time, close_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE open_time = VALUES(open_time), close_time = VALUES(close_time)");
        $stmt->execute([$day, $open_time, $close_time]);
        $success_message = "Zoo hours updated successfully!";
    } catch (PDOException $e) {
        $error_message = "Error updating zoo hours: " . $e->getMessage();
    }
}

// Handle habitat management
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['manage_habitat'])) {
    $action = $_POST['action'];
    $habitat_id = isset($_POST['habitat_id']) ? $_POST['habitat_id'] : null;
    $name = $_POST['name'];
    $description = $_POST['description'];

    try {
        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO habitats (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
        } elseif ($action === 'update' && $habitat_id) {
            $stmt = $pdo->prepare("UPDATE habitats SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $habitat_id]);
        } elseif ($action === 'delete' && $habitat_id) {
            $stmt = $pdo->prepare("DELETE FROM habitats WHERE id = ?");
            $stmt->execute([$habitat_id]);
        }
        $success_message = "Habitat $action successful!";
    } catch (PDOException $e) {
        $error_message = "Error managing habitat: " . $e->getMessage();
    }
}

// Fetch existing data for dropdowns and lists
$services = $pdo->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);
$zoo_hours = $pdo->query("SELECT * FROM zoo_hours")->fetchAll(PDO::FETCH_ASSOC);
$habitats = $pdo->query("SELECT * FROM habitats")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - Arcadia Zoo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <?php include "header.php"; ?>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#create-user">
                                Create User
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#manage-services">
                                Manage Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#manage-hours">
                                Manage Zoo Hours
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#manage-habitats">
                                Manage Habitats
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Administrator Dashboard</h1>
                </div>

                <?php
                if (isset($success_message)) {
                    echo "<div class='alert alert-success'>$success_message</div>";
                }
                if (isset($error_message)) {
                    echo "<div class='alert alert-danger'>$error_message</div>";
                }
                ?>

                <!-- User Creation Form -->
                <section id="create-user">
                    <h2 class="mt-4">Create New User</h2>
                    <form method="post" class="mb-4">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" name="lastname" id="lastname" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="2">Employee</option>
                                <option value="3">Veterinarian</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
                    </form>
                </section>

                <!-- Service Management Form -->
                <section id="manage-services">
                    <h2 class="mt-4">Manage Services</h2>
                    <form method="post" class="mb-4">
                        <div class="mb-3">
                            <label for="service_action" class="form-label">Action</label>
                            <select name="action" id="service_action" class="form-select" required>
                                <option value="create">Create</option>
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Service (for update/delete)</label>
                            <select name="service_id" id="service_id" class="form-select">
                                <option value="">Select a service</option>
                                <?php foreach ($services as $service) : ?>
                                    <option value="<?php echo $service['id']; ?>"><?php echo htmlspecialchars($service['nom']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Service Name</label>
                            <input type="text" name="nom" id="nom" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" required></textarea>
                        </div>
                        <button type="submit" name="manage_service" class="btn btn-primary">Manage Service</button>
                    </form>
                </section>

                <!-- Zoo Hours Management Form -->
                <section id="manage-hours">
                    <h2 class="mt-4">Manage Zoo Hours</h2>
                    <form method="post" class="mb-4">
                        <div class="mb-3">
                            <label for="day" class="form-label">Day</label>
                            <select name="day" id="day" class="form-select" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="open_time" class="form-label">Opening Time</label>
                            <input type="time" name="open_time" id="open_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="close_time" class="form-label">Closing Time</label>
                            <input type="time" name="close_time" id="close_time" class="form-control" required>
                        </div>
                        <button type="submit" name="manage_hours" class="btn btn-primary">Update Hours</button>
                    </form>
                </section>

                <!-- Habitat Management Form -->
                <section id="manage-habitats">
                    <h2 class="mt-4">Manage Habitats</h2>
                    <form method="post" class="mb-4">
                        <div class="mb-3">
                            <label for="habitat_action" class="form-label">Action</label>
                            <select name="action" id="habitat_action" class="form-select" required>
                                <option value="create">Create</option>
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="habitat_id" class="form-label">Habitat (for update/delete)</label>
                            <select name="habitat_id" id="habitat_id" class="form-select">
                                <option value="">Select a habitat</option>
                                <?php foreach ($habitats as $habitat) : ?>
                                    <option value="<?php echo $habitat['id']; ?>"><?php echo htmlspecialchars($habitat['nom']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Habitat Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" required></textarea>
                        </div>
                        <button type="submit" name="manage_habitat" class="btn btn-primary">Manage Habitat</button>
                    </form>
                </section>
        </div>
    </div>
    </main>

    <?php include "footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            const sections = document.querySelectorAll('main section');

            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);

                    sections.forEach(section => section.style.display = 'none');
                    targetSection.style.display = 'block';

                    sidebarLinks.forEach(link => link.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Show the first section by default
            sections[0].style.display = 'block';
        });
    </script>
</body>

</html>