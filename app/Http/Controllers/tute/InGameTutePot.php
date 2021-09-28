<?php


namespace App\Http\Controllers\tute;


use Illuminate\Support\Facades\Log;

class InGameTutePot extends InGameTuteFunction
{
    const PotIn = "hotpot";
    const PotOut = "win hot pot";
    public function index($type = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->listChart = array(
            'chartPotGoldInOut'=>$this->chartTotalPotGoldInOut(),
            'chartPotGoldInByChannel'=>$this->chartPotGoldInOut(1, "Channel"),
            'chartPotGoldInByBetLevel'=>$this->chartPotGoldInOut(1, "BetLevel"),
            'chartPotGoldOutByChannel'=>$this->chartPotGoldInOut(2, "Channel"),
            'chartPotGoldOutByBetLevel'=>$this->chartPotGoldInOut(2, "BetLevel"),
            'chartNumberUserByNumberEatPot'=>$this->chartNumberUserByNumberEatPot(),
        );

        $this->listChartId=['chartPotGoldInOut', 'chartPotGoldInByChannel', 'chartPotGoldInByBetLevel',
            'chartPotGoldOutByChannel', 'chartPotGoldOutByBetLevel', 'chartNumberUserByNumberEatPot'];
        $this->addViewsToMain=json_encode(
            $this->addCharts(['chartPotGoldInOut']).
            $this->addGrid('', 'gridTotalPotGoldInOut', $this->gridTotalPotGoldInOut('tableid_gridTotalPotGoldInOut'), false).
            $this->addChart('chartPotGoldInByChannel').
            $this->addGrid('', 'gridPotGoldInByChannel', $this->gridPotGoldInOut('tableid_gridPotGoldInByChannel', 1, "Channel"), false).
            $this->addChart('chartPotGoldInByBetLevel').
            $this->addGrid('', 'gridPotGoldInByBetLevel', $this->gridPotGoldInOut('tableid_gridPotGoldInByBetLevel', 1, "BetLevel"), false).
            $this->addChart('chartPotGoldOutByChannel').
            $this->addGrid('', 'gridPotGoldOutByChannel', $this->gridPotGoldInOut('tableid_gridPotGoldOutByChannel', 2, "Channel"), false).
            $this->addChart('chartPotGoldOutByBetLevel').
            $this->addGrid('', 'gridPotGoldOutByBetLevel', $this->gridPotGoldInOut('tableid_gridPotGoldOutByBetLevel', 2, "BetLevel"), false).
            $this->addChart('chartNumberUserByNumberEatPot').
            $this->addGrid('', 'gridNumberUserByNumberEatPot', $this->gridNumberUserByNumberEatPot('tableid_gridNumberUserByNumberEatPot'), false).
            ''
        );
        $this->pageTitle='Tute Pot Report';
        return parent::__index($type);
    }

    // create chart
    private function chartTotalPotGoldInOut()
    {
        $sql = $this->getSQLTotalPotGoldInOut();
        $options = [    'title' => 'Total Gold In Out',
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options, true);
    }

