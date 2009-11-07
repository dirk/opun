<?php
class Data {
	/*
	Manages persistent data storage for the application.
	*/
	var $file, $data;
	
	function Data($file = 'app/data/serialized.store'){
		$this->file = $file;
		if(file_exists($this->file)){
			$this->data = unserialize(file_get_contents($this->file));
		}else{
			file_put_contents($this->file, serialize($this->data));
		}
		
		
		
		
		/*
		
		$this->data = array(
			'slave.masters' => array(
				array(
					'slave.bandwidth.used' => 0,
					'slave.bandwidth.total' => 0,
					'slave.bandwidth.maximum' => 1000000000,
					'slave.bandwidth.month' => 11,
					'slave.clients.total' => 0,
					'slave.last_update' => time(),
					'master.identifier' => 'localhost.opun.master',
					'master.gateway' => 'http://localhost/opun/master/gateway.php',
					'slave.packages' => array(
						array(
							'file'     => 'test.zip',
							'checksum' => '',
							'serving'  => true
						)
					),
					'master.packages' => array(
						'test.zip'
					)
				)
			)
		);
		*/
	}
	
	function key($key, $value = null, $write = true){
		if(!is_null($value)){
			$this->data[$key] = $value;
			if($write){
				$this->commit();
			}
		}else{
			return $this->data[$key];
		}
	}
	function commit() {
		file_put_contents($this->file, serialize($this->data));
	}
}