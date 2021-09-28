<?php
namespace App\Http\Controllers\Account;
use View;
use App\Http\Controllers\CommonFunction;

class UserAge extends CommonFunction {
	
	public function index ($type='') {

		$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		$this->listChart = array(
			'chartA1Age'=>$this->chartA1Age(),
			'chartRevByNUAge'=>$this->chartRevByNUAge(),
			'chartAvgRevByNUAge'=>$this->chartAvgRevByNUAge(),
			'chartPayingRateByAge'=>$this->chartPayingRateByAge(),
			'chartARPUByAge'=>$this->chartARPUByAge(),
			'chartARPUByAge'=>$this->chartARPUByAge(),
		);	

		$this->addViewsToMain=json_encode(
			$this->addChart('chartA1Age').
			$this->addGrid('', 'gridA1Age', $this->gridA1Age(), false).		
			$this->addChart('chartRevByNUAge').
			$this->addChart('chartAvgRevByNUAge').
			$this->addGrid('', 'gridRevByNUAge', $this->gridRevByNUAge(), false).
			$this->addChart('chartPayingRateByAge').
			$this->addChart('chartARPUByAge').
			$this->addGrid('', 'gridARPUByAge', $this->gridARPUByAge(), false).			
			''
		);

		$this->pageTitle='User By Age';	
			
		return parent::__index($listApp, $type);

	}
	

	function chartA1Age () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, `A1_AgeOlder`,`A1_Age91-180`,`A1_Age61-90`, `A1_Age31-60`, `A1_Age16-30`, `A1_Age8-15`, 
		`A1_Age4-7`, `A1_Age2-3`, `A1_Age1`, `A1_Age0`
			from tracker_report_dis.daily_age_report
			where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and AppName = ".$this->quote($this->AppName)."  and Platform = 'AllPlatform' order by ReportDate";
		
