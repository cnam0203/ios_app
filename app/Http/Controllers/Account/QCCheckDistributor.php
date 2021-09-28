<?php
namespace App\Http\Controllers\Account;
use View, Request;
use App\Http\Controllers\CommonFunction;

class QCCheckDistributor extends CommonFunction {

	public function index ($type='') {
		$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		
		$this->listChart = array(
			'chartA1Distributor' => $this->chartA1Distributor(),
		);				
		$this->listChartId=['chartA1Distributor']; 

		$this->addViewsToMain=json_encode(
			$this->addChart('chartA1Distributor').'<br>'.
			''
		);		

		return View::make('pages.Common1Field',array(  // pages.CommonShowTop
					'pagetitle' => 'QC Check Distributor',
					'menu' => $this->menu,
					'date' => $this->date,
					'listApp'=> $listApp,
					'selectedApp' => $this->AppName,
					
					'addViewsToMain' => $this->addViewsToMain,

					'functions' => $this->createJSFunction($this->listChartId),
					'addViews'=>$this->addViews,
					'annotation'=>$this->getListAnnotation(),
					'darkMode'=>$this->darkMode,
					'pageInfo'=>$this->pageInfo,

					'field1Title'=>'App Name',
				));
	}	

	function chartA1Distributor () { // newest , maxvalue=100
		// list newest distributor  
		$sqlform = "select distinct Distributor from dailydistributor 
			where Distributor not like 'ZPPortal_fbDL%' and Distributor not like 'fbDL%' 
				and Platform = 'AllPlatform' and AppName=".$this->quote($this->AppName)."";
		$sql = $sqlform . " and ReportDate >= curdate() - interval 31 day " . 
				" and Distributor not in (".$sqlform." and ReportDate >= curdate() - interval 120 day and ReportDate < curdate() - interval 31 day)";
		$data = $this->getDataSQL($sql);
		$dis = [];
		foreach ($data as $row)
			$dis[] = $row['Distributor'];
		$str = "'".implode("','",$dis)."'";
		
		// 
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Distributor, if(A1>100,100,A1) A1
				from dailydistributor where Platform = 'AllPlatform' and Distributor in (".$str.")
					and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
					and AppName=".$this->quote($this->AppName)."
				order by ReportDate";
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data);
		$visibleItems = $this->getBiggestFields($pivot, 4);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);		
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A1 of Newest Distributor (Max Value=100)',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
}
