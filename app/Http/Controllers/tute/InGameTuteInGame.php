<?php


namespace App\Http\Controllers\tute;

use App\Http\Controllers\InGameFunction;
use Illuminate\Support\Facades\Log;

class InGameTuteInGame extends InGameTuteFunction
{
    function index($type = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->pageTitle='Tute InGame Report';

        $this->listChart = array(
            'chartTotalAcuse'=>$this->chartTotalAcuse(),
            'chartNumberAcuseByGamePlayed'=>$this->chartNumberAcuseByGamePlayed(),
            'chartNumberAcuseByUser'=>$this->chartNumberAcuseByUser(),
            'chartNumberWinTute'=>$this->chartNumberWinTute(),
            'chartNumberTuteByUser'=>$this->chartNumberTuteByUser(),
            'chartNumberUserByNoPoint'=>$this->chartNumberUserByNoPoint(),
            'chartNoPointByGamePlayed'=>$this->chartNoPointByGamePlayed(),
            'chartNumberGamePlayedByWinner1'=>$this->chartNumberGamePlayedByWinner(1),
            'chartNumberGamePlayedByWinner2'=>$this->chartNumberGamePlayedByWinner(2),
            'chartNumberGamePlayedByWinner3'=>$this->chartNumberGamePlayedByWinner(3),
            'chartNumberGamePlayedByWinner4'=>$this->chartNumberGamePlayedByWinner(4),
        );

        $this->listChartId=['chartTotalAcuse', 'chartNumberAcuseByGamePlayed', 'chartNumberAcuseByUser',
                            'chartNumberWinTute', 'chartNumberTuteByUser', 'chartNumberUserByNoPoint', 
                            'chartNoPointByGamePlayed', 'chartNumberGamePlayedByWinner1', 
                            'chartNumberGamePlayedByWinner2', 'chartNumberGamePlayedByWinner3', 'chartNumberGamePlayedByWinner4'];

        $this->addViewsToMain=json_encode(
            $this->addCharts(['chartTotalAcuse']).
            $this->addGrid('', 'gridTotalAcuse', $this->gridTotalAcuse('tableid_gridTotalAcuse'), false).
            $this->addCharts(['chartNumberAcuseByGamePlayed']).
            $this->addGrid('', 'gridNumberAcuseByGamePlayed', $this->gridNumberAcuseByGamePlayed('tableid_gridNumberAcuseByGamePlayed'), false).
            $this->addCharts(['chartNumberAcuseByUser']).
            $this->addGrid('', 'gridNumberAcuseByUser', $this->gridNumberAcuseByUser('tableid_gridNumberAcuseByUser'), false).
            $this->addCharts(['chartNumberWinTute']).
            $this->addGrid('', 'gridNumberWinTute', $this->gridNumberWinTute('tableid_gridNumberWinTute'), false).
            $this->addCharts(['chartNumberTuteByUser']).
            $this->addGrid('', 'gridNumberTuteByUser', $this->gridNumberTuteByUser('tableid_gridNumberTuteByUser'), false).
            $this->addCharts(['chartNumberUserByNoPoint']).
            $this->addGrid('', 'gridNumberUserByNoPoint', $this->gridNumberUserByNoPoint('tableid_gridNumberUserByNoPoint'), false).
            $this->addCharts(['chartNoPointByGamePlayed']).
            $this->addGrid('', 'gridNoPointByGamePlayed', $this->gridNoPointByGamePlayed('tableid_gridNoPointByGamePlayed'), false).
            $this->addCharts(['chartNumberGamePlayedByWinner1']).
            $this->addGrid('', 'gridNumberGamePlayedByWinner1', $this->gridNumberGamePlayedByWinner('tableid_gridNumberGamePlayedByWinner1', 1), false).
            $this->addCharts(['chartNumberGamePlayedByWinner2']).
            $this->addGrid('', 'gridNumberGamePlayedByWinner2', $this->gridNumberGamePlayedByWinner('tableid_gridNumberGamePlayedByWinner2', 2), false).
            $this->addCharts(['chartNumberGamePlayedByWinner3']).
            $this->addGrid('', 'gridNumberGamePlayedByWinner3', $this->gridNumberGamePlayedByWinner('tableid_gridNumberGamePlayedByWinner3', 3), false).
            $this->addCharts(['chartNumberGamePlayedByWinner4']).
            $this->addGrid('', 'gridNumberGamePlayedByWinner4', $this->gridNumberGamePlayedByWinner('tableid_gridNumberGamePlayedByWinner4', 4), false).
            ''
        );

        return parent::__index($type);
    }

