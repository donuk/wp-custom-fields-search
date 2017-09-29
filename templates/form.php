<?php echo $args['before_widget']?>
<form method='<?php echo $method?>' action='<?php echo htmlspecialchars($results_page)?>' class='wpcfs-search-form' id='<?php echo htmlspecialchars($form_id)?>'>
	<?php foreach($components as $config) { ?>
        <div class='wpcfs-input-wrapper'>
            <label for="<?php echo htmlspecialchars($config['html_id'])?>" class='wpcfs-label'>
                <?php echo $config['label']; ?>
            </label>
            <div class='wpcfs-input'>
    	    	<?php echo $config['class']->render($config,$query); ?>
            </div>
        </div>
	<?php } ?>

<input type='submit' value='<?php echo __("Search")?>'>
<?php echo $hidden; ?>
</form>
<?php echo $args['after_widget']?>
