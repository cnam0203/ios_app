<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*

CHART
	chartLine ($sql, $opt, $dataopt='', $pdo='') : NOT pivot , multi lines by multi column
	chartMultiLine ($sql, $opt, $dataopt='') : 	pivot data & draw multi line/column
	chartStack1Line ($sqlstack, $sqlline, $opt, $datastackopt='', $datalineopt='', $pdo='') : 	pivot stackdata   Stack col + 1 line
	chartStackMultiLine ($sqlstack, $sqlline, $opt, $datastackopt='', $datalineopt='', $pdo='', $listparam=[]) :   pivot stackdata+linedata 	Stack col + multi line
	
	chartLinked_MultiGroup ($sql, $opt, $dataopt=[], $pdo='') :  Pivot & linked >= 3th columns. 
	
	chart2Y_NotPivot_LineLine ($sqlY1, $sqlY2, $opt=[], $dataopt=[], $pdo='')
	
GRID
	createGrid2020($sql, $option)


$dataopt = array()
	type				line (default) / column / ...
	invisible/visible	array(SeriNames)
	IsPrefixSeries		true (default) / false


$listparam =  					list of parameters
	['istr_colname'=>'', 'istr_listvalue'=>[],  		change all string in 1 col of sql to same-case-sensitive value
	]

*/

namespace App\Http\Controllers;
use View, PDO;
define('MAX_LEGEND_CENTER',15);

class CommonFunction extends Chart2018 {

    protected $inGameDB;
    protected $addViews;
    protected $listChartId;
    protected $listChart;
    protected $pageTitle;
    protected $field1Title='AppName';

    public function __construct($ListAppTable='', $AppField='AppName', $AddFilterToListApp = '', $conn='pdoReportTool') {            
        parent::__construct($ListAppTable,$AppField,$AddFilterToListApp,$conn); // change table AppList        

        $this->addViewsToMain='';
        $this->listChartId=[];
        $this->listChart=[];
        $this->addViews='null';
        $this->pageInfo = "";
        $this->field1Title='AppName';
        $this->currentApp = null;
    }

    public function __index($listApp, $type='', $addParam=[], $view='pages.Common1FieldAnnotation')
    {                
        // if($this->listChart !== null){
        //     $this->listChartId=array_keys($this->listChart); 	
        // }
        // if($this->currentApp === null){
        //     $this->currentApp = $this->AppName;
        // }        
        
        $viewParams = array(
            'pageTitle' => $this->pageTitle,
            'startDate' => $this->date[0],
            'endDate' => $this->date[1],
            'appName' => $this->AppName,
            'listAppNames'=> $listApp,
            'charts' => $this->listChart,
            // 'addViewsToMain' => $this->addViewsToMain,
            // 'functions' => $this->createJSFunction($this->listChartId),
            // 'addViews'=>$this->addViews,
            // 'annotation'=>$this->getListAnnotation($this->currentApp),
            // 'eventHighlight'=> $this->addEventHightLight(),
            // 'pageInfo'=>$this->pageInfo,
            // 'darkMode'=>$this->darkMode,
            // 'field1Title'=>$this->field1Title,
            // 'notifyNewURL'=>$this->notifyNewURL,
            // 'tabView'=>$this->tabScript,            
        );
        $viewParams=array_merge($viewParams, $addParam);        
        return response()->json([
            'status' => true,
            'data' => $viewParams,
        ]);
    }

	// ---------------------------- Chart ------------------------
	function chartLine ($sql, $opt, $dataopt='', $pdo='') { // NOT pivot (same chartMultiLine)
		if ($pdo === '') 	$data = $this->getDataSQL($sql);
		else 				$data = $this->getDataPDOSQL($pdo, $sql);
		if ($dataopt == '')
			$dataopt = ['type' => 'line'];
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $dataopt, true);        

