<?php $this->render('header', array('title' => 'Masters')); ?>
	<h2 class="section"><?php echo ($title) ? $title : 'Edit Master'; ?></h2>
	<form action="?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
		<label>Identifier</label>
		<input type="text" name="identifier" class="text" value="<?php echo $master['identifier']; ?>" />
		<label>
			Gateway
			<sup name="For an Opun master, it should be in the format: 'http://master.example.com/gateway.php?'" class="help">?</sup>
		</label>
		<input type="text" name="gateway" style="width: 480px;" class="text" value="<?php echo $master['gateway']; ?>" />
		
		<?php if($new): ?>
		<p style="line-height: 150%;">
			After adding this master, contact the person who runs the master and send them your identifier("<em><?php echo $this->config['identifier']; ?>"</em>), gateway ("<em><?php echo $this->config['gateway']; ?></em>"), and secret.
		</p>
		<?php else: ?>
			<label>
				Bandwidth Maximum (megabytes)
				<sup name="Maximum amount of bandwidth the slave can consume per month" class="help">?</sup>
			</label>
			<input type="text" name="bandwidth_maximum" class="text" value="<?php echo round($master['bandwidth']['maximum'] / 1000000); ?>" />
			<p style="color: #888;">
				Current bandwidth used: <?php echo round($master['bandwidth']['used'] / 1000000); ?> megabytes
			</p>
		<?php endif; ?>
		<input type="submit" class="submit" value="Save" />
	</form>
<?php $this->render('footer'); ?>