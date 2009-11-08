<?php $this->render('header', array('title' => 'Dashboard')); ?>
	<h2 class="section">Slaves</h2>
	<?php if(count($slaves) > 0): ?>
	<ul class="slaves">
		<?php foreach($slaves as $slave): ?>
		<li class="slave">
			<div class="capacity right">
				<div class="indicator" style="width: 150px;">
					<?php if($slave['packages']['percent'] > $slave['bandwidth']): ?>
						<div style="width: <?php echo round($slave['packages']['percent'] * 150); ?>px;" class="files"></div>
						<div style="width: <?php echo round($slave['bandwidth'] * 150); ?>px;" class="bandwidth"></div>
					<?php else: ?>
						<div style="width: <?php echo round($slave['bandwidth'] * 150); ?>px;" class="bandwidth"></div>
						<div style="width: <?php echo round($slave['packages']['percent'] * 150); ?>px;" class="files"></div>
					<?php endif; ?>
				</div>
				<div class="detail">
					<span class="files">
						<?php echo $slave['packages']['slave']; ?>
						/
						<?php echo $slave['packages']['total']; ?> packages
					</span>
					<br />
					<span class="bandwidth">
						<?php echo round($slave['bandwidth'] * 100); ?>% bandwidth
					</span>
				</div>
			</div>
			<h2 class="identifier"><?php echo $slave['identifier']; ?></h2>
			<div class="detail">
				<a href="<?php $this->link('slaves/reverify/' . $slave['identifier']); ?>">Reverify</a>
					<sup name="Resends a verification message to the slave" class="help">?</sup>
				- <a href="<?php $this->link('slaves/update/' . $slave['identifier']); ?>">Update Status</a>
					<sup name="Fetches slaves status and updates its package list" class="help">?</sup>
			</div>
			<div class="clear"></div>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php else: ?>
		<p style="margin-bottom: 20px;">Looks like there aren't any slaves on your network. Might want to add a few.</p>
	<?php endif; ?>
	<a href="<?php $this->link('slaves/new'); ?>" class="button">Add Slave</a>
	<div style="height: 20px;"></div>
	<hr />
	<h2 class="section">Packages</h2>
	<?php if(count($packages) > 0): ?>
	<ul class="packages">
		<?php foreach($packages as $package): ?>
		<li class="package">
			<div class="capacity right">
				<div class="indicator" style="width: 100px;">
					<div style="width: <?php echo round(($package['total'] / count($slaves)) * 100); ?>px;"></div>
				</div>
				<div class="detail">
					<?php
					if(count($slaves) == 0){
						$percent = 0;
					}else{
						$percent = round(($package['total'] / count($slaves)) * 100);
					}
					?>
					<?php echo $percent; ?>% propagation
				</div>
			</div>
			<h2 class="file">
				<?php echo $package['file']; ?>
				<?php if($package['checksum'] == ''): ?>
					<span class="note">Checksum has not been calculated</span>
				<?php else: ?>
					<span class="note">Checksum: <span style="font-size: 11px;"><?php echo $package['checksum']; ?></span></span>
				<?php endif; ?>
			</h2>
			<div class="detail">
				<a href="<?php $this->link('packages/edit/' . $package['file']); ?>">Edit</a> - 
				<a href="<?php $this->link('packages/delete/' . $package['file']); ?>">Delete</a> - 
				<a href="<?php $this->link('packages/checksum/' . $package['file']); ?>">Calculate Checksum</a>
					<sup name="You should recalculate the checksum if you have changed the file in any way" class="help">?</sup>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php else: ?>
		<p style="margin-bottom: 20px;">To distribute a file over your network, you first need to assign it to a package.</p>
	<?php endif; ?>
	<a href="<?php $this->link('packages/new'); ?>" class="button">Add Package</a>
<?php $this->render('footer'); ?>