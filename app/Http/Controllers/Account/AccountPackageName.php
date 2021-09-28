<?php
namespace App\Http\Controllers\Account;
use View;
use App\Http\Controllers\CommonFunction;

class AccountPackageName extends CommonFunction {
	
	public function __construct() {
		$this->sqlAppPermission = "select Value AppName from dailyextra where ReportDate>= curdate() - interval 60 day and Type='PackageName' {{WHERE}} group by Value having sum(QuantityAcc) > 100";
		parent::__construct('', 'Value'); // check permission
	}
	
	public function index ($type='') {

		$this->listChart = array(
			'linePackageName' => $this->chartLinePackageName(),
		);				
		$this->listChartId=['linePackageName']; 

		$this->addViewsToMain=json_encode(
			$this->addChart('linePackageName').
			$this->addGrid('', 'gridPackageName', $this->gridPackageName(), false).
			''
		);
		$this->field1Title='Package Name';

		$this->pageTitle='App / Package Name';	

		return parent::__index($this->listApp);
	}
	
	
	function chartLinePackageName() {
		// top 20
		$sql = "select AppName, sum(QuantityAcc) sumQuantityAcc from dailyextra where Type='PackageName' and Value=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." group by AppName order by sumQuantityAcc desc limit 10";  // AppName not like 'ZPPortal%' and  
		$rs = $this->getDataSQL($sql);
		$modelnames = array();
		foreach ($rs as $v) {
			$modelnames[] = $v['AppName'];
		}
		
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AppName, QuantityAcc from dailyextra where Type='PackageName' and Value=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName in ('".implode(count($modelnames)==0?['']:$modelnames,"','")."') order by ReportDate, AppName desc";		
		$rs1 = $this->getDataSQL($sql);
		$pivot4chart1 = $this->pivotdata($rs1);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot4chart1, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($rs1, 'ReportDate');
		
		$options = [	'title' => 'TOP App / Package Name',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#device',
						'chart_height' => 500,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridPackageName() {
		$table = '';
		
		$sql = "select ReportDate, Platform, AppName, QuantityAcc from dailyextra where Type='PackageName' and Value=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc, QuantityAcc desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridPackageName',
					'align' => ['PackageName'=>'left'],
					];
		$table .= $this->_createGridData_html($data, $option);
		
		return $table;
	}
}
