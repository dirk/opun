<?php
$config = array();
// ----------CONFIG START------------
// Set up configuration data in here!
// 
// This is the password to access your Opun instance.
$config['password']   = 'opun-master'; 
// Unique string to identify this Opun instance.
$config['identifier'] = 'localhost.opun.master';
// The URL for this Opun instance's gateway. Should be something like:
// "http://master.example.com/gateway.php"
$config['gateway'] = 'http://localhost/opun/master/gateway.php';
// The base location of the master directory, with trailing slash.
$config['base']    = 'http://localhost/opun/master/';
// The directory to store uploaded packages.
$config['packages'] = 'packages';

$config['slaves'] = array();
	// Time (seconds) between sending slave.status requests to slaves.
	$config['slaves']['status_timeout'] = 120; // 2 minutes
// 
// -----------CONFIG END-------------



// Pull in the base system.
include('app/master.php');
include('app/lib/data.php');
// Load the application database file. This is exposed in case you want to use
// a custom database layer (EG: A wrapper for MySQL or similar system).
// The default database layer is located in app/lib/data.php.
$datastore = new Data();

// Initialize the new Opun Master instance with a datastore and
// configuration variables.
$master = new Master($datastore, $config);