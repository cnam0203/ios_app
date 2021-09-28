<?php
namespace App\Http\Controllers\Device;

use App\Http\Controllers\CommonFunction;

class DeviceDistributor extends CommonFunction {
	
	public function index ($type='') {

		$this->listChart = array(
			'chartA1' => $this->chartA1(),
			'chartN1' => $this->chartN1(),
			'chartRev' => $this->chartRev(),
			'gridAll' => $this->gridAll(),
		);	
	
		// $this->listChartId=array_keys($this->listChart); 	
	
		// $this->addViewsToMain=json_encode(
		// 	$this->addChart('chartA1').
		// 	$this->addChart('chartN1').
		// 	$this->addChart('chartRev').
		// 	$this->addGrid('', 'gridAll', $this->gridAll(), false).
		// 	''
		// );
	
		$this->pageTitle='Device / Distributor';	
			
		return parent::__index($this->addTopViewApp($this->listApp, 'Group101'), $type);
	}
	
	function chartA1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, sum(ActiveDevice) as AD from
					(select ReportDate, Distributor DistributorGroup, ActiveDevice, NewDevice
						from devicedistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		$visibleItems = $this->getBiggestFields($pivot, 4);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'ActiveDevice / Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#device',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartN1 () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, sum(NewDevice) as ND from
					(select ReportDate, Distributor DistributorGroup, ActiveDevice, NewDevice
						from devicedistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		$visibleItems = $this->getBiggestFields($pivot, 4);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'NewDevice / Distributor',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#device',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function chartRev () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, DistributorGroup Distributor, sum(RevGross) as Rev from
					(select ReportDate, Distributor DistributorGroup, ActiveDevice, RevGross	
						from devicedistributor where Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName).") t
					group by ReportDate, DistributorGroup
					order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
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
						'yAxis_title' => '#device',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridAll() {
        $sql = "select ReportDate, Distributor, Platform, ActiveDevice, NewDevice, PayDevice, NewPayDevice, RevGross, New_RR1, New_RR3, New_RR7, New_RR15, New_RR30, Session_AvgDuration, Session_AvgQty, Session_TotalQty ".
				" from devicedistributor where AppName=".$this->quote($this->AppName)." and Platform <> 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'align' => array ('Distributor' => 'left'),
					'datatype' => ['Session_AvgQty'=>'DEC2', 'NewDevice'=>'DEC0', 'PayDevice'=>'DEC0', 'NewPayDevice'=>'DEC0', 
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT', ],
					];
        return $this->_createGridData_html($data, $option);
	}
}
