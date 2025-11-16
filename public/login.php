<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';

$error = '';
$db_status = '';
$db_status_class = '';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit;
}

// Check database connection status
if ($mysqli->connect_error) {
    $db_status = "Error: Database connection failed.";
    $db_status_class = 'alert-danger';
} else {
    $db_status = "Success: Database connection established.";
    $db_status_class = 'alert-success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prevent login attempts if the database connection has failed
    if ($mysqli->connect_error) {
        $error = 'Cannot process login, database connection is down.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $mysqli->prepare("SELECT id, password FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
                        $stmt->bind_result($id, $stored_password);
                        $stmt->fetch();
            
                        if ($password === $stored_password) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'No user found with that username.';
        }
        $stmt->close();
    }
}

// It's good practice to close the connection when the script is done with it.
if (!$mysqli->connect_error) {
    $mysqli->close();
}

$page_title = 'Admin Login';
require_once '../templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 login-card">
        <div class="card mt-5">
            <div class="card-header">
                <h3 class="text-center">Admin Login</h3>
            </div>
            <div class="card-body">
                <?php if ($db_status): ?>
                    <div class="alert <?php echo $db_status_class; ?>"><?php echo $db_status; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" <?php if ($mysqli->connect_error) echo 'disabled'; ?>>Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../templates/footer.php';
?>

