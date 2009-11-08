<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en-us" />
	<title>Downloading Package &mdash; Opun</title>
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
	
	.capacity .indicator {
	  -moz-border-radius: 6px;
	  height: 8px;
	  padding: 1px;
	  border: 1px solid #ccc;
	  position: relative;
	}
	.capacity .indicator div {
	  display: block;position: absolute;
	  height: 8px;
	  -moz-border-radius: 4px;
	  background: #444;
	}
	.capacity .detail {
	  margin-top: 4px;
	  font-size: 10px !important;text-transform: uppercase;
	  text-align: right;
	  color: #888;
	}
	.capacity .indicator div.bandwidth {background: #44f;}
	.capacity .detail span.bandwidth {color: #88f;}
	.capacity .indicator div.files {background: #f44;}
	.capacity .detail span.files {color: #f88;}
	</style>
</head>
<body>
	<div id="container">
		<h2>Downloading file...</h2>
		<div class="capacity">
			<div class="indicator" style="width: 236px">
				<div id="indicator_item"></div>
			</div>
		</div>
		<p style="line-height: 150%; display: none; margin-top: 10px;" id="checksum_start">
			<span style="color: #0c0;">
				Successfully downloaded file!
			</span>
			<br />
			<br />
			Generating checksum...
		</p>
		<p style="line-height: 150%; display: none;" id="checksum_done">
			<span style="color: #0c0;">
				Successfully generated checksum!
			</span>
			<br />
			<br />
			<a href="<?php $this->link('masters/view/' . $master['identifier']); ?>">Continue</a>
		</p>
	</div>
	<?php
	include('app/lib/downloader.php');
	global $previous;
	$previous = 0;
	function user_handle_download($part, $whole){
		global $previous;
		$progress = round(($part / $whole) * 118);
		if($progress > $previous):
		?>
		<script type="text/javascript">
		//$('#indicator_item').width('<?php echo $progress * 2; ?>px');
		indicator = document.getElementById('indicator_item');
		indicator.style.width = '<?php echo $progress * 2; ?>px';
		</script>
		<?php
		flush();
		$previous = $progress;
		endif;
	}
	//echo $download_location;
	$request = new HTTP_Request2(
	    $download_location,
	    HTTP_Request2::METHOD_GET, array('store_body' => false)
	);
	$sf = $_SERVER['SCRIPT_FILENAME'];
	$base_dir = substr($sf, 0, strlen($sf) - strlen(basename($sf)));
	$file = $base_dir . $this->config['packages'] .'/'. $master['identifier'] .'-'. $package['file'];
	$request->attach(new HTTP_Request2_Observer_Download($file, 'user_handle_download'));
	$request->send();
	flush();
	
	$master =& $this->masters[$master['identifier']];
	if(!$master){echo 404; return;}
	
	foreach($master['packages']['slave'] as &$pr) {
		if($pr['file'] == $package['file']) {
			$package =& $pr;
		}
	}
	?>
	<script type="text/javascript">
	document.getElementById('checksum_start').style.display = 'block';
	</script>
	<?php
	flush();
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