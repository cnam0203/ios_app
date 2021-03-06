<?php
namespace App\Http\Controllers\Dashboard;
use Request, View;

class DashboardGSN extends Dashboard2018 {
	
	public function index ($type='') {
		$this->listAllow101();

		// ------------------ ajax ----------------------
		// if (Request::input('sync') == '1') {  // GameCode, MainNumber, Percent, LastChange
		// 	echo $this->jsonData();
		// 	exit;
		// }
		
		// log access (only write only one log per day)
		// $this->writelogaccess_oneperday();
		
		// if ($this->IsAdmin == 1)
		// 	$viewfile = 'DashboardGSN';
		// else
		// 	$viewfile = 'DashboardGSN_NotFull';
		
		$this->pagetitle = 'Revenue';

		// return View::make('pages.'.$viewfile,array(
		// 			'ajaxURL' => '/gsnreport/dashboardgsn?sync=1',
		// 			'pagetitle' => $this->pagetitle,
					
		// 			'widgets' => $this->jsonWidgets(),
		// 			'widthWidget' => $this->constWidgetX,
		// 			'isMobile' => $_COOKIE[self::constCookieIsMobileName],
		// 		));
		return response()->json(
			[
				'status' => true,
				'data' => [
					'pageTitle' => $this->pagetitle,
					'widgets' => $this->jsonWidgets(),
					'screenType' => 'svg',
				]
			]);
	}
	

	// ---------------- -------------------- ajax service -----------------------
	function jsonData() {
		$data = $this->_prepairData();
		return json_encode($data);
	}
	
	function _prepairData () {
		if ($this->IsAdmin == 1)
			$sql = $this->sqlListDashboardGSN('RevenueTotal');
		else
			$sql = $this->sqlListDashboardGSN_notfull('RevenueTotal');
		$data = $this->getDataPDOSQL($this->pdoReportTool, $sql);
		
		$this->autoLink($data);
		$this->fillPercent($data);
		return $data;
	}
	
	function _prepairDataNotList () {
		$sql = $this->sqlNotListDashboardGSN('RevenueTotal');
		$data = $this->getDataPDOSQL($this->pdoReportTool, $sql);
		
		$this->autoLink($data);
		$this->fillPercent($data);
		return $data;
	}
	
	
	// ---------------- -------------------- newdashboard.dashboard function (admin) -----------------------
	function specialWidgetOverwriteField (&$gamecode, &$fieldname) {  // overwrite if you have a special widget
		if ($gamecode == 'IAP') {
			$fieldname = 'RevenueIAP';
			$gamecode = 'GSN';
		}
	}
	
	function jsonWidgets () {
		if ($this->IsAdmin != 1) {
			return $this->jsonWidgets_notfull();
			exit;
		}
		// $html = '';
		
		// $maxWidgetPerRow = $this->maxWidgetPerRow();
		// $currentRow = 1;
		// $currentCol = 1;
		
		// // configured list
		// $data = $this->_prepairData();
		// $html .= $this->showWidgets($data, 'RevenueTotal', $maxWidgetPerRow, $currentRow, $currentCol);
		
		// // add an empty row
		// $currentRow++;
		// $currentCol = 1;
		// $html .= "['<li></li>', ".$maxWidgetPerRow.", 1, ".$currentCol.", ".$currentRow."],";
		// $currentRow++;
		
		// // not configured list
		// $data = $this->_prepairDataNotList();
		// $html .= $this->showWidgets($data, 'RevenueTotal', $maxWidgetPerRow, $currentRow, $currentCol);
		
		// return $html;
	}
	// ---------------- -------------------- newdashboard.dashboard function (admin) -----------------------
	
	
	// ---------------- -------------------- newdashboard.dashboard function (notfullapp) -----------------------
	function jsonWidgets_notfull () {
		$maxWidgetPerRow = 1;
		$currentRow = 1;
		$currentCol = 1;
		
		// configured list
		$data = $this->_prepairData();
		$this->showWidgets($data, 'RevenueTotal', $maxWidgetPerRow, $currentRow, $currentCol); 
		
		return $data;
	}
	// ---------------- -------------------- newdashboard.dashboard function (notfullapp) -----------------------
}
