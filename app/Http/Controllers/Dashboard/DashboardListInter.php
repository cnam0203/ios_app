<?php
namespace App\Http\Controllers\Dashboard;
use Request, View;

class DashboardListInter extends Dashboard2018 {
	
	public function index ($type='') {
		
		$this->GameCode = 'ZPInter';
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
					'ajaxURL' => '/gsnreport/dashboardlistinter?sync=1',
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
		
		$sql = $this->sqlCCU();
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		$data = array_merge($data,$data2);

		$sql = $this->sqlCountry();
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		$this->createLink($data2,'dashboarddetailcountry');
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data2[0]['LineFeed'] = 1;
			$data = array_merge($data,$data2);
		}
		
		$sql = $this->sqlGroupGame();
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		$this->createLink($data2,'dashboarddetailgroupgame');
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data2[0]['LineFeed'] = 1;
			$data = array_merge($data,$data2);
		}
		
		$sql = $this->sqlRev();
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data2[0]['LineFeed'] = 1;
			$data = array_merge($data,$data2);
		}
		
		$sql = $this->sqlPU();
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data = array_merge($data,$data2);
		}
		
		$sql = $this->sqlA1();
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data = array_merge($data,$data2);
		}
		
		$sql = $this->sqlN1();
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (count($data2) > 0) {
			$data2[0]['IsSameGroupWithPrecede'] = 0;
			$data = array_merge($data,$data2);
		}
		
		$this->fillPercent($data);
		return $data;
	}
	
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	function sqlCountry() {
		$sql = "SELECT d.GameCode, RevenueTotal_Today MainNumber, RevenueTotal_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.RevenueTotal_Last is null then '' else concat('Last ',date_format(RevenueTotal_Last, '%H:%i')) end) LastChange 
		FROM newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
		WHERE d.GameCode IN (SELECT distinct Country FROM GSNConfig.AppName WHERE Country IS NOT NULL AND Country<>'' AND Country NOT IN ('Viet','Portal'))  and (RevenueTotal_Today<>0 or RevenueTotal_Today<>0)";
		return $sql;
	}
	
	function sqlGroupGame() {
		$sql = "SELECT d.GameCode, RevenueTotal_Today MainNumber, RevenueTotal_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.RevenueTotal_Last is null then '' else concat('Last ',date_format(RevenueTotal_Last, '%H:%i')) end) LastChange 
		FROM newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
		WHERE d.GameCode IN (SELECT distinct GameGroup FROM GSNConfig.AppName WHERE Country IS NOT NULL AND Country<>'' AND Country NOT IN ('Viet','Portal') AND GameGroup<>'' AND GameGroup NOT IN ('Viet','Portal'))  and (RevenueTotal_Today<>0 or RevenueTotal_Today<>0) order by RevenueTotal_Today desc";
		return $sql;
	}
	
	function sqlHourly ($gamecode, $fieldname, $istoday) { // overwrite
		if ($gamecode == 'RevenueTotal' || substr($gamecode,0,8) == 'Revenue_' || substr($gamecode,0,2) == 'PU') // rev, PU
			if ($istoday)
				$sql = "select Hour, ".$gamecode." GameDetail from revenuehourly where GameCode='".$this->GameCode."' and ReportDate=curdate() order by Hour";
			else
				$sql = "select Hour, ".$gamecode." GameDetail from revenuehourly where GameCode='".$this->GameCode."' and ReportDate=curdate()-interval 1 day order by Hour";
		elseif (substr($gamecode,0,2) == 'A1' || substr($gamecode,0,2) == 'N1') // A1, N1
			if ($istoday)
				$sql = "select Hour, ".$gamecode." GameDetail from A1hourly where GameCode='".$this->GameCode."' and ReportDate=curdate() order by Hour";
			else
				$sql = "select Hour, ".$gamecode." GameDetail from A1hourly where GameCode='".$this->GameCode."' and ReportDate=curdate()-interval 1 day order by Hour";
		elseif ($gamecode == 'CCU') // ccu
			if ($istoday)
				$sql = "select ReportTime Hour, ".$gamecode." GameDetail from CCU where GameCode='".$this->GameCode."' and ReportDate=curdate() order by ReportTime";
			else
				$sql = "select ReportTime Hour, ".$gamecode." GameDetail from CCU where GameCode='".$this->GameCode."' and ReportDate=curdate()-interval 1 day order by ReportTime";
		else // country / gamegroup
			if ($istoday)
				$sql = "select Hour, RevenueTotal GameDetail from revenuehourly where GameCode='".$gamecode."' and ReportDate=curdate() order by Hour";
			else
				$sql = "select Hour, RevenueTotal GameDetail from revenuehourly where GameCode='".$gamecode."' and ReportDate=curdate()-interval 1 day order by Hour";
		
		return $sql;
	}
	
	function jsonWidgets () {
		$html = '';
		
		$maxWidgetPerRow = $this->maxWidgetPerRow();
		$currentRow = 1;
		$currentCol = 1;
		
		// configured list
		$data = $this->_prepairData();
		$html .= $this->showWidgets($data, 'GameDetail', $maxWidgetPerRow, $currentRow, $currentCol);
		
		return $html;
	}
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	
	
}
