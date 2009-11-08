<?php $this->render('header', array('title' => 'Packages')); ?>
	<h2 class="section">Generating Checksum</h2>
	<p style="line-height: 150%;">
		A checksum is currently being generated. For a large file, this may take a few seconds.
	</p>
	<br />
	<script type="text/javascript">
	$(document).ready(function(){
		$.get('<?php $this->link('packages/checksum/javascript/' . $package['file']); ?>', function(data){
		  if(data == '200'){
				$('#output').html('Success!');
				$('#success').fadeIn();
			}else{
				$('#output').html('Error: ' + data);
			}
		});
	});
	</script>
	<p style="line-height: 150%; color: #0c0;" id="output">
		Generating...
	</p>
	<p style="line-height: 150%; display: none;" id="success">
		The checksum for the file has been successfully generated. Click <a href="<?php $this->link(''); ?>">here</a> to continue.
	</p>
	<!--
	<p style="line-height: 150%;">
		The package has been successfully created, however, before you can distribute it to the network, you need to upload the file ("<em><?php echo $package['file']; ?></em>") to your packages directory ("<em><?php echo $this->config['packages']; ?></em>").
	</p>
	<br />
	<p style="line-height: 150%;">
		Once you've got it uploaded, click <a href="<?php $this->link('packages/checksum/' . $package['file']); ?>">here</a> to generate the checksum and start distributing.
	</p>
	-->
<?php $this->render('footer'); ?>