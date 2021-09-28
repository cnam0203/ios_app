<?php


namespace App\Http\Controllers\tute;

use App\Http\Controllers\InGameFunction;
use Illuminate\Support\Facades\Log;

class InGameTutePigEvent extends InGameTuteFunction
{
    function index($columnType = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->pageTitle='Tute Payment Report';

        $this->listChart = array(
            'chartNumberActionBreakByCategory'=>$this->chartNumberActionByType(1, 1, 1, 1),
            'chartNumberActionUpgradeByCategory'=>$this->chartNumberActionByType(2, 2, 1, 1),
            'chartNumberActionBreakByGoldRange'=>$this->chartNumberActionByType(3, 1, 3, 1),
            'chartNumberActionBreakByAge'=>$this->chartNumberActionByType(4, 1, 4, 1),
            'chartNumberActionUpgradeByLevel'=>$this->chartNumberActionByType(5, 2, 2, 1),
            'chartNumberActionPlayByUser'=>$this->chartNumberActionByType(6, 3, 0, 1),
            'chartTotalGoldBreakByCategory'=>$this->chartNumberActionByType(7, 1, 1, 2),
        );

        $this->listChartId=['chartNumberActionBreakByCategory', 'chartNumberActionUpgradeByCategory', 'chartNumberActionBreakByGoldRange',
                            'chartNumberActionBreakByAge', 'chartNumberActionUpgradeByLevel', 'chartNumberActionPlayByUser',
                            'chartTotalGoldBreakByCategory'];
        $this->addViewsToMain=json_encode(
            $this->addCharts(['chartNumberActionBreakByCategory']).
            $this->addGrid('', 'gridNumberActionBreakByCategory', $this->gridNumberActionByType('tableid_gridNumberActionBreakByCategory', 1, 1, 1), false).
            $this->addCharts(['chartNumberActionUpgradeByCategory']).
            $this->addGrid('', 'gridNumberActionUpgradeByCategory', $this->gridNumberActionByType('tableid_gridNumberActionUpgradeByCategory', 2, 1, 1), false).
            $this->addCharts(['chartNumberActionBreakByGoldRange']).
            $this->addGrid('', 'gridNumberActionBreakByGoldRange', $this->gridNumberActionByType('tableid_gridNumberActionBreakByGoldRange', 1, 3, 1), false).
            $this->addCharts(['chartNumberActionBreakByAge']).
            $this->addGrid('', 'gridNumberActionBreakByAge', $this->gridNumberActionByType('tableid_gridNumberActionBreakByAge', 1, 4, 1), false).
            $this->addCharts(['chartNumberActionUpgradeByLevel']).
            $this->addGrid('', 'gridNumberActionUpgradeByLevel', $this->gridNumberActionByType('tableid_gridNumberActionUpgradeByLevel', 2, 2, 1), false).
            $this->addCharts(['chartNumberActionPlayByUser']).
            $this->addGrid('', 'gridTNumberActionPlayByUser', $this->gridNumberActionByType('tableid_gridNumberActionPlayByUser', 3, 0, 1), false).
            $this->addCharts(['chartTotalGoldBreakByCategory']).
            $this->addGrid('', 'gridTotalGoldBreakByCategory', $this->gridNumberActionByType('tableid_gridTotalGoldBreakByCategory', 1, 1, 2), false).
            ''
        );

        return parent::__index($columnType);
    }

    private function chartNumberActionByType($titleType=1, $actionType=1, $categoryType=1, $reportType=1){
        Log::error("chartNumberPaymentByType");
        if ($titleType==1){
            $title = "Số lượt đập heo theo loại heo";
        } elseif ($titleType==2){
            $title = "Số lượt vỗ béo heo theo loại heo";
        } elseif ($titleType==3){
            $title = "Số lượt đập heo theo mức gold heo đang có";
        } elseif ($titleType==4){
            $title = "Số lượt đập heo theo số ngày tuổi của heo";
        } elseif ($titleType==5){
            $title = "Số heo vỗ béo theo số lần";
        } elseif ($titleType==6){
            $title = "Số user nhận được heo";
            $sql = $this->getSQLTotalNumberAction($actionType, $reportType);
            $options = [    'title' => $title,
                'subtitle' => $this->AppName,
                'yAxis_title' => '#',
                'stack_col' => true,
                'showall' => true,
                'hide_show_button'=>true,
            ];
            return $this->chart1LineOption($sql, false, $options);
        } elseif ($titleType==7){
            $title = "Số vàng nhận từ heo theo loại";
        }
    
        
        $sql = $this->getSQLNumberActionByType($actionType, $categoryType, $reportType);
        $sql2 = $this->getSQLTotalNumberAction($actionType, $reportType);
        $sort = InGameTuteFunction::defaultSort;

        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options, InGameTuteFunction::defaultSort, $sort);
    }

    private function gridNumberActionByType($tid, $actionType, $categoryType, $reportType){
        if ($categoryType == 0) {
            $sql = $this->getSQLTotalNumberAction($actionType, $reportType);
            return $this->createGrid($sql, $tid);
        }
        else {
            $sql = $this->getSQLNumberActionByType($actionType, $categoryType, $reportType);
            $sort = InGameTuteFunction::defaultSort;
            $data = $this->getDataSQLInGame($sql);
            $gridData = $this->pivotdata4grid_withSort($data, 'defaultRSort', $sort);
            $this->fillEmptyData($gridData);
            return $this->createGridData($gridData, $tid);
        }
    }

    // get sql query
    private function getSQLTotalNumberAction($actionType, $reportType, $isChart=true){
        $table = "tute_report_by_pig_event";
        $action = '';
        $report = '';

        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';

        if ($actionType == 1)
            $action = 'BreakPig';
        elseif ($actionType == 2)
            $action = 'UpgradePig';
        elseif ($actionType == 3)
            $action = 'Play';

        if ($reportType == 1) {
            $report = 'NumberActions';
        } else {
            $report = 'SumGold';
        }

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, sum(".$report.") as Total from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and actionID=".$this->quote($action)." GROUP BY ReportDate order by ReportDate"." ".$sort;
        return $sql;
    }

    private function getSQLNumberActionByType($actionType, $categoryType, $reportType, $isChart=true){
        $table = "tute_report_by_pig_event";
        $action = '';
        $group = '';
        $category = '';
        $report = '';

        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';

        if ($actionType == 1)
            $action = 'BreakPig';
        elseif ($actionType == 2)
            $action = 'UpgradePig';
        elseif ($actionType == 3)
            $action = 'Play';

        if ($categoryType != 0) {
            $group = ', ';
            if ($categoryType == 1) {
                $category = 'PigType';
            } elseif ($categoryType == 2) {
                $category = 'PigLevel';
            } elseif ($categoryType == 3) {
                $category = 'GoldRange';
            } elseif ($categoryType == 4) {
                $category = 'PigAge';
            }
        }
        $group = $group.$category;

        if ($reportType == 1) {
            $report = 'NumberActions';
        } else {
            $report = 'SumGold';
        }

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate ".$group.",sum(".$report.") as Total from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and actionID=".$this->quote($action)." GROUP BY ReportDate".$group." order by ReportDate"." ".$sort;
        return $sql;
    }
}