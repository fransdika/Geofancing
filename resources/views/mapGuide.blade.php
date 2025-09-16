<!DOCTYPE html>
<html>
<head>
    <title>Routing Real Map</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <style>#map { height: 600px; }</style>
</head>
<body>
<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

<script>
    const map = L.map('map').setView([-7.2575, 112.7521], 14); // Surabaya

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Rute realistis pakai OSRM
    L.Routing.control({
        waypoints: [
            L.latLng(-2.23859524725229,115.527862043558), // titik A
            L.latLng(-2.23826753537849,115.532334947122)  // titik B
        ],
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1'
        }),
        show: true,
        routeWhileDragging: true
    }).addTo(map);
</script>
</body>
</html>
