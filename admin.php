<?php
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'novosti';

$content_file = '';
if ($active_tab === 'korisnici') {
    $content_file = 'korisnici.php';
} elseif ($active_tab === 'novosti') {
    $content_file = 'novostiManagement.php';
}elseif ($active_tab === 'novostiManagementEdit') {
    $content_file = 'novostiManagementEdit.php';
}elseif ($active_tab === 'upitiCheck') {
    $content_file = 'upitiCheck.php';
}
?>

<div class="dashboard">
    <div class="dashboard__menu">
        <ul class="menu">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['is_admin'] === 1): ?>
                <li class="menu__item <?php echo $active_tab === 'korisnici' ? 'menu__item--active' : ''; ?>">
                    <a href="index.php?page=admin&tab=korisnici">Korisnici</a>
                </li>
                <li class="menu__item <?php echo $active_tab === 'upiti' ? 'menu__item--active' : ''; ?>">
                    <a href="index.php?page=admin&tab=upitiCheck">Upiti</a>
                </li>
            <?php endif; ?>

            <li class="menu__item <?php echo $active_tab === 'novosti' ? 'menu__item--active' : ''; ?>">
                <a href="index.php?page=admin&tab=novosti">Novosti</a>
            </li>
        </ul>
    </div>

    <div class="dashboard__content">
        <?php
        if ($content_file && file_exists($content_file)) {
            include $content_file;
        } else {
            echo '<p class="error-message">Selected tab does not exist.</p>';
        }
        ?>
    </div>
</div>