        $highchartseries = array_merge($arr1);        
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        $opt = array_merge($opt, ['hide_show_button'=>true]);
		return $this->script_lineChart($categories, $highchartseries, $opt);
    }
    
	function chartMultiLine ($sql, $opt, $dataopt='', $pdo='', $isPrefix=True, $showTop=false, $numField=5, $showOrder=true,$align='auto') { // pivot sql (same chartLine)
		if ($pdo === '') 	$data = $this->getDataSQL($sql);
		else 				$data = $this->getDataPDOSQL($pdo, $sql);
		
		if ($dataopt == '') {
			$dataopt = [];
			$dataopt['type'] = 'line';
		}
		
		if (isset($dataopt['IsPrefixSeries'])) // dataopt is more priority
			if ($dataopt['IsPrefixSeries'])
				$pivot = $this->pivotdata_withColNamePrefix2020($data);
			else
				$pivot = $this->pivotdata2020($data);
		elseif ($isPrefix) {
			$pivot = $this->pivotdata_withColNamePrefix2020($data);
		} 
		else {
			$pivot = $this->pivotdata2020($data);
		}
		if ($showTop){
			$visibleItems = $this->getBiggestFields($pivot, $numField, $showOrder);
			$dataopt['visible'] = $visibleItems;
		}

		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $dataopt);
		
		if (!isset($opt['legend_align'])) {
			if($align==='auto'){
				$align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
			}
			$opt['legend_align'] = $align;
		}

		$highchartseries = array_merge($arr1);        
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		return $this->script_lineChart($categories, $highchartseries, $opt);
	}
    
	function chartStack1Line ($sqlstack, $sqlline, $opt, $datastackopt='', $datalineopt='', $pdo='') { // pivot stackdata
		if ($pdo === '') 	$data = $this->getDataSQL($sqlstack);
		else 				$data = $this->getDataPDOSQL($pdo, $sqlstack);
		$pivot = $this->pivotdata_withColNamePrefix2020($data);
		
		if ($datastackopt == '')
			$datastackopt = ['type' => 'column',   'stackname' => 'Col1'];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $datastackopt);

		if ($pdo === '') 	$data = $this->getDataSQL($sqlline);
		else 				$data = $this->getDataPDOSQL($pdo, $sqlline);
		if ($datalineopt == '')
			$datalineopt = ['type' => 'line',  'zIndexAdd' => 2];      
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $datalineopt, true);

        // build high chart                
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate', false);
		
		return $this->script_lineChart($categories, $highchartseries, $opt);
	}
    
	function chartStackMultiLine ($sqlstack, $sqlline, $opt, $datastackopt='', $datalineopt='', $pdo='') {
		if ($pdo === '') 	$data = $this->getDataSQL($sqlstack);
		else 				$data = $this->getDataPDOSQL($pdo, $sqlstack);
		$pivot = $this->pivotdata_withColNamePrefix2020($data);
		
		if ($datastackopt == '')
			$datastackopt = ['type' => 'column',   'stackname' => 'Col1'];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $datastackopt);

		if ($pdo === '') 	$data = $this->getDataSQL($sqlline);
		else 				$data = $this->getDataPDOSQL($pdo, $sqlline);
		$pivot = $this->pivotdata_withColNamePrefix2020($data);
		
		if ($datalineopt == '')
			$datalineopt = ['type' => 'line',  'zIndexAdd' => 2];      
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $datalineopt);

        // build high chart                
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate', false);
		
		return $this->script_lineChart($categories, $highchartseries, $opt);
	}
	
	
	// ---------------------------- Chart (Series-Linked)  ------------------------
	/*
		$sql => [{ReportDate, GroupName, data1, data2, ...}]
	*/
	function chartLinked_MultiGroup ($sql, $opt, $dataopt=[], $pdo='') { // Pivot & linked >= 3th columns. 
		if($pdo === '')  	$pdo = $this->pdoReport;
		$alldata = $this->getDataPDOSQL($pdo, $sql);
		
		// pivot values
		$i = 1;
		foreach ($alldata[0] as $k=>$v) {
			$pivotcolname = $k;
			if ($i == 2) break;
			$i++;
		}
		$pivotvalues = $this->listunique_valueofcolumn($alldata, $pivotcolname);
		
		// series
		$highchartseries = array();
		foreach ($pivotvalues as $valuefilter) {
			// filter data
			$filterdata = array();
			$linkedGroup = array();
			foreach ($alldata as $row) {
				$startofrow = 1;
				$startofdata = 1;
				$newrow = [];
				if ($row[$pivotcolname] == $valuefilter) {
					foreach ($row as $k=>$v)
						if ($k != $pivotcolname)
							if ($startofrow == 1) { // not change first colname
								$newrow[$k] = $v;
								$startofrow = 0;
							}
							else { // add prefix
								$newrow[$valuefilter.'_'.$k] = $v;
								if (count($filterdata) == 0) // first row
									$linkedGroup[$valuefilter][] = $valuefilter.'_'.$k;
							}
					$filterdata[] = $newrow;
				}
			}
			
			$opt['type'] = 'column';
			$opt['stackname'] = 'Col'.$valuefilter;
			$opt['stack_col'] = true;
			$arr1 = $this->_create_ArrayFor_HighchartSeries($filterdata, $opt, true, $linkedGroup);
			$highchartseries = array_merge($highchartseries, $arr1);
		}
		
		// chart
		$categories = $this->listunique_valueofcolumn($alldata, 'ReportDate');
		return $this->script_lineChart($categories, $highchartseries, $opt);
	}
	
	
	// ---------------------------- Chart (2Y)  ------------------------
	function chart2Y_NotPivot_LineLine ($sqlY1, $sqlY2, $opt=[], $dataopt=[], $pdo='') {
		if($pdo === '')  	$pdo = $this->pdoReport;
		
		$data = $this->getDataSQL($sqlY1);
		if (!isset($dataopt[0]['type']))
			$dataopt[0]['type'] = 'line';
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $dataopt[0], true);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$data = $this->getDataSQL($sqlY2);
		if (!isset($dataopt[1]['type']))
			$dataopt[1]['type'] = 'line';
		$dataopt[1]['yAxis'] = 1;
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $dataopt[1], true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
		return $this->script_chart2Y($categories, $highchartseries, $opt);
	}

	function chart1stack1line_1p1np($sql1, $sql2, $opt, $pdo='', $align='auto'){
		if($pdo===''){
			$pdo = $this->pdoReport;
		}
		$data = $this->getDataPDOSQL($pdo, $sql1);   
		$pivot = $this->pivotdata($data);
		$options = ['type' => 'column',
					'stackname' => 'Col1'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$data = $this->getDataPDOSQL($pdo, $sql2);   
		$options = ['type' => 'line',
					'zIndexAdd' => 2,
					];      
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        // build high chart        
        if($align==='auto'){
            $align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
        }
        $opt['legend_align']=$align;
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate', false);
		
		return $this->script_lineChart($categories, $highchartseries, $opt);
	}

    function chart1stack1line_s1ln($sql1, $sql2, $opt){
        $data = $this->getDataSQL($sql1);   
        $pivot = $this->pivotdata($data);
        $options = ['type' => 'line',                    
                    ];          
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

        $data = $this->getDataSQL($sql2);   
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

    function chart1stack1line_1np1np($sql1, $sql2, $opt, $pdo='', $align='auto'){
		if($pdo===''){
			$pdo = $this->pdoReport;
		}
		$data = $this->getDataPDOSQL($pdo, $sql1);   		
		$options = ['type' => 'column',
					'stackname' => 'Col1'
					];
		$arr1 =  $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		$data = $this->getDataPDOSQL($pdo, $sql2);   
		$options = ['type' => 'line',
					'zIndexAdd' => 2,
					];      
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        // build high chart        
        if($align==='auto'){
            $align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
        }
        $opt['legend_align']=$align;
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate', false);
		
		return $this->script_lineChart($categories, $highchartseries, $opt);
	}

    function chart1LineOpt($sql, $options=[], $pdo=''){    
        if($pdo === ''){
            $pdo = $this->pdoReport;
        }    
        $listPredefineOpt = ['title','subtitle','yAxis_title','align', 'isPivot','showBig','isBig','isPivotPrefix'];
        $data = $this->getDataPDOSQL($pdo,$sql);    
        $title = isset($options['title'])?$options['title']:'';     
        $subtitle = isset($options['subtitle'])?$options['subtitle']:'';    
        $yAxis_title = isset($options['yAxis_title'])?$options['yAxis_title']:'';    
        $align = isset($options['align'])?$options['align']:'center';    
        $isPivot = isset($options['isPivot'])?$options['isPivot']:false;    
        $showBig = isset($options['showBig'])?$options['showBig']:0;    
        $isBig = isset($options['isBig'])?$options['align']:'center';   
        $pivotPrefix = isset($options['isPivotPrefix'])?$options['isPivotPrefix']:true;   
        $other = [];
        foreach($options as $key=>$value){
            if(!in_array($key, $listPredefineOpt)){
                $other[$key]=$value;
            }
        }
        return $this->chart1LineData($data, $title, $subtitle, $yAxis_title, $align,$isPivot, $showBig, $isBig, $pivotPrefix, $other);
    }

    function chart1Line($sql, $title, $subtitle, $yAxis_title, $align='center', $isPivot=false, $showBig=0, $isBig = true, $pivotPrefix=true){        
        $data = $this->getDataSQL($sql);           
        return $this->chart1LineData($data, $title, $subtitle, $yAxis_title, $align,$isPivot, $showBig, $isBig, $pivotPrefix);
    }

    function chart1LineInGame($sql, $title, $subtitle, $yAxis_title, $pdo='', $align='center', $isPivot=false, $showBig=0, $isBig = true, $pivotPrefix=true, $addopt=[]){        
        if($pdo === ''){
            if(($this->AppName === 'p2' || $this->AppName==='p13') && strpos($sql, 'report_client_notify') == false ){
                $pdo = $this->pdo54;
            }else{
                $pdo = $this->pdoIngame;
            }
        }
        $data = $this->getDataPDOSQL($pdo,$sql);           
        return $this->chart1LineData($data, $title, $subtitle, $yAxis_title, $align,$isPivot, $showBig, $isBig, $pivotPrefix,  $addopt);
    }

    /* Trả về chart line dùng data từ getDataSQL         
     * @param $isPivot có pivot hay không 
     * @param $showBig top x item visible
     * @param $isBig top nhỏ nhất hay lớn nhất
     */	
    function chart1LineData($data, $title, $subtitle, $yAxis_title, $align='center', $isPivot=false, $showBig=0, $isBig = true, $pivotPrefix=true, $addopt=[]){
        $options = ['type' => 'line',
                    ];             
             
        if($isPivot){            
            $rData = $this->pivotdata2020($data, $pivotPrefix);
        }else{
            $rData = $data;
        }       
        
        if($showBig > 0){
            $visible = $this->getBiggestFields($rData, $showBig, $isBig);
        }

        if($visible){
            $options['visible'] = $visible;            
        } 

        if($isPivot){
            $arr1 = $this->_create_ArrayFor_HighchartSeries($rData, $options);          
        }else{
            $arr1 = $this->_create_ArrayFor_HighchartSeries($rData, $options, true);
        }

        $highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');      
        if($align==='auto'){
            $align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
        }
        
        $opt = [    'title' => $title,
                    'subtitle' =>  $subtitle,
                    'yAxis_title' => $yAxis_title,
                    'legend_align' => $align
                    ];
        if(count($addopt)>0){
            $opt = array_merge($opt, $addopt);
        }
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }    

    function chart1LineOption($sql,$isPivot, $opt, $prefix=true){
        $data = $this->getDataSQL($sql);        
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

    function chart1StackInGame($sql, $title, $subtitle, $yAxis_title, $isPivot=false, $pdo=''){
        if($pdo === ''){
            if(($this->AppName === 'p2' || $this->AppName==='p13') && strpos($sql, 'report_client_notify') == false ){
                $pdo = $this->pdo54;
            }else{
                $pdo = $this->pdoIngame;
            }
        }
        return $this->chart1Stack($sql, $title, $subtitle, $yAxis_title, $isPivot, $pdo);
    }

    function chart1Stack($sql, $title, $subtitle, $yAxis_title, $isPivot=false, $pdo='', $isPrefix=true, $align='auto', $opt=[]){        
        if($pdo===''){
            $data = $this->getDataSQL($sql);                   
        }else{
            $data = $this->getDataPDOSQL($pdo, $sql);
        }
        $options = ['type' => 'column',
                    'stackname' => 'Col1'
                    ];      
        if($isPivot){
            if ($isPrefix){
                $pivot = $this->pivotdata_withColNamePrefix($data);
            } else {
                $pivot = $this->pivotdata($data);
            }            
            $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
        }else{
            $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
        }

        $highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

        if($align==='auto'){
            $align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
        }
        
        $options = [    'title' => $title,
                    'subtitle' =>  $subtitle,
                    'yAxis_title' => $yAxis_title,
                    'stack_col' => true,
                    'legend_align' => $align
                    ];
        $options = array_merge($options, $opt);                
        return $this->script_lineChart($categories, $highchartseries, $options);
    }    

    function chart1stack1line_withPrefix($sql1, $sql2, $opt, $pdo=''){
        if($pdo===''){
            $pdo = $this->pdoReport;
        }
        $data = $this->getDataPDOSQL($pdo, $sql1);
        $pivot = $this->pivotdata_withColNamePrefix($data);
        $options = ['type' => 'column',
                    'stackname' => 'Col1'
                    ];
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

        $data = $this->getDataPDOSQL($pdo, $sql2);
        $options = ['type' => 'line',
                    'zIndexAdd' => 2,
                    ];      
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        // build high chart        
        $highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate', false);
        
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }

    function chartMultiSqlLine($arrSql, $opt, $dataopt='', $isPivot = false, $pdo=''){
        if($pdo === ''){
            $pdo = $this->pdoReport;
        }
        $highchartseries = array();
        foreach($arrSql as $sql){
            $data = $this->getDataPDOSQL($pdo, $sql);
            if($isPivot){
                $pivot = $this->pivotdata_withColNamePrefix2020($data);
                $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $dataopt);
            }else{
                $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $dataopt, true);
            }
            if ($dataopt == '')
			    $dataopt = ['type' => 'line'];                        
            $highchartseries = array_merge($highchartseries, $arr1);   
        }             
        // dd($highchartseries);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		return $this->script_lineChart($categories, $highchartseries, $opt);
    }

    /* Trả về chart merge nhiều câu lệnh sql, default là chart stack
     * @param $arrSql List các câu lệnh sql sinh ra chart
     * @param $options Option truyền vào script_lineChart
     * @param $dataopt List các options của từng câu SQL, thứ tự match với thứ tự trong $arrSql, default = []
     * @param $isPivot List có pivot hay không với các câu SQL, true: pivot all, false: không pivot all, array: pivot cho từng câu SQL, default = false
     * @param $pdo pdo cho các câu lệnh, default = pdoReport
     * @param $linkedGroup Link các legend trong highchart với nhau, khi đó các legend sẽ không show hết mà chỉ show legend cho từng group, default = []
     */
	/* $listparam = check above description */
    function chartMultiSqlStack($arrSql, $options, $dataopt=[], $isPivot = false, $pdo='', $linkedGroup=[], $listparam=[]) {
        if($pdo === ''){
            $pdo = $this->pdoReport;
        }
        $highchartseries = array();
        $haveOpt = false;
        if(count($dataopt) === count($arrSql)){
            $haveOpt = true;
        }
        $tmpData = [];
        foreach($arrSql as $key=>$sql){
            $data = $this->getDataPDOSQL($pdo, $sql);
			if (isset($listparam['istr_colname']))
				$data = $this->istr_data($data, $listparam['istr_colname'], $listparam['istr_listvalue']);
            if(count($data) > count($tmpData))   {
                $tmpData = $data;
            }
            $opt = ['type' => 'column',
                    'stackname' => 'Col'.$key,
                    ]; 
            if($haveOpt){
                // $opt = $dataopt[$key];                
                foreach($dataopt[$key] as $optName=>$value){
                    $opt[$optName] = $value;                    
                }
            }            
            if(is_array($isPivot)){
                if(count($isPivot) === count($arrSql)){
                    foreach($isPivot as $item){
                        if($item){
                            $pivot = $this->pivotdata2020($data, true);
                            $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $opt, false, $linkedGroup);                
                        }else{
                            $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $opt, true, $linkedGroup);
                        }       
                    }
                }else{
                    //nếu $isPivot khác số phần tử với $arrSql thì không pivot
                    $pivot = $this->pivotdata2020($data, true);
                    $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $opt, false, $linkedGroup);                
                }
            }
            else if($isPivot){
                $pivot = $this->pivotdata2020($data, true);
                $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $opt, false, $linkedGroup);                
            }else{
                $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $opt, true, $linkedGroup);
            }                                
            $highchartseries = array_merge($highchartseries, $arr1);   
        }                             
		$categories = $this->listunique_valueofcolumn($tmpData, 'ReportDate');
		return $this->script_lineChart($categories, $highchartseries, $options);
    }

    function chart1stackPercent($sql, $title, $subtitle, $yAxis_title, $align='auto', $isPivot=false, $showBig=0, $isBig = true, $pivotPrefix=true, $pdo='', $addOpt=[]){
        if($pdo === ''){
            $pdo = $this->pdoReport;
        }
        $data = $this->getDataPDOSQL($pdo, $sql);
        
        $options = ['type' => 'column',
                    'stackname' => 'Col1',     
                    'stacking'=>'percent',                                    
                    ];

        if($isPivot){            
            $rData = $this->pivotdata2020($data, $pivotPrefix);
        }else{
            $rData = $data;
        }       
        
        if($showBig > 0){
            $visible = $this->getBiggestFields($rData, $showBig, $isBig);
        }

        if($visible){
            $options['visible'] = $visible;            
        } 

        if($isPivot){
            $arr1 = $this->_create_ArrayFor_HighchartSeries($rData, $options);          
        }else{
            $arr1 = $this->_create_ArrayFor_HighchartSeries($rData, $options, true);
        }

        $highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        
        if($align==='auto'){
            $align = count($arr1) > MAX_LEGEND_CENTER ? 'right' : 'center';
        }
        $opt = [    'title' => $title,
                        'subtitle' => $subtitle,
                        'yAxis_title' => $yAxis_title,
                        'legend_align' => $align,
                        'stack_col' => true,              
                        'tooltip'=>'pointFormat: \'<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>\',
                        ',                          
                    ];
        $opt = array_merge($opt, $addOpt);
        return $this->script_lineChart($categories, $highchartseries, $opt);
    }

    function chartBasic($sql, $opt, $pivot=false, $removeFirstCol=true){
        $data = $this->getDataSQL($sql);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');
        $options = ['type' => $opt['type']??'line',
        ];
        if ($pivot) $data = $this->pivotdata($data);
        $series = $this->_create_ArrayFor_HighchartSeries($data, $options, $removeFirstCol);
        return $this->script_lineChart($categories,$series, $opt);
    }
	
	// ----------------------------- grid ------------------------------
    function createGrid2020($sql, $option) {
        $table = '';
        $data = $this->getDataSQL($sql);
        $table .= $this->_createGridData_html_2020($data, $option);
		
        return $table;
    }	
	
    function createGrid($sql,$tid, $datatype=[], $pdo='', $align=[]) {
        if($pdo === ''){
            $data = $this->getDataSQL($sql);
        }else{
            $data = $this->getDataPDOSQL($pdo, $sql);
        }        
        return $this->createGridData($data,$tid, $datatype, $align);
    }

    function createGridData($data,$tid, $datatype=[], $align=[]) {
        $table = '';
        $option = ['tableid' => $tid,
                    'datatype' => $datatype,
                    'align' => $align
                    ];
        $table .= $this->_createGridData_html_2020($data, $option);

        return $table;
    }

    function createGridInGame($sql,$tid, $datatype=[], $pdo='') {        
        if($pdo === ''){            
            if(($this->AppName === 'p2' || $this->AppName==='p13') && strpos($sql, 'report_client_notify') == false ){
                $pdo = $this->pdo54;
            }else{
                $pdo = $this->pdoIngame;
            }
        }        
        $data = $this->getDataPDOSQL($pdo, $sql);
        return $this->createGridData($data,$tid, $datatype);
    }

    /**
     * function to generate css for show/hide group of columns in a grid
     * @param $listColHeader array contains list of column by order in grid
     * @param $groupToggle list of item, example: [{"name":"name show in toggle button", "column":["c1","c2","c3"]}]
     */
    function generateGroupToggle($listColHeader, $groupToggle){
        if(count($listColHeader) <= 0){
            return '';
        }
        $res = [];
        foreach($groupToggle as $key => $value){
            $tmp=[];
            $tmp['name'] = $value['name'];
            $tmp['column'] = [];
            foreach($value['column'] as $colName){
                for($i = 0; $i < count($listColHeader); $i++){            
                    if($listColHeader[$i] === $colName){
                        $tmp['column'][] = $i+1;
                        break;
                    }
                }
            }   
            $res[] = $tmp;
        }        
        return $res;
    }

    function convertColNameToIndex($listColHeader, $colConvert){
        $res = [];
        foreach($colConvert as $colName){
            for($i = 0; $i < count($listColHeader); $i++){            
                if($listColHeader[$i] === $colName){
                    $res[] = $i+1;
                    break;
                }
            }
        }
        return $res;
    }

    // ----------------------------- chart drilldown ------------------------------
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
        
        $combinations_data = $this->getDrillDownCombinationData($dataArray, $group_params, $value_key);

        if(self::$DEBUG_MODE){
            // dd($combinations_data);
        }
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
        $data = $this->getDrilldownDrawData($combinations_data, $group_params, $value_key, $option);

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
    function getDrilldownDrawData($combinations_data, $group_params, $value_key, $options){

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
                'visible' => $visible,
            );

            if ($count == 1){
                if ( isset($options['visible']) && !in_array($series_name, $options['visible']) 
                        || isset($options['invisible']) && in_array($series_name, $options['invisible']) )
                    $series_data['visible'] = false;
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
    // -----------------------------------------------------------------------------
 
}
