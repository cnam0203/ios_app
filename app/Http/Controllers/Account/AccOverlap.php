<?php
namespace App\Http\Controllers\Account;
use View,PDO;
use App\Http\Controllers\CommonFunction;

class AccOverlap extends CommonFunction {

	public function __construct() {
		$fromDate = date('Y-m-d',time() - 60 * 60 * 24 * 15);		
        $toDate = date('Y-m-d',time());
		$this->sqlAppPermission = "select distinct FromApp AppName from tracker_report.daily_overlap where ReportDate between '".$fromDate."' and '".$toDate."' {{WHERE}} order by FromApp";
		parent::__construct('','FromApp'); // check permission		
	}
	
	public function index ($type='') {
		$this->devicefilterApp = $this->filterAppHasValue();
		$this->date = $this->getInputDate(true);
		$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		$this->listChart = array(
			'chartPercentAccDay' => $this->chartPercentAccDay(),
			'chartPercentAccPrev30' => $this->chartPercentAccPrev30(),
			'chartPercentNewDay' => $this->chartPercentNewDay(),
			'chartPercentNewPrev30' => $this->chartPercentNewPrev30(),
			'chartPercentDeviceDay' => $this->chartPercentDeviceDay(),
			'chartPercentDevicePrev30' => $this->chartPercentDevicePrev30(),
			'chartPercentNewDeviceDay' => $this->chartPercentNewDeviceDay(),
			'chartPercentNewDevicePrev30' => $this->chartPercentNewDevicePrev30(),
			'chartPercentDeviceBelowA8Day' => $this->chartPercentDeviceBelowA8Day(),
			'chartPercentDeviceBelowA8Prev30' => $this->chartPercentDeviceBelowA8Prev30(),					
			'chartPercentNewDeviceBelowA8Day' => $this->chartPercentNewDeviceBelowA8Day(),
			'chartPercentNewDeviceBelowA8Prev30' => $this->chartPercentNewDeviceBelowA8Prev30(),
		);				
		$this->listChartId=array_keys($this->listChart); 

		$this->addViewsToMain=json_encode(
			$this->addChart('chartPercentAccDay').
			$this->addChart('chartPercentAccPrev30').
			$this->addChart('chartPercentNewDay').
			$this->addChart('chartPercentNewPrev30').
			$this->addChart('chartPercentDeviceDay').
			$this->addChart('chartPercentDevicePrev30').
			$this->addChart('chartPercentNewDeviceDay').
			$this->addChart('chartPercentNewDevicePrev30').
			$this->addChart('chartPercentDeviceBelowA8Day').
			$this->addChart('chartPercentDeviceBelowA8Prev30').
			$this->addChart('chartPercentNewDeviceBelowA8Day').
			$this->addChart('chartPercentNewDeviceBelowA8Prev30').
			$this->addGrid('', 'gridAll', $this->gridAll(), false).
			''
		);

		$this->pageInfo=
		'<p>
		<br> Diễn giải:
		<br>  - Account phải cùng hệ thống mới tính overlap được
		<br>  - Chart overlap theo device tự ẩn khi không có data
		<br>  - "this day": so sánh trong cùng ngày
		<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; + "% Acc overlap this day": A1(FromApp,25/5/2019) so sánh với A1(ToApp,25/5/2019)
		<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; + "% New Acc overlap this day": N1(FromApp,25/5/2019) so sánh với A1(ToApp,25/5/2019)
		<br>  - "previous 30 day": so sánh với 30 ngày trước đó
		<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; + "% Acc overlap previous 30 day": A1(FromApp,25/5/2019) so sánh với A30(ToApp,<font color=red><strong>24</strong></font>/5/2019)
		<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; + "% New Acc overlap previous 30 day": N1(FromApp,25/5/2019) so sánh với A30(ToApp,<font color=red><strong>24</strong></font>/5/2019)
		
		</p>';
		$this->pageTitle='Overlap Report';	
		
		return parent::__index($listApp, $type);
	}
	
	function filterAppHasValue () {
		$sql = "select distinct ToApp from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and OverlapDev_ThisDay > 0";
		$data = $this->getDataSQL($sql);
		$arr = array();
		foreach ($data as $row)
			$arr[] = $row['ToApp'];
		$str = "'". implode("','", $arr) ."'";
		return $str;
	}

	function checkValueExist($data, $fieldCheck){
		$checkExists = false;
		foreach ($data as $value) {
			if($value[$fieldCheck] != NULL){
				$checkExists = true;
				break;
			} 
		}
		return $checkExists;
	}

