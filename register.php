<?php
session_start();
include 'config.php';
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = strtolower(trim($_POST['username']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        header("Location: login.php?register=success");
    } else {
        $error = "Username sudah terdaftar atau error lainnya.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Catatan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
        }
        .form-container {
            max-width: 400px;
            margin: auto;
            margin-top: 10%;
        }
    </style>
</head>
<body class="bg-light">

<div class="form-container">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="text-center mb-4">Daftar Akun</h3>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

</body>
</html>
