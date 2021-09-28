<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Account;
use App\Http\Controllers\CommonFunction;
use View, PDO;

class ActiveBackReturnUser extends CommonFunction {

    protected $inGameDB;
    protected $addViews;
    protected $listChartId;
    protected $listChart;
    protected $pageTitle;

    public function __construct($ListAppTable='', $AppField='AppName', $AddFilterToListApp = '', $conn='pdoReport') {            
        parent::__construct($ListAppTable,$AppField,$AddFilterToListApp,$conn); // change table AppList        

        $this->addViewsToMain='';
        $this->listChartId=[];
        $this->listChart=[];
        $this->addViews='null';
        $this->pdoUserCount = $this->pdoAuthen;
    }

    public function index($type='')
    {
        $this->listCountryFromListApp(); // câu này + thêm if bên dưới + move addTopViewApp + $viewparams + đổi view
		if ($this->selectedCountry == '--All--')
            // $listApp = $this->addTopViewApp($this->listApp, 'Group101');
            $listApp = $this->listApp;
		else
            $listApp = $this->reListAppByCountry($this->selectedCountry);
            
        $this->listChart = array(
            'chartUserNum' => $this->chartUserNum(),
            'chartUserNumAndroid' => $this->chartUserNumAndroid(),
            'chartUserNumiOS' => $this->chartUserNumiOS(),
            'chartReturnUser' => $this->chartReturnUser(),
            'chartChurnUser' => $this->chartChurnUser(),
            'chartChurnUserPay' => $this->chartChurnUserPay(),
            'chartChurnUserByLiveDuration' => $this->chartChurnUserByLiveDuration(),
            'chartChurnN1ByLiveDuration' => $this->chartChurnN1ByLiveDuration(),
            'chartChurnN1ByLiveDurationPercent' => $this->chartChurnN1ByLiveDurationPercent(),
        );        
        
        $this->listChartId=array_keys($this->listChart);

        $this->addViewsToMain=json_encode(	
            $this->addCharts(['chartUserNum']).	
            $this->addCharts(['chartUserNumAndroid']).	
            $this->addCharts(['chartUserNumiOS']).													
            $this->addCharts(['chartReturnUser']).
            $this->addGrid('Data', 'gridReturnUser', $this->gridReturnUser(), true). 
            $this->addCharts(['chartChurnUser']).  
            $this->addCharts(['chartChurnUserPay']). 
            $this->addCharts(['chartChurnUserByLiveDuration']).
            $this->addGrid('Data', 'gridChurnUser', $this->gridChurnUser(), true).
            $this->addCharts(['chartChurnN1ByLiveDuration']).
            $this->addCharts(['chartChurnN1ByLiveDurationPercent']).
            $this->addGrid('Data', 'gridChurnN1', $this->gridChurnN1(), true).     
            ''
        );
        $this->pageTitle='Active Back & Return';
        $this->pageInfo=
		"<p>
            <br>Diễn giải:
            <br> - Churn: User không active liên tiếp 30 ngày
            <br> - ReturnUser: User đã churn quay lại game (đồng nghĩa A1Back>30)
            <br> - A1BackX: User login hôm nay mà chỉ login vào ngày thứ X trước đó (X-1 đến hôm qua không login)
            <br> - ActiveDate: 
            <br>    &emsp;&emsp;&emsp;&emsp;    New User: Account Date
            <br>    &emsp;&emsp;&emsp;&emsp;    Return User: Ngày re-active lại sau khi churn
            <br> - Live Duration: Số ngày tồn tại của User, được tính từ <font color=red>ActiveDate</font> đến LastActiveDate
            <br>
		</p>";

        $viewparams = ['listCountry' => $this->listCountry,
                        'selectedCountry' => $this->selectedCountry,];
                        
        return parent::__index($listApp, $type, $viewparams, 'pages.Common1FieldWithCountryEvent');
    }

