<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\InGameEvent;
use App\Http\Controllers\CommonFunction;
use View, PDO, Request;

class InGameEventFunction extends CommonFunction {

    protected $inGameDB;
    protected $addViews;
    protected $listChartId;
    protected $listChart;
    protected $pageTitle;

    public function __construct($ListAppTable='game_report_dis.event_config', $AppField='AppName', $AddFilterToListApp = '', $conn='pdoIngame13') {            

        $this->sqlAppPermission = "select distinct AppName from game_report_dis.event_config where (1=1) {{WHERE}} order by AppName";
        parent::__construct($ListAppTable, $AppField, $AddFilterToListApp, $conn); // change table AppList        

        $this->chart_id = 0;
        $this->addViewsToMain='';
        $this->listChartId=[];
        $this->listChart=[];
        $this->addViews='null';
        $this->pdoInGameEvent = $this->pdoIngame13;
        $this->inGameDB = 'game_report_dis';
        $this->viewEventInfo($conn);
        $this->getAssetPoint('game_report_dis.event_config');
        $this->getActionMeaning('game_report_dis.event_action_config');
    }


    public function index($type='')
    {   

        return View::make('pages.InGameEvent3Field',
            array('menu' => $this->menu,
                'pagetitle' => $this->pageTitle,
                'date' => $this->date,
                'selectedValue1' => $this->AppName,
                'listField1'=> $this->listApp,

                'selectedValue2' => $this->Country,
                'listField2'=> $this->ListCountry,
                
                'selectedValue3' => $this->EventID,
                'listField3'=> $this->ListEventID,

                'addViewsToMain' => $this->addViewsToMain,

                'functions' => $this->createJSFunction($this->listChartId),
                'fieldName1'=>'App Name',
                'fieldName2'=>'Country',
                'fieldName3'=>'Event ID',
                'addViews'=>$this->addViews,
                'comment' =>$this->comment,
                'darkMode'=>$this->darkMode,
            )
        );
    }


    function viewEventInfo($conn){
        $this->ListCountry = $this->getCountryList("game_report_dis.event_config", $conn);
        $this->Country = '';
        $r = $this->madSafety(Request::input('Country'));
        foreach ($this->ListCountry as $k=>$v){
            if ($v == $r) {
                $this->Country = $v;
                break;
            }
        }
        if ($this->Country === '')
            $this->Country = $this->ListCountry[0];
        
        $this->CountryCondition = '';
        if ($this->Country === '--All--')
            $this->CountryCondition = '1=1';
        else
            $this->CountryCondition = "Country = ".$this->quote($this->Country);

        $this->ListEventID = $this->getEventIDList("game_report_dis.event_config", $conn);
        $this->EventID = '';
        $r = $this->madSafety(Request::input('EventID'));
        foreach ($this->ListEventID as $k=>$v){
            if ($v == $r) {
                $this->EventID = $v;
                break;
            }
        }
        if ($this->EventID === '')
            $this->EventID = $this->ListEventID[0];
    }


    function getActionMeaning ($TableName) {
        $sql = "SELECT `Action`, SubAction1, SubAction2 FROM ".$TableName." 
                WHERE AppName = ".$this->quote($this->AppName)."
                and EventName = ".$this->quote($this->EventID)."
                order by `Action`, SubAction1, SubAction2;";
        $this->ActionMeaning = $this->getDataSQLInGame($sql);
    }


    function getAssetPoint($TableName){
        $listAsset = $this->getAssetList($TableName, $this->EventID);
        $this->Asset1 = $listAsset["Asset1"];
        $this->Asset2 = $listAsset["Asset2"];
        $this->Asset3 = $listAsset["Asset3"];
        $listPoint = $this->getPointList($TableName, $this->EventID);
        $this->Point1 = $listPoint["Point1"];
        $this->Point2 = $listPoint["Point2"];
        $this->Point3 = $listPoint["Point3"];
    }


    function getCountryList ($TableName, $conn) {
        $sql = "SELECT distinct Country FROM ".$TableName." WHERE AppName = ".$this->quote($this->AppName)." order by Country;";
        $listCountry = array();	
        $listCountry[] = '--All--';	
        $stmt = $this->$conn->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            if (!is_null($row['Country']) and $row['Country'] !== '')
                $listCountry[] = $row['Country'];
		return $listCountry;
    }


    function getEventIDList ($TableName, $conn) {
        $sql = "SELECT distinct EventID FROM ".$TableName." WHERE AppName = ".$this->quote($this->AppName)." and ".$this->CountryCondition." order by RecordTime desc;";
		$listEventID = array();		
        $stmt = $this->$conn->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            $listEventID[] = $row['EventID'];
		return $listEventID;
    }


