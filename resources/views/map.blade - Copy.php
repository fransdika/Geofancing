<!DOCTYPE html>
<html>
<head>
    <title>Laravel 8 Leaflet Map</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        #map {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body>

    <h2 style="text-align:center;">Leaflet Map in Laravel 8</h2>
    <div id="map"></div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

   <!--  <script>
        $(document).ready(function () {
            // Initialize map
            var map = L.map('map').setView([51.505, -0.09], 3);

            // Load tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Load markers via AJAX
            $.getJSON('/api/markers', function (data) {
                data.forEach(function (point) {
                    L.marker([point.Latitude, point.Longitude])
                        .addTo(map)
                        .bindPopup(point.RoadSegment);
                });
            });
        });
    </script> -->


 <style>
    .leaflet-tooltip.marker-label {
        background: none;
        border: none;
        box-shadow: none;
        padding: 0;
        font-size: 11px;
        font-weight: normal;
        color: black;
        text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.8); /* White glow */
    }
</style>

<script>
$(document).ready(function () {
    var map = L.map('map').setView([-2.22422, 115.493], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const distinctColors = ['blue', 'orange', 'purple', 'violet', 'grey', 'black', 'yellow', 'pink', 'brown'];

    function getMarkerIconUrl(color) {
        return `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${color}.png`;
    }

    $.getJSON('/api/markers', function (data) {
        if (!Array.isArray(data)) {
            console.error("Invalid data format");
            return;
        }

        const groups = {};
        data.flat().forEach(point => {
            const id = point["﻿ID"] || point.ID;
            if (!groups[id]) groups[id] = [];
            groups[id].push(point);
        });

        Object.keys(groups).forEach((id, idx) => {
            const sorted = groups[id].sort((a, b) => Number(a.RouteNumber) - Number(b.RouteNumber));
            const markerColor = distinctColors[idx % distinctColors.length];

            // Draw polylines between points with color based on status
            for (let i = 0; i < sorted.length - 1; i++) {
                const from = sorted[i];
                const to = sorted[i + 1];

                const latlngs = [
                    [parseFloat(from.Latitude), parseFloat(from.Longitude)],
                    [parseFloat(to.Latitude), parseFloat(to.Longitude)]
                ];

                const segmentColor = (to.status || '').toLowerCase() === 'good' ? 'green' : 'red';
                const segmentPopup = `
                    <b>ID:</b> ${id}<br>
                    <b>From:</b> ${from.RoadSegment} (#${from.RouteNumber})<br>
                    <b>To:</b> ${to.RoadSegment} (#${to.RouteNumber})<br>
                    <b>Status:</b> ${to.status || 'unknown'}
                `;

                L.polyline(latlngs, {
                    color: segmentColor,
                    weight: 4,
                    opacity: 0.8
                }).addTo(map).bindPopup(segmentPopup);
            }

            // Add markers with icon/shape based on position
            sorted.forEach((point, i) => {
                const lat = parseFloat(point.Latitude);
                const lng = parseFloat(point.Longitude);

                let popupText = `<b>${point.RoadSegment}</b><br>Route #${point.RouteNumber}<br>Status: ${point.status || 'unknown'}`;

                // First point (white marker)
                if (i === 0) {
                    const icon = new L.Icon({
                        iconUrl: getMarkerIconUrl('grey'),
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });

                    L.marker([lat, lng], { icon })
                        .addTo(map)
                        .bindPopup(popupText)
                        .bindTooltip(`${point.RoadSegment}`, {
                            permanent: true,
                            direction: 'top',
                            offset: [0, -10],
                            className: 'marker-label'
                        });

                // Last point (green marker)
                } else if (i === sorted.length - 1) {
                    const icon = new L.Icon({
                        iconUrl: getMarkerIconUrl('green'),
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });

                    L.marker([lat, lng], { icon })
                        .addTo(map)
                        .bindPopup(popupText)
                        .bindTooltip(`${point.RoadSegment}`, {
                            permanent: true,
                            direction: 'top',
                            offset: [0, -10],
                            className: 'marker-label',
                            opacity:0.5
                        });

                // Middle points as circle markers
                } else {
                    L.circleMarker([lat, lng], {
                        radius: 6,
                        color: markerColor,
                        fillColor: markerColor,
                        fillOpacity: 1
                    }).addTo(map)
                      .bindPopup(popupText)
                      .bindTooltip(`${point.RoadSegment}`, {
                          permanent: true,
                          direction: 'top',
                          offset: [0, -10],
                          className: 'marker-label',
                          opacity:0.5
                      });
                }
            });
        });
    });
 // Add car icon marker
    $.getJSON('/api/units', function (cars) {
        const carIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/296/296216.png',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        });

        cars.forEach(car => {
            const lat = parseFloat(car.LNG); // Note: Lng is negative
            const lng = parseFloat(car.LAT);

            L.marker([lat, lng], { icon: carIcon })
                .addTo(map)
                .bindPopup("🚗 Car is here");
        });
    });


});
</script>













</body>
</html>
