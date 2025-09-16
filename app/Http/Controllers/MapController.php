<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Schema;


class MapController extends Controller
{
    public function index()
    {
        return view('map');
    }

    public function mapRadius()
    {
        return view('mapRadius');
    }

    public function mapTilerRadius()
    {
        return view('mapTilerRadius');
    } 

    public function mapGuide()
    {
        return view('mapGuide');
    }

    public function getMarkers()
    {
        $path = storage_path('app/ptd_latlong.csv');
        $data=[];
        if (file_exists($path)) {
            $file = fopen($path, 'r');
            $headers = fgetcsv($file); // or use delimiter "\t" if needed
            $data = [];

            while (($row = fgetcsv($file)) !== false) {
                $data[] = array_combine($headers, $row);
            }

            fclose($file);

            // Now $data is an array of all rows
            // dd($data);
        }

        return response()->json($data);
        // return response()->json([
        //     ['lat' => 51.505, 'lng' => -0.09, 'popup' => 'London'],
        //     ['lat' => 48.8566, 'lng' => 2.3522, 'popup' => 'Paris'],
        //     ['lat' => 40.7128, 'lng' => -74.0060, 'popup' => 'New York']
        // ]);
    }
    public function getMarkersDB($ptd_id)
    {
        if ($ptd_id==0) {
            $sql="SELECT * FROM getLatestPtd()";
        }else{
            $sql="SELECT * FROM getLatestPtd() WHERE ID IN($ptd_id)";
        }

        // $data = DB::select("SELECT * FROM ptd_latlong");
        $data = DB::select($sql);
        return response()->json($data);
    }

    public function getPtd()
    {
        // $sql="SELECT ROW_NUMBER() OVER(ORDER BY PTD) AS ID,PTD FROM header GROUP BY PTD"
        $sql="SELECT * from getComparisonPTDPlanActual()";
        $data = DB::select($sql);

        // $formatted = array_map(function ($item) {
        //     return [
        //         'id' => $item->ID,
        //         'text' => $item->PTD
        //     ];
        // }, $data);
        // array_unshift($formatted, [
        //     'id' => '0',
        //     'text' => 'ALL PTD'
        // ]);
        return response()->json($data);
    }
    public function getRoadSegmentPlan()
    {
        // $sql="SELECT ROW_NUMBER() OVER(ORDER BY PTD) AS ID,PTD FROM header GROUP BY PTD"
        $sql="SELECT distinct RoadSegment from header";
        $data = DB::select($sql);

        // $formatted = array_map(function ($item) {
        //     return [
        //         'id' => $item->ID,
        //         'text' => $item->PTD
        //     ];
        // }, $data);
        // array_unshift($formatted, [
        //     'id' => '0',
        //     'text' => 'ALL PTD'
        // ]);
        return response()->json($data);
    }





    public function getUnit()
    {

        return response()->json([
            ['LAT' => 115.493, 'LNG' => -2.22422, 'Unit' => 'Unit 1'],
            ['LAT' => 115.481, 'LNG' => -2.23006, 'Unit' => 'Unit 2'],
        ]);
    }
    public function drawObject()
    {

        return view('drawMap', []);
    }

    public function getUnitSupport($radius,$latitude,$longitude)
    {
        $data = DB::select("SELECT * FROM getUnitSupport($radius,$latitude,$longitude)");
        return response()->json($data);
    }



    // ##################################################################### CRUD ######################################################


    public function indexPtd()
    {
        return view('ptdMasterSetUp');
        // return view('ptdMasterSetUpCurveLine');
    }



    public function getRoadSegmentPTDs($ptd)
    {
        $ptd_ex=explode('_', $ptd);
        $sql="SELECT * FROM getRoadSegment() WHERE id IN($ptd_ex[0]) ORDER BY RouteNumber";
        $data = DB::select($sql);
        $status=1;        
        if (count(explode(',',$ptd_ex[0]))>1) {
            $data=[];
            $status=0;
        }

        $sqlActual="SELECT * FROM GetPTDActual() WHERE REPLACE(PtdName,'Via','From')='".$ptd_ex[1]."' ORDER BY MostRoute DESC";
        // echo $sqlActual;
        $dataActual = DB::select($sqlActual);


        $r['data'] = $data;
        $r['dataActual'] = $dataActual;
        $r['func'] = 'loadRoadSegmentPtd';
        $r['status'] = $status;
        return view('ajax_ptd', $r);
    } 
    public function getRoadSegmentMaster(Request $request)
    {
        $sql="SELECT RoadSegment,RoadDetailSegment,CONVERT(DECIMAL(16,8),latitude) AS latitude,CONVERT(DECIMAL(16,8),longitude) AS longitude,ISNULL(Elevasi,0) AS Elevasi FROM header WHERE Latitude IS NOT NULL  AND Longitude IS NOT NULL  GROUP BY RoadSegment,RoadDetailSegment,latitude,longitude,Elevasi";
        $data = DB::select($sql);
        $r['data'] = $data;
        $r['func'] = 'loadRoadSegmentMaster';
        $r['ptd_id'] = $request->ptd_id;
        return view('ajax_ptd', $r);
    }
    public function getPTDActual($ptd)
    {
        $sql="SELECT * FROM ptdActual WHERE PtdName='$ptd' ORDER BY id";
        // echo $sql;
        $data = DB::select($sql);
        $r['data'] = $data;
        $r['func'] = 'ptd_actual';
        return view('ptd_actual', $r);
    }


