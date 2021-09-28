<?php
namespace App\Http\Controllers\Account;
use View;
use App\Http\Controllers\CommonFunction;

class AccountPlatform extends CommonFunction {
	
	public function index ($type='') {

		$this->listChart = array(
			'chartA1' => $this->chartA1(),
			'chartN1' => $this->chartN1(),
			'chartRev' => $this->chartRev(),
			'chartARPU' => $this->chartARPU(),
			'chartCV' => $this->chartCV(),
			'chartRR' => $this->chartRR(),
			'chartChurn' => $this->chartChurn(),
			'chartPUChurn' => $this->chartPUChurn(),
			'chartA30' => $this->chartA30(),
			'gridAll' => $this->gridAll()
		);				
		// $this->listChartId=['chartA1','chartN1','chartRev','chartARPU','chartCV','chartRR',
		// 		'chartChurn','chartPUChurn','chartA30']; 

		// $this->addViewsToMain=json_encode(
		// 	$this->addChart('chartA1').
		// 	$this->addChart('chartN1').
		// 	$this->addChart('chartRev').
		// 	$this->addChart('chartARPU').
		// 	$this->addChart('chartCV').
		// 	$this->addChart('chartRR').			
		// 	$this->addChart('chartChurn').
		// 	$this->addChart('chartPUChurn').			
		// 	$this->addChart('chartA30').
		// 	$this->addGrid('', 'gridAll', $this->gridAll(), false).
		// 	''
		// );

		$this->pageTitle='Account by Platform';	
		
		return parent::__index($this->addTopViewApp($this->listApp, 'Group101'), $type);
	}	
	
