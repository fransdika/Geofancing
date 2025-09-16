@extends('layout')

@section('title', 'Map Tiler Radius')

@push('styles')
    <!-- Page-specific CSS libs -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">
    <style>
        /* ====== THEME VARS (Light default) ====== */
        :root{
          --bg:#f7f7fb;--card-bg:#fff;--text:#1f2328;--muted:#6c757d;--border:#e5e7eb;--accent:#0d6efd;
          --thead-bg:#343a40;--thead-text:#fff;--table-row:#f9f9f9;--popup-bg:#000;--popup-text:#fff;
        }
        body{background:var(--bg);color:var(--text);}
        .page-title{margin:24px 0;text-align:center;font-weight:600;color:var(--text);}
        .card{border:0;border-radius:.75rem;box-shadow:0 8px 18px rgba(22,28,45,.08);background:var(--card-bg);}
        .form-group label{margin-bottom:.25rem;}
        #map{height:70vh;min-height:520px;border-radius:.5rem;}

        /* Leaflet */
        .leaflet-tooltip.marker-label{
          background:none;border:none;box-shadow:none;padding:0;font-size:11px;color:#fff;
          text-shadow:1px 1px 2px rgba(0,0,0,.6);
        }
        .leaflet-popup{width:550px!important;}
        .leaflet-popup-content-wrapper{background:var(--popup-bg);color:var(--popup-text);}
        .leaflet-popup-content{width:100%;}
        .custom-leaflet-popup table{width:100%;border-collapse:collapse;}
        .custom-leaflet-popup th,.custom-leaflet-popup td{padding:4px 8px;white-space:normal;word-wrap:break-word;}

        /* Select2 */
        .select2-container{width:100%!important;}
        .select2-container--default .select2-selection--single{background:var(--card-bg);border-color:var(--border);}
        .select2-container--default .select2-selection--single .select2-selection__rendered{color:var(--text);}
        .select2-dropdown{background:var(--card-bg);color:var(--text);border-color:var(--border);}
        .select2-results__option--highlighted{background:var(--accent)!important;color:#fff!important;}

        .tableFixHead thead th{position:sticky;top:0;z-index:2;background:var(--thead-bg);color:var(--thead-text);}

        /* Dark mode */
        body.dark-mode{
          --bg:#0f1419;--card-bg:#111827;--text:#e5e7eb;--muted:#9ca3af;--border:#374151;--accent:#60a5fa;
          --thead-bg:#1f2937;--thead-text:#e5e7eb;--table-row:#0b0f14;--popup-bg:#0b0f14;--popup-text:#e5e7eb;
        }
        body.dark-mode .btn-primary{background-color:var(--accent);border-color:var(--accent);}
        body.dark-mode .btn-success{background-color:#10b981;border-color:#10b981;}
        body.dark-mode .table,body.dark-mode .table td,body.dark-mode .table th{border-color:var(--border)!important;}
        body.dark-mode .leaflet-tile{filter:invert(.92) hue-rotate(180deg) saturate(.7) brightness(.9);}
        #rs>*:first-child{margin-top:0!important;}
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <h2 class="page-title">PTD Actual Performance</h2>

        <div class="form-group mb-2 container-fluid" style="width: 100%">
          <label for="mySelect" class="font-weight-semibold">PTD Name</label>
          <select id="mySelect" class="form-control"></select>
        </div>
        <hr class="my-2">
    <div class="row">
      <!-- Map -->
      <div class="col-lg-7 mb-3">
        <div class="card">
          <div class="card-body p-2">
            <div id="map"></div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-5 mb-3">
        <div class="card h-100">
          <div class="card-body p-2">
            <div class="form-group mb-2 container-fluid" style="width: 100%">
              <label for="mySelect" class="font-weight-semibold">PTD Performance</label>
            </div>
            <hr class="my-2">
            <div id="rs"><!-- optional ajax content --></div>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Page-specific JS libs -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    // ===== Globals (dipertahankan) =====
    const maptilerKey='DOIetENrqrAuzIMN1sha';
    let carMarkers=[];     // unit support markers
    let routeMap={};
    let radiusCircle=null;
    let ptd_id=0;
    let max_route_number=0;

    // ===== Map init =====
    const map=L.map('map',{center:[-2.22422,115.493],zoom:13,doubleClickZoom:false});

    L.tileLayer('/map/{z}/{x}/{y}.jpg',{
      attribution:'&copy; BukitMakmur & contributors',
      tileSize:512, zoomOffset:-1, opacity:0.8
    }).addTo(map);

    const imageBounds=[[-4.5,106.94],[7.7,120.5]];
    L.imageOverlay(
      'https://www.researchgate.net/publication/365362464/figure/fig1/AS%3A11431281097599366%401668656616423/Kalimantan-map-showing-its-five-provinces-and-relationship-to-Malaysian-Borneo.png',
      imageBounds,{opacity:0.0,interactive:false}
    ).addTo(map);

    const distinctColors=['blue','orange','purple','violet','grey','black','yellow','pink','brown'];
    function getMarkerIconUrl(color){
      return `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-${color}.png`;
    }

    // ===== First load =====
    loadMarkersForRoute(ptd_id);

    // ===== PTD Select2 =====
    $.getJSON('/api/ptds', function(data){
      data.forEach(item => $('#mySelect').append(new Option(item.text,item.id)));
      $('#mySelect').select2({placeholder:'Select a route'});
    });

    $('#mySelect').on('select2:select', function(){
      let selectedRouteId=[];
      selectedRouteId.push($(this).val() || [0]);
      loadMarkersForRoute(selectedRouteId.join(','));

      // optional: load sidebar content
      // let ptdSelected = selectedRouteId==0 ? Array.from(this.options).map(o=>o.value).join(',') : selectedRouteId;
      GetRoadSegmentPTDs(ptdSelected);
    });

    function GetRoadSegmentPTDs(idcsv){
      $.ajax({
        url:'/api/getRoadSegmentPTDs/'+idcsv, type:'GET',
        success:function(r){
          $('#rs').html(r);
          const $scroller = $('#rs').find('.table-scroll'); // kalau ada
          if($scroller.length) $scroller.scrollTop(0);
        }
      });
    }

    // ===== Core loader =====
    function loadMarkersForRoute(routeId){
      $.getJSON(`/api/markersDB/${routeId}`, function(data){

        // Bersihkan layer geometri saja, simpan tile/overlay
        map.eachLayer(layer=>{
          if (layer instanceof L.Marker || layer instanceof L.Polyline || layer instanceof L.Circle || layer instanceof L.CircleMarker){
            map.removeLayer(layer);
          }
        });

        carMarkers=[]; routeMap={}; if(radiusCircle){map.removeLayer(radiusCircle); radiusCircle=null;}

        const groups={};
        data.flat().forEach(point=>{
          const id=point["\uFEFFID"]||point.ID;
          if(!groups[id]) groups[id]=[];
          groups[id].push(point);
        });

        Object.keys(groups).forEach((id,idx)=>{
          const sorted=groups[id].sort((a,b)=>Number(a.RouteNumber)-Number(b.RouteNumber));
          const markerColor=distinctColors[idx%distinctColors.length];
          const latlngsForCar=[];
          let totalLat=0,totalLng=0;

          for(let i=0;i<sorted.length-1;i++){
            const from=sorted[i], to=sorted[i+1];
            const latlngs=[
              [parseFloat(from.Latitude), parseFloat(from.Longitude)],
              [parseFloat(to.Latitude),   parseFloat(to.Longitude)]
            ];
            latlngsForCar.push(latlngs[0]);
            if(i===sorted.length-2) latlngsForCar.push(latlngs[1]);

            const segmentColor=(to.Status||'').toLowerCase()==='good'?'green':'red';

            L.polyline(latlngs,{color:'white',weight:5,opacity:1,lineJoin:'round'}).addTo(map);
            const polyline=L.polyline(latlngs,{color:segmentColor,weight:4,opacity:.8})
              .addTo(map).bindPopup('Loading...');

            polyline.on('popupopen', function(e){
              const popup=e.popup;
              const fromLat=parseFloat(from.Latitude), fromLng=parseFloat(from.Longitude);
              const center=[fromLat, fromLng];

              $.getJSON(`/api/getUnitSupport/1/${fromLat}/${fromLng}`, function(list){
                if(carMarkers.length>0){carMarkers.forEach(m=>map.removeLayer(m));carMarkers=[];}

                let rows='';
                list.forEach(equip=>{
                  const carIcon=L.icon({
                    iconUrl:'https://img.icons8.com/color/48/dump-truck.png',
                    iconSize:[32,32],iconAnchor:[16,16],popupAnchor:[0,-16]
                  });
                  const m=L.marker([equip.Latitude,equip.Longitude],{icon:carIcon})
                    .addTo(map)
                    .bindPopup(`<b>Unit: ${equip.Equipment}</b><br>Operator: ${equip.Operator}<br>Position: ${equip.Position||'unknown'} (${equip.Latitude},${equip.Longitude})`)
                    .bindTooltip(`${equip.Equipment}`,{permanent:true,direction:'top',offset:[0,-10],className:'custom-leaflet-popup'});
                  carMarkers.push(m);

                  rows += `
                    <tr>
                      <td>${equip.Equipment}</td>
                      <td><span class="badge badge-success">Good</span></td>
                      <td>${equip.Operator}</td>
                      <td>${equip.Position||''}</td>
                      <td><span class="badge badge-success">${parseFloat(equip.DistanceKm).toFixed(2)} km</span></td>
                      <td class="text-success">AutoAssignment</td>
                    </tr>`;
                });

                const unitAv = carMarkers.length===0
                  ? `No Support Unit nearby, <a href="#">search more</a>`
                  : `
                    <div class="card bg-dark text-white p-3" style="width:500px">
                      <div class="mb-2"><strong>Budi - Supervisor MHR</strong></div>
                      <div class="alert alert-warning p-2 d-flex align-items-center" role="alert">
                        Perlu perbaikan P1 jalan di segment ${from.RoadSegment}. Equipment support yang tersedia:
                      </div>
                      <table class="table table-sm table-bordered table-dark mb-0">
                        <thead class="thead-black text-light" style="background-color:black">
                          <tr>
                            <th>Equipment</th><th>Status</th><th>Operator</th><th>Position</th>
                            <th>Distance to Position</th><th>Action</th>
                          </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                      </table>
                    </div>`;

                popup.setContent(`
                  <p><b>${from.PTD}</b></p><hr style="border-top:1px solid #fff;">
                  <b>From:</b> ${from.RoadSegment} (#${from.RouteNumber})<br>
                  <b>To:</b> ${to.RoadSegment} (#${to.RouteNumber})<br>
                  <b>Status:</b> ${to.Status||'unknown'}
                  <hr style="border-top:1px solid #fff;"><p><b>Unit Support Available:</b></p>${unitAv}
                `);
                popup.update();
              });

              if(radiusCircle) map.removeLayer(radiusCircle);
              radiusCircle=L.circle(center,{radius:1000,color:'blue',fillColor:'#30a3ec',fillOpacity:.2,interactive:false}).addTo(map);
            });
          }

          routeMap[id]=latlngsForCar;

          sorted.forEach((point,i)=>{
            const lat=parseFloat(point.Latitude), lng=parseFloat(point.Longitude);
            const popupText=`<b>${point.RoadSegment}</b><br>Route #${point.RouteNumber}<br>Status: ${point.Status||'unknown'}`;
            totalLat+=lat; totalLng+=lng;

            if(i===0){
              const icon=new L.Icon({iconUrl:getMarkerIconUrl('grey'),
                shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize:[25,41],iconAnchor:[12,41],popupAnchor:[1,-34],shadowSize:[41,41]});
              L.marker([lat,lng],{icon}).addTo(map).bindPopup(popupText)
                .bindTooltip(`${point.RoadSegment}`,{permanent:true,direction:'top',offset:[0,-10],className:'marker-label'});
            } else if(i===sorted.length-1){
              const icon=new L.Icon({iconUrl:getMarkerIconUrl('green'),
                shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize:[25,41],iconAnchor:[12,41],popupAnchor:[1,-34],shadowSize:[41,41]});
              L.marker([lat,lng],{icon}).addTo(map).bindPopup(popupText)
                .bindTooltip(`${point.RoadSegment}`,{permanent:true,direction:'top',offset:[0,-10],className:'marker-label',opacity:.5});
            } else {
              L.circleMarker([lat,lng],{radius:2,color:markerColor,fillColor:markerColor,fillOpacity:1})
                .addTo(map).bindPopup(popupText)
                .bindTooltip(`${point.RoadSegment}`,{permanent:true,direction:'top',offset:[0,-10],className:'marker-label',opacity:.5});
            }
          });

          const count=sorted.length;
          const centerLatLng=[totalLat/count,totalLng/count];
          let zoom=13; if(routeId>0) zoom=15;
          map.setView(centerLatLng,zoom);
        });
      });
    }

    map.on('dblclick', function(){
      if(radiusCircle){map.removeLayer(radiusCircle);radiusCircle=null;}
      if(carMarkers.length>0){carMarkers.forEach(m=>map.removeLayer(m));carMarkers=[];}
    });
    </script>
@endpush