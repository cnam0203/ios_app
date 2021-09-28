<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers\tute;

use App\Http\Controllers\InGameFunction;
use Illuminate\Support\Facades\Log;
use IP, View;

class InGameTuteGold extends InGameTuteFunction {

    public function index($type='') {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->listChart = array(
            'chartTotalGoldInOut' => $this->chartTotalGoldInOut(),
            'gridTotalGoldInOut' => $this->gridTotalGoldInOut(),
            'chartGoldInByChannel' => $this->chartGoldInOut(1, "Channel"),
            'gridGoldInChannel' => $this->gridGoldInOut(1, 'tableid_gridGoldInChannel',"Channel"),
            'chartGoldOutByChannel' => $this->chartGoldInOut(2, "Channel"),
            'gridGoldOutChannel' => $this->gridGoldInOut(2,'tableid_gridGoldOutChannel',"Channel"),
            'chartGoldInByBetLevel' => $this->chartGoldInOut(1, "BetLevel"),
            'gridGoldInBet' => $this->gridGoldInOut(1, 'tableid_gridGoldInBet', "BetLevel"),
            'chartGoldOutByBetLevel' => $this->chartGoldInOut(2, "BetLevel"),
            'gridGoldOutBet' => $this->gridGoldInOut(2,'tableid_gridGoldOutBet', "BetLevel"),
            'chartGoldInByChannelLine' => $this->chartGoldInOut(1, "Channel", true),
            'gridGoldInChannelLine' => $this->gridGoldInOut(1, 'tableid_gridGoldInChannelLine',"Channel"),
            'chartGoldOutByChannelLine' => $this->chartGoldInOut(2, "Channel", true),
            'gridGoldOutChannelLine' => $this->gridGoldInOut(2,'tableid_gridGoldOutChannelLine',"Channel"),
            'chartGoldInByBetLevelLine' => $this->chartGoldInOut(1, "BetLevel", true),
            'gridGoldInBetLine' => $this->gridGoldInOut(1, 'tableid_gridGoldInBetLine', "BetLevel"),
            'chartGoldOutByBetLevelLine' => $this->chartGoldInOut(2, "BetLevel", true),
            'gridGoldOutBetLine' => $this->gridGoldInOut(2,'tableid_gridGoldOutBetLine', "BetLevel"),
            'chartGoldInByAction' => $this->chartGoldInOut(1),
            'gridGoldInAction' => $this->gridGoldInOut(1, 'tableid_gridGoldInAction'),
            'chartGoldOutByAction' => $this->chartGoldInOut(2),
            'gridGoldOutAction' => $this->gridGoldInOut(2,'tableid_gridGoldOutAction'),
            'chartNumberUserLoginByGoldRange'=>$this->chartNumberUserByGoldRange(1),
            'gridNumberUserLogin' => $this->gridNumberUserByGoldRange(1,'tableid_gridNumberUserLogin'),
            'chartNumberUserLogoutByGoldRange'=>$this->chartNumberUserByGoldRange(2),
            'gridNumberUserLogout' => $this->gridNumberUserByGoldRange(2,'tableid_gridNumberUserLogout'),
            'chartNumberUserLoginByGoldRangeLine'=>$this->chartNumberUserByGoldRange(1, true),
            'gridNumberUserLoginLine' => $this->gridNumberUserByGoldRange(1,'tableid_gridNumberUserLoginLine'),
            'chartNumberUserLogoutByGoldRangeLine'=>$this->chartNumberUserByGoldRange(2, true),
            'gridNumberUserLogoutLine' => $this->gridNumberUserByGoldRange(2,'tableid_gridNumberUserLogoutLine'),
        );
        // $this->listChartId=['chartGoldInByAction','chartGoldInByChannel', 'chartGoldOutByChannel', 'chartGoldInByBetLevel', 'chartGoldOutByBetLevel', 'chartGoldOutByAction','chartTotalGoldInOut', 
        //                     'chartNumberUserLoginByGoldRange', 'chartNumberUserLogoutByGoldRange', 'chartGoldInByChannelLine', 'chartGoldInByBetLevelLine',
        //                     'chartGoldOutByChannelLine', 'chartGoldOutByBetLevelLine', 'chartNumberUserLoginByGoldRangeLine', 'chartNumberUserLogoutByGoldRangeLine'];
        // $this->addViewsToMain=json_encode(
        //     $this->addCharts(['chartTotalGoldInOut']).
        //     $this->addGrid('', 'gridTotalGoldInOut', $this->gridTotalGoldInOut(), false).
        //     $this->addCharts(['chartGoldInByChannel']).
        //     $this->addGrid('', 'gridGoldInChannel', $this->gridGoldInOut(1, 'tableid_gridGoldInChannel',"Channel"), false).
        //     $this->addCharts(['chartGoldInByChannelLine']).
        //     $this->addGrid('', 'gridGoldInChannelLine', $this->gridGoldInOut(1, 'tableid_gridGoldInChannelLine',"Channel"), false).
        //     $this->addCharts(['chartGoldOutByChannel']).
        //     $this->addGrid('', 'gridGoldOutChannel', $this->gridGoldInOut(2,'tableid_gridGoldOutChannel',"Channel"), false).
        //     $this->addCharts(['chartGoldOutByChannelLine']).
        //     $this->addGrid('', 'gridGoldOutChannelLine', $this->gridGoldInOut(2,'tableid_gridGoldOutChannelLine',"Channel"), false).
        //     $this->addCharts(['chartGoldInByBetLevel']).
        //     $this->addGrid('', 'gridGoldInBet', $this->gridGoldInOut(1, 'tableid_gridGoldInBet', "BetLevel"), false).
        //     $this->addCharts(['chartGoldInByBetLevelLine']).
        //     $this->addGrid('', 'gridGoldInBetLine', $this->gridGoldInOut(1, 'tableid_gridGoldInBetLine', "BetLevel"), false).
        //     $this->addCharts(['chartGoldOutByBetLevel']).
        //     $this->addGrid('', 'gridGoldOutBet', $this->gridGoldInOut(2,'tableid_gridGoldOutBet', "BetLevel"), false).
        //     $this->addCharts(['chartGoldOutByBetLevelLine']).
        //     $this->addGrid('', 'gridGoldOutBetLine', $this->gridGoldInOut(2,'tableid_gridGoldOutBetLine', "BetLevel"), false).
        //     $this->addCharts(['chartGoldInByAction']).
        //     $this->addGrid('', 'gridGoldInAction', $this->gridGoldInOut(1, 'tableid_gridGoldInAction'), false).
        //     $this->addCharts(['chartGoldOutByAction']).
        //     $this->addGrid('', 'gridGoldOutAction', $this->gridGoldInOut(2,'tableid_gridGoldOutAction'), false).
        //     $this->addCharts(['chartNumberUserLoginByGoldRange']).
        //     $this->addGrid('', 'gridNumberUserLogin', $this->gridNumberUserByGoldRange(1,'tableid_gridNumberUserLogin'), false).
        //     $this->addCharts(['chartNumberUserLoginByGoldRangeLine']).
        //     $this->addGrid('', 'gridNumberUserLoginLine', $this->gridNumberUserByGoldRange(1,'tableid_gridNumberUserLoginLine'), false).
        //     $this->addCharts(['chartNumberUserLogoutByGoldRange']).
        //     $this->addGrid('', 'gridNumberUserLogout', $this->gridNumberUserByGoldRange(2,'tableid_gridNumberUserLogout'), false).
        //     $this->addCharts(['chartNumberUserLogoutByGoldRangeLine']).
        //     $this->addGrid('', 'gridNumberUserLogoutLine', $this->gridNumberUserByGoldRange(2,'tableid_gridNumberUserLogoutLine'), false).
        //     ''
        // );
        $this->pageTitle='Tute Gold Report';
        return parent::__index($type);
    }

