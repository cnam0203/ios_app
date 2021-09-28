<?php


namespace App\Http\Controllers\tute;

use App\Http\Controllers\InGameFunction;
use Illuminate\Support\Facades\Log;

class InGameTuteGamePlayed extends InGameTuteFunction
{
    function index($type = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->pageTitle='Tute Game Played Report';

        $this->listChart = array(
            'chartTotalGamePlayed'=>$this->chartTotalGamePlayed(),
            'chartGamePlayedByChannel'=>$this->chartGamePlayedByType(1),
            'chartGamePlayedByChannelLine'=>$this->chartGamePlayedByType(1, true),
            'chartGamePlayedByBetLevel'=>$this->chartGamePlayedByType(2),
            'chartGamePlayedByBetLevelLine'=>$this->chartGamePlayedByType(2, true),
            'chartGamePlayedByNumberUser'=>$this->chartGamePlayedByType(5),
            'chartNumberUserByGamePlayed'=>$this->chartNumberUserByGamePlayed(),
            'chartNumberUserByGamePlayedLine'=>$this->chartNumberUserByGamePlayed(true),
        );

        $this->listChartId=['chartTotalGamePlayed', 'chartGamePlayedByChannel', 'chartGamePlayedByChannelLine', 'chartGamePlayedByBetLevel', 
                            'chartGamePlayedByBetLevelLine', 'chartGamePlayedByNumberUser', 'chartNumberUserByGamePlayed', 'chartNumberUserByGamePlayedLine'];
        $this->addViewsToMain=json_encode(
            $this->addCharts(['chartTotalGamePlayed']).
            $this->addGrid('', 'gridTotalGamePlayed', $this->gridTotalGamePlayed('tableid_gridTotalGamePlayed'), false).
            $this->addCharts(['chartGamePlayedByChannel']).
            $this->addGrid('', 'gridGamePlayedByChannel', $this->gridTotalGamePlayedByType('tableid_gridGamePlayedByChannel', 1), false).
            $this->addCharts(['chartGamePlayedByChannelLine']).
            $this->addGrid('', 'gridGamePlayedByChannelLine', $this->gridTotalGamePlayedByType('tableid_gridGamePlayedByChannelLine', 1), false).
            $this->addCharts(['chartGamePlayedByBetLevel']).
            $this->addGrid('', 'gridGamePlayedByBetLevel', $this->gridTotalGamePlayedByType('tableid_gridGamePlayedByBetLevel', 2), false).
            $this->addCharts(['chartGamePlayedByBetLevelLine']).
            $this->addGrid('', 'gridGamePlayedByBetLevelLine', $this->gridTotalGamePlayedByType('tableid_gridGamePlayedByBetLevelLine', 2), false).
            $this->addCharts(['chartGamePlayedByNumberUser']).
            $this->addGrid('', 'gridGamePlayedByNumberUser', $this->gridTotalGamePlayedByType('tableid_gridGamePlayedByNumberUser', 5), false).
            $this->addCharts(['chartNumberUserByGamePlayed']).
            $this->addGrid('', 'gridUserByGamePlayed', $this->gridUserByGamePlayed('tableid_gridUserByGamePlayed'), false).
            $this->addCharts(['chartNumberUserByGamePlayedLine']).
            $this->addGrid('', 'gridUserByGamePlayedLine', $this->gridUserByGamePlayed('tableid_gridUserByGamePlayedLine'), false).
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

    private function chartGamePlayedByType($type=1, $isLine=false){
        Log::error("chartGamePlayedByType");
        if ($type==1){
            $title = "Total Game Played By Channel";
            $sql = $this->getSQLGamePlayedByChannel();
            $sql2 = $this->getSQLTotalGamePlayed();
            $sort = InGameTuteFunction::sortChannel;
        } elseif ($type==2){
            $title = "Total Game Played By BetLevel";
            $sql = $this->getSQLGamePlayedByBetLevel();
            $sql2 = $this->getSQLTotalGamePlayed();
            $sort = InGameTuteFunction::sortMoney;
        } elseif ($type==3){
            $title = "Total Game Played By GoldRange";
            $sql = $this->getSQLGamePlayedByGoldRange();
            $sql2 = $this->getSQLTotalNumberMatch();
            $sort = InGameTuteFunction::defaultSort;
        }  elseif ($type==4){
            $title = "Total Game Played By Time";
            $sql = $this->getSQLGamePlayedByTime();
            $sql2 = $this->getSQLTotalEndGamePlayed();
            $sort = InGameTuteFunction::defaultSort;
        }  elseif ($type==5){
            $title = "Total Game Played By NumberUser";
            $sql = $this->getSQLGamePlayedByNumberUser();
            $sql2 = $this->getSQLTotalEndGamePlayed();
            $sort = InGameTuteFunction::defaultSort;
        } else {
            $sort = InGameTuteFunction::defaultSort;
        }
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];

        if ($isLine)
            return $this->chartMultiLine($sql, $options, true);
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, InGameTuteFunction::defaultSort, $sort);
    }

