<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

if (!isset($_SESSION['logged_in']) || $_SESSION['is_admin'] !== 1) {
    header('Location: index.php?page=login');
    exit();
}

require 'db_connection.php';

$limit = 5; 
$page = isset($_GET['page_num']) ? max((int)$_GET['page_num'], 1) : 1; 
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = '';
if ($search) {
    $search_query = "WHERE username LIKE ? OR email LIKE ?";
}

$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM users $search_query");
if ($search) {
    $search_term = "%$search%";
    $count_stmt->bind_param('ss', $search_term, $search_term);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_users = $count_result['total'];

$stmt = $conn->prepare("SELECT users.*, drzave.ime_drzave AS country_name 
                        FROM users 
                        LEFT JOIN drzave ON users.country_id = drzave.id 
                        $search_query 
                        LIMIT ? OFFSET ?");
if ($search) {
    $stmt->bind_param('ssii', $search_term, $search_term, $limit, $offset);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

$selected_user = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_user = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $country_id = $_POST['country_id'];
        $city = $_POST['city'];
        $street = $_POST['street'];
        $birth_date = $_POST['birth_date'];
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        $is_editor = isset($_POST['is_editor']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO users 
            (username, password, email, first_name, last_name, country_id, city, street, birth_date, is_admin, is_editor) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssiissii', $username, $password, $email, $first_name, $last_name, $country_id, $city, $street, $birth_date, $is_admin, $is_editor);
        $stmt->execute();
    }

    if (isset($_POST['update'])) {

        $id = $_POST['id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $country_id = $_POST['country_id'];
        $city = $_POST['city'];
        $street = $_POST['street'];
        $birth_date = $_POST['birth_date'];
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        $is_editor = isset($_POST['is_editor']) ? 1 : 0;

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users 
                SET username = ?, email = ?, password = ?, first_name = ?, last_name = ?, country_id = ?, city = ?, street = ?, birth_date = ?, is_admin = ?, is_editor = ? 
                WHERE id = ?");
            $stmt->bind_param('sssssiissiii', $username, $email, $password, $first_name, $last_name, $country_id, $city, $street, $birth_date, $is_admin, $is_editor, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users 
                SET username = ?, email = ?, first_name = ?, last_name = ?, country_id = ?, city = ?, street = ?, birth_date = ?, is_admin = ?, is_editor = ? 
                WHERE id = ?");
            $stmt->bind_param('sssssiissii', $username, $email, $first_name, $last_name, $country_id, $city, $street, $birth_date, $is_admin, $is_editor, $id);
        }
        $stmt->execute();
    }

    if (isset($_POST['delete'])) {

        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    header('Location: index.php?page=admin&tab=korisnici');
    exit();
}
?>

<div class="crud-container">
    <h1>Upravljanje korisnicima</h1>

    <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="page" value="korisnici">
        <input type="text" name="search" placeholder="Pretraži korisnike..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
        <button type="submit" class="search-button">Pretraži</button>
    </form>

    <table class="crud-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Korisničko ime</th>
            <th>E-mail</th>
            <th>Ime</th>
            <th>Prezime</th>
            <th>Država</th>
            <th>Grad</th>
            <th>Ulica</th>
            <th>Datum rođenja</th>
            <th>Admin</th>
            <th>Editor</th>
            <th>Akcije</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                <td><?php echo htmlspecialchars($user['country_name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($user['city']); ?></td>
                <td><?php echo htmlspecialchars($user['street']); ?></td>
                <td><?php echo htmlspecialchars($user['birth_date']); ?></td>
                <td><?php echo $user['is_admin'] ? 'Da' : 'Ne'; ?></td>
                <td><?php echo $user['is_editor'] ? 'Da' : 'Ne'; ?></td>
                <td>
                    <a href="index.php?page=admin&tab=korisnici&edit_id=<?php echo $user['id']; ?>" class="edit-link">Uredi</a>
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete" class="delete-button">Obriši</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php
        $total_pages = ceil($total_users / $limit);
        for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=admin&tab=korisnici&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>

    <form method="POST" class="crud-form">
        <input type="hidden" name="id" value="<?php echo $selected_user['id'] ?? ''; ?>">

        <label for="username">Korisničko ime:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($selected_user['username'] ?? ''); ?>" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($selected_user['email'] ?? ''); ?>" required>

        <label for="first_name">Ime:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($selected_user['first_name'] ?? ''); ?>" required>

        <label for="last_name">Prezime:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($selected_user['last_name'] ?? ''); ?>" required>

        <label for="country_id">Država:</label>
        <select id="country_id" name="country_id" required>
            <option value="">-- Odaberite državu --</option>
            <?php
            $sqlD = "SELECT id, ime_drzave FROM drzave ORDER BY ime_drzave";
            $resultD = $conn->query($sqlD);
            while ($row = $resultD->fetch_assoc()) {
                $selected = isset($selected_user['country_id']) && $selected_user['country_id'] == $row['id'] ? 'selected' : '';
                echo "<option value='{$row['id']}' $selected>{$row['ime_drzave']}</option>";
            }
            ?>
        </select>

        <label for="city">Grad:</label>
        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($selected_user['city'] ?? ''); ?>" required>

        <label for="street">Ulica:</label>
        <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($selected_user['street'] ?? ''); ?>" required>

        <label for="birth_date">Datum rođenja:</label>
        <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($selected_user['birth_date'] ?? ''); ?>" required>

        <label for="password">Lozinka (ostavite prazno za nepromjenu):</label>
        <input type="password" id="password" name="password">

        <label for="is_admin">Administrator:</label>
        <input type="checkbox" id="is_admin" name="is_admin" <?php echo isset($selected_user['is_admin']) && $selected_user['is_admin'] ? 'checked' : ''; ?>>

        <label for="is_editor">Editor:</label>
        <input type="checkbox" id="is_editor" name="is_editor" <?php echo isset($selected_user['is_editor']) && $selected_user['is_editor'] ? 'checked' : ''; ?>>

        <?php if ($selected_user): ?>
            <button type="submit" name="update" class="auth-button">Ažuriraj korisnika</button>
        <?php else: ?>
            <button type="submit" name="create" class="auth-button">Dodaj korisnika</button>
        <?php endif; ?>
    </form>
</div>
