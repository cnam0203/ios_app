<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Base2018, SVGGraph;
	
class Dashboard2018 extends Base2018 {
	const constWidgetBGColor = ['#47bbb3', '#77bbb3', '#47aab3'];
	const constCookieScreenSizeName = 'ScreenSize';
	const constCookieIsMobileName = 'IsMobile';
	var $constWidgetX = 200;
	const constWidgetMargin = 10;
	var $currentBGColorIndex = 0;
	var $CountPlatform = array('iOS'=>0, 'Android'=>0, 'Web'=>0, 'Undefined'=>0);
	var $GameCode = '';
	
	// ---------------------- SQL for newdashboard.dashboard GSN --------------------------
	function sqlListDashboardGSN ($fieldname) {
		$sql = "select d.GameCode, d.".$fieldname."_Today MainNumber, d.".$fieldname."_PreviousDay PreviousDay, c.IsSameGroupWithPrecede, c.ChartType,
						(case when l.".$fieldname."_Last is null then '' else concat('Last ',date_format(".$fieldname."_Last, '%H:%i')) end) LastChange, Priority
					from newdashboard.dashboard d join GSNConfig.dashboard c on d.GameCode collate utf8_general_ci = c.GameCode
						left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
					where (d.".$fieldname."_Today <> 0 or d.".$fieldname."_PreviousDay <> 0)";
		
		if ($fieldname == 'RevenueTotal') // note: use RevenueTotal_Last (no need RevenueIAP_Last ?)
			$sql .= "	union
				SELECT 'IAP', RevenueIAP_Today, RevenueIAP_PreviousDay, c.IsSameGroupWithPrecede, c.ChartType,
						(case when l.RevenueIAP_Last is null then '' else concat('Last ',date_format(RevenueIAP_Last, '%H:%i')) end) LastChange, Priority
					FROM newdashboard.dashboard d join GSNConfig.dashboard c on c.GameCode='IAP'
							left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode
					WHERE d.GameCode='GSN'	";
		
		$sql .= "	order by Priority";
		return $sql;
	}
	
	function sqlListDashboardGSN_notfull ($fieldname) {
		$sql = "select d.GameCode, d.".$fieldname."_Today MainNumber, d.".$fieldname."_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'MAIN' ChartType,
						(case when l.".$fieldname."_Last is null then '' else concat('Last ',date_format(".$fieldname."_Last, '%H:%i')) end) LastChange, 0 Priority,
						concat('dashboarddetailcountry?gc=',d.GameCode) link
					from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
					where (d.".$fieldname."_Today <> 0 or d.".$fieldname."_PreviousDay <> 0) 
						and d.GameCode in ('".implode("','",$this->AllowCountry101)."')";
		$sql .= " union select d.GameCode, d.".$fieldname."_Today MainNumber, d.".$fieldname."_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType,
						(case when l.".$fieldname."_Last is null then '' else concat('Last ',date_format(".$fieldname."_Last, '%H:%i')) end) LastChange, 1 Priority,
						'' link
					from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
					where (d.".$fieldname."_Today <> 0 or d.".$fieldname."_PreviousDay <> 0) 
						and d.GameCode in ('".implode("','",$this->AllowGameGroup101)."')";
		$sql .= " union
				select d.GameCode, d.".$fieldname."_Today MainNumber, d.".$fieldname."_PreviousDay PreviousDay, 0 IsSameGroupWithPrecede, 'NORMAL' ChartType,
						(case when l.".$fieldname."_Last is null then '' else concat('Last ',date_format(".$fieldname."_Last, '%H:%i')) end) LastChange, 2 Priority,
						'' link
					from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
					where (d.".$fieldname."_Today <> 0 or d.".$fieldname."_PreviousDay <> 0) 
						and d.GameCode in ('".implode("','",$this->AllowGameCode101)."')
						and d.GameCode not in (select DISTINCT ToAppName from GSNConfig.AppName WHERE GameGroup in ('".implode("','",$this->AllowGameGroup101)."'))
						and d.GameCode not in (select DISTINCT ToAppName from GSNConfig.AppName WHERE Country in ('".implode("','",$this->AllowCountry101)."'))
				order by Priority, MainNumber desc";
		return $sql;
	}
	
