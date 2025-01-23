<?php
require 'db_connection.php';

$limit = 4; 
$page = isset($_GET['nr']) ? max((int)$_GET['nr'], 1) : 1; 
$offset = ($page - 1) * $limit; 


$count_query = "SELECT COUNT(*) AS total FROM articles";
$total_result = $conn->query($count_query);
$total_articles = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_articles / $limit);


$stmt = $conn->prepare("SELECT id, title, publication_date, thumbnail, is_approved FROM articles ORDER BY publication_date DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$articles = $stmt->get_result();
?>
<div class="">
    <div class="dashboard__header">
        <h1>Novosti</h1>
        <div>
            <a href="index.php?page=admin&tab=novostiManagementEdit" class="button button--primary">Novi</a>
        </div>
    </div>

    <div class="article-list">
        <?php if ($articles->num_rows > 0 && ($_SESSION['is_admin'] === 1 || $_SESSION['is_editor'] === 1)): ?>
            <?php while ($article = $articles->fetch_assoc()): ?>
                <div class="article-card">
                    <div class="article-card__thumbnail">
                        <img src="<?php echo htmlspecialchars($article['thumbnail']); ?>" alt="Thumbnail">
                    </div>
                    <div class="article-card__content">
                        <h2 class="article-card__title"><?php echo htmlspecialchars($article['title']); ?></h2>
                        <p class="article-card__date">Objavljeno: <?php echo htmlspecialchars($article['publication_date']); ?></p>
                        <p class="article-card__status">
                            Status: 
                            <strong><?php echo $article['is_approved'] ? 'Potvrđeno' : 'Nije potvrđeno'; ?></strong>
                        </p>
                        <a href="index.php?page=admin&tab=novostiManagementEdit&id=<?php echo $article['id']; ?>" class="button button--secondary">Uredi</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nema novosti</p>
        <?php endif; ?>
    </div>
    
    <?php if ($articles->num_rows > 0 && ($_SESSION['is_admin'] === 1 || $_SESSION['is_editor'] === 1)): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="index.php?page=admin&tab=novosti&nr=<?php echo $page - 1; ?>" class="pagination-link">Prethodna</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=admin&tab=novosti&nr=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="index.php?page=admin&tab=novosti&nr=<?php echo $page + 1; ?>" class="pagination-link">Sljedeća</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
