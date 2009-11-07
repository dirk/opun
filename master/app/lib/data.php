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