    public function GetActualDetail($ptd_id)
    {
        // $sql="SELECT * FROM GetLatestActualPTD($ptd_id)";
        $sql="SELECT RoadSegment,WayPoint,RouteNumber,Latitude,Longitude FROM ptdActual WHERE id='$ptd_id'  ";
        $data = DB::select($sql);
        return response()->json($data);
    }
    public function markersActual($ptd_id)
    {
        $sql="SELECT * FROM getActualByPTD($ptd_id)";
        $data = DB::select($sql);
        return response()->json($data);
    }




    public function SavePtd(Request $request)
    {
         $items = $this->extractItems($request);

        if (!is_array($items) || empty($items)) {
            return response()->json([
                'ok' => false,
                'error' => 'Invalid or missing items. Send { "items": [ ... ] } or raw [ ... ] JSON.'
            ], 400);
        }

        // Keep budgets and other fields by merging normalize() over the original item.
        $norm = array_map(function ($item) {
            return array_replace($item, $this->normalize($item));
        }, $items);


        $date = $norm[0]['Date'] ?? null;
        $ptd  = $norm[0]['PTD']  ?? null;

        // === Build payload for `header` (no budget columns) ===
        $headerCols = Schema::getColumnListing('header');                 // whitelist actual columns
        $headerRows = array_map(function (array $r) use ($headerCols) {
            $row = array_intersect_key($r, array_flip($headerCols));      // drop any keys not in header
            return $row;
        }, $norm);

        // === Build payload for `HeaderPlan` (with budgets) ===
        // Assume HeaderPlan.RoadSegment is INT (your earlier SQL error showed that).
        $planRows = array_map(function (array $r) {
            $empty  = isset($r['BudgetSpeedEmpty'])  && $r['BudgetSpeedEmpty']  !== '' ? (float)$r['BudgetSpeedEmpty']  : 0;
            $loaded = isset($r['BudgetSpeedLoaded']) && $r['BudgetSpeedLoaded'] !== '' ? (float)$r['BudgetSpeedLoaded'] : 0;
            $avg    = isset($r['BudgetSpeedAVG'])    && $r['BudgetSpeedAVG']    !== '' ? (float)$r['BudgetSpeedAVG']    : (($empty + $loaded) / 2);

            return [
                'SiteId'            => isset($r['SiteId']) ? (int)$r['SiteId'] : null,
                'Date'              => $r['Date'] ?? null,
                'PTD'               => $r['PTD'] ?? null,
                'RoadSegment'       => isset($r['RoadSegment']) ? $r['RoadSegment'] : null,
                'Waypoint'         => isset($r['waypoints']) ? $r['waypoints'] : null,
                'Elevasi'           => $r['Elevasi'] ?? null,
                'BudgetSpeedEmpty'  => $empty,
                'BudgetSpeedLoaded' => $loaded,
                'BudgetSpeedAVG'    => $avg,
            ];
        }, $norm);

        DB::beginTransaction();
        try {
            if ($ptd && $date) {
                DB::table('header')->where('PTD', $ptd)->where('Date', $date)->delete();
                DB::table('HeaderPlan')->where('PTD', $ptd)->where('Date', $date)->delete();
            }

            if (!empty($headerRows)) {
                foreach (array_chunk($headerRows, 500) as $chunk) {
                    DB::table('header')->insert($chunk);
                }
            }

            if (!empty($planRows)) {
                foreach (array_chunk($planRows, 500) as $chunk) {
                    DB::table('HeaderPlan')->insert($chunk);
                }
            }

            DB::commit();
            return response()->json([
                'status'               => 1,
                'inserted_header'      => count($headerRows),
                'inserted_header_plan' => count($planRows),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 500);
        }
    }


    public function saveActualPlan(Request $request)
    {
       $id_ptdActual = $request->id;
        $nowDate      = Carbon::now('Asia/Singapore')->toDateString();

        try {
            DB::beginTransaction();

            // Delete existing rows that match the PTD of this id and same date
            $deleteSql = "
                DELETE h
                FROM header h
                INNER JOIN ptdActual p ON REPLACE(p.PTDName,'Via','From') = h.PTD
                WHERE p.id = ? AND h.[Date] = ?
            ";
            DB::statement($deleteSql, [$id_ptdActual, $nowDate]);

            // Insert new row(s)
            $insertSql = "
                INSERT INTO header ([SiteId], [Date], [PTD], [RoadSegment], RoadDetailSegment, [RouteNumber], [Latitude], [Longitude])
                SELECT ?, ?, REPLACE(PTDName,'Via','From') AS PTDName, RoadSegment, Waypoint, RouteNumber, Latitude, Longitude
                FROM ptdActual WHERE id = ?
            ";
            $inserted = DB::affectingStatement($insertSql, [2010, $nowDate, $id_ptdActual]);

            DB::commit();

            return response()->json([
                'status'   => 1,
                'deleted'  => 'ok',
                'inserted' => $inserted
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'error'  => $e->getMessage()
            ], 500);
        }

    }


    private function extractItems(Request $request)
    {
        // a) If sent correctly: { items: [...] }
        $items = $request->input('items');

        // b) If items is a JSON string: "{...}"
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $items = $decoded;
            }
        }

