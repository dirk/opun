<?php
include('lib/util.php');

class Slave {
	var $masters;
	var $secret = 'aabbccdd';
	
	var $datastore, $config;
	function Slave($datastore, $config) {
		session_start();
		$this->datastore = $datastore; $this->config = $config;
		
		if(!$this->load()){
			$this->masters = array();
			$this->save();
			/*
			$this->masters = array(
				'localhost.opun.master' => array(
					'identifier' => 'localhost.opun.master',
					'gateway' => 'http://localhost/opun/master/gateway.php?',
					'verified' => true,
					'bandwidth' => array(
						'used' => 750000000,
						'maximum' => 1000000000,
						'total' => 25000000000,
						'month' => 11
					),
					'packages' => array(
						'slave' => array(
							array(
								'file' => 'test.zip',
								'checksum' => '986d6075457c9282446d149190130435',
								'serving' => true,
								'bandwidth' => 0,
								'clients' => 0
							)
						),
						'master' => array(
							array(
								'file' => 'test.zip',
								'checksum' => '986d6075457c9282446d149190130435'
							)
						)
					),
				)
			);*/
		}
		$changed = false;
		foreach($this->masters as &$master) {
			if($master['bandwidth']['month'] != intval(date('n'))){
				$master['bandwidth']['used'] = 0;
				$master['bandwidth']['month'] = intval(date('n'));
				$changed = true;
			}
			foreach($master['packages']['slave'] as &$package) {
				if(!file_exists($this->get_package_file($master['identifier'], $package['file']))){
					$package['checksum'] = '';
					$package['serving']  = false;
					$changed = true;
				}
				foreach($master['packages']['master'] as $master_package) {
					if($package['file'] == $master_package['file'] and $package['checksum'] != $master_package['checksum']) {
						$package['serving'] = false;
					}
				}
			}
		}
		if($changed) {$this->save();}
	}
	
