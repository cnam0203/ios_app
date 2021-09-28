<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;
use View, PDO;

class InGameFunction extends Chart2018 {

    protected $inGameDB;
    protected $addViews;
    protected $listChartId;
    protected $listChart;
    protected $pageTitle;

    public function __construct($ListAppTable='', $AppField='AppName', $AddFilterToListApp = '', $conn='pdoReportTool') {            
        parent::__construct($ListAppTable,$AppField,$AddFilterToListApp,$conn); // change table AppList        

        $this->addViewsToMain='';
        $this->listChartId=[];
        $this->listChart=[];
        $this->addViews='null';
    }

    public function __index($type='')
    {
        // return View::make('pages.InGameCommon',
        //     array('menu' => $this->menu,
        //         'pagetitle' => $this->pageTitle,
        //         'date' => $this->date,
        //         'selectedApp' => $this->AppName,
        //         'listApp'=> $this->listApp,

        //         'tabView'=>$this->createTabScript(),
        //         'addViewsToMain' => $this->addViewsToMain,

        //         'functions' => $this->createJSFunction($this->listChartId),
        //         'addViews'=>$this->addViews,
        //     )
        // );

        $viewParams = array(
            'pageTitle' => $this->pageTitle,
            'startDate' => $this->date[0],
            'endDate' => $this->date[1],
            'appName' => $this->AppName,
            'charts' => $this->listChart,
        );

        return response()->json([
            'status' => true,
            'data' => $viewParams,
        ]);
    }

    public function getDataSQLInGame($sql){
        $this->pdoIngame->exec("use ".$this->inGameDB);        
        
		$result = array();
		$stmt = $this->pdoIngame->query($sql);
        $result = $stmt->fetchall(PDO::FETCH_ASSOC);
		return $result;	
    }    
    
    public function getDataPDOSQL($pdo, $sql){
        return null;
    }

    function chart1stack1line_1p1np($sql1, $sql2, $opt){
        $data = $this->getDataSQLInGame($sql1);   
        $pivot = $this->pivotdata($data);
        $options = ['type' => 'column',
                    'stackname' => 'Col1'
                    ];
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

        $data = $this->getDataSQLInGame($sql2);   
        $options = ['type' => 'line',
                    'zIndexAdd' => 2,
                    ];      
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        // build high chart        
        $highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate', false);
        
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }

    function chart1stack1line_s1ln($sql1, $sql2, $opt){
        $data = $this->getDataSQLInGame($sql1);   
        $pivot = $this->pivotdata($data);
        $options = ['type' => 'line',                    
                    ];          
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

        $data = $this->getDataSQLInGame($sql2);   
        $options = ['type' => 'column',
                    'stackname' => 'Col1',
                    'zIndexAdd' => 2,
                    ];   
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        // build high chart        
        $highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate', false);
        
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }

    function chart1Line($sql, $title, $subtitle, $yAxis_title, $align='center', $isPivot=false, $showBig=0){        
        $data = $this->getDataSQLInGame($sql);           
        return $this->chart1LineData($data, $title, $subtitle, $yAxis_title, $align,$isPivot, $showBig);
    }

    function chart1LineData($data, $title, $subtitle, $yAxis_title, $align='center', $isPivot=false, $showBig=0){
        $options = ['type' => 'line',
                    ];             
        if($showBig > 0){            
            $visible = $this->getBiggestFields($data, $showBig);            
        }
        if($visible){
            $options['visible'] = $visible;
        } 
        if($isPivot){
            $pivot = $this->pivotdata_withColNamePrefix($data);
            $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
        }else{
            $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
        }

        $highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');        
        
        $opt = [    'title' => $title,
                    'subtitle' =>  $subtitle,
                    'yAxis_title' => $yAxis_title,
                    'legend_align' => $align
                    ];
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }    

    function chart1LineOption($sql,$isPivot, $opt, $prefix=true){
        $data = $this->getDataSQLInGame($sql);        
        $options = ['type' => 'line'
                    ];      
        if($isPivot){
            if($prefix){
                $pivot = $this->pivotdata_withColNamePrefix($data);
            }else{
                $pivot = $this->pivotdata($data);
            }
            $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
        }else{
            $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
        }

        $highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate'); 
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }

