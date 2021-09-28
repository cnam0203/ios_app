<?php
namespace App\Http\Controllers\Device;
use View;
use App\Http\Controllers\CommonFunction;

class DeviceActiveAndNew extends CommonFunction {
	
	public function index ($type='') {
		$this->listChart = array(
			'lineRR' => $this->chartLineRR(),
			'lineCV' => $this->chartCV(),
			'lineChurn' => $this->chartLineChurn(),
			'lineAvgSessTime' => $this->chartLineAvgSessTime(),
			'colSessQty' => $this->chartColSessQty(),
			'lineA30' => $this->chartLineA30(),
			'chartActiveDevice'=>$this->chartActiveDevice(),
			'chartNewDevice'=>$this->chartNewDevice(),
			'chartRev'=>$this->chartRev(),
			'gridAll'=> $this->gridAll(),
		);	
	
		// $this->listChartId=array_keys($this->listChart); 	
	
		// $this->addViewsToMain=json_encode(
		// 	$this->addChart('chartActiveDevice').
		// 	$this->addChart('chartNewDevice').
		// 	$this->addChart('chartRev').
		// 	$this->addChart('lineRR').
		// 	$this->addChart('lineCV').
		// 	$this->addChart('lineChurn').
		// 	"<div>".
		// 		$this->addChart('lineAvgSessTime','two_chart chart').
		// 		$this->addChart('colSessQty','two_chart chart', false).
		// 	"</div>".		
		// 	$this->addChart('lineA30').
		// 	$this->addGrid('', 'gridAll', $this->gridAll(), false).
		// 	''
		// );
	
		$this->pageTitle='Active & New Device';	
		// $this->pageInfo=
		// "<p>
		// 	<br>Diễn giải:
		// 	<br> - NewDevice : Số device mới theo log LOGIN
		// 	<br> - Session : session kết thúc khi client không có log nào trong 5 phút
		// </p><br><br>";
			
		return parent::__index($this->addTopViewApp($this->listApp, 'Group101'), $type);
	}


	function syncChart() {
        $sql = "select ReportDate, ActiveDevice, NewDevice, RevGross, PayDevice, NewPayDevice from device where Platform='AllPlatform' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";       	
		$data = $this->getDataSQL($sql);
		
		// return
		$ret['pointStart'] = '{"Y":'.substr($data[0]['ReportDate'],0,4).',"M":'.(substr($data[0]['ReportDate'],5,2)+0).',"D":'.(substr($data[0]['ReportDate'],8,2)+0).'}';
		$ret['datasets'] = '[{"name": "Active Device", "type": "line", "data": ['.
			$this->implode_chartdata($this->listValueFromArray($data,'ActiveDevice')).'],   "unit": "","valueDecimals": 0}, {"name": "New Device", "type": "line", "data": ['.
			$this->implode_chartdata($this->listValueFromArray($data,'NewDevice')).'],    "unit": "", "valueDecimals": 0}, {"name": "Revenue", "type": "area", "data": ['.
			$this->implode_chartdata($this->listValueFromArray($data,'RevGross')).'],    "unit": "","valueDecimals": 0}, {"name": "Pay Device", "type": "line", "data": ['.
			$this->implode_chartdata($this->listValueFromArray($data,'PayDevice')).'],    "unit": "","valueDecimals": 0}, {"name": "New Pay Device", "type": "line", "data": ['.
			$this->implode_chartdata($this->listValueFromArray($data,'NewPayDevice')).'],    "unit": "","valueDecimals": 0}]';
			
		// dd($ret);
		return $ret;
	}

	function chartActiveDevice () {
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
		
		$options = [	'title' => 'Active Device',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartNewDevice () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, NewDevice from device where AppName=".$this->quote($this->AppName)." and Platform  not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'N1'
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
		
		$options = [	'title' => 'New Device',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartRev () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, RevGross from device where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'area',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, NewPayDevice, PayDevice from device where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					'yAxis' => 1,
					'invisible' => ['PayDevice']
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Revenue',
						'subtitle' => $this->AppName,
						'yAxis_title' => ['VND','#user'],
						'stack_area' => true,
					];
		return $this->script_chart2Y($categories, $highchartseries, $options);
	}
	
	function chartLineRR () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_RR1*100,1) RR1, round(New_RR3*100,1) RR3, round(New_RR7*100,1) RR7, round(New_RR15*100,1) RR15, round(New_RR30*100,1) RR30 from device where Platform='AllPlatform' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true); //true = remove first col
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Retention Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'chart_height' => 400,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartCV () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_CV0*100,1) CV0, round(New_CV1*100,1) CV1, round(New_CV3*100,1) CV3, round(New_CV7*100,1) CV7, round(New_CV15*100,1) CV15, round(New_CV30*100,1) CV30 from device where AppName=".$this->quote($this->AppName)." and Platform='AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Conversion Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'chart_height' => 400,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartLineChurn () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(Churn1*100,1) Churn1, round(Churn3*100,1) Churn3, round(Churn7*100,1) Churn7, round(Churn15*100,1) Churn15, round(Churn30*100,1) Churn30 from device where Platform='AllPlatform' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true); //true = remove first col
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Churn Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'chart_height' => 400,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartLineA30 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AD3, AD7, AD15, AD30 from device where Platform='AllPlatform' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true); //true = remove first col
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Active Device 3,7,15,30',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'chart_height' => 400,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartLineAvgSessTime () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Session_AvgDuration from device where Platform='AllPlatform' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true); //true = remove first col
		
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
	
	function chartColSessQty () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Session_TotalQty from device where Platform='AllPlatform' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'column',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true); //true = remove first col
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(Session_TotalQty/ActiveDevice,1) AvgSessionPerUser from device where Platform='AllPlatform' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					'yAxis' => 1,
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true); //true = remove first col
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Total Session',
						'subtitle' => $this->AppName,
						'yAxis_title' => ['#','#'],
						'chart_height' => 400,
					];
		return $this->script_chart2Y($categories, $highchartseries, $options);
	}
	
	function gridAll() {
        $sql = "select ReportDate, ActiveDevice, NewDevice, PayDevice, NewPayDevice, RevGross, New_RR1, New_RR3, New_RR7, New_RR15, New_RR30, New_CV0, New_CV1, New_CV3, New_CV7, New_CV15, New_CV30, New_PU30, ".
			" New_Rev0Gross, New_Rev1Gross, New_Rev3Gross, New_Rev7Gross, New_Rev15Gross, New_Rev30Gross, Session_AvgDuration, Session_AvgQty, Session_TotalQty, ".
			" AD3, AD7, AD15, AD30, Churn1, Churn3, Churn7, Churn15, Churn30 ".
			" from device where Platform='AllPlatform' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'datatype' => ['AD3'=>'DEC0', 'AD7'=>'DEC0', 'AD15'=>'DEC0', 'AD30'=>'DEC0',
								'New_Rev15Gross'=>'DEC0', 'New_Rev30Gross'=>'DEC0',
								'Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Churn15'=>'PERCENT', 'Churn30'=>'PERCENT', 'Session_AvgQty'=>'DEC2',
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT', 
								'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT',],
					];
        return $this->_createGridData_html($data, $option);
	}
}
