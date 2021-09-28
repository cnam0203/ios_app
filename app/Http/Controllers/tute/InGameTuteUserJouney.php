<?php


namespace App\Http\Controllers\tute;


use Illuminate\Support\Facades\Log;

class InGameTuteUserJouney extends InGameTuteFunction
{
    public function index($type = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->listChart = array(
            'chartNumberUserByAction'=>$this->chartNumberUserByAction(1),
            'chartNumberNewUserByAction'=>$this->chartNumberUserByAction(2),
            'chartUserAction'=>$this->chartNumberUserByAction(3),
            'chartNewUserAction'=>$this->chartNumberUserByAction(4),
            'chartNumUserOutTable'=>$this->chartNumUserOutTable(1),
            'chartNewUserOutTable'=>$this->chartNumUserOutTable(2),
            'chartOutTable'=>$this->chartActionOutTable(3)
        );

        $this->listChartId=['chartNumberUserByAction', 'chartNumberNewUserByAction', 'chartUserAction', 'chartNewUserAction',
            'chartOutTable', 'chartNumUserOutTable', 'chartNewUserOutTable'];

        $this->addViewsToMain = json_encode(
            $this->addCharts(['chartNumberUserByAction']).
            $this->addGrid('','gridNumberUserByAction', $this->gridNumberUserByAction( 'tableid_gridNumberUserByAction', 1), false).
            $this->addCharts(['chartNumberNewUserByAction']).
            $this->addGrid('','gridNumberNewUserByAction', $this->gridNumberUserByAction( 'tableid_gridNumberNewUserByAction', 2), false).
            $this->addCharts(['chartUserAction']).
            $this->addGrid('','gridNumberAction', $this->gridNumberUserByAction( 'tableid_gridNumberAction', 3), false).
            $this->addCharts(['chartNewUserAction']).
            $this->addGrid('','gridNumberNewUserAction', $this->gridNumberUserByAction( 'tableid_gridNumberNewUserAction', 4), false).

            $this->addCharts(['chartNumUserOutTable']).
            $this->addGrid('','gridNumUserOutTable', $this->gridOutTable( 'tableid_gridNumUserOutTable', 1), false).
            $this->addCharts(['chartNewUserOutTable']).
            $this->addGrid('','gridNewUserOutTable', $this->gridOutTable( 'tableid_gridNewUserOutTable', 2), false).
            $this->addCharts(['chartOutTable']).
            $this->addGrid('','gridOutTable', $this->gridOutTable( 'tableid_gridOutTable', 3), false).
            ''
        );
        $this->pageTitle='Tute User Jouney Report';
        return parent::__index($type);
    }

