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
            // var map = L.map('map').setView([-2.22422, 115.493], 13);
            var map = L.map('map', {
                center: [-2.22422, 115.493],
                zoom: 13,
                doubleClickZoom: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var imageBounds = [[-4.5, 106.94], [7.7, 120.5]];
            var overlay = L.imageOverlay('https://www.researchgate.net/publication/365362464/figure/fig1/AS%3A11431281097599366%401668656616423/Kalimantan-map-showing-its-five-provinces-and-relationship-to-Malaysian-Borneo.png', imageBounds, {
                opacity: 0.0,
                interactive: false
            }).addTo(map);

            const distinctColors = ['blue', 'orange', 'purple', 'violet', 'grey', 'black', 'yellow', 'pink', 'brown'];

            function getMarkerIconUrl(color) {
                return `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${color}.png`;
            }
            

            function createCarIcon() {
                return L.icon({
                    iconUrl: 'https://img.icons8.com/color/48/car--v1.png',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                });
            }

            let routeMap = {};
            let radiusCircle = null;
            let  carMarkers = [];

            $.getJSON('/api/markers', function (data) {
                if (!Array.isArray(data)) {
                    console.error("Invalid data format");
                    return;
                }

                const groups = {};
                data.flat().forEach(point => {
                    const id = point["\uFEFFID"] || point.ID;
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

                        const polyline = L.polyline(latlngs, {
                            color: segmentColor,
                            weight: 4,
                            opacity: 0.8
                        }).addTo(map).bindPopup("Loading...");

                        polyline.on('popupopen', function (e) {
                            const popup = e.popup;

                            const fromLat = parseFloat(from.Latitude);
                            const fromLng = parseFloat(from.Longitude);
                    const center = [fromLat, fromLng];  // ✅ Define this first

                    // 1. Show API content
                    $.getJSON(`/api/getUnitSupport/1/${fromLat}/${fromLng}`, function (data) {

                        if (carMarkers.length > 0) {
                            carMarkers.forEach(marker => map.removeLayer(marker));
                            carMarkers = [];
                        }
                        let dataUnitSupport='';
                        data.forEach(equip => {
                            const carIcon = L.icon({
                                iconUrl: 'https://img.icons8.com/color/48/dump-truck.png',
                                iconSize: [32, 32],
                                iconAnchor: [16, 16],
                                popupAnchor: [0, -16]
                            });
                            let popupText = `<b> Unit: ${equip.Equipment}</b><br>Operator ${equip.Operator}<br>Position: ${equip.Position || 'unknown'}`;
                            const marker = L.marker([equip.Latitude, equip.Longitude], {
                                icon: carIcon

                            }).addTo(map)
                            .bindPopup(popupText)
                            .bindTooltip(`${equip.Equipment}`, {
                                permanent: true,
                                direction: 'top',
                                offset: [0, -10],
                                className: 'marker-label'
                            });
                            ;
                            carMarkers.push(marker);
                            dataUnitSupport+=`
                            <tr>
                            <td>${equip.Equipment}</td>
                            <td>${equip.Operator}</td>
                            <td>${equip.Position}</td>
                            <td>${parseFloat(equip.DistanceKm).toFixed(2)}</td>
                            <td><img src="https://img.icons8.com/office/50/send.png" alt="send" width="30"/></td>
                            </tr>
                            `

                        });

                        let unitAv=`No Support Unit nearby, <a href="#">search more</a>`
                        if (carMarkers.length > 0) {
                            unitAv=`<table style="border:1">
                            <tr>
                            <th>Unit</th>
                            <th>Operator</th>
                            <th>Position</th>
                            <th style="text-align:center">DistanceKM</th>
                            <th style="text-align:center">#</th>
                            </tr>
                            `+dataUnitSupport+`
                            
                            </table>`;
                        }

                        const content = `
                        <p align="center"><b>${from.ptd}</b></p><hr>
                        <b>From:</b> ${from.RoadSegment} (#${from.RouteNumber})<br>
                        <b>To:</b> ${to.RoadSegment} (#${to.RouteNumber})<br>
                        <b>Status:</b> ${to.status || 'unknown'}
                        <hr>
                        <img src="https://img.icons8.com/color/48/fleet.png" width="30" />
                        <img src="https://img.icons8.com/ios-glyphs/30/road.png" width="30" />
                        <img src="https://img.icons8.com/dusk/64/speed.png" width="30" />
                        <hr>
                        <p><b>Unit Support Available:</b></p>
                        `+unitAv+`
                        `;
                        popup.setContent(content);
                        popup.update();
                    }).fail(function () {
                        popup.setContent("Failed to load data.");
                        popup.update();
                    });

                    // 2. Remove previous radius image if exists
                    if (radiusCircle) {
                        map.removeLayer(radiusCircle);
                    }

                    // 3. Add new image overlay
                    const radiusInKm = 5;
                    const radiusInDegrees = radiusInKm / 111.32;

                    const bounds = [
                    [center[0] - radiusInDegrees, center[1] - radiusInDegrees],
                    [center[0] + radiusInDegrees, center[1] + radiusInDegrees]
                    ];
                    radiusCircle = L.circle(center, {
                            radius: 1000, // in meters
                            color: 'blue',
                            fillColor: '#30a3ec',
                            fillOpacity: 0.2,
                            interactive: false
                        }).addTo(map);

                    // window.radiusImageOverlay = L.imageOverlay(
                    //     'https://upload.wikimedia.org/wikipedia/commons/thumb/9/99/Simpleicons_Places_map-marker.svg/512px-Simpleicons_Places_map-marker.svg.png',
                    //     bounds,
                    //     {
                    //         opacity: 0.3,
                    //         interactive: false
                    //     }
                    // ).addTo(map);
                });

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

            // const carMarker = L.marker(routeMap[id][0], {
            //     icon: createCarIcon(),
            //     zIndexOffset: 1000
            // }).addTo(map);

            // carMarkers.push({ id, marker: carMarker, route: routeMap[id], index: 0 });
        });
});

    // function moveCars() {
    //     carMarkers.forEach(car => {
    //         if (!car.route || car.route.length < 2) return;

    //         car.index = (car.index + 1) % car.route.length;
    //         car.marker.setLatLng(car.route[car.index]);
    //     });
    // }

    // setInterval(moveCars, 4000);

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


    map.on('dblclick', function () {
        // Remove radius
        if (radiusCircle) {
            map.removeLayer(radiusCircle);
            radiusCircle = null;
        }

        // Remove unit markers
        carMarkers.forEach(marker => map.removeLayer(marker));
        carMarkers = [];
    });
});
</script>














</body>
</html>
