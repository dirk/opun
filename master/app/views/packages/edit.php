<?php $this->render('header', array('title' => 'Packages')); ?>
	<h2 class="section"><?php echo ($title) ? $title : 'Edit Package'; ?></h2>
	<form action="?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
		<label>File</label>
		<input type="text" name="file" class="text" value="<?php echo $package['file']; ?>" />
		
		<label>
			Release
			<sup name="The package will not be available for downloading until after this time" class="help">?</sup>
		</label>
		<input type="text" name="release_hour" value="<?php echo date('G', $package['release']); ?>" size="1" /> : 
		<input type="text" name="release_minute" value="<?php echo date('i', $package['release']); ?>" size="1" />
		<select name="release_month">
			<?php
			$months = array('January', 'February', 'March', 'April', 'May', 'June',
				'July', 'August', 'September', 'October', 'November', 'December');
			for($i = 1; $i < 13; $i++):
			$selected = '';
			if(date('n', $package['release']) == $i){$selected = ' selected="selected"';}
			?>
				<option value="<?php echo $i; ?>"<?php echo $selected; ?>><?php echo $months[$i - 1]; ?></option>
			<?php endfor; ?>
		</select>
		<select name="release_day">
			<?php
			for($i = 1; $i < 32; $i++):
			$selected = '';
			if(date('j', $package['release']) == $i){$selected = ' selected="selected"';}
			?>
				<option value="<?php echo $i; ?>"<?php echo $selected; ?>><?php echo $i; ?></option>
			<?php endfor; ?>
		</select>
		<select name="release_year">
			<?php
			for($i = (date('Y') - 3); $i < (date('Y') + 10); $i++):
			$selected = '';
			if(date('Y', $package['release']) == $i){$selected = ' selected="selected"';}
			?>
				<option value="<?php echo $i; ?>"<?php echo $selected; ?>><?php echo $i; ?></option>
			<?php endfor; ?>
		</select>
		
		<input type="submit" class="submit" value="Save" />
	</form>
<?php $this->render('footer'); ?>