        // c) If items missing, maybe raw body is the array itself: [...]
        if (!is_array($items)) {
            $raw = $request->getContent(); // raw JSON body
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Support either {items:[...]} or just [...]
                if (isset($decoded['items'])) {
                    $items = $decoded['items'];
                } else {
                    $items = $decoded; // assume it’s the array directly
                }
            }
        }

        return $items;
    }

    private function normalize(array $r)
    {
        $nn = function ($v) {
            if ($v === null) return null;
            if (is_string($v) && strtoupper($v) === 'NULL') return null;
            return $v;
        };
        $toInt = function ($v) {
            return ($v === null || $v === '' || strtoupper((string)$v) === 'NULL') ? null : (int)$v;
        };
        $toFloat = function ($v) {
            return ($v === null || $v === '' || strtoupper((string)$v) === 'NULL') ? null : (float)$v;
        };

        $now         = \Carbon\Carbon::now('Asia/Singapore');
        $nowDate     = $now->toDateString();          // YYYY-MM-DD
        $nowDateTime = $now->format('Y-m-d H:i:s.v'); // YYYY-MM-DD HH:MM:SS.mmm

        // start from the original item so we KEEP all keys (Budget*, Elevasi, Waypoints, etc.)
        $out = $r;

        // override/normalize fields you care about
        $out['SiteId']      = isset($r['SiteId']) ? (string)$r['SiteId'] : '';
        $out['Date']        = $nowDate;
        $out['PTD']         = $nn($r['PTD'] ?? null);
        $out['PTDLongName'] = $nn($r['PTDLongName'] ?? null);
        $out['PTDKey']      = $nn($r['PTDKey'] ?? null);
        $out['RoadSegment'] = $nn($r['RoadSegment'] ?? null);
        $out['RoadDetailSegment'] = $nn($r['Waypoints'] ?? null);

        $out['RouteNumber']   = $toInt($r['RouteNumber'] ?? null);
        $out['RouteX']        = $toFloat($r['RouteX'] ?? null);
        $out['RouteY']        = $toFloat($r['RouteY'] ?? null);
        $out['Latitude']      = $toFloat($r['Latitude'] ?? null);
        $out['Longitude']     = $toFloat($r['Longitude'] ?? null);
        $out['Status']        = $nn($r['Status'] ?? null);
        $out['RouteGroup']    = $toInt($r['RouteGroup'] ?? null);
        $out['RouteStsGroup'] = $toInt($r['RouteStsGroup'] ?? null);
        $out['StatusChange']  = $toInt($r['StatusChange'] ?? null);

        $out['LoadUtcDate'] = $nowDateTime;
        $out['CreateDate']  = $nowDateTime;
        $out['ModifDate']   = null;

        // explicitly normalize budgets if present (but KEEP provided values)
        if (array_key_exists('BudgetSpeedEmpty', $out)) {
            $out['BudgetSpeedEmpty'] = $toFloat($out['BudgetSpeedEmpty']);
        }
        if (array_key_exists('BudgetSpeedLoaded', $out)) {
            $out['BudgetSpeedLoaded'] = $toFloat($out['BudgetSpeedLoaded']);
        }
        if (array_key_exists('BudgetSpeedAVG', $out)) {
            $out['BudgetSpeedAVG'] = $toFloat($out['BudgetSpeedAVG']);
        } else {
            if ($out['BudgetSpeedEmpty'] !== null && $out['BudgetSpeedLoaded'] !== null) {
                $out['BudgetSpeedAVG'] = ($out['BudgetSpeedEmpty'] + $out['BudgetSpeedLoaded']) / 2;
            }
        }

        // normalize name casing if you sometimes send Waypoints/waypoints
        if (isset($out['Waypoints']) && !isset($out['waypoints'])) {
            $out['waypoints'] = $out['Waypoints'];
        }

        return $out;
    }



}