    function getAssetList ($TableName) {
        $sql = "SELECT Asset1, Asset2, Asset3 FROM ".$TableName." WHERE EventID = ".$this->quote($this->EventID)." and ".$this->CountryCondition." order by EventID;";
        $listAsset = $this->getDataSQLInGame($sql);
        return $listAsset[0];
    }


    function getPointList ($TableName) {
        $sql = "SELECT Point1, Point2, Point3 FROM ".$TableName." WHERE EventID = ".$this->quote($this->EventID)." and ".$this->CountryCondition." order by EventID;";
        $listPoint = $this->getDataSQLInGame($sql);
        return $listPoint[0];
    }
    

    public function getDataSQLInGame($sql){
        $this->pdoInGameEvent->exec("use ".$this->inGameDB);        
        
		$result = array();
		$stmt = $this->pdoInGameEvent->query($sql);
        $result = $stmt->fetchall(PDO::FETCH_ASSOC);
		return $result;	
    }    


    function addChartDetail($listView, $charts){
        $chart_keys = array_keys($charts);
        $chart_values = array_values($charts);


        for ($i = 0; $i < count($chart_keys); $i++) {
            if (strpos($chart_keys[$i], 'gridgrid') === false) {
                $this->listChart[$chart_keys[$i]] = $chart_values[$i];
                $listView = $listView.$this->addCharts([$chart_keys[$i]]);
            } else {
                $listView = $listView.$chart_values[$i];
            }
        } 
        return $listView;
    }


    protected $chart_id;
    public $listChartName = array();


    function acceptDuplicate(){
        $this->chart_id += 1;
    }

    function getChartName($ChartName){
        $ChartName = $ChartName.$this->chart_id;
        return $ChartName;
    }
    
        
    function chartGroupOverview($TableOrder, $TableName, $ActionID, $SumParam, $ParamName, $GroupParam, $ChartTitle, $ExtraWhereCondition, $chartType){
        $charts = array();

        if ($ActionID == "" or strtolower($ActionID) == "null" or is_null($ActionID)) {
            $ActionIDcondition = "1=1";
        } else {
            $ActionIDcondition="Action=".$this->quote($ActionID);
        }

        $chartDetailName = 'chart'.$TableOrder.'detail';
        $chartDetailName = $this->getChartName($chartDetailName);
        $charts[$chartDetailName] = $this->chartGroupOverviewDetail($TableName, $ActionIDcondition, $SumParam, $ParamName, $GroupParam, $ChartTitle, $ExtraWhereCondition, $chartType);
        
        return $charts;
    }


    function chartGroupOverviewDetail($TableName, $ActionIDcondition, $SumParam, $ParamName, $GroupParam, $ChartTitle, $ExtraWhereCondition, $chartType){        

        if (!is_null($GroupParam)){
            $Order = ", ";
            $Group = ", ";
            $GroupParamArray = explode(",", $GroupParam);
            for ($i = 0; $i < count($GroupParamArray); $i++) {
                $Param = trim($GroupParamArray[$i]);
                $Group = $Group."".$Param;
                $Order = $Order." length(".$Param."), ".$Param;
                if ($i < count($GroupParamArray) - 1){
                    $Group = $Group.", ";
                    $Order = $Order.", ";
                }
            }
        } else {
            $Group = "";
            $Order = "";
        }

        if (!in_array('ExtraAction', $GroupParamArray))
            $ExtraActionCondition = 'ExtraAction is null';
        else 
            $ExtraActionCondition = '1=1';
    
        if (!in_array('ExtraActionValue', $GroupParamArray))
            $ExtraActionValueCondition = 'ExtraActionValue is null';
        else 
            $ExtraActionValueCondition = '1=1';

        $sql1 = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate ".$Group.",
                ".$SumParam." as `".$ParamName."` 
                from ".$TableName." 
                where ".$ActionIDcondition." 
                and ".$ExtraWhereCondition." 
                and ".$ExtraActionCondition." 
                and ".$ExtraActionValueCondition." 
                and ReportDate BETWEEN ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
                and AppName = ".$this->quote($this->AppName)."
                and ".$this->CountryCondition."
                and EventName = ".$this->quote($this->EventID)."
                group by ReportDate ".$Group."
                order by ReportDate ".$Order.";";

        $sql2 = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ".$SumParam." `Tổng ".$ParamName."` from ".$TableName." 
                where ".$ActionIDcondition." 
                and ".$ExtraWhereCondition." 
                and ".$ExtraActionCondition." 
                and ".$ExtraActionValueCondition." 
                and ReportDate BETWEEN ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
                and AppName = ".$this->quote($this->AppName)."
                and ".$this->CountryCondition."
                and EventName = ".$this->quote($this->EventID)."
                group by ReportDate;";

        if ($chartType == 'chart_stack_only') {
            // if ($Action == 'Cycle')
            //     dd($sql1);
            return $this->chart1Stack($sql1, $ChartTitle, $this->AppName." - ".$this->EventID, '#', true, $this->pdoInGameEvent, false);

        } elseif ($chartType == 'chart_multi_line') {
            $options = ['title' => $ChartTitle,
                        'subtitle' => $this->AppName." - ".$this->EventID,
                        'yAxis_title' => '#',
                        'hide_show_button' => true
                        ];

            return $this->chartMultiLine($sql1, $options, '', $this->pdoInGameEvent, false, true);

        } else {
            $options = ['title' => $ChartTitle,
            'subtitle' => $this->AppName." - ".$this->EventID,
            'yAxis_title' => '#',
            'stack_col' => true,
            ];

            return $this->chart1stack1line_1p1np($sql1, $sql2, $options, $this->pdoInGameEvent);
        }
    }


