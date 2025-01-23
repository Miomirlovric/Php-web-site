<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
require 'db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['is_editor'] = $user['is_editor'];
        header('Location: index.php?page=pocetna'); 
        exit();
    } else {
        $error = "Pogrešno korisničko ime ili lozinka.";
    }
}
?>
<div class="auth-container">
    <h1 class="auth-title">Prijava</h1>
    <?php if (isset($error)) {
        echo "<p class='auth-message error'>$error</p>";
    } ?>
    <form method="POST" action="index.php?page=login" class="auth-form">
        <label for="username">Korisničko ime:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Lozinka:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit" class="auth-button">Prijava</button>
    </form>
    <p class="auth-link">Nemate račun? <a href="index.php?page=register">Registrirajte se ovdje</a>.</p>
</div>
