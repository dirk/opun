<?php $this->render('header', array('title' => 'Login')); ?>
	<form action="<?php $this->link('login'); ?>" method="post">
		<label>Password</label>
		<input type="password" name="password" class="text" />
		<input type="submit" class="submit" value="Submit" />
	</form>
<?php $this->render('footer'); ?>