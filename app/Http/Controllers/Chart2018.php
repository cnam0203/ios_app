<?php

namespace App\Http\Controllers;
	
class Chart2018 extends Base2018 {
	const color_main = ['#ff3300', '#059c00', '#012cff', '#ffe700', '#d400e1', '#00aea9', '#34df00', '#000000', '#ff8c1a', '#8a00e6'];
	const color_sub = ['#ffdad1', '#b9d4b8', '#d3dafd', '#f4f0c2', '#e3c1e5', '#bfe7e6', '#e2ffd9', '#c7c7c7', '#ffd9b3', '#e0b3ff'];
	
	/*	2017-10-20
		options
			type				line / column / line--XXXXX (Solid, ShortDash, ShortDot, ShortDashDot, ShortDashDotDot, Dot, Dash, LongDash, DashDot, LongDashDot, LongDashDotDot)
			yAxis				0 / 1
			legendIndexAdd		int
			indexAdd			int
			zIndexAdd			int
			invisible			[seriesname]
			visible				[seriesname]
			stackname			stackname
	*/
	function _create_ArrayFor_HighchartSeries ($pivoted4chart_data, $options, $removeFirstCol=false) {
		if (count($pivoted4chart_data) == 0)
			return array();
		
		foreach ($pivoted4chart_data as $row) { // get column name
			$colnames = array_keys($row);
			break;
		}
		
		if ($removeFirstCol)
			array_shift($colnames);
		
		$result = array();
		for ($i=0; $i<count($colnames); $i++) {
			$arr = array();
			$arr['name'] = $colnames[$i];
			$arr['data'] = array();
			$arr['zIndex'] = 0;
			foreach ($pivoted4chart_data as $row)
				if ($row[$colnames[$i]] === 'null' || $row[$colnames[$i]] === null )
					$arr['data'][] = null;
				else 
					$arr['data'][] = (int) $row[$colnames[$i]] + 0;
			if (isset($options['stackname']))
				$arr['stack'] = $options['stackname'];
			$arr['index'] = $i;
			if (isset($options['indexAdd']))
				$arr['index'] += $options['indexAdd'];
			$arr['legendIndex'] = count($colnames)-$i;
			if (isset($options['legendIndexAdd']))
				$arr['legendIndex'] += $options['legendIndexAdd'];
			if (isset($options['zIndexAdd']))
				$arr['zIndex'] += $options['zIndexAdd'];
			if (isset($options['color'])){				
				if (is_array($options['color'])) {
					$numColor = count($options['color']);
					$cIndex = $i % $numColor;
					$arr['color'] = $options['color'][$cIndex];
				} else {
					$arr['color'] = $options['color'];
				}
			}
			
			// options
			if (isset($options['type']))
				if (strpos($options['type'],'--')) {
					$linetype = substr($options['type'], strpos($options['type'],'--')+2,100);
					$arr['dashStyle'] = $linetype;
					$arr['type'] = substr($options['type'], 0, strpos($options['type'],'--'));
				}
				else
					$arr['type'] = $options['type'];
			else
				$arr['type'] = 'line';
			//$arr['type'] = $options['type']? $options['type'] : 'line';

			if (isset($options['yAxis']))
				$arr['yAxis'] = $options['yAxis'];
			else
				$arr['yAxis'] = 0;
				
			if ( isset($options['visible']) && !in_array($colnames[$i], $options['visible']) 
					|| isset($options['invisible']) && in_array($colnames[$i], $options['invisible']) )
				$arr['visible'] = false;
			
			$result[] = $arr;
		}
		
		return $result;
	}
	
	
// 	/* ***************
// 		$options = [	'title' => ,
// 						'subtitle' => ,
// 						'seriesname' => ,
// 					];
// 	*/
// 	function script_pieChart($chartdata, $options) {
// 		$str = "
// {
// 	chart: {
// 		plotBackgroundColor: null,
// 		plotBorderWidth: null,
// 		plotShadow: false,
// 		type: 'pie'
// 	},
// 	title: {
// 		text: '".str_replace("'",'',$options['title'])."'
// 	},
// 	subtitle: {
// 		text: '".str_replace("'",'',$options['subtitle'])."'
// 	},
// 	tooltip: {
// 		pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
// 	},
// 	plotOptions: {
// 		pie: {
// 			allowPointSelect: true,
// 			cursor: 'pointer',
// 			dataLabels: {
// 				enabled: true,
// 				format: '<b>{point.name}</b>: {point.percentage:.1f} %',
// 				style: {
// 					color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
// 				}
// 			}
// 		}
// 	},
// 	series: [{
// 		name: '".($options['seriesname']==''?' ':str_replace("'",'',$options['seriesname']))."',
// 		colorByPoint: true,
// 		data: ".json_encode($chartdata)."
// 	}]	
// }";
// 		return $str;
// 	}
	
// 	/* ***************
// 		$options = [	'title' => ,
// 						'subtitle' => ,
// 						'yAxis_title' => ,
// 						'chart_height' => ,
// 						'chart_backgroundColor' => ,
// 						'stack_col' => true / false
// 						'stack_area' => true / false
// 						'legend_align' => 'right',
// 					];
// 	*/
function script_lineChart($categories, $chartdata, $options) {
    // $this->fitBgrColorByTheme($options);
    $series = $chartdata;	
    $str = '
		{
		"title": {
			"text": "'.str_replace('"',"",$options["title"]).'"
		},
		"credits": {
			"enabled": false
		},
		"subtitle": {
			"text": "'.str_replace('"',"",$options["subtitle"]).'"
		},
		'.(!array_key_exists("tooltip", $options)?"":("tooltip:{".$options["tooltip"]."},")).'
		"xAxis": {
			"categories": '.json_encode($categories).'
		},
		"yAxis": {
			"title": {
				"text": "'.str_replace('"',"",$options["yAxis_title"]).'"
			},
			"min": 0
		},
		"legend": {
			"maxHeight": 100,
			"align": "'.(isset($options["legend_align"])?$options["legend_align"]:"center").'",
			"verticalAlign": "bottom"
		},
		"plotOptions": {
			"column": {'.(isset($options["stack_col"])===true?'"stacking": "normal"':'').
				'},
			"area": {'.(isset($options["stack_area"])===true?'"stacking": "normal"':'').'}
		},
		"series": '.json_encode($series, JSON_PARTIAL_OUTPUT_ON_ERROR).'
		}';	

    return $str;
}
// 	/* ***************
// 		$options = [	'title' => ,
// 						'subtitle' => ,
// 						'yAxis_title' => array(),
// 						'chart_height' => ,
// 						'stack_col' => true / false
// 						'stack_area' => true / false
// 					];
// 	*/
function script_chart2Y($categories, $chartdata, $options) {
    $str = '
		{
		"title": {
			"text": "'.str_replace('"',"",$options["title"]).'"
		},

		"subtitle": {
			"text": "'.str_replace('"',"",$options["subtitle"]).'"
		},

		"xAxis": {
			"categories": '.json_encode($categories).'
		},
		"yAxis": [{
			"title": {
				"text": "'.str_replace('"',"",$options["yAxis_title"][0]).'"
			},
			"min": 0
		},{
			"title": {
				"text": "'.str_replace('"',"",$options["yAxis_title"][1]).'"
			},
			"min": 0,
			"opposite": true
		}],
		"legend": {
			"align": "center",
			"verticalAlign": "bottom",
			"borderWidth": 1
		},

		"plotOptions": {
			"column": {'.(isset($options["stack_col"])===true?'"stacking": "normal"':'').'},
			"area": {'.(isset($options["stack_area"])===true?'"stacking": "normal"':'').'}
		},

		"series": '.json_encode($chartdata).'
		}';
    return $str;
}
// 	function script_chart2Y($categories, $chartdata, $options) {
// 		$str = "
// {
//     title: {
//         text: '".str_replace("'",'',$options['title'])."'
//     },

//     subtitle: {
//         text: '".str_replace("'",'',$options['subtitle'])."'
//     },
	
// 	chart: {
// 		".($options['chart_height']!=''?'height: '.$options['chart_height']:'')."
// 	},
	
//     xAxis: {
// 		categories: ".json_encode($categories)."
// 	},
//     yAxis: [{
//         title: {
//             text: '".str_replace("'",'',$options['yAxis_title'][0])."',
//         },
// 		min: 0,
//     },{
//         title: {
//             text: '".str_replace("'",'',$options['yAxis_title'][1])."',
//         },
// 		min: 0,
// 		opposite: true
//     }],
//     legend: {
//         align: 'center',
//         verticalAlign: 'bottom',
// 		borderWidth: 1,
//     },
	
//     plotOptions: {
// 		column: {".($options['stack_col']===true?"stacking: 'normal'":"")."},
// 		area: {".($options['stack_area']===true?"stacking: 'normal'":"")."}
//     },

//     series: ".json_encode($chartdata).",

//     responsive: {
//         rules: [{
//             condition: {
//                 maxWidth: 500
//             },
//             chartOptions: {
//                 legend: {
//                     layout: 'horizontal',
//                     align: 'center',
//                     verticalAlign: 'bottom'
//                 }
//             }
//         }]
//     }

// }";
// 		return $str;
// 	}
	
// 	/* ***************
// 		$options = [	'title' => ,
// 						'subtitle' => ,
// 						'yAxis_title' => ,
// 						'chart_height' => ,
// 					];
// 	*/
// 	function script_liveLineChart ($divid, $csvURL, $options) {
// 		$script = "
// Highcharts.chart('".$divid."', {
//     title: {
//         text: '".str_replace("'",'',$options['title'])."'
//     },

//     subtitle: {
//         text: '".str_replace("'",'',$options['subtitle'])."'
//     },
	
// 	chart: {
// 		".($options['chart_height']!=''?'height: '.$options['chart_height']:'')."
// 	},
	
//     yAxis: {
//         title: {
//             text: '".str_replace("'",'',$options['yAxis_title'])."',
//         },
// 		min: 0,
//     },
	
// 	data: {
// 		csvURL: '".$csvURL."',
// 		enablePolling: true,
// 		dataRefreshRate: 60
// 	},
	
// 	series:[{dashStyle:'ShortDot'}],
// });";
		
// 		return $script;
// 	}

// 	function script_pyramidChart($chartdata, $options) {
//         $str = 
//         "{
//             title: {
//                 text: '".str_replace("'",'',$options['title'])."'
// 			},
			
// 			subtitle: {
// 				text: '".str_replace("'",'',$options['subtitle'])."'
// 			},

// 			chart: {
//                 type: 'pyramid'
// 			},
			
//             series: [{
//                 name: 'Device',
//                 data: ".$chartdata."
//             }],
        
// 			responsive: {
// 				rules: [{
// 					condition: {
// 						maxWidth: 500
// 					}
// 				}]
// 			}
//         }";
// 		return $str;
// 	}
// 	function fitBgrColorByTheme(&$opt, $key){
//         if(array_key_exists($key,$opt)){
//             // echo('1');
//             $bgrColor = $opt[$key];
//             $colorCode = ltrim($bgrColor, '#');
//             if(!ctype_xdigit($colorCode)){
//                 $colorRgb = Color::getRGBColor(strtolower($bgrColor));
//                 $colorHex = Color::fromRGB($colorRgb)->getHex();
//                 $c = Color::fromRGB($colorRgb)->invert();                
//             }else{
//                 $colorHex = Color::fromHex($bgrColor)->getHex();
//                 $colorRgb = Color::fromHex($bgrColor)->getRGB();
//                 $c = Color::fromHex($bgrColor)->invert();
//                 // echo $c." ".$colorHex." ";
//             }             
//             // dd ($this->stringToColorCode('white'));
//             // echo $colorHex."\n";            
//             $hexValue = bin2hex(ltrim($colorHex, '#'));
//             if($this->darkMode){
//                 if(Color::fromHex($colorHex)->isBright()){                                                       
//                     $newColor = $this->lightToDark($colorRgb);
//                     $newHex = Color::fromRGB($newColor)->getHex();
//                     $opt[$key]  = $newHex;
//                     // echo $c;
//                 }
//             }
// 		}
//     }

//     function lightToDark($rgb){
//         $r = $rgb[0];
//         $g = $rgb[1];
//         $b = $rgb[2];
//         $min = min(180, $r, $g, $b);
//         $min += 10;
//         $res = [$r-$min, $g-$min, $b-$min];
//         return $res;
// 	}

// 	function script_stack_drilldown ($option, $data)
// 	{

// 		$function = '';
// 		$extend_function = "";
// 		$function .= ($option['extend_function'] == '' ? '' : $extend_function);
		
// 		if(array_key_exists('background_color_split', $option)){
// 			if (is_array($option['background_color_split'])){
// 				if (count($option['background_color_split']) == 1){
// 					$background_color_value = array_values($option['background_color_split']);
// 					$option['background_color_split'] = [$background_color_value[0], $background_color_value[0]];

// 				} elseif (count($option['background_color_split']) >= 2){
// 					$background_color_value = array_values($option['background_color_split']);
// 					$option['background_color_split'] = [$background_color_value[0], $background_color_value[1]];
// 				}
// 			} elseif (is_string($option['background_color_split'])) {
// 				$option['background_color_split'] = [$option['background_color_split'], $option['background_color_split']];
// 			}

// 			$option['background_color_split'][0] = $option['background_color_split'][0] === '' ? '#ffffff' : $option['background_color_split'][0];
// 			$option['background_color_split'][1] = $option['background_color_split'][1] === '' ? '#ffffff' : $option['background_color_split'][1];

// 		} else {
// 			$is_has_positive = false;
//             $is_has_negative = false;
//             foreach ($data as $data_value){
// 				foreach ($data_value as $series_value){
// 					foreach ($series_value['data'] as $key => $value){
// 						if ($value['y'] > 0){
// 							if (!$is_has_positive)
// 								$is_has_positive = true;
// 						}
// 						if ($value['y'] < 0){
// 							if (!$is_has_negative)
// 								$is_has_negative = true;
// 						}
// 					}
// 				}
//             }
//             if ($is_has_positive and $is_has_negative)
// 				$option['background_color_split'] = ['#eafaf1', '#fbeee6'];
// 			else
// 				$option['background_color_split'] = ['#ffffff', '#ffffff'];
// 		}
		
// 		// foreach ($option['background_color_split'] as $key => $value){
// 		// 	$option['background_color_split'][$key] = $this->fitColorByTheme($value);
// 		// }

//         $design = "
// 		{
// 			chart: {
//                 type: '".(!$option['chart_type'] ? 'column' : $option['chart_type'])."',
//                 events: {
// 					render: function() {
// 						if(!this.hideFlag){
// 							const chart = this,
// 								yAxis = chart.yAxis[0],
// 								top = yAxis.top,
// 								left = yAxis.left,
// 								zero = yAxis.toPixels(0),
// 								height = yAxis.height,
// 								width = yAxis.width,
// 								lowerHeight = height - (zero - top),
// 								upperHeight = height - lowerHeight;
									
// 							if (chart.LowerRect || chart.UpperRect) {
// 								chart.LowerRect.destroy();
// 								chart.UpperRect.destroy();
// 							}

// 							chart.UpperRect = chart.renderer.rect(left, top, width, upperHeight)
// 							.attr({
// 								fill: '".$option['background_color_split'][0]."'
// 							})
// 							.add();
// 							chart.LowerRect = chart.renderer.rect(left, zero, width, lowerHeight)
// 								.attr({
// 									fill: '".$option['background_color_split'][1]."'
// 								})
// 								.add();
// 						}
// 					},
// 					load() {
// 						this.hideFlag = false;
// 						".(!$option['hide_show_button']?'':'this.reflow();')."
// 					},
//                     drilldown: function(e) {
//                         if (!e.seriesOptions) {
//                             var chart = this,
//                                 drilldowns = ".json_encode($data['drilldown']).";
//                                 chart.setTitle(null, {text: e.point.subtitle});

//                                 var series_count=0;
//                                 var type = typeof drilldowns[e.point.drilldown_name];
//                                 while (type !== 'undefined') {
//                                   series_count += 1
//                                   var type = typeof drilldowns[e.point.drilldown_name.concat(series_count.toString())];
//                                 }
                     
//                                 var series = [];
// 								let subTitle = e.point.name;

//                                 for (i = 0; i < series_count; i++) {
// 								  var name_count = i == 0 ? '' : i.toString();
//                                   series.push(drilldowns[e.point.drilldown_name.concat(name_count.toString())]);
//                                   chart.addSingleSeriesAsDrilldown(e.point, series[i]);
// 								} 
								
//                                 chart.applyDrilldown();
//                         }
//                     },
//                     drillupall: function(e) {
// 						".(!$option['hide_show_button']?'':"
// 						this.series.forEach(series => {series.setVisible(true, false)});
// 						this.exportSVGElements[2].attr({ text: '<i class=\"fas fa-eye-slash fa-lg\"></i>' });
// 						this.hideFlag = false;")."

//                         var str = this.subtitle.textStr;
//                         var last_index = str.lastIndexOf(' / ');

//                         if (last_index == -1){
//                             this.setTitle(null, {text: this.userOptions.subtitle.text});
//                         } else {
//                             this.setTitle(null, {text: str.substring(0, last_index)});
//                         }
// 					}
//                 }                    
//             },
//             title: {
// 				text: '".$option['title']."'
// 			},
// 			subtitle: {
// 				text: '".$option['subtitle']."'
// 			},
// 			xAxis: {
// 				type: 'category',
// 				labels: {
// 					//rotation: 0,
// 					autoRotation: [0, -45]
// 				}
//             },
//             yAxis: {
// 				title: {
// 					text: '#'
// 				}
// 			},
// 			legend: {
// 				align: '".($option['legend_align']?$option['legend_align']:'right')."',
// 				verticalAlign: 'top',
// 				borderWidth: 1,
// 				".($option['legend_align']=='right'?'layout: \'vertical\',':'')."
// 				x: 0,
// 				y: 50
// 			},
// 			plotOptions: {
// 				series: {
// 				    ".($option['is_stacking']?"stacking: 'normal',":'')."					
// 				}
// 			},
//             series: ".json_encode($data['series']).",
// 			drilldown: {
//                 series: [],
//                 drillUpButton: {
// 					position: {
// 						verticalAlign: 'top',
// 						x: -30,
// 						y: -50
//                     },
//                 },
//                 animation: {
//                     duration: 500
//                 },
// 			},
// 			".(!$option['hide_show_button']?'':"
// 			exporting: {
// 				buttons: {
// 					hide_button: {				
// 						text: '<i class=\"fas fa-eye-slash fa-lg\"></i>',
// 						align: 'right',
// 						verticalAlign: 'top',
// 						x: -40,
// 						y: 0,								
// 						symbolSize: 12,
// 						".($this->darkMode?"theme: {
// 							fill: '#cccccc'
// 						},":"")."
// 						onclick: function () {
// 							if (this.hideFlag) {
// 								this.series.forEach(series => {series.setVisible(true, false)});
// 								this.exportSVGElements[2].attr({ text: '<i class=\"fas fa-eye-slash fa-lg\"></i>' });
// 							} else {
// 								this.series.forEach(series => {series.setVisible(false, false)});
// 								this.exportSVGElements[2].attr({ text: '<i class=\"fas fa-eye fa-lg\"></i>' });					
// 							}
// 							this.hideFlag = !this.hideFlag;
// 							this.redraw();
// 						}
// 					}
// 				}
// 			},")."
// 			lang: {
// 				drillUpText: '◁ Back'
// 			}
// 		}, 
// 		function (chart) {
// 			".$function."
// 		}";

// 		return $design;
// 	}
}