    private function chartNumberUserByGamePlayed($isLine=false){
        $title = "Total Number of User by GamePlayed";
        $sql = $this->getSQLNumberUserByType(self::byGamePlayed);
        $sql2 = $this->getSQLTotalNumberUserByType(self::byGamePlayed);
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        
        if ($isLine)
            return $this->chartMultiLine($sql, $options, true);
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options);
    }

    // create grid script
    private function gridTotalGamePlayed($tid){
        $sql = $this->getSQLTotalGamePlayed(false);
        return $this->createGrid($sql, $tid);
    }

    private function gridTotalGamePlayedByType($tid, $type=1){
        if ($type==1){
            $sql = $this->getSQLGamePlayedByChannel(false);
            $sort = InGameTuteFunction::sortChannel;
        } elseif ($type==2){
            $sql = $this->getSQLGamePlayedByBetLevel(false);
            $sort = InGameTuteFunction::sortMoney;
        } elseif ($type==3){
            $sql = $this->getSQLGamePlayedByGoldRange(false);
            $sort = InGameTuteFunction::defaultSort;
        } elseif ($type==4){
            $sql = $this->getSQLGamePlayedByTime(false);
            $sort = InGameTuteFunction::defaultSort;
        } elseif ($type==5){
            $sql = $this->getSQLGamePlayedByNumberUser(false);
            $sort = InGameTuteFunction::defaultSort;
        } else {
            $sort=null;
        }
        $data = $this->getDataSQLInGame($sql);
        $gridData = $this->pivotdata4grid_withSort($data, 'defaultRSort', $sort);
        $this->fillEmptyData($gridData);
        return $this->createGridData($gridData, $tid);
    }

    private function gridUserByGamePlayed($tid){
        $sql = $this->getSQLNumberUserByType(self::byGamePlayed, false);
        $data = $this->getDataSQLInGame($sql);
        $gridData = $this->pivotdata4grid_withSort($data, 'defaultRSort');
        $this->fillEmptyData($gridData);
        return $this->createGridData($gridData, $tid);
    }

    // get sql query
    private function getSQLTotalGamePlayed($isChart=true){
        $table = "tute_report_by_gameplayed";
        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, sum(GamePlayed) as TotalGamePlayed from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate order by ReportDate ".$sort;
        return $sql;
    }

    private function getSQLGamePlayedByChannel($isChart=true){
        $table = "tute_report_by_gameplayed";
        $channel_mapping = InGameTuteFunction::sql_channel_mapping;
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, ".$channel_mapping." as Channel,sum(GamePlayed) as TotalGamePlayed from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate, Channel order by ReportDate ";
        return $sql;
    }

    private function getSQLGamePlayedByBetLevel($isChart=true){
        $table = "tute_report_by_gameplayed";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, ".$this->getMoneyFormat("BetLevel")." ,sum(GamePlayed) as TotalGamePlayed from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate, BetLevel order by ReportDate ";
        return $sql;
    }

    private function getSQLGamePlayedByGoldRange($isChart=true){
        $table = "tute_report_by_gold_range";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, GoldRange, NumberMatch as TotalGamePlayed from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." order by ReportDate ";
        return $sql;
    }

    private function getSQLTotalNumberMatch($isChart=true){
        $table = "tute_report_by_gold_range";
        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, IFNULL(sum(NumberMatch), 0) as TotalGamePlayed from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate order by ReportDate ".$sort;
        return $sql;
    }

    private function getSQLGamePlayedByTime($isChart=true){
        $table = "tute_user_report";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, FieldRange as Time, NumValue as TotalGamePlayed from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=12 order by ReportDate ";
        return $sql;
    }

    private function getSQLGamePlayedByNumberUser($isChart=true){
        $table = "tute_user_report";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, FieldRange as NumberPlayer, NumValue as TotalGamePlayed from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=13 order by ReportDate ";
        return $sql;
    }

    private function getSQLTotalEndGamePlayed($isChart=true){
        $table = "tute_user_report";
        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." as ReportDate, IFNULL(sum(NumValue), 0) as TotalGamePlayed from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=12 GROUP BY ReportDate order by ReportDate ".$sort;
        return $sql;
    }
}