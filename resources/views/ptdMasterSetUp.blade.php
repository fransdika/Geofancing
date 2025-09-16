@extends('layouts.mat_app')

<!-- @section('title', 'PTD Plan Setup') -->

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">
<!-- Select2 (needed for #RoadSegmentPlan) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
  :root{
    --bg:#f7f7fb; --card-bg:#fff; --text:#1f2328; --muted:#6c757d; --border:#e5e7eb; --accent:#0d6efd;
    --thead-bg:#343a40; --thead-text:#fff; --row-h:38px;
  }
  #map{ height:70vh; min-height:520px; width:100%; border-radius:.5rem; }

/* ===== PTD TABLE (4 rows visible) ===== */
.ptd-table-wrap{ border:1px solid var(--border); border-radius:.5rem; overflow:hidden; background:var(--card-bg); }
#ptdTable{ width:100%; margin:0; border-collapse:separate; border-spacing:0; table-layout:fixed; }
#ptdTable thead th{ position:sticky; top:0; z-index:1; background:var(--thead-bg); color:var(--thead-text); padding:.45rem .5rem; font-weight:600; }
#ptdTable tbody{ display:block; max-height:calc(var(--row-h)*4 + 2px); overflow-y:auto; }
#ptdTable thead, #ptdTable tbody tr{ display:table; width:100%; table-layout:fixed; }
#ptdTable td{ padding:.40rem .5rem; vertical-align:middle; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
#ptdTable tbody tr{ cursor:pointer; }
#ptdTable tbody tr:hover{ background:#f6f8fa; }
#ptdTable tbody tr.table-active{ background:var(--accent)!important; color:#fff!important; }
#ptdTable td.col-id, #ptdTable th.col-id{ display:none; }

/* ===== Leaflet small buttons ===== */
.leaflet-bar .ctrl-btn{ display:block; min-width:32px; min-height:32px; text-align:center; line-height:32px; font-weight:700;
  font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; text-decoration:none; background:#fff; color:#111; }
  .leaflet-bar .ctrl-btn:hover{ background:#f3f4f6; }
  .leaflet-bar .ctrl-btn.is-off{ opacity:.6; }

/* Labels */
.leaflet-tooltip.marker-label,
.leaflet-tooltip.route-num-label{
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
  padding: 0 2px !important;
  opacity: 0.5 !important;
  pointer-events: none;
}
.leaflet-tooltip.marker-label{
  color: #fff;
  font: 600 11px/1.2 system-ui,-apple-system,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif;
  text-shadow:
  -1px -1px 0 rgba(0,0,0,.75),
  1px -1px 0 rgba(0,0,0,.75),
  -1px  1px 0 rgba(0,0,0,.75),
  1px  1px 0 rgba(0,0,0,.75);
}
.leaflet-tooltip.route-num-label{
  color: #fff;
  font: 700 10px/1.1 ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
  text-shadow:
  -1px -1px 0 rgba(0,0,0,.85),
  1px -1px 0 rgba(0,0,0,.85),
  -1px  1px 0 rgba(0,0,0,.85),
  1px  1px 0 rgba(0,0,0,.85);
}
.numbers-hidden .leaflet-tooltip.route-num-label{ display:none!important; }
.labels-hidden  .leaflet-tooltip.marker-label  { display:none!important; }

/* Distance chip */
.leaflet-control #distance-panel{
  background:var(--card-bg); color:var(--text); border:1px solid var(--border); border-radius:6px;
  box-shadow:0 4px 10px rgba(0,0,0,.08); padding:6px 8px;
}

/* (optional) dark mode */
body.dark-mode{
  --bg:#0f1419; --card-bg:#111827; --text:#e5e7eb; --muted:#9ca3af; --border:#374151; --accent:#60a5fa;
  --thead-bg:#1f2937; --thead-text:#e5e7eb;
}
body.dark-mode .leaflet-tooltip.marker-label{ background:rgba(0,0,0,.7); }
body.dark-mode .leaflet-tooltip.route-num-label{ background:rgba(0,0,0,.75); }
.select2-container .select2-selection--single { height: 38px; }
.select2-container--default .select2-selection--single .select2-selection__rendered { 
  line-height: 38px; 
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 38px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Master PTD</div>
    <div class="ps-3"> 
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 p-0">
          <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
          <li class="breadcrumb-item active" aria-current="page">Set Up PTD</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="form-group mb-2 container-fluid" style="width:100%">
    <div class="table-responsive">
      <table id="ptdTable" class="table table-sm table-striped table-hover mb-0">
        <thead class="thead-dark">
          <tr>
            <th style="text-align:center" rowspan="2">PTD</th>
            <th style="text-align:center" colspan="3">Plan VS Actual (Latest Most Actual)</th>
            <th rowspan="2" style="text-align:center;">Freq. H</th>
            <th rowspan="2" style="text-align:center;">Freq. H-1</th>
            <th rowspan="2" style="text-align:center;">Ptd Suggestion</th>
          </tr>
          <tr>
            <th style="text-align:center;">Comply %</th>
            <th style="text-align:center;">Missing Route %</th>
            <th style="text-align:center;">Mismatch Route %</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <hr>
    <div class="row">
      <div class="col-lg-7 mb-3">
        <div class="card"><div class="card-body p-2"><div id="map"></div></div></div>
      </div>
      <div class="col-lg-5 mb-3">
        <div class="card"><div class="card-body" style="height: 540px;"><div id="rs"></div></div></div>
      </div>
    </div>

    <!-- Point Picker -->
    <div class="mb-2">
      <div class="d-flex flex-wrap" style="gap:.5rem;">

        <select id="RoadSegmentPlan" class="form-control" style="max-width:180px">
          <option></option>
        </select>
        <input name="WaypointNew" id="WaypointNew" class="form-control" style="max-width:250px" placeholder="WaypointNew">
        <input id="lat" class="form-control" style="max-width:180px" placeholder="Latitude">
        <input id="lng" class="form-control" style="max-width:180px" placeholder="Longitude">
        <button id="newPoint" type="button" class="btn btn-sm btn-primary">AddPoint</button>
      </div>
      <small class="text-muted">Click the map to drop/move marker, drag it to refine. Editing the fields will move the marker.</small>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-editable@1.2.0/src/Leaflet.Editable.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<!-- Select2 (must be after jQuery, before .select2() calls) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
/* ==========================================
   ============  CONFIG & HELPERS  ==========
   ========================================== */
   const API = {
    ptds   : '/api/ptds',
    planned: function(id){ return '/api/markersDB/' + encodeURIComponent(id); },
    actual : function(id){ return '/api/markersActual/' + encodeURIComponent(id); },
    rs     : function(arg){ return '/api/getRoadSegmentPTDs/' + arg; },
    rsp    : '/api/getRoadSegmentPlan'
  };


  const distinctColors = ['blue','orange','purple','violet','grey','black','yellow','pink','brown'];
  const markerIconUrl  = c => `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${c}.png`;
  const fmtDistance    = m => !Number.isFinite(m) ? '-' : (m>=1000 ? (m/1000).toFixed(2)+' km' : Math.round(m)+' m');

  let plannedTotalMetersGlobal = 0;
  let tablePTDClickedId=0;
  let tablePTDClickedabel='';
  let dataSaveAble = [];

// ajax guards
let plannedReq=null, plannedToken=0;
let actualReq =null, actualToken =0;

// toggles
let labelsHidden=false;  // LBL button state
let numbersHidden=false; // 123 button state
let actualVisible=true;  // ACT button state
let isAllMode=false;     // true when "ALL PTD" selected

let lblBtn=null, numBtn=null;

/* ==========================================
   ===============  MAP SETUP  ==============
   ========================================== */
   const map = L.map('map',{ center:[-2.22422,115.493], zoom:13, doubleClickZoom:false, minZoom: 13,   // <-- min zoom out
    maxZoom: 18 });
   // const baseTileLayer = L.tileLayer('/map/{z}/{x}/{y}.jpg',{
     const baseTileLayer = L.tileLayer('https://appdevbumaidsta001.blob.core.windows.net/map-mhr/{z}/{x}/{y}.jpg',{
      attribution:'&copy; BukitMakmur & contributors', tileSize:512, zoomOffset:-1, opacity:.8
    }).addTo(map);

     const plannedGroup    = L.layerGroup().addTo(map);
     const plannedNumLayer = L.layerGroup().addTo(map);
     const actualGroup     = L.layerGroup().addTo(map);
     const actualNumLayer  = L.layerGroup().addTo(map);

// NEW: markers visualized from the table rows
const markerLayer = L.layerGroup().addTo(map);

// point picker (optional)
const pickLayer = L.layerGroup().addTo(map);
let pickedMarker=null;
function updatePickInputs(lat,lng){
  const $lat=$('#lat'), $lng=$('#lng');
  if($lat.length) $lat.val(Number(lat).toFixed(8));
  if($lng.length) $lng.val(Number(lng).toFixed(8));
}
function setPickedMarker(lat,lng,opts){
  const pan=!(opts&&opts.pan===false);
  const latNum=Number(lat), lngNum=Number(lng);
  if(!Number.isFinite(latNum)||!Number.isFinite(lngNum)) return;
  if(!pickedMarker){
    pickedMarker=L.marker([latNum,lngNum],{draggable:true}).addTo(pickLayer);
    pickedMarker.on('dragend',()=>{const p=pickedMarker.getLatLng(); updatePickInputs(p.lat,p.lng);});
  } else pickedMarker.setLatLng([latNum,lngNum]);
  updatePickInputs(latNum,lngNum); if(pan) map.panTo([latNum,lngNum]);
}
map.on('click',e=>setPickedMarker(e.latlng.lat,e.latlng.lng));
$(document).on('input change blur keyup','#lat,#lng',function(){
  const lat=parseFloat($('#lat').val()), lng=parseFloat($('#lng').val());
  if(Number.isFinite(lat)&&Number.isFinite(lng)) setPickedMarker(lat,lng,{pan:false});
});
$(document).on('click','#clearPoint',()=>{ pickLayer.clearLayers(); pickedMarker=null; $('#lat,#lng').val(''); });

// distance chip
const distancePanel=L.control({position:'bottomleft'});
distancePanel.onAdd=function(){ const div=L.DomUtil.create('div'); div.id='distance-panel'; div.textContent='Distance: -'; return div; };
distancePanel.addTo(map);
function updateDistancePanel(text){ const el=document.getElementById('distance-panel'); if(el) el.textContent=text; }

/* ==========================================
   =====  SAFE PARSE & GROUPING HELPERS  ====
   ========================================== */
   function toNum(v){
    if(typeof v==='number') return Number.isFinite(v)?v:NaN;
    if(typeof v==='string'){
      const s=v.trim(); if(!s) return NaN;
      const cleaned = s.includes('.') ? s : s.replace(',','.');
      const n = Number(cleaned);
      return Number.isFinite(n) ? n : NaN;
    }
    return NaN;
  }
const MAX_SEG_METERS = 1000; // tweak if needed

function groupKey(p){
  return [
  p.HeaderID ?? p.PlanID ?? p.RouteID ?? p.ID ?? p['\uFEFFID'] ?? 'NA',
  p.PTD ?? p.PTDName ?? ''
  ].join('::');
}
function toLL(p){ const lat=toNum(p.Latitude), lng=toNum(p.Longitude); return (Number.isFinite(lat)&&Number.isFinite(lng)) ? L.latLng(lat,lng) : null; }

function canConnect(a,b){
  const gA=a.HeaderID??a.PlanID??a.RouteID??a.ID??a['\uFEFFID'];
  const gB=b.HeaderID??b.PlanID??b.RouteID??b.ID??b['\uFEFFID'];
  if(gA!==gB) return false;

  const ra=Number(a.RouteNumber), rb=Number(b.RouteNumber);
  if(!Number.isFinite(ra)||!Number.isFinite(rb)||rb!==ra+1) return false;

  const A=toLL(a), B=toLL(b);
  if(!A||!B) return false;
  return A.distanceTo(B) <= MAX_SEG_METERS;
}

/* ==========================================
   ============  MAP CONTROLS  ==============
   ========================================== */
   function applyToggleStates(){
    map.getContainer().classList.toggle('labels-hidden',  labelsHidden);
    map.getContainer().classList.toggle('numbers-hidden', numbersHidden);
    if(lblBtn){ lblBtn.classList.toggle('is-off', labelsHidden);  lblBtn.title = labelsHidden  ? 'Show labels'        : 'Hide labels'; }
    if(numBtn){ numBtn.classList.toggle('is-off', numbersHidden); numBtn.title = numbersHidden ? 'Show route numbers' : 'Hide route numbers'; }
  }
// LBL
const lblToggle=L.control({position:'topright'});
lblToggle.onAdd=function(){ const div=L.DomUtil.create('div','leaflet-bar'); const a=L.DomUtil.create('a','ctrl-btn',div);
a.href='#'; a.textContent='LBL'; lblBtn=a;
L.DomEvent.on(a,'click',L.DomEvent.stop).on(a,'mousedown',L.DomEvent.stop).on(a,'dblclick',L.DomEvent.stop)
.on(a,'click',()=>{ labelsHidden=!labelsHidden; applyToggleStates(); });
applyToggleStates(); return div;
}; lblToggle.addTo(map);

// 123
const numToggle=L.control({position:'topright'});
numToggle.onAdd=function(){ const div=L.DomUtil.create('div','leaflet-bar'); const a=L.DomUtil.create('a','ctrl-btn',div);
a.href='#'; a.textContent='123'; numBtn=a;
L.DomEvent.on(a,'click',L.DomEvent.stop).on(a,'mousedown',L.DomEvent.stop).on(a,'dblclick',L.DomEvent.stop)
.on(a,'click',()=>{ numbersHidden=!numbersHidden; applyToggleStates(); });
applyToggleStates(); return div;
}; numToggle.addTo(map);

// ACT
const actToggle=L.control({position:'topright'});
actToggle.onAdd=function(){ const div=L.DomUtil.create('div','leaflet-bar'); const a=L.DomUtil.create('a','ctrl-btn',div);
a.href='#'; a.title='Hide actual route'; a.textContent='ACT';
L.DomEvent.on(a,'click',L.DomEvent.stop).on(a,'mousedown',L.DomEvent.stop).on(a,'dblclick',L.DomEvent.stop)
.on(a,'click',()=>{ actualVisible=!actualVisible;
  if(actualVisible){ actualGroup.addTo(map); actualNumLayer.addTo(map); a.title='Hide actual route'; a.classList.remove('is-off'); }
  else{ map.removeLayer(actualGroup); map.removeLayer(actualNumLayer); a.title='Show actual route'; a.classList.add('is-off'); }
}); return div;
}; actToggle.addTo(map);

/* ==========================================
   ========  PLANNED ROUTE RENDERER  ========
   ========================================== */
   function renderPlanned(data, zoomTo=true){
    plannedGroup.clearLayers();
    plannedNumLayer.clearLayers();
    if(!Array.isArray(data)||!data.length) return;

    const arr = Array.isArray(data[0]) ? data.flat() : data;
    // console.log(arr)
    const groups={}; arr.forEach(p=>{ const k=groupKey(p); (groups[k] ||= []).push(p); });

    const isMulti = Object.keys(groups).length>1;
    const PERMA_LABELS = !isMulti && !isAllMode;

    let bounds=null; const ext = ll => (bounds ? bounds.extend(ll) : (bounds=L.latLngBounds(ll,ll)));
    let totalMetersAll=0;

    Object.entries(groups).forEach(([gid,list], idx)=>{
      const sorted=list.slice().sort((a,b)=>Number(a.RouteNumber)-Number(b.RouteNumber));
      const color = distinctColors[idx % distinctColors.length];
      let totalMeters=0;
      dataSaveAble = sorted;
    // console.log(dataSaveAble)
    // SEGMENTS
    for(let i=0;i<sorted.length-1;i++){
      const a=sorted[i], b=sorted[i+1];
      const A=toLL(a), B=toLL(b);
      if(!A || !B){ if (A) ext(A); if (B) ext(B); continue; }

      if(isAllMode){ ext(A); ext(B); continue; }
      if(!canConnect(a,b)){ ext(A); ext(B); continue; }

      L.polyline([A,B],{color:'white',weight:5,opacity:1,lineJoin:'round'}).addTo(plannedGroup);
      L.polyline([A,B],{
        color:(b.Status||'').toLowerCase()==='good' ? 'green' : 'red',
        weight:4, opacity:.85
      }).addTo(plannedGroup)
      .bindPopup(
        `<div style="min-width:200px">
        <div><b>${a.Waypoints}</b> (#${a.RouteNumber}) → <b>${b.Waypoints}</b> (#${b.RouteNumber})</div>
        <div>Segment: <b>${fmtDistance(A.distanceTo(B))}</b></div>
        </div>`
        );

      totalMeters += A.distanceTo(B);
      ext(A); ext(B);
    }

    // VERTICES
    sorted.forEach((pt,i)=>{
      const A=toLL(pt); if(!A) return;
      const label = `${pt.Waypoints}`;
      const popup = `<b>${pt.Waypoints}</b><br>Route #${pt.RouteNumber}<br>Status: ${pt.Status||'unknown'}`;

      if(i===0 || i===sorted.length-1){
        const icon=new L.Icon({
          iconUrl: markerIconUrl(i===0?'grey':'green'),
          shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
          iconSize:[25,41], iconAnchor:[12,41], popupAnchor:[1,-34], shadowSize:[41,41]
        });
        const m=L.marker(A,{icon}).addTo(plannedGroup).bindPopup(popup);
        if (PERMA_LABELS) m.bindTooltip(label,{permanent:true,direction:'top',offset:[0,-10],className:'marker-label'});
      } else {
        const cm=L.circleMarker(A,{radius:PERMA_LABELS?2:1.5,color:color,fillColor:color,fillOpacity:1,weight:0})
        .addTo(plannedGroup).bindPopup(popup);
        if (PERMA_LABELS) cm.bindTooltip(label,{permanent:true,direction:'top',offset:[0,-8],className:'marker-label'});
      }

      if (!isAllMode){
        const tt=L.tooltip({permanent:true,direction:'center',offset:[0,0],className:'route-num-label'})
        .setContent(String(pt.RouteNumber ?? ''))
        .setLatLng(A);
        plannedNumLayer.addLayer(tt);
      }

      ext(A);
    });

    totalMetersAll += totalMeters;
  });

    plannedTotalMetersGlobal = totalMetersAll;
    if (isAllMode) updateDistancePanel('Planned (ALL): points only');
    else updateDistancePanel(`Planned total: ${fmtDistance(totalMetersAll)}`);

    if(zoomTo && bounds) map.fitBounds(bounds,{padding:[30,30]});
    applyToggleStates();
  }

/* ==========================================
   ========   ACTUAL ROUTE RENDERER   =======
   ========================================== */
   function renderActual(data){
    actualGroup.clearLayers();
    actualNumLayer.clearLayers();
    if(!Array.isArray(data)||!data.length) return;

    const arr = Array.isArray(data[0]) ? data.flat() : data;
    const groups={}; arr.forEach(p=>{ const k=groupKey(p); (groups[k] ||= []).push(p); });

    Object.values(groups).forEach(list=>{
      const sorted=list.slice().sort((a,b)=>Number(a.RouteNumber)-Number(b.RouteNumber));

      for(let i=0;i<sorted.length-1;i++){
        const a=sorted[i], b=sorted[i+1];
        if(!canConnect(a,b)) continue;
        const A=toLL(a), B=toLL(b); if(!A||!B) continue;
        L.polyline([A,B],{color:'#1e90ff',weight:4,opacity:.9}).addTo(actualGroup);
      }

      sorted.forEach((pt,i)=>{
        const A = toLL(pt); if (!A) return;
        const label = `${pt.WayPoint}`;
        const popup = `<b>${pt.WayPoint}</b><br>Route #${pt.RouteNumber}<br>Status: ${pt.Status||'unknown'}`;

        const cm = L.circleMarker(A,{
          radius:2,
          color:'#1e90ff',
          fillColor:'#1e90ff',
          fillOpacity:1,
          weight:0
        }).addTo(actualGroup).bindPopup(popup);

        // waypoint labels (like in Planned)
        cm.bindTooltip(label,{
          permanent:true,
          direction:'top',
          offset:[0,-8],
          className:'marker-label'
        });

        // route number labels (centered)
        const tt=L.tooltip({
          permanent:true,
          direction:'center',
          offset:[0,0],
          className:'route-num-label'
        })
        .setContent(String(pt.RouteNumber ?? ''))
        .setLatLng(A);
        actualNumLayer.addLayer(tt);
      });

    });

    if(!actualVisible){ map.removeLayer(actualGroup); map.removeLayer(actualNumLayer); }
  }

/* ==========================================
   ============  DATA LOADERS  ==============
   ========================================== */
   function loadPlanned(routeId){
    if(plannedReq && plannedReq.readyState!==4){ try{plannedReq.abort();}catch(_){} }
    const token=++plannedToken;
    plannedReq=$.ajax({url:API.planned(routeId),dataType:'json'})
    .done(resp=>{ if(token!==plannedToken) return; renderPlanned(resp,true); })
    .fail((xhr,st)=>{ if(st!=='abort') console.error('planned error:',st); });
  }
  function loadActual(routeId){
    if(actualReq && actualReq.readyState!==4){ try{actualReq.abort();}catch(_){} }
    const token=++actualToken;
    actualReq=$.ajax({url:API.actual(routeId),dataType:'json'})
    .done(resp=>{ if(token!==actualToken) return; renderActual(resp); })
    .fail((xhr,st)=>{ if(st!=='abort') console.error('actual error:',st); });
  }

/* ==========================================
   ==============  PTD TABLE  ===============
   ========================================== */
   const nz = v => Number(v ?? 0);
   function buildPtdTable(){
    const tbody=$('#ptdTable tbody').empty();
    tbody.append(`<tr style="text-align:center" data-id="0" data-label="ALL PTD">
      <td colspan="7"><b>ALL PTD</b></td>
      </tr>`);

    $.getJSON(API.ptds).done(data=>{
      (data||[]).forEach(item=>{
        const id=item.ID ?? item.id, PTD=item.PTD ?? item.text; if(!id||!PTD) return;

        let sugesStatus=''; let badgeStatus='';
        if (item.MatchPercentage>95)       { sugesStatus='Consistent';     badgeStatus='badge bg-success'; }
        else if(item.MatchPercentage>50)   { sugesStatus='Need to Check';  badgeStatus='badge bg-warning'; }
        else if((item.MatchPercentage>0) || (item.MatchPercentage==0 && item.MismatchPercentage==100))    { sugesStatus='Need to revise'; badgeStatus='badge bg-danger'; }
        else                               { sugesStatus='No Actual Found';}

        tbody.append(`<tr style="text-align:center" data-id="${id}" data-label="${PTD}">
          <td>${PTD}</td>
          <td>${nz(item.MatchPercentage)}%</td>
          <td>${nz(item.MissingPercentage)}%</td>
          <td>${nz(item.MismatchPercentage)}%</td>
          <td>${Math.round(item.MostRouteH||0)}</td>
          <td>${Math.round(item.MostRouteHm1||0)}</td>
          <td><span class="${badgeStatus} rounded-pill ">${sugesStatus}</span></td>
          </tr>`);
      });
    $('#ptdTable tbody tr').eq(0).trigger('click'); // auto pick ALL
  });
  }

  $(document).on('click','#ptdTable tbody tr',function(){
    $('#ptdTable tbody tr').removeClass('table-active'); $(this).addClass('table-active');
    const id=Number($(this).data('id')); 
    const label=String($(this).data('label')||'');
    tablePTDClickedId=id;
    tablePTDClickedlabel=label;
    reloadAll(id,label);

  });
  function reloadAll(id,label){
    if(id===0){ isAllMode=true;  labelsHidden=true;  numbersHidden=true; }
    else      { isAllMode=false; labelsHidden=false; numbersHidden=false; }
    applyToggleStates();

    if(id===0){
      const allIds=$('#ptdTable tbody tr').map((_,tr)=>Number($(tr).data('id'))).get().filter(v=>v>0);
      const idsCsv=allIds.join(',');
      if(!idsCsv){ plannedGroup.clearLayers(); plannedNumLayer.clearLayers(); updateDistancePanel('Distance: -'); return; }
      loadPlanned(idsCsv);
      // loadActual(idsCsv);
      GetRoadSegmentPTDs(idsCsv+'_'+label);
    }else{
      loadPlanned(id);
      loadActual(id);
      GetRoadSegmentPTDs(id+'_'+label);
    }
    setTimeout(()=>map.invalidateSize(true),0);
  }

/* ==========================================
   ====== ROAD SEGMENT PLAN (Select2)  ======
   ========================================== */
   function initRoadSegmentPlanSelect2(){
    const $el = $('#RoadSegmentPlan');
    if(!$el.length) return;
    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
    if ($.fn && $.fn.select2){
      $el.select2({ placeholder:'Select RoadSegment', allowClear:true });
      const $first = $el.find('option:not([disabled])[value!=""]').first();
      if ($first.length) {
        $el.val($first.val()).trigger('change');
      }
    }else{
      console.error('Select2 not loaded');
    }
  }

// Load options and init Select2
$.getJSON(API.rsp).done(data=>{
  const $el = $('#RoadSegmentPlan').empty();
  $el.append(new Option('', '', false, false)); // keep a blank first option
  (data||[]).forEach(item => $el.append(new Option(item.RoadSegment, item.RoadSegment)));
  initRoadSegmentPlanSelect2();
});

/* ==========================================
   ===== TABLE → MARKERS & TOTAL DIST   =====
   ========================================== */
// columns in table: 0=name, 1=route#, 2=lng, 3=lat, ...
// function loadTableMarkers(){
//   markerLayer.clearLayers();
//   const $rows = $('#roadSegmentList tbody tr').length
//     ? $('#roadSegmentList tbody tr')
//     : $('#sortable tr');

//   $rows.each(function(){
//     const tds = $(this).find('td');
//     if (tds.length >= 4){
//       const name = $(tds[0]).text().trim();
//       const rn   = parseInt($(tds[1]).text().trim(),10);
//       const lng  = toNum($(tds[2]).text());
//       const lat  = toNum($(tds[3]).text());
//       if (Number.isFinite(lat) && Number.isFinite(lng)){
//         L.marker([lat,lng]).bindPopup(`<b>${name}</b><br>Route #${rn}`).addTo(markerLayer);
//       }
//     }
//   });

//   computeTableDistances();
// }

function computeTableDistances(){
  const rows = Array.from(document.querySelectorAll('#rs #roadSegmentList tbody tr, #rs #sortable tr, #roadSegmentList tbody tr, #sortable tr'));
  if (!rows.length){ updateDistancePanel('Table total: -'); return; }

  let total=0;
  for(let i=0;i<rows.length-1;i++){
    const a = rows[i  ].querySelectorAll('td');
    const b = rows[i+1].querySelectorAll('td');
    if (a.length>=4 && b.length>=4){
      const lngA = toNum(a[2].textContent);
      const latA = toNum(a[3].textContent);
      const lngB = toNum(b[2].textContent);
      const latB = toNum(b[3].textContent);
      if ([latA,lngA,latB,lngB].every(Number.isFinite)){
        total += L.latLng(latA,lngA).distanceTo(L.latLng(latB,lngB));
      }
    }
  }
  updateDistancePanel(`Table total: ${fmtDistance(total)}`);
}

function bindTableDistanceRecalc(){
  const $tbl = $('#rs #roadSegmentList tbody').length
  ? $('#rs #roadSegmentList tbody')
  : ($('#rs #sortable').length ? $('#rs #sortable') : $());

  if(!$tbl.length){ updateDistancePanel('Table total: -'); return; }

  computeTableDistances();

  // sortable?
  $tbl.off('sortstop.compute').on('sortstop.compute', computeTableDistances);

  // edits
  $tbl.off('keyup.compute change.compute','td')
  .on('keyup.compute change.compute','td', computeTableDistances);

  // add/remove rows
  const obs = new MutationObserver(()=>computeTableDistances());
  obs.observe($tbl[0], {childList:true, subtree:true});
}

// keep totals correct when deleting rows
$(document).on('click','.delete-row',function(){
  $(this).closest('tr').remove();
  loadTableMarkers();
  computeTableDistances();
});

/* ==========================================
   ==============  RS PANEL  ================
   ========================================== */
   function GetRoadSegmentPTDs(param){
    $.ajax({url:API.rs(param),type:'GET'}).done(html=>{
      $('#rs').html(html);

    // After RS is injected, (re)bind and compute
    setTimeout(()=>{
      bindTableDistanceRecalc();
      // loadTableMarkers();      // also refresh markers from table
      map.invalidateSize(true);
    },0);

    if(pickedMarker){ const p=pickedMarker.getLatLng(); updatePickInputs(p.lat,p.lng); }
    const $scroller=$('#rs').find('.table-scroll'); if($scroller.length) $scroller.scrollTop(0);
  });
  }

/* ==========================================
   ==============  BOOTSTRAP  ===============
   ========================================== */
   $(function(){
    if(!$('.ptd-table-wrap').length){ $('#ptdTable').wrap('<div class="ptd-table-wrap"></div>'); }
    buildPtdTable();
  });

   /* === LEGACY GLOBAL SHIMS (compat) === */
   window.loadMarkersForRoute = function(routeId, data){
    try{
      if(data && typeof renderPlanned==='function'){ renderPlanned(data,true); }
      else if(typeof loadPlanned==='function'){ loadPlanned(routeId); }
    }catch(e){ console.error('Shim loadMarkersForRoute error:', e); }
  };
  window.loadMarkersForRouteActual = function(routeId, data){
    try{
      if(data && typeof renderActual==='function'){ renderActual(data); }
      else if(typeof loadActual==='function'){ loadActual(routeId); }
    }catch(e){ console.error('Shim loadMarkersForRouteActual error:', e); }
  };

// LayerGroup bringToFront guard
if(window.L && L.LayerGroup && !L.LayerGroup.prototype.bringToFront){
  L.LayerGroup.prototype.bringToFront=function(){ this.eachLayer(l=>l&&l.bringToFront&&l.bringToFront()); return this; };
}

/* ==========================================
   ===     ADD NEW POINT BUTTON        ======
   ========================================== */
   $('#newPoint').on('click', function () {
  const newSegment = $('#RoadSegmentPlan').val(); // use the Select2 value
  const newlat = $('#lat').val();
  const newlong = $('#lng').val();
  const WaypointNew = $('#WaypointNew').val();


  if (newSegment && newlat && newlong) {
    $('#roadSegmentList tbody').append(`
      <tr style="text-align:center">
      <td>${newSegment}</td>
      <td>0</td>
      <td>${newlong}</td>
      <td>${newlat}</td>
      <td>0</td>
      <td>${WaypointNew}</td>
      <td>0</td>
      <td>0</td>
      <td>0</td>
      <td>0</td>
      <td><i class="lni lni-trash-can delete-row" style="color:red; cursor:pointer;"></i></td>
      </tr>
      `);

    loadTableMarkers();
    computeTableDistances();
  }else{
    alert('please check blank values');
  }
});
</script>
@endpush