	function sqlNotListDashboardGSN ($fieldname) {
		$sql = "select d.GameCode, d.".$fieldname."_Today MainNumber, d.".$fieldname."_PreviousDay PreviousDay, 0 IsSameGroupWithPrecede, 'NORMAL' ChartType,
						(case when l.".$fieldname."_Last is null then '' else concat('Last ',date_format(".$fieldname."_Last, '%H:%i')) end) LastChange
					from newdashboard.dashboard d left join GSNConfig.dashboard c on d.GameCode collate utf8_general_ci = c.GameCode
						left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode
					WHERE c.GameCode IS NULL 
						AND d.GameCode NOT IN (SELECT distinct ToAppName FROM GSNConfig.AppName WHERE GameGroup IN (SELECT GameCode FROM GSNConfig.dashboard))
						AND d.GameCode NOT IN (SELECT distinct Country FROM GSNConfig.AppName WHERE Country IS NOT null)
						AND d.GameCode NOT IN (SELECT distinct FAGroup FROM GSNConfig.AppName WHERE FAGroup IS NOT null)
						AND (".$fieldname."_Today <> 0 or ".$fieldname."_PreviousDay <> 0) order by d.GameCode";
		return $sql;
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
				$sql = "select date_format(ReportTime,'%H:%i') Hour, InstallCost/Install ".$fieldname." from newdashboard.dashboard_cpi_chart where GameCode=".$this->quote($gamecode)." and ReportTime>=curdate()+interval 2 hour order by ReportTime";
			else
				$sql = "select date_format(ReportTime,'%H:%i') Hour, InstallCost/Install ".$fieldname." from newdashboard.dashboard_cpi_chart where GameCode=".$this->quote($gamecode)." and ReportTime>=curdate()-interval 22 hour and ReportTime<curdate() order by ReportTime";
		elseif (in_array($fieldname, array('CPIgg')))
			if ($istoday)
				$sql = "select date_format(ReportTime,'%H:%i') Hour, InstallCost/Install ".$fieldname." from newdashboard.dashboard_cpigg_chart where GameCode=".$this->quote($gamecode)." and ReportTime>=curdate()+interval 2 hour order by ReportTime";
			else
				$sql = "select date_format(ReportTime,'%H:%i') Hour, InstallCost/Install ".$fieldname." from newdashboard.dashboard_cpigg_chart where GameCode=".$this->quote($gamecode)." and ReportTime>=curdate()-interval 22 hour and ReportTime<curdate() order by ReportTime";
		else
			if ($istoday)
				$sql = "select Hour, ".$fieldname." from revenuehourly where GameCode=".$this->quote($gamecode)." and ReportDate=curdate() order by Hour";
			else
				$sql = "select Hour, ".$fieldname." from revenuehourly where GameCode=".$this->quote($gamecode)." and ReportDate=curdate()-interval 1 day order by Hour";
		
		return $sql;
	}
	
	
	// detail page
	
	function sqlRevenueTotal () {
		$sql = "select 'RevenueTotal' GameCode, d.RevenueTotal_Today MainNumber, d.RevenueTotal_PreviousDay PreviousDay, 0 IsSameGroupWithPrecede, 'MAIN' ChartType,
						(case when l.RevenueTotal_Last is null then '' else concat('Last ',date_format(RevenueTotal_Last, '%H:%i')) end) LastChange
					from newdashboard.dashboard d
						left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
					where d.GameCode = '".$this->GameCode."'";
		return $sql;
	}
	
	function sqlListChannel () {
		$sql = "SELECT distinct left(COLUMN_NAME, LOCATE('_',COLUMN_NAME)-1) ColName FROM information_schema.COLUMNS 
				WHERE table_schema='newdashboard' AND TABLE_NAME='dashboard' AND COLUMN_NAME LIKE 'Revenue%' AND COLUMN_NAME not LIKE 'RevenueTotal%' AND COLUMN_NAME not LIKE 'Revenue\_%'";
		$data = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		
		$sql = '';
		foreach ($data as $row)
			$sql .= " union select '".str_replace('Revenue','',$row['ColName'])."' GameCode, ".$row['ColName']."_Today MainNumber, ".$row['ColName']."_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.".$row['ColName']."_Last is null then '' else concat('Last ',date_format(".$row['ColName']."_Last, '%H:%i')) end) LastChange 
				from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
				where d.GameCode='".$this->GameCode."' and (".$row['ColName']."_Today <> 0 or ".$row['ColName']."_PreviousDay <> 0) ";
		
		$sql = ltrim($sql, 'union ');
		return $sql;
	}
	
