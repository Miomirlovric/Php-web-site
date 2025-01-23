<?php
require 'db_connection.php'; 

$limit = 3; 
$page = isset($_GET['nr']) ? max((int) $_GET['nr'], 1) : 1; 
$offset = ($page - 1) * $limit; 

$count_query = "SELECT COUNT(*) AS total FROM articles  WHERE is_approved = true ";
$total_result = $conn->query($count_query);
$total_articles = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_articles / $limit);

$articles_query = $conn->prepare("SELECT * FROM articles WHERE is_approved = true ORDER BY publication_date DESC LIMIT ? OFFSET ?");
$articles_query->bind_param('ii', $limit, $offset);
$articles_query->execute();
$articles_result = $articles_query->get_result();
?>
<div class="crud-container">
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
            <a href="index.php?page=novosti&nr=<?php echo $page - 1; ?>" class="pagination-link">Prethodna</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=novosti&nr=<?php echo $i; ?>"
                class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="index.php?page=novosti&nr=<?php echo $page + 1; ?>i" class="pagination-link">Sljedeća</a>
        <?php endif; ?>
    </div>
</div>