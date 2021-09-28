<?php
namespace App\Http\Controllers\Account;
use View;
use App\Http\Controllers\CommonFunction;

class AccountNewUser extends CommonFunction {
	
	public function index ($type='') {

		$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		$this->listChart = array(
			'chartNU' => $this->chartNU(),
			'chartFPU' => $this->chartFPU(),
			'chartRR_FPU' => $this->chartRR_FPU(),
			'chartFpuChannel' => $this->chartFpuChannel(),
			'chartRevChannel' => $this->chartRevChannel(),
			'chartRevByNUAge'=>$this->chartRevByNUAge(),
			'chartAvgRevByNUAge'=>$this->chartAvgRevByNUAge(),
		);	

		$this->listChartId=['chartNU','chartFPU','chartRR_FPU','chartFpuChannel','chartRevChannel','chartRevByNUAge',
				'chartAvgRevByNUAge']; 

		$this->addViewsToMain=json_encode(
			$this->addChart('chartNU').
			$this->addGrid('', 'gridNU', $this->getNUGrid(), false).
			$this->addChart('chartFPU').
			$this->addChart('chartRR_FPU').
			$this->addGrid('', 'gridFPU', $this->gridFPU(), false).
			$this->addChart('chartFpuChannel').
			$this->addGrid('', 'gridFPUChannel', $this->gridFPUChannel(), false).
			$this->addChart('chartRevChannel').
			$this->addGrid('', 'gridRevChannel', $this->gridRevChannel(), false).
			$this->addChart('chartRevByNUAge').
			$this->addChart('chartAvgRevByNUAge').
			$this->addGrid('', 'gridRevByNUAge', $this->gridRevByNUAge(), false).
			''
		);

		$this->pageTitle='New User Pay - First Pay User';	
			
		return parent::__index($listApp, $type);

	}
	
	function chartNU () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, FRev30-FRev15 `Rev16-30`, FRev15-FRev7 `Rev8-15`, FRev7-FRev3 `Rev4-7`, FRev3-FRev1 `Rev2-3`, FRev1-FRev0 Rev1, FRev0 Rev0 from daily_n1firstpay
			where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." 
				and platform = 'AllPlatform' and AppName = ".$this->quote($this->AppName)." and FirstPayChannel = 'AllChannel' order by ReportDate";
		
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'column',
					'stackname' => 'UserValue',					
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'New User - User Value',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'VND',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function getNUGrid() {
		$sql = "select ReportDate, Platform, FirstPayChannel, FPU, AvgConversonDay, AvgFirstPayAmount, FPU0 NU_PU0, FPU1 NU_PU1, FPU3 NU_PU3, FPU7 NU_PU7, FPU15 NU_PU15, FPU30 NU_PU30, FRev0 NU_Rev0, FRev1 NU_Rev1, FRev3 NU_Rev3, FRev7 NU_Rev7, FRev15 NU_Rev15, FRev30 NU_Rev30
					from daily_n1firstpay
					where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and Platform <> 'AllPlatform'
						and AppName = ".$this->quote($this->AppName)." and FirstPayChannel <> 'AllChannel' order by ReportDate desc";

        $data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridNU',
					'align' => ['FirstPayChannel'=>'left'],
					];
        $table = $this->_createGridData_html($data, $option);

