<form>
<dl>
	<?php foreach($components as $config) { ?>
	<dt>
		<?php echo $config['label']; ?>
	</dt>
	<dd>
		<?php echo $config['class']->render($config); ?>
	</dd>
	<?php } ?>
</dl>

<input type='submit' value='Search'>
<?php echo $hidden; ?>
</form>
