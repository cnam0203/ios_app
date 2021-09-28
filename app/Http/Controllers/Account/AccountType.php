<?php
namespace App\Http\Controllers\Account;
use View;
use App\Http\Controllers\CommonFunction;

class AccountType extends CommonFunction {
	
	public function index ($type='') {

		$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		$this->listChart = array(
			'chartA1' => $this->chartA1(),
			'chartN1' => $this->chartN1(),
			'chartRev' => $this->chartRev(),
			'chartPU' => $this->chartPU(),
			'chartRR' => $this->chartRR(),
			'chartCV' => $this->chartCV(),
			'chartChurn' => $this->chartChurn(),
			'chartPUChurn' => $this->chartPUChurn(),
			'chartA30' => $this->chartA30(),
		);				
		// $this->listChartId=['chartA1','chartN1','chartRev','chartPU','chartRR','chartCV','chartChurn',
		// 		'chartPUChurn','chartA30']; 

		// $this->addViewsToMain=json_encode(
		// 	$this->addChart('chartA1').
		// 	$this->addChart('chartN1').
		// 	$this->addChart('chartRev').
		// 	$this->addChart('chartPU').
		// 	$this->addChart('chartRR').
		// 	$this->addChart('chartCV').
		// 	$this->addChart('chartChurn').
		// 	$this->addChart('chartPUChurn').			
		// 	$this->addChart('chartA30').
		// 	$this->addGrid('', 'gridAll', $this->gridAll(), false).
		// 	''
		// );

		$this->pageTitle='Account / AccountType';	
		
		return parent::__index($listApp, $type);

	}
	
	function chartA1 () {        
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, sum(A1) as A1 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A1 / AccountType',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartN1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, sum(N1) as N1 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'N1 / Account Type',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartRev () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, sum(RevGross) as Rev from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";

		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Revenue / Account Type',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'VNĐ',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPU () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, sum(P1) as PU from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";

		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Pay User / Account Type',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartRR () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(New_RR1)*100, 1) as RR1 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(New_RR3)*100, 1) as RR3 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dot',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(New_RR7)*100, 1) as RR7 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dash',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr3);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Retension Rate / Account Type',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartCV () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(New_CV1)*100, 1) as CV1 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(New_CV3)*100, 1) as CV3 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dot',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(New_CV7)*100, 1) as CV7 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dash',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(New_CV15)*100, 1) as CV15 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--DashDotDot',
					'invisible' => ['CV15_facebook','CV15_zalo','CV15_google', 'CV15_zingme'],
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(New_CV30)*100, 1) as CV30 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--LongDash',
					'invisible' => ['CV30_facebook','CV30_zalo','CV30_google', 'CV30_zingme'],
					];
		$arr5 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr3, $arr4, $arr5);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Conversion Rate / Account Type',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartChurn() {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(Churn3)*100, 1) as Churn3 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(Churn7)*100, 1) as Churn7 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dot',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(Churn15)*100, 1) as Churn15 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dash',
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(Churn30)*100, 1) as Churn30 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--DashDot',
					];
		$arr5 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr2, $arr3, $arr4, $arr5);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Churn Rate / Account Type',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartA30() {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, sum(A3) A3 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";					
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, sum(A7) A7 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dot',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, sum(A15) A15 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dash',
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, sum(A30) A30 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--DashDot',
					];
		$arr5 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr2, $arr3, $arr4, $arr5);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A3, A7, A15, A30',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPUChurn() {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(PUChurn3)*100, 1) as PUChurn3 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(PUChurn7)*100, 1) as PUChurn7 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dot',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(PUChurn15)*100, 1) as PUChurn15 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--Dash',
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, AccountType, round(sum(PUChurn30)*100, 1) as PUChurn30 from dailyacctype where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate, AccountType
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line--DashDot',
					];
		$arr5 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr2, $arr3, $arr4, $arr5);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'PU Churn Rate / Account Type',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridAll() {
		$table = '';
		
        $sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, AccountType, Platform, A1, N1, DeviceOfN1, NewDeviceOfN1, N1OfNewDevice, ActiveDeviceCount ActiveDevice, P1, NPU FPU, RevGross, New_RR1, New_RR3, New_RR7, New_RR15, New_RR30, New_CV0, New_CV1, New_CV3, New_CV7, New_CV15, New_CV30, New_PU30, New_Rev30Gross, ".
				" A3, A7, A15, A30, Churn1, Churn3, Churn7, Churn15, Churn30, PUChurn3, PUChurn7, PUChurn15, PUChurn30 ".
				" from dailyacctype where AppName=".$this->quote($this->AppName)." and Platform <> 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'align' => array ('AccountType' => 'left'),
					'datatype' => ['A3'=>'DEC0', 'A7'=>'DEC0', 'A15'=>'DEC0', 'A30'=>'DEC0',
								'Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Churn15'=>'PERCENT', 'Churn30'=>'PERCENT', 
								'PUChurn3'=>'PERCENT', 'PUChurn7'=>'PERCENT', 'PUChurn15'=>'PERCENT', 'PUChurn30'=>'PERCENT', 
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT', 
								'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT', ]
					];
        $table .= $this->_createGridData_html($data, $option);
		
		return $table;
	}
}
