<?php
include('lib/util.php');

class Master {
	var $slaves, $packages;
	
	var $datastore, $config;
	function Master($datastore, $config) {
		session_start();
		$this->datastore = $datastore; $this->config = $config;
		
		if(!$this->load()){
			$this->packages = array();
			$this->slaves = array();
			/*
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
			);*/
			$this->save();
		}
		// Check for outdated slaves and fetch new information.
		foreach($this->slaves as $key => $slave) {
			if($slave['last_status'] < (time() - $this->config['slaves']['status_timeout'])) {
				$this->update_slave_status($key);
				$this->save();
				$this->check_slave_package_list($key);
			}
		}
	}
	function check_slave_package_list($slave_id) {
		$slave =& $this->slaves[$slave_id];
		
		$slave_packages = array();
		foreach($slave['packages']['master'] as $package) {
			$slave_packages[] = $package['file'] .':'. $package['checksum'];
		}
		sort($slave_packages);
		
		$master_packages = array();
		foreach($this->packages as $package) {
			$master_packages[] = $package['file'] .':'. $package['checksum'];
		}
		sort($master_packages);
		
		if($slave_packages != $master_packages) {
			$this->request($slave['gateway'] . '/packages/update', array(
				'master' => $this->config['identifier']), $slave['secret']);
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
			return true;
		}else{
			return false;
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
			}else if(starts_with($qs, '/packages/new')){
				$this->packages_new();
			}else if(starts_with($qs, '/slaves/new')){
				$this->slaves_new();
			}else if($matches = $this->match('/^slaves\/reverify\/(?<slave>.+)/i')){
				$this->slaves_reverify($matches['slave']);
			}else if($matches = $this->match('/^slaves\/update\/(?<slave>.+)/i')){
				$this->slaves_update($matches['slave']);
			}else if($matches = $this->match('/^packages\/success\/(?<file>.+)/i')){
				$this->packages_success($matches['file']);
			}else if($matches = $this->match('/^packages\/edit\/(?<file>.+)/i')){
				$this->packages_edit($matches['file']);
			}else if($matches = $this->match('/^packages\/delete\/(?<file>.+)/i')){
				$this->packages_delete($matches['file']);
			}else if($matches = $this->match('/^packages\/checksum\/javascript\/(?<file>.+)/i')){
				$this->packages_checksum_javascript($matches['file']);
			}else if($matches = $this->match('/^packages\/checksum\/(?<file>.+)/i')){
				$this->packages_checksum($matches['file']);
			}else if($qs == '/' or $qs == ''){
				$this->dashboard();
			}else{
				echo 404;
			}
		}
	}
		function dashboard() {
			$slaves = array();
			$total_packages = count($this->packages);
			foreach($this->slaves as $key => $slave){
				$append = array(
					'identifier' => $key,
					'bandwidth' => ($slave['bandwidth']['maximum'] > 0) ? ($slave['bandwidth']['used'] / $slave['bandwidth']['maximum']) : 0
				);
				$slave_packages = 0;
				foreach($slave['packages']['slave'] as $package){
					foreach($this->packages as $master_package){
						if($master_package['file'] == $package['file'] && $master_package['checksum'] == $package['checksum']){
							$slave_packages++;
						}
					}
				}
				if($total_packages == 0) {
					$percent = 0;
				}else{
					$percent = $slave_packages / $total_packages;
				}
				$append['packages'] = array(
					'total'   => $total_packages,
					'slave'   => $slave_packages,
					'percent' => $percent
				);
				$slaves[] = $append;
			}
			$packages = array();
			foreach($this->packages as $package){
				$append = $package;
				$slaves_with_package = 0;
				foreach($this->slaves as $key => $slave){
					foreach($slave['packages']['slave'] as $slave_package){
						if($slave_package['file'] == $package['file'] && $slave_package['checksum'] == $package['checksum']){
							$slaves_with_package++;
						}
					}
				}
				$append['total'] = $slaves_with_package;
				$packages[] = $append;
			}
			$data = array(
				'slaves' => $slaves,
				'packages' => $packages
			);
			$this->render('dashboard', $data);
		}
		function packages_success($file){
			foreach($this->packages as $package){
				if($package['file'] == $file){break;}
			}
			$this->render('packages/success', array('package' => $package));
		}
		function packages_checksum($file){
			foreach($this->packages as $package){
				if($package['file'] == $file){break;}
			}
			if($package['file'] == $file){
				$file = $this->config['packages'] .'/'. $file;
				if(file_exists($file)) {
						$this->render('packages/checksum', array('package' => $package));
				}else{
					echo 404;
				}
			}else{
				echo 404;
			}
		}
		function packages_checksum_javascript($file) {
			$package = null;
			for($i = 0; $i < count($this->packages); $i++){
				if($this->packages[$i]['file'] == $file){
					$package =& $this->packages[$i];
					break;
				}
			}
			if($package){
				$file = $this->config['packages'] .'/'. $file;
				if(file_exists($file)) {
					$checksum = md5_file($file);
					$package['checksum'] = $checksum;
					$this->save();
					echo 200;
				}else{
					echo 404;
				}
			}else{echo 404;}
		}
		function packages_edit($file) {
			$package = null;
			for($i = 0; $i < count($this->packages); $i++){
				if($this->packages[$i]['file'] == $file){
					$package =& $this->packages[$i];
					break;
				}
			}
			if($package){
				if(!empty($_POST)){
					$package['release'] = strtotime($_POST['release_month'] . '/' . $_POST['release_day'] . '/' . $_POST['release_year'] . ' ' . $_POST['release_hour'] . ':' . $_POST['release_minute']);
					if($_POST['file'] != $package['file']){
						$package['checksum'] = '';
					}
					$package['file'] = $_POST['file'];
					$this->save();
					$this->redirect('');
				}
				$this->render('packages/edit', array('package' => $package));
			}else{echo 404;}
		}
		function packages_delete($file) {
			$deleted = false;
			for($i = 0; $i < count($this->packages); $i++){
				if($this->packages[$i]['file'] == $file){
					unset($this->packages[$i]);
					$this->save();
					$deleted = true;
					break;
				}
			}
			if($deleted){
				$this->render('header', array('title' => 'Packages'));
				?>
					<h2 class="section">Package Deleted</h2>
					<p style="line-height: 150%;">
						The package was successfully removed. However, you must manually remove the file. Click <a href="<?php $this->link(''); ?>">here</a> to return to the dashboard.
					</p>
				<?php
				$this->render('footer');
			}else{echo 404;}
		}
		function packages_new() {
			$data = array(
				'package' => array('file' => ($_POST['file']) ? $_POST['file'] : '', 'release' => time()),
				'title' => 'New Package'
			);
			if($_POST['file']){
				$this->packages[] = array(
					'file' => $_POST['file'],
					'checksum' => '',
					'release' => strtotime($_POST['release_month'] . '/' . $_POST['release_day'] . '/' . $_POST['release_year'] . ' ' . $_POST['release_hour'] . ':' . $_POST['release_minute'])
				);
				$this->save();
				$this->redirect('packages/success/' . $_POST['file']);
			}
			$this->render('packages/edit', $data);
		}
		function slaves_new() {
			if(!empty($_POST)){
				$slave = array(
					'gateway' => $_POST['gateway'],
					'identifier' => $_POST['identifier'],
					'secret' => $_POST['secret'],
					'bandwidth' => array('used' => 0, 'maximum' => 0, 'total' => 0),
					'clients' => 0,
					'last_status' => time(),
					'packages' => array(
						'slave' => array(),
						'master' => array()
					),
				);
				$this->slaves[$_POST['identifier']] = $slave;
				$this->save();
				if($this->remote_slave_verify($_POST['gateway'], $_POST['identifier'], $_POST['secret'])){
					$this->update_slave_status($_POST['identifier']);
					$this->save();
					$this->redirect('');
				}else{
					unset($this->slaves[$_POST['identifier']]);
					$this->save();
				}
			}
			$this->render('slaves/edit', array('new' => true));
		}
		function slaves_reverify($slave) {
			if($slave = $this->slaves[$slave]){
				if($this->remote_slave_verify($slave['gateway'], $slave['identifier'], $slave['secret'])){
					$this->redirect('');
				}else{echo 500;}
			}else{echo 404;}
			
		}
		function slaves_update($slave) {
			if($slave = $this->slaves[$slave]){
				$this->update_slave_status($slave['identifier']);
				$this->save();
				$this->check_slave_package_list($slave['identifier']);
				
				$this->redirect('');
			}else{echo 404;}
		}
		function remote_slave_verify($gateway, $identifier, $secret) {
			$status = $this->request($gateway . '/masters/verify/' . $this->config['identifier'], array(), $secret);
			if($status == 200) {
				return true;
			}else{
				echo $status;
				return false;
			}
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
	
	function preflush() {
		@apache_setenv('no-gzip', 1);
		@ini_set('zlib.output_compression', 0);
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
		if($matches = $this->match('/^packages\/download\/(?<file>.+)/i')){
			$this->gateway_packages_download(strtolower($matches['file']));
		}else if($matches = $this->match('/^download\/automatic\/(?<file>.+)/i')){
			$this->download_automatic(strtolower($matches['file']));
		}else if($matches = $this->match('/^packages(?:.(?<format>[a-z]+))?/i')){
			$this->gateway_packages(strtolower($matches['format']));
		}else{
			echo 404;
		}
	}
		function download_automatic($package) {
			$found = false;
			foreach($this->packages as &$pack) {
				if($package == $pack['file']){
					$package =& $pack;
					$package['size'] = filesize($this->config['packages'] .'/'. $package['file']);
					$found = true;
				}
			}
			if(!$found){echo 404; return;}
			
			$possible_slaves = array();
			foreach($this->slaves as $slave) {
				foreach($slave['packages']['slave'] as $slave_package) {
					if($slave_package['file'] == $package['file'] and $slave_package['checksum'] && $package['checksum'] && $slave_package['serving'] == true){
						//$possible_slaves[] = $slave;
						if(($slave['bandwidth']['maximum'] - $slave['bandwidth']['used']) > $package['size']){
							$possible_slaves[] = $slave;
						}
					}
				}
			}
			
			foreach($possible_slaves as &$slave) {
				$slave['bandwidth']['percent'] = ($slave['bandwidth']['maximum'] > 0) ? ($slave['bandwidth']['used'] / $slave['bandwidth']['maximum']) : 0;
			}
			
			function user_sort_slaves_bandwidth($a, $b){
				if ($a['bandwidth']['percent'] == $b['bandwidth']['percent']) {
			  	return 0;
				}
				return ($a['bandwidth']['percent'] < $b['bandwidth']['percent']) ? -1 : 1;
			}
			usort($possible_slaves, 'user_sort_slaves_bandwidth');
			
			header('Location: ' . $possible_slaves[0]['gateway'] .'/packages/serve/'. $this->config['identifier'] .'/'. $package['file']);
		}
		function gateway_packages($format) {
			if(!$this->verify_slave()){
				echo 403;
				return;
			}
			
			$packages = array();
			foreach($this->packages as $package){
				if(file_exists($this->config['packages'] .'/'. $package['file']) && $package['checksum'] != ''){
					$packages[] = $package['file'] .':'. $package['checksum'];
				}
			}
		
			if($format == 'json'){
				echo json_encode($packages);
			}
		}
		function gateway_packages_download($file) {
			if(!$this->verify_slave()){
				echo 403;
				return;
			}
			
			foreach($this->packages as $package){
				if($package['file'] == $file){break;}
			}
			if($package['file'] == $file){
				$file = $this->config['packages'] .'/'. $file;
				if(file_exists($file)) {
					echo $this->config['base'] . $file;
				}else{
					echo 404;
				}
			}else{
				echo 404;
			}
		}
	function verify_slave(){
		$verified = false;
		foreach($this->slaves as $key => $slave){
			if($key == $_POST['slave']){
				$signature = md5($slave['identifier'] .':'. $slave['secret']);
				if($signature == $_POST['signature']){
					$verified = true;
					break;
				}
			}
		}
		return $verified;
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
		if(is_array($this->datastore->data['slaves']) and is_array($this->datastore->data['packages'])){
			$this->slaves   = $this->datastore->data['slaves'];
			$this->packages = $this->datastore->data['packages'];
			return true;
		}else{return false;}
	}
}