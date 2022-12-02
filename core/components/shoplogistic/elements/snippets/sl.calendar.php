<?php
$start = microtime(true);

if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	return false;
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);

// return (array) week:
// [unix] => (int) unixtime
// [day] => (int) day_number
// [future] => (bool) future or past
// [diff_month] => (bool) different month or not
// [resources] => (array) Array of resources

// dynamyc params
if (!empty($_REQUEST['month'])) {
	$month = (int) $_REQUEST['month'];
}else if (!empty($month)) {
	$month = $month;
} else {
	$month = date('n');
}
if (!empty($_REQUEST['year'])) {
	$year = (int) $_REQUEST['year'];
}else if (!empty($year)) {
	$year = $year;
} else {
	$year = date('Y');
}
$parent = !empty($parent) ? $parent : $modx->resourceIdentifier;
$dateSource = !empty($dateSource) ? $dateSource : 'publishedon';
// dynamyc params ^^
$data_out = array();
$tmp = array();
if ($parent) {
	$tmp = $modx->getChildIds($parent);
}
//$modx->log(1, $parent.print_r($tmp, 1));
$query = $modx->newQuery('slWarehouseShipment');
$query->where(array(
	'active' => true,
	'warehouse_id' => $_REQUEST['col_id']? : $_REQUEST['warehouse_id']
));
$resources = $modx->getCollection('slWarehouseShipment',$query);
//$modx->log(1, count($resources));


$day_count = 1;
$day = mktime(0, 0, 0, $month, $day_count, $year);
$now = time();
$month_days = date('t', $day) + 1;
$num = 0;
$data_out['month'] = $day;
// get previos month
$current_month = (int) $month;
if($current_month == 1){
	$prev_month = 12;
	$prev_year = $year - 1;
}else{
	$prev_month = $current_month - 1;
	$prev_year = $year;
}
$data_out['prev_month'] = $prev_month;
$data_out['prev_year'] = $prev_year;
$prev_day = mktime(0, 0, 0, $prev_month, $day_count, $prev_year);

