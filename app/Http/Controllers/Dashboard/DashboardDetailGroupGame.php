<?php
namespace App\Http\Controllers\Dashboard;
use Request, View;

class DashboardDetailGroupGame extends Dashboard2018 {
	
	public function index ($type='') {
		
		// check game code
		$this->listAllow101();

		
		$this->GameCode = str_replace("'","''",Request::input('gc'));
		if ($this->checkGameCodeExist($this->GameCode) && $this->checkAllowGameCode($this->GameCode)) {
			$this->pagetitle = $this->GameCode;
			$this->countPlatform($this->pagetitle);

		// ------------------ ajax ----------------------
		// if (Request::input('sync') == '1') {  // GameCode, MainNumber, Percent, LastChange
		// 	echo $this->jsonData();
		// 	exit;
		// }
		
		// // log access (only write only one log per day)
		// $this->writelogaccess_oneperday();
		
		// if ($this->IsAdmin == 1)
		// 	$viewfile = 'DashboardGSN';
		// else
		// 	$viewfile = 'DashboardGSN_NotFull';
		
		// return View::make('pages.'.$viewfile,array(
		// 			'ajaxURL' => '/gsnreport/dashboarddetailgroupgame?sync=1&gc='.$this->GameCode,
		// 			'pagetitle' => $this->pagetitle,
					
		// 			'widgets' => $this->jsonWidgets(),
		// 			'widthWidget' => $this->constWidgetX,
		// 			'isMobile' => $_COOKIE[self::constCookieIsMobileName],
					
		// 		));
			return response()->json(
				[
					'status' => 200, 
					'data' => [
						'pageTitle' => $this->pagetitle,
						'widgets' => $this->jsonWidgets(),
					]
				]);
		}

		return response()->json(['data' => [], 'status' => 200]);
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

		$sql = $this->sqlGroupGameDetail();
		$data2 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		$this->createLink($data2,'dashboarddetailgame');
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
	function sqlGroupGameDetail() {
		$sql = "SELECT concat('RevenueTotal',d.GameCode) GameCode, RevenueTotal_Today MainNumber, RevenueTotal_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.RevenueTotal_Last is null then '' else concat('Last ',date_format(RevenueTotal_Last, '%H:%i')) end) LastChange 
			FROM newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode  
			WHERE d.GameCode IN (SELECT distinct ToAppName FROM GSNConfig.AppName WHERE GameGroup='".$this->GameCode."' ) and (RevenueTotal_Today<>0 or RevenueTotal_PreviousDay<>0) order by RevenueTotal_Today desc ";
		return $sql;
	}
	
	function sqlHourly ($gamecode, $fieldname, $istoday) {
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
		elseif (substr($gamecode,0,12) == 'RevenueTotal') { // game group
			$gamecode = str_replace('RevenueTotal','',$gamecode);
			if ($istoday)
				$sql = "select Hour, RevenueTotal GameDetail from revenuehourly where GameCode='".$gamecode."' and ReportDate=curdate() order by Hour";
			else
				$sql = "select Hour, RevenueTotal GameDetail from revenuehourly where GameCode='".$gamecode."' and ReportDate=curdate()-interval 1 day order by Hour";
		}
		else // channel
			if ($istoday)
				$sql = "select Hour, Revenue".$gamecode." GameDetail from revenuehourly where GameCode='".$this->GameCode."' and ReportDate=curdate() order by Hour";
			else
				$sql = "select Hour, Revenue".$gamecode." GameDetail from revenuehourly where GameCode='".$this->GameCode."' and ReportDate=curdate()-interval 1 day order by Hour";
		
		return $sql;
	}
	
	function jsonWidgets () {
		$maxWidgetPerRow = 1;
		$currentRow = 1;
		$currentCol = 1;
		
		// configured list
		$data = $this->_prepairData();
		$this->showWidgets($data, 'GameDetail', $maxWidgetPerRow, $currentRow, $currentCol);
		
		return $data;
	}
	// ---------------- -------------------- newdashboard.dashboard function -----------------------
	
	
}
