<?php
/*
- slave.identifier (com.esherido.keyframe.east)
- slave.gateway (http://east.keyframe.esherido.com/gateway.php)
- slave.protocol.version (=0.1)*/
$data = array(
	'slave.identifier' => 'localhost.slave',
	'slave.gateway' => 'http://localhost/opun/slave/gateway.php',
	'slave.protocol.version' => 0.1
);
ksort($data);
$type = $_SERVER['QUERY_STRING'];

//print_r($data);

function sign($type, $data) {
	return md5($type . '&' . http_build_query($data) . '&' . 'aabbccdd');
}
$data['signature'] = sign($type, $data);

echo http_build_query($data);