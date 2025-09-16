@extends('layout')

@section('title', 'PTD Master Setup')

@push('styles')
<!-- Page-specific CSS libs -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">
<style>
    /* ====== THEME VARS (Light default) ====== */
    :root {
      --bg: #f7f7fb; --card-bg: #ffffff; --text: #1f2328; --muted: #6c757d; --border: #e5e7eb; --accent: #0d6efd;
      --thead-bg: #343a40; --thead-text: #ffffff; --table-row: #f9f9f9; --popup-bg: #000000; --popup-text: #ffffff;
  }
  body { background: var(--bg); color: var(--text); }
  .page-title { text-align: center; margin: 24px 0; font-weight: 600; color: var(--text); }
  .card { border: 0; border-radius: .75rem; box-shadow: 0 8px 18px rgba(22, 28, 45, .08); background: var(--card-bg); }
  .form-group label { margin-bottom: .25rem; }
  #map { height: 70vh; min-height: 520px; width: 100%; border-radius: .5rem; }

  /* Leaflet tweaks */
  .leaflet-tooltip.marker-label { background: none; border: none; box-shadow: none; padding: 0; font-size: 11px; color: #fff; text-shadow: 1px 1px 2px rgba(0,0,0,.6); }
  .leaflet-popup { width: 550px !important; }
  .leaflet-popup-content-wrapper { background-color: var(--popup-bg); color: var(--popup-text); }
  .leaflet-popup-content { width: 100%; }

  /* Select2 */
  .select2-container { width: 100% !important; }
  .select2-container--default .select2-selection--single { background-color: var(--card-bg); border-color: var(--border); }
  .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text); }
  .select2-dropdown { background: var(--card-bg); color: var(--text); border-color: var(--border); }
  .select2-results__option--highlighted { background: var(--accent) !important; color: #fff !important; }

  .modal-content { background: var(--card-bg); color: var(--text); border: 1px solid var(--border); }
  .swal2-popup { background: var(--card-bg) !important; color: var(--text) !important; }

  .tableFixHead thead th { position: sticky; top: 0; z-index: 2; background: var(--thead-bg); color: var(--thead-text); }

  #rs > *:first-child { margin-top: 0 !important; }
  #rs .row:first-child { margin-top: 0 !important; }
  #rs .tableFixHead { margin-top: .25rem; }

  body.dark-mode {
      --bg: #0f1419; --card-bg: #111827; --text: #e5e7eb; --muted: #9ca3af; --border: #374151; --accent: #60a5fa;
      --thead-bg: #1f2937; --thead-text: #e5e7eb; --table-row: #0b0f14; --popup-bg: #0b0f14; --popup-text: #e5e7eb;
  }
  body.dark-mode .btn-primary { background-color: var(--accent); border-color: var(--accent); }
  body.dark-mode .btn-success { background-color: #10b981; border-color: #10b981; }
  body.dark-mode .table, body.dark-mode .table td, body.dark-mode .table th { border-color: var(--border) !important; }
  body.dark-mode .leaflet-tile { filter: invert(.92) hue-rotate(180deg) saturate(.7) brightness(.9); }

  /* AJAX dark-mode fixes (biar teks terbaca) */
  body.dark-mode .table, body.dark-mode .table td, body.dark-mode .table th { color: var(--text) !important; background-color: transparent; }
  body.dark-mode #sortable tr { color: var(--text) !important; background-color: var(--table-row); }
  body.dark-mode .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(255,255,255,0.03); }
  body.dark-mode .table-striped tbody tr:nth-of-type(even) { background-color: rgba(255,255,255,0.015); }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h2 class="page-title">PTD Master Setup</h2>
    <div class="form-group mb-2 container-fluid" style="width: 100%">
        <label for="mySelect" class="font-weight-semibold">PTD Name</label>
        <select id="mySelect" class="form-control"></select>
    </div>
    <div class="row">

        <hr class="my-2">
        <!-- Map -->
        <div class="col-lg-7 mb-3">
            <div class="card">
                <div class="card-body p-2">
                    <div id="map"></div>
                </div>
            </div>
        </div>

        <!-- Controls / Results -->
        <div class="col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-body p-2">

                    <div id="rs"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Point Picker -->
    <div class="mb-2">
        <div class="d-flex flex-wrap" style="gap:.5rem;">
            <input id="RoadSegmentNew" class="form-control" style="max-width:180px" placeholder="RoadSegment">
            <input id="lat" class="form-control" style="max-width:180px" placeholder="Latitude">
            <input id="lng" class="form-control" style="max-width:180px" placeholder="Longitude">
            <!-- <button id="useLocation" type="button" class="btn btn-sm btn-primary">Use my location</button> -->
            <button id="newPoint" type="button" class="btn btn-sm btn-primary">AddPoint</button>
            <button id="clearPoint" type="button" class="btn btn-sm btn-outline-secondary">Clear</button>
        </div>
        <small class="text-muted">Click the map to drop/move marker, drag it to refine. Editing the fields will move the marker.</small>
    </div>


</div>
@endsection

@push('scripts')
<!-- Page-specific JS libs -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-editable@1.2.0/src/Leaflet.Editable.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
        // ====== Global Vars ======
        const maptilerKey = 'DOIetENrqrAuzIMN1sha';
        let carMarkers = [];
        let routeMap = {};
        let radiusCircle = null;
        let ptd_id = 0;
        let max_route_number = 0;
        let dataSaveAble = [];
        let defaultPtd = [];

        // ====== Map Init ======
        const map = L.map('map', {
            center: [-2.22422, 115.493],
            zoom: 12,
            doubleClickZoom: false,
            editable: true
        });

        const baseTileLayer = L.tileLayer('/map/{z}/{x}/{y}.jpg', {
            attribution: '&copy; BukitMakmur & contributors',
            tileSize: 512,
            zoomOffset: -1,
            opacity: 0.8
        }).addTo(map);


        // ====== Point Picker (AJAX-safe) ======
        const pickLayer = L.layerGroup().addTo(map); // survives route reloads
        let pickedMarker = null;

        // helpers always re-select DOM (no stale refs)
        function updatePickInputs(lat, lng) {
          const $lat = $('#lat'), $lng = $('#lng');
          if ($lat.length) $lat.val(Number(lat).toFixed(6));
          if ($lng.length) $lng.val(Number(lng).toFixed(6));
      }

      function setPickedMarker(lat, lng, opts) {
          const pan = opts && opts.pan === false ? false : true;
          const latNum = Number(lat), lngNum = Number(lng);
          if (!Number.isFinite(latNum) || !Number.isFinite(lngNum)) return;

          if (!pickedMarker) {
            pickedMarker = L.marker([latNum, lngNum], { draggable: true }).addTo(pickLayer);
            pickedMarker.on('dragend', function () {
              const p = pickedMarker.getLatLng();
              updatePickInputs(p.lat, p.lng);
          });
        } else {
            pickedMarker.setLatLng([latNum, lngNum]);
        }
        updatePickInputs(latNum, lngNum);
        if (pan) map.panTo([latNum, lngNum]);
    }

        // Map click places/moves the marker
        map.on('click', function (e) {
          setPickedMarker(e.latlng.lat, e.latlng.lng);
      });

        // Delegated handlers so they work for AJAX-inserted UI
        function tryMoveFromInputs() {
          const lat = parseFloat($('#lat').val());
          const lng = parseFloat($('#lng').val());
          if (Number.isFinite(lat) && Number.isFinite(lng)) setPickedMarker(lat, lng, { pan: false });
      }

      $(document).on('input change blur keyup', '#lat,#lng', tryMoveFromInputs);

      $(document).on('click', '#useLocation', function () {
          if (!navigator.geolocation) return alert('Geolocation not supported.');
          navigator.geolocation.getCurrentPosition(
            function (pos) {
              const { latitude, longitude } = pos.coords;
              setPickedMarker(latitude, longitude);
              map.setView([latitude, longitude], Math.max(map.getZoom(), 15));
          },
          function (err) { alert('Could not get location: ' + err.message); },
          { enableHighAccuracy: true, timeout: 10000 }
          );
      });

      $(document).on('click', '#clearPoint', function () {
          pickLayer.clearLayers();
          pickedMarker = null;
          $('#lat,#lng').val('');
      });





      const imageBounds = [[-4.5, 106.94], [7.7, 120.5]];
      L.imageOverlay(
        'https://www.researchgate.net/publication/365362464/figure/fig1/AS%3A11431281097599366%401668656616423/Kalimantan-map-showing-its-five-provinces-and-relationship-to-Malaysian-Borneo.png',
        imageBounds,
        { opacity: 0.0, interactive: false }
        ).addTo(map);

        // Layer contoh dari table (tetap)
        const markerLayer = L.layerGroup().addTo(map);
        function loadTableMarkers() {
            markerLayer.clearLayers();
            $('#sortable tr').each(function () {
                const cells = $(this).find('td');
                if (cells.length >= 4) {
                    const roadSegment = $(cells[0]).text().trim();
                    const routeNumber = parseInt($(cells[1]).text().trim());
                    const longitude = parseFloat($(cells[2]).text().trim());
                    const latitude  = parseFloat($(cells[3]).text().trim());
                    if (!isNaN(latitude) && !isNaN(longitude)) {
                        const marker = L.marker([latitude, longitude])
                        .bindPopup(`<strong>${roadSegment}</strong><br>Route #${routeNumber}`);
                        markerLayer.addLayer(marker);
                    }
                }
            });
            console.log("Updated markers:", markerLayer.getLayers().length);
        }

        const distinctColors = ['blue', 'orange', 'purple', 'violet', 'grey', 'black', 'yellow', 'pink', 'brown'];
        function getMarkerIconUrl(color) {
            return `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${color}.png`;
        }

        // Load pertama
        loadMarkersForRoute(ptd_id, null);

        // PTD Select2
        $.getJSON('/api/ptds', function (data) {
            data.forEach(item => $('#mySelect').append(new Option(item.text, item.id)));
            $('#mySelect').select2({ placeholder: 'Select a route' });
        });

        $('#mySelect').on('select2:select', function () {
            const selectedRouteId = [];
            selectedRouteId.push($(this).val() || [0]);
            loadMarkersForRoute(selectedRouteId.join(','));

            let ptd_id_selected = '';
            if (selectedRouteId == 0) {
                const allOptionValues = Array.from(this.options).map(option => option.value);
                ptd_id_selected = allOptionValues.join(',');
            } else {
                ptd_id_selected = selectedRouteId;
            }
            GetRoadSegmentPTDs(ptd_id_selected);
        });

        function GetRoadSegmentPTDs(ptd_id) {
            $.ajax({
                url: '/api/getRoadSegmentPTDs/' + ptd_id,
                type: 'GET',
                success: function (r) {
                    $('#rs').html(r);

                        // Make Leaflet recalc sizes after DOM changes
                        setTimeout(function(){ map.invalidateSize(true); }, 0);

                        // If a point is already picked, reflect it in the newly injected inputs
                        if (pickedMarker) {
                            const p = pickedMarker.getLatLng();
                            updatePickInputs(p.lat, p.lng);
                        }

                        const $scroller = $('#rs').find('.table-scroll');
                        if ($scroller.length) $scroller.scrollTop(0);
                    }
                });
        }

        const initialVal = $('#mySelect').val();
        const initialText = $('#mySelect option:selected').text();
        if (initialVal && initialText) {
            $('#mySelect').append(new Option(initialText, initialVal, true, true)).trigger('change');
        }

        function loadMarkersForRoute(routeId, data) {
            map.eachLayer(layer => {
                if (layer === baseTileLayer) return;
                if (layer === pickLayer) return;
                if (pickLayer.hasLayer && pickLayer.hasLayer(layer)) return;
                if ( layer instanceof L.Marker || layer instanceof L.Polyline || layer instanceof L.Circle || layer instanceof L.CircleMarker || layer instanceof L.LayerGroup) {
                    map.removeLayer(layer);
                }
            });

            baseTileLayer.addTo(map);

            if (!data) {
                $.getJSON(`/api/markersDB/${routeId}`, function (response) {
                    loadMarkersForRoute(routeId, response);
                });
                return;
            }

            carMarkers = [];
            routeMap = {};
            radiusCircle = null;

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
    dataSaveAble = sorted;

    let totalLat = 0, totalLng = 0;

    // === Build latlng array for editable polyline ===
    const latlngsAll = sorted.map(p => [
        parseFloat(p.Latitude),
        parseFloat(p.Longitude)
    ]);

    // === Keep segment statuses aligned to edges ===
    const segStatus = sorted.slice(1).map(p => (p.Status || '').toLowerCase());

    // LayerGroup for colored segments
    const segLayer = L.layerGroup().addTo(map);

    function redrawSegments(latlngs) {
        segLayer.clearLayers();
        for (let i = 0; i < latlngs.length - 1; i++) {
            const a = latlngs[i];
            const b = latlngs[i + 1];

            // white halo
            L.polyline([a, b], { color: 'white', weight: 5, opacity: 1, lineJoin: 'round' })
              .addTo(segLayer);

            // segment color
            const color = segStatus[i] === 'good' ? 'green' : 'red';
            L.polyline([a, b], { color, weight: 4, opacity: 0.8 })
              .addTo(segLayer)
              .bindPopup("Loading...");
        }
    }

    // Editable control polyline (invisible but thick)
    const routeEdit = L.polyline(latlngsAll, { color: '#000', weight: 10, opacity: 0.01 }).addTo(map);
    routeEdit.enableEdit();

    // Initial draw
    redrawSegments(routeEdit.getLatLngs());

    // Update on edit
    function onEdited() {
        const ll = routeEdit.getLatLngs();
        latlngsForCar.length = 0;
        ll.forEach(pt => latlngsForCar.push([pt.lat, pt.lng]));
        redrawSegments(ll);
    }

    routeEdit.on('editable:vertex:drag', onEdited);
    routeEdit.on('editable:vertex:new', onEdited);
    routeEdit.on('editable:vertex:deleted', onEdited);
    routeEdit.on('editable:dragend', onEdited);

    // === CTRL + click to insert curve control point ===
    map.on('click', function (e) {
        if (e.originalEvent.ctrlKey) {
            const ll = routeEdit.getLatLngs();
            let minDist = Infinity;
            let insertAt = null;

            for (let i = 0; i < ll.length - 1; i++) {
                const segDist = L.LineUtil.pointToSegmentDistance(
                    map.latLngToLayerPoint(e.latlng),
                    map.latLngToLayerPoint(ll[i]),
                    map.latLngToLayerPoint(ll[i + 1])
                );
                if (segDist < minDist) {
                    minDist = segDist;
                    insertAt = i + 1;
                }
            }

            if (insertAt !== null) {
                ll.splice(insertAt, 0, e.latlng);
                routeEdit.setLatLngs(ll);
                onEdited();
            }
        }
    });

    // === Markers at points ===
    sorted.forEach((point, i) => {
        const lat = parseFloat(point.Latitude);
        const lng = parseFloat(point.Longitude);
        const popupText = `<b>${point.RoadSegment}</b><br>Route #${point.RouteNumber}<br>Status: ${point.Status || 'unknown'}`;

        totalLat += lat; totalLng += lng;

        if (i === 0) {
            const icon = new L.Icon({
                iconUrl: getMarkerIconUrl('grey'),
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41], iconAnchor: [12, 41],
                popupAnchor: [1, -34], shadowSize: [41, 41]
            });
            L.marker([lat, lng], { icon })
             .addTo(map).bindPopup(popupText)
             .bindTooltip(`${point.RoadSegment}`, { permanent: true, direction: 'top', offset: [0, -10], className: 'marker-label' });
        } else if (i === sorted.length - 1) {
            const icon = new L.Icon({
                iconUrl: getMarkerIconUrl('green'),
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41], iconAnchor: [12, 41],
                popupAnchor: [1, -34], shadowSize: [41, 41]
            });
            L.marker([lat, lng], { icon })
             .addTo(map).bindPopup(popupText)
             .bindTooltip(`${point.RoadSegment}`, { permanent: true, direction: 'top', offset: [0, -10], className: 'marker-label', opacity: 0.5 });
        } else {
            L.circleMarker([lat, lng], { radius: 2, color: markerColor, fillColor: markerColor, fillOpacity: 1 })
             .addTo(map).bindPopup(popupText)
             .bindTooltip(`${point.RoadSegment}`, { permanent: true, direction: 'top', offset: [0, -10], className: 'marker-label', opacity: 0.5 });
        }
    });

    const count = sorted.length;
    const centerLatLng = [totalLat / count, totalLng / count];
    map.setView(centerLatLng, (routeId > 0 ? 15 : 13));
});

        }

        $('#newPoint').on('click',function() {
            let newSegment = $('#RoadSegmentNew').val();
            let newlat = $('#lat').val();
            let newlong = $('#lng').val();
            
            if (newSegment && newlat && newlong) {
                // alert('f');
                $('#roadSegmentList tbody').append(`
                    <tr style="text-align:center">
                    <td>${newSegment}</td>
                    <td>0</td>
                    <td>${newlong}</td>
                    <td>${newlat}</td>
                    <td><i class="fa-solid fa-trash delete-row" style="color:red; cursor:pointer;"></i></td>
                    </tr>`
                    );

                loadTableMarkers()
            }
        })


    </script>
    @endpush
