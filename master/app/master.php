<?php
include('lib/opun.php');

class Master extends Opun {
	var $data, $slaves;
	
	function Master($datastore, $config){
		$this->data = $datastore;$this->config = $config;
		
		$this->boilerplate();
		
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
		if(count($this->slaves) == 0){
			$this->slaves = array(
				array(
					'slave.gateway' => 'http://localhost/opun/slave/gateway.php',
					'slave.secret' => 'aabbccdd',
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
		}
	}
	
	function route() {
		$qs = $_SERVER['QUERY_STRING'];
		if($qs == '') {
			$this->dashboard();
		}else if($qs == 'status'){
			foreach($this->slaves as $slave){
				
			}
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