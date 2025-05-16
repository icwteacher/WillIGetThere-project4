let map = L.map('map').setView([51.2194, 4.4025], 8); // Start in Antwerpen
let routeControl;
let energieZonderCorrectie = 0; // in Wh

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
}).addTo(map);

function calculateRoute() {
  const start = document.getElementById('startAddress').value;
  const end = document.getElementById('endAddress').value;

  if (!start || !end) {
    alert('Vul zowel een begin- als eindadres in.');
    return;
  }

  if (routeControl) {
    map.removeControl(routeControl);
  }

  energieZonderCorrectie = 0;

  // Haal het e-mailadres van de ingelogde gebruiker op
  const gebruikerEmail = localStorage.getItem('email') || 'default@user';

  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${start}`)
    .then(res => res.json())
    .then(startData => {
      if (!startData[0]) throw new Error("Startadres niet gevonden");
      const startCoords = [parseFloat(startData[0].lat), parseFloat(startData[0].lon)];

      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${end}`)
        .then(res => res.json())
        .then(endData => {
          if (!endData[0]) throw new Error("Eindadres niet gevonden");
          const endCoords = [parseFloat(endData[0].lat), parseFloat(endData[0].lon)];

          routeControl = L.Routing.control({
            waypoints: [L.latLng(startCoords[0], startCoords[1]), L.latLng(endCoords[0], endCoords[1])],
            routeWhileDragging: false
          }).addTo(map);

          routeControl.on('routesfound', function(e) {
            const route = e.routes[0];
            const totalDistance = route.summary.totalDistance / 1000; // in km
            const avgSpeed = 15; // km/u
            const timeInHours = totalDistance / avgSpeed;

            // Gebruik meerdere punten langs de route voor windgemiddelde
            const coordinates = route.coordinates;
            const samplePoints = [coordinates[0], coordinates[Math.floor(coordinates.length / 2)], coordinates[coordinates.length - 1]];
            const windRequests = samplePoints.map(p =>
              fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${p.lat}&lon=${p.lng}&appid=e5c7456ed9c32736eb22911896c78b40&units=metric`)
            );

            Promise.all(windRequests)
              .then(responses => Promise.all(responses.map(res => res.json())))
              .then(windDataArray => {
                let avgWindSpeed = 0;
                let avgWindDir = 0;
                windDataArray.forEach(data => {
                  avgWindSpeed += data.wind.speed;
                  avgWindDir += data.wind.deg;
                });
                avgWindSpeed /= windDataArray.length;
                avgWindDir /= windDataArray.length;

                // Simpele windcorrectie op basis van gemiddelde windrichting
                const heading = route.inputWaypoints.length >= 2 ? getHeading(startCoords, endCoords) : 0;
                const windAngle = Math.abs(avgWindDir - heading);
                const windEffect = Math.cos(windAngle * Math.PI / 180); // headwind = -1, tailwind = 1

                energieZonderCorrectie = totalDistance * 10; // Wh
                const windCorrectie = 1 + (windEffect * avgWindSpeed / 20); // max ±50%

                // Haal de factor op voor de ingelogde gebruiker
                fetch('get_factor.php?email=' + encodeURIComponent(gebruikerEmail))
                  .then(r => r.json())
                  .then(data => {
                    let factor = data.factor || 1.0;
                    // Begrens de factor voor realistische waarden
                    factor = Math.max(0.7, Math.min(1.3, factor));

                    const gecorrigeerdeEnergie = energieZonderCorrectie * windCorrectie * factor;

                    // Omgerekend naar kWh
                    const energieZonderCorrectieKWh = energieZonderCorrectie / 1000;
                    const gecorrigeerdeEnergieKWh = gecorrigeerdeEnergie / 1000;

                    // Windrichting en windtype bepalen
                    const windRichtingTekst = gradenNaarWindrichting(avgWindDir);
                    const windType = bepaalWindType(windAngle);

                    document.getElementById('result').innerHTML = `
                      <p><strong>Afstand:</strong> ${totalDistance.toFixed(2)} km</p>
                      <p><strong>Gemiddelde wind:</strong> ${avgWindSpeed.toFixed(1)} m/s uit ${avgWindDir.toFixed(0)}° (${windRichtingTekst})</p>
                      <p><strong>Windtype:</strong> ${windType}</p>
                      <p><strong>Energie:</strong> ${gecorrigeerdeEnergieKWh.toFixed(3)} kWh</p>
                    `;

                    // Optioneel: factor updaten na feedback
                  });
              });
          });
        });
    })
    .catch(err => {
      console.error(err);
      alert("Er ging iets mis met het berekenen van de route.");
    });
}

function resetForm() {
  document.getElementById('startAddress').value = '';
  document.getElementById('endAddress').value = '';
  document.getElementById('result').innerHTML = '';
  document.getElementById('feedback-resultaat').innerText = '';
  if (routeControl) map.removeControl(routeControl);
}

function stuurFeedback() {
  // Gebruiker vult effectief verbruik in kWh in!
  const effectiefKWh = parseFloat(document.getElementById('effectief').value);
  if (isNaN(effectiefKWh) || effectiefKWh <= 0 || energieZonderCorrectie <= 0) {
    alert("Voer een geldig effectief energieverbruik in (in kWh).");
    return;
  }

  // Zet energieZonderCorrectie om naar kWh voor de backend
  const energieZonderCorrectieKWh = energieZonderCorrectie / 1000;

  const gebruikerEmail = localStorage.getItem('email') || 'default@user';

  fetch('update_factor.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `email=${encodeURIComponent(gebruikerEmail)}&effectief=${effectiefKWh}&energieZonderCorrectie=${energieZonderCorrectieKWh}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('feedback-resultaat').innerText = "Feedback succesvol verwerkt. Nieuwe factor: " + data.newFactor.toFixed(2);
      } else {
        document.getElementById('feedback-resultaat').innerText = "Fout bij verzenden: " + data.error;
      }
    });
}

function getHeading(start, end) {
  const dLon = (end[1] - start[1]) * Math.PI / 180;
  const lat1 = start[0] * Math.PI / 180;
  const lat2 = end[0] * Math.PI / 180;

  const y = Math.sin(dLon) * Math.cos(lat2);
  const x = Math.cos(lat1) * Math.sin(lat2) -
            Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);

  let brng = Math.atan2(y, x);
  brng = brng * 180 / Math.PI;
  return (brng + 360) % 360;
}

function gradenNaarWindrichting(graden) {
  const richtingen = ['N', 'NO', 'O', 'ZO', 'Z', 'ZW', 'W', 'NW', 'N'];
  const index = Math.round(graden / 45);
  return richtingen[index];
}

function bepaalWindType(windAngle) {
  if (windAngle < 45 || windAngle > 315) return "Meewind";
  if (windAngle > 135 && windAngle < 225) return "Tegenwind";
  return "Zijwind";
}

