@extends('layout')

@section('title', 'PTD Plan Setup')

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
    <h2 class="page-title">PTD Plan Setup</h2>
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


<!--     <hr>
    <h2>MAKE PLAN PTD BY ACTUAL</h2>
    <hr>

    <div class="row">

        <hr class="my-2">

        <div class="col-lg-7 mb-3">
            <div class="card">
                <div class="card-body p-2">
                    <div id="rsActual"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-3">
            <div class="card h-100"> 
                <div class="card-body p-2">

                    <div id="mapActual"></div>
                </div>
            </div>
        </div>
    </div>
 -->
    <!-- <div id="ct-ptd-actual"></div> -->
    

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
/* ===============================
   ===  GLOBALS & MAP SETUP    ===
   =============================== */
const maptilerKey = 'DOIetENrqrAuzIMN1sha';
let carMarkers = [];
let routeMap = {};
let radiusCircle = null;
let ptd_id = 0;
let max_route_number = 0;
let dataSaveAble = [];
let defaultPtd = [];

const map = L.map('map', {
  center: [-2.22422, 115.493],
  zoom: 13,
  doubleClickZoom: false
});

const baseTileLayer = L.tileLayer('/map/{z}/{x}/{y}.jpg', {
  attribution: '&copy; BukitMakmur & contributors',
  tileSize: 512,
  zoomOffset: -1,
  opacity: 0.8
}).addTo(map);



// const mapActual = L.map('mapActual', {
//   center: [-2.22422, 115.493],
//   zoom: 13,
//   doubleClickZoom: false
// });

// const baseTileLayerActual = L.tileLayer('/map/{z}/{x}/{y}.jpg', {
//   attribution: '&copy; BukitMakmur & contributors',
//   tileSize: 512,
//   zoomOffset: -1,
//   opacity: 0.8
// }).addTo(mapActual);


// requestAnimationFrame(() => {
//       map.invalidateSize();
//       mapActual.invalidateSize();
//     });
//     $(window).on('load', function(){
//       map.invalidateSize();
//       mapActual.invalidateSize();
//     });










// ====== Point Picker (AJAX-safe) ======
const pickLayer = L.layerGroup().addTo(map); // survives route reloads
let pickedMarker = null;

function updatePickInputs(lat, lng) {
  const $lat = $('#lat'), $lng = $('#lng');
  if ($lat.length) $lat.val(Number(lat).toFixed(8));
  if ($lng.length) $lng.val(Number(lng).toFixed(8));
}

function setPickedMarker(lat, lng, opts) {
  const pan = !(opts && opts.pan === false);
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

map.on('click', function (e) {
  setPickedMarker(e.latlng.lat, e.latlng.lng);
});

function tryMoveFromInputs() {
  const lat = parseFloat($('#lat').val());
  const lng = parseFloat($('#lng').val());
  if (Number.isFinite(lat) && Number.isFinite(lng)) setPickedMarker(lat, lng, { pan: false });
}
$(document).on('input change blur keyup', '#lat,#lng', tryMoveFromInputs);

$(document).on('click', '#clearPoint', function () {
  pickLayer.clearLayers();
  pickedMarker = null;
  $('#lat,#lng').val('');
});

// Optional geolocation button (kept in case you enable the button later)
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

// ====== (Optional) Reference overlay (transparent) ======
const imageBounds = [[-4.5, 106.94], [7.7, 120.5]];
L.imageOverlay(
  'https://www.researchgate.net/publication/365362464/figure/fig1/AS%3A11431281097599366%401668656616423/Kalimantan-map-showing-its-five-provinces-and-relationship-to-Malaysian-Borneo.png',
  imageBounds,
  { opacity: 0.0, interactive: false }
).addTo(map);

// ====== Layer from table ======
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
  computeTableDistances(); // NEW: show table total distance
}


// ====== Layer from table ======
// const markerLayerActual = L.layerGroup().addTo(mapActual);
// function loadTableMarkerActual() {
//   markerLayerActual.clearLayers();
//   $('#sortable tr').each(function () {
//     const cells = $(this).find('td');
//     if (cells.length >= 4) {
//       const roadSegment = $(cells[0]).text().trim();
//       const routeNumber = parseInt($(cells[1]).text().trim());
//       const longitude = parseFloat($(cells[2]).text().trim());
//       const latitude  = parseFloat($(cells[3]).text().trim());
//       if (!isNaN(latitude) && !isNaN(longitude)) {
//         const marker = L.marker([latitude, longitude])
//           .bindPopup(`<strong>${roadSegment}</strong><br>Route #${routeNumber}`);
//         markerLayer.addLayer(marker);
//       }
//     }
//   });
//   console.log("Updated markers:", markerLayer.getLayers().length);
//   computeTableDistances(); // NEW: show table total distance
// }


