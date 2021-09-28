<?php

namespace App\Http\Controllers;
use DB, Request, PDO, Auth;
use App\User;

	
class Base2018 extends Controller {

	// const URLIDFor101 = 234; // http://tracking.playzing.g6.zing.vn:8080/gsnreport/accactive
	var $IsAdmin = 0;
	var $AllowRules101 = array();
	var $AllowGameCode101 = array();
	var $AllowGameGroup101 = array();
	var $AllowCountry101 = array();
	
	// dropdown filter config
    // const menuNames = [];
    // const minMenuHeight = 4;
    // const maxLevel = 3;
    
    public function __construct($ListAppTable='', $AppField='AppName', $AddFilterToListApp = '', $conn='pdoReportTool') {
		if ($AppField == '')
			$AppField = 'AppName';

		$this->username = Request::get('user')['email'];
		
		// db connection
		$this->pdoReportTool = DB::connection()->getPdo();		
		$this->pdoAuthen = DB::connection('mysql_authen')->getPdo();
		$this->pdoIngame = DB::connection()->getPdo();
		$this->insertPdo = DB::connection()->getPdo();

		$this->listApp = $this->getAppList($ListAppTable, $AppField, $conn);
		
		// permission (AppName)
		if (Request::query('AppName'))
			$this->AppName = Request::query('AppName');
		else 
			$this->AppName = $this->listApp[0];
		
		$this->date = $this->getInputDate();
		// $this->menu = $this->getMenu();
    }
	
    public function __destruct() {
        $this->pdoAuthen = NULL;
        $this->pdoReportTool = NULL;
        $this->pdoIngame = NULL;
		$this->insertPdo = NULL;
    }
	
	// -------------------------------- Permission ------------------------- -----------------------------------------------------------
	
	function getAppList ($ListAppTable, $AppField, $conn) {
		$sql = 'select distinct ToAppName AppName from GSNConfig.AppName order by AppName';
		
		$listApp = array();		
        $stmt = $this->$conn->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            $listApp[] = $row['AppName'];
		return $listApp;
	}
	
    function getMenu() {
		$menu = $this->getMenu_data($this->username);
        $menu = $this->createMenu($menu);
		
		// log in as
		if ($_SESSION['username_back'] != '')
			$user_back = $_SESSION['username'];
		else
			$user_back = '';
		
		return array($user_back,$menu);
    }

    protected function getMenu_data ($user) {
		$sql = "select distinct UriId from groups where UriId>0 ".
					" union select UriId from userright where UserId = '".$user."' and UriId > 0";
		
		$stmt = $this->pdoAuthen->query($sql);
		$listUriId = '';
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$listUriId .= $row['UriId'] . ",";
		}

		$listUriId = rtrim($listUriId,", ");
		$sql = "select 		case when lv0 is null and lv1 is null and lv2 is null then lv3 when lv0 is null and lv1 is null then lv2 when lv0 is null then lv1 else lv0 end as lv0,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then lv3 when lv0 is null then lv2 else lv1 end as lv1,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then null when lv0 is null then lv3 else lv2 end as lv2,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then null when lv0 is null then null else lv3 end as lv3,
				case when lv0 is null and lv1 is null and lv2 is null then Order3 when lv0 is null and lv1 is null then Order2 when lv0 is null then Order1 else Order0 end as Orderlv0,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then Order3 when lv0 is null then Order2 else Order1 end as Orderlv1,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then null when lv0 is null then Order3 else Order2 end as Orderlv2,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then null when lv0 is null then null else Order3 end as Orderlv3
					from (select a.MenuId as lv3, b.MenuId as lv2, c.MenuId as lv1, d.MenuId as lv0,
								a.Order as Order3, b.Order as Order2, c.Order as Order1, d.Order as Order0
							from menus a 
								left join menus b on a.ParentId=b.MenuId 
								left join menus c on b.ParentId=c.MenuId 
								left join menus d on c.ParentId=d.MenuId
							where a.UriId in ($listUriId) ) a
					order by Orderlv0, Orderlv1, Orderlv2, Orderlv3";
		$stmt = $this->pdoAuthen->query($sql);

