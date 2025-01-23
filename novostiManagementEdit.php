<?php
require 'db_connection.php';

$article = [
    'id' => '',
    'title' => '',
    'content' => '',
    'thumbnail' => '',
    'publication_date' => '',
    'is_approved' => 0 
];

$gallery_images = [];

$is_edit = isset($_GET['id']) && !empty($_GET['id']);
if ($is_edit) {
    $article_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->bind_param('i', $article_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $article = $result->fetch_assoc();
    } else {
        echo "<p>Article not found!</p>";
        exit();
    }

    $stmt = $conn->prepare("SELECT i.id, i.file_path FROM images i
                            INNER JOIN article_images ai ON i.id = ai.image_id
                            WHERE ai.article_id = ?");
    $stmt->bind_param('i', $article_id);
    $stmt->execute();
    $gallery_result = $stmt->get_result();
    $gallery_images = $gallery_result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM article_images WHERE article_id = ?");
        $stmt->bind_param('i', $article_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM images WHERE id IN (SELECT image_id FROM article_images WHERE article_id = ?)");
        $stmt->bind_param('i', $article_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->bind_param('i', $article_id);
        $stmt->execute();

        header('Location: index.php?page=admin&tab=novosti');
        exit();
    }

    $title = $_POST['title'];
    $content = $_POST['content'];
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    $thumbnail_path = $article['thumbnail'];

    if (!empty($_FILES['thumbnail']['name'])) {
        $thumbnail_dir = "uploads/thumbnails/";
        $thumbnail_name = uniqid() . "_" . basename($_FILES['thumbnail']['name']);
        $thumbnail_path = $thumbnail_dir . $thumbnail_name;

        if (!is_dir($thumbnail_dir)) {
            mkdir($thumbnail_dir, 0777, true);
        }

        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
            echo "<p>Error uploading the thumbnail. Please try again.</p>";
            exit();
        }
    }

    $current_date = date('Y-m-d'); 

    if ($is_edit) {
        if ($article['is_approved'] == 0 && $is_approved == 1) {
            $publication_date = $current_date; 
        } else {
            $publication_date = $article['publication_date'];
        }

        $stmt = $conn->prepare("UPDATE articles SET title = ?, content = ?, thumbnail = ?, publication_date = ?, is_approved = ? WHERE id = ?");
        $stmt->bind_param('ssssii', $title, $content, $thumbnail_path, $publication_date, $is_approved, $article_id);
        $stmt->execute();
    } else {
        $publication_date = $is_approved ? $current_date : null;

        $stmt = $conn->prepare("INSERT INTO articles (title, content, thumbnail, publication_date, is_approved) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssi', $title, $content, $thumbnail_path, $publication_date, $is_approved);
        $stmt->execute();
        $article_id = $stmt->insert_id; 
    }

    if (!empty($_FILES['gallery_images']['name'][0])) {
        $gallery_dir = "uploads/gallery/";

        if (!is_dir($gallery_dir)) {
            mkdir($gallery_dir, 0777, true);
        }

        if ($is_edit) {
            $stmt = $conn->prepare("DELETE FROM article_images WHERE article_id = ?");
            $stmt->bind_param('i', $article_id);
            $stmt->execute();
        }

        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            $gallery_name = uniqid() . "_" . basename($_FILES['gallery_images']['name'][$key]);
            $gallery_path = $gallery_dir . $gallery_name;

            if (move_uploaded_file($tmp_name, $gallery_path)) {
                $stmt = $conn->prepare("INSERT INTO images (file_path) VALUES (?)");
                $stmt->bind_param('s', $gallery_path);
                $stmt->execute();
                $image_id = $stmt->insert_id;

                $stmt = $conn->prepare("INSERT INTO article_images (article_id, image_id) VALUES (?, ?)");
                $stmt->bind_param('ii', $article_id, $image_id);
                $stmt->execute();
            } else {
                echo "<p>Error uploading gallery image: " . htmlspecialchars($_FILES['gallery_images']['name'][$key]) . "</p>";
            }
        }
    }

    header('Location: index.php?page=admin&tab=novosti');
    exit();
}
?>

<div class="article-manager">
    <h1 class="header__title"><?php echo ($is_edit ? 'Uredi' : 'Dodaj').' novost'; ?></h1>
    <form method="POST" enctype="multipart/form-data" class="article-form">
        <div class="form-group">
            <label for="title">Naslov</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
        </div>

        <div class="form-group">
            <label for="content">Sadržaj</label>
            <textarea id="content" name="content" rows="5" required><?php echo htmlspecialchars($article['content']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="thumbnail">Naslovna slika</label>
            <input type="file" id="thumbnail" name="thumbnail">
            <?php if ($is_edit && !empty($article['thumbnail'])): ?>
                <p>Naslovna slika:</p>
                <img src="<?php echo htmlspecialchars($article['thumbnail']); ?>" alt="Current Thumbnail" style="max-width: 150px;">
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="gallery_images">Gallerija</label>
            <input type="file" id="gallery_images" name="gallery_images[]" multiple>
            <?php if ($is_edit && !empty($gallery_images)): ?>
                <p>Slike gallerije:</p>
                <div class="gallery-preview">
                    <?php foreach ($gallery_images as $image): ?>
                        <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Gallery Image" style="max-width: 100px; margin-right: 10px;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($_SESSION['is_admin'] === 1 || $_SESSION['is_editor'] === 1): ?>
            <div class="form-group">
                <label for="is_approved">Odobreno</label>
                <input type="checkbox" id="is_approved" name="is_approved" <?php echo $article['is_approved'] ? 'checked' : ''; ?>>
            </div>
        <?php endif; ?>

        <button type="submit" class="button button--primary"><?php echo $is_edit ? 'Ažuriraj' : 'Dodaj'; ?></button>
        <?php if ($is_edit && $_SESSION['is_admin'] === 1): ?>
            <button type="submit" name="delete" class="button button--danger">Obriši</button>
        <?php endif; ?>
        <a href="index.php?page=admin&tab=novosti" class="button button--secondary">Nazad</a>
    </form>
</div>
