<?php
session_start();

$xmlFile = 'users.xml';

if (!file_exists($xmlFile)) {
    $xml = new SimpleXMLElement('<users></users>');
    $xml->asXML($xmlFile);
}

function registerUser($username, $password) {
    global $xmlFile;
    $xml = simplexml_load_file($xmlFile);

    foreach ($xml->user as $user) {
        if ($user->email == $username) {
            return "Dit e-mailadres is al in gebruik.";
        }
    }    

    // Nieuwe gebruiker toevoegen
    $newUser = $xml->addChild('user');
    $newUser->addChild('email', $username);
    $newUser->addChild('password', $password); // NIET-geëncrypteerd opslaan

    // Mooi opmaken met DOMDocument
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $dom->save($xmlFile);

    // Redirect na succesvol registreren
    header("Location: Inlogpagina.php?success=registered");
    exit();
}

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
        <?php if (isset($message)) echo "<p class='error'>$message</p>"; ?>
        <form method="post">
            <input type="email" name="username" placeholder="E-mailadres" required><br>
            <input type="password" name="password" placeholder="Wachtwoord" required><br>
            <button type="submit" name="register">Registreren</button>
        </form>
        <a href="Inlogpagina.php">Terug naar inloggen</a>
    </div>
</body>
</html>