		// dd($sql);
		return $this->chart1Stack($sql, 'A1 By Age', $this->AppName, '#', false, $this->pdoAuthen);
	}

	function gridA1Age(){
		$sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, AppName, `A1_AgeOlder`,`A1_Age91-180`,`A1_Age61-90`, `A1_Age31-60`, `A1_Age16-30`, `A1_Age8-15`, 
		`A1_Age4-7`, `A1_Age2-3`, `A1_Age1`, `A1_Age0`
			from tracker_report_dis.daily_age_report
			where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and AppName = ".$this->quote($this->AppName)."  and Platform = 'AllPlatform' order by ReportDate desc";
		
		return $this->createGrid($sql, 'tableid_gridA1Age', [], $this->pdoAuthen);
	}

	function chartRevByNUAge() {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Rev_Older, (Rev_N16+Rev_N17+Rev_N18+Rev_N19+Rev_N20+Rev_N21+Rev_N22+Rev_N23+Rev_N24+Rev_N25+Rev_N26+Rev_N27+Rev_N28+Rev_N29+Rev_N30) `Rev_N16-30`, (Rev_N8+Rev_N9+Rev_N10+Rev_N11+Rev_N12+Rev_N13+Rev_N14+Rev_N15) `Rev_N8-15`, (Rev_N4+Rev_N5+Rev_N6+Rev_N7) `Rev_N4-7`, (Rev_N2+Rev_N3) `Rev_N2-3`, Rev_N1, Rev_N0 
			from daily_revenue
			where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and AppName = ".$this->quote($this->AppName)."  order by ReportDate";
		
		$data = $this->getDataSQL($sql);
		// dd($sql);
		
		$options = ['type' => 'column',
					'stackname' => 'Rev',
					'invisible' => ['Rev_Older']
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Revenue By User Age',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'VND',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartAvgRevByNUAge() {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(Rev_Older/PU_Older,0) ARPPU_Older, 
		round((Rev_N16+Rev_N17+Rev_N18+Rev_N19+Rev_N20+Rev_N21+Rev_N22+Rev_N23+Rev_N24+Rev_N25+Rev_N26+Rev_N27+Rev_N28+Rev_N29+Rev_N30)/(PU_N16+PU_N17+PU_N18+PU_N19+PU_N20+PU_N21+PU_N22+PU_N23+PU_N24+PU_N25+PU_N26+PU_N27+PU_N28+PU_N29+PU_N30),0) `ARPPU_N16-30`, 
		round((Rev_N8+Rev_N9+Rev_N10+Rev_N11+Rev_N12+Rev_N13+Rev_N14+Rev_N15)/(PU_N8+PU_N9+PU_N10+PU_N11+PU_N12+PU_N13+PU_N14+PU_N15),0) `ARPPU_N8-15`, 
		round((Rev_N4+Rev_N5+Rev_N6+Rev_N7)/(PU_N4+PU_N5+PU_N6+PU_N7),0) `ARPPU_N4-7`, round((Rev_N2+Rev_N3)/(PU_N2+PU_N3),0) `ARPPU_N2-3`, round(Rev_N1/PU_N1,0) `ARPPU_1`, round(Rev_N0/PU_N0,0) `ARPPU_0`
			from daily_revenue
			where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and AppName = ".$this->quote($this->AppName)."  order by ReportDate";
		
		$data = $this->getDataSQL($sql);
		// dd($sql);
		
		$options = ['type' => 'line',					
					'invisible' => ['ARPPU_Older']
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'ARPPU By User Age',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'VND',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function gridRevByNUAge(){
		$sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, Rev_Older, (Rev_N16+Rev_N17+Rev_N18+Rev_N19+Rev_N20+Rev_N21+Rev_N22+Rev_N23+Rev_N24+Rev_N25+Rev_N26+Rev_N27+Rev_N28+Rev_N29+Rev_N30) `Rev_N16-30`, (Rev_N8+Rev_N9+Rev_N10+Rev_N11+Rev_N12+Rev_N13+Rev_N14+Rev_N15) `Rev_N8-15`, (Rev_N4+Rev_N5+Rev_N6+Rev_N7) `Rev_N4-7`, (Rev_N2+Rev_N3) `Rev_N2-3`, Rev_N1, Rev_N0 ,
		
		Rev_Older/PU_Older ARPPU_Older,  (Rev_N16+Rev_N17+Rev_N18+Rev_N19+Rev_N20+Rev_N21+Rev_N22+Rev_N23+Rev_N24+Rev_N25+Rev_N26+Rev_N27+Rev_N28+Rev_N29+Rev_N30)/(PU_N16+PU_N17+PU_N18+PU_N19+PU_N20+PU_N21+PU_N22+PU_N23+PU_N24+PU_N25+PU_N26+PU_N27+PU_N28+PU_N29+PU_N30) `ARPPU_N16-30`, 
		(Rev_N8+Rev_N9+Rev_N10+Rev_N11+Rev_N12+Rev_N13+Rev_N14+Rev_N15)/(PU_N8+PU_N9+PU_N10+PU_N11+PU_N12+PU_N13+PU_N14+PU_N15) `ARPPU_N8-15`, 
		(Rev_N4+Rev_N5+Rev_N6+Rev_N7)/(PU_N4+PU_N5+PU_N6+PU_N7) `ARPPU_N4-7`, (Rev_N2+Rev_N3)/(PU_N2+PU_N3) `ARPPU_N2-3`, Rev_N1/PU_N1 `ARPPU_1`, Rev_N0/PU_N0 `ARPPU_0`
			from tracker_report.daily_revenue
			where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and AppName = ".$this->quote($this->AppName)." order by ReportDate desc";
		
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridRevByNUAge',
					];
        $table = $this->_createGridData_html($data, $option);

        return $table;
	}	

	function chartPayingRateByAge() {
		$sql = "select date_format(a.ReportDate,'".$this->formatXDate()."') ReportDate, 
		round(PU_Older*100/(b.`A1_Age31-60` + b.`A1_Age61-90`+b.`A1_Age91-180` + b.A1_AgeOlder),2) PR_Older, 
		round((PU_N16+PU_N17+PU_N18+PU_N19+PU_N20+PU_N21+PU_N22+PU_N23+PU_N24+PU_N25+PU_N26+PU_N27+PU_N28+PU_N29+PU_N30)*100/(b.`A1_Age16-30`),2) `PR_N16-30`, 
		round((PU_N8+PU_N9+PU_N10+PU_N11+PU_N12+PU_N13+PU_N14+PU_N15)*100/(b.`A1_Age8-15`),2) `PR_N8-15`, 
		round((PU_N4+PU_N5+PU_N6+PU_N7)*100 /(b.`A1_Age4-7`),2) `PR_N4-7`, round((PU_N2+PU_N3)*100/(b.`A1_Age2-3`),2) `PR_N2-3`, round(PU_N1*100/b.`A1_Age1`,2) `PR_1`, round(PU_N0*100/b.`A1_Age0`,2) `PR_0`
			from tracker_report.daily_revenue a
		join tracker_report_dis.daily_age_report b on a.ReportDate=b.ReportDate and a.AppName=b.AppName
			where a.ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and a.AppName = ".$this->quote($this->AppName)."  and b.Platform = 'AllPlatform' order by a.ReportDate";
		
		$data = $this->getDataPDOSQL($this->pdoAuthen, $sql);
		
		$options = ['type' => 'line',					
					'invisible' => ['PR_Older']
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Paying Rate By User Age',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartARPUByAge() {
		$sql = "select date_format(a.ReportDate,'".$this->formatXDate()."') ReportDate, 
		round(Rev_Older/(b.`A1_Age31-60` + b.`A1_Age61-90`+b.`A1_Age91-180` + b.A1_AgeOlder),2) ARPU_Older, 
		round((Rev_N16+Rev_N17+Rev_N18+Rev_N19+Rev_N20+Rev_N21+Rev_N22+Rev_N23+Rev_N24+Rev_N25+Rev_N26+Rev_N27+Rev_N28+Rev_N29+Rev_N30)/(b.`A1_Age16-30`),0) `ARPU_N16-30`, 
		round((Rev_N8+Rev_N9+Rev_N10+Rev_N11+Rev_N12+Rev_N13+Rev_N14+Rev_N15)/(b.`A1_Age8-15`),0) `ARPU_N8-15`, 
		round((Rev_N4+Rev_N5+Rev_N6+Rev_N7) /(b.`A1_Age4-7`),0) `ARPU_N4-7`, round((Rev_N2+Rev_N3)/(b.`A1_Age2-3`),0) `ARPU_N2-3`, round(Rev_N1/b.`A1_Age1`,0) `ARPU_1`, round(Rev_N0/b.`A1_Age0`,0) `ARPU_0`
			from tracker_report.daily_revenue a
		join tracker_report_dis.daily_age_report b on a.ReportDate=b.ReportDate and a.AppName=b.AppName
			where a.ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and a.AppName = ".$this->quote($this->AppName)."  and b.Platform = 'AllPlatform' order by a.ReportDate";
		
		$data = $this->getDataPDOSQL($this->pdoAuthen, $sql);
		
		$options = ['type' => 'line',					
					'invisible' => ['ARPU_Older']
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'ARPU By User Age',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'VND',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function gridARPUByAge(){
		$sql = "select date_format(a.ReportDate,'".$this->formatXDate()."') ReportDate, 
		round(Rev_Older/(b.`A1_Age31-60` + b.`A1_Age61-90`+b.`A1_Age91-180` + b.A1_AgeOlder),2) ARPU_Older, 
		round((Rev_N16+Rev_N17+Rev_N18+Rev_N19+Rev_N20+Rev_N21+Rev_N22+Rev_N23+Rev_N24+Rev_N25+Rev_N26+Rev_N27+Rev_N28+Rev_N29+Rev_N30)/(b.`A1_Age16-30`),0) `ARPU_N16-30`, 
		round((Rev_N8+Rev_N9+Rev_N10+Rev_N11+Rev_N12+Rev_N13+Rev_N14+Rev_N15)/(b.`A1_Age8-15`),0) `ARPU_N8-15`, 
		round((Rev_N4+Rev_N5+Rev_N6+Rev_N7) /(b.`A1_Age4-7`),0) `ARPU_N4-7`, round((Rev_N2+Rev_N3)/(b.`A1_Age2-3`),0) `ARPU_N2-3`, round(Rev_N1/b.`A1_Age1`,0) `ARPU_1`, round(Rev_N0/b.`A1_Age0`,0) `ARPU_0`,
		round(PU_Older/(b.`A1_Age31-60` + b.`A1_Age61-90`+b.`A1_Age91-180` + b.A1_AgeOlder),4) PR_Older, 
		round((PU_N16+PU_N17+PU_N18+PU_N19+PU_N20+PU_N21+PU_N22+PU_N23+PU_N24+PU_N25+PU_N26+PU_N27+PU_N28+PU_N29+PU_N30)/(b.`A1_Age16-30`),4) `PR_N16-30`, 
		round((PU_N8+PU_N9+PU_N10+PU_N11+PU_N12+PU_N13+PU_N14+PU_N15)/(b.`A1_Age8-15`),4) `PR_N8-15`, 
		round((PU_N4+PU_N5+PU_N6+PU_N7) /(b.`A1_Age4-7`),4) `PR_N4-7`, round((PU_N2+PU_N3)/(b.`A1_Age2-3`),4) `PR_N2-3`, round(PU_N1/b.`A1_Age1`,4) `PR_1`, round(PU_N0/b.`A1_Age0`,4) `PR_0`
			from tracker_report.daily_revenue a
		join tracker_report_dis.daily_age_report b on a.ReportDate=b.ReportDate and a.AppName=b.AppName
			where a.ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and a.AppName = ".$this->quote($this->AppName)."  and b.Platform = 'AllPlatform' order by a.ReportDate desc";
		
		$data = $this->getDataPDOSQL($this->pdoAuthen, $sql);
		$option = ['tableid' => 'tableid_gridARPUByAge',
					'datatype'=>['PR_Older'=>'PERCENT2','PR_N16-30'=>'PERCENT2','PR_N8-15'=>'PERCENT2','PR_N4-7'=>'PERCENT2','PR_N2-3'=>'PERCENT2','PR_1'=>'PERCENT2','PR_0'=>'PERCENT'],
					];
		$table = $this->_createGridData_html($data, $option);

        return $table;
	}
}
