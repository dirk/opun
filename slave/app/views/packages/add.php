<?php $this->render('header', array('title' => 'Packages')); ?>
	<h2 class="section">Package Added</h2>
	<p style="line-height: 150%;">
		The package has been added, however, you still need to fetch the actual file and calculate a checksum.
	</p>
	<br />
	<h2 class="section">Automatic</h2>
	<p style="line-height: 150%;">
		You can click <a href="<?php $this->link('packages/automatic/' . $master['identifier'] .'/' . $package['file']); ?>">here</a> to attempt to automatically fetch the package from the master and calculate the checksum.
	</p>
	<br />
	<h2 class="section">Manual</h2>
	<p style="line-height: 150%;">
		To manually finish adding the package, you need to fetch the <a href="<?php echo $location; ?>">file</a> from the master and manually calculate a checksum to verify its accuracy.
	</p>
	<p style="line-height: 150%; margin-top: 10px;">
		If you do take this route place the file in: <em><?php echo $this->get_package_file($master['identifier'], $package['file']); ?></em>
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