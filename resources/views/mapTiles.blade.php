<!DOCTYPE html>
<html>
<head>
    <title>Custom Tiled Map</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        #map {
            width: 100%;
            height: 90vh;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

    <h3 style="text-align:center;">High-Res Tiled Map</h3>
    <div id="map"></div>

    <script>
    // Optional: set bounds if known
    var bounds = [[0, 0], [1000, 1000]];

    // var map = L.map('map', {
    //     crs: L.CRS.Simple,
    //     minZoom: 0,
    //     maxZoom: 4,
    //     center: [500, 500],
    //     zoom: 2
    // });
    var map = L.map('map').setView([-1.5, 114.5], 13); // Kalimantan center

    // Load tiles
    L.tileLayer('/map/{z}/{x}/{y}.jpg', {
        minZoom: 13,
        maxZoom: 18,
        tileSize: 256,
        noWrap: true,
        attribution: '© Your Map'
    }).addTo(map);
</script>

</body>
</html>
