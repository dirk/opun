<?php
// You'll want to change this to something more secure in production.
error_reporting(E_ALL & ~E_NOTICE);

include('config.php');
$slave->gateway();