    //    create chart script
    private function chartTotalGoldInOut()
    {
        $sql = $this->getSQLTotalGoldInOut();
        $options = [    'title' => 'Total Gold In Out',
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];

        $chartInfo = str_replace("min: 0,", "", $this->chart1LineOption($sql, false, $options, true));
        return $chartInfo;
    }

    function chartGoldInOut($type=1, $by="Action", $isLine=false)
    {
        $action_condition = '';

        if ($by == "BetLevel") {
            $table_type = strtolower("Channel");
            $ySort = InGameTuteFunction::sortMoney;
        }
        elseif ($by=="Action"){
            $table_type = strtolower($by);
            $ySort = InGameTuteFunction::defaultRSort;
            $action_condition = " and actionID <> 'UpdateMoney'";
        } elseif ($by=="Channel"){
            $table_type = strtolower($by);
            $ySort = InGameTuteFunction::sortChannel;
        }

        if($type===1){
            $table = "tute_".$table_type."_gold_in_daily";
            $title='Gold In By '.$by;
        } elseif($type===2){
            $table = "tute_".$table_type."_gold_out_daily";
            $title='Gold Out By '.$by;
        }

        $sql1 = $this->getSQLGoldInOut($type, $by);
        $sql2 = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, SUM(Gold) as Total_Gold from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to).$action_condition." GROUP BY ReportDate";

        $options = [    'title' => $title,
            'subtitle' =>  $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'legend_align' => 'center',
            'showall' => true,
            'hide_show_button'=>true,
        ];

