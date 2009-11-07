<?php
include('lib/opun.php');

class Master {
	var $slaves, $packages;
	
	var $datastore, $config;
	function Master($datastore, $config) {
		session_start();
		$this->datastore = $datastore; $this->config = $config;
		
		if(!$this->load()){
			$this->packages = array(
				array(
					'file' => 'test.zip',
					'checksum' => '',
					'release' => time() - 300
				)
			);

			$this->slaves = array(
				'localhost.opun.slave' => array(
					'gateway' => 'http://localhost/opun/slave/gateway.php?',
					'identifier' => 'localhost.opun.slave',
					'secret' => 'aabbccdd11223344',
					'bandwidth' => array(
						'used' => 0,
						'maximum' => 1000000000,
						'total' => 25000000000
					),
					'clients' => 0,
					'last_status' => time() - 300,
					'packages' => array(
						'slave' => array(
							array(
								'file' => 'test.zip',
								'checksum' => '',
								'serving' => false,
							)
						),
						'master' => array(
							array(
								'file' => 'test.zip',
								'checksum' => ''
							)
						)
					),
				)
			);
			$this->save();
		}
	}
	function update_slave_status($slave_id) {
		$slave =& $this->slaves[$slave_id];
		
		$status = $this->request($slave['gateway'] . '/status.json', array(
			'master' => $this->config['identifier']), $slave['secret']);
		if(!is_numeric($status)){
			$data = json_decode($status, true);
			$slave['bandwidth']['maximum'] = $data['bandwidth_maximum'];
			$slave['bandwidth']['used'] = $data['bandwidth_used'];
			$slave['bandwidth']['total'] = $data['bandwidth_total'];
			$packages = array();
			foreach($data['packages'] as $package){
				$rev = strrev($package);
				$parts = array_reverse(explode(':', $rev, 2));
				$parts = array_map('strrev', $parts);
				$append = array(
					'file' => $parts[0],
					'checksum' => $parts[1],
					'serving' => (in_array($package, $data['packages_serving'])) ? true : false
				);
				$packages[] = $append;
			}
			$slave['packages']['slave'] = $packages;
			$packages_master = array();
			foreach($data['packages_master'] as $package){
				$rev = strrev($package);
				$parts = array_reverse(explode(':', $rev, 2));
				$parts = array_map('strrev', $parts);
				$append = array(
					'file' => $parts[0],
					'checksum' => $parts[1]
				);
				$packages_master[] = $append;
			}
			$slave['packages']['master'] = $packages_master;
			$slave['clients'] = $data['clients'];
			$slave['last_status'] = time();
			$slave['last_update'] = $data['last_update'];
		}
	}
	
	function request($res, $data = array(), $signature = ''){
		// Makes a request to a remote server. Include optional signing functionality.
		$handle = curl_init();
		if($signature != ''){
			$data['signature'] = md5($this->config['identifier'] .':'. $signature);
		}
		
		curl_setopt($handle, CURLOPT_URL, $res);
		curl_setopt($handle, CURLOPT_POST, 1);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($handle);
		curl_close($handle);
		return $result;
	}
	
	function route(){
		$qs = $_SERVER['QUERY_STRING'];
		// Routes the admin section
		if($_SESSION['password'] != sha1($this->config['password'])){
			$this->login();
		}else{
			if(starts_with($qs, '/logout')){
				$this->logout();
			}else if($qs == '/' or $qs == ''){
				$this->dashboard();
			}else{
				echo 404;
			}
		}
	}
		function dashboard() {
			$slaves = array();
			/*
			$this->slaves = array(
				'localhost.opun.slave' => array(
					'gateway' => 'http://localhost/opun/slave/gateway.php?',
					'identifier' => 'localhost.opun.slave',
					'secret' => 'aabbccdd11223344',
					'bandwidth' => array(
						'used' => 0,
						'maximum' => 1000000000,
						'total' => 25000000000
					),
					'clients' => 0,
					'last_status' => time() - 300,
					'packages' => array(
						'slave' => array(
							array(
								'file' => 'test.zip',
								'checksum' => '',
								'serving' => false,
							)
						),
						'master' => array(
							array(
								'file' => 'test.zip',
								'checksum' => ''
							)
						)
					),
				)
			);*/
			$total_packages = count($this->packages);
			foreach($this->slaves as $key => $slave){
				$append = array(
					'identifier' => $key
				);
				$slave_packages = 0;
				foreach($slave['packages']['slave'] as $package){
					foreach($this->packages as $master_package){
						if($master_package['file'] == $package['file'] && $master_package['checksum'] == $package['checksum']){
							$slave_packages++;
						}
					}
				}
				$append['packages'] = array(
					'total'   => $total_packages,
					'slave'   => $slave_packages,
					'percent' => $slave_packages / $total_packages
				);
				$slaves[] = $append;
			}
			$data = array(
				'slaves' => $slaves
			);
			$this->render('dashboard', $data);
		}
		function login() {
			if($_POST['password']){
				if($_POST['password'] == $this->config['password']){
					$_SESSION['password'] = sha1($this->config['password']);
					$this->redirect('');
				}
			}
			$this->render('login');
		}
		function logout() {
			unset($_SESSION['password']);
			$this->redirect('login');
		}
	
