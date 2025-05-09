<?php
session_start(); // Start sessie zodat we hem kunnen vernietigen
session_destroy(); // Vernietig de hele sessie (uitloggen)
header("Location: Inlogpagina.php"); // Stuur terug naar de loginpagina
exit(); // Stop verdere uitvoering
?>