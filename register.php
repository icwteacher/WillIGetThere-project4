<?php
session_start(); // Start sessie

$xmlFile = 'users.xml'; // Bestand waarin gebruikers worden opgeslagen

// Als bestand nog niet bestaat, maak het aan
if (!file_exists($xmlFile)) {
    $xml = new SimpleXMLElement('<users></users>'); // Maak root element
    $xml->asXML($xmlFile); // Sla bestand op
}

// Functie om gebruiker te registreren
function registerUser($username, $password) {
    global $xmlFile;
    $xml = simplexml_load_file($xmlFile); // Laad XML-bestand

    // Controleer of e-mailadres al bestaat
    foreach ($xml->user as $user) {
        if ($user->email == $username) {
            return "Dit e-mailadres is al in gebruik."; // Foutmelding
        }
    }

    // Voeg nieuwe gebruiker toe
    $newUser = $xml->addChild('user');
    $newUser->addChild('email', $username);
    $newUser->addChild('password', $password); // LET OP: wachtwoord is niet versleuteld

    // Netjes formatteren met DOMDocument
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $dom->save($xmlFile); // Bewaar nieuwe inhoud

    // Redirect naar loginpagina na succesvolle registratie
    header("Location: Inlogpagina.php?success=registered");
    exit();
}

// Als formulier verzonden is, registreer gebruiker
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $message = registerUser($_POST['username'], $_POST['password']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registreren</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="container">
        <h2>Account aanmaken</h2>

        <!-- Toon foutmelding indien nodig -->
        <?php if (isset($message)) echo "<p class='error'>$message</p>"; ?>

        <!-- Registratieformulier -->
        <form method="post">
            <input type="email" name="username" placeholder="E-mailadres" required><br>
            <input type="password" name="password" placeholder="Wachtwoord" required><br>
            <button type="submit" name="register">Registreren</button>
        </form>

        <a href="Inlogpagina.php">Terug naar inloggen</a>
    </div>
</body>
</html>