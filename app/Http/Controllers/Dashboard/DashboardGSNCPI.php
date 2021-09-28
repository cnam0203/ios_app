<?php
namespace App\Http\Controllers\Dashboard;
use Request, View;
class DashboardGSNCPI extends Dashboard2018 {
	
	public function index ($type='') {
		// ------------------ ajax ----------------------
		if (Request::input('sync') == '1') {  // GameCode, MainNumber, Percent, LastChange
			echo $this->jsonData();
			exit;
		}
		
		// log access (only write only one log per day)
		$this->writelogaccess_oneperday();
		
		$viewfile = 'DashboardGSN';
		
		$this->pagetitle = 'CPI';
		return View::make('pages.'.$viewfile,array(
					'ajaxURL' => '/gsnreport/dashboardgsncpi?sync=1',
					'pagetitle' => $this->pagetitle,
					
					'widgets' => $this->jsonWidgets(),
					'widthWidget' => $this->constWidgetX,
					'isMobile' => $_COOKIE[self::constCookieIsMobileName],
					
				));
	}
	

	function sqlHourly ($gamecode, $fieldname, $istoday) {
		if (in_array($fieldname, array('A1Total','N1Total')))
			if ($istoday)
				$sql = "select Hour, ".$fieldname." from A1hourly where GameCode=".$this->quote($gamecode)." and ReportDate=curdate() order by Hour";
			else
				$sql = "select Hour, ".$fieldname." from A1hourly where GameCode=".$this->quote($gamecode)." and ReportDate=curdate()-interval 1 day order by Hour";
		elseif (in_array($fieldname, array('CCU')))
			if ($istoday)
				$sql = "select ReportTime Hour, ".$fieldname." from CCU where GameCode=".$this->quote($gamecode)." and ReportDate=curdate() order by ReportTime";
			else
				$sql = "select ReportTime Hour, ".$fieldname." from CCU where GameCode=".$this->quote($gamecode)." and ReportDate=curdate()-interval 1 day order by ReportTime";
		elseif (in_array($fieldname, array('CPI')))
			if ($istoday)
				$sql = "select date_format(ReportTime,'%H:%i') Hour, InstallCost/Install ".$fieldname." from newdashboard.dashboard_cpi_chart where GameCode=".$this->quote(substr($gamecode,0,strlen($gamecode)-3))." and ReportTime>=curdate()+interval 2 hour order by ReportTime";
			else
				$sql = "select date_format(ReportTime,'%H:%i') Hour, InstallCost/Install ".$fieldname." from newdashboard.dashboard_cpi_chart where GameCode=".$this->quote(substr($gamecode,0,strlen($gamecode)-3))." and ReportTime>=curdate()-interval 22 hour and ReportTime<curdate() order by ReportTime";
		elseif (in_array($fieldname, array('CPIgg')))
			if ($istoday)
				$sql = "select date_format(ReportTime,'%H:%i') Hour, InstallCost/Install ".$fieldname." from newdashboard.dashboard_cpigg_chart where GameCode=".$this->quote(substr($gamecode,0,strlen($gamecode)-3))." and ReportTime>=curdate()+interval 2 hour order by ReportTime";
			else
				$sql = "select date_format(ReportTime,'%H:%i') Hour, InstallCost/Install ".$fieldname." from newdashboard.dashboard_cpigg_chart where GameCode=".$this->quote(substr($gamecode,0,strlen($gamecode)-3))." and ReportTime>=curdate()-interval 22 hour and ReportTime<curdate() order by ReportTime";
		else
			if ($istoday)
				$sql = "select Hour, ".$fieldname." from revenuehourly where GameCode=".$this->quote($gamecode)." and ReportDate=curdate() order by Hour";
			else
				$sql = "select Hour, ".$fieldname." from revenuehourly where GameCode=".$this->quote($gamecode)." and ReportDate=curdate()-interval 1 day order by Hour";
		
		return $sql;
	}
	

