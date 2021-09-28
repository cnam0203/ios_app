<?php

namespace App\Http\Controllers\Account;
use View, Request, PDO, URL, Redirect, Session;
use App\Http\Controllers\Chart2018;

class AccRemarkList extends Chart2018 {
	
	public function index ($type='') {		
		$this->listAllow101();

		$this->listGroup = $this->getListGroupGame();
		$this->listCountry = $this->getListCountry();		
		$this->listApp = $this->AllowGameCode101;
		$this->canEditAll = false;
		$sql = "select count(distinct ToAppName) AppName, count(distinct GameGroup) GameGroup, count(distinct Country)Country from GSNConfig.AppName";
		$this->numAllField = $this->getDataSQL($sql);
		$this->numAllField = $this->numAllField[0];		


		if($this->numAllField['AppName'] == count($this->listApp)){
			array_unshift($this->listApp, 'All_App');
		}
		if($this->numAllField['GameGroup'] == count($this->listGroup)){
			array_unshift($this->listGroup, 'All_Group');
		}
		if($this->numAllField['Country'] == count($this->listCountry)){
			array_unshift($this->listCountry, 'All_Country');
		}

		if ($_GET['action'] == 'saveChange'){ //save after edit one row						
			if(strlen($_POST['ShortNote']) < 5){
				$messageSucc="Short Note must contain at least 8 characters";				
				$messageSucc="Note phải có tối thiểu 5 ký tự";								
			}else if(!$this->checkTwoTime($_POST['FromTime'],$_POST['ToTime'])){
				$messageSucc="FromDate must be smaller than ToDate";
				$messageSucc="FromDate phải nhỏ hơn hoặc bằng ToDate";
			}else if(count($_POST['EditApp']) == 0 && count($_POST['EditGroup']) == 0 && count($_POST['EditCountry']) == 0){
				$messageSucc="Must chose at least one Field";
				$messageSucc="Phải chọn ít nhất 1 ô trong App Name HOẶC Game Group HOẶC Country";
			}else{
				$arrAppName = $_POST['EditApp'];
				$arrGroupGame = $_POST['EditGroup'];
				$arrCountry = $_POST['EditCountry'];
				if(in_array("All_App", $arrAppName)){
					$arrAppName = array("All_App");
				}
				if(in_array("All_Group", $arrGroupGame)){
					$arrGroupGame = array("All_Group");
				}
				if(in_array("All_Country", $arrCountry)){
					$arrCountry = array("All_Country");
				}

				$strApp = implode(",", $arrAppName);
				if(count($arrAppName) > 0) $strApp.=",";
				$strGroup = implode(",", $arrGroupGame);
				if(count($arrGroupGame) > 0) $strGroup.=",";
				$strCountry = implode(",", $arrCountry);
				if(count($arrCountry) > 0) $strCountry.=",";
				$sql = "update notehistory set ShortNote=".$this->pdoAuthen->quote($_POST['ShortNote']).
								", FullNote=".$this->pdoAuthen->quote($_POST['FullNote']).
								", FromDate=".$this->pdoAuthen->quote($_POST['FromTime']).
								", ToDate=".$this->pdoAuthen->quote($_POST['ToTime']).
								", ListApp=".$this->pdoAuthen->quote($strApp).
								", ListGameGroup=".$this->pdoAuthen->quote($strGroup).
								", ListCountry=".$this->pdoAuthen->quote($strCountry).								
								", UserInput=".$this->pdoAuthen->quote($this->username).
								", RecordDate=current_timestamp".
								" where NoteID=".$this->pdoAuthen->quote($_POST['NoteID'])."";
				$this->queryPDOSQL($this->pdoAuthen,$sql);
				$messageSucc = 'Change successful.';
				// $this->load->helper('url');
				header("LOCATION: ".$this->getCurrentURL());
			}			 
		}else if ($_GET['action'] == 'delete') { // delete one row						
			// $sql = "delete from gsn_report_ggauthen.notehistory where NoteID = ".$this->pdoAuthen->quote($_GET['ID'])."";			
			$sql = "update notehistory set IsDelete=1 where NoteID = ".$this->pdoAuthen->quote($_GET['ID'])."";
			$this->queryPDOSQL($this->pdoAuthen,$sql);
			$messageSucc = 'Delete successful.';			
		}

		if(isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'saveChange' || $_GET['action'] == 'cancel') &&
			(strlen(Request::input('ToDate'))==0 && strlen(Request::input('FromDate'))==0)){	
			//have action and have no input, then get data from old input		

			if(strlen(Request::old('ToDate'))!=0 && strlen(Request::old('FromDate'))!=0){		
			//old action, don't have input new action		
				$this->fromDate = Request::old('FromDate');
				$this->toDate = Request::old('ToDate');
				$date[] = $this->fromDate;
				$date[] = $this->toDate;
				$this->date = $date;

				$arrCountry = Request::old('SelectCountry');
				$arrApp = Request::old('SelectApp');
				$arrGroup = Request::old('SelectGroup');
				$this->shortNote=Request::old('InputNote');
				$this->fullNote=Request::old('InputFullNote');

				Request::merge(['FromDate' => $this->fromDate,'ToDate'=>$this->toDate,'SelectCountry'=>$arrCountry,'SelectApp'=>$arrApp,'SelectGroup'=>$arrGroup,'InputNote'=>$this->shortNote,'InputFullNote'=>$this->fullNote]);			
			} 			
		}else{
			$arrCountry = Request::input('SelectCountry');
			$arrApp = Request::input('SelectApp');
			$arrGroup = Request::input('SelectGroup');
			$this->shortNote=Request::input('InputNote');
			$this->fullNote=Request::input('InputFullNote');

			if(strlen(Request::input('ToDate'))==0 && strlen(Request::input('FromDate'))==0 && Request::old('isold')){
				$this->fromDate = Request::old('FromDate');
				$this->toDate = Request::old('ToDate');
				$date[] = $this->fromDate;
				$date[] = $this->toDate;
				$this->date = $date;

				$this->shortNote=Request::old('InputNote');
				$this->fullNote=Request::old('InputFullNote');
				$arrCountry = Request::old('SelectCountry');
				$arrApp = Request::old('SelectApp');
				$arrGroup = Request::old('SelectGroup');

				Request::merge(['isold'=>'true','FromDate' => $this->fromDate,'ToDate'=>$this->toDate, 'InputNote'=>$this->shortNote,'InputFullNote'=>$this->fullNote,'SelectCountry'=>$arrCountry,'SelectApp'=>$arrApp,'SelectGroup'=>$arrGroup]);
			}			
		}

						
		$this->editApp = "";
		$this->editGroup = "";
		$this->editCountry = "";
		// $this->selectCountry = array();
		
