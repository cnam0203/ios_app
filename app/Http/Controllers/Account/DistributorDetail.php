<?php
namespace App\Http\Controllers\Account;
use App\Http\Controllers\CommonFunction;
use Request;

class DistributorDetail extends CommonFunction {
	
	// get AppName by complex query
	
	public function index ($type='') {
		
		$this->pageTitle='Distributor Detail';
		$this->Country = $this->getCountryOfAppName($this->AppName);
		$this->listDistributor = $this->getListDistributor($this->AppName);
		$this->getInputDistributor();
		
		// list platform
		$listPlatform = $this->listPlatformOfTableDistributor('dailydistributor', $this->AppName, $this->selectedDistributor);
		// dd($listPlatform);
		if (in_array('Android', $listPlatform))
			$this->listChart = array_merge($this->listChart, array('chartPortalMarInstallAndroid' => $this->chartMarInstall('Android'),));
		if (in_array('iOS', $listPlatform))
			$this->listChart = array_merge($this->listChart, array('chartPortalMarInstalliOS' => $this->chartMarInstall('iOS'),));
		
		$this->addViewsToMain=json_encode(
			$this->addCharts(array_keys($this->listChart)).
			$this->addGrid('', 'gridAll', $this->gridAll(), false).
			''
		);
		
		$this->pageInfo=
		"<p>
			<br>Diễn giải:
			<br> - ZPSInstall = InstallDeviceCount : ghi nhận Install nếu device lần đầu install (re-install không được tính)
			<br> - MarAppInstall30 = Marketing Install 30 / App : 30 ngày qua device không xuất hiện Install ở bất cứ Distributor nào của App thì được tính
			<br> - MarDisInstall30 = Marketing Install 30 / Distributor : 30 ngày qua device không xuất hiện Install ở Distributor này thì được tính
			<br> - InstallUnique <= MarAppInstall30 <= MarDisInstall30
		</p>";
		return parent::__index($this->listApp, $type, 
		['listDistributor'=> $this->listDistributor,
		'selectedDistributor' => $this->selectedDistributor,
		'field2Title'=>'Distributor',
		], 'pages.Common2SField');
	}

	function chartMarInstall ($Platform, $bgcolor='White') {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, InstallDeviceCount ZPSInstall, MarAppInstall30, MarAppInstall15, MarAppInstall7, MarDisInstall30, MarDisInstall15, MarDisInstall7
				from dailydistributor 
				where AppName = ".$this->quote($this->AppName)." and Distributor = ".$this->quote($this->selectedDistributor)." and Platform=".$this->quote($Platform)." 
					and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."";
		$dataopt = ['type' => 'line',
					'visible' => ['ZPSInstall','MarAppInstall30','MarDisInstall30'],
					];
		$options = [	'title' => 'Marketing Install - '.$Platform,
						'subtitle' => $this->AppName.' , '.$this->selectedDistributor,
						'chart_backgroundColor' => $bgcolor,
						'yAxis_title' => '#device',
					];
		
		return $this->chartLine($sql, $options, $dataopt);
	}

	function gridAll () {
		$sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, Platform, InstallDeviceCount ZPSInstall, MarAppInstall30, MarAppInstall15, MarAppInstall7, MarDisInstall30, MarDisInstall15, MarDisInstall7
				from dailydistributor 
				where AppName = ".$this->quote($this->AppName)." and Distributor = ".$this->quote($this->selectedDistributor)." 
					and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc, Platform";

		$option = [ 'tableid' => 'tableid_' . 'gridAll', // "gridAll" must match $this->addGrid
					'align' => ['AppName'=>'left'],
					];
		
		return $this->createGrid2020($sql, $option);
	}
	
}
