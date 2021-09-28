<?php


namespace App\Http\Controllers\tute;


use App\Http\Controllers\InGameFunction;
use Illuminate\Support\Facades\Log;

class InGameTuteFunction extends InGameFunction
{
    const APP_NAME = 'tute';
    const GAME = 'tute';

    const sortChannel = "sortChannelName";
    const sortMoney = "sortShortenMoney";
    const defaultSort = "defaultSort";
    const defaultRSort = "defaultRSort";

    const byLevel = 1;
    const byAge = 2;
    const byGamePlayed=3;
    const newUserbyGamePlayed=4;
    const byEatPot= 5;

    public function __construct($ListAppTable='', $AppField='AppName', $AddFilterToListApp = '', $conn='pdoReportTool') {
        $this->inGameDB='log_tute';
        parent::__construct($ListAppTable,$AppField,$AddFilterToListApp,$conn); // change table AppList

        $this->host = 'http://10.11.165.10/gsnreport';
        // $this->tabData = [
        //     ['Gold', $this->host.'/game/tute/gold'],
        //     ['Login', $this->host.'/game/tute/login'],
        //     ['Game', $this->host.'/game/tute/game'],
        //     ['Pot', $this->host.'/game/tute/pot'],
        //     ['Journey', $this->host.'/game/tute/journey'],
        //     ['New User', $this->host.'/game/tute/newuser'],
        // ];

    }

    //    override function create script stack chart
    function chart1stack1line_withPrefix_v2($sql1, $sql2, $opt, $xSort=InGameTuteFunction::defaultSort,$ySort=InGameTuteFunction::defaultSort) {
        $data = $this->getDataSQLInGame($sql1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        $options = ['type' => 'column',
            'stackname' => 'Col1'
        ];
        $data_pivot = $this->pivotdata_withSort($data, $xSort, $ySort);
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data_pivot, $options);
        $data = $this->getDataSQLInGame($sql2);
        $options = ['type' => 'line',
            'zIndexAdd' => 2,
            'legendIndexAdd'=>-1,
        ];
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
        $highchartseries = array_merge($arr1, $arr2);

        return $this->script_lineChart($categories, $highchartseries, $opt);
    }

    function chartMultiLine($sql1, $opt, $pivot=false){
        $data = $this->getDataSQLInGame($sql1);
        Log::error($data);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        $options = ['type' => 'line',
        ];
        if ($pivot) $data = $this->pivotdata($data);
        $series = $this->_create_ArrayFor_HighchartSeries($data, $options);
        return $this->script_lineChart($categories,$series, $opt);
    }

    //SQL
    const sql_channel_mapping = "case when ChannelID = 0 then 'Beginner'
                  when ChannelID = 1 then 'Amatuer'
                  when ChannelID = 2 then 'Expert'
                  when ChannelID = 3 then 'Master' end";

    const sql_channel_mapping1 = "case IF ChannelID = 0 as 'Beginner'
                  when ChannelID = 1 then 'Amatuer'
                  when ChannelID = 2 then 'Expert'
                  when ChannelID = 3 then 'Master' end";

    const eatPotRange = "case when GoldRange='0-5K' or GoldRange='5K-10K' then '0-10K'".
                        "else GoldRange end";

    const numberEatPot = "case when EatPot>10 then '>10' else EatPot end";

    function getChannelQuery($column){
        return "SUM(IF(ChannelID=0, ".$column.", 0)) as Beginner, ".
            "SUM(IF(ChannelID=1, ".$column.", 0)) as Amatuer, ".
            "SUM(IF(ChannelID=2, ".$column.", 0)) as Expert, ".
            "SUM(IF(ChannelID=3, ".$column.", 0)) as Master ";
    }

