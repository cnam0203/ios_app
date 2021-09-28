<?php


namespace App\Http\Controllers\tute;

use App\Http\Controllers\InGameFunction;
use Illuminate\Support\Facades\Log;

class InGameTutePayment extends InGameTuteFunction
{
    function index($columnType = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->pageTitle='Tute Payment Report';

        $this->listChart = array(
            'chartNumberPaymentByMethod'=>$this->chartNumberPaymentByType(1, 1),
            'chartNumberPaymentByMethodLine'=>$this->chartNumberPaymentByType(1, 1, true),
            'chartNumberPaymentByChargeLevel'=>$this->chartNumberPaymentByType(2, 1),
            'chartNumberPaymentByChargeLevelLine'=>$this->chartNumberPaymentByType(2, 1, true),
            'chartNumberPaymentByGoldRange'=>$this->chartNumberPaymentByType(3, 1),
            'chartNumberPaymentByGoldRangeLine'=>$this->chartNumberPaymentByType(3, 1, true),
            'chartNumberPaymentByUserAge'=>$this->chartNumberPaymentByType(4, 1),
            'chartNumberPaymentByUserAgeLine'=>$this->chartNumberPaymentByType(4, 1, true),
            'chartNumberPaymentByMethodA30'=>$this->chartNumberPaymentByType(1, 2),
            'chartNumberPaymentByChargeLevelA30'=>$this->chartNumberPaymentByType(2, 2),
            'chartNumberPaymentByGoldRangeA30'=>$this->chartNumberPaymentByType(3, 2),
            'chartNumberPaymentByUserAgeA30'=>$this->chartNumberPaymentByType(4, 2),
        );

        $this->listChartId=['chartNumberPaymentByMethod', 'chartNumberPaymentByMethodLine', 'chartNumberPaymentByChargeLevel', 'chartNumberPaymentByChargeLevelLine',
        'chartNumberPaymentByGoldRange', 'chartNumberPaymentByGoldRangeLine','chartNumberPaymentByUserAge', 'chartNumberPaymentByUserAgeLine',
        'chartNumberPaymentByMethodA30', 'chartNumberPaymentByChargeLevelA30', 'chartNumberPaymentByGoldRangeA30', 'chartNumberPaymentByUserAgeA30'];
        $this->addViewsToMain=json_encode(
            $this->addCharts(['chartNumberPaymentByMethod']).
            $this->addGrid('', 'gridNumberPaymentByMethod', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByMethod', 1, 1), false).
            $this->addCharts(['chartNumberPaymentByMethodLine']).
            $this->addGrid('', 'gridNumberPaymentByMethodLine', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByMethodLine', 1, 1), false).
            $this->addCharts(['chartNumberPaymentByChargeLevel']).
            $this->addGrid('', 'gridNumberPaymentByChargeLevel', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByChargeLevel', 2, 1), false).
            $this->addCharts(['chartNumberPaymentByChargeLevelLine']).
            $this->addGrid('', 'gridNumberPaymentByChargeLevelLine', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByChargeLevelLine', 2, 1), false).
            $this->addCharts(['chartNumberPaymentByGoldRange']).
            $this->addGrid('', 'gridNumberPaymentByGoldRange', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByGoldRange', 3, 1), false).
            $this->addCharts(['chartNumberPaymentByGoldRangeLine']).
            $this->addGrid('', 'gridNumberPaymentByGoldRangeLine', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByGoldRangeLine', 3, 1), false).
            $this->addCharts(['chartNumberPaymentByUserAge']).
            $this->addGrid('', 'gridNumberPaymentByUserAge', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByUserAge', 4, 1), false).
            $this->addCharts(['chartNumberPaymentByUserAgeLine']).
            $this->addGrid('', 'gridNumberPaymentByUserAgeLine', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByUserAgeLine', 4, 1), false).
            $this->addCharts(['chartNumberPaymentByMethodA30']).
            $this->addGrid('', 'gridNumberPaymentByMethodA30', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByMethodA30', 1, 2), false).
            $this->addCharts(['chartNumberPaymentByChargeLevelA30']).
            $this->addGrid('', 'gridNumberPaymentByChargeLevelA30', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByChargeLevelA30', 2, 2), false).
            $this->addCharts(['chartNumberPaymentByGoldRangeA30']).
            $this->addGrid('', 'gridNumberPaymentByGoldRangeA30', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByGoldRangeA30', 3, 2), false).
            $this->addCharts(['chartNumberPaymentByUserAgeA30']).
            $this->addGrid('', 'gridNumberPaymentByUserAgeA30', $this->gridNumberPaymentByType('tableid_gridNumberPaymentByUserAgeA30', 4, 2), false).
            ''
        );

        return parent::__index($columnType);
    }

    private function chartNumberPaymentByType($columnType=1, $dateType=1, $isLine=false){
        Log::error("chartNumberPaymentByType");
        if ($columnType==1){
            $title = "Số lượng Payment theo Phương thức";
        } elseif ($columnType==2){
            $title = "Số lượng Payment theo Mức nạp";
        } elseif ($columnType==3){
            $title = "Số lượng Payment theo GoldRange";
        }  elseif ($columnType==4){
            $title = "Số lượng Payment theo Ngày tuổi";
        }

        if ($dateType==2)
            $title = $title.' (A30)';
        
        $sql = $this->getSQLNumberPaymentByType($columnType, $dateType);
        $sql2 = $this->getSQLTotalNumberPayment($dateType);
        $sort = InGameTuteFunction::defaultSort;

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

    private function gridNumberPaymentByType ($tid, $columnType=1, $dateType=1){
        $sql = $this->getSQLNumberPaymentByType($columnType, $dateType, false);
        $sort = InGameTuteFunction::defaultSort;
        $data = $this->getDataSQLInGame($sql);
        $gridData = $this->pivotdata4grid_withSort($data, 'defaultRSort', $sort);
        $this->fillEmptyData($gridData);
        return $this->createGridData($gridData, $tid);
    }

    // get sql query
    private function getSQLTotalNumberPayment($dateType=1, $isChart=true){
        $table = "tute_report_by_payment";
        if ($isChart)
            $sort = '';
        else
            $sort = 'desc';

        if ($dateType==1) 
            $reportDate = $this->getChartDateFormat("ReportDate", $isChart);
        else
            $reportDate = "DATE_FORMAT(ReportDate,'%Y-%m')";

        $sql = "select ".$reportDate." as ReportDate, sum(NumberValue) as TotalPayment from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ".$reportDate."order by ".$reportDate." ".$sort;
        return $sql;
    }

    private function getSQLNumberPaymentByType($columnType, $dateType, $isChart=true){
        $table = "tute_report_by_payment";
        $column = '';

        if ($columnType == 1)
            $column = 'Method';
        elseif ($columnType == 2)
            $column = 'ChargeLevel';
        elseif ($columnType == 3)
            $column = 'GoldRange';
        elseif ($columnType == 4)
            $column = 'UserAge';

        if ($dateType==1) 
            $reportDate = $this->getChartDateFormat("ReportDate", $isChart);
        else
            $reportDate = "DATE_FORMAT(ReportDate,'%Y-%m')";

        $sql = "select ".$reportDate." as ReportDate, ".$column.",sum(NumberValue) as NumberPayment from ".$table.
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." GROUP BY ".$reportDate.", ".$column." order by ".$reportDate;
        return $sql;
    }
}