	function sqlRev () {
		$sql = '';
		if ($this->CountPlatform['iOS'] == 1)
			$sql .= " union select 'Revenue_iOS' GameCode, Revenue_iOS_Today MainNumber, Revenue_iOS_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.Revenue_iOS_Last is null then '' else concat('Last ',date_format(Revenue_iOS_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		if ($this->CountPlatform['Android'] == 1)
			$sql .= " union select 'Revenue_Android' GameCode, Revenue_Android_Today MainNumber, Revenue_Android_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.Revenue_Android_Last is null then '' else concat('Last ',date_format(Revenue_Android_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		if ($this->CountPlatform['Web'] == 1)
			$sql .= " union select 'Revenue_Web' GameCode, Revenue_Web_Today MainNumber, Revenue_Web_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.Revenue_Web_Last is null then '' else concat('Last ',date_format(Revenue_Web_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		if ($this->CountPlatform['Undefined'] == 1 || $sql == '')
			$sql .= " union select 'Revenue_Undefined' GameCode, Revenue_Undefined_Today MainNumber, Revenue_Undefined_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.Revenue_Undefined_Last is null then '' else concat('Last ',date_format(Revenue_Undefined_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		
		$sql = ltrim($sql, 'union ');
		return $sql;
	}
	
	function sqlPU () {
		$sql = '';
		if ($this->CountPlatform['iOS'] == 1)
			$sql .= " union select 'PU_iOS' GameCode, PU_iOS_Today MainNumber, PU_iOS_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.PU_iOS_Last is null then '' else concat('Last ',date_format(PU_iOS_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		if ($this->CountPlatform['Android'] == 1)
			$sql .= " union select 'PU_Android' GameCode, PU_Android_Today MainNumber, PU_Android_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.PU_Android_Last is null then '' else concat('Last ',date_format(PU_Android_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		if ($this->CountPlatform['Web'] == 1)
			$sql .= " union select 'PU_Web' GameCode, PU_Web_Today MainNumber, PU_Web_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.PU_Web_Last is null then '' else concat('Last ',date_format(PU_Web_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		$sql .= " union select 'PUTotal' GameCode, PUTotal_Today MainNumber, PUTotal_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.PUTotal_Last is null then '' else concat('Last ',date_format(PUTotal_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		
		$sql = ltrim($sql, 'union ');
		return $sql;
	}
	
	function sqlA1 () {
		$sql = '';
		if ($this->CountPlatform['iOS'] + $this->CountPlatform['Android'] + $this->CountPlatform['Web'] > 1) {
			if ($this->CountPlatform['iOS'] == 1)
				$sql .= " union select 'A1_iOS' GameCode, A1_iOS_Today MainNumber, A1_iOS_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.A1_iOS_Last is null then '' else concat('Last ',date_format(A1_iOS_Last, '%H:%i')) end) LastChange 
				from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
				where d.GameCode='".$this->GameCode."'";
			if ($this->CountPlatform['Android'] == 1)
				$sql .= " union select 'A1_Android' GameCode, A1_Android_Today MainNumber, A1_Android_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.A1_Android_Last is null then '' else concat('Last ',date_format(A1_Android_Last, '%H:%i')) end) LastChange 
				from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
				where d.GameCode='".$this->GameCode."'";
			if ($this->CountPlatform['Web'] == 1)
				$sql .= " union select 'A1_Web' GameCode, A1_Web_Today MainNumber, A1_Web_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.A1_Web_Last is null then '' else concat('Last ',date_format(A1_Web_Last, '%H:%i')) end) LastChange 
				from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
				where d.GameCode='".$this->GameCode."'";
		}
		$sql .= " union select 'A1Total' GameCode, A1Total_Today MainNumber, A1Total_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.A1Total_Last is null then '' else concat('Last ',date_format(A1Total_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		
		$sql = ltrim($sql, 'union ');
		return $sql;
	}
	
	function sqlN1 () {
		$sql = '';
		if ($this->CountPlatform['iOS'] + $this->CountPlatform['Android'] + $this->CountPlatform['Web'] > 1) {
			if ($this->CountPlatform['iOS'] == 1)
				$sql .= " union select 'N1_iOS' GameCode, N1_iOS_Today MainNumber, N1_iOS_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.N1_iOS_Last is null then '' else concat('Last ',date_format(N1_iOS_Last, '%H:%i')) end) LastChange 
				from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
				where d.GameCode='".$this->GameCode."'";
			if ($this->CountPlatform['Android'] == 1)
				$sql .= " union select 'N1_Android' GameCode, N1_Android_Today MainNumber, N1_Android_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.N1_Android_Last is null then '' else concat('Last ',date_format(N1_Android_Last, '%H:%i')) end) LastChange
				from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
				where d.GameCode='".$this->GameCode."'";
			if ($this->CountPlatform['Web'] == 1)
				$sql .= " union select 'N1_Web' GameCode, N1_Web_Today MainNumber, N1_Web_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.N1_Web_Last is null then '' else concat('Last ',date_format(N1_Web_Last, '%H:%i')) end) LastChange 
				from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
				where d.GameCode='".$this->GameCode."'";
		}
		$sql .= " union select 'N1Total' GameCode, N1Total_Today MainNumber, N1Total_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.N1Total_Last is null then '' else concat('Last ',date_format(N1Total_Last, '%H:%i')) end) LastChange 
			from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
			where d.GameCode='".$this->GameCode."'";
		
		$sql = ltrim($sql, 'union ');
		return $sql;
	}
	
	function sqlCCU () {
		$sql = '';
		$sql .= " union select 'CCU' GameCode, CCU_Today MainNumber, CCU_PreviousDay PreviousDay, 1 IsSameGroupWithPrecede, 'NORMAL' ChartType, (case when l.CCU_Last is null then '' else concat('Last ',date_format(CCU_Last, '%H:%i')) end) LastChange 
		from newdashboard.dashboard d left join newdashboard.dashboard_lastchange l on l.GameCode=d.GameCode 
		where d.GameCode='".$this->GameCode."'";
		
		$sql = ltrim($sql, 'union ');
		return $sql;
	}
	// ---------------------- END SQL for newdashboard.dashboard GSN --------------------------
	
	
	
	function maxWidgetPerRow() {
		if ($_COOKIE[self::constCookieIsMobileName] == 1) {
			$this->constWidgetX = round(   ($_COOKIE[self::constCookieScreenSizeName] - 10) / 2 - self::constWidgetMargin,   0);
			return 2;
		} else
			return floor(($_COOKIE[self::constCookieScreenSizeName] - 20) / ($this->constWidgetX + self::constWidgetMargin));
	}
	
	function createSVGSmallDashboard($datavalues, $charttype, $bgcolor) {  // $datavalues = array(   array('0h'=>30, '1h'=>50, ...),   array('0h'=>20, ...) )
		// styles
		$sizeX = $this->constWidgetX;
		if ($_COOKIE[self::constCookieIsMobileName] == 1) 
			$sizeY = 50;
		else
			$sizeY = 70;
		if ($charttype == 'MAIN') {
			$sizeX = $this->constWidgetX*2 + self::constWidgetMargin;
			$sizeY = 75;
		}
		
		$settings = [
			'show_grid' 	=> false,
			'show_axes' 	=> false,
			'back_stroke_width'	=> 0,
			'pad_top' 		=> 0,
			'pad_bottom' 	=> 0,
			'pad_right'		=> 0,
			'pad_left'		=> 0,
			'marker_size'	=> 0,
			'fill_under'	=> array(true, false),
			'back_colour'	=> $bgcolor,
			'line_dash'		=> array('0', '3,1,3'),
		];
		$colours = array(array('#999'), array('#ddd'));
		$graph = new SVGGraph($sizeX, $sizeY, $settings);
		$graph->colours = $colours;
		 
		$graph->Values($datavalues);
		return $graph->Fetch('MultiLineGraph', false, false, true);
	}
	

	function specialWidgetOverwriteField (&$gamecode, &$fieldname) {  // overwrite if you have a special widget
	}
	
	function showWidgets(&$listwidgetdata, $fieldname, $maxWidgetPerRow, &$currentRow, &$currentCol) {
		// $html = '';

		foreach ($listwidgetdata as $key=>$row) {
			// $LineFeeded = 0;
			// $row = $listwidgetdata[$datarow];
			// if ($row['Percent'] >= 2)			$color='#3F3';
			// else if ($row['Percent'] <= -2)	$color='#F00';
			// else 					$color='#FFF';
			
			$gamecodeoverwrite = $row['GameCode'];
			$fieldnameoverwrite = $fieldname;
			$this->specialWidgetOverwriteField($gamecodeoverwrite, $fieldnameoverwrite);
			
			// // change percent
			// $classpct = 'pChangePctNornal';
			// if ($row['Percent'] >= 10)
			// 	$classpct = 'pChangePctGreenBig';
			// else if ($row['Percent'] > 2)
			// 	$classpct = 'pChangePctGreen';
			// else if ($row['Percent'] <= -10)
			// 	$classpct = 'pChangePctRedBig';
			// else if ($row['Percent'] < -2)
			// 	$classpct = 'pChangePctRed';
			
			// // chart type
			// $classpostfix = '';
			// $sizeX = 1;
			// if ($row['ChartType'] == 'MAIN') {
			// 	$classpostfix = 'MAIN';
			// 	$sizeX = 2;
			// }
			
			// # bgcolor
			// if ($row['IsSameGroupWithPrecede'] == 1)
			// 	$bgcolor = self::constWidgetBGColor[$this->currentBGColorIndex];
			// else {
			// 	$this->currentBGColorIndex++;
			// 	if ($this->currentBGColorIndex == count(self::constWidgetBGColor))
			// 		$this->currentBGColorIndex = 0;
			// 	$bgcolor = self::constWidgetBGColor[$this->currentBGColorIndex];
			// }
			// if ($row['ChartType'] == 'MAIN')
			// 	$bgcolor = '#ff9618';
			
			// change text
			$WidgetName = $row['GameCode'];
			$WidgetName = str_replace('RevenueTotal','',$WidgetName);
			/*if ($WidgetName == 'Revenue_Web')
				$WidgetName = 'Rev Undefined';*/
			$WidgetName = str_replace("_Web","_H5",$WidgetName);
			if ($row['ChartType'] == 'MAIN')
				$WidgetName .= ' '.$this->pagetitle;

			$listwidgetdata[$key]['WidgetName'] = $WidgetName;
			// # link
			// $link = " style=\"background-color: ".$bgcolor."\"";
			// if ($row['link'] != '')
			// 	//$link = "onclick=\"location.href=\\'".$row['link']."\\'\" style=\"cursor: pointer;background-color: ".$bgcolor."\"";
			// 	$link = "onclick=\"dashboardclick(event, \\'".$row['link']."\\')\" style=\"cursor: pointer;background-color: ".$bgcolor."\"";
			
			// $h = '<div id="gc'.$row['GameCode'].'" class="divWidget'.$classpostfix.'" '.$link.'>';
			// $h .= '<span class="pTitle'.$classpostfix.'" data-bind="title">'.$WidgetName.'</span>';
			// if ($row['ChartType'] != 'NOCHART')
			// 	$h .= '<span class="'.$classpct.''.$classpostfix.'"><font id="pct'.$row['GameCode'].'" color='.$color.'>'.$row['Percent'].'%</font></span>';
			// $h .= '<h3 class="h3MainNumber'.$classpostfix.'"><font id="main'.$row['GameCode'].'">'.number_format($row['MainNumber'],0,'.',',').'</font></h3>';
			// $h .= '<span id="last'.$row['GameCode'].'" class="spanLastTime'.$classpostfix.'">Last</span>';
			if ($row['ChartType'] != 'NOCHART') {
				$values = $this->getDataHourly($gamecodeoverwrite, $fieldnameoverwrite);
				$listwidgetdata[$key]['curDate'] = $values[1];
				$listwidgetdata[$key]['prevDate'] = $values[0];
				// $h .= '<div class="svgChart'.$classpostfix.'">'.rtrim($this->createSVGSmallDashboard($values, $row['ChartType'], $bgcolor), "</div>\r\n");
			}
			// $h .= '</div>';
			
			// if ($maxWidgetPerRow > 4 && $row['IsSameGroupWithPrecede'] == 0) { // not same group
			// 	// count this group
			// 	$countgroup = 1;
			// 	for ($j=$datarow+1; $j<count($listwidgetdata); $j++)
			// 		if ($listwidgetdata[$j]['IsSameGroupWithPrecede'] == 0)
			// 			break;
			// 		else
			// 			$countgroup++;
			// 	if ($currentCol + $countgroup -1 > $maxWidgetPerRow) {
			// 		while ($currentCol <= $maxWidgetPerRow) {
			// 			$html .= "['<li></li>', 1, 1, ".$currentCol.", ".$currentRow."],";
			// 			$currentCol++;
			// 		}
			// 		$currentRow++;
			// 		$currentCol = 1;
			// 		$LineFeeded = 1;
			// 	}
			// }
			// if ($currentCol + $sizeX -1 > $maxWidgetPerRow) {
			// 	$currentRow++;
			// 	$currentCol = 1;
			// 	$LineFeeded = 1;
			// }
			// if ($row['LineFeed'] == 1 && $LineFeeded == 0) { // line feed
			// 	while ($currentCol <= $maxWidgetPerRow) {
			// 		$html .= "['<li></li>', 1, 1, ".$currentCol.", ".$currentRow."],";
			// 		$currentCol++;
			// 	}
			// 	$currentRow++;
			// 	$currentCol = 1;
			// 	$LineFeeded = 1;
			// }
			// $html .= "['<li>".$h."</li>', ".$sizeX.", 1, ".$currentCol.", ".$currentRow."],";
			// $currentCol = $currentCol + $sizeX;
		}
		// return $html;
	}
	
	function getDataHourly ($gamecode, $fieldname) { // $fieldname :RevenueTotal / A1Total/N1Total/
		// today
		$sql = $this->sqlHourly ($gamecode, $fieldname, true);
		$data = $this->getDataPDOSQL($this->pdoReportTool, $sql);
		$result1 = array();
		$totalcompare1 = 0;
		$pctHour = 0;
		foreach ($data as $row) {
			$result1[$row['Hour']] = $row[$fieldname];
			$totalcompare1 += $row[$fieldname];
			$pctHour = $row['Hour'];
		}
		
		// previous day
		$sql = $this->sqlHourly ($gamecode, $fieldname, false);
		$data = $this->getDataPDOSQL($this->pdoReportTool, $sql);
		$result2 = array();
		$totalcompare2 = 0;
		foreach ($data as $row) {
			$result2[$row['Hour']] = $row[$fieldname];
			if ($row['Hour'] <= $pctHour)
				$totalcompare2 += $row[$fieldname];
		}
	
		return array($result2, $result1);
	}
	
	function fillPercent (&$data) {
		foreach ($data as $key=>$row) {
			if ($row['PreviousDay'] == 0)
				$pct = 0;
			else
				$pct = round(($row['MainNumber']/$row['PreviousDay']-1)*100);
			$data[$key]['Percent'] = $pct;
		}
	}
	
	function countPlatform($GameCode) {
		$sql = "SELECT Revenue_iOS_Today, Revenue_iOS_PreviousDay, Revenue_Android_Today, Revenue_Android_PreviousDay, Revenue_Web_Today, Revenue_Web_PreviousDay, Revenue_Undefined_Today, Revenue_Undefined_PreviousDay, PU_iOS_Today, PU_iOS_PreviousDay, PU_Web_Today, PU_Web_PreviousDay, PU_Android_Today, PU_Android_PreviousDay, A1_iOS_Today, A1_iOS_PreviousDay, A1_Web_Today, A1_Web_PreviousDay, A1_Android_Today, A1_Android_PreviousDay, N1_iOS_Today, N1_iOS_PreviousDay, N1_Web_Today, N1_Web_PreviousDay, N1_Android_Today, N1_Android_PreviousDay FROM newdashboard.dashboard WHERE GameCode='".$GameCode."'";
		$dataToday = $this->getDataPDOSQL($this->pdoReportTool,$sql);

		$sql = "SELECT max(Revenue_iOS) Revenue_iOS, max(Revenue_Android) Revenue_Android, max(Revenue_Web) Revenue_Web, max(Revenue_Undefined) Revenue_Undefined, max(PU_iOS) PU_iOS, max(PU_Web) PU_Web, max(PU_Android) PU_Android FROM previousrevenue WHERE GameCode='".$GameCode."'";
		$dataPreRev = $this->getDataPDOSQL($this->pdoReportTool,$sql);
	
		$sql = "SELECT max(A1_iOS) A1_iOS, max(A1_Web) A1_Web, max(A1_Android) A1_Android, max(N1_iOS) N1_iOS, max(N1_Web) N1_Web, max(N1_Android) N1_Android from previousA1 WHERE GameCode='".$GameCode."'";
		$dataPreA1 = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		
		if ( $dataToday[0]['Revenue_iOS_Today'] > 0 || $dataToday[0]['Revenue_iOS_PreviousDay'] > 0
			|| $dataToday[0]['PU_iOS_Today'] > 0 || $dataToday[0]['PU_iOS_PreviousDay'] > 0
			|| $dataToday[0]['A1_iOS_Today'] > 0 || $dataToday[0]['A1_iOS_PreviousDay'] > 0
			|| $dataToday[0]['N1_iOS_Today'] > 0 || $dataToday[0]['N1_iOS_PreviousDay'] > 0
			|| $dataPreRev[0]['Revenue_iOS'] > 0 || $dataPreA1[0]['A1_iOS'] > 0	)
			$this->CountPlatform['iOS'] = 1;
		
		if ( $dataToday[0]['Revenue_Android_Today'] > 0 || $dataToday[0]['Revenue_Android_PreviousDay'] > 0
			|| $dataToday[0]['PU_Android_Today'] > 0 || $dataToday[0]['PU_Android_PreviousDay'] > 0
			|| $dataToday[0]['A1_Android_Today'] > 0 || $dataToday[0]['A1_Android_PreviousDay'] > 0
			|| $dataToday[0]['N1_Android_Today'] > 0 || $dataToday[0]['N1_Android_PreviousDay'] > 0
			|| $dataPreRev[0]['Revenue_Android'] > 0 || $dataPreA1[0]['A1_Android'] > 0	)
			$this->CountPlatform['Android'] = 1;
		
		if ( $dataToday[0]['Revenue_Web_Today'] > 0 || $dataToday[0]['Revenue_Web_PreviousDay'] > 0
			|| $dataToday[0]['PU_Web_Today'] > 0 || $dataToday[0]['PU_Web_PreviousDay'] > 0
			|| $dataToday[0]['A1_Web_Today'] > 0 || $dataToday[0]['A1_Web_PreviousDay'] > 0
			|| $dataToday[0]['N1_Web_Today'] > 0 || $dataToday[0]['N1_Web_PreviousDay'] > 0
			|| $dataPreRev[0]['Revenue_Web'] > 0 || $dataPreA1[0]['A1_Web'] > 0	)
			$this->CountPlatform['Web'] = 1;
		
		if ( $dataToday[0]['Revenue_Undefined_Today'] > 0 || $dataToday[0]['Revenue_Undefined_PreviousDay'] > 0
			|| $dataPreRev[0]['Revenue_Undefined'] > 0	)
			$this->CountPlatform['Undefined'] = 1;
		
	}
	
	function autoLink(&$data) {
		for ($i=0; $i<count($data); $i++)
			if ($data[$i]['link'] == '')
				if (in_array($data[$i]['GameCode'], array('GSN','ZPBoard'))) // nolink
					$data[$i]['link'] = '';
				elseif ($data[$i]['GameCode'] == 'ZPInter')
					$data[$i]['link'] = 'dashboardlistinter';
				elseif ($data[$i]['GameCode'] == 'ZPPortal_all')
					$data[$i]['link'] = 'dashboardlistportal';
				elseif ($data[$i]['GameCode'] == 'IAP')
					$data[$i]['link'] = 'dashboardgsniap';
				elseif (substr($data[$i]['GameCode'],strlen($data[$i]['GameCode'])-4,4) == '_all')
					$data[$i]['link'] = 'dashboarddetailgroupgame?gc='.$data[$i]['GameCode'];
				else
					$data[$i]['link'] = 'dashboarddetailgame?gc='.$data[$i]['GameCode'];
			
	}
	
	function createLink (&$data, $link) {
		for ($i=0; $i<count($data); $i++)
			$data[$i]['link'] = $link.'?gc='.str_replace('RevenueTotal','',$data[$i]['GameCode']);
	}
	
	function checkGameCodeExist ($GameCode) {
		$sql = "select GameCode from newdashboard.dashboard where GameCode=".$this->quote($GameCode);
		$data = $this->getDataPDOSQL($this->pdoReportTool,$sql);
		if (isset($data[0]))
			return true;
			
		return false;
	}
	
	function checkAllowGameCode($GameCode) {
		return true;
	}
}
