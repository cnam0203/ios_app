<?php

namespace App\Http\Controllers\Device;
use View;
use App\Http\Controllers\CommonFunction;

class DeviceModel extends CommonFunction {
	
	function __construct() {
		parent::__construct();
		$this->date = $this->getInputDate(true);
	}
	
	public function index ($type='') {

		// TotalQuantity
		$sql = "select sum(Quantity) Quantity from model where AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate)."";		
		$rs = $this->getDataSQL($sql);			
		$this->TotalQuantity = $rs[0]['Quantity'];
		if($this->TotalQuantity === null){
			$this->TotalQuantity = 0;
		}

		$this->listChart = array(
			'pieModelName' => $this->chartPieModelName(),
			'pieModelGroup' => $this->chartPieModelGroup(),
			'lineModelName' => $this->chartLineModelName(),
		);	
	
		$this->listChartId=array_keys($this->listChart); 	
	
		$this->addViewsToMain=json_encode(		
			"<div>".
				$this->addChart('pieModelName','two_chart chart').
				$this->addChart('pieModelGroup','two_chart chart', false).
			"</div>".		
			$this->addChart('lineModelName').			
			$this->addGrid('', 'gridModelName', $this->gridModelName(), false).
			''
		);
	
		$this->pageTitle='Device Model';	
			
		return parent::__index($this->addTopViewApp($this->listApp, 'Group101'), $type);
	}
	
	// ---------------- -------------------- SQL -----------------------
	// ---------------- -------------------- END SQL -----------------------
	
	function chartPieModelName() {
		// top 20
		$sql = "select RecordID, ModelName, Quantity*100/".$this->TotalQuantity." Quantity from model where ModelGroup<>'other_gsn' and AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate)." order by Quantity desc limit 15";
		$rs = $this->getDataSQL($sql);
		$chartdata = array();
		$ids = array();
		foreach ($rs as $v) {
			$ids[] = $v['RecordID'];
			$chartdata[] = array("name"=>$v['ModelName'], "y"=>round($v['Quantity'],1));
		}
		
		// others = not in top
		$sql = "select (Quantity)*100/".$this->TotalQuantity." Quantity from model where AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate).
		" and RecordID not in (".implode(count($ids)==0?['0']:$ids,',').") ";
		$rs = $this->getDataSQL($sql);
		$chartdata[] = array("name"=>'others', "y"=>round($rs[0]['Quantity'],1));
		
		$options = [	'title' => 'Device Model in '.$this->toDate,
						'subtitle' => $this->AppName,
					];
		return $this->script_pieChart($chartdata, $options);
	}
	
	function chartPieModelGroup() {
		// top 20
		$sql = "select ModelGroup, sum(Quantity)*100/".$this->TotalQuantity." Quantity, group_concat(RecordID) RecordID from model where ModelGroup<>'other_gsn' and AppName=".$this->quote($this->AppName).
		" and ReportDate= ".$this->quote($this->toDate)." group by ModelGroup order by sum(Quantity) desc limit 15";
		$rs = $this->getDataSQL($sql);
		$chartdata = array();
		$ids = array();
		foreach ($rs as $v) {
			$ids[] = $v['RecordID'];
			$chartdata[] = array("name"=>$v['ModelGroup'], "y"=>round($v['Quantity'],1));
		}
		
		// others = not in top
		$sql = "select sum(Quantity)*100/".$this->TotalQuantity." Quantity from model where AppName=".$this->quote($this->AppName)." and ReportDate= ".$this->quote($this->toDate).
		" and RecordID not in (".implode(count($ids)==0?['0']:$ids).",',')";
		$rs = $this->getDataSQL($sql);
		$chartdata[] = array("name"=>'others', "y"=>round($rs[0]['Quantity'],1));
		
		$options = [	'title' => 'Model Group in '.$this->toDate,
						'subtitle' => $this->AppName,
					];
		return $this->script_pieChart($chartdata, $options);
	}
	
	function chartLineModelName() {
		// top 20
		$sql = "select distinct ModelName from model where ModelGroup<>'other_gsn' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by Quantity desc limit 10";
		$rs = $this->getDataSQL($sql);
		$modelnames = array();
		foreach ($rs as $v) {
			$modelnames[] = $v['ModelName'];
		}
		
		// top 20
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, ModelName, Quantity from model where ModelGroup<>'other_gsn' and AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." and ModelName in ('".implode(count($modelnames)==0?['']:$modelnames,"','")."') order by ReportDate, ModelName desc";
		$rs1 = $this->getDataSQL($sql);
		$pivot4chart1 = $this->pivotdata($rs1);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($pivot4chart1, $options);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($rs1, 'ReportDate');
		
		$options = [	'title' => 'TOP Device Model',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#device',
						'chart_height' => 500,
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	function gridModelName() {
		$table = '';
		
        $sql = "select ReportDate, Platform, ModelName, ModelGroup, Quantity from model where AppName=".$this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc, Quantity desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridModelName',
					'align' => ['ModelName'=>'left', 'ModelGroup'=>'left'],
					];
        $table .= $this->_createGridData_html_2020($data, $option);
		
		return $table;
	}
}
