<?php
include('lib/opun.php');

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
				/*$response = $slave['oscen.instance']->request('slave.packages.list.update', 
					array(
						'master.packages.list' => array(
							array('file' => 'test.zip', 'checksum' => ''),
							array('file' => 'test2.zip', 'checksum' => '')
						)
					), $this->config['identifier']);*/
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
	
	
	// Complete Implementations:
	/*
	slave.packages.check_servability:
	$slave['oscen.instance']->request('slave.packages.check_servability', array('request.package' => 'test.zip'), $this->config['identifier']);
	
	
	*/
	
	
	
	
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