		$this->selectCountry = implode(",", $arrCountry);
		$this->selectCountry = $arrCountry;
		$inputCountry = implode("','", $arrCountry);

		$this->selectApp = implode(",", $arrApp);
		$this->selectApp = $arrApp;
		$inputApp = implode("','", $arrApp);

		$this->selectGroup = implode(",", $arrGroup);
		$this->selectGroup = $arrGroup;
		$inputGroup = implode("','", $arrGroup);				
		// dd(Request::old());
		Request::flash();

		if($_GET['action'] == 'delete' || $_GET['action'] == 'cancel'){
			//if action = delete or cancel, redirect to note page, save input information for new page
			Request::merge(['isold'=>'true','FromDate' => $this->fromDate,'ToDate'=>$this->toDate,'SelectCountry'=>$arrCountry,'SelectApp'=>$arrApp,'SelectGroup'=>$arrGroup,'InputNote'=>$this->shortNote,'InputFullNote'=>$this->fullNote]);
			Request::flash();			
			return Redirect::to($this->getCurrentURL())->withInput(['isold'=>'true','FromDate' => $this->fromDate,'ToDate'=>$this->toDate,'SelectCountry'=>$arrCountry,'SelectApp'=>$arrApp,'SelectGroup'=>$arrGroup,'InputNote'=>$this->shortNote,'InputFullNote'=>$this->fullNote]);
		}		
		
