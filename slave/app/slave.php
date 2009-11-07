<?php
include('lib/opun.php');

class Slave extends Opun {
	var $data, $masters, $config;
	
	function Slave($datastore, $config) {
		$this->data = $datastore;$this->config = $config;
		
		// Initialize Oscen instances for each master.
		$masters = $this->data->key('slave.masters');
		foreach($masters as $master){
			$master['oscen.instance'] = new Oscen(array(
				'gateway' => $master['master.gateway'],
				'secret' => 'aabbccdd'
			));
			$this->masters[] = $master;
		}
	}
	
	function gateway() {
		$qs = $_SERVER['QUERY_STRING'];
		$parts = explode('/', $qs);
		$masters = $this->masters;
		$master = null;
		foreach($masters as $master){
			if($parts[0] = $master['master.identifier']){
				$master = $parts[0];
				$type = $parts[1];
				break;
			}
		}
		if($master = &$this->get_master($master)){
			if($type == 'slave.status'){
				$this->slave_status($master);
			}else if($type == 'slave.packages.check_servability'){
				$this->slave_packages_check_servability($master);
			}else if($type == 'slave.packages.list.update'){
				$this->slave_packages_list_update($master);
			}else{
				echo 500;
			}
			/*}else{
				$master = &$this->get_master($master);
				print_r();
			}*/
		}
	}
	/*
	Complete Implentations:
	$master['oscen.instance']->request('master.packages.info', array(
		'request.package' => 'test.zip'
	), $this->config['identifier'])
	*/
	function &get_master($master){
		for($i = 0; $i < count($this->masters); $i++){
			if($this->masters[$i]['master.identifier'] = $master){
				return $this->masters[$i];
			}
		}
	}
	function get_package($package, $master = null){
		if($master){
			for($i = 0; $i < count($master['slave.packages']); $i++){
				if($master['slave.packages'][$i]['file'] == $package){
					return $master['slave.packages'][$i];
				}
			}
		}
		return null;
	}
	function slave_packages_list_update($master){
		$post = parse_post();
		if($params = $master['oscen.instance']->verify('slave.packages.list.update', $post)){
			$master['master.packages'] = $params['master.packages.list'];
			$this->commit_masters_data();
		}else{
			echo 404;
		}
	}
	function slave_packages_check_servability($master){
		$post = parse_post($_POST);
		if($params = $master['oscen.instance']->verify('slave.packages.check_servability', $post)){
			if($package = $this->get_package($params['request.package'], $master)){
				if($package['serving']){
					$data = array('request.package.servability' => 1);
				}else{
					$data = array('request.package.servability' => 0);
				}
				echo $master['oscen.instance']->response('slave.packages.check_servability', $data);
			}else{
				echo 404;
			}
		}else{
			echo 403;
		}
	}
	function slave_status($master) {
		$packages = array();
		$packages_serving = array();
		foreach($master['slave.packages'] as $package){
			$packages[] = $package['file'];
			if($package['serving']){
				$packages_serving[] = $package['file'];
			}
		}
		$master_packages = array();
		foreach($master['master.packages'] as $package){
			$master_packages[] = $package['file'];
		}
		
		$data = array(
			'slave.bandwidth.remaining' => $master['slave.bandwidth.maximum'] - $master['slave.bandwidth.used'],
			'slave.bandwidth.maximum' => $master['slave.bandwidth.maximum'],
			'slave.bandwidth.used' => $master['slave.bandwidth.used'],
			'slave.bandwidth.total' => $master['slave.bandwidth.total'],
			'slave.packages' => $packages,
			'slave.packages.serving' => $packages_serving,
			'slave.last_update' => $master['slave.last_update'],
			'slave.clients.total' => $master['slave.clients.total'],
			'master.packages' => $master_packages
		);
		echo $master['oscen.instance']->response('slave.status', $data);
	}
	
	function commit_masters_data() {
		$data = array();
		foreach($this->masters as $master){
			$append = array();
			foreach($master as $key => $value){
				if(starts_with($key, 'slave') or starts_with($key, 'master')){
					$append[$key] = $value;
				}
			}
			$data[] = $append;
		}
		$this->data->key('slave.masters', $data);
	}
}