	function chartA1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, A1 from daily where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'A1'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        /*$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, OpenDevice from daily where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					'stackname' => 'OpenDevice',
					'invisible' => ['OpenDevice_iOS','OpenDevice_Android','OpenDevice_WP','OpenDevice_Web']
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);*/
		
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

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, DeviceOfN1 from daily where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";	
        $data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);			
		$options = ['type' => 'column',
					'Device' => 'Install',
					'invisible' => ['DeviceOfN1_iOS','DeviceOfN1_Android','DeviceOfN1_WP','DeviceOfN1_Web'],					
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
		
		$options = [	'title' => 'N1 / Platform',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartRev () {

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, RevGross from daily 
			where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);	
		
		$options = ['type' => 'column',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Platform, P1 as PU from daily 
			where AppName=".$this->quote($this->AppName)." and Platform not in ('WP','AllPlatform') and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata_withColNamePrefix($data);	
		
		$options = ['type' => 'line',
					'yAxis' => 1,
					'invisible' => ['PU_iOS','PU_Android','PU_WP','PU_Web'],
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);		      
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Revenue / Platform',
						'subtitle' => $this->AppName,
						'yAxis_title' => ['VND','#user'],
					];
		return $this->script_chart2Y($categories, $highchartseries, $options);
	}
	
	function chartRR () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_RR1*100,1) RR1_Android, round(New_RR3*100,1) RR3_Android, round(New_RR7*100,1) RR7_Android, round(New_RR15*100,1) RR15_Android, round(New_RR30*100,1) RR30_Android from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Android' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--Dot',
					'invisible' => ['RR7_Android','RR15_Android','RR30_Android'],
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		/*$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_RR1*100,1) RR1_WP, round(New_RR3*100,1) RR3_WP, round(New_RR7*100,1) RR7_WP, round(New_RR15*100,1) RR15_WP, round(New_RR30*100,1) RR30_WP from daily where AppName=".$this->quote($this->AppName)." and Platform = 'WP' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data1 = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--Dash',
					'invisible' => ['RR7_WP','RR15_WP','RR30_WP'],
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($data1, $options, true);*/

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_RR1*100,1) RR1_Web, round(New_RR3*100,1) RR3_Web, round(New_RR7*100,1) RR7_Web, round(New_RR15*100,1) RR15_Web, round(New_RR30*100,1) RR30_Web from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Web' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data1 = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--LongDash',
					'invisible' => ['RR7_Web','RR15_Web','RR30_Web'],
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($data1, $options, true);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_RR1*100,1) RR1_iOS, round(New_RR3*100,1) RR3_iOS, round(New_RR7*100,1) RR7_iOS, round(New_RR15*100,1) RR15_iOS, round(New_RR30*100,1) RR30_iOS from daily where AppName=".$this->quote($this->AppName)." and Platform = 'iOS' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$fixedData = $this->fillMissingDate($data, $categories);
		
		$options = ['type' => 'line',
					'invisible' => ['RR7_iOS','RR15_iOS','RR30_iOS'],
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($fixedData, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr4); // , $arr3		
		
		$options = [	'title' => 'Retention Rate / Platform',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartCV () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_CV0*100,1) CV0_Android, round(New_CV1*100,1) CV1_Android, round(New_CV3*100,1) CV3_Android, round(New_CV7*100,1) CV7_Android, round(New_CV15*100,1) CV15_Android, round(New_CV30*100,1) CV30_Android from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Android' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--Dot',
					'invisible' => ['CV7_Android','CV15_Android','CV30_Android'],
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		/*$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_CV0*100,1) CV0_WP, round(New_CV1*100,1) CV1_WP, round(New_CV3*100,1) CV3_WP, round(New_CV7*100,1) CV7_WP, round(New_CV15*100,1) CV15_WP, round(New_CV30*100,1) CV30_WP from daily where AppName=".$this->quote($this->AppName)." and Platform = 'WP' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data1 = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--Dash',
					'invisible' => ['CV7_WP','CV15_WP','CV30_WP'],
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($data1, $options, true);*/

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_CV0*100,1) CV0_Web, round(New_CV1*100,1) CV1_Web, round(New_CV3*100,1) CV3_Web, round(New_CV7*100,1) CV7_Web, round(New_CV15*100,1) CV15_Web, round(New_CV30*100,1) CV30_Web from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Web' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data1 = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--LongDash',
					'invisible' => ['CV7_Web','CV15_Web','CV30_Web'],
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($data1, $options, true);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_CV0*100,1) CV0_iOS, round(New_CV1*100,1) CV1_iOS, round(New_CV3*100,1) CV3_iOS, round(New_CV7*100,1) CV7_iOS, round(New_CV15*100,1) CV15_iOS, round(New_CV30*100,1) CV30_iOS from daily where AppName=".$this->quote($this->AppName)." and Platform = 'iOS' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$fixedData = $this->fillMissingDate($data, $categories);
		
		$options = ['type' => 'line',
					'invisible' => ['CV7_iOS','CV15_iOS','CV30_iOS'],	
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($fixedData, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr4); // , $arr3		
		
		$options = [	'title' => 'Conversion Rate / Platform',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartChurn () {        
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(Churn3*100,1) Churn3_Android, round(Churn7*100,1) Churn7_Android, round(Churn15*100,1) Churn15_Android, round(Churn30*100,1) Churn30_Android from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Android' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--Dot',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(Churn3*100,1) Churn3_Web, round(Churn7*100,1) Churn7_Web, round(Churn15*100,1) Churn15_Web, round(Churn30*100,1) Churn30_Web from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Web' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data1 = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--LongDash',
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($data1, $options, true);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(Churn3*100,1) Churn3_iOS, round(Churn7*100,1) Churn7_iOS, round(Churn15*100,1) Churn15_iOS, round(Churn30*100,1) Churn30_iOS from daily where AppName=".$this->quote($this->AppName)." and Platform = 'iOS' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$fixedData = $this->fillMissingDate($data, $categories);
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($fixedData, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr4); //, $arr3		
		
		$options = [	'title' => 'Churn Rate / Platform',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartPUChurn () {

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(PUChurn3*100,1) PUChurn3_Android, round(PUChurn7*100,1) PUChurn7_Android, round(PUChurn15*100,1) PUChurn15_Android, round(PUChurn30*100,1) PUChurn30_Android from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Android' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--Dot',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(PUChurn3*100,1) PUChurn3_Web, round(PUChurn7*100,1) PUChurn7_Web, round(PUChurn15*100,1) PUChurn15_Web, round(PUChurn30*100,1) PUChurn30_Web from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Web' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data1 = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--LongDash',
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($data1, $options, true);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(PUChurn3*100,1) PUChurn3_iOS, round(PUChurn7*100,1) PUChurn7_iOS, round(PUChurn15*100,1) PUChurn15_iOS, round(PUChurn30*100,1) PUChurn30_iOS from daily where AppName=".$this->quote($this->AppName)." and Platform = 'iOS' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$fixedData = $this->fillMissingDate($data, $categories);
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($fixedData, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr4); // , $arr3		
		
		$options = [	'title' => 'PU Churn Rate / Platform',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartA30 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, A3 A3_Android, A7 A7_Android, A15 A15_Android, A30 A30_Android from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Android' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--Dot',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, A3 A3_Web, A7 A7_Web, A15 A15_Web, A30 A30_Web from daily where AppName=".$this->quote($this->AppName)." and Platform = 'Web' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data1 = $this->getDataSQL($sql);
		
		$options = ['type' => 'line--LongDash',
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($data1, $options, true);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, A3 A3_iOS, A7 A7_iOS, A15 A15_iOS, A30 A30_iOS from daily where AppName=".$this->quote($this->AppName)." and Platform = 'iOS' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		$fixedData = $this->fillMissingDate($data, $categories);		
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($fixedData, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr4); // , $arr3		
		
		$options = [	'title' => 'A3, A7, A15, A30',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartARPU () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(RevGross/A1,-2) ARPU_Android, round(RevGross/P1,-2) ARPPU_Android from daily 
        where AppName=".$this->quote($this->AppName)." and Platform = 'Android' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);		
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = ['type' => 'line--Dot',
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(RevGross/A1,-2) ARPU_Web, round(RevGross/P1,-2) ARPPU_Web from daily 
        where AppName=".$this->quote($this->AppName)." and Platform = 'Web' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data1 = $this->getDataSQL($sql);		
		
		$options = ['type' => 'line--LongDash',
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($data1, $options, true);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(RevGross/A1,-2) ARPU_iOS, round(RevGross/P1,-2) ARPPU_iOS from daily 
        where AppName=".$this->quote($this->AppName)." and Platform = 'iOS' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."order by ReportDate";		
		$data = $this->getDataSQL($sql);		
		$fixedData = $this->fillMissingDate($data, $categories);
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($fixedData, $options, true);
		
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2, $arr4); // , $arr3				
		
		$options = [	'title' => 'ARPU / Platform',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridAll() {
        $sql = "select ReportDate, Platform, A1, N1, DeviceOfN1, NewDeviceOfN1, N1OfNewDevice, InstallDeviceCount Install, ".
				" ActiveDeviceCount ActiveDevice, P1, NPU FPU, RevGross, ".
				" New_RR1, New_RR3, New_RR7, New_RR15, New_RR30, New_CV0, New_CV1, New_CV3, New_CV7, New_CV15, New_CV30, New_PU30, New_Rev30Gross, ".
				" A3, A7, A15, A30, Churn1, Churn3, Churn7, Churn15, Churn30, ".
				" PUChurn3, PUChurn7, PUChurn15, PUChurn30 ".
				" from daily where AppName=".$this->quote($this->AppName)." and Platform <> 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'datatype' => ['A3'=>'DEC0', 'A7'=>'DEC0', 'A15'=>'DEC0', 'A30'=>'DEC0',
								'Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Churn15'=>'PERCENT', 'Churn30'=>'PERCENT',
								'PUChurn3'=>'PERCENT', 'PUChurn7'=>'PERCENT', 'PUChurn15'=>'PERCENT', 'PUChurn30'=>'PERCENT',
								'Session_AvgQty'=>'DEC2',
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT',
								'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT', ],
					];
        return $this->_createGridData_html($data, $option);
	}

	function fillMissingDate($data, $categories) {
		return $data;
	}
}