    function chartUserNum(){        

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        SUM(N1) N1, SUM(A1Back1) A1Back1, SUM(`A1Back2-3`) `A1Back2-3`, SUM(`A1Back4-7`) `A1Back4-7`, SUM(`A1Back8-15`) `A1Back8-15`, SUM(`A1Back16-30`) `A1Back16-30`, SUM(ReturnUser) as `A1Back>30 (ReturnUser)`
        from tracker_report_dis.report_user_back 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)."
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'column',
                    'stackname' => 'Col1'
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        SUM(A1) A1
        from tracker_report_dis.report_user_back 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'line'
                    ];
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => 'A1 theo số ngày quay lại',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => '#',
                    'stack_col' => true,
                    ];	

		return $this->script_lineChart($categories, $highchartseries, $options);       
    }

    function chartUserNumAndroid(){        

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        SUM(A1Back1) A1Back1_Android, 
        SUM(`A1Back2-3`) `A1Back2-3_Android`, 
        SUM(`A1Back4-7`) `A1Back4-7_Android`, 
        SUM(`A1Back8-15`) `A1Back8-15_Android`, 
        SUM(`A1Back16-30`) `A1Back16-30_Android`, 
        SUM(ReturnUser) as `A1Back>30_Android`
        from tracker_report_dis.report_user_back 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)."
        and Platform = 'Android'
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'column',
                    'stackname' => 'Android'
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        SUM(A1Back1) A1Back1_iOs, 
        SUM(`A1Back2-3`) `A1Back2-3_iOs`, 
        SUM(`A1Back4-7`) `A1Back4-7_iOs`, 
        SUM(`A1Back8-15`) `A1Back8-15_iOs`, 
        SUM(`A1Back16-30`) `A1Back16-30_iOs`, 
        SUM(ReturnUser) as `A1Back>30_iOs`
        from tracker_report_dis.report_user_back 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)."
        and Platform = 'iOs'
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        if (count($data) == 0)
            return '';

        $options = ['type' => 'column',
                    'stackname' => 'iOs'
                    ]; 
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		// build high chart
		$highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => 'A1Back theo Android',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => '#',
                    'stack_col' => true,
                    ];	

		return $this->script_lineChart($categories, $highchartseries, $options);       
    }

    function chartUserNumiOS(){        
        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        SUM(A1Back1) A1Back1_iOs, 
        SUM(`A1Back2-3`) `A1Back2-3_iOs`, 
        SUM(`A1Back4-7`) `A1Back4-7_iOs`, 
        SUM(`A1Back8-15`) `A1Back8-15_iOs`, 
        SUM(`A1Back16-30`) `A1Back16-30_iOs`, 
        SUM(ReturnUser) as `A1Back>30_iOs`
        from tracker_report_dis.report_user_back 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)."
        and Platform = 'iOs'
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        if (count($data) == 0)
            return '';

        $options = ['type' => 'column',
                    'stackname' => 'iOs'
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		// build high chart
		$highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => 'A1Back theo iOS',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => '#',
                    'stack_col' => true,
                    ];	

		return $this->script_lineChart($categories, $highchartseries, $options);       
    }

    function chartReturnUser(){        

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        SUM(ReturnUser) ReturnUser
        from tracker_report_dis.report_user_back 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'line',
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        round(SUM(ReturnUser_P0) * 100 / SUM(ReturnUser), 1) as ReturnUser_PR0
        from tracker_report_dis.report_user_back 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'line',
                    'yAxis' => 1,
                    'invisible' => ['ReturnUser_PR0']
                    ]; 
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => 'User đã churn quay lại game',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => ['#', '%'],
                    'stack_col' => true,
                    ];	

		return $this->script_chart2Y($categories, $highchartseries, $options);       
    }


    function chartChurnUser(){        

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        sum(Churn30 - Churn30_Return) as `Churn lần đầu`, sum(Churn30_Return) as `Đã từng churn`
        from tracker_report_dis.report_user_churn  
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'column',
                    'stackname' => 'Col2'
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        sum(Churn30) as `Churn30`
        from tracker_report_dis.report_user_churn 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'line',
                    ]; 
        $arr4 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		// build high chart
		$highchartseries = array_merge($arr1, $arr4);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => 'Số User churn',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => '#',
                    'stack_col' => true,
                    ];	

		return $this->script_lineChart($categories, $highchartseries, $options);       
    }


    function chartChurnUserPay(){        

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        sum(Churn30_Pay - Churn30_Return_Pay) as `Churn lần đầu`, sum(Churn30_Return_Pay) as `Đã từng churn`
        from tracker_report_dis.report_user_churn  
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'column',
                    'stackname' => 'Col1'
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        sum(Churn30_Pay) as `Churn30Pay`
        from tracker_report_dis.report_user_churn 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'line',
                    ]; 
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => 'Số pay user churn (pay in Live Duration)',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => '#',
                    'stack_col' => true,
                    ];	

		return $this->script_lineChart($categories, $highchartseries, $options);       
    }


    function chartChurnUserByLiveDuration(){        

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        concat(LiveDuration, '_Day'), Churn30
        from tracker_report_dis.report_user_churn 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);
        $pivot = $this->pivotdata2020($data);

        $options = ['type' => 'column',
                    'stackname' => 'Churn30'
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);
        

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        SUM(Churn30) as `Churn30`
        from tracker_report_dis.report_user_churn 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate 
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'line'
                    ]; 
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => 'Số User churn theo Live Duration',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => '#',
                    'stack_col' => true,
                    ];	

		return $this->script_chart2Y($categories, $highchartseries, $options);       
    }


    function chartChurnN1ByLiveDuration(){        

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        concat(LiveDuration, '_Day') as LiveDuration, 
        (case when DATEDIFF(".$this->quote(date("Y-m-d"))." - interval 30 day, ReportDate) > SUBSTRING_INDEX(LiveDuration, '-', -1) then Churn30 ELSE NULL END) as Churn30
        from tracker_report_dis.report_n1_churn 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        and LiveDuration <> '>30'
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);
        $pivot = $this->pivotdata2020($data);
        foreach($pivot as $key => $value){
            krsort($value, 1);
            $pivot[$key] = $value;
        }
        $options = ['type' => 'column',
                    'stackname' => 'Churn30'
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

        $sql = "select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
        SUM(Churn30) as `Churn30`
        from tracker_report_dis.report_n1_churn 
        where ReportDate BETWEEN ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        group by ReportDate 
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);

        $options = ['type' => 'line',
                    'invisible' => ['Churn30']
                    ]; 
        $arr2 = $this->_create_ArrayFor_HighchartSeries($data, $options, true);

		// build high chart
		$highchartseries = array_merge($arr1, $arr2);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => 'N1 churn theo Live Duration',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => '#',
                    'stack_col' => true,
                    ];	

		return $this->script_chart2Y($categories, $highchartseries, $options);       
    }


    function chartChurnN1ByLiveDurationPercent(){        

        $sql = "select a.ReportDate, a.LiveDuration, format(a.Churn30 / b.N1, 2) as ChurnRatio from
                (select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
                concat(LiveDuration, '_Day') as LiveDuration, 
                (case when DATEDIFF(".$this->quote(date("Y-m-d"))." - interval 30 day, ReportDate) > SUBSTRING_INDEX(LiveDuration, '-', -1) then Churn30 ELSE NULL END) as Churn30
                from tracker_report_dis.report_n1_churn 
                where ReportDate BETWEEN ".$this->quote($this->from)." 
                and ".$this->quote($this->to)." 
                and AppName = ".$this->quote($this->AppName)." 
                and LiveDuration <> '>30') a
            join
                (select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, SUM(N1) as N1
                from tracker_report_dis.report_user_back 
                where ReportDate BETWEEN ".$this->quote($this->from)." 
                and ".$this->quote($this->to)." 
                and AppName = ".$this->quote($this->AppName)." 
                group by ReportDate) b
            on a.ReportDate = b.ReportDate
        order by ReportDate";
        $data = $this->getDataPDOSQL($this->pdoUserCount, $sql);
        $pivot = $this->pivotdata2020($data);
        foreach($pivot as $key => $value){
            krsort($value, 1);
            $pivot[$key] = $value;
        }
        $options = ['type' => 'area',
                    'stackname' => 'Col1'
                    ]; 
        $arr1 = $this->_create_ArrayFor_HighchartSeries($pivot, $options);

		// build high chart
		$highchartseries = array_merge($arr1);
        $categories = $this->listunique_valueofcolumn($data, 'ReportDate');

		$options = ['title' => '%N1 churn trên N1 theo Live Duration',
                    'subtitle' => $this->AppName,
                    'yAxis_title' => '#',
					'stack_area' => true,
                    ];	

		return $this->script_lineChart($categories, $highchartseries, $options); 
    }


    function gridReturnUser(){
        $tid = 'tableid_gridReturnUser';
        $sql = "select date_format(ReportDate, '%Y-%m-%d') ReportDate, 
        A1, P1, N1, N1_P0, A1Back1, `A1Back2-3`, `A1Back4-7`, `A1Back8-15`, `A1Back16-30`, ReturnUser as `A1Back>30 (ReturnUser)`, ReturnUser_P0, ReturnUser_P0 / ReturnUser as `ReturnUser_PR0`
        from tracker_report_dis.report_user_back 
        where ReportDate BETWEEN  ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        order by ReportDate DESC";
        return $this->createGrid($sql, $tid, ['ReturnUser_PR0'=>'PERCENT'], $this->pdoUserCount);
    }


    function gridChurnUser(){
        $tid = 'tableid_gridChurnUser';
        $sql = "select date_format(ReportDate, '%Y-%m-%d') ReportDate, 
        concat(LiveDuration, ' Day') as `LiveDuration`, Churn30, Churn30_Pay, Churn30_Return as `Số User đã từng churn`, Churn30_Return_Pay as `Số pay user đã từng churn`
        from tracker_report_dis.report_user_churn
        where ReportDate BETWEEN  ".$this->quote($this->from)." 
        and ".$this->quote($this->to)." 
        and AppName = ".$this->quote($this->AppName)." 
        order by ReportDate DESC, LiveDuration";
        return $this->createGrid($sql, $tid, [], $this->pdoUserCount);
    }


    function gridChurnN1(){
        $tid = 'tableid_gridChurnN1';
        $sql = "select a.ReportDate, a.LiveDuration, a.Churn30 as Churn30, format(a.Churn30 / b.N1, 2) as Ratio from
                (select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, 
                concat(LiveDuration, ' Day') as LiveDuration, 
                (case when DATEDIFF(".$this->quote(date("Y-m-d"))." - interval 30 day, ReportDate) > SUBSTRING_INDEX(LiveDuration, '-', -1) then Churn30 ELSE NULL END) as Churn30
                from tracker_report_dis.report_n1_churn 
                where ReportDate BETWEEN ".$this->quote($this->from)." 
                and ".$this->quote($this->to)." 
                and AppName = ".$this->quote($this->AppName)." 
                and LiveDuration <> '>30') a
            join
                (select date_format(ReportDate,'".$this->formatXDate()."') ReportDate, SUM(N1) as N1
                from tracker_report_dis.report_user_back 
                where ReportDate BETWEEN ".$this->quote($this->from)." 
                and ".$this->quote($this->to)." 
                and AppName = ".$this->quote($this->AppName)." 
                group by ReportDate) b
            on a.ReportDate = b.ReportDate
        order by ReportDate desc, LiveDuration";
        return $this->createGrid($sql, $tid, ['Ratio'=>'DEC2'], $this->pdoUserCount);
    }
}
