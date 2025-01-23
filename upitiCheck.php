<?php
require 'db_connection.php';

$limit = 5;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $limit; 

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = '';

if ($search) {
    $search_query = " WHERE email LIKE '%$search%'";
}

$count_query = "SELECT COUNT(*) AS total FROM contact $search_query";
$total_result = $conn->query($count_query);
$total_contacts = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_contacts / $limit);

$contacts_query = "
    SELECT id, ime, prezime, email, drzava, opis, checked, created_at 
    FROM contact
    $search_query 
    ORDER BY checked ASC, created_at DESC 
    LIMIT $limit OFFSET $offset
";
$contacts = $conn->query($contacts_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_checked'])) {
    $contact_id = (int)$_POST['contact_id'];
    $current_status = (int)$_POST['current_status'];
    $new_status = $current_status ? 0 : 1;

    $update_stmt = $conn->prepare("UPDATE contact SET checked = ? WHERE id = ?");
    $update_stmt->bind_param('ii', $new_status, $contact_id);
    if ($update_stmt->execute()) {
        header("Refresh:0");
    } else {
        echo "<p class='contacts-error-message'>Error updating status.</p>";
    }
}
?>

<div class="contacts-container">
    <h1 class="contacts-header">Upiti</h1>

    <form method="get" action="index.php" class="search-form">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="tab" value="upitiCheck">
        <input type="text" name="search" placeholder="Pretraži po emailu..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Pretraži</button>
    </form>

    <div class="contacts-table-wrapper">
        <table class="contacts-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ime</th>
                    <th>Prezime</th>
                    <th>Email</th>
                    <th>Država</th>
                    <th>Opis</th>
                    <th>Datum Kreiranja</th>
                    <th>Status</th>
                    <th>Akcija</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($contact = $contacts->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($contact['id']); ?></td>
                        <td><?php echo htmlspecialchars($contact['ime']); ?></td>
                        <td><?php echo htmlspecialchars($contact['prezime']); ?></td>
                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td><?php echo htmlspecialchars($contact['drzava']); ?></td>
                        <td style="word-break: break-word;"><?php echo htmlspecialchars($contact['opis']); ?></td>
                        <td><?php echo htmlspecialchars($contact['created_at']); ?></td>
                        <td><?php echo $contact['checked'] ? 'Pregledano' : 'Nije pregledano'; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $contact['checked']; ?>">
                                <button type="submit" name="toggle_checked" class="contacts-button-toggle">
                                    <?php echo $contact['checked'] ? 'Označi kao nepregledano' : 'Označi kao pregledano'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="index.php?page=admin&tab=kontakti&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link">Prethodna</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=admin&tab=kontakti&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="index.php?page=admin&tab=kontakti&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link">Sljedeća</a>
        <?php endif; ?>
    </div>
</div>
