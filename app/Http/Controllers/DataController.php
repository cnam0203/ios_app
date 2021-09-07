<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function getMenu(Request $request) {
        error_log('Get Menu');

        $user_id = $request['user']['id'];
        $pdo = DB::getPdo();
        
        $sql = "select u.name as name, u.uri as uri, r.name as report_name from uri u, report r
        where u.report_id = r.id and u.report_id in (
            select n.report_id
            from user_report_right n
            where n.user_id = :userID);";

        $param = [':userID' => $user_id];

        $stmt = $pdo->prepare($sql);
        $stmt->execute($param);

        $menu = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return response()->json([
            'status' => true,
            'data' => ['menu' => $menu]
        ]);
    }

    public function getRandomNumber(Request $request){
        return response()->json([
            'status' => true,
            'data' => rand(10,100)
        ]);
    }

    public function getChart(Request $request, $game, $report){
        error_log('Get Charts');
        $charts = [
            [
                'chart' => ['type' => 'column'],
                'title' => ['text' => 'Total daily '.$report.' in', 
                            'style' => ['color' => 'black', 'fontSize' => '20', 'fontWeight' => 'bold']],
                'xAxis' => ['categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            'crosshair' => true],
                'yAxis' => ['title' => ''],
                'series' => [
                    ['name' => 'Tokyo', 'data' => [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]],
                    ['name' => 'New York', 'data' => [83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3]]
                ],
                'credits' => ['enabled' => false],
            ], [
                'title' => ['text' => 'Total daily '.$report.' out', 
                            'style' => ['color' => 'black', 'fontSize' => '20', 'fontWeight' => 'bold']],
                'yAxis' => ['title' => ''],
                'series' => [
                    ['name' => 'Tokyo', 'data' => [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]],
                    ['name' => 'New York', 'data' => [83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3]]
                ],
                'credits' => ['enabled' => false],
            ]
        ];

        return response()->json([
            'status' => true,
            'data' => ['charts' => $charts]
        ]);
    }
}
