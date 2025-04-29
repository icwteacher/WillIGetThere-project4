let seconds = 5;
let countdownElement = document.getElementById("seconds");

let countdown = setInterval(function () {
    seconds--;
    countdownElement.textContent = seconds;
    if (seconds <= 0) {
        clearInterval(countdown);
        window.location.href = "index.html";
    }
}, 1000);