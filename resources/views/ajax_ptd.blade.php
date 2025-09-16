<?php if ($func === 'loadRoadSegmentPtd'): ?>
    <style>
        /* Scrollable table with sticky header */
        .tableFixHead { max-height: 500px; overflow-y: auto; }
        .tableFixHead table { border-collapse: separate; border-spacing: 0; table-layout: fixed; width: 100%; }
        #sortable tr { cursor: move; background-color: var(--table-row); }

        /* ==== Dark mode fixes for AJAX content ==== */
        body.dark-mode .table,
        body.dark-mode .table td,
        body.dark-mode .table th {
            color: var(--text) !important;
            background-color: transparent;
        }
        body.dark-mode #sortable tr {
            color: var(--text) !important;
            background-color: var(--table-row);
        }
        body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255,255,255,0.03);
        }
        body.dark-mode .table-striped tbody tr:nth-of-type(even) {
            background-color: rgba(255,255,255,0.015);
        }
        body.dark-mode .table thead th {
            color: var(--thead-text) !important;
            background-color: var(--thead-bg) !important;
            border-color: var(--border) !important;
        }
        body.dark-mode .table, 
        body.dark-mode .table td, 
        body.dark-mode .table th {
            border-color: var(--border) !important;
        }
        body.dark-mode .modal-content,
        body.dark-mode #RsModalContent {
            color: var(--text) !important;
            background-color: var(--card-bg) !important;
        }

        #routesTable tbody tr { cursor: pointer; }
        #routesTable tbody tr:hover { background:#f7f9fc; }
        #routesTable tbody tr.active { background:#e8f0fe; }

        .tableFixHead {
            overflow: auto;             
        }

        .tableFixHead thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #343a40;         
            color: #fff;
        }

        .tableFixHead th,
        .tableFixHead td {
            white-space: nowrap;
        }

        .tableFixHead table {
            table-layout: fixed;         
            min-width: 1200px;          
        }
        #bsModal .form-label { font-weight: 700; }
    </style>

    <script>
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
        $(document).ready(function () {
            max_route_number = `<?= empty($data) ? 0 : max(array_map(fn($item) => $item->RouteNumber, $data)); ?>`;

            if ($.ui && $.ui.sortable) {
                $("#sortable").sortable({
                    items: "tr",
                    cursor: "move",
                    axis: "y",
                    containment: "parent",
                    update: function () { loadTableMarkers(); }
                });
            } else {
                console.error("jQuery UI tidak terload.");
            }

            const rows = $('#sortable tr').get();
            rows.sort(function (a, b) {
                const A = parseInt($(a).find('td:eq(1)').text(), 10);
                const B = parseInt($(b).find('td:eq(1)').text(), 10);
                return A - B;
            });
            $.each(rows, function (_, row) { $('#sortable').append(row); });

            // loadTableMarkers();
        });

        function loadTableMarkers() {
            let mapData = [];
            let i = 1;
            $('#sortable tr').each(function () {
                const $cells = $(this).find('td');
                if ($cells.length >= 4) {
                    const item = {
                        ID: "1",
                        SiteId: "2010",
                        Date: "2025-07-16",
                        // PTD: $('#mySelect option:selected').text(),
                        PTD: tablePTDClickedlabel,
                        PTDLongName: tablePTDClickedlabel,
                        // PTDKey: $('#mySelect option:selected').text() + $cells.eq(0).text().trim(),
                        PTDKey: tablePTDClickedlabel + $cells.eq(0).text().trim(),
                        RoadSegment: $cells.eq(0).text().trim(),
                        Waypoints: $cells.eq(5).text().trim(),
                        RouteNumber: i,
                        RouteX: 0, RouteY: 0,
                        Latitude: parseFloat($cells.eq(3).text().trim()),  
                        Longitude: parseFloat($cells.eq(2).text().trim()),
                        Status: "Good",
                        RouteGroup: 1, RouteStsGroup: 1, StatusChange: 0,
                        rowNum: 0,
                        Elevasi: parseFloat($cells.eq(6).text().trim()),
                        BudgetSpeedEmpty: parseFloat($cells.eq(7).text().trim()),
                        BudgetSpeedLoaded: parseFloat($cells.eq(8).text().trim()),
                        BudgetSpeedAVG: parseFloat($cells.eq(9).text().trim()),
                        LoadUtcDate: "2025-07-28 01:50:28.870",
                        CreateDate: "2025-07-28 08:50:28.9200000",
                        ModifDate: null
                    };
                    mapData.push(item);
                    i++;
                }
            });

            if (mapData.length > 0) {

                const ptd_id=tablePTDClickedId
                if (typeof window.loadMarkersForRoute === 'function') {
                    window.loadMarkersForRoute(ptd_id, mapData);
                } else {
                    console.error("Function loadMarkersForRoute() tidak ditemukan di global scope.");
                }
            }
        }
    </script>

    <ul class="nav nav-tabs" id="rsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tab-rs-tab" data-bs-toggle="tab" href="#tab-rs" role="tab" aria-controls="tab-rs" aria-selected="true">
                Plan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-other-tab" data-bs-toggle="tab" href="#tab-other" role="tab" aria-controls="tab-other" aria-selected="false">
                Actual Historical
            </a>
        </li>
    </ul>

    <div class="tab-content border-left border-right border-bottom p-3" id="rsTabsContent">
        <div class="tab-pane fade show active" id="tab-rs" role="tabpanel" aria-labelledby="tab-rs-tab">

            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2">
                       <strong>Route List</strong>
                       <i
                       class="lni lni-question-circle ms-1"
                       style="color:green; cursor:pointer;"
                       data-bs-toggle="tooltip"
                       data-bs-placement="right"
                       title="Klik 2x pada baris record untuk melihat detail dan mengubah data"
                       aria-label="Help: Route List"
                       ></i>
                   </p>
               </div>
               <div class="col-md-6 text-right" style="text-align: right;">
                <?php if (!empty($status)): ?>
                    <button id="addRs" class="btn btn-primary btn-sm mb-2">
                        <i class="lni lni-plus"></i> WayPoint</button>
                    <?php endif ?>
                </div>
            </div>
            <hr class="m-1">

            <div class="tableFixHead" style="max-height:350px;">
                <table class="table table-bordered table-striped table-sm mb-0" id="roadSegmentList">
                    <colgroup>
                        <col style="width:180px"><col style="width:130px"><col style="width:140px">
                        <col style="width:160px"><col style="width:130px"><col style="width:300px">
                        <col style="width:120px"><col style="width:110px"><col style="width:110px">
                        <col style="width:110px"><col style="width:60px">
                    </colgroup>

                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>Road Segment</th>
                            <th>Route Number</th>
                            <th>Longitude</th>
                            <th>Latitude</th>
                            <th>Distance</th>
                            <th>WayPoint</th>
                            <th>Elevasi</th>
                            <th>B.S.Empty</th>
                            <th>B.S.Loaded</th>
                            <th>B.S.Avg</th>
                            <th>#</th>
                        </tr>
                    </thead>

                    <tbody id="sortable">
                        <?php foreach ($data as $value): ?>
                            <tr class="text-center" data-id="<?= $value->RouteNumber ?>">
                                <td><?= $value->RoadSegment ?></td>
                                <td><?= $value->RouteNumber ?></td>
                                <td><?= $value->Longitude ?></td>
                                <td><?= $value->Latitude ?></td>
                                <td><?= 0 ?></td>
                                <td><?= $value->RoadDetailSegment ?></td>
                                <td><?= $value->Elevasi ?></td>
                                <td><?= $value->BudgetSpeedEmpty ?></td>
                                <td><?= $value->BudgetSpeedLoaded ?></td>
                                <td><?= $value->BudgetSpeedAVG ?></td>
                                <td><i class="lni lni-trash-can delete-row" style="color:red; cursor:pointer;"></i></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <hr class="mt-1">
            <div class="row">
                <div class="col-md-6 text-right">
                    <span class="badge badge-info" id="distance"></span>                  
                </div>
                <div class=" col-md-6" style="text-align: right;">
                    <?php if (!empty($status)): ?>
                        <button id="SaveRoute" class="btn btn-success btn-sm"><i class="lni lni-save t-1"></i> Save Change</button>
                    <?php endif ?>
                </div>    
            </div>

        </div>

        {{-- SECOND TAB (placeholder) --}}

        <div class="tab-pane fade" id="tab-other" role="tabpanel" aria-labelledby="tab-other-tab">

            <div class="table-scroll" style="max-height: 450px; overflow-y: auto;">

                <table class="table table-condensed table-hover" id="routesTable">
                    <thead>
                        <tr style="text-align:center">
                            <td></td>
                            <td>#</td>
                            <td>Date</td>
                            <td>Route</td>
                            <td>Route Frekuensi</td>
                            <td>Jumlah Loader</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataActual as $keyPtdRoute => $valuePtdRoute): 
                            $arr = explode(",", $valuePtdRoute->RoadSegment);
                            if (!empty($arr[1])) {
                                $result = $arr[0] . " => " . $arr[1] . " => ..... => " . end($arr);
                            }else{
                                $result =$valuePtdRoute->RoadSegment;
                            }
                            ?>
                            <tr class="clickable-row" data-id="<?= $valuePtdRoute->id ?>"  data-date="<?= htmlspecialchars($valuePtdRoute->Date) ?>">
                                <td><i class="lni lni-information detail-row" style="color:red; cursor:pointer;"></i></td>
                                <td> <?=$keyPtdRoute+1 ?></td>
                                <td><?=$valuePtdRoute->Date ?></td>
                                <td><?=$result ?></td>
                                <td style="text-align: center;"><?=number_format($valuePtdRoute->MostRoute,0) ?></td>
                                <td style="text-align: center;"><?=number_format($valuePtdRoute->JumlahLoader,0) ?></td>
                                <td style="text-align: center;">
                                    <button data-id="<?=$valuePtdRoute->id ?>" class="btn btn-success btn-sm save-actual-plan">Apply</button>
                                </td>
                            </tr>

                        <?php endforeach ?>
                    </tbody>
                </table>
                <!-- <p class="text-muted mb-0">Second tab content…</p> -->
            </div>

        </div>
    </div>

    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitle" class="modal-title">Add Waypoint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="RsModalContent">Loading...</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="addSelected" type="button" class="btn btn-primary">Tambah</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalDtActual" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitle" class="modal-title">Waypoint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="PtdActualModalContent">Loading...</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="bsModal" class="modal fade" tabindex="-1" aria-labelledby="bsTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 id="bsTitle" class="modal-title">Route Detail List</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label mb-0">Road Segment</label>
                            <div id="infoRoadSegment" class="form-control-plaintext"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-0">Route #</label>
                            <div id="infoRouteNumber" class="form-control-plaintext"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-0">Longitude</label>
                            <div id="infoLongitude" class="form-control-plaintext"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-0">Latitude</label>
                            <div id="infoLatitude" class="form-control-plaintext"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0">Waypoint</label>
                            <div id="infoWaypoint" class="form-control-plaintext"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-0">Elevasi</label>
                            <div id="infoElevasi" class="form-control-plaintext"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-0">Distance</label>
                            <div id="infoDistance" class="form-control-plaintext"></div>
                        </div>
                    </div>

                    <input type="hidden" id="__bsRowIndex">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">B.S. Empty</label>
                            <!-- switched to text + numeric-only -->
                            <input type="text" inputmode="decimal" min="0" step="any"
                            class="form-control numeric-only" id="bsEmpty" autocomplete="off">
                            <div class="invalid-feedback">Masukkan angka yang valid.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">B.S. Loaded</label>
                            <!-- switched to text + numeric-only -->
                            <input type="text" inputmode="decimal" min="0" step="any"
                            class="form-control numeric-only" id="bsLoaded" autocomplete="off">
                            <div class="invalid-feedback">Masukkan angka yang valid.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button id="saveBs" type="button" class="btn btn-primary btn-sm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
        $('#addRs').on('click', function () {
            $('#RsModalContent').html('Loading...');
            $.ajax({
                url: '/api/getRoadSegmentMaster',
                method: 'GET',
                data: { ptd_id: `${max_route_number}` },
                success: function (r) { $('#RsModalContent').html(r); $('#myModal').modal('show'); },
                error: function (xhr) {
                    $('#RsModalContent').html('<div class="text-danger">Gagal memuat data.</div>');
                    console.error(xhr.responseText);
                }
            });
        });
        $('.save-actual-plan').on('click',function(){
            Swal.fire({
                title: 'Anda Yakin?',
                text: 'Apakah sudah yakin untuk menyimpan route PTD ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'green',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const $btn = $('#save-actual-plan').prop('disabled', true).append(' <span class="spinner-border spinner-border-sm"></span>');
                    $.ajax({
                        url: '/api/saveActualPlan',
                        method: 'POST',
                        dataType:'json',
                        data: {id: $(this).data('id')},
                        success: function (r) 
                        { 
                            if (r.status == 1) {
                                Swal.fire('Data Disimpan!', 'PTD Berhasil Diupdate', 'success');
                                reloadAll(tablePTDClickedId,tablePTDClickedlabel)
                            } else {
                                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan.', 'error');
                            }
                        },
                        error: function (xhr) {
                            $('#RsModalContent').html('<div class="text-danger">Gagal memuat data.</div>');
                            console.error(xhr.responseText);
                        }
                    }); 
                }
            });        
        })

        $('.detail-row').on('click', function () {
            $('#modalDtActual').modal('show'); 
        });

        document.addEventListener('click', function (e) {
            const icon = e.target.closest('.delete-row');
            if (!icon) return;

            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const $row = $(icon).closest('tr');

            Swal.fire({
                title: 'Anda Yakin?',
                text: 'Road Segment ini akan dihapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                focusCancel: true,
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: true,
                allowEnterKey: true
            }).then(({ isConfirmed }) => {
                if (!isConfirmed) return;
                $row.remove();
                Swal.fire('Data Terhapus!', 'Road Segment Berhasil Dihapus', 'success');
                if (typeof loadTableMarkers === 'function') loadTableMarkers();
            });
        }, true);



        $('#SaveRoute').on('click', function () {
            Swal.fire({
                title: 'Anda Yakin?',
                text: 'Apakah sudah yakin untuk menyimpan route PTD ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'green',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log(dataSaveAble)
                    const $btn = $('#SaveRoute').prop('disabled', true).append(' <span class="spinner-border spinner-border-sm"></span>');
                    $.ajax({
                        url: '/api/savePtd',
                        method: 'POST',
                        data: JSON.stringify({ items: dataSaveAble ,ptd:tablePTDClickedlabel}),
                        contentType: 'application/json; charset=UTF-8',
                        success: function (res) {
                            if (res.status == 1) {
                                Swal.fire('Data Disimpan!', 'PTD Berhasil Diupdate', 'success');
                            } else {
                                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan.', 'error');
                            }
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            Swal.fire('Error', 'Tidak bisa menyimpan saat ini.', 'error');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).find('.spinner-border').remove();
                        }
                    });
                }
            });
        });

        $('#routesTable tbody').on('dblclick', 'tr.clickable-row', function () {
            const id = $(this).data('id');
            const date = $(this).data('date');
            $('#modalDtActual').modal('show'); 
        });

        $('#routesTable tbody').on('click', 'tr.clickable-row', function (e) {

            const $tr = $(this);
            const id = $tr.data('id');          
            const date = $tr.data('date');      

            $('#routesTable tbody tr').removeClass('active');
            $tr.addClass('active');

            if ($tr.data('loading')) return;
            $tr.data('loading', true);

            $.ajax({
                url: `/api/getActualDetail/${id}`,   
                method: 'POST',
                dataType: 'json',
                data: { date: date },            
                beforeSend: function () {
                    $tr.css('opacity', 0.6);
                },
                success: function (resp) {
                    const ptd_id = $('#mySelect').val();
                    if (typeof window.loadMarkersForRoute === 'function') {
                        window.loadMarkersForRouteActual(ptd_id, resp);
                    } else {
                        console.error("Function loadMarkersForRoute() tidak ditemukan di global scope.");
                    }
                    var items = resp;
                    var list = Array.isArray(items) ? items : Object.values(items);

                    const esc = s => String(s ?? '').replace(/[&<>"']/g, m =>
                      ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])
                      );

                    var rows = '';
                    $.each(items, function(_, item){
                        rows += `
                        <tr class="text-center" data-id="${esc(item.RouteNumber)}">
                        <td>${esc(item.RoadSegment)}</td>
                        <td>${esc(item.WayPoint)}</td>
                        <td>${esc(item.RouteNumber)}</td>
                        <td>${esc(item.Latitude)}</td>
                        <td>${esc(item.Longitude)}</td>
                        </tr>`;
                    });

                    var html = `
                    <div class="tableFixHead" style="max-height:450px; overflow-y:auto;">
                    <table class="table table-bordered table-striped table-sm mb-0" style="table-layout:fixed;">
                    <colgroup>
                    <col style="width:180px"><col style="width:240px"><col style="width:140px">
                    <col style="width:160px"><col style="width:130px">
                    </colgroup>
                    <thead class="thead-dark">
                    <tr class="text-center">
                    <th>Road Segment</th>
                    <th>Waypoint</th>
                    <th>Route Number</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    </tr>
                    </thead>
                    <tbody>
                    ${rows}
                    </tbody>
                    </table>
                    </div>`;

                    $('#PtdActualModalContent').html(html);

                // loadTableMarkerActual(resp)
                // console.log('Detail response:', resp);
            },
            error: function (xhr) {
                console.error('Load detail failed', xhr);
                alert('Gagal memuat detail. Coba lagi.');
            },
            complete: function () {
                $tr.data('loading', false).css('opacity', 1);
            }
        });
        });

        $('#routesTable tbody').on('click', '.detail-row', function (e) {
            e.stopPropagation();  
            $(this).closest('tr.clickable-row').trigger('click');
        });
    // don't let trash icon trigger the row dblclick
    // $('#roadSegmentList').on('click dblclick', '.delete-row', function (e) { e.stopPropagation(); });

    // helpers: sanitize numeric (allow digits + one dot; convert comma->dot)
    function cleanNum(val) {
      if (val == null) return '';
      let s = String(val).trim().replace(',', '.');
      // keep only digits and dots, and only the first dot
      let out = '', dot = false;
      for (const ch of s) {
        if (ch >= '0' && ch <= '9') out += ch;
        else if (ch === '.' && !dot) { out += ch; dot = true; }
    }
    return out;
}
function isValidNum(s) {
  if (s === '') return false;
  const n = Number(s);
  return Number.isFinite(n) && n >= 0;
}

    // prevent typing e/E/+/- in number inputs (legacy handlers kept)
    $(document).on('keydown', '#bsEmpty, #bsLoaded', function(e){
      if (['e','E','+','-'].includes(e.key)) e.preventDefault();
  });

    // live sanitize on input (legacy handlers kept)
    $(document).on('input', '#bsEmpty, #bsLoaded', function(){
      const cleaned = cleanNum(this.value);
      if (this.value !== cleaned) this.value = cleaned;
      this.classList.remove('is-invalid');
  });

    // === NEW strict numeric handling for .numeric-only inputs ===
    function normalizeNum(val) {
      if (val == null) return '';
      let s = String(val).trim().replace(',', '.');
      s = s.replace(/[^0-9.]/g, '');
      const parts = s.split('.');
      if (parts.length > 2) s = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, '');
      return s;
  }
  function isValidNonNegative(val) {
      if (val === '') return false;
      const n = Number(val);
      return Number.isFinite(n) && n >= 0;
  }
    // block bad characters early (typing)
    $(document).on('beforeinput', '.numeric-only', function (e) {
      if (e.inputType === 'insertText' && !/[0-9.,]/.test(e.data)) e.preventDefault();
  });
    // sanitize on any change
    $(document).on('input', '.numeric-only', function () {
      const cleaned = normalizeNum(this.value);
      if (this.value !== cleaned) this.value = cleaned;
      this.classList.remove('is-invalid');
  });
    // flag invalid on blur
    $(document).on('blur', '.numeric-only', function () {
      const v = normalizeNum(this.value);
      if (!isValidNonNegative(v)) this.classList.add('is-invalid');
  });

    // dblclick row -> open modal with info + inputs
    $('#roadSegmentList tbody').on('dblclick', 'tr', function () {
        const $row = $(this);
        const $td  = $row.find('td');
        $('#infoRoadSegment').text($td.eq(0).text().trim());
        $('#infoRouteNumber').text($td.eq(1).text().trim());
        $('#infoLongitude').text($td.eq(2).text().trim());
        $('#infoLatitude').text($td.eq(3).text().trim());
        $('#infoDistance').text($td.eq(4).text().trim());
        $('#infoWaypoint').text($td.eq(5).text().trim());
        $('#infoElevasi').text($td.eq(6).text().trim());

        $('#bsEmpty').val($td.eq(7).text().trim()).removeClass('is-invalid');
        $('#bsLoaded').val($td.eq(8).text().trim()).removeClass('is-invalid');

        $('#__bsRowIndex').val($row.index());

        bootstrap.Modal.getOrCreateInstance(document.getElementById('bsModal')).show();
    });

    // save -> validate numeric; write back; update average
    $('#saveBs').on('click', function () {
      const rowIndex = parseInt($('#__bsRowIndex').val(), 10);
      const $row = $('#roadSegmentList tbody tr').eq(rowIndex);
      if (!$row.length) return;

      const emptyVal  = normalizeNum($('#bsEmpty').val());
      const loadedVal = normalizeNum($('#bsLoaded').val());

      let ok = true;
      if (!isValidNonNegative(emptyVal)) { $('#bsEmpty').addClass('is-invalid'); ok = false; }
      if (!isValidNonNegative(loadedVal)) { $('#bsLoaded').addClass('is-invalid'); ok = false; }
      if (!ok) return;

      const $td = $row.find('td');
      $td.eq(7).text(emptyVal);
      $td.eq(8).text(loadedVal);

      // optional: update B.S.Avg (col 9)
      const eNum = parseFloat(emptyVal), lNum = parseFloat(loadedVal);
      if (!isNaN(eNum) && !isNaN(lNum)) $td.eq(9).text(((eNum + lNum) / 2).toFixed(2));

      // refresh any dependent views
      if (typeof loadTableMarkers === 'function') loadTableMarkers();

      bootstrap.Modal.getInstance(document.getElementById('bsModal')).hide();
  });
