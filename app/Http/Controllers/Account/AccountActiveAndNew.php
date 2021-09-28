<?php
namespace App\Http\Controllers\Account;
use View;
use App\Http\Controllers\CommonFunction;

class AccountActiveAndNew extends CommonFunction {
	
	public function index ($type='') {
		$this->listCountryFromListApp(); // câu này + thêm if bên dưới + move addTopViewApp + $viewparams + đổi view
		if ($this->selectedCountry == '--All--')
			$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		else
			$listApp = $this->reListAppByCountry($this->selectedCountry);
		$this->listChart = array(
			'chartA1' => $this->chartA1(),
			'chartN1' => $this->chartN1(),
			'chartRev' => $this->chartRev(),
			'chartRR' => $this->chartRR(),
			'chartCV' => $this->chartCV(),
			'chartChurn' => $this->chartChurn(),
			'chartPUChurn' => $this->chartPUChurn(),
			'chartARPU' => $this->chartARPU(),
			'chartPR' => $this->chartPR(),
//			'chartLTV' => $this->chartLTV(),
			'chartA30' => $this->chartA30(),
			'gridAll' => $this->gridAll(),
		);

// 		$this->addViewsToMain=json_encode(
// 			$this->addChart('chartA1').
// 			$this->addChart('chartN1').
// 			$this->addChart('chartRev').
// 			$this->addChart('chartRR').
// 			$this->addChart('chartCV').
// 			"<div>".
// 			$this->addChart('chartChurn','two_chart chart', false).
// 			$this->addChart('chartPUChurn','two_chart chart', false).
// 			"</div>".
// 			$this->addChart('chartARPU').
// 			$this->addChart('chartPR').
// //			$this->addChart('chartLTV').
// 			$this->addChart('chartA30').			
// 			$this->addGrid('', 'gridAll', $this->gridAll(), false).
// 			''
// 		);

		// $this->pageInfo=
		// "<p>
		// 	<br>Diễn giải:
		// 	<br> - ActiveDevice : Số device theo log LOGIN
		// 	<br> - Install : Số device mới theo log INSTALL
		// 	<br> - RRx : Retention rate, phần trăm N1 có active lại sau x ngày
		// 	<br> - Churn rate (ChurnX): %A1 không thấy active sau X ngày. VD: Churn3 của ngày 10/9 là tính trên A1 10/9 không thấy active 11,12,13/9.
		// </p>";
		$this->pageTitle='Account New & Active';	
		
		$viewparams = ['listCountry' => $this->listCountry,
						'selectedCountry' => $this->selectedCountry,];

		return parent::__index($listApp, $type, $viewparams);
	}
	
	function chartA1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, A1 from daily where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'A1'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, A1 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, ActiveDeviceCount ActiveDevice from daily where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'ActiveDevice',
					'invisible' => ['ActiveDevice_iOS','ActiveDevice_Android','ActiveDevice_WP','ActiveDevice_Web']
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr3, $arr4);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A1',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartN1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, N1 from daily where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'N1'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, InstallDeviceCount Install from daily where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'Install',
					'invisible' => ['Install_Web']
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, N1 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'line',
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr3);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'N1',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartRev () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, RevGross from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'area',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, NPU FPU, P1 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					'yAxis' => 1,
					'invisible' => ['P1']
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
	
	function chartRR () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_RR1*100,1) RR1, round(New_RR3*100,1) RR3, round(New_RR7*100,1) RR7, round(New_RR15*100,1) RR15, round(New_RR30*100,1) RR30 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Retention Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartCV () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_CV0*100,1) CV0, round(New_CV1*100,1) CV1, round(New_CV3*100,1) CV3, round(New_CV7*100,1) CV7, round(New_CV15*100,1) CV15, round(New_CV30*100,1) CV30 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
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
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartChurn () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(Churn1*100,1) Churn1, round(Churn3*100,1) Churn3, round(Churn7*100,1) Churn7, round(Churn15*100,1) Churn15, round(Churn30*100,1) Churn30 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Churn Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartPUChurn () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(PUChurn3*100,1) PUChurn3, round(PUChurn7*100,1) PUChurn7, round(PUChurn15*100,1) PUChurn15, round(PUChurn30*100,1) PUChurn30 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'PU Churn Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartARPU () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(RevGross/A1) ARPU, round(RevGross/P1) ARPPU from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'ARPU',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'VND',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartPR () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(P1*100/A1,1) PR from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Paying Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
/*	
	function chartLTV () {
		$sql_std = "select date_format(ReportDate+interval {NUMDAY} day,'".$this->formatXDate()."') ReportDate, round(New_Rev{NUMDAY}/N1,-2) Age{NUMDAY}_LTV{NUMDAY} from daily where AppName=".$this->quote($this->AppName)." and Platform='AllPlatform' and ReportDate between ".$this->quote($this->fromDate)."- interval {NUMDAY} day and ".$this->quote($this->toDate)."- interval {NUMDAY} day order by ReportDate ";
		
		$options = ['type' => 'line',
					];
		
		// 30d
        $sql = str_replace('{NUMDAY}','30',$sql_std);
		$data = $this->getDataSQL($sql);
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// 60d
        $sql = str_replace('{NUMDAY}','60',$sql_std);
		$data = $this->getDataSQL($sql);
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// 90d
        $sql = str_replace('{NUMDAY}','90',$sql_std);
		$data = $this->getDataSQL($sql);
		$arr3 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// 120d
        $sql = str_replace('{NUMDAY}','120',$sql_std);
		$data = $this->getDataSQL($sql);
		$arr4 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// 150d
        $sql = str_replace('{NUMDAY}','150',$sql_std);
		$data = $this->getDataSQL($sql);
		$arr5 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// 180d
        $sql = str_replace('{NUMDAY}','180',$sql_std);
		$data = $this->getDataSQL($sql);
		$arr6 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr3, $arr4, $arr5, $arr6);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'LTV of old user (ARPNewUser of old day)',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'VND',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
*/	
	function chartA30 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, A3, A7, A15, A30 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A3, A7, A15, A30',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
