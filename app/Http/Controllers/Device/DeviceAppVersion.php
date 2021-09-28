<?php
namespace App\Http\Controllers\Device;
use View;
use App\Http\Controllers\CommonFunction;

class DeviceAppVersion extends CommonFunction {
	
	function __construct() {
		parent::__construct();
		$this->date = $this->getInputDate(true);
	}
	
	public function index ($type='') {

		// TotalQuantity
		$sql = "select sum(Quantity) Quantity from AppVersion where AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate)."";
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
	
		$this->pageTitle='App Version';	
			
		return parent::__index($this->addTopViewApp($this->listApp, 'Group101'), $type);		
	}
	
	// ---------------- -------------------- SQL -----------------------
	// ---------------- -------------------- END SQL -----------------------
	
	function chartPieOSVersion($Platform) {
		// top
		$sql = "select RecordID, AppVersion, Quantity from AppVersion where Platform='".$Platform."' and AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate)." order by Quantity desc limit 15";
		$rs = $this->getDataSQL($sql);
		$chartdata = array();
		$ids = array();
		foreach ($rs as $v) {
			$ids[] = $v['RecordID'];
			$chartdata[] = array("name"=>$v['AppVersion'], "y"=>round($v['Quantity'],1));
		}
		
		// others = not in top
		$sql = "select sum(Quantity) Quantity from AppVersion where Platform='".$Platform."' and AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate)." and RecordID not in (".implode(count($ids)==0?['0']:$ids,',').")";
		$rs = $this->getDataSQL($sql);
		$chartdata[] = array("name"=>'others', "y"=>round($rs[0]['Quantity'],1));
		
		$options = [	'title' => ' AppVersion in '.$this->toDate.' ('.$Platform.')',
						'subtitle' => $this->AppName,
					];
		return $this->script_pieChart($chartdata, $options);
	}
	
	function chartLineOSVersion() {
		// top 20
		$sql = "select distinct concat(Platform,' ',AppVersion) AppVersion from AppVersion where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." group by Platform, AppVersion order by sum(Quantity) desc limit 10";
		$rs = $this->getDataSQL($sql);
		$modelnames = array();
		foreach ($rs as $v) {
			$modelnames[] = $v['AppVersion'];
		}
		
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, concat(Platform,' ',AppVersion) AppVersion, Quantity from AppVersion where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and concat(Platform,' ',AppVersion) in ('".implode(count($modelnames)==0?['']:$modelnames,"','")."') order by ReportDate, AppVersion desc";
		$rs1 = $this->getDataSQL($sql);
		$pivot4chart1 = $this->pivotdata($rs1);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot4chart1, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($rs1, 'ReportDate');
		
		$options = [	'title' => 'TOP App Version',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#device',
						'chart_height' => 500,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridOSVersion() {
		$table = '';
		
        $sql = "select ReportDate, Platform, AppVersion, Quantity from AppVersion where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc, Quantity desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridOSVersion',
					'align' => ['AppVersion'=>'left'],
					'datatype' => ['AppVersion'=>'String'],
					];
        $table .= $this->_createGridData_html($data, $option);
		
		return $table;
	}
}