</script>


<?php elseif ($func === 'loadRoadSegmentMaster'): ?>

    <table id="sourceTable" class="table table-bordered table-striped table-sm mb-0">
        <thead class="thead-dark">
            <tr>
                <th style="width:48px;">#</th>
                <th>Road Segment</th>
                <th>Waypoint</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Elevasi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $value): ?>
                <tr>
                    <td class="text-center"><input type="checkbox" class="rowCheckbox"></td>
                    <td><?= $value->RoadSegment ?></td>
                    <td><?= $value->RoadDetailSegment ?></td>
                    <td><?= $value->latitude ?></td>
                    <td><?= $value->longitude ?></td>
                    <td><?= $value->Elevasi ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function () {
            let max_route_number_now = `<?= $ptd_id ?>`;

            $('#addSelected').off('click').on('click', function () {
                $('#sourceTable .rowCheckbox:checked').each(function () {
                    const $row = $(this).closest('tr');
                    const segment  = $row.find('td:eq(1)').text().trim();
                    const waypoint = $row.find('td:eq(2)').text().trim();
                    const latitude = $row.find('td:eq(3)').text().trim();
                    const longitude = $row.find('td:eq(4)').text().trim();
                    const elevasi = $row.find('td:eq(5)').text().trim();

                    const exists = $('#roadSegmentList tbody tr').filter(function () {
                        return $(this).find('td:eq(0)').text().trim() === segment;
                    }).length > 0;

                    max_route_number_now = Number(max_route_number_now) + 1;

                    if (!exists) {
                        $('#roadSegmentList tbody').append(`
                            <tr style="text-align:center">
                            <td>${segment}</td>
                            <td>${max_route_number_now}</td>
                            <td>${longitude}</td>
                            <td>${latitude}</td>
                            <td>0</td>
                            <td>${waypoint}</td>
                            <td>${elevasi}</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td><i class="lni lni-trash-can delete-row" style="color:red; cursor:pointer;"></i></td>
                            </tr>
                            `);
                    }
                    $(this).prop('checked', false);
                });

                $('#myModal').modal('hide');
                if (typeof loadTableMarkers === 'function') loadTableMarkers();
            });

        });



    </script>

<?php endif; ?>