    // type: 1-gold in, 2-gold out, by: Channel, BetLevel
    private function chartPotGoldInOut($type=1, $by="Channel"){

        $xSort = InGameTuteFunction::defaultSort;
        if ($type==1){
            $sql2 = $this->getSQLTotalPotGoldIn();
            $title = 'Pot Gold In By '.$by;
        }elseif ($type==2){
            $sql2 = $this->getSQLTotalPotGoldOut();
            $title = 'Pot Gold Out By '.$by;
        }
        if ($by=="Channel"){
            $ySort = InGameTuteFunction::sortChannel;
        } elseif ($by=="BetLevel"){
            $ySort = InGameTuteFunction::sortMoney;
        }
        $sql = $this->getSQLPotGoldInOut($type, $by);

        $options = [    'title' => $title,
            'subtitle' =>  $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'legend_align' => 'center',
            'showall' => true,
            'hide_show_button'=>true,
        ];

        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, $xSort, $ySort);
    }

    private function chartNumberUserByNumberEatPot(){
        $sql = $this->getSQLNumberUserByType(self::byEatPot);
        $sql2 = $this->getSQLTotalNumberUserByType(self::byEatPot);
        $options = [    'title' => "Number of Users By EatPot",
            'subtitle' =>  $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'legend_align' => 'center',
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, self::defaultSort, self::defaultSort);
    }


    // create grid
    private function gridTotalPotGoldInOut($tid): string
    {
        $sql = $this->getSQLTotalPotGoldInOut(false);
        return $this->createGrid($sql, $tid);
    }

    private function gridPotGoldInOut($tid, $type=1, $by="Channel"){
        $sql = $this->getSQLPotGoldInOut($type, $by);
        $data = $this->getDataSQLInGame($sql);
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

    private function gridNumberUserByNumberEatPot($tid){
        $sql = $this->getSQLNumberUserByType(self::byEatPot,false);
        $data = $this->getDataSQLInGame($sql);
        $grid_data = $this->pivotdata4grid_withSort($data, self::defaultRSort);
        $this->fillEmptyData($grid_data);
        return $this->createGridData($grid_data, $tid);
    }

    // create sql
    private function getSQLTotalPotGoldInOut($isChart=true){
        $sql1 = $this->getSQLTotalPotGoldIn();
        $sql2 = $this->getSQLTotalPotGoldOut();
        $sql = "select ii.ReportDate, IFNULL(ii.Pot_Gold_In, 0) as Pot_Gold_In, IFNULL(oo.Pot_Gold_Out, 0) as Pot_Gold_Out, (Pot_Gold_In - Pot_Gold_Out) as Sub_Gold from ((".$sql1.") as ii join (".$sql2.") as oo on ii.ReportDate = oo.ReportDate)";
        return $sql;
    }

    private function getSQLTotalPotGoldIn($isChart=true){
        $order = ($isChart)?"asc":"desc";
        $table_in = "tute_channel_gold_out_daily";
        return "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, SUM(Gold) as Pot_Gold_In from ".$table_in." as i "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Param1 = ".$this->quote(self::PotIn)
            ." GROUP BY i.ReportDate order by i.ReportDate ".$order;
    }

    private function getSQLTotalPotGoldOut($isChart=true){
        $order = ($isChart)?"asc":"desc";
        $table_out = "tute_channel_gold_in_daily";
        return "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, SUM(Gold) as Pot_Gold_Out from ".$table_out." as o "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Param1 = ".$this->quote(self::PotOut)
            ." GROUP BY o.ReportDate order by o.ReportDate ".$order;
    }

    private function getSQLPotGoldInOut($type, $by, $isChart=true){
        if ($type==1){
            $table = "tute_channel_gold_out_daily";
            $column = self::PotIn;
        }elseif ($type==2){
            $table = "tute_channel_gold_in_daily";
            $column = self::PotOut;
        }
        if ($by=="Channel"){
            return $this->getSQLPotGoldByChannel($table, $column, $isChart);
        } elseif ($by=="BetLevel"){
            return $this->getSQLPotGoldByBetLevel($table, $column, $isChart);
        }
    }

    private function getSQLPotGoldByChannel($table, $column, $isChart=true){
        $channel_mapping = InGameTuteFunction::sql_channel_mapping;
        $sql1 = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, ".$channel_mapping." as Channel, IFNULL(Sum(Gold),0) from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Param1=".$this->quote($column)." GROUP BY ReportDate, Channel";
        return $sql1;
    }
    private function getSQLPotGoldByBetLevel($table, $column, $isChart=true){
        $sql1 = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, ".$this->getMoneyFormat("BetLevel")." , IFNULL(Sum(Gold),0) from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Param1=".$this->quote($column)." GROUP BY ReportDate, BetLevel";

        return $sql1;
    }
}