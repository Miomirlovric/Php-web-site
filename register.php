<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
require 'db_connection.php'; 

function generateUsername($firstName, $lastName, $conn) {
    $base = strtolower(substr($firstName, 0, 1) . $lastName);
    $base = preg_replace("/\s+/", "", $base); 
    
    $username = $base;
    $count = 1;
    
    while (usernameExists($username, $conn)) {
        $username = $base . $count;
        $count++;
    }
    return $username;
}

function usernameExists($username, $conn) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result && $result->num_rows > 0);
}

function generateRandomPassword($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $password;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName   = trim($_POST['first_name']);
    $lastName    = trim($_POST['last_name']);
    $email       = trim($_POST['email']);
    $countryId   = $_POST['country_id']; 
    $city        = trim($_POST['city']);
    $street      = trim($_POST['street']);
    $birthDate   = $_POST['birth_date'];  
    
    $username = generateUsername($firstName, $lastName, $conn);
    $plainPassword = generateRandomPassword(10); 


    $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
    

    $stmt = $conn->prepare("
        INSERT INTO users
        (first_name, last_name, email, country_id, city, street, birth_date, username, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssisssss", 
        $firstName,
        $lastName,
        $email,
        $countryId, 
        $city,
        $street,
        $birthDate,
        $username,
        $hashedPassword
    );
    
    if ($stmt->execute()) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = false;
        $_SESSION['is_editor'] = false;
        echo "<p class='auth-message success'>
                Uspješna registracija!<br>
                Vaše korisničko ime: <strong>$username</strong><br>
                Lozinka (spremite je!): <strong>$plainPassword</strong>
              </p>";
    } else {
        echo "<p class='auth-message error'>Došlo je do greške prilikom registracije.</p>";
    }
}
?>

<div class="auth-container">
    <h1 class="auth-title">Registracija</h1>
    <form method="POST" action="index.php?page=register" class="auth-form">
        
        <label for="first_name">Ime:</label>
        <input type="text" id="first_name" name="first_name" required>
        
        <label for="last_name">Prezime:</label>
        <input type="text" id="last_name" name="last_name" required>
        
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="country_id">Država:</label>
        <select id="country_id" name="country_id" required>
            <option value="">-- Odaberite državu --</option>
            <?php
            $sqlD = "SELECT id, ime_drzave FROM drzave ORDER BY ime_drzave";
            $resultD = $conn->query($sqlD);
            while ($row = $resultD->fetch_assoc()) {
                $drzavaId = $row['id'];
                $drzavaNaziv = $row['ime_drzave'];
                echo "<option value='$drzavaId'>$drzavaNaziv</option>";
            }
            ?>
        </select>
        
        <label for="city">Grad:</label>
        <input type="text" id="city" name="city" required>
        
        <label for="street">Ulica:</label>
        <input type="text" id="street" name="street" required>
        
        <label for="birth_date">Datum rođenja:</label>
        <input type="date" id="birth_date" name="birth_date" required>
        
        <button type="submit" class="auth-button">Registracija</button>
    </form>
</div>
