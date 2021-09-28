<?php
namespace App\Http\Controllers\Account;
use View, Request;
use App\Http\Controllers\CommonFunction;

class AccountAppVersion extends CommonFunction {

	const SORT_NAME_ASC = 'name_asc';
	const SORT_NAME_DESC = 'name_desc';
	const SORT_VALUE_ASC = 'value_asc';
	const SORT_VALUE_DESC = 'value_desc';
	const SHOW_ITEM_NUMBER=[5, 10, 20, 50, 100, 1000];
	
	public function index ($type='') {
		if(Request::input('exportData')){
			$this->exportData(json_decode($params['exportData'], true));
		}
		
		$this->listCountryFromListApp(); // câu này + thêm if bên dưới + move addTopViewApp + $viewparams + đổi view
		if ($this->selectedCountry == '--All--')
			$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		else
			$listApp = $this->reListAppByCountry($this->selectedCountry);
		
		$sortOptions=['Version asc'=>self::SORT_NAME_ASC, 'Version desc'=>self::SORT_NAME_DESC, 
					'Value asc'=>self::SORT_VALUE_ASC, 'Value desc'=>self::SORT_VALUE_DESC];
		$this->getSortType(Request::input('SortBy'));
		$this->getNumberItemShow(Request::input('ShowTop'));		
		
		$this->listChart = array(
			'chartA1' => $this->chartA1(),
			'chartN1' => $this->chartN1(),
		);				
		$this->listChartId=['chartA1','chartN1']; 

		$this->addViewsToMain=json_encode(
			$this->addChart('chartA1').
			$this->addChart('chartN1').			
			''
		);		

		/*$viewparams = ['listCountry' => $this->listCountry,
						'selectedCountry' => $this->selectedCountry,];*/
		return View::make('pages.Common1FieldWithCountry_Sort',array(  // pages.CommonShowTop
					'pagetitle' => 'Account / AppVersion',
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

					'sortOptions'=>$sortOptions,
					'sortSelect'=>$this->sortSelect,
					'numberItem'=>self::SHOW_ITEM_NUMBER,
					'numberItemSelect'=>$this->numberItemSelect,
					'field1Title'=>'App Name',
					
					'listCountry' => $this->listCountry,
					'selectedCountry' => $this->selectedCountry,
				));
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

	function chartA1 () {		
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, concat(Platform,'_', AppVersion) AppVersion, sum(A1) as A1 from tracker_report_dis.daily_appversion 
						where Platform != 'AllPlatform' 
							and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and AppName=".$this->quote($this->AppName)."
					group by ReportDate,Platform, AppVersion
					order by ReportDate,Platform, AppVersion";
		$data = $this->getDataPDOSQL($this->pdoAuthen,  $sql);		
		$pivot = $this->pivotdata_withColNamePrefix_new($data, $this->sortType, $this->sortOrder, $this->numberItemSelect);				
		$visibleItems = $this->getBiggestFields($pivot, 8);
		
		$options = ['type' => 'line',
					'visible' => $visibleItems
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);		
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A1 / AppVersion',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartN1 () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, concat(Platform,'_', AppVersion) AppVersion, sum(N1) as N1 from tracker_report_dis.daily_appversion 
					where Platform != 'AllPlatform' 
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
		
		$options = [	'title' => 'N1 / AppVersion',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'legend_align' => 'right',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
}
