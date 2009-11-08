<?php $this->render('header', array('title' => 'Packages')); ?>
	<h2 class="section">Success!</h2>
	<p style="line-height: 150%;">
		The package has been successfully created, however, before you can distribute it to the network, you need to upload the file ("<em><?php echo $package['file']; ?></em>") to your packages directory ("<em><?php echo $this->config['packages']; ?></em>").
	</p>
	<br />
	<p style="line-height: 150%;">
		Once you've got it uploaded, click <a href="<?php $this->link('packages/checksum/' . $package['file']); ?>">here</a> to generate the checksum and start distributing.
	</p>
<?php $this->render('footer'); ?>