// ====== NEW: table distance calculator ======
function computeTableDistances() {
  const rows = Array.from(document.querySelectorAll('#sortable tr'));
  let total = 0;
  for (let i = 0; i < rows.length - 1; i++) {
    const a = rows[i].querySelectorAll('td');
    const b = rows[i+1].querySelectorAll('td');
    if (a.length >= 4 && b.length >= 4) {
      const lngA = parseFloat(a[2].textContent.trim());
      const latA = parseFloat(a[3].textContent.trim());
      const lngB = parseFloat(b[2].textContent.trim());
      const latB = parseFloat(b[3].textContent.trim());
      if (Number.isFinite(latA) && Number.isFinite(lngA) && Number.isFinite(latB) && Number.isFinite(lngB)) {
        total += L.latLng(latA, lngA).distanceTo(L.latLng(latB, lngB));
      }
    }
  }
  updateDistancePanel(`Table total: ${fmtDistance(total)}`);
}

// ====== Marker color helpers ======
const distinctColors = ['blue', 'orange', 'purple', 'violet', 'grey', 'black', 'yellow', 'pink', 'brown'];
function getMarkerIconUrl(color) {
  return `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${color}.png`;
}

/* ===============================
   ===   NEW DISTANCE HELPERS  ===
   =============================== */
function fmtDistance(m) {
  if (!Number.isFinite(m)) return '-';
  return m >= 1000 ? (m/1000).toFixed(2) + ' km' : Math.round(m) + ' m';
}

const distancePanel = L.control({ position: 'bottomleft' });
distancePanel.onAdd = function () {
  const div = L.DomUtil.create('div', 'leaflet-bar p-2');
  div.style.background = 'var(--card-bg)';
  div.style.color = 'var(--text)';
  div.style.border = '1px solid var(--border)';
  div.style.borderRadius = '6px';
  div.style.boxShadow = '0 4px 10px rgba(0,0,0,.08)';
  div.id = 'distance-panel';
  div.textContent = 'Distance: -';
  return div;
};
distancePanel.addTo(map);
function updateDistancePanel(text) {
  const el = document.getElementById('distance-panel');
  if (el) el.textContent = text;
}

/* ==========================================
   ===  MAIN LOADER (WITH DISTANCE SUM)   ===
   ========================================== */
function loadMarkersForRoute(routeId, data) {
  // Clear overlays but keep base & picker
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
  // console.log(data.length)
  carMarkers = [];
  routeMap = {};
  radiusCircle = null;

  // Group by ID
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

    // NEW: distance accumulator
    let totalMeters = 0;

    for (let i = 0; i < sorted.length - 1; i++) {
      const from = sorted[i];
      const to = sorted[i + 1];

      const fromLL = L.latLng(parseFloat(from.Latitude),  parseFloat(from.Longitude));
      const toLL   = L.latLng(parseFloat(to.Latitude),    parseFloat(to.Longitude));

      const latlngs = [ [fromLL.lat, fromLL.lng], [toLL.lat, toLL.lng] ];

      latlngsForCar.push(latlngs[0]);
      if (i === sorted.length - 2) latlngsForCar.push(latlngs[1]);

      const segmentColor = (to.Status || '').toLowerCase() === 'good' ? 'green' : 'red';

      // NEW: segment distance
      const segMeters = fromLL.distanceTo(toLL);
      totalMeters += segMeters;

      // Update kolom distance on table RoadSegmemt
      $('#roadSegmentList tbody tr').eq(i).find('td').eq(4).text(Number(segMeters).toFixed(2));

      // Draw lines
      L.polyline(latlngs, { color: 'white', weight: 5, opacity: 1, lineJoin: 'round' }).addTo(map);
      L.polyline(latlngs, { color: segmentColor, weight: 4, opacity: 0.8 })
        .addTo(map)
        .bindPopup(
          `<div style="min-width:200px">
             <div><b>${from.RoadSegment}</b> (#${from.RouteNumber}) → 
                  <b>${to.RoadSegment}</b> (#${to.RouteNumber})</div>
             <div>Segment: <b>${fmtDistance(segMeters)}</b></div>
           </div>`
        );
    }

    routeMap[id] = latlngsForCar;

    // Render markers along the route
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

    // NEW: show route total in panel
    updateDistancePanel(`Route ${data.length} total: ${fmtDistance(totalMeters)}`);
    $('#distance').html(`total Distance: ${fmtDistance(totalMeters)}`)

    // Center map to this route
    const count = sorted.length;
    if (count > 0) {
      const centerLatLng = [totalLat / count, totalLng / count];
      map.setView(centerLatLng, (routeId > 0 ? 15 : 13));
    }
  });
}