    function gridSumDetailGroup4Param($GridOrder, $TableName, $Action, $SumParam, $ParamName, $GroupParam, $GridTitle, $ExtraWhereCondition){
        
        if ($Action == "" or strtolower($Action) == "null" or is_null($Action)) {
            $ActionIDcondition = "1=1";
        } else {
            $ActionIDcondition="Action=".$this->quote($Action);
        }

        if (!is_null($GroupParam)){
            $Order = ", ";
            $Group = ", ";
            $GroupParamArray = explode(",", $GroupParam);
            for ($i = 0; $i < count($GroupParamArray); $i++) {
                $Param = trim($GroupParamArray[$i]);
                $Group = $Group."".$Param;
                $Order = $Order." length(".$Param."), ".$Param;
                if ($i < count($GroupParamArray) - 1){
                    $Group = $Group.", ";
                    $Order = $Order.", ";
                }
            }
        } else {
            $Group = "";
            $Order = "";
        }

        $ShowParam = $Group."";
        $SumParamArray = explode(",", $SumParam);
        $ParamNameArray = explode(",", $ParamName);
        for ($i = 0; $i < count($SumParamArray); $i++) {
            $Param = trim($SumParamArray[$i]);
            $Name = trim($ParamNameArray[$i]);

            $ShowParam = $ShowParam.", ".$Param." as `".$Name."`";
        }

        if (!in_array('ExtraAction', $GroupParamArray))
            $ExtraActionCondition = 'ExtraAction is null';
        else 
            $ExtraActionCondition = '1=1';

        if (!in_array('ExtraActionValue', $GroupParamArray))
            $ExtraActionValueCondition = 'ExtraActionValue is null';
        else 
            $ExtraActionValueCondition = '1=1';

        $tableID = 'tableid_grid'.$GridOrder;
        $gridID = 'grid'.$GridOrder;

        $sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate ".$ShowParam."
        from ".$TableName." a
        where ReportDate BETWEEN ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
        and a.AppName = ".$this->quote($this->AppName)."
        and ".$this->CountryCondition."
        and a.EventName = ".$this->quote($this->EventID)."
        and ".$ActionIDcondition." 
        and ".$ExtraWhereCondition."
        and ".$ExtraActionCondition." 
        and ".$ExtraActionValueCondition." 
        group by ReportDate ".$Group."
        ORDER BY ReportDate DESC ".$Order.";";

        $grid = $this->createGrid($sql, $tableID, array_fill_keys($GroupParamArray, "STRING"), $this->pdoInGameEvent);
        $grid = $this->addGrid($GridTitle, $gridID, $grid, false, false);

        return $grid;
    }