		$menu = array();
		$listMenuId = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['lv3']) {
                $listMenuId[$row['lv3']] = [];
                $listMenuId[$row['lv2']] = [];
                $listMenuId[$row['lv1']] = [];
                $listMenuId[$row['lv0']] = [];
				if (!is_array($menu[$row['lv0']]['sub'][$row['lv1']]['sub'][$row['lv2']]))
					$menu[$row['lv0']][$row['lv1']][$row['lv2']] = array();
				$menu[$row['lv0']]['sub'][$row['lv1']]['sub'][$row['lv2']]['sub'][$row['lv3']] = [];
			} else if ($row['lv2']) {
                $listMenuId[$row['lv2']] = [];
                $listMenuId[$row['lv1']] = [];
                $listMenuId[$row['lv0']] = [];
				if (!is_array($menu[$row['lv0']]['sub'][$row['lv1']]))
					$menu[$row['lv0']]['sub'][$row['lv1']] = array();
				$menu[$row['lv0']]['sub'][$row['lv1']]['sub'][$row['lv2']] = [];
			} else if ($row['lv1']) {
                $listMenuId[$row['lv1']] = [];
                $listMenuId[$row['lv0']] = [];
				if (!is_array($menu[$row['lv0']]))
					$menu[$row['lv0']] = array();
				$menu[$row['lv0']]['sub'][$row['lv1']] = [];
			} else{
                $listMenuId[$row['lv0']] = [];
                $menu[$row['lv0']] = [];
            }
		}
		$uriData = $this->getUriData(array_keys($listMenuId));
		$recentUri = $this->getAccessHistoryData(self::maxRecentMenuLength);
        $this->createMenuData($menu, $uriData, $recentUri);
		return $menu;
	}

	function createMenu ($aList) {
        $menu = '<nav> <a href="#"><i class="fa fa-bars fa-2x"></i></a>';
        $menu .= '<ul class="menu-bar">';
        $i=0;
        if (is_array($aList)) {
            foreach ($aList as $k => $aLv1) {
                $menu .= '<li class="dropdown menu-item"><a href="'.$aLv1['data']['Uri'].'">'.$aLv1['data']['MenuName'].'</a>';
                if (is_array($aLv1['sub'])) {
                    $menu .= '
                    <ul class="dropdown-menu">
                    ';
                    foreach ($aLv1['sub'] as $k2 => $aLv2) {
                        if (is_array($aLv2['sub'])) {
                            $menu .= '<li class="menu-item dropdown"><a href="'.$aLv2['data']['Uri'].'">'.$aLv2['data']['MenuName'].'</a>';
                            $menu .= '
                            <ul class="dropdown-menu dropdown-right">
                            ';
                            foreach ($aLv2['sub'] as $k3 => $aLv3 ) {
                                if (is_array($aLv3['sub'])) {
                                    $menu .= '<li class="menu-item dropdown"><a href="'.$aLv3['data']['Uri'].'">'.$aLv3['data']['MenuName'].'</a>';
                                    $menu .= '
                                    <ul class="dropdown-menu dropdown-right">
                                    ';
                                    foreach ($aLv3['sub'] as $k4 => $aLv4) {
                                        $tmp = $this->getMenuNameUri($k4);
                                        $menu .= '<li class="menu-item" id="i'.$i.'"><a href="'.$aLv4['data']['Uri'].'">'.$aLv4['data']['MenuName'].'</a>';
                                        $menu .= '</li>
                                        ';
                                        $i++;
                                    }
                                    $menu .= '</ul>
                                    ';
                                }
                                else
                                    $menu .= '<li class="menu-item"><a href="'.$aLv3['data']['Uri'].'">'.$aLv3['data']['MenuName'].'</a>';
                                $menu .= '</li>
                                ';
                            }
                            $menu .= '</ul>
                            ';
                        }
                        else
                            $menu .= '<li class="menu-item"><a href="'.$aLv2['data']['Uri'].'">'.$aLv2['data']['MenuName'].'</a>';
                        $menu .= '</li>
                        ';
                    }
                    $menu .= '</ul>
                    ';
                }
                $menu .= '</li>
                ';
            }
        }

        $menu .= "<li class='dropdown' id='search-menu'><input type='search' placeholder='Search..' class='search-filter menu-filter' id='search-bar' ></li>";
        $menu .= '</ul>';
        $menu .= '</nav>';
        return $menu;
    }
	
	protected function getMenuNameUri ($menuid) {		
		$sql = "select m.*, u.Uri from menus m  left join uri u on u.UriId = m.UriId  where m.MenuId = '".$menuid."'";
		$stmt = $this->pdoAuthen->prepare($sql);
		$result = $stmt->execute();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$menu['MenuName'] = $row['MenuName'];
			$menu['Uri'] = $row['Uri'];
			$menu['UriId'] = $row['UriId'];
			if ($menu['UriId'] == 21)
				$menu['Uri'] = '#';
		}
		$stmt = NULL;
		return $menu;
	}

	protected function getUriData($listMenuId){

        $sql = "select m.*, u.Uri from menus m  left join uri u on u.UriId = m.UriId  where m.MenuId IN (".join(',',$listMenuId).")";
        $stmt = $this->pdoAuthen->prepare($sql);
        $result = $stmt->execute();
        $menu = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $menu[$row['MenuId']]['MenuName'] = $row['MenuName'];
            $menu[$row['MenuId']]['Uri'] = $row['Uri'];
            $menu[$row['MenuId']]['UriId'] = $row['UriId'];
            if ($menu[$row['MenuId']]['UriId'] == 21)
                $menu[$row['MenuId']]['Uri'] = '#';
        }
        $stmt = NULL;
        return $menu;
    }
	

	// add MenuFilter to dropdown-menu
	private function addMenuFilterScript(&$menu, $menuName, $menuItems, $level=1){
        if (in_array($menuName, self::menuNames) && count($menuItems) >= self::minMenuHeight && $level<=self::maxLevel)
            $menu .= "<input type='search' placeholder='Search..' class='dropdown-filter' >";
    }

    function createTabScript(){
        $script = '';
        if (property_exists($this, "tabData")){
            if (gettype($this->tabData)==='array' && count($this->tabData)>0){
                $url = Request::url();
                $script = '<div class="nav nav-tabs" id="nav-tab" style="z-index: 999">';
                foreach ($this->tabData as $tab){
                    $script .= '<a class="nav-item nav-link '.($tab[1]===$url?'active':'').'" href="'.$tab[1].'" >'.$tab[0].'</a>';
                }
                $script .= '</div>';
            }
        }
        return $script;
    }

    // add access history menu
    
    function updateAccessHistory(){
        $sql = "INSERT INTO ".self::tbAccessHistory."(UserID, URI, Quantity, LastTime)
                VALUES (".$this->quote($this->username).", ".$this->quote(Request::url()).", 1, NOW())
                ON DUPLICATE KEY UPDATE Quantity = IF(LastTime > SUBDATE(NOW(), INTERVAL 30 DAY), Quantity+1, 1), LastTime=NOW();
                ";
        $stmt = $this->insertPdo->query($sql);
        $result = $stmt->fetchall(PDO::FETCH_ASSOC);
    }

    function getAccessHistoryData($max=10){
        $max = min($max, 20);
        $sql = "SELECT Uri 
        FROM ".self::tbAccessHistory." 
        WHERE UserID=".$this->quote($this->username)." and LastTime > SUBDATE(CURDATE(), INTERVAL 30 DAY)
        ORDER BY Quantity desc, LastTime desc LIMIT ".$max;

        $stmt = $this->pdoReportTool->query($sql);
        $listUri = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $listUri[$this->shortenUrl($row['Uri'])] = 1;
        }
        return $listUri;
    }

    function createMenuData(&$menu, $uriData, $recentUri){
        foreach ($menu as $lv0 => $dataLv0){
            if (isset($uriData[$lv0])){
                $menu[$lv0]['data'] = $uriData[$lv0];
                $url = $this->shortenUrl($uriData[$lv0]["Uri"]);
                if (isset($recentUri[$url])){
                    $recentUri[$url] = [
                        'MenuName'=>$uriData[$lv0]['MenuName'],
                        'Uri'=>$uriData[$lv0]["Uri"]];
                }
            }
            foreach ($dataLv0['sub'] as $lv1 => $dataLv1){
                if (isset($uriData[$lv1])){
                    $menu[$lv0]['sub'][$lv1]['data'] = $uriData[$lv1];
                    $url = $this->shortenUrl($uriData[$lv1]["Uri"]);
                    if (isset($recentUri[$url])){
                        $recentUri[$url] = [
                            'MenuName'=>($uriData[$lv0]['MenuName'] . ' > ' . $uriData[$lv1]['MenuName']),
                            'Uri'=>$uriData[$lv1]["Uri"]];
                    }
                }

                foreach ($dataLv1['sub'] as $lv2 => $dataLv2){
                    if (isset($uriData[$lv2])){
                        $menu[$lv0]['sub'][$lv1]['sub'][$lv2]['data'] = $uriData[$lv2];
                        $url = $this->shortenUrl($uriData[$lv2]["Uri"]);
                        if (isset($recentUri[$url])){
                            $recentUri[$url] = [
                                'MenuName'=>($uriData[$lv0]['MenuName'] . ' > ' . $uriData[$lv1]['MenuName'] . ' > ' . $uriData[$lv2]['MenuName']),
                                'Uri'=>$uriData[$lv2]["Uri"]];
                        }
                    }
                    foreach ($dataLv2['sub'] as $lv3 => $dataLv3){
                        if (isset($uriData[$lv3])){
                            $menu[$lv0]['sub'][$lv1]['sub'][$lv2]['sub'][$lv3]['data'] = $uriData[$lv3];
                            $url = $this->shortenUrl($uriData[$lv3]["Uri"]);
                            if (isset($recentUri[$url])){
                                $recentUri[$url] = [
                                    'MenuName'=>($uriData[$lv0]['MenuName'] . ' > ' . $uriData[$lv1]['MenuName'] . ' > ' . $uriData[$lv2]['MenuName'] . ' > ' . $uriData[$lv3]['MenuName']),
                                    'Uri'=>$uriData[$lv3]["Uri"]];
                            }
                        }
                    }
                }
            }
        }

        if (count($recentUri)>0){
            $menu['recent'] = ['sub'=>[], 'data'=>['MenuName'=>'Recent', 'Uri'=>'#']];
            foreach ($recentUri as $url=>$data){
                if (is_array($data)){
                    $menu['recent']['sub'][$url]['data'] = $data;
                }
            }
        }
    }

    private function shortenUrl($url, $offset=1){
        $url = explode('//', $url);
        $url = $url[count($url)-1];
        $offset = min(count($url)-4, $offset);
        $url = explode('/',$url);
        return join('/', array_slice($url, $offset));
    }

    // old function

    function getMenu_data_v0 ($user) {
		$sql = "select distinct UriId from groups where UriId>0 ".
					" union select UriId from userright where UserId = '".$user."' and UriId > 0";
		
		$stmt = $this->pdoAuthen->query($sql);
		$listUriId = '';
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$listUriId .= $row['UriId'] . ",";
		}

		$listUriId = rtrim($listUriId,", ");
		$sql = "select 		case when lv0 is null and lv1 is null and lv2 is null then lv3 when lv0 is null and lv1 is null then lv2 when lv0 is null then lv1 else lv0 end as lv0,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then lv3 when lv0 is null then lv2 else lv1 end as lv1,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then null when lv0 is null then lv3 else lv2 end as lv2,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then null when lv0 is null then null else lv3 end as lv3,
				case when lv0 is null and lv1 is null and lv2 is null then Order3 when lv0 is null and lv1 is null then Order2 when lv0 is null then Order1 else Order0 end as Orderlv0,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then Order3 when lv0 is null then Order2 else Order1 end as Orderlv1,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then null when lv0 is null then Order3 else Order2 end as Orderlv2,
							case when lv0 is null and lv1 is null and lv2 is null then null when lv0 is null and lv1 is null then null when lv0 is null then null else Order3 end as Orderlv3
					from (select a.MenuId as lv3, b.MenuId as lv2, c.MenuId as lv1, d.MenuId as lv0,
								a.Order as Order3, b.Order as Order2, c.Order as Order1, d.Order as Order0
							from menus a 
								left join menus b on a.ParentId=b.MenuId 
								left join menus c on b.ParentId=c.MenuId 
								left join menus d on c.ParentId=d.MenuId
							where a.UriId in ($listUriId) ) a
					order by Orderlv0, Orderlv1, Orderlv2, Orderlv3";
		$stmt = $this->pdoAuthen->query($sql);

		$menu = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($row['lv3']) {
				if (!is_array($menu[$row['lv0']][$row['lv1']][$row['lv2']]))
					$menu[$row['lv0']][$row['lv1']][$row['lv2']] = array();
				$menu[$row['lv0']][$row['lv1']][$row['lv2']][$row['lv3']] = 1;
			} else if ($row['lv2']) {
				if (!is_array($menu[$row['lv0']][$row['lv1']]))
					$menu[$row['lv0']][$row['lv1']] = array();
				$menu[$row['lv0']][$row['lv1']][$row['lv2']] = 1;
			} else if ($row['lv1']) {
				if (!is_array($menu[$row['lv0']]))
					$menu[$row['lv0']] = array();
				$menu[$row['lv0']][$row['lv1']] = 1;
			} else
				$menu[$row['lv0']] = 1;
		}
		//print_r($menu);
		//exit;
		return $menu;
	}

	function createMenu_old ($aList) {
		$menu = '<nav> <a href="#"><i class="fa fa-bars fa-2x"></i></a>';
        $menu .= '<ul class="dropdown">';
		$i=0;
		// dd($menu);
        // dd($aList);
        // $menu .= '<li><a href="javascript:void(0);" style="font-size:15px;" class="icon" onclick="myFunction()">&#9776;</a></li>';
        if (is_array($aList)) {
            foreach ($aList as $k => $aLv1) {
                $tmp = $this->getMenuNameUri($k);
                $menu .= '<li><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                // $menu .= '<li><a '.($tmp['Uri']==null?'':'href="'.$tmp['Uri'].'"').'">'.$tmp['MenuName'].'</a>';
                if (is_array($aLv1)) {
                    $menu .= '
                    <ul>
                    ';
                    foreach ($aLv1 as $k2 => $aLv2) {
                        $tmp = $this->getMenuNameUri($k2);
                        $menu .= '<li><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                        // $menu .= '<li><a '.($tmp['Uri']==null?'':'href="'.$tmp['Uri'].'"').'">'.$tmp['MenuName'].'</a>';
                        if (is_array($aLv2)) {
                            $menu .= '
                            <ul>
                            ';
                            foreach ($aLv2 as $k3 => $aLv3 ) {
                                $tmp = $this->getMenuNameUri($k3);
                                $menu .= '<li><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                                // $menu .= '<li><a '.($tmp['Uri']==null?'':'href="'.$tmp['Uri'].'"').'">'.$tmp['MenuName'].'</a>';
                                if (is_array($aLv3)) {
                                    $menu .= '
                                    <ul>
                                    ';
                                    foreach ($aLv3 as $k4 => $aLv4) {
                                        $tmp = $this->getMenuNameUri($k4);
                                        $menu .= '<li id="i'.$i.'"><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                                        // $menu .= '<li><a '.($tmp['Uri']==null?'':'href="'.$tmp['Uri'].'"').'">'.$tmp['MenuName'].'</a>';
                                        $menu .= '</li>
                                        ';
                                        $i++;
                                    }
                                    $menu .= '</ul>
                                    ';
                                }
                                $menu .= '</li>
                                ';
                            }
                            $menu .= '</ul>
                            ';
                        }
                        $menu .= '</li>
                        ';
                    }
                    $menu .= '</ul>
                    ';
                }
                $menu .= '</li>
                ';
            }
        }
        $menu .= '</ul>';
        $menu .= '</nav>';
        // echo $menu; exit;
        return $menu;
	}

	function createMenu_v0 ($aList) {
		$menu = '<ul class="dropdown" >';
		$i=0;
		if (is_array($aList)) {
			foreach ($aList as $k => $aLv1) {
				$tmp = $this->getMenuNameUri($k);
				// $menu .= '<li><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
				// $menu .= '<li><a '.($tmp['Uri']==null?'':'href="'.$tmp['Uri'].'"').'">'.$tmp['MenuName'].'</a>';
				$menu .= '<li><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
				if (is_array($aLv1)) {	
					$menu .= '<ul class="sub_menu">';
					foreach ($aLv1 as $k2 => $aLv2) { 
						$tmp = $this->getMenuNameUri($k2);
						// $menu .= '<li><a '.($tmp['Uri']==null?'':'href="'.$tmp['Uri'].'"').'">'.$tmp['MenuName'].'</a>';
						$menu .= '<li><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
						if (is_array($aLv2)) {
							$menu .= '<ul>';
							foreach ($aLv2 as $k3 => $aLv3 ) {
								$tmp = $this->getMenuNameUri($k3);
								// $menu .= '<li><a '.($tmp['Uri']==null?'':'href="'.$tmp['Uri'].'"').'">'.$tmp['MenuName'].'</a>';
								$menu .= '<li><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
								if (is_array($aLv3)) {
									$menu .= '<ul>';
									foreach ($aLv3 as $k4 => $aLv4) {
										$tmp = $this->getMenuNameUri($k4);
										$menu .= '<li id="i'.$i.'"><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
										$menu .= '</li>';
										$i++;
									}
									$menu .= '</ul>';
								}
								$menu .= '</li>';
							}
							$menu .= '</ul>';
						}
						$menu .= '</li>';
					}
					$menu .= '</ul>';
				}
				$menu .= '</li>';
			}
		}
		$menu .= '</ul>';
		// echo $menu; exit;
		return $menu;
	}

	function createMenu_v1 ($aList) {
        $menu = '<nav> <a href="#"><i class="fa fa-bars fa-2x"></i></a>';
        $menu .= '<ul class="menu-bar">';
        $i=0;
        // dd($menu);
        if (is_array($aList)) {
            foreach ($aList as $k => $aLv1) {
                $tmp = $this->getMenuNameUri($k);
                $menu .= '<li class="dropdown menu-item"><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                if (is_array($aLv1)) {
                    $menu .= '
                    <ul class="dropdown-menu">
                    ';
                    $this->addMenuFilterScript($menu, $tmp['MenuName'], $aLv1, 1);
                    foreach ($aLv1 as $k2 => $aLv2) {
                        $tmp = $this->getMenuNameUri($k2);

                        if (is_array($aLv2)) {
                            $menu .= '<li class="menu-item dropdown"><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                            $menu .= '
                            <ul class="dropdown-menu dropdown-right">
                            ';
                            $this->addMenuFilterScript($menu, $tmp['MenuName'], $aLv2, 2);
                            foreach ($aLv2 as $k3 => $aLv3 ) {
                                $tmp = $this->getMenuNameUri($k3);
                                if (is_array($aLv3)) {
                                    $menu .= '<li class="menu-item dropdown"><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';

                                    $menu .= '
                                    <ul class="dropdown-menu dropdown-right">
                                    ';
                                    $this->addMenuFilterScript($menu, $tmp['MenuName'], $aLv3, 3);
                                    foreach ($aLv3 as $k4 => $aLv4) {
                                        $tmp = $this->getMenuNameUri($k4);
                                        $menu .= '<li class="menu-item" id="i'.$i.'"><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                                        $menu .= '</li>
                                        ';
                                        $i++;
                                    }
                                    $menu .= '</ul>
                                    ';
                                }
                                else
                                    $menu .= '<li class="menu-item"><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                                $menu .= '</li>
                                ';
                            }
                            $menu .= '</ul>
                            ';
                        }
                        else
                            $menu .= '<li class="menu-item"><a href="'.$tmp['Uri'].'">'.$tmp['MenuName'].'</a>';
                        $menu .= '</li>
                        ';
                    }
                    $menu .= '</ul>
                    ';
                }
                $menu .= '</li>
                ';
            }
        }

        $menu .= "<li class='dropdown' id='search-menu'><input type='search' placeholder='Search..' class='search-filter menu-filter' id='search-bar' ></li>";
        $menu .= '</ul>';
        $menu .= '</nav>';
        return $menu;
    }
	
	protected function getMenuNameUri_v0 ($menuid) {		
		$sql = "select m.*, u.Uri from menus m  left join uri u on u.UriId = m.UriId  where m.MenuId = '".$menuid."'";
		$stmt = $this->pdoAuthen->prepare($sql);
		$result = $stmt->execute();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$menu['MenuName'] = $row['MenuName'];
			$menu['Uri'] = $row['Uri'];
			$menu['UriId'] = $row['UriId'];
			if ($menu['UriId'] == 21)
				$menu['Uri'] = '#';
		}
		$stmt = NULL;
		return $menu;
	}
	
	// -------------------------------- END Permission ------------------------- -----------------------------------------------------------
	

	// -------------------------------- input/filter ------------------------- -----------------------------------------------------------
	function quote ($str) {
		return $this->pdoReportTool->quote($str);
	}
	
    function getInputDate_old($toYesterday=false, $startdays=15) {
		// from-to
        $this->fromDate = $this->madSafety(Request::input('FromDate'));
        $this->toDate = $this->madSafety(Request::input('ToDate'));

		if(!$this->fromDate || !$this->toDate) { // default
			$this->fromDate = date('Y-m-d',time() - 60 * 60 * 24 * $startdays);
			if ($toYesterday)
				$this->toDate = date('Y-m-d',time() - 60 * 60 * 24 * 1);
			else
				$this->toDate = date('Y-m-d',time());
		}
		
		$this->from = str_replace('-', '', $this->fromDate) ;
		$this->to = str_replace('-', '', $this->toDate) ;
		
		$date[] = $this->fromDate;
		$date[] = $this->toDate;
		return $date;
	}

	function getInputDate($toYesterday=false, $startdays=15) {
		$this->fromDate = $this->madSafety(Request::query('FromDate'));
		$this->toDate = $this->madSafety(Request::query('ToDate'));
        if(!$this->fromDate || !$this->toDate) { // default
            $this->fromDate = date('Y-m-d',time() - 60 * 60 * 24 * $startdays);
            if ($toYesterday)
                $this->toDate = date('Y-m-d',time() - 60 * 60 * 24 * 1);
            else
                $this->toDate = date('Y-m-d',time());
        }

        $this->from = str_replace('-', '', $this->fromDate) ;
        $this->to = str_replace('-', '', $this->toDate) ;

        $date[] = $this->fromDate;
        $date[] = $this->toDate;

        return $date;
    }
	
	function getMonthList () {		
		$cDate = strtotime("2016-01");
		$fDate = time();
		$from = array(date("Y-m", $cDate));
		$to = array(date("Y-m", $cDate));
		while ($cDate <= $fDate) {
			$cDate = strtotime("+1 month", $cDate);
			// dd($cDate);
			array_push($from, date("Y-m", $cDate));
			array_push($to, date("Y-m", $cDate));
		}        
        return array($from,$to);        
    }

    function getFromToMonth() {
        $this->fromDate = Request::input('FromDate');
        $this->toDate = Request::input('ToDate');

         if(!$this->fromDate || !$this->toDate) {                
             $date = $this->getDefaultMonth();
             $this->fromDate = $date[0];
             $this->toDate = $date[1];
			 
         } else {
             $date[] = $this->fromDate;
             $date[] = $this->toDate;
         }
         return $date;
    }

    protected function getDefaultMonth() {
        $fromDate = date('Y-m',time() - 60 * 60 * 24 * 210);
        $toDate = date('Y-m',time());
        return array($fromDate,$toDate);
    }
	// -------------------------------- END input/filter ------------------------- -----------------------------------------------------------
	
	
	// -------------------------------- general ------------------------- -----------------------------------------------------------
	function formatXDate() {
		if (substr($this->fromDate,0,4) != substr($this->toDate,0,4))
			return '%Y-%m-%d';
		return '%m-%d';
	}
	
	function madSafety($string) {
		$string = stripslashes($string);
		$string = strip_tags($string);
		$string = addslashes($string);
		return $string;
	}
	
	/*	2017-10-20
		option = array ('datatype' => array (ColName => type),			// default=string/number.  DEC0, DEC1, DEC2, PERCENT, 100PERCENT0
						'color' => array (ColName => color),			// default=no
						'align' => array (ColName => align),			// default=center
						'tableid' => string								// need for "copy to clipboard"
	*/


	function _createGridData_html ($data, $option=array( 'datatype'=>array(), 'color'=>array(), 'align'=>array())) {
		$gridData = array();

		if (count($data) > 0) {	
			$gridData['colHeaders'] = array();
			$gridData['data'] = array();
			$colheaders = array_keys($data[0]);
	
			// check datatype (auto)
			$datatype = array();
			$maxRows = count($data) >= 10 ? 10 : count($data);
			for ($i=0; $i<$maxRows; $i++) // first 10 rows
			foreach ($data[$i] as $key=>$d) {
				if (!isset($datatype[$key]) || $datatype[$key] == '')
					if (is_float($d))
						$datatype[$key] = 'FLOAT';
					else if (is_numeric($d))
						$datatype[$key] = 'NUMERIC';
					else
						$datatype[$key] = '';
			}

			foreach($colheaders as $c) {
				$color = '';
				$align = '';
				$whiteSpace = '';
				// color
				if (isset($option['color'][$c]))
					$color = $option['color'][$c];
				if(isset($option['nowrap'][$c]) && $option['nowrap'][$c]){
					$whiteSpace = 'nowrap';
				}
				if (isset($option['align'][$c]))
					$align = $option['align'][$c];
				else if ($datatype[$c] == 'NUMERIC' || $datatype[$c] == 'FLOAT')
					$align = "right";
				else
					$align = "center";

				array_push($gridData['colHeaders'], array(
					'col' => $c, 
					'color' => $color,
					'align'=> $align,
					'whiteSpace' => $whiteSpace
				));
			}
			
			foreach ($data as $row) {
				$formatedRow = array();
				
				foreach ($colheaders as $c) {
					// format
					$str_data = '';	

					if (isset($option['datatype'][$c])) {
						switch ($option['datatype'][$c]) {
							case 'DEC0':
								$str_data = number_format($row[$c],0,'.',',');
								break;
							case 'DEC1':
								$str_data = number_format($row[$c],1,'.',',');
								break;
							case 'DEC2':
								$str_data = number_format($row[$c],2,'.',',');
								break;
							case 'PERCENT':
								if ($row[$c] == 0)
									$str_data = '-';
								else
									$str_data = number_format($row[$c]*100,1,'.',',') . '%';
								break;
							case '100PERCENT0':
								$str_data = number_format($row[$c],0,'.',',') . '%';
								break;
							default:
								$str_data = $row[$c];
								break;
						}
					}
					else
						if ($datatype[$c] == 'NUMERIC')
							$str_data = number_format($row[$c],0,'.',',');
						else if ($datatype[$c] == 'FLOAT')
							$str_data = number_format($row[$c],2,'.',',');
						else
							$str_data = $row[$c];

					if(isset($option['alltype'])){
						switch ($option['alltype']) {
							case 'DEC0':
								$str_data = number_format($row[$c],0,'.',',');
								break;
							case 'DEC1':
								$str_data = number_format($row[$c],1,'.',',');
								break;
							case 'DEC2':
								$str_data = number_format($row[$c],2,'.',',');
								break;
							case 'PERCENT':
								if ($row[$c] == 0)
									$str_data = '-';
								else
									$str_data = number_format($row[$c]*100,1,'.',',') . '%';
								break;
							case '100PERCENT0':
								$str_data = number_format($row[$c],0,'.',',') . '%';
								break;
							default:
								$str_data = $row[$c];
								break;
						}
					}
					array_push($formatedRow, $str_data);
				}
				array_push($gridData['data'], $formatedRow);
			}
		}

		return $gridData;
    }
	
	/*	2017-10-20
		option = array ('datatype' => array (ColName => type),			// default=string/number.  DEC0, DEC1, DEC2, PERCENT, 100PERCENT0
						'color' => array (ColName => color),			// default=no
						'align' => array (ColName => align),			// default=center
						'tableid' => string								// need for "copy to clipboard"
	*/
	function _createGridData_html_multivalue ($data, $option=array( 'datatype'=>array(), 'color'=>array(), 'align'=>array())) {
		$rowi = 0;
		$html = '';
		
		
		// header
		$colheaders = array_keys($data[0]);
		$i=0;
		foreach ($data[0] as $d) {
			if ($i == 1) {
				$colheaders_l2 = array_keys($d);
				break;
			}
			$i++;
		}
		if (isset($option['tableid']))
			$tableid = 'id="'.$option['tableid'].'"';
		else
			$tableid = "";
		if(isset($option['colspan'])){
			$colspan = $option['colspan'];
		}else{
			$colspan = 2;
		}
		if(isset($option['rowspan'])){
			$rowspan = $option['rowspan'];
		}else{
			$rowspan = 2;
		}

		$html .= '<table class="table-fill" cellspacing="0" cellpadding="0" align="center" '.$tableid.'>' . '<tr>';
		for ($i=0;$i<count($colheaders);$i++)
			if ($i > 0)
				$html .= '<th class="title" colspan='.$colspan.'>'.$colheaders[$i].'</th>';
			else
				$html .= '<th class="title" rowspan='.$rowspan.'>'.$colheaders[$i].'</th>';
		$html .= '</tr><tr>';
		for ($j=0;$j<count($colheaders)-1;$j++)
			for ($i=0;$i<count($colheaders_l2);$i++)
				$html .= '<th class="title">'.$colheaders_l2[$i].'</th>';
		$html .= '</tr>';
		$html .= '<tbody class="table-hover">';
		
		
		// check datatype (auto)
		$datatype = array();
		for ($i=0; $i<10; $i++) { // first 10 rows
			// first col
			foreach ($data[$i] as $key=>$d) { 
				if (!isset($datatype[$key]) || $datatype[$key] == '')
					if (is_float($d))
						$datatype[$key] = 'FLOAT';
					else if (is_numeric($d))
						$datatype[$key] = 'NUMERIC';
					else
						$datatype[$key] = '';
				break;
			}
			
			// sub cols
			$j = 0;
			foreach ($data[$i] as $key=>$d) { 
				if ($j==1) {
					foreach ($d as $key2=>$d2)
						if (!isset($datatype[$key2]) || $datatype[$key2] == '')
							if (is_float($d2))
								$datatype[$key2] = 'FLOAT';
							else if (is_numeric($d2))
								$datatype[$key2] = 'NUMERIC';
							else
								$datatype[$key2] = '';
					break;
				}
				$j++;
			}
		}
		
		
		// create table
		foreach ($data as $row) {
			// row background color
			if ($rowi % 2 == 0)   $html .= '<tr class="tr-class">';
			else   $html .= '<tr class="tr-class-hi">';
			$rowi++;
			
			$colj = 0;
			foreach ($colheaders as $c) {
				$values= array();
				if ($colj > 0)
					$values = $row[$c];
				else // first row
					$values = [$c=>$row[$c]];
				
				foreach ($values as $key=>$val) {
					// format
					$str_data = '';
					if (isset($option['datatype'][$key])) {
						switch ($option['datatype'][$key]) {
							case 'DEC0':
								$str_data = number_format($val,0,'.',',');
								break;
							case 'DEC1':
								$str_data = number_format($val,1,'.',',');
								break;
							case 'DEC2':
								$str_data = number_format($val,2,'.',',');
								break;
							case 'PERCENT':
								if ($val == 0)
									$str_data = '-';
								else
									$str_data = number_format($val*100,1,'.',',') . '%';
								break;
							case '100PERCENT0':
								$str_data = number_format($val,0,'.',',') . '%';
								break;
							default:
								$str_data = $val;
								break;
						}
					}
					else
						if ($datatype[$key] == 'NUMERIC')
							$str_data = number_format($val,0,'.',',');
						else if ($datatype[$key] == 'FLOAT')
							$str_data = number_format($val,2,'.',',');
						else
							$str_data = $val;
					
					// color
					if (isset($option['color'][$key]))
						$str_data = '<font color="'.$option['color'][$key].'">'.$str_data.'</font>';
					
					if (isset($option['align'][$key]))
						$html .= '<td class="text-left" align="'.$option['align'][$key].'">'.$str_data.'</td> ';
					else if ($datatype[$key] == 'NUMERIC' || $datatype[$key] == 'FLOAT')
						$html .= '<td class="text-left" align="right">'.$str_data.'</td> ';
					else
						$html .= '<td class="text-left" align="center">'.$str_data.'</td> ';
				}
				
				$colj++;
			}
			
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';

		return $html;
    }
	
	function implode_chartdata ($data) {
		//implode($data ,',')
		$ret = '';
		foreach ($data as $v) {
			if ($v === null)
				$ret .= 'null,';
			else
				$ret .= $v . ',';
		}
		$ret = rtrim($ret, ',');
		return $ret;
	}
	
	function format_csv($arr) {
		$ret = implode(",",array_keys($arr[0])) .  "\n";
		foreach ($arr as $v)
			$ret .= implode(",",array_values($v)) .  "\n";
		return $ret;
	}
	
	// -------------------------------- END general ------------------------- -----------------------------------------------------------

	
	// -------------------------------- database / data ------------------------- -----------------------------------------------------------
	function getDataSQL($sql){; 
		$result = array();
		$stmt = $this->pdoReportTool->query($sql);
        $result = $stmt->fetchall(PDO::FETCH_ASSOC);
		return $result;	
	}
	
	function getDataPDOSQL($pdo, $sql){; 
		$result = array();
		$stmt = $pdo->query($sql);
        $result = $stmt->fetchall(PDO::FETCH_ASSOC);
		return $result;	
	}
	
	function listunique_valueofcolumn($dataarr, $column_name='ReportDate') {
		$result = array();
		foreach($dataarr as $row)
			$result[] = $row[$column_name];
		$result = array_unique($result);		
		sort($result, SORT_NATURAL | SORT_FLAG_CASE);		
		
		$r = array();
		foreach ($result as $a)
			$r[] = $a;
		
		return $r;
	}

	function pivotdata_withColNamePrefix ($dataFromDB) { // use for charting.   dataFromDB=array(xAxis,value) or array(xAxis,yAxis,value)   => array with index=xAxis, columnname=valuename_yAxis
		if (count($dataFromDB) == 0)
			return array();
		$colnames = array_keys($dataFromDB[0]);
		$xaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[0]);
		if (count($colnames) > 2)
			$yaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[1]);
	
		// fill empty
		$pivot = array();
		foreach ($xaxislist as $d) { 
			$pivot[$d] = array();
			if (count($colnames) > 2)
				foreach ($yaxislist as $c)
					$pivot[$d][$colnames[2].'_'.$c] = '';
			else
				$pivot[$d][$colnames[1]] = '';
		}
		
		// fill data
		foreach ($dataFromDB as $row)
			if (count($colnames) > 2)
				$pivot[ $row[$colnames[0]]][ $colnames[2].'_'.$row[$colnames[1] ] ] = $row[$colnames[2]];
			else
				$pivot[$row[$colnames[0]]][$colnames[1]] = $row[$colnames[1]];
		
		return $pivot;
	}
	
	function pivotdata ($dataFromDB) { // use for charting.   dataFromDB=array(xAxis,value) or array(xAxis,yAxis,value)   => array with index=xAxis, columnname=yAxis
		if (count($dataFromDB) == 0)
			return array();
		$colnames = array_keys($dataFromDB[0]);
		$xaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[0]);
		if (count($colnames) > 2)
			$yaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[1]);
	
		// fill empty
		$pivot = array();
		foreach ($xaxislist as $d) { 
			$pivot[$d] = array();
			if (count($colnames) > 2)
				foreach ($yaxislist as $c)
					$pivot[$d][$c] = null;
			else
				$pivot[$d][$colnames[1]] = null;
		}
		
		// fill data
		foreach ($dataFromDB as $row)
			if (count($colnames) > 2)
				$pivot[ $row[$colnames[0]]][ $row[$colnames[1] ] ] = $row[$colnames[2]];
			else
				$pivot[$row[$colnames[0]]][$colnames[1]] = $row[$colnames[1]];
		
		return $pivot;
	}
	
	function pivotdata4grid ($dataFromDB) { // array(xAxis,yAxis,value)   => array( array(xAxis=>,yAxis1=>,yAxis2=>,...) )
		if (count($dataFromDB) == 0)
			return array();
		$colnames = array_keys($dataFromDB[0]);
		$xaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[0]);
		$yaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[1]);
	
		// fill empty (pivot)
		$pivot = array();
		$xarray = array(); $i=0;
		foreach ($xaxislist as $d) {
			$a = array();
			$a[$colnames[0]] = $d;
			foreach ($yaxislist as $c)
				$a[$c] = '';
			
			$pivot[] = $a;
			$xarray[$d] = $i;
			$i++;
		}
		
		// fill data
		foreach ($dataFromDB as $row) {
			$x = $xarray[$row[$colnames[0]]];
			$y = $row[$colnames[1]];
			//if ($x && $y)
			$pivot[$x][$y] = $row[$colnames[2]];
		}
		
		return $pivot;
	}
	
	function pivotdata4grid_multivalue ($dataFromDB) { // array(xAxis,yAxis,value1,value2,...valueX)   => array( array(xAxis=>,yAxis1=>[value1=>,value2=>,...],yAxis2=>[value1=>,value2=>,...],...) )
		if (count($dataFromDB) == 0)
			return array();
		$colnames = array_keys($dataFromDB[0]);
		$xaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[0]);
		$yaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[1]);
	
		// fill empty (pivot)
		$pivot = array();
		$xarray = array(); $i=0;
		foreach ($xaxislist as $d) {
			$a = array();
			$a[$colnames[0]] = $d;
			foreach ($yaxislist as $c)
				for ($j=2;$j<count($colnames);$j++)
					$a[$c][$colnames[$j]] = '';
			
			$pivot[] = $a;
			$xarray[$d] = $i;
			$i++;
		}
		
		// fill data
		foreach ($dataFromDB as $row) {
			$x = $xarray[$row[$colnames[0]]];
			$y = $row[$colnames[1]];
			//if ($x && $y)
			for ($j=2;$j<count($colnames);$j++)
				$pivot[$x][$y][$colnames[$j]] = $row[$colnames[$j]];
		}
		
		return $pivot;
	}
		
	function listValueFromArray($arr, $col) {
		$r = array();
		foreach ($arr as $v)
			$r[] = $v[$col];
		return $r;
	}
	// -------------------------------- END database / data ------------------------- -----------------------------------------------------------
	
	// ------------------------------------- ZP Portal ------------------------------------------------------------------------------------------
	function getBiggestFields($data, $numberField){
		if (empty($data)) return array();		
		$sumData = array_values($data)[0];
		$first = true;
		foreach ($data as $key => $value) {
			if($first){
				$first = false;
			}else{
				foreach ($sumData as $ki => $vi) {
					$sumData[$ki] += $value[$ki] == "" ? "0" : $value[$ki];
				}
			}
		}

		arsort($sumData);	
		$result = array();
		for($i = 0; $i < $numberField; $i++){
			array_push($result, array_keys($sumData)[$i]);
		}		
		return $result;
	}

	// ------------------------------------------- END ZP Portal ------------------------------------------------------------------------------------------
	function getTitleByValue($sql, $numTop, $isBig=true, $title='Title', $checkField='Value', $pdo = ''){
		if($pdo == ''){
			$pdo = $this->pdoReportTool;
		}		
		$data = $this->getDataPDOSQL($pdo, $sql);
		$colnames = array_keys($data[0]);
		$xaxislist = $this->listunique_valueofcolumn($data,$colnames[0]);
		if (count($colnames) > 2){
			return array();
		}

		// fill empty
		$pivot = array();
		foreach ($xaxislist as $d) { 
			$pivot[$d] = array();
			$pivot[$d][$colnames[1]] = 0;
		}
		// fill data
		foreach ($data as $row)
			$pivot[$row[$colnames[0]]][$colnames[1]] += $row[$colnames[1]];

		if($isBig){
			arsort($pivot);
		}else{
			asort($pivot);
		}
		$res = array();

		$count = 0;
		foreach ($pivot as $key => $value) {
			$res[$count] = $key;
			$count++;			
			if($count >= $numTop){
				break;
			}
		}
		$strBiggest = implode(",",$res);
		return $res;
	}

	function addChart($id, $type = "chart") {
		$string = '</br><div id="'.$id.'" class="'.$type.'"></div>';
		return $string;
	}
	function addCharts($ids) {
		$all = '';
		foreach ($ids as $id) {
			$all = $all.$this->addChart($id);			
		}
		return $all;
	}

	function getViewScript($id, $obj) {
		return "$(function () {\$('#".$id."').highcharts(".($obj).");});
		";
	}

	function addGrid($name, $id, $obj, $showName=false) {		
		$string = '</br>'.($showName?'<p align="center"><b align="center">'.$name.'</b></p>':'').
        '<p style="text-align: center;"></p>        
        <p align="center"><a href="#visible_'.$id.'" style="cursor: pointer" id="visible_'.$id.'" onclick="return clickDisplay(\''.$id.'\');">Show data</a></p>
        <div id="'.$id.'"  style="display:none ">
		<p align="center"><a style="cursor: pointer" onclick="selectElementContents(document.getElementById(\''.$id.'\'))">Copy to clipboard</a></p>
		'.$obj.'</div>';
		return $string;
	}

	function createJSFunction($fields){
		$res = "";
		foreach ($fields as $key => $value) {
			$res .= $this->getViewScript($value, $this->listChart[$value]);
		}
		return $res;
	}

	public function query($sql){
		$data =  DB::select($sql);
		$data = collect($data)->map(function($x){ return (array) $x; })->toArray(); 
		return $data;
	}

	public function addDescription($description){
		return json_encode($description);
	}

	function addTopViewApp ($listApp, $GroupPage) { // Group101, Group110
		return $listApp;
	}

	function listCountryFromListApp () { // $this->listCountry  $this->selectedCountry
		$str = '';
		foreach ($this->listApp as $v)
			$str .= $this->quote($v) . ',';
		$str = rtrim($str, ',');
		
		$this->listCountry = array();
		$this->listCountry[] = '--All--';	
		$sql = "select distinct Country from GSNConfig.AppName where ToAppName in (".$str.") and Country is not null order by Country";
		$data = $this->getDataSQL($sql);
		foreach ($data as $row)
			$this->listCountry[] = $row['Country'];
		
		if (Request::query('Country'))
			$this->selectedCountry = Request::query('Country');
		if (!isset($this->selectedCountry))
			$this->selectedCountry = '--All--';
	}
	
	function reListAppByCountry ($country) { // return list app + re-select app if not in list
		$str = '';
		foreach ($this->listApp as $v)
			$str .= $this->quote($v) . ',';
		$str = rtrim($str, ',');
		
		$l = array();
		$sql = "select distinct ToAppName from GSNConfig.AppName where Country = ".$this->quote($country)." and ToAppName in (".$str.") order by ToAppName";
		$data = $this->getDataSQL($sql);
		$hasselectedapp = 0;
		foreach ($data as $row) {
			$l[] = $row['ToAppName'];
			if ($this->AppName == $row['ToAppName'])
				$hasselectedapp = 1;
		}
		if ($hasselectedapp == 0)
			$this->AppName = $l[0];
		return $l;
	}

	function pivotdata_withColNamePrefix_new ($dataFromDB, $sortType='name', $order='asc', $maxItem=10000) { // use for charting.   dataFromDB=array(xAxis,value) or array(xAxis,yAxis,value)   => array with index=xAxis, columnname=valuename_yAxis
		if (count($dataFromDB) == 0)
			return array();
		$colnames = array_keys($dataFromDB[0]);
		$xaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[0]);
		if (count($colnames) > 2){
			if($sortType==='value'){
				$yaxislist = $this->sortColumnByValue($dataFromDB,$colnames[1],$colnames[0], $order);
			}else{
				$yaxislist = $this->listunique_valueofcolumn($dataFromDB,$colnames[1], $order);
			}	
			$yaxislist = array_slice($yaxislist, 0, $maxItem);
			$yaxislist = array_reverse($yaxislist);
		}					
	
		// fill empty
		$pivot = array();
		foreach ($xaxislist as $d) { 
			$pivot[$d] = array();
			if (count($colnames) > 2)
				foreach ($yaxislist as $c)
					$pivot[$d][$colnames[2].'_'.$c] = '';
			else
				$pivot[$d][$colnames[1]] = '';
		}
		
		// fill data				
		foreach ($dataFromDB as $row)
			if (count($colnames) > 2){				
				if(in_array($row[$colnames[1]], $yaxislist)){
					$pivot[ $row[$colnames[0]]][ $colnames[2].'_'.$row[$colnames[1] ] ] = $row[$colnames[2]];
				}				
			}				
			else
				$pivot[$row[$colnames[0]]][$colnames[1]] = $row[$colnames[1]];		
		return $pivot;
	}

	function sortColumnByValue($dataarr, $column_name='ReportDate', $firstColName='ReportDate', $sortOrder='asc'){
		$values = [];
		foreach($dataarr as $row){
			$result[] = $row[$column_name];
			$values[$row[$column_name]] = 0;
		}	
		
		foreach($dataarr as $row){
			foreach($row as $k=>$v){
				if(is_numeric($v) && $k != $column_name && $k != $firstColName){
					$values[$row[$column_name]] += $v;
				}				
			}			
		}

		if($sortOrder==='asc'){
			array_multisort(array_values($values), SORT_ASC, array_keys($values), SORT_ASC, $values);
			// asort($values);			
		}elseif($sortOrder==='desc'){
			array_multisort(array_values($values), SORT_DESC, array_keys($values), SORT_ASC, $values);
			// arsort($values);
		}		
		$r = array();
		foreach ($values as $k=>$v)
			$r[] = $k;
							
		return $r;
	}

	function whereFilterAllowApps101($field) {
		return "1=1";
	}

	function listAllow101() {
		// ----------- list GameCode --------------
		$sql = 'select distinct ToAppName AppName  from GSNConfig.AppName  where '.$this->whereFilterAllowApps101('ToAppName').' order by AppName';
		$this->AllowGameCode101 = array();
		$stmt = $this->pdoReportTool->query($sql);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
		  $this->AllowGameCode101[] = $row['AppName'];
		
		
		// ----------- list Game Group --------------
		$sql = "SELECT t1.GameGroup FROM
		  (SELECT GameGroup, COUNT(DISTINCT ToAppName) AppQty FROM GSNConfig.AppName WHERE GameGroup is not NULL AND GameGroup<>'' GROUP BY GameGroup) t1
		  join 
		  (SELECT GameGroup, COUNT(DISTINCT ToAppName) AppQty FROM GSNConfig.AppName WHERE GameGroup is not NULL AND GameGroup<>'' AND ToAppName in ('".implode("','",$this->AllowGameCode101)."') GROUP BY GameGroup) t2
		  on t1.GameGroup=t2.GameGroup and t1.AppQty=t2.AppQty";
		$this->AllowGameGroup101 = array();
		$stmt = $this->pdoReportTool->query($sql);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
		  $this->AllowGameGroup101[] = $row['GameGroup'];
		
		
		// ----------- list Country --------------
		$sql = "SELECT t1.Country FROM
		  (SELECT Country, COUNT(DISTINCT ToAppName) AppQty FROM GSNConfig.AppName WHERE Country is not NULL AND Country<>'' GROUP BY Country) t1
		  join 
		  (SELECT Country, COUNT(DISTINCT ToAppName) AppQty FROM GSNConfig.AppName WHERE Country is not NULL AND Country<>'' AND ToAppName in ('".implode("','",$this->AllowGameCode101)."') GROUP BY Country) t2
		  on t1.Country=t2.Country and t1.AppQty=t2.AppQty";
		$this->AllowCountry101 = array();
		$stmt = $this->pdoReportTool->query($sql);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
		  $this->AllowCountry101[] = $row['Country'];
	
		$this->AllowRules101[] = $row['AppName'];
	}
}

?>