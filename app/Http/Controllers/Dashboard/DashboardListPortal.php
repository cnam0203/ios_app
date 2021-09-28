<?php
namespace App\Http\Controllers\Dashboard;
use Request, View;

class DashboardListPortal extends Dashboard2018 {
	
	public function index ($type='') {
		
		$this->GameCode = 'ZPPortal_all';
		$this->checkGameCodeExist($this->GameCode);
		$this->pagetitle = $this->GameCode;
		$this->countPlatform($this->pagetitle);

		// ------------------ ajax ----------------------
		if (Request::input('sync') == '1') {  // GameCode, MainNumber, Percent, LastChange
			echo $this->jsonData();
			exit;
		}
		
		// log access (only write only one log per day)
		$this->writelogaccess_oneperday();
		
		return View::make('pages.DashboardGSN',array(
					'ajaxURL' => '/gsnreport/dashboardlistportal?sync=1',
					'pagetitle' => $this->pagetitle,
					
					'widgets' => $this->jsonWidgets(),
					'widthWidget' => $this->constWidgetX,
					'isMobile' => $_COOKIE[self::constCookieIsMobileName],
					
				));
	}
	

	// ---------------- -------------------- ajax service -----------------------
	function jsonData() {
		$data = $this->_prepairData();		
		return json_encode($data);
	}
	
	function _prepairData () {
		$sql = $this->sqlRevenueTotal();
		$data = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		
		$sql = $this->sqlPortal('RevenueTotal');
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data2[0]['LineFeed'] = 1;
			$data = array_merge($data,$data2);
		}
		
		/*$sql = $this->sqlPortal('PUTotal');
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data = array_merge($data,$data2);
		}*/
		
		$sql = $this->sqlPortal('A1Total');
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data = array_merge($data,$data2);
		}
		
		$sql = $this->sqlPortal('N1Total');
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data = array_merge($data,$data2);
		}
		
		$this->fillPercent($data);
		return $data;
	}
	
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	function sqlPortal($Prefix) {
		$sql = "SELECT concat('".$Prefix."','_',d.GameCode) GameCode, ".$Prefix."_Today MainNumber, ".$Prefix."_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NOCHART' ChartType, (case when l.".$Prefix."_Last is null then '' else concat('Last ',date_format(".$Prefix."_Last, '%H:%i')) end) LastChange 
			FROM newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode  
			WHERE d.GameCode IN (SELECT distinct ToAppName FROM GSNConfig.AppName WHERE Country='Portal')  and (RevenueTotal_Today<>0 or RevenueTotal_Today<>0)";
		return $sql;
	}
	
	function jsonWidgets () {
		$html = '';
		
		$maxWidgetPerRow = $this->maxWidgetPerRow();
		$currentRow = 1;
		$currentCol = 1;
		
		// configured list
		$data = $this->_prepairData();
		$html .= $this->showWidgets($data, 'RevenueTotal', $maxWidgetPerRow, $currentRow, $currentCol);
		
		return $html;
	}
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	
	
}