    // create chart
    private function chartNumberUserByAction($type){
        if ($type==1){
            $title = 'Number User By Action';
        } elseif ($type==2){
            $title = 'Number New User By Action';
        } elseif ($type==3){
            $title = 'Number User Actions';
        } elseif ($type==4){
            $title = 'Number New User Actions';
        }
        $sql = $this->getSQLUserAction($type);
        $data = $this->getDataSQLInGame($sql);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        $options = ['type' => 'column',
            'stackname' => 'Col1'
        ];
        $highchartseries = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'hide_show_button' => true,
        ];
        return $this->script_lineChart($categories, $highchartseries, $options);
    }

    private function chartNumUserOutTable($type){
        if ($type==1){
            $title = 'Number User Out Table';
        } elseif ($type==2){
            $title = 'Number New User Out Table';
        }

        $sql = $this->getSQLUserActionOutTable($type);
        $data = $this->getDataSQLInGame($sql);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        $options = ['type' => 'column',
            'stackname' => 'Col1'
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

    private function chartActionOutTable($type){
        $title = 'Percent Action Out Table';

        $sql = $this->getSQLUserActionOutTable($type);
        $data = $this->getDataSQLInGame($sql);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        $options = ['type' => 'column',
            'stackname' => 'Col1'
        ];
        $data = $this->pivotdata($data);
        $highchartseries = $this->_create_ArrayFor_HighchartSeries($data, $options);
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'stack_col'=>true,
            'yAxis_title' => '#',
            'hide_show_button' => true,
        ];
        return $this->script_stackPercentChart($categories, $highchartseries, $options);
    }

    // create grid
    private function gridNumberUserByAction($tid, $type=1){
        $sql = $this->getSQLUserAction($type, false);
        return $this->createGrid($sql, $tid);
    }

    private function gridOutTable($tid, $type=1){
        $sql = $this->getSQLUserActionOutTable($type, false);
        $data = $this->getDataSQLInGame($sql);
        $data = $this->pivotdata4grid_withSort($data);
        return $this->createGridData($data, $tid);
    }

    // create sql
    // type 1: Number User By Action, type 2: Number new User by Action, type 3: number action
    private function getSQLUserAction($type=1, $isChart=true){
        $table = "tute_report_by_user_action";
        if ($type==1){
            $column = 'NumUser';
        } elseif ($type==2){
            $column = 'NumNewUser';
        } elseif ($type==3){
            $column = 'Quantity';
        } elseif ($type==4){
            $column = 'NewUserQuantity';
        }

        $column_sql = "SUM(IF(UserAction='PlayNow', ".$column.", 0)) as PlayNow,
         SUM(IF(Category='quickMatch' and UserAction='All', ".$column.", 0)) as QuickMatch, 
         SUM(IF(Category='channel' and UserAction='All', ".$column.", 0)) as Channel,
         SUM(IF(Category='lobby' and UserAction='All', ".$column.", 0)) as Lobby,
         SUM(IF(Category='Play' and UserAction='All', ".$column.", 0)) as Play,
         SUM(IF(Category='JoinTable' and UserAction='All', ".$column.", 0)) as JoinTable, 
         SUM(IF(Category='StartGameUser' and UserAction='All', ".$column.", 0)) as StartGameUser, 
         SUM(IF(Category='ResultGame' and UserAction='All', ".$column.", 0)) as ResultGame, 
         SUM(IF(Category='OutTable' and UserAction='All', ".$column.", 0)) as OutTable,
         SUM(IF(Category='DisconnectTable' and UserAction='All', ".$column.", 0)) as DisconnectTable";

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, ".$column_sql." from ".$table
            . " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." group by ReportDate order by ReportDate ".(($isChart)?'':' desc');
//        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, concat(Category, '-', UserAction) as Action, ".$column." from ".$table
//            . " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and (Category='JoinTable' or Category='OutTable' or Category='StartGameUser' or (UserAction!='All' and UserAction!='')) order by ReportDate ".(($isChart)?'':' desc');
        return $sql;
    }

    private function getSQLUserActionOutTable($type=1, $isChart=true){
        $table = "tute_report_by_user_action";
        if ($type==1){
            $column = 'NumUser';
        } elseif ($type==2){
            $column = 'NumNewUser';
        } elseif ($type==3){
            $column = 'Quantity';
        }

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, UserAction as Reason, ".$column." from ".$table
            . " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Category = 'OutTable' and UserAction != 'All' order by ReportDate ".(($isChart)?'':' desc');
        return $sql;
    }

    //
    function script_stackPercentChart($categories, $chartdata, $options) {
        $str = "
            {
                title: {
                    text: '".str_replace("'",'',$options['title'])."'
                },
            
                subtitle: {
                    text: '".str_replace("'",'',$options['subtitle'])."'
                },
                
                chart: {
                    ".($options['chart_height']!=''?'height: '.$options['chart_height'].",":'')."
                    ".($options['chart_backgroundColor']!=''?"backgroundColor: '".$options['chart_backgroundColor']."',":'')."
                },
                
                xAxis: {
                    categories: ".json_encode($categories)."
                },
                yAxis: {
                    title: {
                        text: '".str_replace("'",'',$options['yAxis_title'])."',
                    },
                    min: 0,
                },
                legend: {
                    align: '".($options['legend_align']?$options['legend_align']:'center')."',
                    verticalAlign: 'bottom',
                    borderWidth: 1,
                    ".($options['legend_align']=='right'?'layout: \'vertical\',':'')."
                },
                
                plotOptions: {
                    column: {".($options['stack_col']===true?"stacking: 'percent'":"")."},
                    area: {".($options['stack_area']===true?"stacking: 'percent'":"")."}
                },
                
                tooltip: {
                    pointFormat: '<span >{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
                    shared: false
                },
            
                series: ".json_encode($chartdata).",
            
                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom'
                            }
                        }
                    }]
                }
            
            }";
        return $str;
    }
}