    function chart1Stack($sql, $title, $subtitle, $yAxis_title, $isPivot=false){
        $data = $this->getDataSQLInGame($sql);                   
        $options = ['type' => 'column',
                    'stackname' => 'Col1'
                    ];      
        if($isPivot){
            $pivot = $this->pivotdata_withColNamePrefix($data);
            $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
        }else{
            $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
        }

        $highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        
        $opt = [    'title' => $title,
                    'subtitle' =>  $subtitle,
                    'yAxis_title' => $yAxis_title,
                    'stack_col' => true,
                    ];
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }    

    function createGrid($sql,$tid, $datatype=[]) {
        $data = $this->getDataSQLInGame($sql);
        return $this->createGridData($data,$tid, $datatype);
    }

    function createGridData($data,$tid, $datatype=[]) {
        $option = ['tableid' => $tid,
                    'datatype' => $datatype,
                    ];
        return $this->_createGridData_html($data, $option);
    }

    function chart1stack1line_withPrefix($sql1, $sql2, $opt){
        $data = $this->getDataSQLInGame($sql1);   
        $pivot = $this->pivotdata_withColNamePrefix($data);
        $options = ['type' => 'column',
                    'stackname' => 'Col1'
                    ];
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

        $data = $this->getDataSQLInGame($sql2);   
        $options = ['type' => 'line',
                    'zIndexAdd' => 2,
                    ];      
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        // build high chart        
        $highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate', false);
        
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }

    function chartStackDrilldown ($dataArray, $group_params, $value_key, $option) {
        // các value của dataArray là các data tương ứng với các cấp drilldown (không cần theo thứ tự cấp drilldown)
        // data bên trong phải có thông tin của data bên ngoài để match cho drilldown
        // VD: data cấp 1 (bên ngoài)
        // [
        //     "ReportDate" => "03-11"
        //     "Action" => "Play"
        //     "Value" => "521"
        // ]
        //  data cấp 2 (bên trong), (*) là phần phải có
        // [
        //     "ReportDate" => "03-11" (*)
        //     "Action" => "Play" (*)
        //     "SubAction1" => "1"
        //     "Value" => "723"
        // ]

        // lấy tổ hợp các trường hợp của group param
        if (count($dataArray) === 0)
            return "";
        if (count($dataArray[0]) === 0)
            return "";
        
        $combinations_data = $this->getDrillDownCombinationData($dataArray, $group_params, $value_key);

        // design chart
        $design = $this->chartStackDrilldownCombination ($combinations_data, $group_params, $option, $value_key);

        return $design;

    }

    function getDrilldownTotalData($combinations_data, $value_key, $name='Total'){
        $totalData = [];
        foreach($combinations_data as $key => $value){
            if (strpos($key, '|||') === false) {
                foreach($value as $key => $data){
                    if (!$totalData[$key]['name']){
                        $totalData[$key]['name'] = $data['ReportDate'];
                        $totalData[$key]['y'] = $data[$value_key];
                        // $totalData[$key]['type'] = 'line';

                    }
                    else{
                        $totalData[$key]['y'] += $data[$value_key];
                    }
                }
            }
        }
        $totalData = [[
            'name' => $name,
            'data' => $totalData,
            'type' => 'line',
        ]];
        return $totalData;
    }

    function getDrillDownCombinationData($dataArray, $group_params, $value_key){
        // trường hợp data bên ngoài là tổng của data bên trong
        if (count($dataArray) == 1){
            $data = array_pop(array_reverse($dataArray));
            $combinations_data = $this->getDrillDownData($data, $group_params, $value_key);
        }

        // trường hợp data bên không phải là tổng của data bên trong
        if (count($dataArray) > 1){
            $combinations_data = [];
            foreach ($dataArray as $data){
                $combinations_data = $this->appendDrillDownCombinationData($data, $value_key, $combinations_data);
            }
        }

        // fill data, fix show data không theo ngày
        $combinations_data = $this->fillCombinationData($dataArray, $combinations_data, $value_key);
        asort($combinations_data);        
        return $combinations_data;
    }

