<?php
namespace App\Http\Controllers\Account;

use App\Http\Controllers\Color;
use App\Http\Controllers\CommonFunction, DateTime, DateInterval;

class AccountEvent extends CommonFunction {
	
	public function index ($type='') {
		if ($_SESSION['username'] == 'sinhtm@vng.com.vn') {
			/*var_dump($this->listApp);
			var_dump($this->isSelectedApp);
			echo 'debug';*/
		}
		$this->listApp = $this->getAppHaveEvent($this->listApp);
		$this->listCountryFromListApp(); // câu này + thêm if bên dưới + move addTopViewApp + $viewparams + đổi view
		$this->convertAppName();
		$this->alllEventMetric = [];
		if ($this->selectedCountry == '--All--')
			$listApp = $this->addTopViewApp($this->listApp, 'Group101');
		else
			$listApp = $this->reListAppByCountry($this->selectedCountry);

		$this->listChart = array(			
			'chartA1Event'=>$this->chartA1Event(),
			'chartRevEvent'=>$this->chartRevEvent(),
			'chartN1Event'=>$this->chartN1Event(),
			'chartCVEvent'=>$this->chartCVEvent(),
            'chartRREvent'=>$this->chartRREvent(),
            'chartSession'=>$this->chartSession(),
            'chartARPU'=>$this->chartARPU(),
		);
		$eventHighlight = $this->addEventHightLight();		
		$eventData = $this->getListEvent();					
		$this->fixEventData($eventData);		
		$eventID = $this->getListEventID($eventData);		
		$noEventID = $this->getListEventID($eventData, 1);
		$annoEvent = $this->annotationEvent($eventData);				

		$this->addViewsToMain=json_encode(
			$this->addChart('chartRevEvent').	
            $this->addChart('chartA1Event').		
            $this->addChart('chartSession').            
            $this->addChart('chartARPU').
			$this->addChart('chartCVEvent').	
			$this->addChart('chartRREvent').
            $this->addChart('chartN1Event').		            
			// $this->addGrid('', 'gridAll', $this->gridAll(), false).
			''
		);

		$this->pageInfo=
		"";
		$this->pageTitle='Account Event';			
		$viewparams = ['listCountry' => $this->listCountry,
						'selectedCountry' => $this->selectedCountry,
						'eventHighlight'=>$eventHighlight,
					'eventMetric'=>json_encode($this->allEventMetric),
					'eventID'=>json_encode($eventID),
					'noEventID'=>json_encode($noEventID),
					'annoEvent'=>$annoEvent];
		return parent::__index($listApp, $type, $viewparams, 'pages.AccountEvent');
	}

	private function getAppHaveEvent($listApp){
		$sql = "select AppName, Country from game_report_dis.event_date_config where StartDate <=".$this->quote($this->toDate).
		" and EndDate >= ".$this->quote($this->fromDate)."  group by AppName, Country order by StartDate";		
		$data = $this->getDataSQL($sql);			
		$res = [];
		foreach($data as $value){
			$app = $value['AppName'];
			$country = $value['Country'];
			if(in_array($country, $listApp) && !in_array($country, $res)){								
				$res[] = $country;
			}elseif(in_array($app."_".$country, $listApp) && !in_array($app."_".$country, $res)){
				$res[] = $app."_".$country;
			}elseif($country == ""){
				// dd($value);
				foreach($listApp as $appName){
					$tmp = explode("_", $appName);
					$appPrefix = $tmp[0];
					if($appPrefix == $app && !in_array($appName, $res)){
						$res[] = $appName;
					}
					// break;
				}
			}
		}		
		return $res;
	}

	function genEventHighLightChart($data){
		if (substr($this->fromDate,0,4) != substr($this->toDate,0,4))
			$format = 'Y/m/d';
		else $format = 'm/d';		
		$res=[];
		
		foreach($data as $value){
			$eventArr = [];
			$startDate = new DateTime($value['StartDate']);
			$endDate = new DateTime($value['EndDate']);

			$startPoint = $startDate->diff(new DateTime($this->fromDate));
			$endPoint = $startDate->diff($endDate);
			if ($startDate > new DateTime($this->fromDate)) {
				$eventArr['from'] = $startPoint->days;
				$eventArr['to'] = $endPoint->days + $startPoint->days;
			} else {
				$eventArr['from'] = -$startPoint->days;
				$eventArr['to'] = $endPoint->days - $startPoint->days;
			}				

			$eventArr['id'] = $value['EventID'];
			$eventArr['label'] = ['text' => $value['EventType'].' ('.$startDate->format($format).' - '.$endDate->format($format).')',
									'align' => 'center',
									'style' => ['color' => $this->darkMode? 'white': 'black']];			
			$eventArr['borderWidth'] = 1;		
			$eventArr['detailData'] = $value['EventID'].'<br>
										'.$startDate->format($format).' - '.$endDate->format($format).'<br>';
			$res[] = $eventArr;
			if($value['EventID'] === 'event123'){
				// dd($res);
			}		
		}							
		return $res;	
	}

