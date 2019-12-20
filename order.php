<?php
ob_start();
 function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = '';

    return $ipaddress;
}

function get_referer() 
{
	$referer = '';
	if (isset($_SERVER['HTTP_REFERER']))
		$referer = $_SERVER['HTTP_REFERER'];
	
	return $referer;
}

function getRequestParams(){
	$params = array();
	
	// take get params from referer
	$params['referer'] = get_referer();
	$parts = parse_url($params['referer']);
	parse_str($parts['query'], $query);	
	
	
	if(isset($_POST['product_id']))
		$params['product_id'] = $_POST['product_id'];
	
	if(isset($_POST['partner_id']))
		$params['partner_id'] = $_POST['partner_id'];
	
	if(isset($_POST['order_custom']))
		$params['order_custom'] = $_POST['order_custom'];
	else $params['order_custom'] = '';
	
	if(isset($_POST['order_custom1']))
		$params['order_custom1'] = $_POST['order_custom1'];
	else $params['order_custom1'] = '';
	
	if(isset($_POST['order_custom2']))
		$params['order_custom2'] = $_POST['order_custom2'];
	else $params['order_custom2'] = '';
	
	if(isset($_POST['order_custom3']))
		$params['order_custom3'] = $_POST['order_custom3'];
	else $params['order_custom3'] = '';
	
	if(isset($_POST['order_custom4']))
		$params['order_custom4'] = $_POST['order_custom4'];
	else $params['order_custom4'] = '';
	
	if(isset($_POST['transaction_id']))
		$params['transaction_id'] = $_POST['transaction_id'];
	else $params['transaction_id'] = '';
	
	if(isset($_POST['comment']))
		$params['comment'] = $_POST['comment'];
	else $params['comment'] = '';
	
	if(isset($_POST['name']))
		$params['name'] = $_POST['name'];
	
	if(isset($_POST['email']))
		$params['email'] = $_POST['name'];
	else $params['email'] = '';
	
	
	if(isset($_POST['phone']))
		$params['phone'] = $_POST['phone'];
	
	if(isset($_POST['result_url']))
		$params['result_url'] = $_POST['result_url'];
	
	return $params;
}

function redirect($url, $statusCode = 303)
{
   header('Location: ' . $url, true, $statusCode);
   die();
}

function sendToRyumka($p) {
	$url = 'http://cc.salesup-crm.com/PostOrder.aspx';
	
	$data = array(
		'product_id' => $p['product_id'],
		'partner_id' => $p['partner_id'],
		'phone' => $p['phone'],
		'name' => $p['name'],
		'email' => $p['email'],
		'transaction_id'=> $p['transaction_id'],
		'order_custom' => $p['order_custom'],
		'order_custom1' => $p['order_custom1'],
		'order_custom2' => $p['order_custom2'],
		'order_custom3' => $p['order_custom3'],
		'order_custom4' => $p['order_custom4'],
		'comment' => $p['comment'],
		'referrer' => parse_url(get_referer(), PHP_URL_HOST),
		'order_url' => get_referer(),
		'ip' => get_client_ip()
	);
	
	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);	
	if ($result === FALSE) { echo 'ERROR'; return $result; /* Handle error */ }
	  
	$resultSendingRyumka = json_decode($result);
	
	$RyumkaOrderID = '-1';
	if(isset($resultSendingRyumka->id))
		$RyumkaOrderID = $resultSendingRyumka->id;

	return $RyumkaOrderID;
	
}



    //main logic --------------
	
	$request_params = getRequestParams();	
	$orderId = sendToRyumka($request_params); 
	redirect($request_params['https://vladimirkostin.github.io/vovablog/trust.html']); //redirect to thnx page
 ?>   