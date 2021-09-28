<?php


namespace App\Http\Controllers\tute;


use Illuminate\Support\Facades\Log;

class InGameTuteNewUser extends InGameTuteFunction
{
    public function index($type = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->listChart = array(
            'chartTotalNewUser'=>$this->chartTotalNewUser(),
            'chartNumberNewUser'=>$this->chartNumNewUserPlayGame(),

            'chartNumberNewUserReceiveNewGift'=>$this->chartNumberNewUserByType(5),
            'chartNumberNewUserReceiveDailyGift'=>$this->chartNumberNewUserByType(4),
            'chartNumberNewUserByGamePlayed'=>$this->chartNumberNewUserByRange(1),
            'chartNumberNewUserByGamePlayedLine'=>$this->chartNumberNewUserByRange(1, true),
            'chartNumberNewUserBySubsidy'=>$this->chartNumberNewUserByRange(3),
            'chartNumberNewUserByGold'=>$this->chartNumberNewUserByRange(2),
            'chartNumberNewUserByGoldLine'=>$this->chartNumberNewUserByRange(2, true),
            'chartNumberNewUserByOnlineTime'=>$this->chartNumberNewUserByRange(4),
            'chartNumberNewUserByOnlineTimeLine'=>$this->chartNumberNewUserByRange(4, true),
            'chartNumberNewUserByTutStep'=>$this->chartNumberNewUserByRange(5),

            'chartNumberNewUserByGoldAfterPlay1Game'=>$this->chartNumberNewUserByGame(1),
            'chartNumberNewUserByGoldAfterPlay2Game'=>$this->chartNumberNewUserByGame(2),
            'chartNumberNewUserByGoldAfterPlay3Game'=>$this->chartNumberNewUserByGame(3),
            'chartNumberNewUserByGoldAfterPlay4Game'=>$this->chartNumberNewUserByGame(4),
            'chartNumberNewUserByGoldAfterPlay5Game'=>$this->chartNumberNewUserByGame(5),
        );

        $this->listChartId=['chartTotalNewUser', 'chartNumberNewUser', 'chartNumberNewUserClickPlayNow', 'chartNumberNewUserStartGame',
            'chartNumberNewUserFinishGame', 'chartNumberNewUserReceiveNewGift', 'chartNumberNewUserReceiveDailyGift',
            'chartNumberNewUserByGamePlayed', 'chartNumberNewUserByGamePlayedLine', 'chartNumberNewUserBySubsidy', 'chartNumberNewUserByGold', 'chartNumberNewUserByGoldLine', 'chartNumberNewUserByOnlineTime',
            'chartNumberNewUserByOnlineTimeLine', 'chartNumberNewUserByGoldAfterPlay1Game','chartNumberNewUserByGoldAfterPlay2Game','chartNumberNewUserByGoldAfterPlay3Game',
            'chartNumberNewUserByGoldAfterPlay4Game','chartNumberNewUserByGoldAfterPlay5Game', 'chartNumberNewUserByTutStep'];

        $this->addViewsToMain=json_encode(
            $this->addCharts(['chartTotalNewUser']).
            $this->addGrid('','gridTotalNewUser', $this->gridTotalNewUser('tableid_gridTotalNewUser'), false).
            $this->addCharts(['chartNumberNewUser']).
            $this->addGrid('','gridNumberNewUser', $this->gridNumberUserPlayGame('tableid_gridNumberNewUser'), false).

            $this->addCharts(['chartNumberNewUserByGamePlayed']).
            $this->addGrid('','gridNumberNewUserByGamePlayed', $this->gridNumberNewUserByRange('tableid_gridNumberNewUserByGamePlayed', 1), false).
            $this->addCharts(['chartNumberNewUserByGamePlayedLine']).
            $this->addGrid('','gridNumberNewUserByGamePlayedLine', $this->gridNumberNewUserByRange('tableid_gridNumberNewUserByGamePlayedLine', 1), false).
            $this->addCharts(['chartNumberNewUserBySubsidy']).
            $this->addGrid('','gridNumberNewUserBySubsidy', $this->gridNumberNewUserByRange('tableid_gridNumberNewUserBySubsidy', 3), false).
            $this->addCharts(['chartNumberNewUserByGold']).
            $this->addGrid('','gridNumberNewUserByGold', $this->gridNumberNewUserByRange('tableid_gridNumberNewUserByGold', 2), false).
            $this->addCharts(['chartNumberNewUserByGoldLine']).
            $this->addGrid('','gridNumberNewUserByGoldLine', $this->gridNumberNewUserByRange('tableid_gridNumberNewUserByGoldLine', 2), false).
            $this->addCharts(['chartNumberNewUserByOnlineTime']).
            $this->addGrid('','gridNumberNewUserByOnlineTime', $this->gridNumberNewUserByRange('tableid_gridNumberNewUserByOnlineTime', 4), false).
            $this->addCharts(['chartNumberNewUserByOnlineTimeLine']).
            $this->addGrid('','gridNumberNewUserByOnlineTimeLine', $this->gridNumberNewUserByRange('tableid_gridNumberNewUserByOnlineTimeLine', 4), false).
            $this->addCharts(['chartNumberNewUserByTutStep']).
            $this->addGrid('','gridNumberNewUserByTutStep', $this->gridNumberNewUserByRange('tableid_gridNumberNewUserByTutStep', 5), false).
            $this->addCharts(['chartNumberNewUserReceiveDailyGift']).
            $this->addGrid('','gridNumberNewUserReceiveDailyGift', $this->gridNumberUserByType('tableid_gridNumberNewUserReceiveDailyGift', 4), false).
            $this->addCharts(['chartNumberNewUserReceiveNewGift']).
            $this->addGrid('','gridNumberNewUserReceiveNewGift', $this->gridNumberUserByType('tableid_gridNumberNewUserReceiveNewGift', 5), false).

            $this->addCharts(['chartNumberNewUserByGoldAfterPlay1Game']).
            $this->addGrid('','gridNumberNewUserPlay1Game', $this->gridNumberNewUserByGame('tableid_gridNumberNewUserPlay1Game', 1), false).
            $this->addCharts(['chartNumberNewUserByGoldAfterPlay2Game']).
            $this->addGrid('','gridNumberNewUserPlay2Game', $this->gridNumberNewUserByGame('tableid_gridNumberNewUserPlay2Game', 2), false).
            $this->addCharts(['chartNumberNewUserByGoldAfterPlay3Game']).
            $this->addGrid('','gridNumberNewUserPlay3Game', $this->gridNumberNewUserByGame('tableid_gridNumberNewUserPlay3Game', 3), false).
            $this->addCharts(['chartNumberNewUserByGoldAfterPlay4Game']).
            $this->addGrid('','gridNumberNewUserPlay4Game', $this->gridNumberNewUserByGame('tableid_gridNumberNewUserPlay4Game', 4), false).
            $this->addCharts(['chartNumberNewUserByGoldAfterPlay5Game']).
            $this->addGrid('','gridNumberNewUserPlay5Game', $this->gridNumberNewUserByGame('tableid_gridNumberNewUserPlay5Game', 5), false).
            ''
        );
        $this->pageTitle='Tute New User Report';
        return parent::__index($type);
    }