/* delete 2021-03-09
	function chartA1Age () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, `A1_AgeOlder`,`A1_Age91-180`,`A1_Age61-90`, `A1_Age31-60`, `A1_Age16-30`, `A1_Age8-15`, 
		`A1_Age4-7`, `A1_Age2-3`, `A1_Age1`, `A1_Age0`
			from tracker_report_dis.daily_age_report
			where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and AppName = ".$this->quote($this->AppName)."  and Platform = 'AllPlatform' order by ReportDate";
		
		// dd($sql);
		return $this->chart1Stack($sql, 'A1 By Age', $this->AppName, '#', false, $this->pdoAuthen);
	}

	function gridA1Age(){
		$sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, AppName, `A1_AgeOlder`,`A1_Age91-180`,`A1_Age61-90`, `A1_Age31-60`, `A1_Age16-30`, `A1_Age8-15`, 
		`A1_Age4-7`, `A1_Age2-3`, `A1_Age1`, `A1_Age0`
			from tracker_report_dis.daily_age_report
			where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and AppName = ".$this->quote($this->AppName)."  and Platform = 'AllPlatform' order by ReportDate desc";
		
		return $this->createGrid($sql, 'tableid_gridA1Age', [], $this->pdoAuthen);
	}
	*/
	
	function gridAll() {
        $sql = "select ReportDate, A1, N1, DeviceOfN1, NewDeviceOfN1, N1OfNewDevice, InstallDeviceCount Install, ".
				" ActiveDeviceCount ActiveDevice, P1, NPU FPU, RevGross, RevNet, RevGG, RevApple, ".
				" New_RR1, New_RR3, New_RR7, New_RR15, New_RR30, New_CV0, New_CV1, New_CV3, New_CV7, New_CV15, New_CV30, New_PU30, New_Rev30Gross, ".
				" if(A1=0,0,RevGross/A1) ARPU, if(P1=0,0,RevGross/P1) ARPPU, ".
				" A3, A7, A15, A30, Churn1, Churn3, Churn7, Churn15, Churn30, ".
				" PUChurn3, PUChurn7, PUChurn15, PUChurn30 ".
				" from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'datatype' => ['A3'=>'DEC0', 'A7'=>'DEC0', 'A15'=>'DEC0', 'A30'=>'DEC0',
								'Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Churn15'=>'PERCENT', 'Churn30'=>'PERCENT',
								'PUChurn3'=>'PERCENT', 'PUChurn7'=>'PERCENT', 'PUChurn15'=>'PERCENT', 'PUChurn30'=>'PERCENT',
								'ARPU'=>'DEC0', 'ARPPU'=>'DEC0', 
								'Session_AvgQty'=>'DEC2',
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT',
								'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT', ],
					];
        return $this->_createGridData_html($data, $option);
	}
}