        if ($isLine)
            return $this->chartMultiLine($sql1, $options, true);
        else
            return $this->chart1stack1line_withPrefix_v2($sql1, $sql2, $options, InGameTuteFunction::defaultSort, $ySort);
    }

    function chartNumberUserByGoldRange($type=1, $isLine=false){
        $ySort = InGameTuteFunction::sortMoney;
        $xSort = InGameTuteFunction::defaultSort;
        if ($type==1){
            $column = 'UserLogin';
            $title = 'Number User Login By Gold Range';
        } elseif ($type==2){
            $column = 'UserLogout';
            $title = 'Number User Logout By Gold Range';
        }
        $table = "tute_report_by_gold_range";

        $sql1 = $this->getSQLUserByGold($column);

        $sql2 = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, SUM(".$column.") as Total_User from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate";

        $options = [    'title' => $title,
            'subtitle' =>  $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'legend_align' => 'center',
            'showall' => true,
            'hide_show_button'=>true,
        ];

        if ($isLine)
            return $this->chartMultiLine($sql1, $options, true);
        else
            return $this->chart1stack1line_withPrefix_v2($sql1, $sql2, $options, $xSort, $ySort);
    }

    //    create table script
    private function gridTotalGoldInOut()
    {
        $sql = $this->getSQLTotalGoldInOut(false);
        return $this->createGrid($sql, "tableid_gridTotalGoldInOut");
    }

    function gridGoldInOut($type=1, $tid, $by="Action"){
        $sql1 = $this->getSQLGoldInOut($type, $by, false);

        $data = $this->getDataSQLInGame($sql1);
        if ($by=='Channel'){
            $ySort = "sortChannelName";
        } elseif ($by=='BetLevel'){
            $ySort = "sortShortenMoney";
        } else
            $ySort = null;
        $grid_data = $this->pivotdata4grid_withSort($data, 'defaultRSort', $ySort);
        $this->fillEmptyData($grid_data);
        return $this->createGridData($grid_data, $tid, ['Rate'=>'PERCENT']);
    }

    function gridNumberUserByGoldRange($type=1, $tid){
        if ($type==1){
            $column = 'UserLogin';
        } elseif ($type==2){
            $column = 'UserLogout';
        }
        $sql1 = $this->getSQLUserByGold($column, false);

        $data = $this->getDataSQLInGame($sql1);
        $grid_data = $this->pivotdata4grid_withSort($data, 'defaultRSort', InGameTuteFunction::sortMoney);
        $this->fillEmptyData($grid_data);
        return $this->createGridData($grid_data, $tid, ['Rate'=>'PERCENT']);
    }


    private function getSQLGoldInOut($type, $by, $isChart=true){
        if ($by=="BetLevel")
            $table_type = strtolower("Channel");
        else
            $table_type = strtolower($by);

        if($type===1){
            $table = "tute_".$table_type."_gold_in_daily";
        } elseif($type===2){
            $table = "tute_".$table_type."_gold_out_daily";
        }

        if ($by=="Action"){
            $sql1 = $this->getSQLGoldByAction($table, $isChart);
        } elseif ($by=="Channel") {
            $sql1 = $this->getSQLGoldByChannel($table, $isChart);
        } elseif ($by=="BetLevel") {
            $sql1 = $this->getSQLGoldByBetLevel($table, $isChart);
        } elseif ($by=="User"){
            $sql1 = $this->getSQLUserByGold();
        }
        return $sql1;
    }

    //get sql query
    private function getSQLGoldByAction($table, $isChart=true){
        $sql1 = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, IF(Param1 = 'NULL', ActionID, Concat(ActionID,'-',Param1)) as Action,IFNULL(Gold,0) from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and actionID <> 'UpdateMoney'";
        return $sql1;
    }

    private function getSQLGoldByBetLevel($table, $isChart=true){

        $sql1 = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, ".$this->getMoneyFormat("BetLevel").", IFNULL(Sum(Gold),0) from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate, BetLevel";
        return $sql1;
    }

    private function getSQLGoldByChannel($table, $isChart=true){
        $channel_mapping = InGameTuteFunction::sql_channel_mapping;
        $sql1 = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, ".$channel_mapping." as Channel, IFNULL(Sum(Gold),0) from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate, Channel";
        return $sql1;
    }

    private function getSQLTotalGoldInOut($isChart=true){
        $table_in = "tute_action_gold_in_daily";
        $order = ($isChart)?"asc":"desc";
        $sql1 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, SUM(Gold) as Total_Gold_In from ".$table_in." as i "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY i.ReportDate order by i.ReportDate ".$order.") as ii";
        $table_out = "tute_action_gold_out_daily";
        $sql2 = "(select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, SUM(Gold) as Total_Gold_Out from ".$table_out." as o "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY o.ReportDate order by o.ReportDate ".$order.") as oo";

        $sql = "select ii.ReportDate, IFNULL(ii.Total_Gold_In, 0) as Total_Gold_In, IFNULL(oo.Total_Gold_Out, 0) as Total_Gold_Out, -10000000 as Sub_Gold from (".$sql1." left join ".$sql2." on ii.ReportDate = oo.ReportDate)";
        return $sql;
    }

    private function getSQLUserByGold($column, $isChart=true){
        $table = "tute_report_by_gold_range";
        $sql1 = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, GoldRange, ".$column." from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to);
        return $sql1;
    }

}
