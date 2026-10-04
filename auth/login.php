<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Login - LibraTech</title>
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
        }
    </style>
</head>

<body>
    <h2>Login Sitem Informasi Perpustakaan</h2>

    <form action="proses_login.php" method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
          <label>Password:</label><br>
        <input type="password" name="password"><br><br>
        <button type="submit" name="login">Sign in</button>
    </form>
</body>

</html>