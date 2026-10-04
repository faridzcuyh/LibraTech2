<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <title>LibraTech - Register</title>
</head>

<body>
    <div class="login-page">

        <!-- Kolom kiri: form -->
        <div class="login-form">
            <div class="login-header">
                <h1>Create Account</h1>
                <p>Join LibraTech! Please fill in your details.</p>
            </div>

            <?php if (isset($_GET['error'])) : ?>
                <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <form action="proses_register.php" method="post">
                <div class="form-group">
                    <label for="nama">Full Name</label>
                    <input type="text" id="nama" name="nama" placeholder="Enter your full name"
                        value="<?= htmlspecialchars($_GET['nama'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username"
                        value="<?= htmlspecialchars($_GET['username'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                </div>

                <div class="form-group">
                    <label for="password2">Confirm Password</label>
                    <input type="password" id="password2" name="password2" placeholder="Retype your password" required>
                </div>

                <button type="submit" name="register" class="btn-signin">Sign up</button>
            </form>

            <p class="signup-link">Already have an account? <a href="login.php">Sign in</a></p>
        </div>

        <!-- Kolom kanan: panel gambar -->
        <div class="login-image"></div>

    </div>
</body>

</html>
