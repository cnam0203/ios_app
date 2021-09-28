<?php


namespace App\Http\Controllers\tute;

use App\Http\Controllers\InGameFunction;
use Illuminate\Support\Facades\Log;

class InGameTuteBot extends InGameTuteFunction
{
    function index($type = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->pageTitle='Tute InGame Report';

        $this->listChart = array(
            'chartNumberGamePlayedByBot'=>$this->chartNumberGamePlayedByBot(),
            'chartNumberGamePlayedByChannel'=>$this->chartNumberColumnTypeByChannel(0),
            'chartTotalGoldBotGiveByChannel'=>$this->chartNumberColumnTypeByChannel(1),
            'chartTotalGoldBotTakeByChannel'=>$this->chartNumberColumnTypeByChannel(2)
        );

        $this->listChartId=['chartNumberGamePlayedByBot', 'chartNumberGamePlayedByChannel',
                            'chartTotalGoldBotGiveByChannel', 'chartTotalGoldBotTakeByChannel'];

        $this->addViewsToMain=json_encode(
            $this->addCharts(['chartNumberGamePlayedByBot']).
            $this->addGrid('', 'gridNumberGamePlayedByBot', $this->gridNumberGamePlayedByBot('tableid_gridNumberGamePlayedByBot'), false).
            $this->addCharts(['chartNumberGamePlayedByChannel']).
            $this->addGrid('', 'gridNumberGamePlayedByChannel', $this->gridNumberColumnTypeByChannel('tableid_gridNumberGamePlayedByChannel',0), false).
            $this->addCharts(['chartTotalGoldBotGiveByChannel']).
            $this->addGrid('', 'gridTotalGoldBotGiveByChannel', $this->gridNumberColumnTypeByChannel('tableid_gridTotalGoldBotGiveByChannel',1), false).
            $this->addCharts(['chartTotalGoldBotTakeByChannel']).
            $this->addGrid('', 'gridTotalGoldBotTakeByChannel', $this->gridNumberColumnTypeByChannel('tableid_gridTotalGoldBotTakeByChannel',2), false).
            ''
        );

        return parent::__index($type);
    }

    private function chartNumberGamePlayedByBot(){
        $sql = $this->getSQLNumberGamePlayedByBot();
        $sql2 = $this->getSQLTotalNumberGamePlayed();
        $options = [    'title' => "Number of GamePlayed By Number Bot",
            'subtitle' =>  $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'legend_align' => 'center',
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, self::defaultSort);
    }

    private function chartNumberColumnTypeByChannel($type){
        $title = '';
        $sql = $this->getSQLNumberColumnTypeByChannel($type);

        if  ($type==0) {
            $title = "Number of GamePlayed By Channel";
            $sql2 = $this->getSQLTotalNumberGamePlayed();
        }
        elseif ($type==1) {
            $title = "Total Gold Bot Give By Channel";
            $sql2 = $this->getSQLTotalGold(0);
        }
        else {
            $title = "Total Gold Bot Take By Channel";
            $sql2 = $this->getSQLTotalGold(1);
        }

        $options = [    'title' => $title,
            'subtitle' =>  $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'legend_align' => 'center',
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, self::defaultSort);
    }

    private function gridNumberGamePlayedByBot($tid){
        $sql = $this->getSQLNumberGamePlayedByBot();
        $data = $this->getDataSQLInGame($sql);
        $grid_data = $this->pivotdata4grid_withSort($data, self::defaultRSort);
        $this->fillEmptyData($grid_data);
        return $this->createGridData($grid_data, $tid);
    }

    private function gridNumberColumnTypeByChannel($tid, $type){
        $sql = $this->getSQLNumberColumnTypeByChannel($type);
        $data = $this->getDataSQLInGame($sql);
        $grid_data = $this->pivotdata4grid_withSort($data, self::defaultRSort);
        $this->fillEmptyData($grid_data);
        return $this->createGridData($grid_data, $tid);
    }

    private function getSQLNumberGamePlayedByBot($isChart=true){
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, NumberBot, IFNULL(Sum(GamePlayed),0) as NumberGamePlayed"
        ." from tute_report_by_bot "
        ." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)
        ." GROUP BY ReportDate, NumberBot";
        return $sql;
    }

    private function getSQLTotalNumberGamePlayed($isChart=true){
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "." IFNULL(Sum(GamePlayed),0) as TotalGamePlayed from tute_report_by_bot "
        . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate";
        return $sql;
    }

    private function getSQLNumberColumnTypeByChannel($type, $isChart=true){
        $column = '';

        if ($type==0) {
            $column = " IFNULL(Sum(GamePlayed),0) as NumberGamePlayed";
        } elseif ($type==1) {
            $column = " IFNULL(Sum(GoldBotGive),0) as TotalGold";
        } else {
            $column = " IFNULL(Sum(GoldBotTake),0) as TotalGold";
        }

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, 
            (case when ChannelID = '0' then 'BEGINNER'
                  when ChannelID = '1' then 'AMATEUR'
                  when ChannelID = '2' then 'EXPERT'
                  else 'MASTER' end) as ChannelID,"
        .$column
        ." from tute_report_by_bot "
        ." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)
        ." GROUP BY ReportDate, ChannelID";
        return $sql;
    }

    private function getSQLTotalGold($type, $isChart=true){
        $column = '';

        if ($type==0) {
            $column = "IFNULL(Sum(GoldBotGive),0) as TotalGold";
        } else {
            $column = "IFNULL(Sum(GoldBotTake),0) as TotalGold";
        }

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, ".$column." from tute_report_by_bot "
        . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate";
        return $sql;
    }
}