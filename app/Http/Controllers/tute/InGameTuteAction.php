<?php


namespace App\Http\Controllers\tute;

use App\Http\Controllers\InGameFunction;
use Illuminate\Support\Facades\Log;

class InGameTuteAction extends InGameTuteFunction
{
    function index($type = '')
    {
        $this->AppName = self::APP_NAME;
        $game = self::GAME;
        $this->pageTitle='Tute Action Report';

        $this->listChart = array(
            'chartNumberInteractByItem'=>$this->chartNumberInteractByItem(),
            'gridNumberInteractByItem'=> $this->gridNumberInteractByItem('tableid_gridNumberInteractByItem'),
            'chartNumberSendAddFriend'=>$this->chartNumberActionByType(2),
            'gridNumberSendAddFriend'=>$this->gridNumberActionByType('tableid_gridNumberSendAddFriend', 2),
            'chartNumberAcceptFriend'=>$this->chartNumberActionByType(3),
            'gridNumberAcceptFriend'=> $this->gridNumberActionByType('tableid_gridNumberAcceptFriend', 3),
            'chartNumberInviteFriend'=>$this->chartNumberActionByType(4),
            'gridNumberInviteFriend'=> $this->gridNumberActionByType('tableid_gridNumberInviteFriend', 4),
            'chartNumberAcceptFriendInvite'=>$this->chartNumberActionByType(5),
            'gridNumberAcceptFriendInvite'=> $this->gridNumberActionByType('tableid_gridNumberAcceptFriendInvite', 5),
            'chartNumberShowInvite'=>$this->chartNumberActionByType(6),
            'gridNumberShowInvite'=> $this->gridNumberActionByType('tableid_gridNumberShowInvite', 6),
            'chartNumberAccept'=>$this->chartNumberActionByType(7),
            'gridNumberAccept'=> $this->gridNumberActionByType('tableid_gridNumberAccept', 7),
            'chartNumberMiniGame'=>$this->chartNumberActionByType(8),
            'gridNumberMiniGame'=> $this->gridNumberActionByType('tableid_gridNumberMiniGame', 8),
            'chartNumberPlayNow'=>$this->chartNumberActionByType(9),
            'gridNumberPlayNow'=> $this->gridNumberActionByType('tableid_gridNumberPlayNow', 9),
            'chartNumberTapByChannel'=>$this->chartNumberTapByChannel(),
            'gridNumberTapByChannel'=>$this->gridNumberTapByChannel('tableid_gridNumberTapByChannel')
        );

        // $this->listChartId=['chartNumberInteractByItem', 'chartNumberSendAddFriend', 'chartNumberAcceptFriend', 'chartNumberInviteFriend',
        //                     'chartNumberAcceptFriendInvite', 'chartNumberShowInvite', 'chartNumberAccept', 'chartNumberMiniGame',
        //                     'chartNumberPlayNow', 'chartNumberTapByChannel'];

        // $this->addViewsToMain=json_encode(
        //     $this->addCharts(['chartNumberInteractByItem']).
        //     $this->addGrid('', 'gridNumberInteractByItem', $this->gridNumberInteractByItem('tableid_gridNumberInteractByItem'), false).
        //     $this->addCharts(['chartNumberSendAddFriend']).
        //     $this->addGrid('', 'gridNumberSendAddFriend', $this->gridNumberActionByType('tableid_gridNumberSendAddFriend', 2), false).
        //     $this->addCharts(['chartNumberAcceptFriend']).
        //     $this->addGrid('', 'gridNumberAcceptFriend', $this->gridNumberActionByType('tableid_gridNumberAcceptFriend', 3), false).
        //     $this->addCharts(['chartNumberInviteFriend']).
        //     $this->addGrid('', 'gridNumberInviteFriend', $this->gridNumberActionByType('tableid_gridNumberInviteFriend', 4), false).
        //     $this->addCharts(['chartNumberAcceptFriendInvite']).
        //     $this->addGrid('', 'gridNumberAcceptFriendInvite', $this->gridNumberActionByType('tableid_gridNumberAcceptFriendInvite', 5), false).
        //     $this->addCharts(['chartNumberShowInvite']).
        //     $this->addGrid('', 'gridNumberShowInvite', $this->gridNumberActionByType('tableid_gridNumberShowInvite', 6), false).
        //     $this->addCharts(['chartNumberAccept']).
        //     $this->addGrid('', 'gridNumberAccept', $this->gridNumberActionByType('tableid_gridNumberAccept', 7), false).
        //     $this->addCharts(['chartNumberMiniGame']).
        //     $this->addGrid('', 'gridNumberMiniGame', $this->gridNumberActionByType('tableid_gridNumberMiniGame', 8), false).
        //     $this->addCharts(['chartNumberPlayNow']).
        //     $this->addGrid('', 'gridNumberPlayNow', $this->gridNumberActionByType('tableid_gridNumberPlayNow', 9), false).
        //     $this->addCharts(['chartNumberTapByChannel']).
        //     $this->addGrid('', 'gridNumberTapByChannel', $this->gridNumberTapByChannel('tableid_gridNumberTapByChannel'), false).
        //     ''
        // );

        return parent::__index($type);
    }

