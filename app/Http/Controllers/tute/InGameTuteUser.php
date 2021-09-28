<?php


namespace App\Http\Controllers\tute;


use Illuminate\Support\Facades\Log;

class InGameTuteUser extends InGameTuteFunction
{

    public function index($type = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->listChart = array(
            'chartNumberUserLogin'=>$this->chartNumberUser(1),
            'chartNumberLoginAction'=>$this->chartNumberLoginAction(),
            'chartNumberUserLoginByLevel'=>$this->chartNumberUserByType(2),
            'chartNumberUserLoginByAge'=>$this->chartNumberUserByType(3),
            'chartUserPlayedByChannel'=>$this->chartNumberUserPlayedByChannel(),
            'chartNumberUserByMatchPlayed'=>$this->chartNumberUserByType(4),
            'chartNumberUserByMatchPlayedLine'=>$this->chartNumberUserByType(4, true),
            'chartNumberUserByGoldRange'=>$this->chartNumberUserByType(5),
            'chartNumberUserByGoldRangeLine'=>$this->chartNumberUserByType(5, true),
            'chartNumberUserBySubsidy'=>$this->chartNumberUserByRange(1),
            'chartNumberUserByOnlineTime'=>$this->chartNumberUserByRange(2),
            'chartNumberUserByOnlineTimeLine'=>$this->chartNumberUserByRange(2, true),
        );

        $this->listChartId=['chartNumberUserLogin', 'chartNumberLoginAction', 'chartNumberUserLoginByLevel', 'chartNumberUserLoginByAge','chartUserPlayedByChannel', 'chartNumberNewUser', 
        'chartNumberUserByMatchPlayed', 'chartNumberUserByMatchPlayedLine', 'chartNumberUserByGoldRange', 'chartNumberUserByGoldRangeLine', 
        'chartNumberUserBySubsidy', 'chartNumberUserByOnlineTime', 'chartNumberUserByOnlineTimeLine', 'chartLoginGiftByAge', 'chartLoginGiftByMission', 'chartUserCompleteMission'];
        $this->addViewsToMain=json_encode(
            $this->addCharts(['chartNumberUserLogin']).
            $this->addGrid('', 'gridNumberUserLogin', $this->gridNumberUser('tableid_gridNumberUserLogin', 1), false).
            $this->addCharts(['chartNumberLoginAction']).
            $this->addGrid('', 'gridNumberLoginAction', $this->gridNumberLoginAction('tableid_gridNumberLoginAction'), false).
            $this->addCharts(['chartNumberUserLoginByLevel']).
            $this->addGrid('', 'gridNumberUserLoginByLevel', $this->gridNumberUserByType('tableid_gridNumberUserLoginByLevel', 2),false).
            $this->addCharts(['chartNumberUserLoginByAge']).
            $this->addGrid('', 'gridNumberUserLoginByAge', $this->gridNumberUserByType('tableid_gridNumberUserLoginByAge', 3),false).
            $this->addCharts(['chartUserPlayedByChannel']).
            $this->addGrid('', 'gridUserPlayByChannel', $this->gridNumberUserPlayedByChannel('tableid_gridUserPlayByChannel'), false).
            $this->addCharts(['chartNumberUserByMatchPlayed']).
            $this->addGrid('', 'gridNumberUserByMatchPlayed', $this->gridNumberUserByType('tableid_gridNumberUserByMatchPlayed', 4),false).
            $this->addCharts(['chartNumberUserByMatchPlayedLine']).
            $this->addGrid('', 'gridNumberUserByMatchPlayedLine', $this->gridNumberUserByType('tableid_gridNumberUserByMatchPlayedLine', 4),false).
            $this->addCharts(['chartNumberUserByGoldRange']).
            $this->addGrid('', 'gridNumberUserByGoldRange', $this->gridNumberUserByType('tableid_gridNumberUserByGoldRange', 5),false).
            $this->addCharts(['chartNumberUserByGoldRangeLine']).
            $this->addGrid('', 'gridNumberUserByGoldRangeLine', $this->gridNumberUserByType('tableid_gridNumberUserByGoldRangeLine', 5),false).
            $this->addCharts(['chartNumberUserBySubsidy']).
            $this->addGrid('','gridNumberUserBySubsidy', $this->gridNumberUserByRange('tableid_gridNumberUserBySubsidy', 1), false).
            $this->addCharts(['chartNumberUserByOnlineTime']).
            $this->addGrid('','gridNumberUserByOnlineTime', $this->gridNumberUserByRange('tableid_gridNumberUserByOnlineTime', 2), false).
            $this->addCharts(['chartNumberUserByOnlineTimeLine']).
            $this->addGrid('','gridNumberUserByOnlineTimeLine', $this->gridNumberUserByRange('tableid_gridNumberUserByOnlineTimeLine', 2), false).
            ''
        );
        $this->pageTitle='Tute User Report';
        return parent::__index($type);
    }

