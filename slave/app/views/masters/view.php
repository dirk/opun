<?php $this->render('header', array('title' => 'Masters')); ?>
	<h1 class="section">Packages</h1>
	<!--
	- $master['packages']['serving'] = $serving_packages;
	- $master['packages']['not_serving'] = $not_serving_packages;
	- $master['packages']['corrupted'] = $corrupted_packages;
	- $master['packages']['deprecated'] = $deprecated_packages;
	-->
	<h2 class="section" style="color: #4b4;">Serving</h2>
	<ul class="packages">
		<?php foreach($master['packages']['serving'] as $package):
			$mp = $master['identifier'] .'/'. $package['file']; ?>
		<li class="package">
			<h2 class="file">
				<?php echo $package['file']; ?>
			</h2>
			<div class="detail">
				Download: 
				<a href="<?php $this->link('packages/automatic/' . $mp); ?>">Automatic</a>
				- <a href="<?php $this->link('packages/serving/' . $mp); ?>">Stop Serving</a>
				- <a href="<?php $this->link('packages/delete/' . $mp); ?>">Delete</a>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<hr />
	<h2 class="section">Not Serving</h2>
	<ul class="packages">
		<?php foreach($master['packages']['not_serving'] as $package):
			$mp = $master['identifier'] .'/'. $package['file']; ?>
		<li class="package">
			<h2 class="file">
				<?php echo $package['file']; ?>
			</h2>
			<div class="detail">
				Download: 
				<a href="<?php $this->link('packages/automatic/' . $mp); ?>">Automatic</a>
				- <a href="<?php $this->link('packages/serving/' . $mp); ?>">Start Serving</a>
				- <a href="<?php $this->link('packages/delete/' . $mp); ?>">Delete</a>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<hr />
	<h2 class="section" style="color: #b44;">
		Corrupted / Missing
		<sup name="Either the file has a different checksum from the master, or the file doesn't exist" class="help">?</sup>
	</h2>
	<ul class="packages">
		<?php foreach($master['packages']['corrupted'] as $package):
			$mp = $master['identifier'] .'/'. $package['file']; ?>
		<li class="package">
			<h2 class="file">
				<?php echo $package['file']; ?>
			</h2>
			<div class="detail">
				Download: 
				<a href="<?php $this->link('packages/automatic/' . $mp); ?>">Automatic</a>
				- <a href="<?php $this->link('packages/checksum/' . $mp); ?>">Calculate Checksum</a>
				- <a href="<?php $this->link('packages/delete/' . $mp); ?>">Delete</a>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<hr />
	<h2 class="section" style="color: #44b;">Not Added</h2>
	<ul class="packages">
		<?php foreach($master['packages']['missing'] as $package):
			$mp = $master['identifier'] .'/'. $package['file']; ?>
		<li class="package">
			<h2 class="file">
				<?php echo $package['file']; ?>
			</h2>
			<div class="detail">
				<a href="<?php $this->link('packages/add/' . $mp); ?>">Add</a>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<hr />
	<h2 class="section">
		Deprecated
		<sup name="The master is no longer serving this file; deletion is advised to free up space and improve performance" class="help">?</sup>
	</h2>
	<ul class="packages">
		<?php foreach($master['packages']['deprecated'] as $package):
			$mp = $master['identifier'] .'/'. $package['file']; ?>
		<li class="package">
			<h2 class="file">
				<?php echo $package['file']; ?>
			</h2>
			<div class="detail">
				<a href="<?php $this->link('packages/delete/' . $mp); ?>">Delete</a>
				<sup name="The package and the file will be removed" class="help">?</sup>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
<?php $this->render('footer'); ?>