    private function chartNumberInteractByItem(){
        $title = "Số lượng dùng tương tác từng loại";
        $sql = $this->getSQLNumberInteractByItem();
        $sql2 = $this->getSQLTotalNumberAction(1);
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options);
    }

    private function chartNumberTapByChannel(){
        $title = "Số lượng tap chọn kênh";
        $sql = $this->getSQLNumberTapByChannel();
        $sql2 = $this->getSQLTotalNumberAction(10);
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'showall' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1stack1line_withPrefix_v2($sql, $sql2, $options);
    }

    private function chartNumberActionByType($type){
        if ($type==2){
            // $sql = $this->getSQLNumberUserLogin();
            $title = 'Số lượng kết bạn';
        } elseif ($type==3) {
            $title = 'Số lượng accept';
        } elseif ($type==4) {
            $title = 'Số lượng mời bạn chơi';
        } elseif ($type==5) {
            $title = 'Số lượng chấp nhận mời chơi';
        } elseif ($type==6) {
            $title = 'Số lượng hiển thị mời chơi tự động';
        } elseif ($type==7) {
            $title = 'Số lượng chấp nhận mời chơi tự động';
        } elseif ($type==8) {
            $title = 'Số lượt tap chơi MiniGame Đua Tốp';
        } elseif ($type==9) {
            $title = 'Số Lượng tap chơi ngay';
        }

        $sql = $this->getSQLTotalNumberAction($type);
        $options = [    'title' => $title,
            'subtitle' => $this->AppName,
            'yAxis_title' => '#',
            'stack_col' => true,
            'hide_show_button' => true,
            'hide_show_button'=>true,
        ];
        return $this->chart1LineOption($sql, false, $options, true);
    }

    private function gridNumberInteractByItem($tid){
        $sql = $this->getSQLNumberInteractByItem(false);
        $data = $this->getDataSQLInGame($sql);
        $gridData = $this->pivotdata4grid_withSort($data, 'defaultRSort');
        $this->fillEmptyData($gridData);
        return $this->createGridData($gridData, $tid);
    }

    private function gridNumberTapByChannel($tid){
        $sql = $this->getSQLNumberTapByChannel(false);
        $data = $this->getDataSQLInGame($sql);
        $gridData = $this->pivotdata4grid_withSort($data, 'defaultRSort');
        $this->fillEmptyData($gridData);
        return $this->createGridData($gridData, $tid);
    }

    private function gridNumberActionByType($tid, $type)
    {
        $sql = $this->getSQLTotalNumberAction($type, false);
        return $this->createGrid($sql, $tid);
    }

    function getSQLNumberInteractByItem($isChart=true){
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, 
            (case when UserAction = '1' then 'Trứng'
                when UserAction = '2' then 'Hôn'
                when UserAction = '3' then 'Vỗ Tay'
                when UserAction = '4' then 'Hoa Hồng'
                when UserAction = '5' then 'Dép'
                when UserAction = '6' then 'Trái Tim'
                when UserAction = '7' then 'Cà Chua'
                when UserAction = '8' then 'Pháo'
                when UserAction = '9' then 'Ly Rượu'
                else 'Xô Nước' end) as Item, Quantity as NumberAction ".
            "from tute_report_by_user_action".
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Category='Interact' and UserAction <> 'All' order by ReportDate";
        return $sql;
    }

    function getSQLNumberTapByChannel($isChart=true){
        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, UserAction as Channel, Quantity as NumberTap ".
            "from tute_report_by_user_action".
            " where ReportDate BETWEEN  ".$this->quote($this->from)." and ".$this->quote($this->to)." and Category='channel' and UserAction <> 'All' order by ReportDate";
        return $sql;
    }

    private function getSQLTotalNumberAction($type, $isChart=true){
        $table = "tute_report_by_user_action";
        $category = "";
        $userAction = "All";

        if ($type==1) {
            $category="Interact";
        } elseif ($type==2) {
            $category='SendAddFriend';
        } elseif ($type==3) {
            $category='AcceptFriend';
        } elseif ($type==4) {
            $category='InviteFriend';
        } elseif ($type==5) {
            $category='InvitePanel';
            $userAction='Accept_Friend_Invite';
        } elseif ($type==6) {
            $category='InvitePanel';
            $userAction='Show';
        } elseif ($type==7) {
            $category='InvitePanel';
            $userAction='Accept';
        } elseif ($type==8) {
            $category='lobby';
            $userAction='MINIGAME';
        } elseif ($type==9) {
            $category='lobby';
            $userAction='PlayNow';
        } elseif ($type==10) {
            $category='channel';
        }

        $sql = "select ".$this->getChartDateFormat("ReportDate", $isChart)." ReportDate, Quantity as TotalAction from ".$table.
        " where ReportDate BETWEEN ".$this->quote($this->from)." and ".$this->quote($this->to).
        " and Category=".$this->quote($category)." and UserAction=".$this->quote($userAction)." order by ReportDate";
        return $sql;
    }
}