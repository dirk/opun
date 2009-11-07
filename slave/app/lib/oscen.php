<?php

function parse_post(){
	$data = array();
	foreach($_POST as $key => $value){
		$keys = explode('_', $key, 3);
		$key = implode('.', $keys);
		$data[$key] = $value;
	}
	return $data;
}

function starts_with($string, $search) {
    return (strncmp($string, $search, strlen($search)) == 0);
}

/**
 * Merges any number of arrays of any dimensions, the later overwriting
 * previous keys, unless the key is numeric, in whitch case, duplicated
 * values will not be added.
 *
 * The arrays to be merged are passed as arguments to the function.
 *
 * @access public
 * @return array Resulting array, once all have been merged
 */
function array_merge_replace_recursive() {
    // Holds all the arrays passed
    $params = & func_get_args ();
   
    // First array is used as the base, everything else overwrites on it
    $return = array_shift ( $params );
   
    // Merge all arrays on the first array
    foreach ( $params as $array ) {
        foreach ( $array as $key => $value ) {
            // Numeric keyed values are added (unless already there)
            if (is_numeric ( $key ) && (! in_array ( $value, $return ))) {
                if (is_array ( $value )) {
                    $return [] = $this->array_merge_replace_recursive ( $return [$$key], $value );
                } else {
                    $return [] = $value;
                }
               
            // String keyed values are replaced
            } else {
                if (isset ( $return [$key] ) && is_array ( $value ) && is_array ( $return [$key] )) {
                    $return [$key] = $this->array_merge_replace_recursive ( $return [$$key], $value );
                } else {
                    $return [$key] = $value;
                }
            }
        }
    }
   
    return $return;
}

class Oscen {
	/*
	Open-source PHP library implementation for the Oscen distributed master-slave
	content delivery network (CDN). 
	*/
	
	var $version = 0.1;
	var $protocol = 0.1;
	
	var $secret = '';
	var $gateway = ''; // Location of the remote gateway.
	
	function Oscen($config = array()) {
		if($config['secret']){
			$this->secret = $config['secret'];
		}
		if($config['gateway']){
			$this->gateway = $config['gateway'];
		}
	}
	function sign($type, $data) {
		return md5($type . '&' . http_build_query($data) . '&' . $this->secret);
	}
	function request($type, $data = array(), $identifier = '') {
		$handle = curl_init();
		ksort($data);
		$data['signature'] = $this->sign($type, $data);
		
		$base = $this->gateway . '?';
		if($identifier != ''){
			$base .= $identifier . '/' . $type;
		}else{
			$base .= $type;
		}
		curl_setopt($handle, CURLOPT_URL, $base);
		curl_setopt($handle, CURLOPT_POST, 1);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($handle);
		curl_close($handle);
		return $this->verify($type, $result);
	}
	function response($type, $data) {
		ksort($data);
		$data['signature'] = $this->sign($type, $data);
		
		return http_build_query($data);
	}
	function verify($type, $result) {
		if(is_array($result)){
			$data = $result;
		}else{
			$data = $this->parse($result);
		}
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