	// ---------------- -------------------- ajax service -----------------------
	function jsonData() {
		$data = $this->_prepairData();		
		return json_encode($data);
	}
	
	function _prepairData () {
		$sql = $this->sqlListDashboardCPI();
		$data = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		
		$this->fillPercent($data);
		return $data;
	}
	
	function sqlListDashboardCPI () {

		$sql10 = $this->sqlListDashboardCPI_fb_template('ph', 10);
		$sql11 = $this->sqlListDashboardCPI_gg_template('ph', 11);

		$sql20 = $this->sqlListDashboardCPI_fb_template('mm', 20);
		$sql21 = $this->sqlListDashboardCPI_gg_template('mm', 21);

		$sql30 = $this->sqlListDashboardCPI_fb_template('br', 30);
		$sql31 = $this->sqlListDashboardCPI_gg_template('br', 31);

		$sql40 = $this->sqlListDashboardCPI_fb_template('mx', 40);
		$sql41 = $this->sqlListDashboardCPI_gg_template('mx', 41);

		$sql50 = $this->sqlListDashboardCPI_fb_template('th', 50);
		$sql51 = $this->sqlListDashboardCPI_gg_template('th', 51);
		
		// others
		$sql90 = $this->sqlListDashboardCPI_fb_template(['ph','mm','br','mx','th'], 90);
		$sql91 = $this->sqlListDashboardCPI_gg_template(['ph','mm','br','mx','th'], 91);
		
		$sql = $sql10 ." union ". $sql11 .
				" union ". $sql20 ." union ". $sql21 .
				" union ". $sql30 ." union ". $sql31 .
				" union ". $sql40 ." union ". $sql41 .
				" union ". $sql50 ." union ". $sql51 .
				" union ". $sql90 ." union ". $sql91 .
				" order by SortOrder, MainNumber2 desc";
		return $sql;
	}
	function sqlListDashboardCPI_fb_template ($cc, $order) {
		if (!is_array($cc))
			$sql = "select concat(GameCode,'_fb') GameCode, round(InstallCost_Today/Install_Today,2) MainNumber, round(InstallCost_PreviousDay/Install_PreviousDay,2) PreviousDay, 0 IsSameGroupWithPrecede, 'NORMAL' ChartType, date_format(ReportTime,'%H:%i') LastChange, Install_Today MainNumber2, Install_PreviousDay PreviousDay2, ".$order." SortOrder from newdashboard.dashboard_cpi where hour(ReportTime) > 1 and GameCode like '%\_".$cc."'";
		else {
			$notlike = '';
			foreach ($cc as $v)
				$notlike .= " and GameCode not like '%\_".$v."'";
			$sql = "select concat(GameCode,'_fb') GameCode, round(InstallCost_Today/Install_Today,2) MainNumber, round(InstallCost_PreviousDay/Install_PreviousDay,2) PreviousDay, 0 IsSameGroupWithPrecede, 'NORMAL' ChartType, date_format(ReportTime,'%H:%i') LastChange, Install_Today MainNumber2, Install_PreviousDay PreviousDay2, ".$order." SortOrder from newdashboard.dashboard_cpi where hour(ReportTime) > 1 ".$notlike;
		}
		return $sql;
	}
	function sqlListDashboardCPI_gg_template ($cc, $order) {
		if (!is_array($cc))
			$sql = "select concat(GameCode,'_gg') GameCode, round(InstallCost_Today/Install_Today,2) MainNumber, round(InstallCost_PreviousDay/Install_PreviousDay,2) PreviousDay, 0 IsSameGroupWithPrecede, 'NORMAL' ChartType, date_format(ReportTime,'%H:%i') LastChange, Install_Today MainNumber2, Install_PreviousDay PreviousDay2, ".$order." SortOrder from newdashboard.dashboard_cpigg where hour(ReportTime) > 1 and GameCode like '%\_".$cc."'";
		else {
			$notlike = '';
			foreach ($cc as $v)
				$notlike .= " and GameCode not like '%\_".$v."'";
			$sql = "select concat(GameCode,'_gg') GameCode, round(InstallCost_Today/Install_Today,2) MainNumber, round(InstallCost_PreviousDay/Install_PreviousDay,2) PreviousDay, 0 IsSameGroupWithPrecede, 'NORMAL' ChartType, date_format(ReportTime,'%H:%i') LastChange, Install_Today MainNumber2, Install_PreviousDay PreviousDay2, ".$order." SortOrder from newdashboard.dashboard_cpigg where hour(ReportTime) > 1 ".$notlike;
		}
		return $sql;
	}
	
