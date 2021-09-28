<?php

namespace App\Http\Controllers\Device;
use View;
use App\Http\Controllers\CommonFunction;

class DevicePlatform extends CommonFunction {
	
	public function index ($type='') {

		$this->listChart = array(
			'chartA1' => $this->chartA1(),
			'chartN1' => $this->chartN1(),
			'chartRev' => $this->chartRev(),
			'chartRR' => $this->chartRR(),
			'chartChurn' => $this->chartChurn(),
			'chartAvgSessTime' => $this->chartAvgSessTime(),
			'chartSessQty' => $this->chartSessQty(),
			'chartA30' => $this->chartA30(),
			'gridAll' => $this->gridAll(),
		);	
	
		// $this->listChartId=array_keys($this->listChart); 	
	
		// $this->addViewsToMain=json_encode(
		// 	$this->addChart('chartA1').
		// 	$this->addChart('chartN1').
		// 	$this->addChart('chartRev').
		// 	$this->addChart('lineRR').
		// 	$this->addChart('chartChurn').			
		// 	"<div>".
		// 		$this->addChart('chartAvgSessTime','two_chart chart').
		// 		$this->addChart('chartSessQty','two_chart chart', false).
		// 	"</div>".		
		// 	$this->addChart('chartA30').
		// 	$this->addGrid('', 'gridAll', $this->gridAll(), false).
		// 	''
		// );
	
		$this->pageTitle='Device / Platform';	
			
		return parent::__index($this->addTopViewApp($this->listApp, 'Group101'), $type);
	}
/*
- session / platform
*/
	
	function chartA1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, ActiveDevice from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'ActiveDevice'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ActiveDevice from device where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr3);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'ActiveDevice',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartA30 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, AD3 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, AD7 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, AD15 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, AD30 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr3, $arr4);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'ActiveDevice 3,7,15,30',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartN1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, NewDevice from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'NewDevice'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, NewDevice from device where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr3);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'NewDevice',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartRev () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, RevGross from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'area',
					'stackname' => 'RevGross'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, NewPayDevice from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'NewPayDevice',
					'yAxis' => 1,
					'invisible' => ['NewPayDevice_iOS','NewPayDevice_Android','NewPayDevice_WP']
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, PayDevice from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'yAxis' => 1,
					'stackname' => 'PayDevice',
					'invisible' => ['PayDevice_iOS','PayDevice_Android','PayDevice_WP']
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr3);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Revenue',
						'subtitle' => $this->AppName,
						'yAxis_title' => ['VND','#user'],
						'stack_area' => true,
						'stack_col' => true,
					];
		return $this->script_chart2Y($categories, $highchartseries, $options);
	}
	
	function chartRR () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(New_RR1*100,1) RR1 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(New_RR3*100,1) RR3 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dot',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(New_RR7*100,1) RR7 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--LongDash',
					'invisible' => ['RR7_iOS','RR7_Android','RR7_WP']
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(New_RR15*100,1) RR15 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--DashDotDot',
					'invisible' => ['RR15_iOS','RR15_Android','RR15_WP']
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(New_RR30*100,1) RR30 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dash',
					'invisible' => ['RR30_iOS','RR30_Android','RR30_WP']
					];
		$arr5 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr3, $arr4, $arr5);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Retention Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartChurn () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(Churn1*100,1) Churn1 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(Churn3*100,1) Churn3 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dot',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(Churn7*100,1) Churn7 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dash',
					'invisible' => ['Churn7_iOS','Churn7_Android','Churn7_WP']
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(Churn15*100,1) Churn15 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--DashDot',
					'invisible' => ['Churn15_iOS','Churn15_Android','Churn15_WP']
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, round(Churn30*100,1) Churn30 from device where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--DashDotDot',
					'invisible' => ['Churn30_iOS','Churn30_Android','Churn30_WP']
					];
		$arr5 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr3, $arr4, $arr5);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Churn Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartAvgSessTime () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, Session_AvgDuration from device where Platform not in ('WP','AllPlatform') and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Session Average Duration',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'second',
						'chart_height' => 400,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartSessQty () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, Session_TotalQty from device where Platform not in ('WP','AllPlatform') and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Total Session',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'chart_height' => 400,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridAll() {
        $sql = "select ReportDate, Platform, ActiveDevice, NewDevice, PayDevice, NewPayDevice, RevGross, New_RR1, New_RR3, New_RR7, New_RR15, New_RR30, New_CV0, New_CV1, New_CV3, New_CV7, New_CV15, New_CV30, New_PU30, ".
			" New_Rev0Gross, New_Rev1Gross, New_Rev3Gross, New_Rev7Gross, New_Rev15Gross, New_Rev30Gross, Session_AvgDuration, Session_AvgQty, Session_TotalQty, ".
			" AD3, AD7, AD15, AD30, Churn1, Churn3, Churn7, Churn15, Churn30 ".
				" from device where AppName=".$this->quote($this->AppName)." and Platform <> 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc, Platform";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'datatype' => ['AD3'=>'DEC0', 'AD7'=>'DEC0', 'AD15'=>'DEC0', 'AD30'=>'DEC0',
								'New_Rev7Gross'=>'DEC0', 'New_Rev15Gross'=>'DEC0', 'New_Rev30Gross'=>'DEC0',
								'Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Churn15'=>'PERCENT', 'Churn30'=>'PERCENT', 'Session_AvgQty'=>'DEC2',
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT', 
								'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT',],
					];
        return $this->_createGridData_html($data, $option);
	}
}
