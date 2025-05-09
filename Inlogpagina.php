<?php 
session_start(); // Start een sessie, nodig voor gebruikersinlog

// Controleer of het formulier verzonden is via POST en de knop 'login' bestaat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $xmlFile = 'users.xml'; // Pad naar XML-bestand met gebruikersdata

    // Controleer of XML-bestand bestaat
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile); // Laad XML in als object
        $username = $_POST['username']; // Haal ingevoerde gebruikersnaam op
        $password = $_POST['password']; // Haal ingevoerd wachtwoord op

        // Loop door alle gebruikers in het XML-bestand
        foreach ($xml->user as $user) {
            // Vergelijk gebruikersnaam en wachtwoord met invoer
            if ($user->email == $username && $user->password == $password) {
                $_SESSION['username'] = $username; // Sla gebruikersnaam op in sessie
                header("Location: welcome.php"); // Stuur door naar welkomspagina
                exit();
            }
        }
    }
    // Toon foutmelding als login mislukt
    $error = "Foutieve gebruikersnaam of wachtwoord.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="Inlogpagina.css"> <!-- Stijlen voor inlogpagina -->
</head>
<body>
    <div class="container">
        <h2>Inloggen</h2>

        <!-- Succesbericht na registratie -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'registered') { ?>
            <p class="success">Account aangemaakt! Log in.</p>
        <?php } ?>

        <!-- Foutmelding bij mislukte login -->
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <!-- Inlogformulier -->
        <form method="post">
            <input type="email" name="username" placeholder="E-mailadres" required>
            <input type="password" name="password" placeholder="Wachtwoord" required><br>
            <button type="submit" name="login">Inloggen</button>
        </form>

        <p>Nog geen account? <a href="register.php">Maak er een!</a></p>
    </div>
</body>
</html>