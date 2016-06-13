<?php echo $args['before_widget']?>
<?php echo $args['before_title']?>
SEARCH
<?php echo $args['after_title']?>
<form method='<?php echo $method?>' action='<?php echo htmlspecialchars($results_page)?>'>
<dl>
	<?php foreach($components as $config) { ?>
	<dt>
		<?php echo $config['label']; ?>
	</dt>
	<dd>
		<?php echo $config['class']->render($config,$query); ?>
	</dd>
	<?php } ?>
</dl>

<input type='submit' value='Search'>
<?php echo $hidden; ?>
</form>
<?php echo $args['after_widget']?>
