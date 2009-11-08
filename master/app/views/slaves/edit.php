<?php $this->render('header', array('title' => 'Slaves')); ?>
	<h2 class="section"><?php echo ($title) ? $title : 'Edit Slave'; ?></h2>
	<form action="?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
		<label>Identifier</label>
		<input type="text" name="identifier" class="text" value="<?php /*echo $package['file'];*/ ?>" />
		<label>
			Gateway
			<sup name="For an Opun slave, it should be in the format: 'http://slave.example.com/gateway.php?'" class="help">?</sup>
		</label>
		<input type="text" name="gateway" class="text" value="<?php /*echo $package['file'];*/ ?>" />
		<label>Secret</label>
		<input type="text" name="secret" class="text" value="<?php /*echo $package['file'];*/ ?>" />
		
		<?php if($new): ?>
		<p style="line-height: 150%;">
			Before clicking Save, ensure that all the data is correct and <strong>ensure that the slave has already added the master</strong>! When you click Save, the master will notify the slave to verify its addition to the master's network.
		</p>
		<?php endif; ?>
		
		<input type="submit" class="submit" value="Save" />
	</form>
<?php $this->render('footer'); ?>