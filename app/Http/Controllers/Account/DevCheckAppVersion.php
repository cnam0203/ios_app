<?php
namespace App\Http\Controllers\Account;
use View, Request;
use App\Http\Controllers\CommonFunction;

class DevCheckAppVersion extends CommonFunction {

	public function index ($type='') {
		$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		
		$newver = $this->NewestAppVersion();
		$this->listChart = array(
			'chartA1' => $this->chartA1($newver),
			'chartN1' => $this->chartN1($newver),
		);				
		$this->listChartId=['chartA1','chartN1']; 
		
		$this->addViewsToMain=json_encode(
			$this->addChart('chartA1').
			$this->addChart('chartN1').			
			''
		);		

		return View::make('pages.Common1Field',array(
					'pagetitle' => 'Dev Check AppVersion',
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
	
	function NewestAppVersion () {
		// list newest   
		$sqlform = "select distinct concat(Platform,'_', AppVersion) AppVersion from tracker_report_dis.daily_appversion 
			where Platform <> 'AllPlatform' and AppName=".$this->quote($this->AppName)."";
		$sql = $sqlform . " and ReportDate >= curdate() - interval 31 day " . 
				" and (AppName not in ('p13_mm','p2_ph') or AppName in ('p13_mm','p2_ph') and AppVersion not regexp '[_][0-9][_][0-9][0-9][0-9]$' and AppVersion not regexp '[_][0-9][0-9][_][0-9][0-9][0-9]$' )". // hardcode for p13_mm, p2_ph
				" and concat(Platform,'_', AppVersion) not in (".$sqlform." and ReportDate >= curdate() - interval 120 day and ReportDate < curdate() - interval 31 day)";
		$data = $this->getDataPDOSQL($this->pdoAuthen, $sql);
		$dis = [];
		foreach ($data as $row)
			$dis[] = $row['AppVersion'];
		return $str = "'".implode("','",$dis)."'";
	}
	
	function chartA1 ($newver) {		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, concat(Platform,'_', AppVersion) AppVersion, if(sum(A1)>100,100,sum(A1)) as A1 from tracker_report_dis.daily_appversion 
						where Platform != 'AllPlatform' and concat(Platform,'_', AppVersion) in (".$newver.")
							and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate,Platform, AppVersion
					order by ReportDate,Platform, AppVersion";
		$data = $this->getDataPDOSQL($this->pdoAuthen, $sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);				
		$visibleItems = $this->getBiggestFields($pivot, 8);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);		
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A1 of Newest AppVersion (max=100)',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartN1 ($newver) {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, concat(Platform,'_', AppVersion) AppVersion, if(sum(N1)>100,100,sum(N1)) as N1 from tracker_report_dis.daily_appversion 
					where Platform != 'AllPlatform' and concat(Platform,'_', AppVersion) in (".$newver.")
						and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
				group by ReportDate,Platform, AppVersion
				order by ReportDate,Platform, AppVersion";
		$data = $this->getDataPDOSQL($this->pdoAuthen,  $sql);
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);
		$visibleItems = $this->getBiggestFields($pivot, 4);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'N1 of Newest AppVersion (max=100)',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
}
