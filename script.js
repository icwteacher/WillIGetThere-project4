let map = L.map('map').setView([51.1, 4.4], 9); // VOnze regio in het midden
let routeControl;
let routeDistanceKm = 0;
const apiKey = 'e5c7456ed9c32736eb22911896c78b40';

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

function calculateRoute() {
  const start = document.getElementById('startAddress').value;
  const end = document.getElementById('endAddress').value;

  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${start}`)
    .then(res => res.json())
    .then(startData => {
      const startCoords = [parseFloat(startData[0].lat), parseFloat(startData[0].lon)];

      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${end}`)
        .then(res => res.json())
        .then(endData => {
          const endCoords = [parseFloat(endData[0].lat), parseFloat(endData[0].lon)];

          if (routeControl) map.removeControl(routeControl);

          routeControl = L.Routing.control({
            waypoints: [L.latLng(...startCoords), L.latLng(...endCoords)],
            routeWhileDragging: false,
            addWaypoints: false,
            createMarker: function() { return null; }
          })
          .on('routesfound', function (e) {
            routeDistanceKm = e.routes[0].summary.totalDistance / 1000; // meter naar km
            getWindAndEnergy(startCoords, endCoords);
          })
          .addTo(map);
        });
    });
}

function getWindAndEnergy(startCoords, endCoords) {
  const midLat = (startCoords[0] + endCoords[0]) / 2;
  const midLon = (startCoords[1] + endCoords[1]) / 2;

  fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${midLat}&lon=${midLon}&appid=${apiKey}&units=metric`)
    .then(res => res.json())
    .then(data => {
      const windSpeed = data.wind.speed * 3.6; // m/s naar km/u
      const windDeg = data.wind.deg;

      const routeAngle = getBearing(startCoords, endCoords);
      const windEffect = getWindEffect(routeAngle, windDeg);
      const windDirectionStr = degToCompass(windDeg);

      const baseEnergy = routeDistanceKm * 10; // 10 Wh per km
      let windFactor = windEffect === 'tegenwind' ? 1.2 : windEffect === 'meewind' ? 0.85 : 1;
      const energyUsed = (baseEnergy * windFactor).toFixed(2);

      document.getElementById('result').innerHTML = `
        <h2>Resultaten:</h2>
        <p><strong>Windsnelheid:</strong> ${windSpeed} km/u</p>
        <p><strong>Windrichting:</strong> ${windDirectionStr} (${windDeg}°)</p>
        <p><strong>Windtype:</strong> ${windEffect}</p>
        <p><strong>Afstand:</strong> ${routeDistanceKm.toFixed(2)} km</p>
        <p><strong>Geschatte energieverbruik:</strong> ${energyUsed} Wh</p>
      `;
    });
}

function getBearing(start, end) {
  const toRad = deg => deg * Math.PI / 180;
  const y = Math.sin(toRad(end[1] - start[1])) * Math.cos(toRad(end[0]));
  const x = Math.cos(toRad(start[0])) * Math.sin(toRad(end[0])) -
            Math.sin(toRad(start[0])) * Math.cos(toRad(end[0])) * Math.cos(toRad(end[1] - start[1]));
  const brng = Math.atan2(y, x) * 180 / Math.PI;
  return (brng + 360) % 360;
}

function getWindEffect(routeAngle, windDeg) {
  const angleDiff = Math.abs(routeAngle - windDeg);
  const diff = angleDiff > 180 ? 360 - angleDiff : angleDiff;

  if (diff < 45) return 'tegenwind';
  if (diff > 135) return 'meewind';
  return 'zijwind';
}

function degToCompass(num) {
  const directions = ['noord', 'noordoost', 'oost', 'zuidoost', 'zuid', 'zuidwest', 'west', 'noordwest'];
  const index = Math.round(num / 45) % 8;
  return directions[index];
}

function resetForm() {
  document.getElementById('startAddress').value = '';
  document.getElementById('endAddress').value = '';
  document.getElementById('result').innerHTML = '';
  if (routeControl) {
    map.removeControl(routeControl);
    routeControl = null;
  }
}
