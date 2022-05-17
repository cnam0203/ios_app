<?php
namespace App\Http\Controllers\Account;
use View, Request;
use App\Http\Controllers\CommonFunction;

class AccountDistributor extends CommonFunction {

	const SORT_NAME_ASC = 'name_asc';
	const SORT_NAME_DESC = 'name_desc';
	const SORT_VALUE_ASC = 'value_asc';
	const SORT_VALUE_DESC = 'value_desc';
	const SHOW_ITEM_NUMBER=[5, 10, 20, 50, 100, 1000];
	
	public function index ($type='') {
		// if(Request::input('exportData')){
		// 	$this->exportData(json_decode($params['exportData'], true));
		// }
		
		$this->listCountryFromListApp(); // câu này + thêm if bên dưới + move addTopViewApp + $viewparams + đổi view
		if ($this->selectedCountry == '--All--')
			$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		else
			$listApp = $this->reListAppByCountry($this->selectedCountry);
		
		$sortOptions=['Dis Name asc'=>self::SORT_NAME_ASC, 'Dis Name desc'=>self::SORT_NAME_DESC, 
					'Value asc'=>self::SORT_VALUE_ASC, 'Value desc'=>self::SORT_VALUE_DESC];
		$this->getSortType(Request::query('SortBy'));
		$this->getNumberItemShow(Request::query('ShowTop'));		
		
		$this->listChart = array(
			'chartA1' => $this->chartA1(),
			'chartN1' => $this->chartN1(),
			'chartInstall' => $this->chartInstall(),
			'chartRev' => $this->chartRev(),
			'chartRR1' => $this->chartRR(1),
			'chartRR3' => $this->chartRR(3),
			'chartRR7' => $this->chartRR(7),
			'chartCV1' => $this->chartCV(1),
			'chartCV3' => $this->chartCV(3),
			'chartCV7' => $this->chartCV(7),
			'chartChurn3' => $this->chartChurn(3),
			'chartChurn7' => $this->chartChurn(7),
			'chartPUChurn3' => $this->chartPUChurn(3),
			'chartPUChurn7' => $this->chartPUChurn(7),
			'chartA30' => $this->chartA30(),
			'gridAll' => $this->gridAll(),
		);				

		// $this->addViewsToMain=json_encode(
		// 	$this->addCharts(array_keys($this->listChart)).
		// 	$this->addGrid('', 'gridAll', $this->gridAll(), false).
		// 	''
		// );		
		
		$viewparams = ['listCountry' => $this->listCountry,
						'selectedCountry' => $this->selectedCountry,
						'numberItem'=>self::SHOW_ITEM_NUMBER,
						'numberItemSelect'=>$this->numberItemSelect,
						'sortOptions'=>$sortOptions,
						'sortSelect'=>$this->sortSelect,];
						
		return parent::__index($listApp, $type, $viewparams);
	}	

	private function getSortType($inSort)
	{
		if($inSort){
			$this->sortSelect = $inSort;
		}else{
			$this->sortSelect = self::SORT_VALUE_DESC;
		}
		$tmp = explode("_", $this->sortSelect);
		$this->sortType = $tmp[0];
		$this->sortOrder= $tmp[1];
	}

	private function getNumberItemShow($inNumber)
	{
		if($inNumber){
			$this->numberItemSelect = $inNumber;
		}else{
			$this->numberItemSelect = self::SHOW_ITEM_NUMBER[1];
		}		
	}

	private function fixDataByColName($data, $listShow){		
		$res = [];
		foreach($data as $colName=>$items){
			$res[$colName] = [];
			foreach($items as $itemName=>$itemValue){				
				if(in_array($itemName, $listShow)){
					$res[$colName][$itemName] = $itemValue;
				}				
			}
		}
		return $res;
	}
	