    // create chart
    private function chartNumberNewUserByType($type){
        if ($type==4){
            // receive daily gift
            $sql = $this->getSQLNumNewUserReceiveDailyGift();
            $title = "Number New User Receive Daily Gift";
        }
        if ($type==5){
            // receive daily gift
            $sql = $this->getSQLNumNewUserReceiveNewGift();
            $title = "Number New User Receive New Gift (Quà khởi nghiệp)";
        }
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options, true);
    }

    private function chartNumNewUserPlayGame(){
        $sql = $this->getSQLNumNewUserStartFinishGame();
        $options = [    'title' => "Number New User By Action",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chartMultiLine($sql, $options);
    }

    private function chartNumberNewUserByRange($type, $isLine=false){
        $ySort = InGameTuteFunction::defaultSort;
        if ($type==1){
            // new user by matchPlayed
            $sql = $this->getSQLNumberUserByType(self::newUserbyGamePlayed);
            $title = "Number New User By GamePlayed";
            $sql2 = $this->getSQLTotalNumberUserByType(self::newUserbyGamePlayed);
        } elseif ($type==2){
            // gold new user
            $sql = $this->getSQLNumberNewUserByGoldRange();
            $title = "Number New User By Gold After LogOut";
            $sql2 = $this->getSQLNumberNewUser_GoldRange();
            $ySort = InGameTuteFunction::sortMoney;
        } elseif ($type==3){
            // subsidy
            $sql = $this->getSQLNumberNewUserBySubsidy();
            $sql2 = $this->getSQLNumberNewUser_Subsidy();
            $title = "Number New User By Subsidy";
        } elseif ($type==4){
            // onlineTime
            $sql = $this->getSQLNumberNewUserByOnlineTime();
            $sql2 = $this->getSQLNumberNewUser_OnlineTime();
            $title = "Number New User By Online Time";
        }
        elseif ($type==5){
            // onlineTime
            $sql = $this->getSQLNumberNewUserByTutStep();
            // $sql2 = $this->getSQLNumberNewUser_TutStep();
            $title = "Number New User By Tutorial Step";
            $data = $this->getDataSQLInGame($sql);
            $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
            $options = ['type' => 'line'
            ];
            $data = $this->pivotdata($data);
            $highchartseries = $this->_create_ArrayFor_HighchartSeries($data, $options);
            $options = [    'title' => $title,
                'subtitle' => $this->AppName,
                'yAxis_title' => '#',
                'hide_show_button' => true,
            ];
            return $this->script_lineChart($categories, $highchartseries, $options);
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

    private function chartNumberNewUserByGame($game=1){
        $sql = $this->getSQLNumberNewUserByGoldAfterGame($game);
        $sql2 = $this->getSQLTotalUserByGoldAfterGame($game);
        $options = [    'title' => "Number New User By Gold After Played ".$game." Game",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, InGameTuteFunction::defaultSort, self::sortMoney);
    }

    private function chartTotalNewUser(){
        $sql = $this->getSQLNumberNewUser();
        $options = [    'title' => "Total New User",
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options);
    }

    // create grid
    private function gridTotalNewUser($tid) {
        $sql = $this->getSQLNumberNewUser(false);
        return $this->createGrid($sql, $tid);
    }

    private function gridNumberUserPlayGame($tid){
        $sql = $this->getSQLNumNewUserStartFinishGame(false);
        return $this->createGrid($sql, $tid);
    }

    private function gridNumberUserByType($tid, $type)
    {
        if ($type==4){
            // receive daily gift
            $sql = $this->getSQLNumNewUserReceiveDailyGift(false);
        }
        if ($type==5){
            // receive new gift
            $sql = $this->getSQLNumNewUserReceiveNewGift(false);
        }
        return $this->createGrid($sql, $tid);
    }

    private function gridNumberNewUserByRange($tid, $type){
        if ($type==1){
            // new user by matchPlayed
            $sql = $this->getSQLNumberUserByType(self::newUserbyGamePlayed);
            $ySort = InGameTuteFunction::defaultSort;
        } elseif ($type==2){
            // gold new user
            $sql = $this->getSQLNumberNewUserByGoldRange();
            $ySort = InGameTuteFunction::sortMoney;
        } elseif ($type==3){
            // subsidy
            $sql = $this->getSQLNumberNewUserBySubsidy();
            $ySort = InGameTuteFunction::defaultSort;
        } elseif ($type==4){
            // online time
            $sql = $this->getSQLNumberNewUserByOnlineTime();
            $ySort = InGameTuteFunction::defaultSort;
        } elseif ($type==5){
            // tutorial step
            $sql = $this->getSQLNumberNewUserByTutStep();
            $ySort = InGameTuteFunction::defaultSort;
        }
        $data = $this->getDataSQLInGame($sql);
        $data_pivot = $this->pivotdata4grid_withSort($data, self::defaultRSort, $ySort);
        $this->fillEmptyData($data_pivot);
        return $this->createGridData($data_pivot, $tid);
    }

    private function gridNumberNewUserByGame($tid, $game){
        $sql = $this->getSQLNumberNewUserByGoldAfterGame($game);
        $data = $this->getDataSQLInGame($sql);
        $data_pivot = $this->pivotdata4grid_withSort($data, self::defaultRSort, self::sortMoney);
        $this->fillEmptyData($data_pivot);
        return $this->createGridData($data_pivot, $tid);
    }

    // create sql
    private function getSQLNumberNewUser($isChart=true){
        $table = "tute_new_user";
        $order = ($isChart)?"asc":"desc";
        $sql = "select ".$this->getChartDateFormat("AccountDate", $isChart)." ReportDate, count(*) as NumberNewUser from ".
            $table." where AccountDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY AccountDate order by AccountDate ".$order;
        return $sql;
    }

    private function getSQLNumberNewUserByGoldRange($isChart=true){
        $table = "tute_report_by_gold_range";
        $column = "NewUserLogout";
        $order = ($isChart)?"asc":"desc";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, GoldRange, ".$column." from ".$table." "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to);
        return $sql;
    }

    private function getSQLNumNewUserStartFinishGame($isChart=true){
        $order = ($isChart)?"asc":"desc";
        $sql = "select a.ReportDate, a.StartGame, a.FinishGame, IFNULL(b.ClickPlayNow, 0) as ClickPlayNow from ".
        " (select ".$this->getChartDateFormat("AccountDate", $isChart)." ReportDate, SUM(IF(StartGame>0, 1, 0)) as StartGame, SUM(IF(FinishGame>0, 1, 0)) as FinishGame from tute_new_user "
            . "where AccountDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." Group by AccountDate) as a left join ".
        " (select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, NumNewUser as ClickPlayNow from tute_report_by_user_action "
            . "where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Category='lobby' and UserAction='PlayNow') as b ".
        "on a.ReportDate = b.ReportDate order by a.ReportDate ".$order;
        return $sql;
    }

    private function getSQLTotalUserForSubsidy($isChart=true){
        $order = ($isChart)?"asc":"desc";
        $sql = "select ".$this->getChartDateFormat("AccountDate", $isChart)." ReportDate, count(*) as NumNewUser, SUM(IF(Subsidy>0, 1, 0)) as NumberUserReceiveSubsidy 
        from tute_new_user where AccountDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." 
        GROUP BY AccountDate order by AccountDate ".$order;
        return $sql;
    }

    private function getSQLNumNewUserReceiveDailyGift($isChart=true){
        $table = 'tute_new_user';
        $order = ($isChart)?"asc":"desc";
        $sql = "select ".$this->getChartDateFormat("AccountDate", $isChart)." ReportDate, Count(*) as NumberNewUser from ".$table." "
            . "where AccountDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and DailyGift > 0 Group by AccountDate order by AccountDate ".$order;
        return $sql;
    }

    private function getSQLNumNewUserReceiveNewGift($isChart=true){
        $table = 'tute_new_user';
        $order = ($isChart)?"asc":"desc";
        $sql = "select ".$this->getChartDateFormat("AccountDate", $isChart)." ReportDate, Count(*) as NumberNewUser from ".$table." "
            . "where AccountDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and newGift > 0 Group by AccountDate order by AccountDate ".$order;
        return $sql;
    }

    private function getSQLNumberNewUser_GoldRange($isChart=true){
        $table = "tute_report_by_gold_range";
        $order = ($isChart)?"asc":"desc";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, sum(IFNULL(NewUserLogout,0)) as NumberNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ReportDate order by ReportDate ".$order;
        return $sql;
    }

    private function getSQLNumberNewUserByGoldAfterGame($game, $isChart=true){
        $column = 'Game'.$game;
        $table = 'tute_report_new_user_by_game';
        $order = ($isChart)?"asc":"desc";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, GoldRange, ".$column." as NumberNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to);
        return $sql;
    }

    private function getSQLTotalUserByGoldAfterGame($game, $isChart=true){
        $column = 'Game'.$game;
        $table = 'tute_report_new_user_by_game';
        $order = ($isChart)?"asc":"desc";
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, sum(".$column.") as NumberNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." group by ReportDate";
        return $sql;
    }

    private function getSQLNumberNewUserBySubsidy($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."FieldRange as SubsidyRange, NumValue as NumberNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=9";
        return $sql;
    }

    private function getSQLNumberNewUser_Subsidy($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."sum(if(FieldRange <> '0', NumValue, 0)) as NumberUserReceiveSubsidy, sum(NumValue) as TotalNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=9 group by ReportDate, FieldID";
        return $sql;
    }

    private function getSQLNumberNewUserByOnlineTime($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."FieldRange as OnlineTimeRange, NumValue as NumberNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=7";
        return $sql;
    }

    private function getSQLNumberNewUser_OnlineTime($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."sum(NumValue) as TotalNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=7 group by ReportDate, FieldID";
        return $sql;
    }

    private function getSQLNumberNewUserByTutStep($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."FieldRange as StepNumber, NumValue as NumberNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=11";
        return $sql;
    }

    private function getSQLNumberNewUser_TutStep($isChart=true){
        $table = 'tute_user_report';

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, "."sum(NumValue) as TotalNewUser from ".
            $table." where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=11 group by ReportDate, FieldID";
        return $sql;
    }
}