/* ==========================================
   ===      SELECT2 + CHANGE HANDLER       ===
   ========================================== */

// Populate options then init Select2
$.getJSON('/api/ptds', function (data) {
  $('#mySelect').append(new Option('ALL PTD', 0));
  data.forEach(item => $('#mySelect').append(new Option(item.PTD, item.ID)));
  $('#mySelect').select2({ placeholder: 'Select a route', allowClear: true });

  // Process initial value (if any)
  processSelection();
});

// ❌ OLD HANDLER — keep for reference, REMOVE after verifying new works
// $('#mySelect').on('select2:select', function () {
//   const selectedRouteId = [];
//   selectedRouteId.push($(this).val() || [0]);
//   loadMarkersForRoute(selectedRouteId.join(','));
//   let ptd_id_selected = '';
//   if (selectedRouteId == 0) {
//     const allOptionValues = Array.from(this.options).map(option => option.value);
//     ptd_id_selected = allOptionValues.join(',');
//   } else {
//     ptd_id_selected = selectedRouteId;
//   }
//   GetRoadSegmentPTDs(ptd_id_selected);
// });

// NEW: normalize values to CSV string
function getSelectedIds($el) {
  const v = $el.val(); // null | string | string[]
  if (v == null) return '';
  return Array.isArray(v) ? v.join(',') : String(v);
}

function clearOverlaysKeepBase() {
  map.eachLayer(function (layer) {
    if (layer !== baseTileLayer && layer !== pickLayer) {
      map.removeLayer(layer);
    }
  });
}

function processSelection() {
  const idsCsv = getSelectedIds($('#mySelect')); // "1,2,3" or ""
  let label = $('#mySelect').select2('data')[0].text;

  if (!idsCsv) {
    // No selection: clear overlays & reset distance panel
    clearOverlaysKeepBase();
    updateDistancePanel('Distance: -');
    return;
  }

  // Load routes + table for selected IDs
  loadMarkersForRoute(idsCsv);
  if(idsCsv>0){
    GetRoadSegmentPTDs(idsCsv+'_'+label);
    // GetPTDActual(label);
  }else{
    $('#rs').html('');
    $('#ct-ptd-actual').html('');
  }

  // Fix Leaflet sizing after DOM updates
  setTimeout(function(){ map.invalidateSize(true); }, 0);

  // If a point is already picked, reflect it in inputs
  if (pickedMarker) {
    const p = pickedMarker.getLatLng();
    updatePickInputs(p.lat, p.lng);
  }

  const $scroller = $('#rs').find('.table-scroll');
  if ($scroller.length) $scroller.scrollTop(0);
}

// Use delegated handler so it works even if #mySelect is re-rendered
$(document).on('change', '#mySelect', processSelection);

/* ==========================================
   ===    LOAD RS PANEL (UNCHANGED API)   ===
   ========================================== */
function GetRoadSegmentPTDs(ptd_id) {
  $.ajax({
    url: '/api/getRoadSegmentPTDs/' + ptd_id,
    type: 'GET',
    success: function (r) {
      $('#rs').html(r);

      // Recalc map size after DOM changes
      setTimeout(function(){ map.invalidateSize(true); }, 0);

      // Reflect picked point back into any newly injected inputs
      if (pickedMarker) {
        const p = pickedMarker.getLatLng();
        updatePickInputs(p.lat, p.lng);
      }

      const $scroller = $('#rs').find('.table-scroll');
      if ($scroller.length) $scroller.scrollTop(0);
    }
  });
}

function GetPTDActual(ptd_id) {
  $.ajax({
    url: '/api/getPTDActual/' + ptd_id,
    type: 'GET',
    success: function (r) {
      $('#rsActual').html(r);

      // Recalc map size after DOM changes
      setTimeout(function(){ map.invalidateSize(true); }, 0);

      }
  });
}




/* ==========================================
   ===     INITIAL DRAW / ADD NEW POINT   ===
   ========================================== */
// Optional: initial load with default ptd_id (0) — comment out if not desired
// loadMarkersForRoute(ptd_id, null);

$('#newPoint').on('click', function () {
  const newSegment = $('#RoadSegmentNew').val();
  const newlat = $('#lat').val();
  const newlong = $('#lng').val();

  if (newSegment && newlat && newlong) {
    $('#roadSegmentList tbody').append(`
      <tr style="text-align:center">
        <td>${newSegment}</td>
        <td>0</td>
        <td>${newlong}</td>
        <td>${newlat}</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
        <td>0</td>
        <td><i class="fa-solid fa-trash delete-row" style="color:red; cursor:pointer;"></i></td>
      </tr>
    `);

    loadTableMarkers();     // redraw markers from the table
    computeTableDistances(); // update table distance
  }else{
    alert('please check blank values')
  }
});
</script>
@endpush
