<?php
require 'db_connection.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p>Article ID not provided. <a href='index.php?page=novosti'>Go back</a></p>";
    exit();
}

$article_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->bind_param('i', $article_id);
$stmt->execute();
$article_result = $stmt->get_result();

if ($article_result->num_rows === 0) {
    echo "<p>Article not found. <a href='index.php?page=novosti'>Go back</a></p>";
    exit();
}

$article = $article_result->fetch_assoc();

$stmt = $conn->prepare("SELECT i.file_path FROM images i
                        INNER JOIN article_images ai ON i.id = ai.image_id
                        WHERE ai.article_id = ?");
$stmt->bind_param('i', $article_id);
$stmt->execute();
$images_result = $stmt->get_result();
$gallery_images = $images_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="article-detail-container">
    <h1 class="article-detail-title"><?php echo htmlspecialchars($article['title']); ?></h1>
    <p class="article-detail-date"><em>Objavljeno: <?php echo htmlspecialchars($article['publication_date']); ?></em></p>
    <p class="article-detail-content"><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>

    <?php if (!empty($article['thumbnail'])): ?>
        <div class="article-detail-thumbnail">
            <h3>Naslovna slika:</h3>
            <img src="<?php echo htmlspecialchars($article['thumbnail']); ?>" alt="Thumbnail for <?php echo htmlspecialchars($article['title']); ?>" class="article-detail-thumbnail-img">
        </div>
    <?php endif; ?>

    <?php if (!empty($gallery_images)): ?>
        <div class="article-detail-gallery">
            <h3>gallerija:</h3>
            <div class="article-detail-gallery-grid">
                <?php foreach ($gallery_images as $image): ?>
                    <figure class="article-detail-gallery-item">
                        <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Gallery Image" class="article-detail-gallery-img">
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="article-detail-back">
        <a href="index.php?page=novosti" class="article-detail-back-link">Nazad</a>
    </div>
</div>
</body>
</html>
