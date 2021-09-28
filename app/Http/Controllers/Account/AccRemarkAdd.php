<?php
namespace App\Http\Controllers\Account;
use View, Request, PDO, URL, Redirect;
use App\Http\Controllers\Chart2018;

class AccRemarkAdd extends Chart2018 {
	
	public function index ($type='') {
		$this->listAllow101();

		$this->listGameGroup = $this->getListGroupGame();
		$this->listCountry = $this->getListCountry();	
		$this->listApp = $this->AllowGameCode101;
		$sql = "select count(distinct ToAppName) AppName, count(distinct GameGroup) GameGroup, count(distinct Country)Country from GSNConfig.AppName";
		$data = $this->getDataSQL($sql);
		$data=$data[0];
		if($data['AppName'] == count($this->listApp)){
			array_unshift($this->listApp, 'All_App');
		}
		if($data['GameGroup'] == count($this->listGameGroup)){
			array_unshift($this->listGameGroup, 'All_Group');
		}
		if($data['Country'] == count($this->listCountry)){
			array_unshift($this->listCountry, 'All_Country');
		}
		$this->listCountryGroup();

		$messageError='';	
		// dd(Request::input());
		if (Request::input('InputSend') == 1) {			
			$noteContent = trim(Request::input('InputNote'));			
			$longNote = trim(Request::input('InputLongNote'));				
			$fromDate = $this->madSafety(Request::input('FromDate'));
			$toDate = $this->madSafety(Request::input('ToDate'));
			$appName = Request::input('AppNameList');
			$groupGame = Request::input('GroupGameList');
			$country = Request::input('CountryList');
			$messageError = '';
			if (strlen($noteContent) < 5)
				$messageError = 'Note phải có tối thiểu 5 ký tự';
			if($messageError == ''){
				$cvFrom = strtotime($fromDate);
				$cvTo = strtotime($toDate);
				if($cvFrom > $cvTo){
					$messageError = 'FromDate phải nhỏ hơn hoặc bằng ToDate';
				}
			}
			if($messageError == ''){				
				if(count($appName) <= 0 && count($groupGame) <= 0 && count($country) <= 0){
					$messageError = 'Phải chọn ít nhất 1 ô trong App Name HOẶC Game Group HOẶC Country';
				}
			}			
			if(in_array("All_App", $appName) ){
				$appName = array("All_App");
			}
			if(in_array("All_Group", $groupGame) ){
				$groupGame = array("All_Group");
			}
			if(in_array("All_Country", $country) ){
				$country = array("All_Country");
			}

			if($messageError == ''){
				$messageSucc = 'Done';
				$data = array();
				$strApp = implode(",", $appName);
				$strGroup = implode(",", $groupGame);
				$strCountry = implode(",", $country);
				if($strApp != ""){
					$strApp .= ",";
				}
				if($strGroup != ""){
					$strGroup .= ",";
				}
				if($strCountry != ""){
					$strCountry .= ",";
				}				
				array_push($data, $noteContent);
				array_push($data, $longNote);
				array_push($data, $fromDate);
				array_push($data, $toDate);
				array_push($data, $strApp);				
				array_push($data, $strGroup);
				array_push($data, $strCountry);
				$this->addNote($data);	
				$currURL = $this->getCurrentURL();

				return Redirect::to($currURL)->withInput(['InputSend'=>'0','RedirectSucc'=>'1']);				
			}			
		}else if(Request::old('RedirectSucc') == 1){
			$messageSucc = 'Done';
		}		
		// dd($this->mapAppCountry);
		return View::make('pages.AccRemarkAdd',array(
					'pagetitle' => 'Remark Add',
					'menu' => $this->menu,
					'date' => $this->date,
					'selectedApp' => $this->AppName,
					'listApp'=> $this->listApp,
					'listGameGroup' => $this->listGameGroup,
					'listCountry' => $this->listCountry,
					'messageError' => $messageError,
					'messageSucc' => $messageSucc,					
					'mapAppCountry' => $this->mapAppCountry,	
					'mapAppGroup'=>$this->mapAppGroup,	
					
					'lastestNote' => $this->lastestNote(),
					'darkMode'=>$this->darkMode,	
					
				));
	}

	function getCurrentURL(){
		$currURL = URL::current();
		if (strpos($currURL, 'tracking.playzing.g6.zing.vn/') !== false)
			$currURL = str_replace('tracking.playzing.g6.zing.vn/', 'tracking.playzing.g6.zing.vn:8080/', $currURL);
		return $currURL;
	}