    function getMoneyFormat($columnName){
        return sprintf("case 
        when CAST(%s AS INT)>1000000000 then concat(REPLACE(FORMAT(%s/1000000, 1), '.0', ''), 'B') 
        when CAST(%s AS INT)>1000000 then concat(REPLACE(FORMAT(%s/1000000, 1), '.0', ''), 'M')
        when CAST(%s AS INT)>1000 then concat(REPLACE(FORMAT(%s/1000, 1), '.0', ''), 'K') 
        ELSE %s 
        END as %s", $columnName, $columnName, $columnName,$columnName, $columnName, $columnName, $columnName, $columnName);
    }

    function getSQLTotalNumberUserByType($type, $isChart=true){

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate , Sum(NumValue) as Number_User ".
            "from tute_user_report ".
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=".$type
            ." Group by ReportDate order by ReportDate ".(($isChart)?"":"desc");
        return $sql;
    }

    function getSQLNumberUserByType($type, $isChart=true){
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate , FieldRange, NumValue ".
            "from tute_user_report ".
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and FieldID=".$type." order by ReportDate";
        return $sql;
    }

    const number_range = [100, 50, 25, 20, 15, 10, 5, 3, 2, 1, 0];
    function getNumberRange($columnName, $from=0, $to=100){
        $sql_format = 'case ';
        $length = count(self::number_range);

        for ($i = 0; $i < $length; $i++){
            if (self::number_range[$i]>$to)
                continue;
            if (self::number_range[$i]<$from)
                break;
            if (self::number_range[$i]<$to){
                if (self::number_range[$i]==self::number_range[$i-1]-1)
                    $range = self::number_range[$i];
                else
                    $range = "'".self::number_range[$i]."-".(self::number_range[$i-1]-1)."'";
            } else {
                $range = "'>=".self::number_range[$i]."'";
            }
            $sql_format = $sql_format."when ".$columnName." >= ".self::number_range[$i]." then ".$range." ";
        }
        return $sql_format."end";
    }

    //    Utils function
    // fill empty array element
    function fillEmptyData(array  &$data, $default=0){
        foreach ($data as $key => $value){
            if ($value == null || $value == ''){
                $data[$key] = $default;
            } elseif (is_array($value)){
                $this->fillEmptyData($data[$key], $default);
            }
        }
    }
    // get RecordDate format for chart
    function getChartDateFormat($columnName, $isChart=true){
        if ($isChart)
            return "date_format(".$columnName.",'".$this->formatXDate()."')";
        else return $columnName;
    }



    //    // override function

    function pivotdata_withSort ($dataFromDB, $xSort="defaultSort", $ySort="defaultSort") {
        if (count($dataFromDB) == 0)
            return array();
        $colnames = array_keys($dataFromDB[0]);
        $xaxislist = $this->listunique_valueofcolumn_withSort($dataFromDB,$colnames[0], $xSort);
        if (count($colnames) > 2)
            $yaxislist = $this->listunique_valueofcolumn_withSort($dataFromDB,$colnames[1], $ySort);

        // fill empty
        $pivot = array();
        foreach ($xaxislist as $d) {
            $pivot[$d] = array();
            if (count($colnames) > 2)
                foreach ($yaxislist as $c)
                    $pivot[$d][$c] = null;
            else
                $pivot[$d][$colnames[1]] = null;
        }

        // fill data
        foreach ($dataFromDB as $row)
            if (count($colnames) > 2)
                $pivot[ $row[$colnames[0]]][ $row[$colnames[1] ] ] = $row[$colnames[2]];
            else
                $pivot[$row[$colnames[0]]][$colnames[1]] = $row[$colnames[1]];

        return $pivot;
    }

    function pivotdata4grid_withSort ($dataFromDB, $xSort="defaultSort", $ySort="defaultSort") {
        if (count($dataFromDB) == 0)
            return array();
        $colnames = array_keys($dataFromDB[0]);
        $xaxislist = $this->listunique_valueofcolumn_withSort($dataFromDB,$colnames[0], $xSort);
        $yaxislist = $this->listunique_valueofcolumn_withSort($dataFromDB,$colnames[1], $ySort);

        // fill empty (pivot)
        $pivot = array();
        $xarray = array(); $i=0;
        foreach ($xaxislist as $d) {
            $a = array();
            $a[$colnames[0]] = $d;
            foreach ($yaxislist as $c)
                $a[$c] = '';

            $pivot[] = $a;
            $xarray[$d] = $i;
            $i++;
        }

        // fill data
        foreach ($dataFromDB as $row) {
            $x = $xarray[$row[$colnames[0]]];
            $y = $row[$colnames[1]];
            //if ($x && $y)
            $pivot[$x][$y] = $row[$colnames[2]];
        }

        return $pivot;
    }

    function listunique_valueofcolumn_withSort($dataarr, $column_name='ReportDate', $sort="defaultSort") {
        $result = array();
        foreach($dataarr as $row)
            $result[] = $row[$column_name];
        $result = array_unique($result);

        if ($sort){
            $this->$sort($result);
        }

        $r = array();
        foreach ($result as $a)
            $r[] = $a;
        return $r;
    }

    function defaultSort(&$data){
        sort($data, SORT_NATURAL | SORT_FLAG_CASE);
    }

    function defaultRSort(&$data){
        rsort($data, SORT_NATURAL | SORT_FLAG_CASE);
    }

    static function getChannelValue($name){
        switch ($name){
            case "Beginner":
                return 0;
            case "Amatuer":
                return 1;
            case "Expert":
                return 2;
            case "Master":
                return 3;
            default:
                return -1;
        }
    }

    function sortChannelName(&$data){

        usort($data, function ($a, $b){
            return InGameTuteFunction::getChannelValue($a) > InGameTuteFunction::getChannelValue($b);
        });
    }
    static function getMoneyValue($name){
        $first =  trim(explode('-', $name)[0]);
        $lastChar = substr($first, strlen($first)-1);
        switch ($lastChar){
            case 'K':
                $mul = 1000;
                $first = substr($first, 0, strlen($first)-1);
                break;
            case 'M':
                $mul = 1000000;
                $first = substr($first, 0, strlen($first)-1);
                break;
            case 'B':
                $mul = 1000000000;
                $first = substr($first, 0, strlen($first)-1);
                break;
            default:
                $mul = 1;
        }
        $idx = 0;
        while (!is_numeric($first[$idx]) and $idx < strlen($first)){
            $idx++;
        }
        return ((float) substr($first, $idx))*$mul;
    }

    function sortShortenMoney(&$data){
        usort($data, function ($a, $b){
            return InGameTuteFunction::getMoneyValue($a) > InGameTuteFunction::getMoneyValue($b);
        });
    }
}