        return $table;
    }
	
	function chartFPU () {
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Rev30-Rev15 `Rev16-30`, Rev15-Rev7 `Rev8-15`, Rev7-Rev3 `Rev4-7`, Rev3-Rev1 `Rev2-3`, Rev1-Rev0 Rev1, Rev0 from daily_firstpay
					where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
						and AppName = ".$this->quote($this->AppName)." and FirstPayChannel = 'AllChannel'  order by ReportDate";
		
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'column',
					'stackname' => 'UserValue',		
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, FPU from daily_firstpay
					where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
						and AppName = ".$this->quote($this->AppName)." and FirstPayChannel = 'AllChannel'  order by ReportDate";

		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					'yAxis' => 1,
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'First Paying User - User Value',
						'subtitle' => $this->AppName,
						'yAxis_title' => ['VND','user'],
						'stack_col' => true,
					];
		return $this->script_chart2Y($categories, $highchartseries, $options);
	}
	
	function chartRR_FPU () {
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(RR1*100,1) RR1, round(RR3*100,1) RR3, round(RR7*100,1) RR7, round(RR15*100,1) RR15, round(RR30*100,1) RR30 from daily_firstpay where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and FirstPayChannel='AllChannel' order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'FPU Retention Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function gridFPU(){
    	$sql = "select ReportDate, FirstPayChannel, FPU, FirstAmount, Rev0 F_Rev0, Rev1 F_Rev1, Rev3 F_Rev3, Rev7 F_Rev7, Rev15 F_Rev15, Rev30 F_Rev30, RR1 F_RR1, RR3 F_RR3, RR7 F_RR7, RR15 F_RR15, RR30 F_RR30 from daily_firstpay
					where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
						and AppName = ".$this->quote($this->AppName)." and FirstPayChannel <> 'AllChannel'  order by ReportDate;";

		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridFPU',
					'align' => array ('FirstPayChannel' => 'left'),	
					'datatype' => ['RR1'=>'PERCENT', 'RR3'=>'PERCENT', 'RR7'=>'PERCENT', 'RR15'=>'PERCENT', 'RR30'=>'PERCENT'],					
					];
        $table .= $this->_createGridData_html($data, $option);

        return $table;
    }
	
	function chartFpuChannel() {
		$sql = "select date_format(a.ReportDate,'".$this->formatXDate()."') ReportDate, a.FirstPayChannel, round((a.FPU/b.TotalFPU)*100,2) PerFRU
				from 
					(select  ReportDate, FirstPayChannel, FPU, ROUND(AvgFirstPayAmount*FPU,2) Rev
						from daily_n1firstpay
						where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
							and appname = ".$this->quote($this->AppName)."  and Platform = 'AllPlatform' and FirstPayChannel <> 'AllChannel') a 
				join 
					(select ReportDate, FPU TotalFPU, round(AvgFirstPayAmount*FPU) TotalRev
						from daily_n1firstpay
						where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
							and appname = ".$this->quote($this->AppName)."  and Platform = 'AllPlatform' and FirstPayChannel = 'AllChannel') b
				on a.ReportDate = b.ReportDate;";

		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'NewUser - No.First Payment By Channel',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%user',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function gridFPUChannel(){
		$sql = "select ReportDate, FirstPayChannel, sum(FPU) FPU from daily_n1firstpay
				where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and FirstPayChannel <> 'AllChannel' and appname = ".$this->quote($this->AppName)."
				group by ReportDate, FirstPayChannel";		

		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata4grid($data);
		$option = ['tableid' => 'tableid_gridFPUChannel',
					];
        $table .= $this->_createGridData_html($pivot, $option);

        return $table;
    }

	function chartRevChannel () {
		$sql = "select date_format(a.ReportDate,'".$this->formatXDate()."') ReportDate, a.FirstPayChannel, round((a.Rev/b.TotalRev)*100,2) PerRev
				from 
					(select  ReportDate, FirstPayChannel, FPU, ROUND(AvgFirstPayAmount*FPU,2) Rev
						from daily_n1firstpay
						where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
							and appname = ".$this->quote($this->AppName)."  and Platform = 'AllPlatform' and FirstPayChannel <> 'AllChannel') a 
				join 
					(select ReportDate,FPU TotalFPU, round(AvgFirstPayAmount*FPU) TotalRev
						from daily_n1firstpay
						where ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
							and appname = ".$this->quote($this->AppName)."  and Platform = 'AllPlatform' and FirstPayChannel = 'AllChannel') b
				on a.ReportDate = b.ReportDate;";

		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		
		$options = ['type' => 'column',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'NewUser - First Payment Revenue By Channel',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%rev',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function gridRevChannel(){
    	$sql = "select ReportDate, FirstPayChannel, sum(Round(AvgFirstPayAmount * FPU, 0)) as Rev from daily_n1firstpay 
				where FirstPayChannel <> 'AllChannel' and appname = ".$this->quote($this->AppName)." 
					and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)."
				group by ReportDate, FirstPayChannel
				order by ReportDate";

		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata4grid($data);
		$option = ['tableid' => 'tableid_gridRevChannel',
					];
        $table .= $this->_createGridData_html($pivot, $option);

        return $table;
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
}
