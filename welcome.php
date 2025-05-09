<?php
session_start(); // Start sessie
// Als gebruiker niet is ingelogd, terugsturen naar loginpagina
if (!isset($_SESSION['username'])) {
    header("Location: Inlogpagina.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welkom</title>
    <link rel="stylesheet" href="welcome.css">
</head>
<body>
    <div class="container">
        <h2>Welkom op de site!</h2>

        <!-- Toon ingelogde gebruiker -->
        <p>Je bent succesvol ingelogd als <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.</p>

        <!-- Aftellen voor redirect -->
        <p id="countdown">Je wordt doorgestuurd over <span id="seconds">3</span> seconden...</p>

        <!-- Uitloggen -->
        <form action="logout.php" method="post">
            <button type="submit">Uitloggen</button>
        </form>
    </div>

    <!-- Start de countdown met redirect.js -->
    <script src="redirect.js"></script>
</body>
</html>