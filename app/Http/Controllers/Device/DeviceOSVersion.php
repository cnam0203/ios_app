<?php

namespace App\Http\Controllers\Device;
use View;
use App\Http\Controllers\CommonFunction;

class DeviceOSVersion extends CommonFunction {
	
	function __construct() {
		parent::__construct();
		$this->date = $this->getInputDate(true);
	}
	
	public function index ($type='') {

		// TotalQuantity
		$sql = "select sum(Quantity) Quantity from OSVersion where AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate)."";
		$rs = $this->getDataSQL($sql);
		$this->TotalQuantity = $rs[0]['Quantity'];

		$this->listChart = array(
			'pieOSVersion_Android' => $this->chartPieOSVersion('Android'),
			'pieOSVersion_iOS' => $this->chartPieOSVersion('iOS'),
			'lineOSVersion' => $this->chartLineOSVersion(),
		);	
	
		$this->listChartId=array_keys($this->listChart); 	
	
		$this->addViewsToMain=json_encode(	
			"<div>".
				$this->addChart('pieOSVersion_Android','two_chart chart').
				$this->addChart('pieOSVersion_iOS','two_chart chart', false).
			"</div>".		
			$this->addChart('lineOSVersion').
			$this->addGrid('', 'gridOSVersion', $this->gridOSVersion(), false).
			''
		);
	
		$this->pageTitle='OS Version';	
			
		return parent::__index($this->addTopViewApp($this->listApp, 'Group101'), $type);		
	}
	
	// ---------------- -------------------- SQL -----------------------
	// ---------------- -------------------- END SQL -----------------------
	
	function chartPieOSVersion($Platform) {
		// top
		$sql = "select RecordID, OSVersion, Quantity from OSVersion where Platform='".$Platform."' and AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate)." order by Quantity desc limit 15";
		$rs = $this->getDataSQL($sql);
		$chartdata = array();
		$ids = array();
		foreach ($rs as $v) {
			$ids[] = $v['RecordID'];
			$chartdata[] = array("name"=>$v['OSVersion'], "y"=>round($v['Quantity'],1));
		}
		
		// others = not in top
		$sql = "select sum(Quantity) Quantity from OSVersion where Platform='".$Platform."' and AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate)." and RecordID not in (".implode(count($ids)==0?['0']:$ids,',').")";
		$rs = $this->getDataSQL($sql);
		$chartdata[] = array("name"=>'others', "y"=>round($rs[0]['Quantity'],1));
		
		$options = [	'title' => $Platform. ' Version in '.$this->toDate,
						'subtitle' => $this->AppName,
					];
		return $this->script_pieChart($chartdata, $options);
	}
	
	function chartLineOSVersion() {
		// top 20
		$sql = "select distinct concat(Platform,' ',OSVersion) OSVersion from OSVersion where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." group by Platform, OSVersion order by sum(Quantity) desc limit 10";
		$rs = $this->getDataSQL($sql);
		$modelnames = array();
		foreach ($rs as $v) {
			$modelnames[] = $v['OSVersion'];
		}
		
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, concat(Platform,' ',OSVersion) OSVersion, Quantity from OSVersion where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and concat(Platform,' ',OSVersion) in ('".implode(count($modelnames)==0?['']:$modelnames,"','")."') order by ReportDate, OSVersion desc";
		$rs1 = $this->getDataSQL($sql);
		$pivot4chart1 = $this->pivotdata($rs1);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot4chart1, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($rs1, 'ReportDate');
		
		$options = [	'title' => 'TOP OS Version',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#device',
						'chart_height' => 500,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridOSVersion() {
		$table = '';
		
        $sql = "select ReportDate, Platform, OSVersion, Quantity from OSVersion where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc, Quantity desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridOSVersion',
					'align' => ['OSVersion'=>'left'],
					'datatype' => ['OSVersion'=>'String'],
					];
        $table .= $this->_createGridData_html($data, $option);
		
		return $table;
	}
}
