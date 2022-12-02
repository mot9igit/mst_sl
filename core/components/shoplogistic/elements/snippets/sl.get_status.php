<?php
if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	return false;
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);

$out  = '';

$criteria = array(
	"active" => 1
);
$query = $modx->newQuery('msOrderStatus', $criteria);
$query->sortby('rank', 'ASC');
$statuses = $modx->getCollection("msOrderStatus", $query);

$output = array(
	"checked" => $id
);
foreach($statuses as $status){
	$output['statuses'][] = $status->toArray();
}

$out .= $pdoFetch->getChunk($tpl, $output);

return $out;