    function gridTopChangebyKey($TableName, $ActionID, $SubAction1, $Key, $ExtraCondition='1=1', $gridTitle='', $topNum=100, $showName=true, $showAll=true){

        if ($ActionID === '' or is_null($ActionID)){
            $ActionIDCondition = 'a.`Action` = "--All--" and a.SubAction1 = "--All--" and a.SubAction2 = "--All--"';
        } else {
            $ActionIDCondition = "a.`Action` = ".$this->quote($ActionID)."";
        }

        if ($SubAction1 === '' or is_null($SubAction1)){
            $SubAction1Condition = 'a.SubAction1 = "--All--" and a.SubAction2 = "--All--"';
        } else {
            $SubAction1Condition = "a.SubAction1 = ".$this->quote($SubAction1)." and a.SubAction2 = '--All--'";
        }

        if ($Key=='Asset1'){
            if ($this->Asset1 !== NULL){
                $gridTitle = str_replace('Asset1', $this->Asset1, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Asset2'){
            if ($this->Asset2 !== NULL){
                $gridTitle = str_replace('Asset2', $this->Asset2, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Asset3'){
            if ($this->Asset3 !== NULL){
                $gridTitle = str_replace('Asset3', $this->Asset3, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Point1'){
            if ($this->Point1 !== NULL){
                $gridTitle = str_replace('Point1', $this->Point1, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Point2'){
            if ($this->Point2 !== NULL){
                $gridTitle = str_replace('Point2', $this->Point2, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Point3'){
            if ($this->Point3 !== NULL){
                $gridTitle = str_replace('Point3', $this->Point3, $gridTitle);
            } else {
                return '';
            }
        }

        if ($Key=='Quantity'){
            $gridTitle = str_replace('Quantity', 'số lượng', $gridTitle);
        }

        $tableID = 'tableid_gridTopChangebyKey'.$Key.'at'.$ActionID.$SubAction1.$this->convertToID($ExtraCondition);
        $gridID = 'gridTopChangebyKey'.$Key.'at'.$ActionID.$SubAction1.$this->convertToID($ExtraCondition);

        $sql = "select ROW_NUMBER() OVER (ORDER BY a.AppName, a.EventID, ABS(SUM(a.Value)) DESC) AS Rank, a.UserName as UserName, a.UserID as UserID, ABS(SUM(a.Value)) AS Value
        from ".$TableName." a
        where ".$ActionIDCondition."
        and ".$SubAction1Condition."
        and a.`Key` = ".$this->quote($Key)."
        and ".$ExtraCondition."
        and a.ReportDate <= ".$this->quote($this->toDate)." 
        and a.AppName = ".$this->quote($this->AppName)." 
        and ".$this->CountryCondition."
        and a.EventID = ".$this->quote($this->EventID)."
        group by a.AppName, a.EventID, a.UserName, a.UserID
        ORDER BY a.AppName, a.EventID, ABS(SUM(a.Value)) desc
        limit ".$topNum.";";

        $grid = $this->createGrid($sql, $tableID, ['UserID' => 'STRING'], $this->pdoInGameEvent);
        $grid = $this->addGrid($gridTitle, $gridID, $grid, $showName, $showAll);

        return $grid;
    }


    function gridTopLastbyKey($TableName, $ActionID, $SubAction1, $SubAction2, $Key, $gridTitle='', $topNum=100, $showName=true, $showAll=true){

        if ($ActionID == ''){
            $ActionIDCondition = 'a.`Action` = "--All--"';
        } else {
            $ActionIDCondition = "a.Action = ".$this->quote($ActionID)."";
        }

        if ($SubAction1 == ''){
            $SubAction1Condition = 'a.SubAction1 = "--All--"';
        } else {
            $SubAction1Condition = "a.SubAction1 = ".$this->quote($SubAction1)."";
        }

        if ($SubAction2 == ''){
            $SubAction2Condition = 'a.SubAction2 = "--All--"';
        } else {
            $SubAction2Condition = "a.SubAction2 = ".$this->quote($SubAction2)."";
        }

        if ($Key=='Asset1'){
            if ($this->Asset1 !== NULL){
                $gridTitle = str_replace('Asset1', $this->Asset1, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Asset2'){
            if ($this->Asset2 !== NULL){
                $gridTitle = str_replace('Asset2', $this->Asset2, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Asset3'){
            if ($this->Asset3 !== NULL){
                $gridTitle = str_replace('Asset3', $this->Asset3, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Point1'){
            if ($this->Point1 !== NULL){
                $gridTitle = str_replace('Point1', $this->Point1, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Point2'){
            if ($this->Point2 !== NULL){
                $gridTitle = str_replace('Point2', $this->Point2, $gridTitle);
            } else {
                return '';
            }
        }
        if ($Key=='Point3'){
            if ($this->Point3 !== NULL){
                $gridTitle = str_replace('Point3', $this->Point3, $gridTitle);
            } else {
                return '';
            }
        }

        if ($Key=='Quantity'){
            $gridTitle = str_replace('Quantity', 'số lượng', $gridTitle);
        }

        $tableID = 'tableid_gridTopLastbyKey'.$Key.'at'.$ActionID.$SubAction1.$SubAction2;
        $gridID = 'gridTopLastbyKey'.$Key.'at'.$ActionID.$SubAction1.$SubAction2;
        $gridTitle = $gridTitle." (".$this->toDate.")";

        $sql = "select * from 
        (select date_format(a.ReportDate,'%Y-%m-%d') ReportDate, 
        ROW_NUMBER() OVER (PARTITION by a.ReportDate, a.AppName, a.EventID ORDER BY a.ReportDate desc, a.AppName, a.EventID, ABS(SUM(a.Value)) DESC) AS Rank, 
        a.UserName as UserName, a.UserID as UserID, ABS(SUM(a.Value)) AS Value
        from ".$TableName." a
        where ".$ActionIDCondition."
        and ".$SubAction1Condition."
        and ".$SubAction2Condition."
        and a.`Key` = ".$this->quote($Key)."
        and a.ReportDate = ".$this->quote($this->toDate)."
        and a.AppName = ".$this->quote($this->AppName)."
        and ".$this->CountryCondition."
        and a.EventID = ".$this->quote($this->EventID)." 
        group by a.ReportDate, a.AppName, a.EventID, a.UserName, a.UserID
        ORDER BY a.ReportDate desc, a.AppName, a.EventID, ABS(SUM(a.Value)) desc) t
        where t.Rank <= ".$topNum.";";

        $grid = $this->createGrid($sql, $tableID, ['UserID' => 'STRING'], $this->pdoInGameEvent);
        $grid = $this->addGrid($gridTitle, $gridID, $grid, $showName, $showAll);

        return $grid;
    }
    

    function getDrilldownStackView($table_name, $sum_param, $group_params, $option, $special_condition='1=1', $is_show_grid=true, $not_show=[]){

        $chart = $this->getChartDrilldownStack($table_name, $sum_param, $group_params, $option, $special_condition);
        $grid = $is_show_grid ? $this->getGridDrilldownStack($table_name, $sum_param, $group_params, $special_condition, $not_show) : '';       

        return $chart.$grid;
    }


    function getUserDrilldownStackView($table_name, $sum_param, $group_params, $option, $special_conditions=[], $is_show_grid=true){

        if(count($group_params) !== count($special_conditions)){
            echo "<p style='color:red'><h2>group_params and special_conditions must has same size.</h2></p>";
            return;
        }

        $chart = $this->getChartUserDrilldownStack($table_name, $sum_param, $group_params, $option, $special_conditions);
        $grid = $is_show_grid ? $this->getUserGridDrilldownStack($table_name, $sum_param, $group_params, $special_conditions) : '';       

        return $chart.$grid;
    }

    
    function getChartDrilldownStack($table_name, $sum_param, $group_params, $option, $special_condition='1=1'){

        $id = $table_name.implode('', $group_params).$sum_param.$special_condition;
        $id = $this->convertToID($id);
        $chart_id = 'chart'.$id;

        $chart_design = $this->chartEventStackDrilldown($table_name, $sum_param, $group_params, $option, $special_condition);
        $this->listChart[$chart_id] = $chart_design;
        $chart_design = $this->addCharts([$chart_id]);

        return $chart_design;
    }

    function getChartUserDrilldownStack($table_name, $sum_param, $group_params, $option, $special_conditions=[]){
        
        $id = $table_name.implode('', $group_params).$sum_param.implode('', $special_conditions);
        $id = $this->convertToID($id);
        $chart_id = 'chart'.$id;

        $chart_design = $this->chartUserStackDrilldown($table_name, $sum_param, $group_params, $option, $special_conditions);
        $this->listChart[$chart_id] = $chart_design;
        $chart_design = $this->addCharts([$chart_id]);

        return $chart_design;
    }


    function getGridDrilldownStack($table_name, $sum_param, $group_params, $special_condition='1=1', $not_show=[]){
        $grid_option = array();
        foreach ($group_params as $key => $value){
            $grid_option[$value] = 'STRING';
        }

        $grid_design = '';
        $group_param_count = count($group_params);
        for ($i = 1; $i <= $group_param_count; $i++) {
            if (in_array($i, $not_show))
                continue;

            $group_param = array_slice($group_params, 0, $i);

            $grid_id = 'grid'.$table_name.implode('', $group_param).$sum_param.$special_condition;
            $grid_id = $this->convertToID($grid_id);
            $table_id = 'tableid_'.$grid_id;

            $sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, 
                    ".implode(', ', $group_param).",
                    ".$sum_param." as Total
                    from ".$table_name." 
                    where ".$special_condition." 
                    and ReportDate BETWEEN ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
                    and AppName = ".$this->quote($this->AppName)."
                    and ".$this->CountryCondition."
                    and EventName = ".$this->quote($this->EventID)."
                    group by ReportDate, ".implode(', ', $group_param)."
                    order by ReportDate desc, ".implode(', ', $group_param).";";
            
            $grid = $this->createGrid($sql, $table_id, $grid_option, $this->pdoInGameEvent);
            $grid_design = $grid_design.$this->addGrid('Data theo '.implode(' / ', $group_param), $grid_id, $grid, true);
        }
        return $grid_design;
    }


    function getUserGridDrilldownStack($table_name, $sum_param, $group_params, $special_conditions=[]){
        $grid_option = array();
        foreach ($group_params as $key => $value){
            $grid_option[$value] = 'STRING';
        }

        $grid_design = '';
        $group_param = array();

        foreach ($group_params as $key => $value){
            $group_param[] = $value;
            $grid_id = 'grid'.$table_name.implode('', $group_param).$sum_param.$special_conditions[$key];
            $grid_id = $this->convertToID($grid_id);
            $table_id = 'tableid_'.$grid_id;

            $sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, 
                    ".implode(', ', $group_param).",
                    ".$sum_param." as Total
                    from ".$table_name." 
                    where ".$special_conditions[$key]." 
                    and ReportDate BETWEEN ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
                    and AppName = ".$this->quote($this->AppName)."
                    and ".$this->CountryCondition."
                    and EventName = ".$this->quote($this->EventID)."
                    group by ReportDate, ".implode(', ', $group_param)."
                    order by ReportDate desc, ".implode(', ', $group_param).";";

            $grid = $this->createGrid($sql, $table_id, $grid_option, $this->pdoInGameEvent);
            $grid_design = $grid_design.$this->addGrid('Data theo '.implode(' / ', $group_param), $grid_id, $grid, true);
        }
        return $grid_design;
    }


    function chartEventStackDrilldown ($TableName, $SumParam, $group_params, $option, $special_condition='1=1') {

        // lấy data khi group theo tất cả group param
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        ".implode(', ', $group_params).",
        ".$SumParam." as Total
        from ".$TableName." 
        where ".$special_condition." 
        and ReportDate BETWEEN ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
        and AppName = ".$this->quote($this->AppName)."
        and ".$this->CountryCondition."
        and EventName = ".$this->quote($this->EventID)."
        group by ReportDate, ".implode(', ', $group_params)."
        order by ReportDate, ".implode(', ', $group_params).";";
        $data = $this->getDataSQLInGame($sql); 

        $design = $this->chartStackDrilldown ($data, $group_params, "Total", $option);

        return $design;
    }


    function chartUserStackDrilldown ($TableName, $SumParam, $group_params, $option, $special_conditions='1=1') {

        $combinations_data = array();
        $group_param = array();

        foreach ($group_params as $key => $value){
            $group_param[] = $value;
            // lấy data khi group theo từng group param nhỏ
            $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
            ".implode(', ', $group_param).",
            ".$SumParam." as Value
            from ".$TableName." 
            where ".$special_conditions[$key]." 
            and ReportDate BETWEEN ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
            and AppName = ".$this->quote($this->AppName)."
            and ".$this->CountryCondition."
            and EventName = ".$this->quote($this->EventID)."
            group by ReportDate, ".implode(', ', $group_param)."
            order by ReportDate, ".implode(', ', $group_param).";";
            $data = $this->getDataSQLInGame($sql); 

            foreach ($data as $data_value){
                $group_param_value = array();
                foreach($data_value as $data_key => $data_val){
                    if ($data_key !== 'ReportDate' and $data_key !== 'Value'){
                        $group_param_value[] = $data_val === '' ? 'empty' : $data_val;
                    }
                }
                $group_param_value = implode('_', $group_param_value);

                $combinations_data[$group_param_value][] = $data_value;
            }
        }

        // dd($combinations_data);
        $design = $this->chartStackDrilldownCombination ($combinations_data, $group_params, $option);

        return $design;
    }


    function chartMultiGroup ($table_name, $total_param, $group_params, $title, $special_condition='1=1', $chart_type='stack_only', $show_percent=false, $not_show=[], $show_grid=true) {

        if (!in_array('ExtraAction', $group_params)){
            $ExtraActionCondition = 'ExtraAction is null';
            $ExtraActionValueCondition = 'ExtraActionValue is null';
        } else {
            $ExtraActionCondition = 'ExtraAction is not null';
            $ExtraActionValueCondition = 'ExtraActionValue is not null';
        }

        $special_condition = $special_condition.' and '.$ExtraActionCondition.' and '.$ExtraActionValueCondition;

        $sql = "select date_format(ReportDate, '".$this->formatXDate()."') ReportDate, ".implode(', ', $group_params).", ".$total_param." as Total
                from ".$table_name."
                where ".$special_condition."
                and ReportDate BETWEEN ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
                and AppName = ".$this->quote($this->AppName)."
                and EventName = ".$this->quote($this->EventID)."
                group by ReportDate, ".implode(', ', $group_params)."
                order by ReportDate, ".implode(', ', $group_params).";";
        $data = $this->getDataSQLInGame($sql);

        $options = array (
                    'chart_type' => $chart_type,
                    'title' => $title,
                    'subtitle' => $this->AppName." - ".$this->EventID,
                    'yAxis_title' => '#',
                    'stack_col' => true,
                    'is_negative' => true,
                    'is_show_percent' => $show_percent
                );

        $table_id = $table_name.implode('', $group_params).$total_param.$special_condition.$chart_type;
        $table_id = $this->convertToID($table_id);

        $charts = $this->chartMultiGroupData($table_id, $data, $group_params, "Total", $options, $not_show, $show_grid);

        return $charts;

    }

    function chartMultiGroupData ($table_id, $data, $group_params, $value_key, $options, $not_show, $show_grid) {

        // lấy tổ hợp các trường hợp của group param
        $combinations_data = $this->getDrillDownCombinationData($data, $group_params, $value_key);

         // tạo chart cho tất cả các trường hợp group
        $charts = $this->getMultiGroupData($combinations_data, $options, $table_id, $not_show, $show_grid);

        return $charts;

    }

    // tạo data cho chart multi group
    function getMultiGroupData($combinations_data, $options, $table_id, $not_show, $show_grid){

        $charts = array();
        $data = array();
        // loop tất cả các tổ hợp, gộp data các trường hợp cùng loại group
        foreach($combinations_data as $key => $value){
            $group_param_value = array();
            foreach($value[0] as $data_key => $data_val){
                if ($data_key !== 'ReportDate' and $data_key !== 'Value'){
                    $group_param_value[] = $data_val;
                }
            }
            array_pop($group_param_value);
            $data_key = implode('_', $group_param_value) === "" ? "root" : implode('_', $group_param_value);
            if (!array_key_exists ($data_key, $data))
                $data[$data_key] = array();
            $data[$data_key] = array_merge($data[$data_key], $value);
        }

        // loop tất cả các tổ hợp, vẽ chart cho từng tổ hợp
        foreach($data as $key => $value){
            $group_param_key = array();
            $group_param_value = array();
            foreach($value[0] as $data_key => $data_val){
                if ($data_key !== 'ReportDate' and $data_key !== 'Value'){
                    $group_param_key[] = $data_key;
                    $group_param_value[] = $data_val;
                }
            }

            // check xem có vẽ group này không
            $count = count($group_param_key);
            if (in_array($count, $not_show))
                continue;
                
            $group_param = $group_param_key[$count - 1];
            $group_param_conditions = "";

            // điều kiện cho title
            for ($i = 0; $i < $count - 1; $i++) {
                if ($group_param_value[$i] !== ""){
                    $group_param_conditions = $group_param_conditions === "" ? "" : $group_param_conditions." ";
                    $group_param_conditions = $group_param_conditions."".$group_param_key[$i]."='".$group_param_value[$i]."'";
                }
                if ($group_param_key[$i] === 'Action')
                    $action_condition = $group_param_value[$i];
                elseif ($group_param_key[$i] === 'SubAction1')
                    $subaction1_condition = $group_param_value[$i];
                
                // xoá các group param to
                foreach($value as $data_key => $data_val){
                    unset($value[$data_key][$group_param_key[$i]]);
                }
            }

            // nếu chỉ có 1 loại group thì không vẽ
            if ($key <> 'root'){
                $param_names = array();
                foreach($value as $data_key => $data_val)
                    if (!in_array($data_val[$group_param], $param_names))
                        $param_names[] = $data_val[$group_param];
                if (count($param_names) <= 1)
                    continue;
            }

            // lấy name cho group param chính để show ở title
            $group_param_meaning = $group_param;
            if ($group_param === 'SubAction1'){
                foreach ($this->ActionMeaning as $action_key => $action_value) {
                    if (array_key_exists('Action', $action_value) and !is_null($action_condition)){
                        if (array_search($action_condition, $action_value) and is_null($action_value['SubAction2']))
                            $group_param_meaning = $action_value[$group_param];
                    }
                }
            } elseif ($group_param === 'SubAction2'){
                foreach ($this->ActionMeaning as $action_key => $action_value) {
                    if (array_key_exists('Action', $action_value) and !is_null($action_condition)){
                        if (array_search($action_condition, $action_value) and array_search($subaction1_condition, $action_value)){
                            $group_param_meaning = $action_value[$group_param];
                        }                            
                    }
                }
                if ($group_param_meaning = $group_param){
                    foreach ($this->ActionMeaning as $action_key => $action_value) {
                        if (array_key_exists('Action', $action_value) and !is_null($action_condition)){
                            if (array_search($action_condition, $action_value) and is_null($action_value['SubAction1'])){
                                $group_param_meaning = $action_value[$group_param];
                            }   
                        }
                    }
                }
            }

            // vẽ chart
            $new_options = $options;
            $new_options['title'] = $options['title'].' theo '.$group_param_meaning.'<br/>'.$group_param_conditions;
            $new_options['hide_show_button'] = true;

            if ($options['chart_type'] == 'stack_only') {
                $charts[$table_id.$key.'Detail'] = $this->chart1StackData($value, $new_options);

            } elseif ($options['chart_type'] == 'multi_line') {
                $charts[$table_id.$key.'Detail'] = $this->chartMultiLineData($value, $new_options, '', false, true);
            }

            if ($options['is_show_percent']){
                $new_options['title'] = 'Tỷ lệ '.$new_options['title'];
                $charts[$table_id.$key.'Percent'] = $this->chart1stackPercentData($value, $new_options);
            }

            // add grid
            if ($show_grid){
                $sort = array();
                foreach($value as $k=>$v) {
                    $sort['ReportDate'][$k] = $v['ReportDate'];
                    $sort[$group_param][$k] = $v[$group_param];
                }
                array_multisort($sort['ReportDate'], SORT_DESC, $sort[$group_param], SORT_ASC, $value);
                $tableID = 'tableid_grid'.$table_id.$key;
                $gridID = 'grid'.$table_id.$key;
                $grid = $this->createGridData($value, $tableID);
                // $grid = $this->addGrid('Data', $gridID, $grid, false, false);
                $charts['grid'.$gridID] = $this->addGrid('Data', $gridID, $grid, false, false);
            }
        }
        
        return $charts;
    }


    function chart1StackData($data, $options, $isPivot=true, $isPrefix=false, $align='auto'){       
  
        $opts = ['type' => 'column',
                'stackname' => 'Col1'
                ];  

        if($isPivot){
            if ($isPrefix){
                $pivot = $this->pivotdata_withColNamePrefix($data);
            } else {
                $pivot = $this->pivotdata($data);
            }
            $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $opts);
        }else{
            $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $opts, true);
        }

        $highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

        if($align==='auto'){
            $align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
        }

        $options['legend_align'] = $align;
        return $this->script_lineChart($categories, $highchartseries, $options);
    }


    function chartMultiLineData ($data, $opt, $dataopt='', $isPrefix=True, $showTop=false, $numField=5, $showOrder=true, $align='auto') { // pivot sql (same chartLine)

        if ($isPrefix){
            $pivot = $this->pivotdata_withColNamePrefix2020($data);
        } else {
            $pivot = $this->pivotdata2020($data);
        }
        if ($showTop){
            $visibleItems = $this->getBiggestFields($pivot, $numField, $showOrder);
        } else {
            $visibleItems = array();
        }
    
        if ($dataopt == ''){
            $dataopt = ['type' => 'line',
                        'visible' => $visibleItems];
        }      
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $dataopt);        
        if($align==='auto'){
            $align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
        }
        $opt['legend_align'] = $align;
    
        $highchartseries = array_merge($arr1);        
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

        return $this->script_lineChart($categories, $highchartseries, $opt);
    }


    function chart1stackPercentData($data, $options, $align='auto', $isPivot=true, $showBig=0, $isBig = true, $pivotPrefix=false){
        $opts['type'] = 'column';
        $opts['stackname'] = 'Col1';
        $opts['stacking'] = 'percent';

        if($isPivot){            
            $rData = $this->pivotdata2020($data, $pivotPrefix);
        }else{
            $rData = $data;
        }       
        
        if($showBig > 0){
            $visible = $this->getBiggestFields($rData, $showBig, $isBig);
        }

        if($visible){
            $opts['visible'] = $visible;            
        } 

        if($isPivot){
            $arr1 = $this->_create_ArrayFor_HighchartSeries($rData, $opts);          
        }else{
            $arr1 = $this->_create_ArrayFor_HighchartSeries($rData, $opts, true);
        }

        $highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        
        if($align==='auto'){
            $align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
        }

        $options['legend_align'] = $align;
        $options['tooltip'] = 'pointFormat: \'<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>\',
                                ';

        return $this->script_lineChart($categories, $highchartseries, $options);
    }


    function convertToID($id_string){
        $id_string = htmlentities($id_string);
        $id_string = str_replace(" ", "", $id_string);
        $id_string = preg_replace('/\s+/', '', $id_string);
        $id_string = preg_replace('/[^A-Za-z0-9\-]/', '', $id_string);
    
        return $id_string;
    }
}
