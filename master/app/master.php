<?php
include('lib/opun.php');

class Master extends Opun {
	
	var $data;
	
	var $slaves;
	
	
	function Master($datastore, $config){
		$this->data = $datastore;$this->config = $config;
		
		$this->slaves = $this->data->key('master.slaves');
		if(count($this->slaves) == 0){
			$this->slaves = array(
				array(
					'slave.gateway' => 'http://localhost/opun/slave/gateway.php',
					'slave.secret' => 'aabbccdd'
				)
			);
		}
		for($i = 0; $i < count($this->slaves); $i++){
			$this->slaves[$i]['oscen.instance'] = new Oscen(array(
				'gateway' => $this->slaves[$i]['slave.gateway'],
				'secret'  => $this->slaves[$i]['slave.secret']
			));
		}
	}
	
	function route(){
		$qs = $_SERVER['QUERY_STRING'];
		if($qs == ''){
			$this->slave_status();
		}
	}
	
	function slave_status(){
		$this->update_slaves_status();
	}
	
	function update_slaves_status() {
		$update = false;
		foreach($this->slaves as $slave){
			if($slave['slave.last_status'] < (time() - 300)){
				$update = true;
				break;
			}
		}
		if($update){
			echo 'Updating';
			$i = 0;
			foreach($this->slaves as $slave){
				if($slave['slave.last_status'] < (time() - 300)){
					$status = $slave['oscen.instance']->request('slave.status');
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