<?php
namespace App\Http\Controllers\Account;
use View;
use App\Http\Controllers\CommonFunction;

class CashTypePlatform extends CommonFunction {
	
	public function index ($type='') {

		$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		$this->listChart = array(
			'chartRevGrossAndroid' => $this->chartRevGrossPlatform('Android'),
			'chartRevGrossiOS' => $this->chartRevGrossPlatform('iOS'),
			'chartRevGrossOther' => $this->chartRevGrossPlatform('Other'),
			'chartP1Android' => $this->chartP1Platform('Android'),
			'chartP1iOS' => $this->chartP1Platform('iOS'),
			'chartP1Other' => $this->chartP1Platform('Other'),
		);				

		$this->addViewsToMain=json_encode(
			$this->addCharts(array_keys($this->listChart)).
			$this->addGrid('', 'gridAll', $this->gridAll(), false).
			''
		);

		$this->pageTitle='CashType Platform';	
		
		return parent::__index($listApp, $type,[], 'pages.Common1SField');

	}

	function chartRevGrossPlatform($platform='Android'){
		$conditionPlatform = "Platform";
		$sTitle = $platform;
		if($platform === 'Android' || $platform ==='iOS'){
			$conditionPlatform .= '='.$this->quote($platform);
		}else{
			$conditionPlatform .= " not in ('Android', 'iOS') ";
		}
		$sql1 = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, CashType, sum(RevGross) ".$sTitle."_Rev from dailycashtype where AppName=".
		$this->quote($this->AppName)." and ReportDate between ".
		$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$conditionPlatform.
		" group by ReportDate, CashType order by ReportDate, CashType";

		$sql2 = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, sum(RevGross) ".$sTitle."_Total from dailycashtype where AppName=".
		$this->quote($this->AppName)." and ReportDate between ".
		$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$conditionPlatform.
		" group by ReportDate order by ReportDate";
		
		$options = [	'title' => 'Rev '.$platform,
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
						
					];
		if($platform==='Other'){
			$options['chart_backgroundColor']=$this->darkMode?'#495249':'#EEFFEE';
		}

		return $this->chart1stack1line_1p1np($sql1, $sql2, $options);
	}
	
	function chartP1Platform($platform='Android'){
		$conditionPlatform = "Platform";
		$sTitle = $platform;
		if($platform === 'Android' || $platform ==='iOS'){
			$conditionPlatform .= '='.$this->quote($platform);
		}else{
			$conditionPlatform .= " not in ('Android', 'iOS') ";
		}
		$sql1 = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, CashType, sum(P1) ".$sTitle."_P1 from dailycashtype where AppName=".
		$this->quote($this->AppName)." and ReportDate between ".
		$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$conditionPlatform.
		" group by ReportDate, CashType order by ReportDate, CashType";

		$sql2 = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, sum(P1) ".$sTitle."_Total from dailycashtype where AppName=".
		$this->quote($this->AppName)." and ReportDate between ".
		$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ".$conditionPlatform.
		" group by ReportDate order by ReportDate";
		
		$options = [	'title' => 'P1 '.$platform,
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
						
					];
		if($platform==='Other'){
			$options['chart_backgroundColor']=$this->darkMode?'#495249':'#EEFFEE';
		}

		return $this->chart1stack1line_1p1np($sql1, $sql2, $options);
	}
	
	function gridAll() {
		$table = '';
		
        $sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, Platform, CashType, RevGross, RevNet, P1".
				" from dailycashtype where AppName=".$this->quote($this->AppName).
				" and Platform <> 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate).
				" and ".$this->quote($this->toDate)." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'align' => array ('CashType' => 'left'),
					'datatype' => ['RevGross'=>'DEC0', 'RevNet'=>'DEC0', 'P1'=>'DEC0']
					];
        $table .= $this->_createGridData_html($data, $option);
		
		return $table;
	}
}
