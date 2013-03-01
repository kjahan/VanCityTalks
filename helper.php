<?php
class Helper{
	//define radius for segmentation
	function phpArrayToJSArray($regions){
		$js_array = "";
		$cnt = 0;
		#$max_ = $k1*$k2;
		$max_ = count($regions);
		//echo count($regions) . "<br>";
		foreach ($regions as &$value){
			if ($cnt == 0)
				$js_array .= '[["'.implode('","', $value).'"],';
			elseif ($cnt == $max_ - 1){
				$js_array .= '["'.implode('","' , $value).'"]]';
				break;
			}
			else
				$js_array .= '["'.implode('","', $value).'"],';
			$cnt += 1;
		}
		//echo $cnt . "<br>";
		//echo $js_array;
		return $js_array;
	}
} 
?>