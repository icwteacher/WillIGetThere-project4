let map = L.map('map').setView([51.2194, 4.4025], 13); // Start in Antwerpen
let routeControl;
let energieZonderCorrectie = 0;

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
              fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${p.lat}&lon=${p.lng}&appid=ae514b1d22269f9e045514f22bdb61a1&units=metric`)
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

                energieZonderCorrectie = totalDistance * 10; // Basis energie (Wh/km)
                const windCorrectie = 1 + (windEffect * avgWindSpeed / 20); // max ±50%

                fetch('get_factor.php')
                  .then(r => r.json())
                  .then(data => {
                    const factor = data.factor || 1.0;
                    const gecorrigeerdeEnergie = energieZonderCorrectie * windCorrectie * factor;

                    document.getElementById('result').innerHTML = `
                      <p><strong>Afstand:</strong> ${totalDistance.toFixed(2)} km</p>
                      <p><strong>Basisenergie:</strong> ${energieZonderCorrectie.toFixed(2)} Wh</p>
                      <p><strong>Gemiddelde wind:</strong> ${avgWindSpeed.toFixed(1)} m/s vanuit ${avgWindDir.toFixed(0)}°</p>
                      <p><strong>Gecorrigeerde energie:</strong> ${gecorrigeerdeEnergie.toFixed(2)} Wh</p>
                    `;
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
  const effectief = parseFloat(document.getElementById('effectief').value);
  if (isNaN(effectief) || effectief <= 0 || energieZonderCorrectie <= 0) {
    alert("Voer een geldig effectief energieverbruik in.");
    return;
  }

  fetch('update_factor.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `effectief=${effectief}&energieZonderCorrectie=${energieZonderCorrectie}`
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