	// Templating methods
	function link($resource){echo $this->config['base'] . '?/' . $resource;}
	function url($resource){echo $this->config['base'] . $resource;}
	function render($template, $vars = array()){
		foreach($vars as $key => $value){
			$$key = $value;
		}
		include('app/views/' . $template . '.php');
	}
	function redirect($resource) {
		header('Location: ?/' . $resource);
		die();
	}
	function gateway(){
		// Routes the gateway.
		if($matches = $this->match('/^packages(?:.(?<format>[a-z]+))?/i')){
			$this->packages(strtolower($matches['format']));
		}else{
			echo 404;
		}
	}
		function packages($format) {
			$packages = array();
		
			foreach($this->packages as $package){
				$packages[] = $package['file'] .':'. $package['checksum'];
			}
		
			if($format == 'json'){
				echo json_encode($packages);
			}
		}
	
	function match($expr, $qs = '') {
		if($qs == ''){
			$qs = $_SERVER['QUERY_STRING'];
		}
		if(starts_with($qs, '/')){
			$qs = substr($qs, 1);
		}
		if(preg_match($expr, $qs, $matches)){
			return $matches;
		}else{
			return false;
		}
	}
	function save() {
		$data = array(
			'slaves' => $this->slaves,
			'packages' => $this->packages
		);
		
		$this->datastore->data = $data;
		$this->datastore->commit();
	}
	function load(){
		if($this->datastore->data['slaves'] and $this->datastore->data['packages']){
			$this->slaves   = $this->datastore->data['slaves'];
			$this->packages = $this->datastore->data['packages'];
			return true;
		}else{return false;}
	}
}

/*

class Master extends Opun {
	var $data, $slaves;
	
	function Master($datastore, $config){
		$this->data = $datastore;$this->config = $config;
		
		$this->boilerplate();
		
		$this->packages = $this->data->key('master.packages');
		// Setting up Oscen instances for each slave.
		for($i = 0; $i < count($this->slaves); $i++){
			$this->slaves[$i]['oscen.instance'] = new Oscen(array(
				'gateway' => $this->slaves[$i]['slave.gateway'],
				'secret'  => $this->slaves[$i]['slave.secret']
			));
		}
	}
	// This should be removed eventually.
	function boilerplate(){
		$this->data->key('master.packages', array(
			array(
				'file' => 'test.zip',
				'release' => time() - 1000
			)
		));
		
		$this->slaves = $this->data->key('master.slaves');
		//if(count($this->slaves) == 0){
			$this->slaves = array(
				array(
					'slave.gateway' => 'http://localhost/opun/slave/gateway.php',
					'slave.secret' => 'aabbccdd',
					'slave.identifier' => 'localhost.opun.slave',
					'slave.packages' => array(
						array(
							'file'     => 'test.zip',
							'checksum' => '',
							'serving'  => false
						)
					)
				)
			);
			$this->data->key('master.slaves', $this->slaves);
		//}
	}
	
	function route() {
		$qs = $_SERVER['QUERY_STRING'];
		if($qs == '') {
			$this->dashboard();
		}else if($qs == 'status'){
			foreach($this->slaves as $slave){
				//$response = 
				//print_r($response);
			}
		}
	}
	function gateway() {
		$qs = $_SERVER['QUERY_STRING'];
		$parts = explode('/', $qs);
		$slave = null;
		for($i = 0; $i < count($this->slaves); $i++) {
			if($parts[0] = $this->slaves[$i]['slave.identifier']){
				$slave =& $this->slaves[$i];
				$type = $parts[1];
				break;
			}
		}
		if($slave){
			if($type == 'master.packages.list'){
				$this->master_packages_list($slave);
			}else if($type == 'master.packages.info'){
				$this->master_packages_info($slave);
			}else{
				echo 500;
			}
		}
	}
	function master_packages_list($slave) {
		echo $slave['oscen.instance']->response('master.packages.list',
			array('master.packages.list' => $this->packages));
	}
	function master_packages_info($slave){
		$post = parse_post();
		if($params = $slave['oscen.instance']->verify('master.packages.info', $post)){
			foreach($this->packages as $package) {
				if($package['file'] == $params['request.package']){
					echo $slave['oscen.instance']->response('master.packages.info', array(
						'request.package.checksum' => $package['checksum'],
						'request.package.file.path' => $this->config['base'] . $this->config['packages'] . '/' . $package['file']
					));
					return;
				}
			}
			echo 404;
		}else{
			echo 403;
		}
	}
	
	function dashboard() {
		$packages = $this->data->key('master.packages');
		foreach($packages as $package) {
			echo $package['file'] . ' &mdash; ' . $package['release'];
		}
	}
	
	// Sends out slave.status requests to the slaves that have not been checked
	// in $config['slaves']['timeout_period'].
	function update_slaves_status() {
		$update = false;
		$timeout = $this->config['slaves']['status_timeout'];
		foreach($this->slaves as $slave){
			if($slave['slave.last_status'] < (time() - $timeout)){
				$update = true;
				break;
			}
		}
		if($update){
			//echo 'Updating';
			$i = 0;
			foreach($this->slaves as $slave){
				if($slave['slave.last_status'] < (time() - $timeout)){
					$status = $slave['oscen.instance']->request('slave.status', array(), $this->config['identifier']);
					unset($status['signature']);
					$this->slaves[$i] = array_merge_replace_recursive($slave, $status);
					$this->slaves[$i]['slave.last_status'] = time();
				}
				$i++;
			}
			$this->commit_slaves_data();
		}
	}
	function commit_packages() {
		$this->data->key('master.packages', $this->packages);
	}
	function commit_slaves_data() {
		$data = array();
		foreach($this->slaves as $slave){
			$append = array();
			foreach($slave as $key => $value){
				if(starts_with($key, 'slave')){
					$append[$key] = $value;
				}
			}
			$data[] = $append;
		}
		$this->data->key('master.slaves', $data);
	}
}
*/