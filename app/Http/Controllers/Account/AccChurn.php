<?php
namespace App\Http\Controllers\Account;
use View;
use App\Http\Controllers\CommonFunction;

class AccChurn extends CommonFunction {

	public function __construct() {
		$this->sqlAppPermission = "select distinct AppName from tracker_report.account_churn_range_define where AppName is not null {{WHERE}} order by AppName";
		parent::__construct('', 'AppName'); // check permission
	}
	
	public function index ($type='') {

		if($this->AppName==='cotyphu'){
			$fieldsC3 = ['chartChurn3EndGold', 'chartChurn3VanChoi'];
			$fieldsC7 = ['chartChurn7EndGold', 'chartChurn7VanChoi'];
		}elseif($this->AppName==='farmeryjs'){
			$fieldsC3 = ['chartChurn3Level'];
			$fieldsC7 = ['chartChurn7Level'];
		}		

		$this->listChart = array(
			'chartChurn3EndGold' => $this->chartChurnGeneral(3, 'End_Gold'),
			'chartChurn3VanChoi' => $this->chartChurnGeneral(3, 'NoOfGame'),
			'chartChurn3Level' => $this->chartChurnGeneral(3, 'Level'),
			'chartChurn7EndGold' => $this->chartChurnGeneral(7, 'End_Gold'),
			'chartChurn7VanChoi' => $this->chartChurnGeneral(7, 'NoOfGame'),
			'chartChurn7Level' => $this->chartChurnGeneral(7, 'Level'),	
		);				
		$this->listChartId=['chartChurn3EndGold','chartChurn3VanChoi','chartChurn3Level',
				'chartChurn7EndGold','chartChurn7VanChoi','chartChurn7Level']; 

		$this->addViewsToMain=json_encode(
			$this->addCharts($fieldsC3).
			$this->addGrid('Grid Churn3', 'gridChurn3', $this->gridAll(3)).
			$this->addCharts($fieldsC7).
			$this->addGrid('Grid Churn7', 'gridChurn7', $this->gridAll(7))
		);

		
		return parent::__index($this->addTopViewApp($this->listApp, 'Group101'),$type);
	}


	function chartChurnGeneral($churn=7, $type='End_Gold'){
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, case when ToValue<9223372036854775807 then concat(FromValue,'-',ToValue) else concat('>',FromValue) end `Group`, Quantity from tracker_report.account_churn".$churn." where AppName=".$this->quote($this->AppName)." and Type=".$this->quote($type)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		// dd($sql);
		$data = $this->getDataSQL($sql);
		$pivot = $this->pivotdata_withColNamePrefix($data);
		$options = ['type' => 'line',					
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Churn'.$churn." - ".$type,
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	
	
	function gridAll($churn=7) {
		$table = '';
		
        $sql = "select date_format(ReportDate,'%Y-%m-%d') ReportDate, Type, case when ToValue<9223372036854775807 then concat(FromValue,'-',ToValue) else concat('>',FromValue) end `Group`, Quantity from tracker_report.account_churn".$churn." where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc, Type, RangeIndex";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll'.$churn,
					'datatype' => ['A3'=>'DEC0', 'A7'=>'DEC0', 'A15'=>'DEC0', 'A30'=>'DEC0',
								'Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Churn15'=>'PERCENT', 'Churn30'=>'PERCENT',
								'PUChurn3'=>'PERCENT', 'PUChurn7'=>'PERCENT', 'PUChurn15'=>'PERCENT', 'PUChurn30'=>'PERCENT',
								'Session_AvgQty'=>'DEC2',
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT',
								'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT', ],
					];
        $table .= $this->_createGridData_html($data, $option);
		
		return $table;
	}

}
