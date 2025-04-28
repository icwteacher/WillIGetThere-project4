<?php
session_start();

// Controleer of er op de login-knop is gedrukt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $xmlFile = 'users.xml';

    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        $username = $_POST['username'];
        $password = $_POST['password'];

        foreach ($xml->user as $user) {
            if ($user->username == $username && password_verify($password, (string)$user->password)) {
                $_SESSION['username'] = $username;
                header("Location: welcome.php"); // Verander naar welcome.php
                exit();
            }
        }
    }
    // Als we hier zijn, is de login mislukt
    $error = "Foutieve gebruikersnaam of wachtwoord.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('fiets.png') no-repeat center center fixed;
            background-size: cover;
            text-align: center;
            margin: 50px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #0000001a;
            display: inline-block;
        }
        input, button {
            margin: 10px;
            padding: 10px;
            width: 80%;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .link-button {
            background: none;
            border: none;
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Inloggen</h2>

        <!-- Toon succesbericht als je net een account hebt gemaakt -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'registered') { ?>
            <p style="color: green;">Account aangemaakt! Log in.</p>
        <?php } ?>

        <!-- Toon foutmelding als login mislukt -->
        <?php if (isset($error)) { ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php } ?>

        <form method="post">
            <input type="text" name="username" placeholder="Gebruikersnaam" required><br>
            <input type="password" name="password" placeholder="Wachtwoord" required><br>
            <button type="submit" name="login">Inloggen</button>
        </form>

        <form action="register.php" method="get">
            <p>Nog geen account? <a href="register.php" style="color: blue; text-decoration: underline;">Maak er een!</a></p>
        </form>
    </div>
</body>
</html>