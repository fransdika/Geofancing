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

    <div id="popupToggleContainer" style="position: absolute; top: 10px; left: 10px; z-index: 1000; background: white; padding: 5px; border-radius: 5px;">
    <label>
        <input type="checkbox" id="togglePopups" checked>
        Show Car Popups
    </label>
</div>

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

    <!-- Checkbox for toggling popups -->


  <style>
    .leaflet-tooltip.marker-label {
        background: none;
        border: none;
        box-shadow: none;
        padding: 0;
        font-size: 11px;
        font-weight: normal;
        color: black;
        text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.8);
    }
</style>

<script>
    const carMarkers = [];

    $(document).ready(function () {
        var map = L.map('map').setView([-2.22422, 115.493], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var imageBounds = [
            [-4.5, 106.94],
            [7.7, 120.5]
        ];
        var overlay = L.imageOverlay('https://www.researchgate.net/publication/365362464/figure/fig1/AS%3A11431281097599366%401668656616423/Kalimantan-map-showing-its-five-provinces-and-relationship-to-Malaysian-Borneo.png', imageBounds, {
            opacity: 0.0,
            interactive: false
        }).addTo(map);

        const distinctColors = ['blue', 'orange', 'purple', 'violet', 'grey', 'black', 'yellow', 'pink', 'brown'];

        function getMarkerIconUrl(color) {
            return `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${color}.png`;
        }

        let routeMap = {};

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
                const latlngsForCar = [];

                for (let i = 0; i < sorted.length - 1; i++) {
                    const from = sorted[i];
                    const to = sorted[i + 1];
                    const latlngs = [
                        [parseFloat(from.Latitude), parseFloat(from.Longitude)],
                        [parseFloat(to.Latitude), parseFloat(to.Longitude)]
                    ];
                    latlngsForCar.push(latlngs[0]);
                    if (i === sorted.length - 2) latlngsForCar.push(latlngs[1]);

                    const segmentColor = (to.status || '').toLowerCase() === 'good' ? 'green' : 'red';
                    const segmentPopup = `
                        <p align="center"><b>${from.ptd}</b></p><hr>
                        <b>From:</b> ${from.RoadSegment} (#${from.RouteNumber})<br>
                        <b>To:</b> ${to.RoadSegment} (#${to.RouteNumber})<br>
                        <b>Status:</b> ${to.status || 'unknown'}
                        <hr>
                        <img src="https://img.icons8.com/color/48/fleet.png" width="30" />
                        <img src="https://img.icons8.com/ios-glyphs/30/road.png" width="30" />
                        <img src="https://img.icons8.com/dusk/64/speed.png" width="30" />
                    `;

                    L.polyline(latlngs, {
                        color: segmentColor,
                        weight: 4,
                        opacity: 0.8
                    }).addTo(map).bindPopup(segmentPopup);
                }

                routeMap[id] = latlngsForCar;

                sorted.forEach((point, i) => {
                    const lat = parseFloat(point.Latitude);
                    const lng = parseFloat(point.Longitude);

                    let popupText = `<b>${point.RoadSegment}</b><br>Route #${point.RouteNumber}<br>Status: ${point.status || 'unknown'}`;

                    if (i === 0) {
                        const icon = new L.Icon({
                            iconUrl: getMarkerIconUrl('grey'),
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        });

                        const staticMarker = L.marker([lat, lng], { icon });
                        staticMarker.addTo(map)
                            .bindPopup(popupText)
                            .bindTooltip(`${point.RoadSegment}`, {
                                permanent: true,
                                direction: 'top',
                                offset: [0, -10],
                                className: 'marker-label'
                            });
                        point._staticMarker = staticMarker;
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
                                opacity: 0.5
                            });

                    } else {
                        L.circleMarker([lat, lng], {
                            radius: 2,
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
                                opacity: 0.5
                            });
                    }
                });
            });

            // Animate cars
            $.getJSON('/api/units', function (cars) {
                const carIcon = L.icon({
                    iconUrl: 'https://img.icons8.com/color/48/dump-truck.png',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                    popupAnchor: [0, -16]
                });

                cars.forEach((car, idx) => {
                    const carId = Object.keys(routeMap)[idx];
                    const route = routeMap[carId];
                    if (!route || route.length === 0) return;

                    const CarPopup = `
                        <p align="center"><b>${car.Unit}</b></p><hr>
                        <b>Speed:</b> 40 KMpH<br>
                        <b>Status:</b> Good
                    `;

                    const carMarker = L.marker(route[0], {
                        icon: carIcon
                    }).addTo(map)
                        .bindPopup(CarPopup, {
                            autoClose: false,
                            closeOnClick: false,
                            closeButton: false
                        }).openPopup();

                    carMarkers.push(carMarker);

                    const staticMarker = Object.values(groups[carId])[0]._staticMarker;
                    if (staticMarker) map.removeLayer(staticMarker);

                    let i = 0;
                    setInterval(() => {
                        if (route.length === 0) return;
                        i = (i + 1) % route.length;
                        carMarker.setLatLng(route[i]);
                    }, 2000);
                });
            });
        });

        // Load shapes if any
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);
        fetch('/storage/map_data.json')
            .then(res => res.json())
            .then(data => {
                L.geoJSON(data, {
                    onEachFeature: function (feature, layer) {
                        layer.bindPopup("Saved Shape");
                    }
                }).addTo(drawnItems);
            })
            .catch(err => {
                console.warn("No saved GeoJSON found or error loading it:", err);
            });
    });

    // Checkbox control
    $(document).on('change', '#togglePopups', function () {
        const show = $(this).is(':checked');
        carMarkers.forEach(marker => {
            if (show) {
                marker.openPopup();
            } else {
                marker.closePopup();
            }
        });
    });
</script>














</body>
</html>