	function addEventHightLight(){		
		//get data
		$sql = "select EventType, concat(EventID, '_', Country) as EventID, StartDate, EndDate from game_report_dis.event_date_config 
			where AppName=".$this->quote($this->AppServer)." and (Country = ".$this->quote($this->CountryCode).
			" ) and StartDate <=".$this->quote($this->toDate).
			" and EndDate >= ".$this->quote($this->fromDate)." order by StartDate";				
        $eventData = $this->getListEvent();        
		$plotEvent = $this->genEventHighLightChart($eventData);		        
		foreach($plotEvent as $key => $value){
			$colorInd = $key % count($plotEvent);
			$color = Color::fromHex(self::$LIST_COLOR_BAND[$colorInd])->getRGB();
			$plotEvent[$key]['color'] = "rgba(".$color[0].",".$color[1].",".$color[2].",0.05)";
			$plotEvent[$key]['borderColor'] = "rgba(".$color[0].",".$color[1].",".$color[2].",1)";
			$plotEvent[$key]['color1'] = self::$LIST_COLOR_BAND[$colorInd];
		}						
		return json_encode($plotEvent);
	}

	function annotationEvent($eventData){
        $allEventAnno = array();		
        $toDate = new DateTime($this->toDate);
        $fromDate = new DateTime($this->fromDate);
		foreach($eventData as $event){									
			$startDate = new DateTime($event['StartDate']);
            $endDate = new DateTime($event['EndDate']);	            
            $startShow = $startDate > $fromDate ? $startDate : $fromDate;
            $endShow = $endDate < $toDate ? $endDate : $toDate;
			$dateShow = $startShow;			
            $dateDiffAvg = round($endShow->diff($startShow)->days / 2);                        
            $dateShow->add(new DateInterval('P'.$dateDiffAvg.'D'));		            	
			if($event['EventType'] === 'NoEvent'){
				$yPos = 150;
			}else{
				$yPos = -10;
			}
			$annoTpl = $this->addAnnotaion($dateShow->format("Y-m-d"), $yPos);				
			$allEventAnno[$event['EventID']] = $annoTpl;
		}		
		if(count($allEventAnno) > 0){			
			$listLabel =[];
			foreach($allEventAnno as $eventID=>$anno){
				$label['labels'] = array($anno);
				$label['id'] = $eventID;				
				if($this->darkMode)	{
					$label['labelOptions'] = ['backgroundColor'=>'rgba(193,193,193,0.85)'];
				}else{
					$label['labelOptions'] = ['backgroundColor'=>'rgba(255,255,255,0.85)'];
				}				
				$listLabel[] = $label;
			}												
		}				
		return json_encode($listLabel);			
	}

	private function addAnnotaion($date, $yPos=-10){
		$tmp = [];		
		// $tmp["x"] = round($dateDiff / (60 * 60 * 24));		
		$tmp["x"]=date($this->formatDatePHP(),strtotime($date));				
		$tmp["y"] = $yPos;
		$tmp["xAxis"] = 0;
		$annoValue = [];
		$annoValue["point"] = $tmp;
		$annoValue["text"]='$text';		
		$annoValue["useHTML"]=true;		
		$annoValue["style"]=['fontSize'=>'13px'];
		return $annoValue;		
	}

	function getListEvent(){		
		//get data
		$sql = "select EventType, concat(EventID, '_', Country) as EventID, StartDate, EndDate from game_report_dis.event_date_config where AppName=".$this->quote($this->AppServer)." and (Country = ".$this->quote($this->CountryCode).
			" ) and StartDate <=".$this->quote($this->toDate).
			" and EndDate >= ".$this->quote($this->fromDate)." order by StartDate";			
		$sql = "select EventType, EventID, StartDate, EndDate from (select RANK() OVER (PARTITION BY EventType, EventID ORDER BY length(Country) DESC) AS Rank, EventType, EventID, StartDate, EndDate from game_report_dis.event_date_config where AppName=".$this->quote($this->AppServer)." and (Country = ".$this->quote($this->CountryCode).
			" or Country is null or Country ='') and StartDate <=".$this->quote($this->toDate).
			" and EndDate >= ".$this->quote($this->fromDate)." group by  EventType, EventID, Country, StartDate, EndDate  order by StartDate) t where t.Rank=1";
		$data = $this->getDataSQL($sql);											
		return $data;
	}

	function fixEventData(&$data){
		//find no event range
		$fromDate = new DateTime($this->fromDate);
		$toDate = new DateTime($this->toDate);					
		$dateDiff = $toDate->diff($fromDate)->days;			
		$dateCheck = new DateTime($this->fromDate);		
		// $dateShow->add(new DateInterval('P'.$dateDiffAvg.'D'));	
		$openRange = false;		
		$closeRange = false;
		$openRangeStr = '';
		$openRangeDate = null;
		for($i = 0; $i < $dateDiff; $i++){	
			$dateCheck->add(new DateInterval('P1D'));
			$inEventID = $this->checkInEventRange($data, $dateCheck);						
			if($inEventID===false){
				if(!$openRange){					
					//date khong trong event
					$openRange = true;
					$closeRange = false;
					$openRangeDate = clone $dateCheck;					
				}
			}else{							
				if($openRange){					
					//ket thuc range khong co event
					$openRange = false;
					$closeRange = true;
					//gen new data			
					$closeRangeDate = clone $dateCheck;		
					$closeRangeDate->sub(new DateInterval('P1D'));							
					// $closeRangeDate = date('Y-m-d', strtotime($closeRangeDate. ' - 1 days'));													
					if($closeRangeDate->diff($openRangeDate)->days > 3){
						// dd($closeRangeDate,$openRangeDate);
						$openRangeStr = $openRangeDate->format('Y-m-d');
						$closeRangeStr = $closeRangeDate->format('Y-m-d');
						$newData = ['EventID'=>'NoEvent'.$i, 'EventType'=>'NoEvent', 'StartDate'=>$openRangeStr, 'EndDate'=>$closeRangeStr];
						$data[] = $newData;
					}					
				}
			}
		}
		if($openRange && !$closeRange){
			$closeRangeDate = clone $dateCheck;	
			if($closeRangeDate->diff($openRangeDate)->days > 3){		
				$openRangeStr = $openRangeDate->format('Y-m-d');
				$closeRangeStr = $closeRangeDate->format('Y-m-d');				
				$newData = ['EventID'=>'NoEvent'.$dateDiff, 'EventType'=>'NoEvent', 'StartDate'=>$openRangeStr, 'EndDate'=>$closeRangeStr];
				$data[] = $newData;
			}
		}
	}

	function checkInEventRange($eventData, $dateCheck){
		foreach($eventData as $event){
			$startDate = new DateTime($event['StartDate']);
			$endDate = new DateTime($event['EndDate']);			
			if($dateCheck >= $startDate && $dateCheck <= $endDate){
				return $event['EventID'];
			}
		}
		return false;
	}

	function eventMetricToText($event, $data, $options=[]){
		$text = "";
		if (substr($event['StartDate'],0,4) != substr($event['EndDate'],0,4))
			$format = 'Y/m/d';
        else $format = 'm/d';	
        
		$startDate = new DateTime($event['StartDate']);
		$endDate = new DateTime($event['EndDate']);                
        $eventDur = $endDate->diff($startDate)->days+1;

		$text = $event['EventType'].' ('.$startDate->format($format).' - '.$endDate->format($format).') '.$eventDur.' days<br><hr class="myhrline">';

		$count = 0;
		foreach($data as $row){			
			foreach($row as $key => $value){
				$count += 1;				
				if(count($options) > 0){
					if(array_key_exists('--all--',$options)){
						$format = $options['--all--'];
					}else{
						$format = $options[$key];
					}					
					if(!isset($format)){
						$format='DEC';
					}
				}else{
					$format='DEC';
				}
				$text .= '<b>'.$key.'</b>'.": ".$this->textFormat($value, $format).", ";
				if($count % 2 == 0){
					$text .= '<br>';
				}
			}			
		}		
		return (trim(trim($text,'<br>'),', '));		
	}

	function textFormat($text, $type='NUMBER'){
		switch($type){
			case 'NUM':
			case 'DEC':
			case 'NUMBER':
			case 'DIGIT':
				return number_format($text, 0, ".",",");
			case 'RATE':
			case 'PERCENT':
				return number_format($text * 100, 2, ".",",").'%';
			default:
				return $text;
		}
	}

	function getListEventID($eventData, $type = 0){
		$res = [];
		foreach($eventData as $event){
			if($type === 1 && $event['EventType'] === 'NoEvent'){
				$res[] = $event['EventID'];
			}elseif($type == 0){
				$res[] = $event['EventID'];
			}
		}
		return $res;
	}

	function fetchDateToEvent($templateData, &$data){
		$listDateEvent = [];
		foreach($data as $row){
			$listDateEvent[] = $row['ReportDate'];
		}
		foreach($templateData as $row){			
			if(!in_array($row['ReportDate'], $listDateEvent)){
				$data[] = ['ReportDate'=>$row['ReportDate']];				
			}
		}
	}

	function fixArrayEvent(&$arr){
		foreach($arr as $key=> $value){			
			if($value['id']===''){				
				unset($arr[$key]);
			}
		}
	}
	

	function chartA1Event(){
		$eventData = $this->getListEvent();			
		$this->fixEventData($eventData);		
		
		foreach($eventData as $event){			
			$sql = "select round(avg(A1),0) avg_A1, sum(A1) sum_A1 from daily where AppName=".$this->quote($this->AppName).
			" and Platform = 'AllPlatform' and ReportDate between ".$this->quote($event['StartDate'])." and ".$this->quote($event['EndDate'])." ";			
			$data = $this->getDataSQL($sql);		
						
			$this->allEventMetric['chartA1Event'][] = $this->eventMetricToText($event, $data);
		}											
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, A1 from daily where AppName=".$this->quote($this->AppName).
			" and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);		
				
		$options = ['type' => 'column',
					'stackname' => 'A1'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		$highchartseries = array_merge($arr1);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, EventName, sum(NumUser) PlayEvent from game_report_dis.report_event_point where AppName=".$this->quote($this->AppServer).
			" and (Country = ".$this->quote($this->CountryCode)." ) and Action = 'Play' and  SubAction1 = '--All--' and SubAction2 = '--All--' and ExtraAction is null and ExtraAction is null
				and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." group by ReportDate, EventName order by ReportDate";				
		$data1 = $this->getDataSQL($sql);		
		$this->fetchDateToEvent($data, $data1);
		$pivot = $this->pivotdata($data1);		
				
		$options = ['type' => 'column',
					'stackname' => 'PlayEvent'
					];
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);		
		$this->fixArrayEvent($arr2);
		$highchartseries = array_merge($arr1, $arr2);

		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'A1',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => false,				
					];					
		return  $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartRevEvent(){
		$eventData = $this->getListEvent();		
		$this->fixEventData($eventData);				

		foreach($eventData as $event){
			$sql = "select round(avg(RevGross),0) avg_Rev, sum(RevGross) sum_Rev, round(avg(P1),0) avg_P1, sum(P1) sum_P1, sum(P1)/sum(A1) PayRate from daily where AppName=".$this->quote($this->AppName).
			" and Platform = 'AllPlatform' and ReportDate between ".$this->quote($event['StartDate'])." and ".$this->quote($event['EndDate'])." ";			
			$data = $this->getDataSQL($sql);		
						
			$this->allEventMetric['chartRevEvent'][] = $this->eventMetricToText($event, $data, ['PayRate'=>'RATE']);
		}											
		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, RevGross from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'area',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, EventName, sum(Point2) Rev from game_report_dis.report_event_point where AppName=".$this->quote($this->AppServer).
			" and (Country = ".$this->quote($this->CountryCode)." ) and Action = 'BuyPoint' and SubAction1 = '--All--' and SubAction2 = '--All--' and ExtraAction is null and ExtraAction is null 
				and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate).
			" group by ReportDate, EventName order by ReportDate";				
		$data1 = $this->getDataSQL($sql);		
		$this->fetchDateToEvent($data, $data1);
		$pivot = $this->pivotdata($data1);	
		$options = ['type' => 'line',
					];	
						
		$arr2 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
        $this->fixArrayEvent($arr2);		
        
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(sum(P1)/sum(A1) * 100, 2) PayRate from daily where AppName=".
            $this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." group by ReportDate order by ReportDate";
		$data = $this->getDataSQL($sql);
		
        $options = ['type' => 'line',
                    'yAxis' => 1,                    
					];
		$arr3 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		$highchartseries = array_merge($arr1, $arr2, $arr3);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Revenue / PayRate',
						'subtitle' => $this->AppName,
						'yAxis_title' => ['VND','%'],
						'stack_area' => true,
					];
		return $this->script_chart2Y($categories, $highchartseries, $options);
	}	

	function chartN1Event(){
		$eventData = $this->getListEvent();		
		$this->fixEventData($eventData);				
		
		foreach($eventData as $event){			
			$sql = "select round(avg(N1),0) avg_N1, sum(N1) sum_N1 from daily where AppName=".$this->quote($this->AppName).
			" and Platform = 'AllPlatform' and ReportDate between ".$this->quote($event['StartDate'])." and ".$this->quote($event['EndDate'])." ";			
			$data = $this->getDataSQL($sql);		
						
			$this->allEventMetric['chartN1Event'][] = $this->eventMetricToText($event, $data);
		}					

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, N1 from daily where AppName=".$this->quote($this->AppName).
			" and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);		
				
		$options = ['type' => 'column',
					'stackname' => 'N1'
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'N1',
						'subtitle' => $this->AppName,
						'yAxis_title' => '#',
						'stack_col' => true,				
					];					
		return  $this->script_lineChart($categories, $highchartseries, $options);
	}

	private function sumN1($field){
		return "sum(case when ".$field." is null then 0 else N1 end)";
	}

	function chartCVEvent(){
		$eventData = $this->getListEvent();						
		$this->fixEventData($eventData);

		foreach($eventData as $event){					
			$sql = "select sum(New_CV0 * N1)/".$this->sumN1("New_CV0")."  avg_CV0, sum(New_CV1 * N1)/".$this->sumN1("New_CV1")."  avg_CV1, 
			sum(New_CV3 * N1)/".$this->sumN1("New_CV3")." avg_CV3, sum(New_CV7 * N1)/".$this->sumN1("New_CV7")." avg_CV7 from daily where AppName=".$this->quote($this->AppName).
			" and Platform = 'AllPlatform' and ReportDate between ".$this->quote($event['StartDate'])." and ".$this->quote($event['EndDate'])." ";			
			$data = $this->getDataSQL($sql);		
									
			$this->allEventMetric['chartCVEvent'][] = $this->eventMetricToText($event, $data, ['--all--'=>'RATE']);
		}					

		$sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_CV0*100,1) CV0, round(New_CV1*100,1) CV1, round(New_CV3*100,1) CV3, round(New_CV7*100,1) CV7, round(New_CV15*100,1) CV15, round(New_CV30*100,1) CV30 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Conversion Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}

	function chartRREvent () {
		$eventData = $this->getListEvent();						
		$this->fixEventData($eventData);

		foreach($eventData as $event){					
			$sql = "select sum(New_RR1 * N1)/".$this->sumN1("New_RR1")." avg_RR1, sum(New_RR3 * N1)/".$this->sumN1("New_RR3")." avg_RR3, 
			sum(New_RR7 * N1)/".$this->sumN1("New_RR7")."  avg_RR7 from daily where AppName=".$this->quote($this->AppName).
			" and Platform = 'AllPlatform' and ReportDate between ".$this->quote($event['StartDate'])." and ".$this->quote($event['EndDate'])." ";			
			$data = $this->getDataSQL($sql);		
									
			$this->allEventMetric['chartRREvent'][] = $this->eventMetricToText($event, $data, ['--all--'=>'RATE']);
		}	

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(New_RR1*100,1) RR1, round(New_RR3*100,1) RR3, round(New_RR7*100,1) RR7, round(New_RR15*100,1) RR15, round(New_RR30*100,1) RR30 from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Retention Rate',
						'subtitle' => $this->AppName,
						'yAxis_title' => '%',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
    }		
    
    function chartSession(){
        $eventData = $this->getListEvent();						
		$this->fixEventData($eventData);

		foreach($eventData as $event){					
            $sql = "select avg(Session_TotalQty) avg_Sess_Qty, sum(Session_TotalQty) sum_Sess_Qty, avg(Session_AvgDuration) `avg_Sess_Dur(s)`, sum(Session_AvgDuration) `sum_Sess_Dur(s)` from device where AppName=".
                $this->quote($this->AppName). " and Platform = 'AllPlatform' and ReportDate between ".$this->quote($event['StartDate'])." and ".$this->quote($event['EndDate'])." ";			
			$data = $this->getDataSQL($sql);		
									
			$this->allEventMetric['chartSession'][] = $this->eventMetricToText($event, $data);
        }	
        
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Session_TotalQty from device where Platform='AllPlatform' and AppName=".
            $this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
        $data = $this->getDataSQL($sql);

        $options = ['type' => 'column',
					'stackname' => 'Session'
					];
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true); //true = remove first col
        
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, Session_AvgDuration from device where Platform='AllPlatform' and AppName=".
            $this->quote($this->AppName)." and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
        $data = $this->getDataSQL($sql);
        

        $options = ['type' => 'line',
                    'yAxis' => 1,
					];
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true); //true = remove first col
		
		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'Session',
						'subtitle' => $this->AppName,
						'yAxis_title' => ['#','sec'],
						'chart_height' => 400,
					];
        return $this->script_chart2Y($categories, $highchartseries, $options);                
    }
    
    function chartARPU () {
        $eventData = $this->getListEvent();						
		$this->fixEventData($eventData);

		foreach($eventData as $event){					
            $sql = "select round(avg(RevGross)/avg(A1)) avg_ARPU, round(avg(RevGross)/avg(P1)) avg_ARPPU from daily where AppName=".
                $this->quote($this->AppName). " and Platform = 'AllPlatform' and ReportDate between ".$this->quote($event['StartDate'])." and ".$this->quote($event['EndDate'])." ";			
			$data = $this->getDataSQL($sql);		
									
			$this->allEventMetric['chartARPU'][] = $this->eventMetricToText($event, $data);
        }

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, round(RevGross/A1) ARPU, round(RevGross/P1) ARPPU from daily where AppName=".
            $this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate";
		$data = $this->getDataSQL($sql);
		
		$options = ['type' => 'line',
					];
		$arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);
		
		// build high chart
		$highchartseries = array_merge($arr1);
		$categories = $this->listunique_valueofcolumn($data, 'ReportDate');
		
		$options = [	'title' => 'ARPU',
						'subtitle' => $this->AppName,
						'yAxis_title' => 'VND',
					];
		return $this->script_lineChart($categories, $highchartseries, $options);
	}
	
	
	function gridAll() {
		$table = '';
		
        $sql = "select ReportDate, A1, N1, DeviceOfN1, NewDeviceOfN1, N1OfNewDevice, InstallDeviceCount Install, ".
				" ActiveDeviceCount ActiveDevice, P1, NPU FPU, RevGross, RevNet, ".
				" New_RR1, New_RR3, New_RR7, New_RR15, New_RR30, New_CV0, New_CV1, New_CV3, New_CV7, New_CV15, New_CV30, New_PU30, New_Rev30Gross, ".
				" if(A1=0,0,RevGross/A1) ARPU, if(P1=0,0,RevGross/P1) ARPPU, ".
				" A3, A7, A15, A30, Churn1, Churn3, Churn7, Churn15, Churn30, ".
				" PUChurn3, PUChurn7, PUChurn15, PUChurn30 ".
				" from daily where AppName=".$this->quote($this->AppName)." and Platform = 'AllPlatform' and ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate desc";
		$data = $this->getDataSQL($sql);
		$option = ['tableid' => 'tableid_gridAll',
					'datatype' => ['A3'=>'DEC0', 'A7'=>'DEC0', 'A15'=>'DEC0', 'A30'=>'DEC0',
								'Churn1'=>'PERCENT', 'Churn3'=>'PERCENT', 'Churn7'=>'PERCENT', 'Churn15'=>'PERCENT', 'Churn30'=>'PERCENT',
								'PUChurn3'=>'PERCENT', 'PUChurn7'=>'PERCENT', 'PUChurn15'=>'PERCENT', 'PUChurn30'=>'PERCENT',
								'ARPU'=>'DEC0', 'ARPPU'=>'DEC0', 
								'Session_AvgQty'=>'DEC2',
								'New_RR1'=>'PERCENT', 'New_RR3'=>'PERCENT', 'New_RR7'=>'PERCENT', 'New_RR15'=>'PERCENT', 'New_RR30'=>'PERCENT',
								'New_CV0'=>'PERCENT', 'New_CV1'=>'PERCENT', 'New_CV3'=>'PERCENT', 'New_CV7'=>'PERCENT', 'New_CV15'=>'PERCENT', 'New_CV30'=>'PERCENT', ],
					];
        $table .= $this->_createGridData_html($data, $option);
		
		return $table;
	}

	
}
