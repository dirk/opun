<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en-us" />
	<title>Generating Checksum &mdash; Opun</title>
	<style type="text/css">
	* {
		margin: 0;
		padding: 0;
	}
	a {color: #44f;}
	body {
		background: #ccc;
		font-family: Helvetica, Arial, sans-serif;font-size: 12px;
	}
	#container {
		margin: 40px auto;
		padding: 20px;
		width: 240px;
		/*-moz-border-radius: 8px;*/
		background: #fff;
		border: 4px solid #aaa;
	}
	#container h2 {
		font-size: 14px;
		text-transform: uppercase;
		margin-bottom: 10px;
		color: #888;
	}
	</style>
</head>
<body>
	<div id="container">
		<h2>Generating Checksum</h2>
		<p style="line-height: 150%;">
			Currently generating the checksum of "<?php echo $package['file']; ?>". This may take a few seconds.
		</p>
		<p style="line-height: 150%; display: none;" id="checksum_done">
			<span style="color: #0c0;">
				Success!
			</span>
			<br />
			<br />
			<a href="<?php $this->link('masters/view/' . $master['identifier']); ?>">Continue</a>
		</p>
	</div>
	<?php
	flush();
	
	//$file = $base_dir . $this->config['packages'] .'/'. $master['identifier'] .'-'. $package['file'];
	$file = $this->get_package_file($master['identifier'], $package['file']);
	
	$master =& $this->masters[$master['identifier']];
	if(!$master){echo 404; return;}
	
	foreach($master['packages']['slave'] as &$pr) {
		if($pr['file'] == $package['file']) {
			$package =& $pr;
		}
	}
	$checksum = md5_file($file);
	
	$package['checksum'] = $checksum;
	$this->save();
	?>
	<script type="text/javascript">
	document.getElementById('checksum_done').style.display = 'block';
	</script>
	<?php
	flush();
	?>
</body>
</html>