	function lastestNote(){
		$sql = "select max(NoteID) mID from notehistory";
        $data = $this->getDataPDOSQL($this->pdoAuthen,$sql);
		$maxId = $data[0]['mID'];
		$maxId = $maxId - 500;


		$sql = "select NoteID, ShortNote, FullNote, FromDate, ToDate, ListApp, ListGameGroup, ListCountry, UserInput from notehistory
					where NoteID > ".$maxId." and IsDelete=0 order by NoteID desc limit 200";
					// dd($sql);
		$data = $this->getDataPDOSQL($this->pdoAuthen,$sql);		
		$option = ['tableid' => 'tableid_lastestNote',
					'align' => ['FirstPayChannel'=>'left'],
					];
		// dd($data);
		$allowData = array();
		$sql = "select count(distinct ToAppName) AppName, count(distinct GameGroup) GameGroup, count(distinct Country)Country from GSNConfig.AppName";
		$numAllField = $this->getDataSQL($sql);
		$numAllField=$numAllField[0];

		foreach ($data as $value) {			
			$count = 0;
			$valueCountry = explode(",", substr($value['ListCountry'],0, -1));			

			if($value['ListCountry'] == "" || $this->checkAllowField($valueCountry,$this->listCountry) || 
				count($this->listCountry) >= $numAllField['Country'] || $this->isAllValue($valueCountry)){
				$count += 1;
			}
			$valueGroup = explode(",", substr($value['ListGameGroup'],0, -1));			
			if($value['ListGameGroup'] == "" || $this->checkAllowField($valueGroup,$this->listGameGroup) || 
				count($this->listGameGroup) >= $numAllField['GameGroup'] || $this->isAllValue($valueGroup)){
				$count += 1;
			}
			$valueApp = explode(",", substr($value['ListApp'],0, -1));			
			if($value['ListApp'] == "" || $this->checkAllowField($valueApp,$this->listApp) || 
				count($this->listApp) >= $numAllField['AppName'] || $this->isAllValue($valueApp)){
				$count += 1;
			}
			if($count >= 3){
				array_push($allowData, $value);				
			}
			// if($value['ListGameGroup'] == 'All,'){
			// 	dd(count($this->listApp)." ".count($this->listGameGroup)." ".count($this->listCountry));
			// }
			if(count($allowData) >= 20) break;
		}
        $table = $this->_createGridData_html($allowData, $option);

        return $table;
	}
	
	function getListGroupGame(){
		$sql = "select distinct GameGroup from GSNConfig.AppName where GameGroup is not null and GameGroup!=''";

		$listGameGroup = array();
        $stmt = $this->pdoReport->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            $listGameGroup[] = $row['GameGroup'];
		return $this->AllowGameGroup101;
	}

	function getListCountry(){
		$sql = "select distinct Country from GSNConfig.AppName where Country is not null and Country!=''";

		$listGameGroup = array();
        $stmt = $this->pdoReport->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            $listGameGroup[] = $row['Country'];
		return $this->AllowCountry101;
	}

	function addNote($data) {
		$sql = 'insert into notehistory (ShortNote, FullNote, FromDate, ToDate, ListApp, ListGameGroup, ListCountry, UserInput) values ';
		$sql .= "(".$this->pdoAuthen->quote($data[0]).", ".
						$this->pdoAuthen->quote($data[1]).", ".
						$this->pdoAuthen->quote($data[2]).", ".
						$this->pdoAuthen->quote($data[3]).", ".
						$this->pdoAuthen->quote($data[4]).", ".
						$this->pdoAuthen->quote($data[5]).", ".
						$this->pdoAuthen->quote($data[6]).", ".						
						$this->pdoAuthen->quote($this->username).") ";		
		$sql = rtrim($sql, ', ');
		// echo $sql;exit;
		$this->queryPDOSQL($this->pdoAuthen,$sql);
	}

	function checkAllowField($dataCheck,$dataAllow){			
		foreach ($dataCheck as $value) {
			if(!in_array($value,$dataAllow)){
				return false;
			}
		}
		return true;
	}

	function isAllValue($value){
		if(in_array('All_App', $value)|| in_array('All_Group', $value) ||  in_array('All_Country', $value)){
			return true;
		}
		return false;
	}

	function checkShowAll(){

	}

	function listCountryGroup(){
		$listAppStr = "'".implode("','", $this->listApp)."'";
		$sql = "select ToAppName, GameGroup, Country from GSNConfig.AppName
					where ToAppName in (".$listAppStr.") ";
		
		$data = $this->getDataPDOSQL($this->pdoAuthen,$sql);
		$this->mapAppCountry = array();
		$this->mapAppGroup = array();
		foreach($data as $value){
			$this->mapAppCountry[$value['ToAppName']] = $value['Country'];			
			$this->mapAppGroup[$value['ToAppName']] = $value['GameGroup'];
		}		
	}

}