// get next month
if($current_month == 12){
	$next_month = 1;
	$next_year = $year + 1;
}else{
	$next_month = $current_month + 1;
	$next_year = $year;
}
$data_out['next_month'] = $next_month;
$data_out['next_year'] = $next_year;
$next_day = mktime(0, 0, 0, $next_month, $day_count, $next_year);
//$modx->log(1, microtime(true) - $start);
// checked first week
for($i = 0; $i < 7; $i++) {
	$dayofweek = date('w', mktime(0, 0, 0, date('m', $day), $day_count, date('Y', $day)));
	$dayofweek = $dayofweek - 1;

	$temp = array();
	$this_day = mktime(0, 0, 0, $month, $day_count, $year);
	$this_day_end = mktime(23, 59, 0, $month, $day_count, $year);
	// to class
	foreach($resources as $res){
		if (preg_match('/^tv/i', $dateSource)) {
			$tv = $res->get('date');
			if (!empty($tv)) {
				$date = strtotime($tv);
				if($date >= $this_day && $date <= $this_day_end){
					$res_arr = $res->toArray();
					$res_arr['date'] = $date;
					$temp['resources'][] = $res_arr;
				}
			}
		}else{
			$date = strtotime($res->get($dateSource));
			if($date >= $this_day && $date <= $this_day_end){
				$res_arr = $res->toArray();
				$res_arr['date'] = $date;
				$temp['resources'][] = $res_arr;
			}
		}
	}
	if($now >= $this_day && $now <= $this_day_end){
		$temp['now'] = 1;
	}
	// to class ^^
	$temp['unix'] = $this_day;

	if($dayofweek == -1) $dayofweek = 6;
	if($dayofweek == $i){
		$temp['day'] = $day_count;
		if($this_day >= $now){
			$temp['future'] = 1;
		}else{
			$temp['future'] = 0;
		}
		$temp['diff_month'] = 0;
		$week[$num][$i] = $temp;
		$day_count++;
	}else{
		$temp = array();
		// add previos month days
		$prev_month_days = date('t', $prev_day);
		$this_prev_day = $prev_month_days - $dayofweek + $i + 1;
		$thiser_day = mktime(0, 0, 0, $prev_month, $this_prev_day, $prev_year);
		$thiser_day_end = mktime(23, 59, 0, $prev_month, $this_prev_day, $prev_year);
		// to class
		foreach($resources as $res){
			if (preg_match('/^tv/i', $dateSource)) {
				$tv = $res->get('date');
				if (!empty($tv)) {
					$date = strtotime($tv);
					if($date >= $thiser_day && $date <= $thiser_day_end){
						$res_arr = $res->toArray();
						$res_arr['date'] = $date;
						$temp['resources'][] = $res_arr;
					}
				}
			}else{
				$date = strtotime($res->get($dateSource));
				if($date >= $thiser_day && $date <= $thiser_day_end){
					$res_arr = $res->toArray();
					$res_arr['date'] = $date;
					$temp['resources'][] = $res_arr;
				}
			}
		}
		// to class ^^
		$temp['unix'] = $thiser_day;
		$temp['day'] = $this_prev_day;
		if($thiser_day >= $now){
			$temp['future'] = 1;
		}else{
			$temp['future'] = 0;
		}
		$temp['diff_month'] = 1;
		$week[$num][$i] = $temp;
	}
}
// checked next weeks
while(true){
	$num++;
	$j = 0;
	for($i = 0; $i < 7; $i++){
		$temp = array();
		$temp['day'] = $day_count;
		$this_day = mktime(0, 0, 0, $month, $day_count, $year);
		$this_day_end = mktime(23, 59, 0, $month, $day_count, $year);
		// to class
		foreach($resources as $res){
			if (preg_match('/^tv/i', $dateSource)) {
				$tv = $res->get('date');
				if (!empty($tv)) {
					$date = strtotime($tv);
					if($date >= $this_day && $date <= $this_day_end){
						$res_arr = $res->toArray();
						$res_arr['date'] = $date;
						$temp['resources'][] = $res_arr;
					}
				}
			}else{
				$date = strtotime($res->get($dateSource));
				if($date >= $this_day && $date <= $this_day_end){
					$res_arr = $res->toArray();
					$res_arr['date'] = $date;
					$temp['resources'][] = $res_arr;
				}
			}
		}
		if($now >= $this_day && $now <= $this_day_end){
			$temp['now'] = 1;
		}
		// to class ^^
		$temp['unix'] = $this_day;
		if($this_day >= $now){
			$temp['future'] = 1;
		}else{
			$temp['future'] = 0;
		}
		$temp['diff_month'] = 0;
		$day_count++;
		// add next month days
		if($day_count > $month_days && $i < 7) {
			$next_month_days = date('t', $next_day);
			$this_next_day = 1 + $j;
			$thiser_day = mktime(0, 0, 0, $next_month, $this_next_day, $next_year);
			$thiser_day_end = mktime(23, 59, 0, $next_month, $this_next_day, $next_year);
			// to class
			foreach($resources as $res){
				if (preg_match('/^tv/i', $dateSource)) {
					$tv = $res->get('date');
					if (!empty($tv)) {
						$date = strtotime($tv);
						if($date >= $thiser_day && $date <= $thiser_day_end){
							$res_arr = $res->toArray();
							$res_arr['date'] = $date;
							$temp['resources'][] = $res_arr;
						}
					}
				}else{
					$date = strtotime($res->get($dateSource));
					if($date >= $thiser_day && $date <= $thiser_day_end){
						$res_arr = $res->toArray();
						$res_arr['date'] = $date;
						$temp['resources'][] = $res_arr;
					}
				}
			}
			// to class ^^
			$temp['unix'] = $thiser_day;
			$temp['day'] = $this_next_day;
			if($thiser_day >= $now){
				$temp['future'] = 1;
			}else{
				$temp['future'] = 0;
			}
			$temp['diff_month'] = 1;
			$j++;
		}
		$week[$num][$i] = $temp;
	}
	if($day_count > $month_days) break;
}

$data_out['calendar'] = $week;
$output = $pdoFetch->getChunk($tpl, $data_out);
echo $output;