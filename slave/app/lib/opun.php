<?php
include('oscen.php');

class Opun {
	/*
	Base framework for both the Opun master and slave systems.
	*/
	var $oscen;
	
	// Not really much in here right now 'eh?
	function Opun() {
		$this->oscen = new Oscen();
	}
}