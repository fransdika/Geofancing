<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MapController;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


// Route::get('/', [MapController::class, 'index']);
Route::get('/api/markers', [MapController::class, 'getMarkers']);
Route::get('/api/markersDB/{ptd_id}', [MapController::class, 'getMarkersDB']);
Route::get('/api/units', [MapController::class, 'getUnit']);
Route::get('/api/ptds', [MapController::class, 'getPtd']);  
Route::get('/draw', [MapController::class, 'drawObject']);
Route::get('/mapRadius', [MapController::class, 'mapRadius']);
Route::get('/mapCurvePolyline', [MapController::class, 'mapCurvePolyline']);
Route::get('/mapTilerRadius', [MapController::class, 'mapTilerRadius']);
Route::get('/ptdMasterSetUp', [MapController::class, 'indexPtd']);
Route::get('/mapGuide', [MapController::class, 'mapGuide']);
Route::get('/api/getUnitSupport/{radius}/{lat}/{lng}', [MapController::class, 'getUnitSupport']);

// CRUD
Route::get('/api/getRoadSegmentPTDs/{ptd}', [MapController::class, 'getRoadSegmentPTDs']);
Route::get('/api/getRoadSegmentMaster', [MapController::class, 'getRoadSegmentMaster']);
Route::get('/api/getPTDActual/{ptd}', [MapController::class, 'getPTDActual']);
Route::get('/api/markersActual/{ptd}', [MapController::class, 'markersActual']);
Route::get('/api/getRoadSegmentPlan', [MapController::class, 'getRoadSegmentPlan']);



Route::redirect('/', '/mapTilerRadius');


// END CRUD


Route::get('/mapTiles', function () {
    return view('mapTiles');
});


Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/test-db', function () {
    try {
        $result = DB::select('SELECT * FROM ptd_latlong');
        return response()->json($result);
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});



Route::post('/save-geojson', function (Request $request) {
    $data = $request->input('geojson');

    $json = json_encode($data);

    file_put_contents(storage_path('app/public/map_data.json'), $json);

    return response()->json(['message' => 'Saved successfully']);
});
