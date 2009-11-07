<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en-us" />
	<title><?php echo $title; ?> &mdash; Opun</title>
	<link rel="stylesheet" href="<?php $this->url('app/static/css/master.css'); ?>" type="text/css" media="screen" />
	<script src="<?php $this->url('app/static/js/jquery.js'); ?>" type="text/javascript"></script>
	<script src="<?php $this->url('app/static/js/jquery.simpletip.js'); ?>" type="text/javascript"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		$("sup.help").simpletip({
			fixed: true,
			position: 'right',
			//persistent: true
			onShow: function(){this.update(this.getParent().attr('name'));}
		}); 
	});
	</script>
</head>
<body>
<div id="container">
	<div id="header">
		<?php if($_SESSION['password']): ?>
		<div class="right">
			Currently logged in. <a href="<?php $this->link('logout'); ?>">Logout</a>
		</div>
		<?php endif; ?>
		<h2>Opun &mdash; Master</h2>
		<?php if($header): ?>
			<h1><?php echo $header; ?></h1>
		<?php else: ?>
			<h1><?php echo $title; ?></h1>
		<?php endif; ?>
	</div>
	<div id="content">