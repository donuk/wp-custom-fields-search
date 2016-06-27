
<ul class='wpcfs-checkboxes'>
<?php 
$index = 0;
foreach($options['options'] as $option) {
$index+=1;
?>
<li><input type='checkbox' name="<?php echo htmlspecialchars($html_name)?>[]" value="<?php echo htmlspecialchars($option['value']); ?>" <?php if(in_array($option['value'],$query[$html_name])) { ?> checked='checked'<?php } ?> id="<?php echo htmlspecialchars($html_name."-$index")?>"/><label for="<?php echo htmlspecialchars($html_name."-$index")?>">
		<?php echo htmlspecialchars($option['label']);?>
	</label></li>
<?php } ?>
</ul>