    function fillCombinationData($dataArray, $combinations_data, $value_key){

        $reportDate = [];
        foreach ($dataArray as $data){
            $date = array_column($data, 'ReportDate');
            $reportDate = array_merge($reportDate, $date);
        }
        $reportDate = array_values(array_unique($reportDate));

        foreach($combinations_data as $key => $data){
            $date = array_column($data, 'ReportDate');
            $noDataDate = array_diff($reportDate, $date);
            if (count($noDataDate) == 0)
                continue;
            
            $firstData = array_pop(array_reverse($data));
            foreach($noDataDate as $date){
                $addData = [
                    'ReportDate' => $date,
                ];

                foreach(array_keys($firstData) as $dataKey){
                    if ($dataKey !== 'ReportDate' and $dataKey !== $value_key){
                        $addData[$dataKey] = $firstData[$dataKey];
                    }
                }
                $addData[$value_key] = 0;
                $data[] = $addData;
            }
            asort($data);
            $data = array_values($data);
            $combinations_data[$key] = $data;
        }

        return $combinations_data;
    }

    function chartStackDrilldownCombination ($combinations_data, $group_params, $option, $value_key) {
        
        // tạo data cho data chính và data cho drilldown
        $data = $this->getDrilldownDrawData($combinations_data, $group_params, $value_key);

        $option['hide_show_button'] = true;
        $option['legend_align'] = 'right';
        $option['is_stacking'] = !isset($option['is_stacking'])? false: $option['is_stacking'];
        $option['changeTitle'] = !isset($option['changeTitle'])? false: $option['changeTitle'];
        $option['changeSubtitle'] = !isset($option['changeSubtitle'])? false: $option['changeSubtitle'];
        $option = $this->getTitleChange($data, $option, $group_params);
        if ($option['is_stacking'])
            if ($option['showTotal'] or !$option['showTotal'])
                $data['series'] = array_merge($data['series'], $this->getDrilldownTotalData($combinations_data, $value_key));
                
        // design chart
        $design = $this->script_stack_drilldown ($option, $data, $group_params);

        return $design;

    }

    function getTitleChange($data, $option, $group_params){
        if (!$option['titleChange']){
            foreach($data['subtitleChange'] as $key => $value){
                $option['titleChange'][$key] = $option['title']."<br/><span style=\"font-size: 75%\">".str_replace('|||', ' - ', $key)."</span>";
            }
        }
        else{
            if (is_array($option['titleChange'])){
                foreach($data['subtitleChange'] as $key => $value){
                    if(!$option['titleChange'][$key]){
                        $option['titleChange'][$key] = $option['title']."<br/><span style=\"font-size: 75%\">".str_replace('|||', ' - ', $key)."</span>";
                    }
                }

            }
            else {
                $titleChange = [];
                foreach($data['subtitleChange'] as $key => $value){
                    $titleChange[$key] = $option['titleChange']."<br/><span style=\"font-size: 75%\">".str_replace('|||', ' - ', $key)."</span>";
                }
                $option['titleChange'] = $titleChange;
            }                
        }
        $option['title'] = $option['title']."<br/><span style=\"font-size: 75%\">".count($group_params)." level clickable chart</span>";

        return $option;
    }

