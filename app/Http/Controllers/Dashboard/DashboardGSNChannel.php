<?php
namespace App\Http\Controllers\Dashboard;
use Request, View;

class DashboardGSNChannel extends Dashboard2018 {
	
	public function index ($type='') {

		// ------------------ ajax ----------------------
		if (Request::input('sync') == '1') {  // GameCode, MainNumber, Percent, LastChange
			echo $this->jsonData();
			exit;
		}
		
		// log access (only write only one log per day)
		$this->writelogaccess_oneperday();
		
		$this->pagetitle = 'Channel';
		return View::make('pages.DashboardGSN',array(
					'ajaxURL' => '/gsnreport/dashboardgsnchannel?sync=1',
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
		$sql = $this->sqlListDashboardGSN('Channel');
		$data = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		
		$this->fillPercent($data);
		return $data;
	}
	
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	function sqlListDashboardGSN ($fieldname) {
		$sql = "SELECT distinct left(COLUMN_NAME, LOCATE('_',COLUMN_NAME)-1) ColName FROM information_schema.COLUMNS 
				WHERE table_schema='newdashboard' AND TABLE_NAME='dashboard' AND COLUMN_NAME LIKE 'Revenue%' AND COLUMN_NAME not LIKE 'RevenueTotal%' AND COLUMN_NAME not LIKE 'Revenue\_%'";
		$data = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		
		$sql = '';
		foreach ($data as $row)
			$sql .= " union select '".str_replace('Revenue','',$row['ColName'])."' GameCode, ".$row['ColName']."_Today MainNumber, ".$row['ColName']."_PreviousDay PreviousDay, 0 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.".$row['ColName']."_Last is null then '' else concat('Last ',date_format(".$row['ColName']."_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='GSN' and (".$row['ColName']."_Today <> 0 or ".$row['ColName']."_PreviousDay <> 0) ";
		
		$sql = ltrim($sql, 'union ');
		return $sql;
	}
	
	function sqlHourly ($gamecode, $fieldname, $istoday) {
		if ($istoday)
			$sql = "select Hour, Revenue".$gamecode." Channel from revenuehourly where GameCode='GSN' and ReportDate=curdate() order by Hour";
		else
			$sql = "select Hour, Revenue".$gamecode." Channel from revenuehourly where GameCode='GSN' and ReportDate=curdate()-interval 1 day order by Hour";
		
		return $sql;
	}
	
	function jsonWidgets () {
		$html = '';
		
		$maxWidgetPerRow = $this->maxWidgetPerRow() - 1;
		$currentRow = 1;
		$currentCol = 1;
		
		// configured list
		$data = $this->_prepairData();
		$html .= $this->showWidgets($data, 'Channel', $maxWidgetPerRow, $currentRow, $currentCol);
		
		return $html;
	}
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	
	
}
