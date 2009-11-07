<?php
// You'll want to change this to something more secure in production.
error_reporting(E_ALL & ~E_NOTICE);

$is_gateway = true;
include('app/slave.php');
include('app/lib/data.php');
$datastore = new Data();

$slave = new Slave($datastore);
$slave->gateway();





die();




function sign($type, $data) {
	return md5($type . '&' . http_build_query($data) . '&' . 'aabbccdd');
}

$type = $_SERVER['QUERY_STRING'];
if($type == 'network.verify'){
	/*
	- slave.identifier (com.esherido.keyframe.east)
	- slave.gateway (http://east.keyframe.esherido.com/gateway.php)
	- slave.protocol.version (=0.1)*/
	$data = array(
		'slave.identifier' => 'localhost.slave',
		'slave.gateway' => 'http://localhost/opun/slave/gateway.php',
		'slave.protocol.version' => 0.1
	);
}else if($type == 'slave.status'){
	/*
	Response:
	- slave.bandwidth.remaining (1,000,000,000 bytes)
	- slave.bandwidth.maximum (2,000,000,000 bytes)
	- slave.bandwidth.used (1,000,000,000 bytes)
	- slave.bandwidth.total (150,000,000,000 bytes)
	- slave.packages.missing ([a.zip, c.mov.qt])
	- slave.packages.list ([a.zip, b.zip, c.mov.qt]; list of the packages the slave knows about)
	- slave.packages.serving ([b.zip])
	- slave.last_update (UNIX timestamp)
	- slave.clients.total (1,234; total number of clients the slave has served)
	- master.packages.total ([a.zip, b.zip, c.mov.qt, d.mov.qt])
	- request.timestamp (Used by slave to update slave.last_update)
	*/
	$data = array(
		'slave.bandwidth.remaing' => '1000000000',
		'slave.bandwidth.maximum' => '2000000000',
		'slave.bandwidth.used'    => '1000000000',
		'slave.bandwidth.total'   => '150000000000',
		'slave.packages.missing'  => array(),
		'slave.packages.list'     => array(),
		'slave.packages.serving'  => array(),
		'slave.last_update'       => 1257550253 - 300,
		'slave.clients.total'     => 1234,
		'master.packages.list'    => array()
	);
}

ksort($data);

$data['signature'] = sign($type, $data);
echo http_build_query($data);