    function appendDrillDownCombinationData($data, $valueName='Value', $combinations_data){

        foreach ($data as $data_value){
            $group_param_value = array();
            foreach($data_value as $data_key => $data_val){
                if ($data_key !== 'ReportDate' and $data_key !== $valueName){
                    $group_param_value[] = $data_val === '' ? 'empty' : $data_val;
                }
            }
            $group_param_value = implode('|||', $group_param_value);
            $combinations_data[$group_param_value][] = $data_value;
        }

        // dd($combinations_data);
        return $combinations_data;
    }
    // lấy tổ hợp tất cả các trường hợp group của group param
    function getDrillDownData($data, $group_params, $value_key){

        $combinations_data = array();
        $GroupParams = $group_params;
        $group_count = count($GroupParams);
        for ($i = 0; $i <= $group_count-1; $i++) {
            foreach($data as $row_data)
            {
                $key_id = '';
                $combination_data = array();
                foreach($GroupParams as $key){
                    $combination_data['ReportDate'] = $row_data['ReportDate'];
                    $key_name = $row_data [$key];
                    if ($key_id === "")
                        $key_id = ($key_name !== "" and !is_null($key_name)) ? $key_id.''.$key_name : $key_id.'empty';
                    else
                        $key_id = ($key_name !== "" and !is_null($key_name)) ? $key_id.'|||'.$key_name : $key_id.'|||empty';
                    $combination_data[$key] = $key_name;
                }
                if (!array_key_exists ($value_key, $combination_data))
                        $combination_data[$value_key] = 0;
                $combination_data[$value_key] += (float) $row_data [$value_key];

                if (array_key_exists ($key_id, $combinations_data)){
                    $is_duplicate = false;
                    foreach($combinations_data[$key_id] as $combi_key => $combi_value){
                        if ($combi_value['ReportDate'] == $combination_data['ReportDate']){
                            $combinations_data[$key_id][$combi_key][$value_key] += (float) $combination_data[$value_key];
                            $is_duplicate = true;
                        }
                    }
                    if(!$is_duplicate)
                            $combinations_data[$key_id][] = $combination_data;
                } else {
                    $combinations_data[$key_id][] = $combination_data;
                }
            }
            array_pop($GroupParams);
        }
        return $combinations_data;
    }
    // tạo data cho data chính và data cho drilldown
    function getDrilldownDrawData($combinations_data, $group_params, $value_key){

        // data chính
        $series_for_root = array();
        // data cho drilldown
        $series_for_drilldown = array();
        // màu cho data
        $colors = ["#7cb5ec", "#434348", "#90ed7d", "#f7a35c", "#8085e9", "#f15c80", "#e4d354", "#2b908f", "#f45b5b", "#91e8e1"];

        $max_length = count($group_params);

        $subtitleChange = [];
        $preDataID = [];
        // loop tất cả các tổ hợp
        foreach($combinations_data as $key => $data){

            $group_param_value = array();
            foreach($data[0] as $data_key => $data_val){
                if ($data_key !== 'ReportDate' and $data_key !== $value_key){
                    $group_param_value[] = $data_val === '' ? 'empty' : $data_val;
                }
            }
            $count = count($group_param_value);

            // tạo id, name của drilldown data và name của drilldown nhỏ hơn của drilldown này
            $series_name = $group_param_value[$count-1];
            $drilldown_id = $group_param_value[0];
            for ($i = 1; $i < $count - 1; $i++) {
                $drilldown_id = $drilldown_id.'|||'.$group_param_value[$i];
            } 
            $drilldown_name = implode('|||', $group_param_value);
            $subtitleChange[$drilldown_name] = implode(' -- ', $group_param_value);

            // tạo data
            $data_array = array();
            foreach($data as $key => $val){
                $stack_col = array(
                    'name' => $val['ReportDate'],
                    'y' => (float) $val[$value_key],
                    'drilldown_name' => (string) $drilldown_name,
                    'drilldown' => $count == $max_length ? false : true,
                );
                $data_array[] = $stack_col;
                $preDataID[(string) $drilldown_name] = (string) $drilldown_name != (string) $drilldown_id ? (string) $drilldown_id: '--firstData--';
            }
            $series_data = array(
                'name' => (string) $series_name,
                'data' => $data_array,
            );

            if ($count == 1){
                $series_for_root[] = $series_data;
            } else {
                // xét xem nếu id của drilldown đã có trong data (đã có drilldowwn data bổ sung cho data trước) thì thêm số vào id để stack cột
                // drilldown data phải chọn màu
                $series_data['type'] = 'column';
                $series_data['stacking'] = 'normal';

                if (!array_key_exists($drilldown_id, $series_for_drilldown)){
                    $series_data['color'] = $colors[0];
                    $series_for_drilldown[(string) $drilldown_id] = $series_data;
                } else {
                    $name_count = 1;
                    $new_key_name = $drilldown_id.''.$name_count;
                    while (array_key_exists($new_key_name, $series_for_drilldown)) {
                        $name_count += 1;
                        $new_key_name = $drilldown_id.''.$name_count;
                    }
                    $series_data['color'] = $colors[fmod($name_count, count($colors))];
                    $series_for_drilldown[(string) $new_key_name] = $series_data;
                }
            }            
        }

        return array(
			'series' => $series_for_root,
            'drilldown' => $series_for_drilldown,
            'subtitleChange' => $subtitleChange,
            'preDataID' => $preDataID,
		);
    }   
}
