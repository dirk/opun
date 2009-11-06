<?php

error_reporting(E_ALL & ~E_NOTICE);

class Oscen {
	/*
	Open-source PHP library implementation for the Oscen distributed master-slave
	content delivery network (CDN). 
	*/
	
	var $version = 0.1;
	var $protocol = 0.1;
	
	var $packages = array(
		'list' => array(),
		'missing' => array(),
		'total' => array()
	);
	var $secret = 'aabbccdd';
	
	var $gateway = 'http://localhost/opun/slave/gateway.php'; // Location of the remote gateway.
	
	function Oscen($config = array()) {
		if($config['secret']){
			$this->secret = $config['secret'];
		}
	}
	function sign($type, $data) {
		return md5($type . '&' . http_build_query($data) . '&' . $this->secret);
	}
	function request($type, $data) {
		$handle = curl_init();
		ksort($data);
		$data['signature'] = $this->sign($type, $data);
		
		curl_setopt($handle, CURLOPT_URL, $this->gateway . '?' . $type);
		curl_setopt($handle, CURLOPT_POST, 1);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($handle);
		curl_close($handle);
		return $this->verify($type, $result);
	}
	function verify($type, $result) {
		$data = $this->parse($result);
		$verify_data = array();
		foreach($data as $key => $value) {
			if($key != 'signature'){
				$verify_data[$key] = $value;
			}
		}
		ksort($verify_data);
		if($this->sign($type, $verify_data) == $data['signature']){
			return $data;
		}else{
			return false;
		}
	}
	function parse($request){
		$data = array();
		$items = explode('&', $request);
		foreach($items as $item){
			$kv = explode('=', $item);$key = urldecode($kv[0]);$value = urldecode($kv[1]);
			$data[$key] = $value;
		}
		return $data;
	}
}
$oscen = new Oscen();
$data = $oscen->request('network.verify', array('this' => 'that', 'that' => 'this'));

print_r($data);