	function fillPercent (&$data) {
		foreach ($data as $key=>$row) {
			if ($row['PreviousDay'] == 0)
				$pct = 0;
			else
				$pct = round(($row['MainNumber']/$row['PreviousDay']-1)*100);
			$data[$key]['Percent'] = $pct;
			
			if (isset($row['PreviousDay2'])) {
				if ($row['PreviousDay2'] == 0)
					$pct = 0;
				else
					$pct = round(($row['MainNumber2']/$row['PreviousDay2']-1)*100);
				$data[$key]['Percent2'] = $pct;
			}
		}
	}
	
	// ---------------- -------------------- dashboard function -----------------------
	function jsonWidgets () {
		$html = '';
		
		$maxWidgetPerRow = $this->maxWidgetPerRow();
		$currentRow = 1;
		$currentCol = 1;
		
		// configured list
		$data = $this->_prepairData();
		$html .= $this->showWidgets2D($data, 'CPI', $maxWidgetPerRow, $currentRow, $currentCol);
		
		return $html;
	}
	
	function showWidgets2D($listwidgetdata, $fieldname, $maxWidgetPerRow, &$currentRow, &$currentCol) {
		$html = '';
		$groupcpi=1;
		for ($datarow=0; $datarow<count($listwidgetdata); $datarow++) {
			$LineFeeded = 0;
			$row = $listwidgetdata[$datarow];
			if ($row['Percent'] >= 2)			$color='#F00';
			else if ($row['Percent'] <= -2)	$color='#3F3';
			else 					$color='#FFF';
			if ($row['Percent2'] >= 2)			$color2='#3F3';
			else if ($row['Percent2'] <= -2)	$color2='#F00';
			else 					$color2='#FFF';
			
			$gamecodeoverwrite = $row['GameCode'];
			//$fieldnameoverwrite = $fieldname;
			if ($row['SortOrder'] == 1)
				$fieldnameoverwrite = 'CPI';
			else
				$fieldnameoverwrite = 'CPIgg';
			$this->specialWidgetOverwriteField($gamecodeoverwrite, $fieldnameoverwrite);
			
			// change percent
			$classpct = 'pChangePctNornal';
			if ($row['Percent'] >= 10)
				$classpct = 'pChangePctGreenBig';
			else if ($row['Percent'] > 2)
				$classpct = 'pChangePctGreen';
			else if ($row['Percent'] <= -10)
				$classpct = 'pChangePctRedBig';
			else if ($row['Percent'] < -2)
				$classpct = 'pChangePctRed';
			
			// chart type
			$classpostfix = '';
			$sizeX = 1;
			if ($row['ChartType'] == 'MAIN') {
				$classpostfix = 'MAIN';
				$sizeX = 2;
			}
			
			# bgcolor
			if ($row['IsSameGroupWithPrecede'] == 1)
				$bgcolor = self::constWidgetBGColor[$this->currentBGColorIndex];
			else {
				$this->currentBGColorIndex++;
				if ($this->currentBGColorIndex == count(self::constWidgetBGColor))
					$this->currentBGColorIndex = 0;
				$bgcolor = self::constWidgetBGColor[$this->currentBGColorIndex];
			}
			if ($row['ChartType'] == 'MAIN')
				$bgcolor = '#ff9618';
			
			// change text
			$WidgetName = $row['GameCode'];
			$WidgetName = str_replace('RevenueTotal','',$WidgetName);
			if ($WidgetName == 'Revenue_Web')
				$WidgetName = 'Rev Undefined';
			if ($row['ChartType'] == 'MAIN')
				$WidgetName .= ' '.$this->pagetitle;
			
			# link
			$link = " style=\"background-color: ".$bgcolor."\"";
			if ($row['link'] != '')
				$link = "onclick=\"location.href=\\'".$row['link']."\\'\" style=\"cursor: pointer;background-color: ".$bgcolor."\"";
			
			$h = '<div id="gc'.$row['GameCode'].'" class="divWidget'.$classpostfix.'" '.$link.'>';
			$h .= '<span class="pTitle'.$classpostfix.'" data-bind="title">'.$WidgetName.'</span>';
			if ($row['ChartType'] != 'NOCHART')
				$h .= '<span class="'.$classpct.''.$classpostfix.'"><font id="pct'.$row['GameCode'].'" color='.$color.'>'.$row['Percent'].'%</font></span>';
			$h .= '<span class="h3MainNumber"><font id="main'.$row['GameCode'].'" style="font-weight:bold">'.number_format($row['MainNumber'],2,'.',',').'</font> <font style="font-size:0.7em">(<font id="main2'.$row['GameCode'].'">'.number_format($row['MainNumber2'],0,'.',',').'</font> <font style="font-size:0.9em" id="pct2'.$row['GameCode'].'" color='.$color2.'>'.$row['Percent2'].'%</font>)</font></span>';
			$h .= '<span id="last'.$row['GameCode'].'" class="spanLastTime'.$classpostfix.'">Last</span>';
			if ($row['ChartType'] != 'NOCHART') {
				$values = $this->getDataHourly($gamecodeoverwrite, $fieldnameoverwrite);
				//print_r($values);
				$h .= '<div class="svgChart">'.rtrim($this->createSVGSmallDashboard($values, $row['ChartType'], $bgcolor), "</div>\r\n");
			}
			$h .= '</div>';
			
			if ($maxWidgetPerRow > 4 && $row['IsSameGroupWithPrecede'] == 0) { // not same group
				// count this group
				$countgroup = 1;
				for ($j=$datarow+1; $j<count($listwidgetdata); $j++)
					if ($listwidgetdata[$j]['IsSameGroupWithPrecede'] == 0)
						break;
					else
						$countgroup++;
				if ($currentCol + $countgroup -1 > $maxWidgetPerRow) {
					while ($currentCol <= $maxWidgetPerRow) {
						$html .= "['<li></li>', 1, 1, ".$currentCol.", ".$currentRow."],";
						$currentCol++;
					}
					$currentRow++;
					$currentCol = 1;
					$LineFeeded = 1;
				}
			}
			if ($currentCol + $sizeX -1 > $maxWidgetPerRow) {
				$currentRow++;
				$currentCol = 1;
				$LineFeeded = 1;
			}
			if ($row['LineFeed'] == 1 && $LineFeeded == 0
				|| $groupcpi != $row['SortOrder']) { // line feed
				
				while ($currentCol <= $maxWidgetPerRow) {
					$html .= "['<li></li>', 1, 1, ".$currentCol.", ".$currentRow."],";
					$currentCol++;
				}
				$currentRow++;
				$currentCol = 1;
				$LineFeeded = 1;
				if ($groupcpi != $row['SortOrder']) {
					$groupcpi = $row['SortOrder'];
					while ($currentCol <= $maxWidgetPerRow) {
						$html .= "['<li></li>', 1, 1, ".$currentCol.", ".$currentRow."],";
						$currentCol++;
					}
					$currentRow++;
					$currentCol = 1;
				}
			}
			$html .= "['<li>".$h."</li>', ".$sizeX.", 1, ".$currentCol.", ".$currentRow."],";
			$currentCol = $currentCol + $sizeX;
		}
		return $html;
	}
	// ---------------- -------------------- dashboard function -----------------------
	
	
}
