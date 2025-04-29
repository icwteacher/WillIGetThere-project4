<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $xmlFile = 'users.xml';

    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        $username = $_POST['username'];
        $password = $_POST['password'];

        foreach ($xml->user as $user) {
            if ($user->email == $username && $user->password == $password) {
                $_SESSION['username'] = $username;
                header("Location: welcome.php");
                exit();
            }
        }
    }
    $error = "Foutieve gebruikersnaam of wachtwoord.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="Inlogpagina.css">
</head>
<body>
    <div class="container">
        <h2>Inloggen</h2>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'registered') { ?>
            <p class="success">Account aangemaakt! Log in.</p>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="post">
            <input type="email" name="username" placeholder="E-mailadres" required>
            <input type="password" name="password" placeholder="Wachtwoord" required><br>
            <button type="submit" name="login">Inloggen</button>
        </form>

        <p>Nog geen account? <a href="register.php">Maak er een!</a></p>
    </div>
</body>
</html>