    // create chart script
    private function chartNumberUser($type){
        if ($type==1){
            // $sql = $this->getSQLNumberUserLogin();
            $sql = $this->getSQLTotalNumberUserByType(self::byLevel);
            $title = 'Number User Login';
        } elseif($type==2){
            // $sql = $this->getSQLNumberNewUser();
            $sql = $this->getSQLTotalNumberUserByType(self::newUserbyGamePlayed);
            $title = 'Number New User';
        }

        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'hide_show_button' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options, true);
    }

    private function chartNumberLoginAction(){
        $sql = $this->getSQLTotalNumberLoginAction();
        $title = 'Number Login Actions';

        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'hide_show_button' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options, true);
    }

    private function chartNumberUserByType($type, $isLine=false){
        $ySort = InGameTuteFunction::defaultSort;
        if ($type==1){
            //channel
            $title = "Number User Login By Channel";
            // $sql2 = $this->getSQLNumberUserLogin();
            $sql2 = $this->getSQLTotalNumberUserByType(self::byLevel);
            $ySort = InGameTuteFunction::sortChannel;
        } elseif ($type==2){
            //level
            $sql = $this->getSQLNumberUserByType(self::byLevel);
            $title = "Number User Login By Level";
            $sql2 = $this->getSQLTotalNumberUserByType(self::byLevel);
        } elseif ($type==3){
            //Age
            $sql = $this->getSQLNumberUserByType(self::byAge);
            $title = "Number User Login By Age";
            $sql2 = $this->getSQLTotalNumberUserByType(self::byAge);
        } elseif ($type==4){
            //user by matchPlayed
            $sql = $this->getSQLNumberUserByType(self::byGamePlayed);
            $title = "Number User By GamePlayed";
            $sql2 = $this->getSQLTotalNumberUserByType(self::byGamePlayed);
        } elseif ($type==5){
            // gold new user
            $sql = $this->getSQLNumberUserByGoldRange();
            $title = "Number User By GoldRange";
            $sql2 = $this->getSQLNumberUser();
            // $sql2 = $this->getSQLTotalNumberUserByType(self::newUserbyGamePlayed);
            $ySort = InGameTuteFunction::sortMoney;
        }
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'hide_show_button' => true,
            'hide_show_button'=>true,
        ];
        // $sql2 = $this->getSQLNumberUserLogin();

        if ($isLine)
            return $this->chartMultiLine($sql, $options, true);
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, InGameTuteFunction::defaultSort, $ySort);
    }

    private function chartNumberUserByRange($type, $isLine=false){
        $ySort = InGameTuteFunction::defaultSort;
        if ($type==1){
            // subsidy
            $sql = $this->getSQLNumberUserBySubsidy();
            $sql2 = $this->getSQLNumberUser_Subsidy();
            $title = "Number User By Subsidy";
        } elseif ($type==2){
            // onlineTime
            $sql = $this->getSQLNumberUserByOnlineTime();
            $sql2 = $this->getSQLNumberUser_OnlineTime();
            $title = "Number User By Online Time";
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
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, InGameTuteFunction::defaultSort, $ySort);
    }

    private function chartNumberUserPlayedByChannel(){
        $sql = $this->getSQLNumberUserPlayedByChannel();
        $options = [    'title' => "User Played Game By Channel",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'hide_show_button' => true,
            'hide_show_button'=>true,
        ];
        return $this->chartMultiLine($sql, $options);
    }

    // create grid script

    private function gridNumberUserByType($tid, $type)
    {
        $ySort = InGameTuteFunction::defaultSort;
        if ($type==1){
            //channel
            $title = "Number User Login By Channel";
        } elseif ($type==2){
            //level
            $sql = $this->getSQLNumberUserByType(self::byLevel, false);
        } elseif ($type==3){
            //Age
            $sql = $this->getSQLNumberUserByType(self::byAge, false);
        } elseif ($type==4){
            //user by matchPlayed
            $sql = $this->getSQLNumberUserByType(self::byGamePlayed, false);
        } elseif ($type==5){
            // gold user
            $sql = $this->getSQLNumberUserByGoldRange(false);
            $ySort = InGameTuteFunction::sortMoney;
        } elseif ($type==6){
            //user by matchPlayed
            $sql = $this->getSQLNumberUserByType(self::userbyGamePlayed, false);
        } 
        $data = $this->getDataSQLInGame($sql);
        $grid_data = $this->pivotdata4grid_withSort($data, "defaultRSort", $ySort);
        $this->fillEmptyData($grid_data);
        return $this->createGridData($grid_data, $tid);
    }

    private function gridNumberUser($tid, $type)
    {
        if ($type==1){
            // $sql = $this->getSQLNumberUserLogin(false);
            $sql = $this->getSQLTotalNumberUserByType(self::byLevel, false);
        } elseif($type==2){
            // $sql = $this->getSQLNumberNewUser(false);
            $sql = $this->getSQLTotalNumberUserByType(self::newUserbyGamePlayed, false);
        }
        return $this->createGrid($sql, $tid);
    }

    private function gridNumberLoginAction($tid)
    {
        $sql = $this->getSQLTotalNumberLoginAction(alse);
        return $this->createGrid($sql, $tid);
    }

    private function gridNumberUserPlayedByChannel($tid){
        $sql = $this->getSQLNumberUserPlayedByChannel(false);
        return $this->createGrid($sql, $tid);
    }


    private function gridNumberUserByRange($tid, $type){
        if ($type==1){
            // subsidy
            $sql = $this->getSQLNumberUserBySubsidy();
            $ySort = InGameTuteFunction::defaultSort;
        } elseif ($type==2){
            // online time
            $sql = $this->getSQLNumberUserByOnlineTime();
            $ySort = InGameTuteFunction::defaultSort;
        } 
        $data = $this->getDataSQLInGame($sql);
        $data_pivot = $this->pivotdata4grid_withSort($data, self::defaultRSort, $ySort);
        $this->fillEmptyData($data_pivot);
        return $this->createGridData($data_pivot, $tid);
    }

    // create sql query
    private function getSQLNumberUserLogin($isChart=true){
        $table = "tute_user_daily";
        if (!$isChart)
            $order = 'desc';
        else
            $order = 'asc';
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, count(*) as NumberUserLogin from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate order by ReportDate ".$order;
        return $sql;
    }

    private function getSQLNumberUser($isChart=true){
        $table = "tute_report_by_gold_range";
        $order = ($isChart)?"asc":"desc";
        // $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, count(*) as NumberNewUser from ".$table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." AND isNew=1 GROUP BY ReportDate order by ReportDate";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, sum(IFNULL(UserLogout,0)) as NumberUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate order by ReportDate ".$order;
        return $sql;
    }

    private function getSQLTotalNumberLoginAction($isChart=true){
        $table = "tute_report_by_user_action";
        $order = ($isChart)?"asc":"desc";
        // $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, count(*) as NumberNewUser from ".$table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." AND isNew=1 GROUP BY ReportDate order by ReportDate";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, sum(IFNULL(Quantity,0)) as NumberAction from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Category='PlayerInfoLogin' GROUP BY ReportDate order by ReportDate ".$order;
        return $sql;
    }

    private function getSQLNumberUserByGoldRange($isChart=true){
        $table = "tute_report_by_gold_range";
        $column = "UserLogin";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, GoldRange, ".$column." from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to);
        return $sql;
    }

    private function getSQLNumberUserPlayedByChannel($isChart=true){
        $table = "tute_report_by_gameplayed";
        if ($isChart)
            $order = "asc";
        else
            $order = 'desc';
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, ".$this->getChannelQuery("NumberUser")." from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate ORDER BY ReportDate ".$order;
        return $sql;
    }


    private function getSQLNumberUserBySubsidy($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."FieldRange as SubsidyRange, NumValue as NumberUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=8";
        return $sql;
    }

    private function getSQLNumberUser_Subsidy($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."sum(if(FieldRange <> '0', NumValue, 0)) as NumberUserReceiveSubsidy, sum(NumValue) as TotalUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=8 group by ReportDate, FieldID";
        return $sql;
    }

    private function getSQLNumberUserByOnlineTime($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."FieldRange as OnlineTimeRange, NumValue as NumberUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=6";
        return $sql;
    }

    private function getSQLNumberUser_OnlineTime($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."sum(NumValue) as TotalUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=6 group by ReportDate, FieldID";
        return $sql;
    }
}