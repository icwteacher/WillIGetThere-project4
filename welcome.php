<?php
session_start();
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
        <p>Je bent succesvol ingelogd als <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.</p>

        <!-- Countdown melding -->
        <p id="countdown">Je wordt doorgestuurd over <span id="seconds">5</span> seconden...</p>

        <!-- Uitlogformulier -->
        <form action="logout.php" method="post">
            <button type="submit">Uitloggen</button>
        </form>
    </div>

    <!-- JavaScript redirect -->
    <script src="redirect.js"></script>
</body>
</html>