	function chartPercentAccDay(){
		$sql = "select ToApp Title, round(OverlapAcc_ThisDay/FromA1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp');
		$visible = $this->getTitleByValue($sql, 5);
		// dd($visible);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapAcc_ThisDay/FromA1 * 100, 1) as AccPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." order by ReportDate";								
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata($data);
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% Acc Overlap this day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentAccPrev30(){
		$sql = "select ToApp Title, round(OverlapAcc_30PrevDay/FromA1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp');
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapAcc_30PrevDay/FromA1 * 100, 1) as AccPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." order by ReportDate";				
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata($data);
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% Acc Overlap previous 30 day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentNewDay(){
		$sql = "select ToApp Title, round(OverlapN1_ThisDay/FromN1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp');
		$visible = $this->getTitleByValue($sql, 5);
		// dd($visible);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapN1_ThisDay/FromN1 * 100, 1) as N1PercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." order by ReportDate";								
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata($data);		
		if(!$this->checkValueExist($data, 'N1PercentFrom')) return "";

		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% New Acc Overlap this day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
						'chart_backgroundColor' => $this->darkMode?'#665853':'#fff5f1',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentNewPrev30(){
		$sql = "select ToApp Title, round(OverlapN1_30PrevDay/FromA1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp');
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapN1_30PrevDay/FromN1 * 100, 1) as N1PercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." order by ReportDate";				
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata($data);
		if(!$this->checkValueExist($data, 'N1PercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% New Acc Overlap previous 30 day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
						'chart_backgroundColor' => $this->darkMode?'#665853':'#fff5f1',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentDeviceDay(){
		$sql = "select ToApp Title, round(OverlapDev_ThisDay/FromAD1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp'). " and ToApp in (".$this->devicefilterApp.")";
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapDev_ThisDay/FromAD1 * 100, 1) as ADPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." and ToApp in (".$this->devicefilterApp.") order by ReportDate";			
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata($data);		
		if(!$this->checkValueExist($data, 'ADPercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% Device Overlap this day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
						'chart_backgroundColor'=>'#feffef',						
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentDevicePrev30(){
		$sql = "select ToApp Title, round(OverlapDev_30PrevDay/FromAD1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp'). " and ToApp in (".$this->devicefilterApp.")";
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapDev_30PrevDay/FromAD1 * 100, 1) as ADPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." and ToApp in (".$this->devicefilterApp.") order by ReportDate";			
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata($data);		
		if(!$this->checkValueExist($data, 'ADPercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% Device Overlap previous 30 day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
						'chart_backgroundColor'=>'#feffef',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentNewDeviceDay(){
		$sql = "select ToApp Title, round(OverlapND1_ThisDay/FromND1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp'). " and ToApp in (".$this->devicefilterApp.")";
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapND1_ThisDay/FromND1 * 100, 1) as NDPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." and ToApp in (".$this->devicefilterApp.") order by ReportDate";			
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata($data);		
		if(!$this->checkValueExist($data, 'NDPercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% NewDevice Overlap this day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentNewDevicePrev30(){
		$sql = "select ToApp Title, round(OverlapND1_30PrevDay/FromND1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp'). " and ToApp in (".$this->devicefilterApp.")";
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapND1_30PrevDay/FromND1 * 100, 1) as ADPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." and ToApp in (".$this->devicefilterApp.") order by ReportDate";			
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata($data);		
		if(!$this->checkValueExist($data, 'ADPercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% NewDevice Overlap previous 30 day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentDeviceBelowA8Day(){
		$sql = "select ToApp Title, round(OverlapDev_A8ThisDay/FromAD1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp'). " and ToApp in (".$this->devicefilterApp.")";
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapDev_A8ThisDay/FromAD1 * 100, 1) as ADPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." and ToApp in (".$this->devicefilterApp.") order by ReportDate";			
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata($data);		
		if(!$this->checkValueExist($data, 'ADPercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% Device < android 8 Overlap this day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
						'chart_backgroundColor'=>'#f0ffe8'
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentDeviceBelowA8Prev30(){
		$sql = "select ToApp Title, round(OverlapDev_A830PrevDay/FromAD1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp'). " and ToApp in (".$this->devicefilterApp.")";
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapDev_A830PrevDay/FromAD1 * 100, 1) as ADPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." and ToApp in (".$this->devicefilterApp.") order by ReportDate";			
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata($data);		
		$checkExists = false;
		if(!$this->checkValueExist($data, 'ADPercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% Device < android 8 Overlap previous 30 day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
						'chart_backgroundColor'=>'#f0ffe8'
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentNewDeviceBelowA8Day(){
		$sql = "select ToApp Title, round(OverlapND1_A8ThisDay/FromND1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp'). " and ToApp in (".$this->devicefilterApp.")";
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapND1_A8ThisDay/FromND1 * 100, 1) as ADPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." and ToApp in (".$this->devicefilterApp.") order by ReportDate";			
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata($data);		
		if(!$this->checkValueExist($data, 'ADPercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% NewDevice < android 8 Overlap this day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
						'chart_backgroundColor'=>'#effbfa'
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPercentNewDeviceBelowA8Prev30(){
		$sql = "select ToApp Title, round(OverlapND1_A830PrevDay/FromND1 * 100, 1) as Value from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp'). " and ToApp in (".$this->devicefilterApp.")";
		$visible = $this->getTitleByValue($sql, 5);
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ToApp, round(OverlapND1_A830PrevDay/FromND1 * 100, 1) as ADPercentFrom from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." and ToApp in (".$this->devicefilterApp.") order by ReportDate";			
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata($data);		
		if(!$this->checkValueExist($data, 'ADPercentFrom')) return "";
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => '% NewDevice < android 8 Overlap previous 30 day',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
						'chart_backgroundColor'=>'#effbfa'
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridAll() {
		$table = '';
		$this->listToAllowApps();
        $sql = "select ReportDate, ToApp, FromA1, FromAD1, FromN1, FromND1, ToA1, ToAD1, ToN1,ToND1,OverlapAcc_ThisDay, OverlapAcc_30PrevDay,OverlapN1_ThisDay,OverlapN1_30PrevDay,OverlapDev_ThisDay, OverlapDev_30PrevDay, OverlapND1_ThisDay, OverlapND1_30PrevDay, OverlapDev_A8ThisDay, OverlapDev_A830PrevDay,OverlapND1_A8ThisDay,OverlapND1_A830PrevDay".
				" from tracker_report.daily_overlap where FromApp=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$this->whereFilterAllowApps('ToApp')." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					// 'datatype' => ['Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Session_AvgQty'=>'DEC2',
					// 			'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT',
					// 			'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT', ],
					];
        $table .= $this->_createGridData_html($data, $option);
		
		return $table;
	}

	function listToAllowApps () {
		// change urlid if AUTO
		$urlidchecking = 234;
		
		// list by gamecode
		$sql = "select sg.GameCode
				from subset_group sg 
					join userright ur on sg.GroupID=ur.GroupId and sg.userid=ur.userid
					join groups g on sg.GroupID=g.GroupId
				where sg.userid='".$this->username."' and g.UriId='".$urlidchecking."'
			union
			select `Value` from subset where UrlId='".$urlidchecking."' AND userid='".$this->username."'
			union
			select sg.GameCode
				from subset_group sg 
					join groups g on sg.GroupID=g.GroupId
					join userright ur on g.UriId=ur.UriId and sg.userid=ur.userid
				where sg.userid='".$this->username."' and g.UriId='".$urlidchecking."' and sg.GameCode is not null";
		$stmt = $this->pdoAuthen->query($sql);
		
		$this->AllowToApps = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			$this->AllowToApps[] = $row['GameCode'];
		
		// list by group leader
		$sql = "select sg.GroupLeader
				from subset_group sg 
					join userright ur on sg.GroupID=ur.GroupId and sg.userid=ur.userid
					join groups g on sg.GroupID=g.GroupId
				where sg.userid='".$this->username."' and g.UriId='".$urlidchecking."' and sg.GroupLeader is not null
				union
				select sg.GroupLeader
					from subset_group sg 
						join groups g on sg.GroupID=g.GroupId
						join userright ur on g.UriId=ur.UriId and sg.userid=ur.userid
					where sg.userid='".$this->username."' and g.UriId='".$urlidchecking."' and sg.GroupLeader is not null";
		$stmt = $this->pdoAuthen->query($sql);
		$arr = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			$arr[] = $row['GroupLeader'];
		if (count($arr) > 0) {
			if ($this->appnametype == 'Product')
				$sql = "select distinct Product AppName from GSNConfig.Product where TeamLeader in ('".implode("','",$arr)."')";
			elseif ($this->appnametype == 'Cash')
				$sql = "select distinct CashTable AppName from GSNConfig.AppName where TeamLeader in ('".implode("','",$arr)."')";
			else
				$sql = "select distinct ToAppName AppName from GSNConfig.AppName where TeamLeader in ('".implode("','",$arr)."')";
			$stmt = $this->pdoReport->query($sql);
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				$this->AllowToApps[] = $row['AppName'];
		}
		
		// dd($this->AllowToApps);
	}
}