	function route(){
		$qs = $_SERVER['QUERY_STRING'];
		// Routes the admin section
		if($_SESSION['password'] != sha1($this->config['password'])){
			$this->login();
		}else{
			if(starts_with($qs, '/logout')){
				$this->logout();
			}else if(starts_with($qs, '/masters/new')){
				$this->masters_new();
			}else if($matches = $this->match('/^masters\/edit\/(?<master>.+)/i')){
				$this->masters_edit($matches['master']);
			}else if($matches = $this->match('/^masters\/view\/(?<master>.+)/i')){
				$this->masters_view($matches['master']);
			}else if($matches = $this->match('/^packages\/add\/(?<master>[^\/]+)\/(?<package>.+)/i')){
				$this->masters_packages_add($matches['master'], $matches['package']);
			}else if($matches = $this->match('/^packages\/automatic\/(?<master>[^\/]+)\/(?<package>.+)/i')){
				$this->masters_packages_automatic($matches['master'], $matches['package']);
			}else if($matches = $this->match('/^packages\/checksum\/(?<master>[^\/]+)\/(?<package>.+)/i')){
				$this->masters_packages_checksum($matches['master'], $matches['package']);
			}else if($matches = $this->match('/^packages\/delete\/(?<master>[^\/]+)\/(?<package>.+)/i')){
				$this->masters_packages_delete($matches['master'], $matches['package']);
			}else if($matches = $this->match('/^packages\/serving\/(?<master>[^\/]+)\/(?<package>.+)/i')){
				$this->masters_packages_toggle_serving($matches['master'], $matches['package']);
			}else if($qs == '/' or $qs == ''){
				$this->dashboard();
			}else{
				echo 404;
			}
		}
	}
		function dashboard() {
			$tasks = array();
			$masters = array();
			foreach($this->masters as $master){
				$serving_packages = 0;
				$not_serving_packages = 0;
				$corrupted_packages = 0;
				$deprecated_packages = 0;
				foreach($master['packages']['slave'] as $slave_package) {
					$deprecated = true;
					foreach($master['packages']['master'] as $master_package) {
						if($slave_package['file'] == $master_package['file']){
							$deprecated = false;
							if($slave_package['checksum'] == $master_package['checksum']){
								if($slave_package['serving'] == true){
									$serving_packages++;
								}else{
									$not_serving_packages++;
								}
							}else{
								$corrupted_packages++;
							}
						}
					}
					if($deprecated){
						$deprecated_packages++;
					}
				}
				$master['packages']['serving'] = $serving_packages;
				$master['packages']['not_serving'] = $not_serving_packages;
				$master['packages']['corrupted'] = $corrupted_packages;
				$master['packages']['deprecated'] = $deprecated_packages;
				$master['bandwidth']['percent'] = ($master['bandwidth']['maximum'] > 0) ? ($master['bandwidth']['used'] / $master['bandwidth']['maximum']) : 0;
				$masters[] = $master;
			}
			$this->render('dashboard', array('masters' => $masters));
		}
		function masters_view($master) {
			$master =& $this->masters[$master];
			if(!$master){echo 404; return;}
			
			$serving_packages = array();
			$not_serving_packages = array();
			$corrupted_packages = array();
			$deprecated_packages = array();
			$missing_packages = array();
			foreach($master['packages']['slave'] as $slave_package) {
				$deprecated = true;
				foreach($master['packages']['master'] as $master_package) {
					if($slave_package['file'] == $master_package['file']){
						$found = true;
						$deprecated = false;
						if($slave_package['checksum'] == $master_package['checksum']){
							if($slave_package['serving'] == true){
								$serving_packages[] = $slave_package; //
							}else{
								$not_serving_packages[] = $slave_package; //
							}
						}else{
							$corrupted_packages[] = $slave_package; //
						}
					}
				}
				if($deprecated){
					$deprecated_packages[] = $slave_package;
				}
			}
			foreach($master['packages']['master'] as $master_package){
				$not_found = true;
				foreach($master['packages']['slave'] as $slave_package) {
					if($slave_package['file'] == $master_package['file']){
						$not_found = false;
					}
				}
				if($not_found) {
					$missing_packages[] = $master_package;
				}
			}
			$master['packages']['missing'] = $missing_packages;
			$master['packages']['serving'] = $serving_packages;
			$master['packages']['not_serving'] = $not_serving_packages;
			$master['packages']['corrupted'] = $corrupted_packages;
			$master['packages']['deprecated'] = $deprecated_packages;
			
			$this->render('masters/view', array('master' => $master));
		}
 		function masters_edit($master) {
			$master =& $this->masters[$master];
			if(!$master){echo 404; return;}
			if(!empty($_POST)){
				$master['identifier'] = $_POST['identifier'];
				$master['gateway'] = $_POST['gateway'];
				$master['bandwidth']['maximum'] = round(intval($_POST['bandwidth_maximum']) * 1000000);
				$this->save();
				$this->redirect('');
			}
			$this->render('masters/edit', array('master' => $master));
		}
		function masters_packages_add($master, $package) {
			$master =& $this->masters[$master];
			if(!$master){echo 404; return;}
			
			$already_has_package = false;
			foreach($master['packages']['slave'] as $slave_package) {
				if($slave_package['file'] == $package) {
					$already_has_package = true;
				}
			}
			if($already_has_package){echo 500; return;}
			
			$not_found = true;
			foreach($master['packages']['master'] as $master_package) {
				if($master_package['file'] == $package) {
					$package = $master_package;
					$not_found = false;
				}
			}
			if($not_found){echo 404; return;}
			
			$package['serving']   = false;
			$package['checksum']  = '';
			$package['bandwidth'] = 0;
			$package['clients']   = 0;
			$master['packages']['slave'][] = $package;
			$this->save();
			
			$download_location = $this->request($master['gateway'] . '/packages/download/' . $package['file'], array(
				'slave' => $this->config['identifier']
			), $this->config['secret']);
			
			$this->render('packages/add', array(
				'package' => $package,
				'master' => $master,
				'location' => $download_location
			));
		}
		function masters_packages_checksum($master, $package) {
			$master =& $this->masters[$master];
			if(!$master){echo 404; return;}
			$not_found = true;
			foreach($master['packages']['master'] as $master_package) {
				if($master_package['file'] == $package) {
					$package = $master_package;
					$not_found = false;
				}
			}
			if($not_found){echo 404; return;}
			
			$this->render('packages/checksum', array(
				'master' => $master,
				'package' => $package
			));
		}
		function masters_packages_delete($master, $package) {
			$master =& $this->masters[$master];
			if(!$master){echo 404; return;}
			
			$deleted = false;
			for($i = 0; $i < count($master['packages']['slave']); $i++) {
				if($master['packages']['slave'][$i]['file'] == $package) {
					$file = $this->get_package_file($master['identifier'], $package);
					unset($master['packages']['slave'][$i]);
					if(file_exists($file)){
						unlink($file);
					}
					$deleted = true;
					$this->save();
					
					$this->redirect('masters/view/' . $master['identifier']);
				}
			}
			if(!$deleted) {
				echo 404;
			}
		}
		function masters_packages_automatic($master, $package) {
			$master =& $this->masters[$master];
			if(!$master){echo 404; return;}
			$not_found = true;
			foreach($master['packages']['master'] as $master_package) {
				if($master_package['file'] == $package) {
					$package = $master_package;
					$not_found = false;
				}
			}
			if($not_found){echo 404; return;}
			
			$download_location = $this->request($master['gateway'] . '/packages/download/' . $package['file'], array(
				'slave' => $this->config['identifier']
			), $this->config['secret']);
			
			@apache_setenv('no-gzip', 1);
			@ini_set('zlib.output_compression', 0);
			
			$this->render('packages/automatic', array(
				'download_location' => $download_location,
				'master' => $master,
				'package' => $package
			));
		}
		function masters_packages_toggle_serving($master, $package) {
			$master =& $this->masters[$master];
			if(!$master){echo 404; return;}
			$not_found = true;
			foreach($master['packages']['slave'] as &$master_package) {
				if($master_package['file'] == $package) {
					$package =& $master_package;
					$not_found = false;
				}
			}
			if($not_found){echo 404; return;}
			
			if($package['serving']){
				$package['serving'] = false;
			}else{$package['serving'] = true;}
			$this->save();
			$this->redirect('masters/view/' . $master['identifier']);
		}
		function masters_new() {
			$empty_master = array('identifier' => '','gateway' => '');
			if(!empty($_POST)){
				$master = array(
					'identifier' => $_POST['identifier'],
					'gateway' => $_POST['gateway'],
					'verified' => false,
					'last_update' => 0,
					'bandwidth' => array('used' => 0, 'maximum' => 0, 'total' => 0, 'month' => intval(date('n'))),
					'packages' => array(
						'slave' => array(),
						'master' => array()
					)
				);
				$this->masters[$_POST['identifier']] = $master;
				$this->save();
				$this->redirect('');
			}
			$this->render('masters/edit', array('new' => true, 'title' => 'New Master', 'master' => $empty_master));
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
	// -------
	// GATEWAY
	function gateway(){
		if($matches = $this->match('/^status(?:.(?<format>[a-z]+))?/i')){
			$this->status(strtolower($matches['format']));
		}else if($this->match('/^packages\/update/')){
			$this->gateway_update_packages();
		}else if($matches = $this->match('/^masters\/verify\/(?<file>.+)/')){
			$this->gateway_verify_master($matches['file']);
		}else if($matches = $this->match('/^packages\/serve\/(?<master>[^\/]+)\/(?<package>.+)/i')){
			$this->gateway_serve_package($matches['master'], $matches['package']);
		}else{
			echo 404;
		}
		$this->save();
	}
		function status($format){
			if($master = $_POST['master']) {
				$signature = md5($master .':'. $this->config['secret']);
				if($signature != $_POST['signature']){
					echo 403;
					return;
				}
				$master =& $this->masters[$master];
				$clients = 0;
				$packages = array();$packages_serving = array();
				foreach($master['packages']['slave'] as $package){
					$packages[] = $package['file'] .':'. $package['checksum'];
					$clients += $package['clients'];
					if($package['serving']){
						$packages_serving[] = $package['file'] .':'. $package['checksum'];
					}
				}
				$packages_master = array();
				foreach($master['packages']['master'] as $package){
					$packages_master[] = $package['file'] .':'. $package['checksum'];
				}
				sort($packages);sort($packages_serving);sort($packages_master);
				$data = array(
					'bandwidth_maximum' => $master['bandwidth']['maximum'],
					'bandwidth_used'    => $master['bandwidth']['used'],
					'bandwidth_total'   => $master['bandwidth']['total'],
					'packages' => $packages,
					'packages_serving' => $packages_serving,
					'packages_master' => $packages_master,
					'last_update' => time(),
					'clients' => $clients
				);
			}
		
			if($format == 'json'){
				echo json_encode($data);
			}
		}
		function gateway_verify_master($identifier) {
			if($master =& $this->masters[$identifier]){
				$signature = md5($master['identifier'] .':'. $this->config['secret']);
				if($signature != $_POST['signature']){
					echo 403;
					return;
				}
				$master['verified'] = true;
				$packages = $this->update_packages($master['identifier']);
				if(is_array($packages)){
					$this->save();
					echo 200;
				}else{
					echo 500;
				}
			}else{
				echo 404;
			}
		}
		function gateway_serve_package($master, $package) {
			$master =& $this->masters[$master];
			if(!$master){echo 404; return;}
			$not_found = true;
			foreach($master['packages']['slave'] as &$slave_package) {
				if($slave_package['file'] == $package) {
					$package =& $slave_package;
					$not_found = false;
				}
			}
			if($not_found){echo 404; return;}
			
			$file = $this->get_package_file($master['identifier'], $package['file']);
			$size = filesize($file);
			$master['bandwidth']['used'] += $size;
			$master['clients'] += 1;
			$package['bandwidth'] += $size;
			$package['clients'] += 1;
			$this->save();
			
			header('Location: '. $this->config['base'] . $file);
		}
		function gateway_update_packages() {
			if($master = $_POST['master']) {
				$signature = md5($master .':'. $this->config['secret']);
				if($signature != $_POST['signature']){
					echo 403;
					return;
				}
				$master =& $this->masters[$master];
				$packages = $this->update_packages($master['identifier']);
				if(is_array($packages)) {
					$master['packages']['master'] = $packages;
					$master['last_update'] = time();
					$this->save();
					echo 200;
				}else{
					echo 500;
				}
			}else{
				echo 403;
			}
		}
	// ----------
	// TEMPLATING
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
	// -------
	// UTILITY
	function get_package_file($master, $package) {
		return $this->config['packages'] .'/'. $master .'-'. $package;
	}
	function update_packages($master){
		$master = $this->masters[$master];
		
		$packages = $this->request($master['gateway'] . '/packages.json', array(
			'slave' => $this->config['identifier']
		), $this->config['secret']);
		if(!is_numeric($packages)){
			$package_list = json_decode($packages);
			if(is_array($package_list)){
				$packages = array();
				foreach($package_list as $package){
					$rev = strrev($package);
					$parts = array_reverse(explode(':', $rev, 2));
					$parts = array_map('strrev', $parts);
					$append = array(
						'file' => $parts[0],
						'checksum' => $parts[1]
					);
					$packages[] = $append;
				}
				return $packages;
			}else{
				return array();
			}
		}else{return false;}
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
	function save() {
		$data = array(
			'masters' => $this->masters
		);
		$this->datastore->data = $data;
		$this->datastore->commit();
	}
	function load() {
		if(is_array($this->datastore->data['masters'])){
			$this->masters = $this->datastore->data['masters'];
			return true;
		}else{return false;}
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
}