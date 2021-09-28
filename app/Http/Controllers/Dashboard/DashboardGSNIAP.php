<?php
namespace App\Http\Controllers\Dashboard;
use Request, View;

class DashboardGSNIAP extends Dashboard2018 {
	
	public function index ($type='') {

		// ------------------ ajax ----------------------
		if (Request::input('sync') == '1') {  // GameCode, MainNumber, Percent, LastChange
			echo $this->jsonData();
			exit;
		}
		
		// log access (only write only one log per day)
		$this->writelogaccess_oneperday();
		
		$this->pagetitle = 'IAP';
		return View::make('pages.DashboardGSN',array(
					'ajaxURL' => '/gsnreport/dashboardgsniap?sync=1',
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
		$sql = $this->sqlListDashboardGSN('RevenueIAP');
		$data = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		
		$this->autoLink($data);
		$this->fillPercent($data);
		return $data;
	}
	
	function _prepairDataNotList () {
		$sql = $this->sqlNotListDashboardGSN('RevenueIAP');
		$data = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		
		$this->autoLink($data);
		$this->fillPercent($data);
		return $data;
	}
	
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	function jsonWidgets () {
		$html = '';
		
		$maxWidgetPerRow = $this->maxWidgetPerRow();
		$currentRow = 1;
		$currentCol = 1;
		
		// configured list
		$data = $this->_prepairData();
		$html .= $this->showWidgets($data, 'RevenueIAP', $maxWidgetPerRow, $currentRow, $currentCol);
		
		// add an empty row
		$currentRow++;
		$currentCol = 1;
		$html .= "['<li></li>', ".$maxWidgetPerRow.", 1, ".$currentCol.", ".$currentRow."],";
		$currentRow++;
		
		// not configured list
		$data = $this->_prepairDataNotList();
		$html .= $this->showWidgets($data, 'RevenueIAP', $maxWidgetPerRow, $currentRow, $currentCol);
		
		return $html;
	}
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	
	
}
