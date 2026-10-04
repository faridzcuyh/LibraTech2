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
    <title>LibraTech - Login</title>
</head>

<body>
    <div class="login-page">

        <!-- Kolom kiri: form -->
        <div class="login-form">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Welcome back! Please enter your details.</p>
            </div>

            <form action="proses_login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••">
                </div>

                <div class="form-row">
                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot">Forgot Password</a>
                </div>

                <button type="submit" name="login" class="btn-signin">Sign in</button>
            </form>

            <?php if (isset($_GET['registered'])) : ?>
                <div class="alert alert-success">Registration successful! Please sign in.</div>
            <?php endif; ?>

            <p class="signup-link">Don't have an account? <a href="register.php">Sign up for free</a></p>
        </div>

        <!-- Kolom kanan: panel gambar -->
        <div class="login-image"></div>

    </div>
</body>

</html>
