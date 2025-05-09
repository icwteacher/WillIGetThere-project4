let seconds = 3; // Aantal seconden voor redirect
let countdownElement = document.getElementById("seconds"); // Element om tijd weer te geven

// Start aftellen
let countdown = setInterval(function () {
    seconds--; // Verlaag seconden met 1
    countdownElement.textContent = seconds; // Toon resterende seconden

    // Als tijd op is, redirect naar index.html
    if (seconds <= 0) {
        clearInterval(countdown); // Stop timer
        window.location.href = "index.html"; // Redirect naar andere pagina
    }
}, 1000); // Herhaal elke seconde (1000 ms)