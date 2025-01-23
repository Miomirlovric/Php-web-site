<?php
require 'db_connection.php'; 

$limit = 3; 
$page = isset($_GET['nr']) ? max((int) $_GET['nr'], 1) : 1; 
$offset = ($page - 1) * $limit; 

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = '';

if ($search) {
    $search_query = " AND (content LIKE CONCAT('%', ?, '%') OR title LIKE CONCAT('%', ?, '%'))";
}

$count_query = "SELECT COUNT(*) AS total FROM articles WHERE is_approved = true $search_query";
$count_stmt = $conn->prepare($count_query);

if ($search) {
    $count_stmt->bind_param('ss', $search, $search);
}

$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_articles = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_articles / $limit);

$articles_query = "SELECT * FROM articles WHERE is_approved = true $search_query ORDER BY publication_date DESC LIMIT ? OFFSET ?";
$articles_stmt = $conn->prepare($articles_query);

if ($search) {
    $articles_stmt->bind_param('ssii', $search, $search, $limit, $offset);
} else {
    $articles_stmt->bind_param('ii', $limit, $offset);
}

$articles_stmt->execute();
$articles_result = $articles_stmt->get_result();
?>
<div class="crud-container">
    <form method="get" action="index.php" class="search-form">
        <input type="hidden" name="page" value="novosti">
        <input type="text" name="search" placeholder="Pretraži..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Pretraži</button>
    </form>

    <div class="articles-container">
        <?php while ($article = $articles_result->fetch_assoc()): ?>
            <div class="article-card">
                <a href="index.php?page=novost&id=<?php echo $article['id']; ?>" class="article-card__thumbnail">
                    <img src="<?php echo htmlspecialchars($article['thumbnail']); ?>"
                        alt="Thumbnail for <?php echo htmlspecialchars($article['title']); ?>">
                </a>
                <div class="article-card__content">
                    <a href="index.php?page=novost&id=<?php echo $article['id']; ?>" class="article-card__title">
                        <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                    </a>
                    <p class="article-card__description"><?php echo htmlspecialchars($article['content']); ?></p>
                    <p class="article-card__date"><em>Datum objave:
                            <?php echo htmlspecialchars($article['publication_date']); ?></em></p>
                    <p><a href="index.php?page=novost&id=<?php echo $article['id']; ?>" class="article-card__read-more">Pročitaj više</a>
                    </p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="index.php?page=novosti&nr=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link">Prethodna</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=novosti&nr=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="index.php?page=novosti&nr=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link">Sljedeća</a>
        <?php endif; ?>
    </div>
</div>