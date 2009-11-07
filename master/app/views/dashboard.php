<?php $this->render('header', array('title' => 'Login')); ?>
	<h2 class="section">Slaves</h2>
	<ul class="slaves">
		<?php foreach($slaves as $slave): ?>
		<li class="slave">
			<div class="capacity right">
				<div class="indicator" style="width: 150px;">
					<div style="width: <?php echo round($slave['packages']['percent'] * 150); ?>px;" class="files"></div>
				</div>
				<div class="detail">
					<span class="files">
						<?php echo $slave['packages']['slave']; ?>
						/
						<?php echo $slave['packages']['total']; ?> packages
					</span>
				</div>
			</div>
			<h2 class="identifier"><?php echo $slave['identifier']; ?></h2>
		</li>
		<?php endforeach; ?>
			
		<!--
		<li class="slave">
			<div class="capacity right">
				<div class="indicator">
					<div style="width: 75px;" class="bandwidth"></div>
					<div style="width:  45px;" class="files"></div>
				</div>
				<div class="detail">
					<span class="bandwidth">
						5 GB / 10 GB
					</span>
					<br />
					<span class="files">
						3 / 9 files
					</span>
				</div>
			</div>
			<h2 class="identifier">com.esherido.slaves.falcon</h2>
			<div class="detail">
				<a href="#">Stats</a> - 
				<a href="#">Remove</a>
			</div>
			<div class="clear"></div>
		</li>-->
	</ul>
	<a href="<?php $this->link('packages/new'); ?>" class="button">Add Package</a>
<?php $this->render('footer'); ?>