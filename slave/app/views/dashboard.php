<?php $this->render('header', array('title' => 'Dashboard')); ?>
	<h2 class="section">Masters</h2>
	<?php if(count($masters) > 0): ?>
	<ul class="masters">
		<?php foreach($masters as $key => $master): ?>
		<li class="master">
			<div class="capacity right">
				<div class="indicator" style="width: 150px;">
					<div style="width: <?php echo round($master['bandwidth']['percent'] * 150); ?>px;" class="bandwidth"></div>
				</div>
				<div class="detail">
					<span class="bandwidth">
						<?php echo round($master['bandwidth']['percent'] * 100); ?>% bandwidth
					</span>
				</div>
			</div>
			<h2 class="identifier">
				<a href="<?php $this->link('masters/view/' . $master['identifier']); ?>" style="color:#000;text-decoration:none;">
					<?php echo $master['identifier']; ?>
				</a>
				<?php if(!$master['verified']): ?>
					<span class="note">
						<strong style="color: #a22;">Not verified!</strong>
						<sup name="Contact the operator of the master server and request him/her to add your slave to the network" class="help">?</sup>
					</span>
				<?php endif; ?>
			</h2>
			<div class="detail">
				<?php if(!$master['verified']): ?>
					Awaiting Verification - 
				<?php endif; ?>
				<a href="<?php $this->link('masters/view/' . $master['identifier']); ?>">View</a> - 
				<a href="<?php $this->link('masters/edit/' . $master['identifier']); ?>">Edit</a> - 
				<a href="<?php $this->link('masters/delete/' . $master['identifier']); ?>">Delete</a>
				
				<p style="line-height: 133%;margin-top: 8px;padding-top: 8px;border-top: 1px solid #eee;">
					<strong style="color: #444;">Packages</strong><br />
					Slave: <?php echo count($master['packages']['slave']); ?> packages
					(<?php echo $master['packages']['serving']; ?> being served, 
						<?php echo $master['packages']['not_serving']; ?> not being served,
						<?php echo $master['packages']['deprecated']; ?> deprecated)
					<br />
					Master: <?php echo count($master['packages']['master']); ?> packages
				</p>
			</div>
			<div class="clear"></div>
		</li>
		<?php endforeach; ?>
	</ul>
	<div style="height: 20px;"></div>
	<?php else: ?>
		<p style="margin-bottom: 20px;">You haven't connected to any masters.</p>
	<?php endif; ?>
	<a href="<?php $this->link('masters/new'); ?>" class="button">Add Master</a>
<?php $this->render('footer'); ?>