<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = htmlspecialchars(trim($_POST['ime']));
    $prezime = htmlspecialchars(trim($_POST['prezime']));
    $email = htmlspecialchars(trim($_POST['email']));
    $drzava = isset($_POST['drzava']) ? htmlspecialchars(trim($_POST['drzava'])) : null;
    $opis = isset($_POST['opis']) ? htmlspecialchars(trim($_POST['opis'])) : null;

    $stmt = $conn->prepare("INSERT INTO contact (ime, prezime, email, drzava, opis) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $ime, $prezime, $email, $drzava, $opis);

    if ($stmt->execute()) {
        echo "<p>Vaša poruka je uspješno poslana. Hvala što ste nas kontaktirali!</p>";
    } else {
        echo "<p>Došlo je do pogreške prilikom slanja poruke. Molimo pokušajte ponovno.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<section>
    <h1>Kontaktirajte nas</h1>
    <p>Imate pitanja? Kontaktirajte nas putem obrasca ispod ili nas posjetite na našoj lokaciji!</p>

    <div class="map-container">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2339.9122635344074!2d15.99830739963656!3d45.77839826957616!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4765d60b86996a39%3A0x17a01f618bb0685a!2sCaffe%20bar%20Friends!5e0!3m2!1sen!2shr!4v1734187532880!5m2!1sen!2shr" 
            width="100%" 
            height="300" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
        </iframe>
    </div>

    <form action="index.php?page=kontakt" method="POST" class="contact-form">
        <div class="form-title">Kontaktirajte nas putem obrasca</div>
        <div class="form-description">Ispunite polja ispod i javit ćemo vam se u najkraćem roku.</div>

        <label for="ime" class="required">Ime</label>
        <input type="text" id="ime" name="ime" required placeholder="Vaše ime">

        <label for="prezime" class="required">Prezime</label>
        <input type="text" id="prezime" name="prezime" required placeholder="Vaše prezime">

        <label for="email" class="required">E-mail adresa</label>
        <input type="email" id="email" name="email" required placeholder="ime@domena.com">

        <label for="drzava">Država</label>
        <select id="drzava" name="drzava">
            <option value="hrvatska">Hrvatska</option>
            <option value="slovenija">Slovenija</option>
            <option value="srbija">Srbija</option>
            <option value="bih">Bosna i Hercegovina</option>
        </select>

        <label for="opis">Opis</label>
        <textarea id="opis" name="opis" rows="5" placeholder="Napišite vašu poruku ovdje..."></textarea>

        <button type="submit">Pošalji</button>
    </form>
</section>