	function chartA1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, sum(A1) as A1 from
					(select ReportDate, Distributor DistributorGroup, A1
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";
		$data = $this->getDataSQL($sql);		
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);			
		$visibleItems = $this->getBiggestFields($pivot, 4);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);		
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A1 / Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'chart_height'=>300,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartN1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, sum(N1) as N1 from
					(select ReportDate, Distributor DistributorGroup, N1
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		$visibleItems = $this->getBiggestFields($pivot, 4);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'N1 / Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'chart_height'=>300,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartInstall () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, sum(InstallDeviceCount) as Install from
					(select ReportDate, Distributor DistributorGroup, InstallDeviceCount
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		$visibleItems = $this->getBiggestFields($pivot, 4);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Install / Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'chart_height'=>300,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartRev () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, sum(RevGross) as Rev from
					(select ReportDate, Distributor DistributorGroup, A1, RevGross	
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		$visibleItems = $this->getBiggestFields($pivot, 4);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Revenue / Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'chart_height'=>300,
						// 'chart_backgroundColor' => $this->darkMode?'#4b574b':'#EFE',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartRR ($RRnum=1) {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, round(sum(New_RR".$RRnum.")*100 / count(DistributorGroup), 1) as RR".$RRnum." from
					(select ReportDate, Distributor DistributorGroup, New_RR".$RRnum."	
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		
		$options = ['type' => 'line',
					'visible' => ['RR'.$RRnum.'__'],
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Retension Rate '.$RRnum.' / Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'chart_height'=>300,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartCV ($RRnum=1) {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, round(sum(New_CV".$RRnum.")*100 / count(DistributorGroup), 1) as CV".$RRnum." from
					(select ReportDate, Distributor DistributorGroup, New_CV".$RRnum."
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";		
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		
		$options = ['type' => 'line',
					'visible' => ['CV'.$RRnum.'__'],
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Conversion Rate '.$RRnum.' / Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'chart_height'=>300,
						// 'chart_backgroundColor' => $this->darkMode?'#4b574b':'#EFE',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartChurn ($RRnum=1) {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, round(sum(Churn".$RRnum.")*100 / count(DistributorGroup), 1) as Churn".$RRnum." from
					(select ReportDate, Distributor DistributorGroup, Churn".$RRnum.", N1
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate,N1 desc";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		
		$options = ['type' => 'line',
					'visible' => ['Churn'.$RRnum.'__'],
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Churn Rate '.$RRnum.'/ Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'chart_height'=>300,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartPUChurn ($RRnum=1) {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, round(sum(PUChurn".$RRnum.")*100 / count(DistributorGroup), 1) as PUChurn".$RRnum." from
					(select ReportDate, Distributor DistributorGroup, PUChurn".$RRnum.", N1
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate,N1 desc";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		
		$options = ['type' => 'line',
					'visible' => ['PUChurn'.$RRnum.'__'],
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);


		// build high chart
		$highchartseries = array_merge($arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'PU Churn Rate '.$RRnum.'/ Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						// 'chart_backgroundColor' => $this->darkMode?'#4b574b':'#EFE',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartA30 () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, A3 from
					(select ReportDate, Distributor DistributorGroup, A3, N1
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate,N1 desc";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		// dd($data[0]["Distributor"]);
		$visible = array();

		if (count($data) > 1)
			array_push($visible, 'A3_'.$data[1]["Distributor"]);	
		if (count($data) > 0)
			array_push($visible, 'A3_'.$data[0]["Distributor"]);

		// array_push($visible, 'A3_'.$data[0]["Distributor"]);
		// array_push($visible, 'A3_'.$data[1]["Distributor"]);		
		// dd($visible);
		
		$options = ['type' => 'line',
					'visible' => $visible,
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, A7 from
					(select ReportDate, Distributor DistributorGroup, A7, N1	
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate,N1 desc";					
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		$visible = array();
		// array_push($visible, 'A7_'.$data[0]["Distributor"]);
		// array_push($visible, 'A7_'.$data[1]["Distributor"]);
		if (count($data) > 1)
			array_push($visible, 'A7_'.$data[1]["Distributor"]);	
		if (count($data) > 0)
			array_push($visible, 'A7_'.$data[0]["Distributor"]);
		
		$options = ['type' => 'line--Dot',
					'visible' => $visible,
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, A15 from
					(select ReportDate, Distributor DistributorGroup, A15, N1	
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate,N1 desc";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		$visible = array();

		if (count($data) > 1)
			array_push($visible, 'A15_'.$data[1]["Distributor"]);	
		if (count($data) > 0)
			array_push($visible, 'A15_'.$data[0]["Distributor"]);
		// array_push($visible, 'A15_'.$data[0]["Distributor"]);
		// array_push($visible, 'A15_'.$data[1]["Distributor"]);		

		$options = ['type' => 'line--Dash',
					'visible' => $visible,
					];
		$arr4 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, A30 from
					(select ReportDate, Distributor DistributorGroup, A30, N1	
						from dailydistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate,N1 desc";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		$visible = array();
		// array_push($visible, 'A30_'.$data[0]["Distributor"]);
		// array_push($visible, 'A30_'.$data[1]["Distributor"]);
		if (count($data) > 1)
			array_push($visible, 'A30_'.$data[1]["Distributor"]);	
		if (count($data) > 0)
			array_push($visible, 'A30_'.$data[0]["Distributor"]);		

		$options = ['type' => 'line--DashDot',
					'visible' => $visible,
					];
		$arr5 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr2, $arr3, $arr4, $arr5);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A3, A7, A15, A30',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'chart_height' => 550,
						'chart_height'=>300,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridAll() {
        $sql = "select ReportDate, Distributor, Platform, A1, N1, DeviceOfN1, NewDeviceOfN1, N1OfNewDevice, InstallDeviceCount Install, ActiveDeviceCount ActiveDevice, P1, NPU FPU, RevGross, New_RR1, New_RR3, New_RR7, New_RR15, New_RR30, New_CV0, New_CV1, New_CV3, New_CV7, New_CV15, New_CV30, New_PU30, New_Rev30Gross, ".
				" A3, A7, A15, A30, Churn1, Churn3, Churn7, Churn15, Churn30, PUChurn3, PUChurn7, PUChurn15, PUChurn30 ".
				" from dailydistributor where AppName=".$this->quote($this->AppName)." and Platform <> 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'align' => array ('Distributor' => 'left'),
					'datatype' => ['NewDeviceOfN1'=>'DEC0', 'N1OfNewDevice'=>'DEC0', 'A3'=>'DEC0', 'A7'=>'DEC0', 'A15'=>'DEC0', 'A30'=>'DEC0', 'New_PU30'=>'DEC0', 'New_Rev30Gross'=>'DEC0', 	
								'Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Churn15'=>'PERCENT', 'Churn30'=>'PERCENT', 
								'PUChurn3'=>'PERCENT', 'PUChurn7'=>'PERCENT', 'PUChurn15'=>'PERCENT', 'PUChurn30'=>'PERCENT', 
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT', 
								'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT', 
								],
					];
        return $this->_createGridData_html($data, $option);
	}
}