		return View::make('pages.AccRemarkList',array(
					'pagetitle' => 'Remark List',
					'menu' => $this->menu,
					'date' => $this->date,
					'selectedApp' => $this->AppName,
					'listApp'=> $this->listApp,
					'listGroup' => $this->listGroup,
					'listCountry' => $this->listCountry,
					'inputNote' => $this->shortNote,
					'inputFullNote' => $this->fullNote,
					'selectedCountry' => $inputCountry,
					'selectedApp' => $inputApp,
					'selectedGroup' => $inputGroup,
					'editablerows' => $this->editablerows(),
					'editApp'=>$this->editApp,
					'editGroup'=>$this->editGroup,
					'editCountry'=>$this->editCountry,									
					'messageSucc' => $messageSucc,
				));
	}

	function getCurrentURL(){
		$currURL = URL::current();		
		return $currURL;
	}

	function checkTwoTime($strFrom, $strTo){
		$cvFrom = strtotime($strFrom);
		$cvTo = strtotime($strTo);
		if($cvFrom > $cvTo){
			return false;
		}
		return true;
	}

	function checkInArray($arrRef, $arrCheck, &$itemWrong){
		foreach ($arrCheck as $item) {
			if(!in_array($item, $arrRef)){
				$itemWrong=$item;
				return false;
			}
		}
		return true;
	}

	function editablerows(){		

		$conStr = '';
		foreach ($this->selectApp as $value) {
			$conStr .= " ListApp like ".$this->quote("%".$value.",%")." or ";
		}
		foreach ($this->selectGroup as $value) {
			$conStr .= " ListGameGroup like ".$this->quote("%".$value.",%")." or ";
		}
		foreach ($this->selectCountry as $value) {
			$conStr .= " ListCountry like ".$this->quote("%".$value.",%")." or ";
		}
		if(strlen($conStr) > 0){
			$conStr = substr($conStr, 0, -3);			
		}else{
			$conStr = ' 1=1 ';
		}

		$sql = "select NoteID, ShortNote, FullNote, FromDate, ToDate, ListApp, ListGameGroup, ListCountry, UserInput, RecordDate from notehistory
					where FromDate > ".$this->quote($this->fromDate)." and ToDate < ".$this->quote($this->toDate)." + interval 1 day and ShortNote like ".$this->quote("%".$this->shortNote."%")." and FullNote like ".$this->quote("%".$this->fullNote."%")." and ".$conStr." and IsDelete=0 order by FromDate";
		$data = $this->getDataPDOSQL($this->pdoAuthen,$sql);		
		$data = $this->getAllowData($data);				

  //   	$sql = "select RecordID, ReportDate, Product, Market, Channel, Platform, `Install`, Cost, CPI, Notes, UserInput, RecordDate   from marketing.CPI   where  ReportDate between ".$this->quote($this->fromDate)." and ".$this->quote($this->toDate)." order by ReportDate, Product, Market, Channel, Platform";
		// $data = $this->getDataSQL($sql);
		
		$html = '<table border="1" align="center" style="border-collapse:collapse" width="90%" id="tableid_RemarkNote">'.
				'<tr><th>Short Note</th><th>Full Note</th><th>From</th><th>To</th><th>List App</th><th>List Game Group</th><th>List Country</th><th>User Input</th><th>RecordDate</th><th>  </th></tr>';
		$count = 0;
		foreach ($data as $row) {
			$this->checkAllowEdit($row['ListApp'], $row['ListGameGroup'], $row['ListCountry']);
			if (isset($_GET['action']) && ($_GET['action'] == 'edit') && ($row['NoteID'] == $_GET['ID'])) {
				$html .= '<tr>';
				$html .= '<form method="post" action="?action=saveChange">';
				$html .= ' <input type=hidden name="_token" value="'.Session::token().'" /> ';
				$html .= '<input type=hidden name="NoteID" value="'.$row['NoteID'].'"/>';
				$html .= '<td><input type="text" name="ShortNote" size=20 value="'.htmlentities($row['ShortNote']).'"></td>';
				$html .= '<td><textarea name="FullNote" cols=40 rows=3>'.htmlentities($row['FullNote']).'</textarea></td>';
				$html .= '<td><input class="flatpickr" type="text" id="FromTime" name="FromTime" size=18 value="'.$row['FromDate'].'"/></td>';
				$html .= '<td><input class="flatpickr" type="text" id="ToTime" name="ToTime" size=18 value="'.$row['ToDate'].'"/></td>';
				$html .= '<td>'.$this->generatePickListApp($row['ListApp']).'</td>';
				$html .= '<td>'.$this->generatePickListGroup($row['ListGameGroup']).'</td>';
				$html .= '<td>'.$this->generatePickListCountry($row['ListCountry']).'</td>';
				
				$html .= '<td>'.htmlentities($row['UserInput']).'</td>';
				$html .= '<td>'.substr($row['RecordDate'],0,16).'</td>';
				
				$html .= '<td align=center><a href="?action=delete&ID='.$row['NoteID'].'" onclick="return confirm(\'Are you sure ?\');">delete</a></td>';
				$html .= '<td align=center><input type=submit value=Save><button type=button onclick="window.top.location.href = \''.URL::current().'?action=cancel\'">Cancel</button></td>';				
				$html .= '</form>';
				$html .= '</tr>';
			}else{
				$html .= "<tr>";
				$html .= '<td>'.htmlentities($row['ShortNote']).'</td>';
				$html .= '<td>'.htmlentities($row['FullNote']).'</td>';
				$html .= '<td>'.htmlentities($row['FromDate']).'</td>';
				$html .= '<td>'.htmlentities($row['ToDate']).'</td>';
				$html .= '<td>'.htmlentities($row['ListApp']).'</td>';
				$html .= '<td>'.htmlentities($row['ListGameGroup']).'</td>';
				$html .= '<td>'.htmlentities($row['ListCountry']).'</td>';
				$html .= '<td>'.htmlentities($row['UserInput']).'</td>';
				$html .= '<td>'.substr($row['RecordDate'],0,16).'</td>';				
				if($this->checkAllowEdit($row['ListApp'], $row['ListGameGroup'], $row['ListCountry'])){					
					$html .= '<td align=center><a href="?action=delete&ID='.$row['NoteID'].'" onclick="return confirm(\'Are you sure ?\');">delete</a></td>';
					$html .= '<td><a href="?action=edit&ID='.$row['NoteID'].'">edit</td>';			
				}else{					
					$html .= '<td align=center> </td>';
					$html .= '<td> </td>';	
				}

				$html .= '</tr>';
			}
		}
		$html .= '</table>';
		return $html;
    }

    function generatePickListApp($app){
    	$htmlText = ' <select data-placeholder="App Name" multiple class="chosen-select" id="EditApp" name="EditApp[]" "> ';
    	foreach ($this->listApp as $value) {
    		$htmlText .= '<option id = "editapp-'.$value.'" value="'.$value.'">'.$value.'</option> ';
    	}
    	$htmlText .= ' </select> ';
		$this->editApp = str_replace(",", "','", $app);		
        return $htmlText;
    }

    function generatePickListGroup($group){
    	$htmlText = ' <select data-placeholder="Game Group" multiple class="chosen-select" id="EditGroup" name="EditGroup[]" "> ';
    	foreach ($this->listGroup as $value) {
    		$htmlText .= '<option id = "editgroup-'.$value.'" value="'.$value.'">'.$value.'</option> ';
    	}
    	$htmlText .= ' </select> ';
		$this->editGroup = str_replace(",", "','", $group);		
        return $htmlText;
    }

    function generatePickListCountry($country){
    	$htmlText = ' <select data-placeholder="Country" multiple class="chosen-select" id="EditCountry" name="EditCountry[]" "> ';
    	foreach ($this->listCountry as $value) {
    		$htmlText .= '<option id = "editcountry-'.$value.'" value="'.$value.'">'.$value.'</option> ';
    	}
    	$htmlText .= ' </select> ';
		$this->editCountry = str_replace(",", "','", $country);		
        return $htmlText;
    }

    function getTableHtml($data){
    	$html="";
    	$count=0;
    	foreach ($data as $row) {
			$count += 1;
			$html .= "<tr id='row_".$count."' >";
			$html .= '<td>'.htmlentities($row['ShortNote']).'</td>';
			$html .= '<td>'.htmlentities($row['FullNote']).'</td>';
			$html .= '<td>'.htmlentities($row['FromDate']).'</td>';
			$html .= '<td>'.htmlentities($row['ToDate']).'</td>';
			$html .= '<td>'.htmlentities($row['ListApp']).'</td>';
			$html .= '<td>'.htmlentities($row['ListGameGroup']).'</td>';
			$html .= '<td>'.htmlentities($row['ListCountry']).'</td>';
			$html .= '<td>'.htmlentities($row['UserInput']).'</td>';
			$html .= '<td>'.substr($row['RecordDate'],0,16).'</td>';
			$html .= '<td><a href="?action=edit&ID='.$row['NoteID'].'"><input type="button" value="edit"></td>';
			// $html .= '<td>'.substr($row['RecordDate'],0,16).'</td>';
			// $html .= '<td align=center><a href="?action=delete&ID='.$row['RecordID'].'" onclick="return confirm(\'Are you sure ?\');">delete</a></td>';
			// $html .= '<td align=center><a href="?action=edit&ID='.$row['RecordID'].'">edit</a></td>';
			$html .= '</tr>';
		}
		return $html;
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

		$listCountry = array();
        $stmt = $this->pdoReport->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            $listCountry[] = $row['Country'];
		return $this->AllowCountry101;
	}

	function getListUserInput(){
		$sql = "select distinct UserInput from notehistory";
		$listUser = array();
        $stmt = $this->pdoAuthen->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            $listUser[] = $row['UserInput'];
		return $listUser;
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

	function getAllowData($data){
		$allowData = array();		
		foreach ($data as $value) {			
			$count = 0;			
			$valueCountry = explode(",", substr($value['ListCountry'],0, -1));					
			if($value['ListCountry'] == "" || $this->checkAllowField($valueCountry,$this->listCountry) || 
				count($this->listCountry) >= $this->numAllField['Country'] || $this->isAllValue($valueCountry)){
				$count += 1;
			}			
			$valueGroup = explode(",", substr($value['ListGameGroup'],0, -1));			
			if($value['ListGameGroup'] == "" || $this->checkAllowField($valueGroup,$this->listGroup) || 
				count($this->listGroup) >= $this->numAllField['GameGroup'] || $this->isAllValue($valueGroup)){
				// dd(count($this->listCountry));
				$count += 1;
			}

			$valueApp = explode(",", substr($value['ListApp'],0, -1));						
			if($value['ListApp'] == "" || $this->checkAllowField($valueApp,$this->listApp) || 
				count($this->listApp) >= $this->numAllField['AppName'] || $this->isAllValue($valueApp)){
				$count += 1;
			}
			if($count >= 3){
				array_push($allowData, $value);				
			}			
		}
		return $allowData;
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

	function isAllValueStr($value){		
		if(strpos($value, 'All_App') !== false || strpos($value, 'All_Group') !== false || strpos($value, 'All_Country') !== false){			
			return true;
		}
		return false;
	}

	function checkCanEditAll(){		
		if(count($this->listApp) < $this->numAllField['AppName'] || count($this->listCountry) < $this->numAllField['Country'] || 
			count($this->listGroup) < $this->numAllField['ListGameGroup']){
			$this->canEditAll = false;
			return false;
		}
		$this->canEditAll = true;
		return true;
	}

	function checkAllowEdit($strApp, $strGroup, $strCountry){
		//check legit app
		$appAnno = explode(",", substr($strApp, 0,-1));
		$groupAnno = explode(",", substr($strGroup, 0,-1));
		$countryAnno = explode(",", substr($strCountry, 0,-1));				
		if($this->isAllValueStr($strApp) || $this->isAllValueStr($strGroup) || $this->isAllValueStr($strCountry)){			
			return $this->checkCanEditAll();
		}		

		if(strlen($strApp) > 0){			
			foreach ($appAnno as $key => $value) {
				if(!in_array($value, $this->listApp)){					
					return false;
				}			
			}		
		}
		if(strlen($strGroup) > 0){
			foreach ($groupAnno as $key => $value) {
				if(!in_array($value, $this->listGroup)){										
					return false;
				}			
			}		
		}
		if(strlen($strCountry) > 0){
			foreach ($countryAnno as $key => $value) {
				if(!in_array($value, $this->listCountry)){				
					return false;
				}
			}
		}

		return true;
	}


}
