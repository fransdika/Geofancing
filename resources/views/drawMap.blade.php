<!DOCTYPE html>
<html>
<head>
  <title>Leaflet Draw to JSON</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <!-- Leaflet Draw CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />

  <style>
    #map {
      height: 500px;
    }
    #downloadBtn {
      margin: 10px;
      padding: 10px 20px;
      background-color: #2e8b57;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    #downloadBtn:hover {
      background-color: #256f48;
    }
  </style>
</head>
<body>

  <button id="downloadBtn">Download JSON</button>
  <button id="sendBtn">Send to Server</button>
  <div id="map"></div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <!-- Leaflet Draw JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

  <script>

    document.getElementById('sendBtn').addEventListener('click', function () {
      var data = drawnItems.toGeoJSON();

      fetch('/api/ptds', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Needed for Laravel
      },
      body: JSON.stringify({ geojson: drawnItems.toGeoJSON() })
    })
      .then(res => res.json())
      .then(response => {
        alert("Data saved: " + response.message);
      })
      .catch(error => {
        alert("Error saving: " + error);
      });

    });

    
    var map = L.map('map').setView([-2.2, 115.4], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Feature group to store drawn items
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Add draw controls
    var drawControl = new L.Control.Draw({
      edit: {
        featureGroup: drawnItems
      },
      draw: {
        polygon: true,
        polyline: true,
        rectangle: true,
        circle: false,
        marker: true
      }
    });
    map.addControl(drawControl);

    // Add new layers to group on draw
    map.on(L.Draw.Event.CREATED, function (e) {
      var layer = e.layer;
      drawnItems.addLayer(layer);
    });

    // Download button
    document.getElementById('downloadBtn').addEventListener('click', function () {
      var data = drawnItems.toGeoJSON();
      var json = JSON.stringify(data, null, 2);
      var blob = new Blob([json], { type: "application/json" });
      var url = URL.createObjectURL(blob);

      var a = document.createElement('a');
      a.href = url;
      a.download = 'map_data.json';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });
  </script>

</body>
</html>
