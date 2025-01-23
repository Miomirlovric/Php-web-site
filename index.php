<?php
session_start();
?>
<!DOCTYPE html>
<html lang="hr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Web stranica modernog frizerskog salona.">
    <meta name="keywords" content="frizerski salon, šišanje, stiliziranje, ljepota, njega kose">
    <meta name="author" content="Miomir Lovrič">
    <title>Frizerski salon</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-b {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background-color: #0077cc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .login-b:hover {
            background-color: #005fa3;
        }

        .username-display {
            position: absolute;
            top: 10px;
            right: 140px;
            font-size: 1rem;
            color: white;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <header>
            <div class="banner">
                <h1>Frizerski Salon</h1>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <span class="username-display">
                        Dobrodošli, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                    </span>
                    <a href="index.php?page=logout" class="login-b">Odjava</a>
                <?php else: ?>
                    <a href="index.php?page=login" class="login-b">Prijava</a>
                <?php endif; ?>
            </div>
            <nav>
                <a href="index.php?page=pocetna">Početna stranica</a>
                <a href="index.php?page=novosti">Novosti</a>
                <a href="index.php?page=kontakt">Kontakt</a>
                <a href="index.php?page=onama">O nama</a>
                <a href="index.php?page=gallerija">Galerija</a>
                <?php if (isset($_SESSION['logged_in']) && ($_SESSION['is_admin'] === 1 || $_SESSION['is_editor'] === 1)): ?>
                    <a href="index.php?page=admin">Admin</a>
                <?php else: ?>
                    <?php if (isset($_SESSION['logged_in'])) : ?>
                        <a href="index.php?page=admin">Unos novosti</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
        </header>
        <main>
            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'pocetna';

            $allowed_pages = ['pocetna', 'novosti', 'kontakt', 'onama', 'gallerija', 'login', 'register', 'logout', 'novost', 'admin'];
            if (in_array($page, $allowed_pages)) {
                include $page . '.php';
            } else {
                echo "<p>Tražena stranica nije pronađena.</p>";
            }
            ?>
        </main>
        <footer>
            <p>&copy; Copyright © 2025 Miomir Lovric <a style="color: blue; font-weight: bold;"
                    href="https://github.com/Miomirlovric">GITHub</a>.</p>
        </footer>
    </div>
</body>

</html>