    private function chartTotalGamePlayed(){
        $sql = $this->getSQLTotalGamePlayed();
        $options = [    'title' => "Total Game Played",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    private function chartTotalAcuse(){
        $sql = $this->getSQLTotalAcuse();
        $options = [    'title' => "Total Acuse",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    private function chartNumberAcuseByGamePlayed(){
        $sql = $this->getSQLNumberAcuseByGamePlayed();
        $options = [    'title' => "Số lượng Acuse trung bình 1 ván",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    private function chartNumberAcuseByUser(){
        $sql = $this->getSQLNumberAcuseByUser();
        $options = [    'title' => "Số lượng Acuse trung bình 1 User",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    private function chartNumberWinTute(){
        $sql = $this->getSQLNumberWinTute();
        $options = [    'title' => "Số trận thắng TUTE",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    private function chartNumberTuteByUser(){
        $sql = $this->getSQLNumberTuteByUser();
        $options = [    'title' => "Trung bình TUTE/user",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    private function chartNumberUserByNoPoint(){
        $sql = $this->getSQLNumberUserByNoPoint();
        $options = [    'title' => "Số User No Point",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    private function chartNoPointByGamePlayed(){
        $sql = $this->getSQLNoPointByGamePlayed();
        $options = [    'title' => "Trung bình No Point/ván chơi",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    private function chartNumberGamePlayedByWinner($numWinner){
        $sql = $this->getSQLNumberGamePlayedByWinner($numWinner);
        $options = [    'title' => "Số trận ".(string)$numWinner." người thắng",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    // create grid script
    private function gridTotalAcuse($tid){
        $sql = $this->getSQLTotalAcuse(false);
        return $this->createGrid($sql, $tid);
    }

    private function gridNumberAcuseByGamePlayed($tid){
        $sql = $this->getSQLNumberAcuseByGamePlayed(false);
        $datatype = ['NumberAcuse' => 'DEC2'];
        return $this->createGrid($sql, $tid, $datatype);
    }

    private function gridNumberAcuseByUser($tid){
        $sql = $this->getSQLNumberAcuseByUser(false);
        $datatype = ['NumberAcuse' => 'DEC2'];
        return $this->createGrid($sql, $tid, $datatype);
    }

    private function gridNumberWinTute($tid){
        $sql = $this->getSQLNumberWinTute(false);
        return $this->createGrid($sql, $tid);
    }

    private function gridNumberTuteByUser($tid){
        $sql = $this->getSQLNumberTuteByUser(false);
        $datatype = ['NumberTute' => 'DEC2'];
        return $this->createGrid($sql, $tid, $datatype);
    }

    private function gridNumberUserByNoPoint($tid){
        $sql = $this->getSQLNumberUserByNoPoint(false);
        return $this->createGrid($sql, $tid);
    }

    private function gridNoPointByGamePlayed($tid){
        $sql = $this->getSQLNoPointByGamePlayed(false);
        $datatype = ['NumberNoPoint' => 'DEC2'];
        return $this->createGrid($sql, $tid, $datatype);
    }
    
    private function gridNumberGamePlayedByWinner($tid, $numberWinner){
        $sql = $this->getSQLNumberGamePlayedByWinner($numberWinner, false);
        return $this->createGrid($sql, $tid);
    }
    // get sql query
    private function getSQLTotalAcuse($isChart=true){
        $table = "tute_report_by_ingame";
        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, NumberAcuse from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by ReportDate ".$sort;
        return $sql;
    }

    private function getSQLNumberAcuseByGamePlayed($isChart=true){
        $table1 = "tute_report_by_ingame";
        $table2 = "tute_report_by_gameplayed";
        $order = ($isChart)?"asc":"desc";

        $sql1 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, NumberAcuse from ".$table1." as i "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by i.ReportDate ".$order.") as ii";
        $sql2 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, SUM(gameplayed) as GamePlayed  from ".$table2." as o "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY o.ReportDate order by o.ReportDate ".$order.") as oo";

        $sql = "select ii.ReportDate, round(ii.NumberAcuse/oo.GamePlayed, 2) as NumberAcuse from (".$sql1." join ".$sql2." on ii.ReportDate = oo.ReportDate)";

        return $sql;
    }

    private function getSQLNumberAcuseByUser($isChart=true){
        $table1 = "tute_report_by_ingame";
        $table2 = "tute_report_by_gameplayed";
        $order = ($isChart)?"asc":"desc";

        $sql1 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, NumberAcuse from ".$table1." as i "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by i.ReportDate ".$order.") as ii";
        $sql2 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, SUM(NumberUser) as NumberUser  from ".$table2." as o "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY o.ReportDate order by o.ReportDate ".$order.") as oo";

        $sql = "select ii.ReportDate, round(ii.NumberAcuse/oo.NumberUser,2) as NumberAcuse from (".$sql1." join ".$sql2." on ii.ReportDate = oo.ReportDate)";

        return $sql;
    }

    private function getSQLNumberWinTute($isChart=true){
        $table = "tute_report_by_ingame";
        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, NumberWinTute from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by ReportDate ".$sort;
        return $sql;
    }

    private function getSQLNumberTuteByUser($isChart=true){
        $table1 = "tute_report_by_ingame";
        $table2 = "tute_report_by_gameplayed";
        $order = ($isChart)?"asc":"desc";

        $sql1 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, NumberTute from ".$table1." as i "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by i.ReportDate ".$order.") as ii";
        $sql2 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, SUM(NumberUser) as NumberUser  from ".$table2." as o "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY o.ReportDate order by o.ReportDate ".$order.") as oo";

        $sql = "select ii.ReportDate, round(ii.NumberTute/oo.NumberUser, 2) as NumberTute from (".$sql1." join ".$sql2." on ii.ReportDate = oo.ReportDate)";

        return $sql;
    }

    private function getSQLNumberUserByNoPoint($isChart=true){
        $table = "tute_report_by_ingame";
        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, IFNULL(NumberNoPoint, 0) as NumberNoPoint from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by ReportDate ".$sort;
        return $sql;
    }

    private function getSQLNoPointByGamePlayed($isChart=true){
        $table1 = "tute_report_by_ingame";
        $table2 = "tute_report_by_gameplayed";
        $order = ($isChart)?"asc":"desc";

        $sql1 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, NumberNoPoint from ".$table1." as i "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by i.ReportDate ".$order.") as ii";
        $sql2 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, SUM(GamePlayed) as GamePlayed  from ".$table2." as o "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY o.ReportDate order by o.ReportDate ".$order.") as oo";

        $sql = "select ii.ReportDate, round(ii.NumberNoPoint/oo.GamePlayed, 2) as NumberNoPoint from (".$sql1." join ".$sql2." on ii.ReportDate = oo.ReportDate)";

        return $sql;
    }

    private function getSQLNumberGamePlayedByWinner($numberWinner, $isChart=true){
        $table = "tute_report_by_ingame";
        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';

        if ($numberWinner == 1)
            $column = 'NumberWinner1';
        elseif ($numberWinner == 2)
            $column = 'NumberWinner2';
        elseif ($numberWinner == 3)
            $column = 'NumberWinner3';
        elseif ($numberWinner == 4)
            $column = 'NumberWinner4';
        else
            $column = 'NumberWinner4';
            
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, IFNULL(".$column.",0) as ".$column." from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by ReportDate ".$sort;
        return $sql;
    }
}