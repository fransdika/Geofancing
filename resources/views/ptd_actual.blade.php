<?php 
if ($page=='ptd_actual') {
    ?>
    <style>
        #routesTable tbody tr { cursor: pointer; }
        #routesTable tbody tr:hover { background:#f7f9fc; }
        #routesTable tbody tr.active { background:#e8f0fe; } 
    </style>
    <table class="table table-condensed table-hover" id="routesTable">
        <thead>
            <tr style="text-align:center">
                <td>Date</td>
                <td>Road Segment</td>
                <td>Route dilewati</td>
                <td>Aksi</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $keyPtdRoute => $valuePtdRoute): 
                $arr = explode(",", $valuePtdRoute->RoadSegment);
                $result = $arr[0] . " => " . $arr[1] . " => ..... => " . end($arr);
                ?>
                <tr class="clickable-row" data-id="<?= $valuePtdRoute->id ?>"  data-date="<?= htmlspecialchars($valuePtdRoute->Date) ?>">
                    <td><?=$valuePtdRoute->Date ?></td>
                    <td><?=$result ?></td>
                    <td style="text-align: center;"><?=$valuePtdRoute->MostRoute ?></td>
                    <td style="text-align: center;">
                        <i class="fa-solid fa-info detail-row" style="color:red; cursor:pointer;"></i>
                    </td>
                </tr>

            <?php endforeach ?>
        </tbody>
    </table>


    <script type="text/javascript">
        

  // Row click handler (event delegation)
  $('#routesTable tbody').on('click', 'tr.clickable-row', function (e) {
    // If you want icon click to behave the same, keep as-is.
    // If you want only row (not icon), you can ignore when target is .detail-row.

    const $tr = $(this);
    const id = $tr.data('id');          
    const date = $tr.data('date');      
    // UI feedback
    $('#routesTable tbody tr').removeClass('active');
    $tr.addClass('active');

    // Optional: disable repeated clicks while loading
    if ($tr.data('loading')) return;
    $tr.data('loading', true);

    // Call your endpoint (adjust URL & method as needed)
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
            window.loadMarkersForRoute(ptd_id, resp);
        } else {
            console.error("Function loadMarkersForRoute() tidak ditemukan di global scope.");
        }
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

  // (Optional) If you want the red info icon to trigger the same action:
  $('#routesTable tbody').on('click', '.detail-row', function (e) {
    e.stopPropagation();   // don’t double-trigger
    $(this).closest('tr.clickable-row').trigger('click');
  });
    </script>

    <?php
}

?>
