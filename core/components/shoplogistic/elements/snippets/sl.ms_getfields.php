<?php
if($modx->user->id){
	$criteria = array(
		"user_id" => $modx->user->id
	);

	$c = $modx->newQuery('msOrder');
	$c->sortby('id','DESC');
	$c->where($criteria);
	$order = $modx->getObject("msOrder", $c);
	if($order){
		$ord = $order->toArray();
		$ord["address"] = $order->getOne("Address")->toArray();
		$ord["address"]["email"] = $order->getOne("UserProfile")->get("email");
		//$modx->log(1, print_r($ord, 1));
		$modx->toPlaceholders($ord);
	}
}